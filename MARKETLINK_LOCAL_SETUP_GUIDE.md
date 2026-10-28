# MarketLink — Google Drive Download, XAMPP Setup, Database Import, and Local Testing Guide

> **Audience:** A developer, instructor, or teammate who received the MarketLink project through Google Drive and needs to run it locally on Windows with XAMPP.
>
> **Outcome:** The project opens locally at `http://localhost/MarketLink/`, reads the imported MySQL database, accepts uploads, and can be tested through the public, customer, farmer, and admin experiences.

## 1. What you need before starting

- A Windows computer.
- Internet access to download the Google Drive archive and, optionally, load CDN resources (fonts, maps, icons, charts).
- [XAMPP](https://www.apachefriends.org/) with **Apache**, **MySQL/MariaDB**, **PHP**, and **phpMyAdmin**.
- A modern browser (Chrome, Edge, Firefox, etc.).
- Permission to write into the XAMPP web root, normally `C:\xampp\htdocs`.
- The MarketLink Google Drive folder or a ZIP archive containing the complete project.

The project already includes `vendor/` and its PHPMailer dependency in the delivered source. Therefore, Composer is normally **not required** just to run the provided project.

## 2. Download the project from Google Drive

### Option A — Download an entire Google Drive folder (recommended)

1. Open the shared MarketLink folder link in Google Drive.
2. Confirm that the folder contains the complete project—not only screenshots, recordings, or a single source file. It should include folders such as `admin`, `customer`, `farmer`, `assets`, `config`, `includes`, `uploads`, and the SQL file `marketlink_db (2).sql`.
3. Right-click the **main MarketLink project folder** and choose **Download**.
4. Google Drive prepares a ZIP file. Wait for the browser download to finish completely.
5. Locate the downloaded ZIP, usually in `C:\Users\<YourUser>\Downloads`.

### Option B — Download a ZIP already shared in Drive

1. Select the provided `.zip` file in Google Drive.
2. Click **Download**.
3. Wait until the download finishes. Do not run the project directly from inside the ZIP or from the Downloads folder.

### If Google Drive says the file is too large to scan

Google Drive may display a warning for large archives. If the file came from a trusted project owner, choose **Download anyway**. After download, run your normal antivirus scan if your organization requires it.

## 3. Extract the source code into XAMPP

1. Open File Explorer and go to the downloaded ZIP file.
2. Right-click it → **Extract All...**.
3. Extract first to a temporary location, for example `C:\Users\<YourUser>\Downloads\MarketLink-extracted`.
4. Open the extracted folder and identify the actual application root. The correct folder is the one containing:

   ```text
   index.php
   config\
   includes\
   customer\
   farmer\
   admin\
   assets\
   uploads\
   marketlink_db (2).sql
   ```

5. Copy or move that folder to:

   ```text
   C:\xampp\htdocs\MarketLink
   ```

6. Check that this exact file exists after the move:

   ```text
   C:\xampp\htdocs\MarketLink\index.php
   ```

### Important: avoid an accidental double folder

Do **not** leave the project as:

```text
C:\xampp\htdocs\MarketLink\MarketLink\index.php
```

If it is nested like that, either move the inner `MarketLink` folder one level up or use its actual folder name in the browser URL. The clean expected layout is:

```text
C:\xampp\htdocs\MarketLink\index.php
```

## 4. Open the source code in an editor

Use VS Code, PhpStorm, or another code editor.

For VS Code:

1. Open VS Code.
2. Select **File → Open Folder...**.
3. Select `C:\xampp\htdocs\MarketLink`.
4. Trust the folder if VS Code asks.
5. Open `config\db.php`; this is the configuration file that must point to your local database and local base URL before testing.

Useful folders to recognize:

| Folder/file | Purpose |
|---|---|
| `index.php`, `about.php`, `contact.php`, `stall.php` | Public pages. |
| `customer/`, `farmer/`, `admin/` | Role-specific portals. |
| `api/`, `customer/api/`, `farmer/api/`, `admin/api/` | Backend endpoints called by page JavaScript/forms. |
| `config/` | Database, Gemini AI, and Pusher configuration. |
| `includes/` | Shared authentication, headers/footers, mail, and AI chatbot code. |
| `assets/` | CSS, JavaScript, images, and videos. |
| `uploads/` | Runtime/user-uploaded profile images, product images, and chat attachments. |
| `marketlink_db (2).sql` | The local test database schema plus data dump. |

## 5. Start XAMPP services

1. Run **XAMPP Control Panel**. If Windows blocks it, open it using **Run as administrator**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
4. Both rows should turn green and show running PIDs/ports.
5. In a browser, open:

   ```text
   http://localhost/dashboard/
   ```

   If the XAMPP dashboard appears, Apache is working.
6. Click **Admin** next to MySQL in XAMPP, or open:

   ```text
   http://localhost/phpmyadmin/
   ```

   If phpMyAdmin opens, MySQL/MariaDB is working.

### If Apache or MySQL will not start

- **Apache port conflict:** Skype, IIS, Docker, another Apache, or another web server may already use port 80/443. Stop the conflicting program or configure a different Apache port, such as 8080. If using port 8080, your local `BASE_URL` must become `http://localhost:8080/MarketLink`.
- **MySQL port conflict:** Another MySQL/MariaDB instance may be using port 3306. Stop the other service or change the XAMPP MySQL port and match it in `config/db.php`.
- Check the XAMPP Control Panel logs for the exact conflicting port/service.

## 6. Create and import the local database

The project includes a database dump called:

```text
C:\xampp\htdocs\MarketLink\marketlink_db (2).sql
```

The SQL dump identifies its database as `marketlink_db` and contains the table structure and test/seed data. It does not rely on you manually creating every table.

### Import with phpMyAdmin (easiest method)

1. Open `http://localhost/phpmyadmin/`.
2. Click **New** in the left sidebar.
3. Enter this database name exactly:

   ```text
   marketlink_db
   ```

4. Choose collation `utf8mb4_unicode_ci` if phpMyAdmin asks. Then click **Create**.
5. Click the new `marketlink_db` database in the left sidebar.
6. Click the **Import** tab.
7. Click **Choose File** and select:

   ```text
   C:\xampp\htdocs\MarketLink\marketlink_db (2).sql
   ```

8. Leave the default SQL format selected and click **Import** / **Go**.
9. Wait for the success message. The left sidebar should now show tables including `users`, `farmer_profiles`, `customer_profiles`, `products`, `markets`, `orders`, and `order_items`.

### Import using the MySQL command line (alternative)

Open Command Prompt and run the following with an XAMPP MySQL account that has permission to create/import the database:

```bat
cd C:\xampp\mysql\bin
mysql -u root -e "CREATE DATABASE IF NOT EXISTS marketlink_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root marketlink_db < "C:\xampp\htdocs\MarketLink\marketlink_db (2).sql"
```

If the local MySQL `root` user has a password, add `-p` after `root`; the command will prompt for it.

### Re-importing / resetting test data

Importing the same SQL dump into an already populated database can fail with “table already exists” or duplicate data errors.

For a clean reset during development:

1. In phpMyAdmin, select `marketlink_db`.
2. Open the **Operations** tab.
3. Use **Drop the database (DROP)** only after confirming that this is your disposable local test database.
4. Create `marketlink_db` again and repeat the import steps above.

Never drop a production or shared database.

## 7. Configure MarketLink for local XAMPP

Open:

```text
C:\xampp\htdocs\MarketLink\config\db.php
```

The delivered file may be configured for a hosted/remote database and hosted base URL. For local XAMPP testing, replace only the database connection and base-URL constants with local values similar to this:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'marketlink_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // Default XAMPP root password is often empty
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'MarketLink');
define('BASE_URL', 'http://localhost/MarketLink');
```

Notes:

- If your XAMPP MySQL root account has a password, put it in `DB_PASS`.
- If Apache uses port 8080, use `http://localhost:8080/MarketLink` for `BASE_URL`.
- If you renamed the folder, change the final URL segment to match it exactly—for example, `http://localhost/marketlink-project`.
- Keep `DB_CHARSET` as `utf8mb4` so Urdu/emoji/multilingual data works correctly.
- Do not upload your local database password, API keys, or production credentials to Google Drive, GitHub, or a public document.

## 8. Confirm PHP requirements and writable folders

### PHP extensions

The project needs normal XAMPP PHP support plus these common extensions/features:

- `pdo_mysql` — MySQL access via PDO.
- `curl` — Gemini requests and Pusher server calls.
- `openssl` — secure external API/mail related functionality.
- `mbstring` — safe multibyte text handling (recommended).
- `fileinfo` — uploaded file type inspection (recommended).

To check or enable them:

1. In XAMPP Control Panel, click **Config** beside Apache → open `php.ini`.
2. Search for each extension, for example `extension=curl` or `extension=pdo_mysql`.
3. Remove a leading semicolon (`;`) only if the extension is disabled.
4. Save `php.ini` and restart Apache.

### Upload directories

Keep these existing folders inside the project and make sure PHP/Apache can write to them:

```text
uploads\profiles\
uploads\products\
uploads\chat\
```

They are used for farmer profile photos, product images, and customer/farmer chat attachments. On a typical local Windows/XAMPP installation, permissions work automatically. If an upload fails, right-click the `uploads` folder → **Properties → Security** and make sure your local user and the web-server process can modify it.

### PHP upload limits (only if uploads fail because of file size)

In `php.ini`, review these values and increase them only when needed:

```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 60
```

Restart Apache after changing `php.ini`.

## 9. Open and test the website

1. Ensure Apache and MySQL are still running in XAMPP.
2. Open:

   ```text
   http://localhost/MarketLink/
   ```

3. The MarketLink home page should load with its CSS, images, video/visual assets, navigation, and theme switcher.
4. Test public routes:

   ```text
   http://localhost/MarketLink/about.php
   http://localhost/MarketLink/contact.php
   http://localhost/MarketLink/login.php
   http://localhost/MarketLink/register.php
   ```

5. Use the test users supplied in the imported SQL data, if your project handoff provides their credentials. If none are documented, register a new customer account through `/register.php` and test the customer flow.
6. After login, verify role redirect behavior:

   ```text
   Customer → /customer/dashboard.php
   Farmer   → /farmer/dashboard.php
   Admin    → /admin/dashboard.php
   ```

7. Smoke-test one complete business path:

   ```text
   Customer login → Browse Harvest → Product Detail → Add to Cart → Cart → place order
   ```

8. In a separate browser or incognito window, log in as the corresponding farmer and check:

   ```text
   Farmer Dashboard → Pre-Orders → accept/update order → Order Messages
   ```

9. Open phpMyAdmin and confirm that changes appear in relevant tables such as `cart_items`, `orders`, `order_items`, `notifications`, and `order_messages`.

## 10. Optional integrations and expected local limitations

The core website can run locally with Apache, MySQL, the imported SQL data, and the local `db.php` change. Some external features need their own valid configuration.

| Feature | Config/file | What to do locally | If not configured |
|---|---|---|---|
| Real-time chat updates | `config/pusher.php` | Add your own Pusher app key, secret, ID, and cluster. | Chat can still use normal HTTP refresh/fetch behaviour, but live push updates are unavailable. |
| Gemini AI assistant | `config/gemini.php` | Use a valid private Gemini API key and a currently supported model; keep it private. | AI assistant will show an API/configuration/fallback error, while marketplace pages still work. |
| Email notifications/password reset/contact replies | `includes/mailer.php` and mail/server settings | Configure a valid mail transport/SMTP environment if needed. | Orders and local actions can work, but outgoing email may fail or not arrive. |
| Maps | Leaflet/OpenStreetMap and Google Maps links | Keep internet access for map tiles and direction links. | Map tiles/directions may not load offline; database/location pages still open. |
| Fonts/icons/charts | CDN resources in shared headers | Keep internet access, or later host vendor assets locally. | The site may show fallback fonts/icons or lose chart/map enhancement offline. |

### Important secret-management rule

Treat all database passwords, Gemini API keys, Pusher secrets, SMTP passwords, and hosted credentials as secrets. Do not paste them into reports or submit them with the project. If a real secret was ever included in a shared archive, source repository, or public chat, revoke/rotate it and replace it with a new value.

## 11. Common problems and fixes

| Problem | Likely cause | Fix |
|---|---|---|
| `localhost/MarketLink` shows 404 | Project is not in `htdocs`, folder spelling differs, or it is nested twice. | Confirm `C:\xampp\htdocs\MarketLink\index.php` exists and use the matching URL. |
| “Database Connection Error” | `config/db.php` still points to a remote host, DB name is wrong, or MySQL is stopped. | Start MySQL; use the local configuration in Section 7; confirm `marketlink_db` exists in phpMyAdmin. |
| “Access denied for user root” | Local root password differs from the configured blank password. | Update `DB_PASS` to the local password or reset/use the correct MySQL account. |
| CSS/images links point to the hosted site or 404 | `BASE_URL` was not changed to localhost. | Set `BASE_URL` exactly to `http://localhost/MarketLink` (or your custom port/folder). |
| Import fails or tables already exist | You imported into a database that already contains data. | Use a fresh local `marketlink_db`, or safely drop only the local test database and re-import. |
| phpMyAdmin import times out | SQL dump exceeds PHP/phpMyAdmin limits. | Use the MySQL command-line import from Section 6. |
| Profile/product/chat upload fails | Missing `uploads` subfolder, no folder permission, or PHP upload limit. | Confirm the three upload folders exist; grant local modify access; check `php.ini` upload settings. |
| Login works but redirects to wrong domain | `BASE_URL` still has production URL. | Change it to the exact local browser URL and clear cookies/restart browser if necessary. |
| AI chatbot fails | No valid local Gemini key/network/model. | Configure a private valid key/model, or treat AI as optional for local UI testing. |
| Live messages do not appear instantly | Pusher credentials are placeholders or external access is blocked. | Add valid Pusher values in `config/pusher.php`; otherwise reload the thread to verify HTTP-backed messages. |
| Email does not arrive | Local PHP mail/SMTP is not configured. | Configure a mail transport or accept that email delivery is not part of basic local test setup. |

## 12. Final local-run checklist

- [ ] Project downloaded from Google Drive completely.
- [ ] ZIP extracted.
- [ ] Application root is `C:\xampp\htdocs\MarketLink\index.php`.
- [ ] Apache and MySQL are running in XAMPP.
- [ ] `marketlink_db` was created/imported from `marketlink_db (2).sql`.
- [ ] `config/db.php` uses local DB values and `BASE_URL = http://localhost/MarketLink`.
- [ ] `uploads/profiles`, `uploads/products`, and `uploads/chat` exist and are writable.
- [ ] Home page opens at `http://localhost/MarketLink/`.
- [ ] Login/registration works and each role redirects to its correct dashboard.
- [ ] Customer product/cart/order flow works against local database data.
- [ ] Farmer/admin pages load with their respective authorized accounts.
- [ ] Optional Pusher, Gemini, and email integrations are configured only if they are required for the test.

