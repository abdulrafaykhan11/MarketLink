# MarketLink — Website Structure and Sitemap Handoff

> **Purpose:** This is an implementation-based handoff for creating a visual sitemap in Gemini. It describes the current MarketLink PHP/MySQL website, its pages, role permissions, shared layout, user flows, API connections, and data areas. It is not a proposed structure; it reflects the files presently in the project.

## 1. Product summary

**MarketLink** is a Pakistan-focused digital farmers-market platform. It connects:

- **Guests** with the marketing site, farmer stalls, registration, login, and support.
- **Customers** with produce discovery, cart/pre-orders, pickup markets, order tracking, farmer messaging, reviews, favorites, and alerts.
- **Farmers / producers** with a stall console for stock, products, pickup slots, orders, customer messages, analytics, reviews, and profile/location.
- **Administrators** with system-wide moderation and operations: users, farmer approval, markets, catalog, orders, reviews, inquiries, announcements, categories, and reports.

**Technology:** PHP pages rendered server-side, MySQL via PDO, session-based authentication, JavaScript `fetch()` endpoints for interactive actions, PHPMailer for mail, Pusher for real-time order chat, Gemini-backed AI assistant, Leaflet/OpenStreetMap for maps, and Chart.js for analytics.

**Production base path:** `https://marketlink.infinityfreeapp.com` (the sitemap should show paths relative to the website root).

## 2. High-level sitemap tree

```text
MarketLink /
│
├── Public / guest experience
│   ├── /index.php (home; also /)
│   │   ├── #how-it-works
│   │   ├── #local-stalls → /stall.php?id={farmer_id}
│   │   ├── #testimonials
│   │   ├── #farm-contrast
│   │   └── #faq
│   ├── /stall.php?id={farmer_id} (public individual farmer/stall page)
│   ├── /about.php
│   ├── /contact.php
│   ├── /login.php
│   ├── /register.php
│   ├── /forgot_password.php
│   ├── /intro.php → redirects to /index.php
│   └── /logout.php → ends session → /login.php
│
├── Customer portal — customer role required
│   ├── /customer/dashboard.php
│   ├── /customer/products.php
│   │   └── /customer/product_detail.php?id={product_id}
│   ├── /customer/markets.php
│   ├── /customer/cart.php
│   ├── /customer/orders.php
│   ├── /customer/chat.php?order_id={order_id}
│   ├── /customer/favorites.php?tab=products|farmers
│   ├── /customer/reviews.php?order_id={order_id}
│   ├── /customer/notifications.php
│   └── /customer/profile.php
│
├── Farmer / producer portal — farmer role required (admin may access)
│   ├── /farmer/dashboard.php
│   ├── /farmer/orders.php
│   ├── /farmer/chat.php?order_id={order_id}
│   ├── /farmer/pickup-slots.php
│   ├── /farmer/products.php?action=new (or edit/filter states)
│   ├── /farmer/analytics.php?export=csv
│   ├── /farmer/reviews.php
│   └── /farmer/profile.php
│
└── Admin portal — admin role required
    ├── /admin/dashboard.php
    ├── /admin/farmers.php?status=pending
    ├── /admin/users.php
    ├── /admin/markets.php
    ├── /admin/products.php
    ├── /admin/orders.php
    ├── /admin/reviews.php
    ├── /admin/inquiries.php
    ├── /admin/announcements.php
    ├── /admin/categories.php
    └── /admin/reports.php?range=7|30|90|all
```

## 3. Global navigation and shared layout

### Public pages

`includes/header.php` provides the document head, core CSS, theme loader, and toast container. `includes/navbar.php` provides the global header. `includes/site_footer.php` provides the public footer. `includes/footer.php` supplies shared client-side scripts.

The public navbar and footer connect users to:

- Home and the Home-page anchors **The Journey**, **Local Stalls**, **Reviews**, and **Why Direct?**
- **About Us** → `/about.php`
- **Contact** → `/contact.php`
- Guests: **Sign In** → `/login.php`; **Create Free Account / Join MarketLink** → `/register.php`
- Signed-in users: **Portal** → `/{role}/dashboard.php`; **Sign Out** → `/logout.php`

All three portal types also have their own shared header/sidebar/footer layout. Every portal layout exposes a return/open link to the public site and a sign-out action. A global light/dark theme switcher is available across the site.

### Access control and post-login routing

`includes/auth_guard.php` starts sessions and implements access rules.

| Visitor state / role | What happens |
|---|---|
| Unauthenticated visitor opens a role-protected page | Redirected to `/login.php` with a flash error. |
| Customer logs in | Redirected to `/customer/dashboard.php`. |
| Farmer logs in | Redirected to `/farmer/dashboard.php`. |
| Admin logs in | Redirected to `/admin/dashboard.php`. |
| Logged-in visitor opens login or registration | Redirected to that user’s role dashboard. |
| User opens a portal outside their role | Receives HTTP 403 Access Denied. |

## 4. Public and authentication pages

| URL | Audience | Purpose | Main connections / actions |
|---|---|---|---|
| `/` or `/index.php` | Everyone | Marketing home page: farm-to-table proposition, how it works, active producer stalls, testimonials, FAQs, and CTAs. | Browse harvest → customer catalog; Find local stalls and stall cards → dynamic stall page; registration CTAs → `/register.php`. |
| `/stall.php?id={farmer_id}` | Everyone | Public profile for one approved farmer stall: identity, location map, available products, farmer reviews, and reserve CTA. | Guest reserve → login (with redirect back); customer reserve → `/customer/products.php?farmer={id}`; links to Google Maps and related public navigation. |
| `/about.php` | Everyone | Mission, vision, food-supply-chain problem, platform principles, origin story. | Account CTA → registration; support CTA → contact. |
| `/contact.php` | Everyone | Contact information, support/contact form, and common questions. | Submits to `/api/contact_submit.php`; messages become admin inquiries and can send mail. |
| `/register.php` | Guests only | Customer/farmer account registration. | POST form → `/api/register_process.php`; username suggestions/field availability are supported by public APIs; login link → `/login.php`. |
| `/login.php` | Guests only | Email/username and password sign-in. | POST → `/api/login_process.php`; redirect destination depends on user role; links to registration and password reset. |
| `/forgot_password.php` | Everyone | Requests password reset through the mail service. | Back link → login. |
| `/intro.php` | Legacy/disabled | Immediately issues a 302 redirect to home. | Do not make it a primary sitemap destination. |
| `/logout.php` | Signed-in users | Destroys the session. | Redirects to `/login.php`. |

### Public service endpoints (not normal visual pages)

- `/api/register_process.php` — validates and creates accounts and role profile records; sends mail where applicable.
- `/api/login_process.php` — authenticates users, establishes the session, and decides the role dashboard redirect.
- `/api/suggest_usernames.php` — returns suggested available usernames.
- `/api/check_availability.php` — checks registration field availability.
- `/api/contact_submit.php` — accepts the contact form; persists the inquiry and uses mail handling.
- `/api/chat_service.php` — shared authenticated order-chat API and Pusher channel authorization.
- `/api/ai_assistant.php` — public/standard AI assistant request endpoint powered through `includes/GeminiBot.php` and `config/gemini.php`.
- `/api/intro_done.php` — legacy intro-completion handler; intro itself is currently disabled.

## 5. Customer portal

### Persistent customer navigation

`customer/includes/customer_header.php` loads the customer shell and data used by its badges. `customer/includes/customer_sidebar.php` contains these permanent routes:

**Marketplace:** Dashboard, Browse Harvest, Markets & Map.  
**Orders & Cart:** Pre-Order Basket, My Orders & Tracking, Seller Order Chat, Saved Favorites, Reviews & Ratings.  
**Preferences:** Notifications, Profile & Pickup, Visit Main Website, Sign Out.

The customer top bar includes global product search (submits to `/customer/products.php?search=...`), notifications, cart badge/link, profile link, theme switcher, and logout.

| URL | What the customer sees / does | Connected pages and data |
|---|---|---|
| `/customer/dashboard.php` | Personal overview: upcoming pickup, latest order, order counts, nearby/featured markets, recommended products. | CTAs lead to products, markets, orders, and individual product details. |
| `/customer/products.php` | Product catalog. Supports `search`, `category`, `market_id`, and farmer-driven filtering. | Product cards → product detail; category and market filters keep the visitor in catalog; adding happens on detail. |
| `/customer/product_detail.php?id={product_id}` | Product information, farmer/stall identity, ratings, related products, pickup/order context. | Farmer name → `/stall.php?id={farmer_id}`; add-to-cart → customer cart API; active order chat → `/customer/chat.php?order_id=...`; category/related products return to catalog/detail. |
| `/customer/markets.php` | Market list and interactive location map. | Market card → filtered catalog (`?market_id=...`); directions open Google Maps. |
| `/customer/cart.php` | Pre-order basket, grouped around selected farmer/market and pickup choices. | Quantity/remove/cart updates call cart API; checkout creates orders via API; empty state returns to catalog. |
| `/customer/orders.php` | Order history and status tracking; filters: all, active, completed, cancelled. | A customer can message the farmer, review eligible completed orders, and use order API actions such as cancellation/collection confirmations. |
| `/customer/chat.php?order_id={order_id}` | Order-specific conversation with the farmer; attachments and live updates. | Reads/sends through shared chat service; order link associates messages with the relevant order. |
| `/customer/favorites.php?tab=products|farmers` | Saved product and farmer lists. | Product/farmer cards go back into filtered product discovery, markets, or product pages; favorite API toggles items. |
| `/customer/reviews.php?order_id={order_id}` | Writes or manages a farmer/order review for eligible orders. | Usually entered from an order; submission → review API. |
| `/customer/notifications.php` | Platform and order alerts. | Mark-read and mark-all-read controls update notifications on the same page. |
| `/customer/profile.php` | Customer contact/profile and pickup preferences. | Stores information used by ordering/pickup flows. |

### Customer APIs

| Endpoint | Used by | Responsibility |
|---|---|---|
| `/customer/api/cart.php` | Product detail and cart | Add/change/remove cart items, validate stock, and checkout/cart-related operations. It may send order email. |
| `/customer/api/order.php` | Orders page | Customer order actions, including cancellation/status actions. It may send order email. |
| `/customer/api/order_details.php` | Customer order UI | Returns authenticated order details. |
| `/customer/api/favorite.php` | Catalog/detail/favorites UI | Adds/removes saved products or farmers. |
| `/customer/api/review.php` | Reviews page | Creates/updates customer review content after eligibility checks. |
| `/customer/api/ai_assistant.php` | Customer AI widget | Authenticated customer AI assistant endpoint. |

## 6. Farmer / producer portal

### Persistent farmer navigation

`farmer/includes/farmer_header.php`, `farmer_sidebar.php`, and `farmer_footer.php` create the producer console. Header data includes the farmer profile, active stall assignments, pending/active order counts, notifications, and cutoff time.

Sidebar routes are: **Dashboard**, **Pre-Orders**, **Order Messages**, **Pickup Slots**, **My Produce**, **Sales Analytics**, **Customer Reviews**, **Stall Profile**, **View Marketplace**, and **Sign Out**. The top bar provides an orders search, cutoff ticker, quick **Add Produce** link to `/farmer/products.php?action=new`, notification anchor to dashboard announcements, theme toggle, and logout.

| URL | What the farmer sees / does | Connected pages and data |
|---|---|---|
| `/farmer/dashboard.php` | Stall-level operational overview: sales/orders, stock signals, recent activity, announcements, and shortcut actions. | Links to orders, catalog, pickup configuration, and profile; dashboard announcements are admin-authored. |
| `/farmer/orders.php` | Pre-order fulfilment queue. Filter by status/market/search; open an order inspection view; update acceptance/ready/completed/cancelled state. | Chat button → `/farmer/chat.php?order_id=...`; pickup configuration → pickup slots; status changes → farmer order API and customer notifications/mail. |
| `/farmer/chat.php?order_id={order_id}` | Order-linked messages with the purchasing customer, including attachments/live updates. | Uses shared `/api/chat_service.php`; only order participants can access related threads. |
| `/farmer/pickup-slots.php` | Defines order cutoff, pickup slots/capacity, and farmer-to-market stall operations. | Changes call farmer APIs; customer checkout reads these slots and active stalls. |
| `/farmer/products.php?action=new` | Produce catalog and weekly inventory. Create/edit products, attach images, control available quantity and stock status. | Save/delete/stock actions use farmer APIs; published inventory populates customer catalog and the public stall page. |
| `/farmer/analytics.php?export=csv` | Sales, orders, product and market performance charts; can export CSV. | Reads farmer/order/product data; shortcut to orders. |
| `/farmer/reviews.php` | Customer ratings and review replies; filters for unanswered/rating levels. | Reply API stores farmer responses; customer and public stall views render approved reviews. |
| `/farmer/profile.php` | Stall identity, contact details, story, business address/location map, order cutoff, and profile photo. | Saves profile and uploads photo via APIs; approved profile feeds `/stall.php?id={farmer_id}` and catalog product cards. |

### Farmer APIs

| Endpoint | Responsibility |
|---|---|
| `/farmer/api/save_profile.php` | Saves farmer stall/profile/location information. |
| `/farmer/api/upload_profile_photo.php` | Uploads farmer profile image to `uploads/profiles/`. |
| `/farmer/api/save_product.php` | Creates or updates a product and its stock/image data. |
| `/farmer/api/delete_product.php` | Removes a farmer product. |
| `/farmer/api/toggle_stock.php` | Switches availability/stock status. |
| `/farmer/api/save_slot.php` | Creates/edits/removes pickup slots. |
| `/farmer/api/save_cutoff.php` | Updates the daily order cutoff time. |
| `/farmer/api/stall_action.php` | Manages farmer-market stall assignment/action. |
| `/farmer/api/update_order.php` | Updates fulfilment status and can trigger order mail/notifications. |
| `/farmer/api/save_reply.php` | Saves a farmer reply to a customer review. |

## 7. Admin portal

### Persistent admin navigation

`admin/includes/admin_header.php`, `admin_sidebar.php`, and `admin_footer.php` create the administrator console. The sidebar shows live badges for pending farmers, active orders, unmoderated reviews, and unread inquiries. It links to the public site in a new tab and supports theme switching/log out.

| URL | Admin responsibility | Main connections / outcomes |
|---|---|---|
| `/admin/dashboard.php` | Executive dashboard: platform counts, activity, sales/order visualization, pending approvals, inquiries, and quick actions. | Direct links to reports, announcements, pending farmers, markets, categories, reviews, orders, and users. |
| `/admin/farmers.php?status=pending` | Reviews farmer applications/stalls and approval state. | Approve/reject/status actions call farmer action API; approval controls public/catalog visibility. |
| `/admin/users.php` | Finds and filters all customer/farmer/admin accounts by role. | Enables/disables account status through user-status API. |
| `/admin/markets.php` | Creates/edits/removes farmers markets, location, operational days and attributes. | Market action API; markets appear in customer map/catalog filters and stall assignments. |
| `/admin/products.php` | Platform-wide product catalog moderation. | Product action API controls status/visibility. |
| `/admin/orders.php` | Central order oversight with status/search filters and order-detail modal. | Reads `/admin/api/order_details.php`; connects operationally to farmer/customer orders. |
| `/admin/reviews.php` | Moderates farmer/product reviews and marks featured testimonials. | Review action API; moderated/featured content appears in public and portal experiences. |
| `/admin/inquiries.php` | Reads contact submissions, updates status, and replies to senders. | Contact form writes here; mail helper sends replies. |
| `/admin/announcements.php` | Creates, updates, and deletes platform announcements. | Announcements surface on farmer dashboard. |
| `/admin/categories.php` | Maintains product-category master data. | Category action API; categories drive customer catalog filters and farmer product forms. |
| `/admin/reports.php?range=7|30|90|all` | Time-range reporting and executive analytics. | Dashboard/reporting view over orders, revenue, users, and marketplace metrics. |

### Admin APIs

- `/admin/api/farmer_action.php` — farmer approval/moderation actions.
- `/admin/api/user_status.php` — account enable/disable changes.
- `/admin/api/market_action.php` — market CRUD actions.
- `/admin/api/product_action.php` — product moderation/status actions.
- `/admin/api/order_details.php` — order information for admin modal/view.
- `/admin/api/review_action.php` — review moderation and featured state.
- `/admin/api/faq_action.php` — FAQ/chatbot FAQ management.
- `/admin/api/category_action.php` — category CRUD actions.
- `/admin/api/announcement_action.php` — announcement CRUD actions.

`/admin/chatbot_faqs.php` exists as an admin-protected supporting page/handler for chatbot FAQ management, even though it is not currently exposed in the main admin sidebar. Include it as an **admin utility / unlinked route** rather than a top-level primary navigation item.

## 8. Critical end-to-end user journeys (show these as cross-links in the sitemap)

### A. Guest discovers a farmer and becomes a customer

```text
Home → Local Stalls section → Public Stall page
     → Sign in / Register (if not authenticated)
     → Customer Dashboard → Browse Harvest → Product Detail
     → Add to Pre-Order Basket → Cart / choose pickup context → Order created
     → My Orders → Farmer Chat and (after eligibility) Review
```

### B. Customer pre-order lifecycle

```text
Customer Products / Product Detail
  → cart_items
  → Cart validates stock, selected farmer/market, and pickup slot
  → order + order_items created
  → Farmer Pre-Orders receives it with status "placed"
  → Farmer accepts / prepares / marks ready
  → Customer My Orders and Notifications reflect the status
  → Customer collects or cancels as permitted
  → Completed order can lead to Customer Review
  → Farmer Reviews can reply; Admin Reviews can moderate/feature it
```

### C. Farmer publishing and fulfilment lifecycle

```text
Admin approves farmer profile
  → Farmer completes Stall Profile and profile photo
  → Farmer joins/configures market stall and pickup slots
  → Farmer creates products / weekly inventory
  → Products become available in Customer Browse Harvest and Public Stall
  → Customer order arrives in Farmer Pre-Orders
  → Farmer updates fulfilment status and messages customer
  → Farmer views results in Analytics and responds to Reviews
```

### D. Support and platform content lifecycle

```text
Public Contact form → contact_messages → Admin Inquiries → reply email
Admin Categories → Farmer Product Form + Customer Catalog Filters
Admin Markets → Farmer Stall Assignment + Customer Markets Map + Catalog Filters
Admin Announcements → Farmer Dashboard
Admin FAQ management → AI/chatbot knowledge area
```

## 9. Data model / database connection map

Database connection code lives in `config/db.php`; pages obtain a shared PDO connection with `getDBConnection()`. Do **not** include credentials in any external prompt or visual artifact.

| Data area | Main tables | Used by |
|---|---|---|
| Accounts and roles | `users`, `customer_profiles`, `farmer_profiles` | Login, registration, role routing, profiles, public stalls. |
| Market/stall setup | `markets`, `farmer_market_stalls`, `pickup_slots` | Admin markets, farmer pickup/stall controls, customer map and checkout. |
| Catalog and availability | `product_categories`, `products`, `product_images`, `weekly_inventory` | Farmer catalog, customer discovery/detail, public stall. |
| Cart and sales | `cart_items`, `orders`, `order_items` | Customer cart/checkout/orders, farmer fulfilment, admin oversight and reports. |
| Communication and alerts | `order_messages`, `notifications`, `platform_announcements`, `contact_messages` | Customer/farmer chat, notifications, farmer dashboard announcements, admin support inbox. |
| Reputation/saved content | `farmer_reviews`, `product_reviews`, `favorite_farmers`, `favorite_products` | Customer reviews/favorites, farmer replies, admin moderation, testimonials. |
| AI support | `ai_chatbot_faqs` | AI/chatbot FAQ management and assistant responses. |

## 10. Repository folder structure

```text
MarketLink/
├── index.php, about.php, contact.php, stall.php        # Public pages
├── login.php, register.php, forgot_password.php         # Authentication UI
├── intro.php, logout.php                                # Redirect/session utility pages
├── customer/                                            # Customer portal pages and API
│   ├── includes/                                        # Customer header/sidebar/footer
│   └── api/                                             # Cart, order, favorite, review, AI endpoints
├── farmer/                                              # Farmer portal pages and API
│   ├── includes/                                        # Farmer header/sidebar/footer
│   └── api/                                             # Profile, product, stock, slot, stall, order, review actions
├── admin/                                               # Admin portal pages and API
│   ├── includes/                                        # Admin header/sidebar/footer
│   └── api/                                             # Moderation and management actions
├── api/                                                 # Shared/public auth, contact, chat, AI endpoints
├── includes/                                            # Shared auth, layout, mail, chatbot code
├── config/                                              # DB, Gemini, Pusher, data/asset helper configuration
├── assets/
│   ├── css/                                             # Main, responsive, public, portal, auth, AI styles
│   ├── js/                                              # Theme, UI animations, validation, customer and AI scripts
│   ├── images/                                          # Logo, home/catalog/intro image assets
│   └── videos/                                          # Home/intro visual video assets
├── uploads/
│   ├── profiles/                                        # Farmer profile photos
│   ├── products/                                        # Uploaded product images
│   └── chat/                                            # Order-chat file/image attachments
├── marketlink_db (2).sql                                # Database schema and seeded data
├── composer.json / composer.lock                         # PHP dependencies (PHPMailer)
└── vendor/                                              # Composer-installed packages; not part of visual sitemap
```

## 11. Visual sitemap instructions for Gemini

Create a clear **role-based sitemap**, not one flat list:

1. Put **MarketLink Home** at the top/center.
2. Branch to **Public Website**, **Authentication**, **Customer Portal**, **Farmer Portal**, and **Admin Portal**.
3. Use a different color or label for each access level: public, guest-only, customer-only, farmer-only, admin-only, and API/non-page.
4. Make `/stall.php?id={farmer_id}` and `/customer/product_detail.php?id={product_id}` visibly **dynamic detail pages**.
5. Draw cross-portal arrows for the four end-to-end flows in Section 8—especially the customer order lifecycle and the farmer approval → product publication journey.
6. Show shared components as reusable blocks: Public Navbar/Footer; Customer Shell; Farmer Shell; Admin Shell; Auth Guard; Database; Shared Chat Service; AI Assistant; Mail/Notification service.
7. Keep query parameters as small annotations rather than independent pages (examples: product id, farmer id, market filter, order id, status filter).
8. Treat API directories as backend/service nodes attached to the page(s) that call them, rather than visible navigation pages.

### Gemini-ready prompt

```text
Create a professional, detailed role-based visual sitemap for the PHP/MySQL website “MarketLink,” a Pakistan-focused farm-to-table marketplace. Use the supplied MarketLink Website Structure and Sitemap Handoff as the authoritative source.

Show Home at the top and create five main branches: Public Website, Authentication, Customer Portal, Farmer/Producer Portal, and Admin Portal. Label every route with its URL and short purpose. Use color-coded access permissions: public, guests only, customer only, farmer only, admin only, and backend/API.

Include shared layout blocks (public navbar/footer; customer, farmer and admin shells), authentication/role redirects, database, chat, AI assistant, mail/notifications, maps, and analytics as supporting service nodes. Mark /stall.php?id={farmer_id} and /customer/product_detail.php?id={product_id} as dynamic detail pages. Add arrows for the customer ordering flow, farmer publishing/fulfilment flow, and admin moderation/configuration effects on public/customer/farmer views. Do not expose database credentials or secrets. Make the result suitable for a software project presentation: readable, hierarchical, and detailed.
```

