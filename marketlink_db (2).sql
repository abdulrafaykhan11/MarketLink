-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2026 at 08:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `marketlink_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_chatbot_faqs`
--

CREATE TABLE `ai_chatbot_faqs` (
  `faq_id` int(11) NOT NULL,
  `category` varchar(50) DEFAULT 'General',
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_chatbot_faqs`
--

INSERT INTO `ai_chatbot_faqs` (`faq_id`, `category`, `question`, `answer`, `keywords`, `is_active`, `updated_at`) VALUES
(1, 'Pre-Orders & Pickup', 'How does pre-ordering work on MarketLink?', 'Browse products from verified local farmers, add your items to the pre-order basket, and choose an available market pickup slot. When market day arrives, visit the farmer\'s stall, inspect your harvest, and pay in person. No online transaction fees!', 'pre-order, how it works, order, reserve, pickup', 1, '2026-09-23 19:58:03'),
(2, 'Payment & Billing', 'Do I have to pay online when placing a pre-order?', 'No! Per MarketLink policy, all pre-orders are strictly reserved online and paid for in person at the stall upon pickup. You can inspect the freshness of your produce before settling payment.', 'pay, payment, online payment, card, cash, pickup', 1, '2026-09-23 19:58:03'),
(3, 'Order Cancellation & Cutoff', 'Can I cancel or modify my pre-order?', 'Yes, you can cancel or adjust your order through your Orders Dashboard as long as it is before the farmer\'s daily order cut-off time (typically 6:00 PM the evening before market day).', 'cancel, modify, cutoff, deadline, change order', 1, '2026-09-23 19:58:03'),
(4, 'Market Timings & Locations', 'Where are the markets located and what are the timings?', 'We currently serve 4 central locations: Downtown Green Plaza (Sat/Sun 7am-1pm), Riverside Organic Market (Fri/Sat 8am-2pm), Hillside Heritage Market (Wed/Sun 6:30am-12:30pm), and Suburban Eco Fair. Check the \"Markets & Map\" tab for interactive GPS navigation.', 'markets, location, map, timings, hours, directions, where', 1, '2026-09-23 19:58:03'),
(5, 'Quality & Organic Assurance', 'Are all products certified organic and fresh?', 'All participating farmers are verified by MarketLink. Farmers harvest produce fresh on the morning or evening preceding market day, guaranteeing high nutritional quality and peak crispness without cold-storage delays.', 'organic, quality, fresh, pesticide, health, guarantee', 1, '2026-09-23 19:58:03'),
(6, 'Farmer Direct Contact', 'How do I contact a farmer directly regarding special requests?', 'Each farmer stall profile displays their direct contact person, verified business phone, and stall location number. You can also view customer reviews on their profile.', 'contact, phone, farmer, question, stall number', 1, '2026-09-23 19:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `customer_id`, `product_id`, `stall_id`, `quantity`, `added_at`) VALUES
(14, 8, 11, 5, 1, '2026-09-25 16:59:15');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `replied_by` int(11) DEFAULT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `admin_reply`, `replied_at`, `replied_by`, `status`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 'Ahmed Raza', 'ahmed.test@example.com', '0300-9876543', 'Farmer Registration Help', 'Hello Admin, I have a 10-acre organic fruit orchard near Model Town and want to set up a certified stall. Could you guide me on the physical market stall allocation process?', NULL, NULL, NULL, 'unread', '127.0.0.1', '2026-09-24 22:38:13', '2026-09-24 22:38:13'),
(2, 'Ahzam', 'abdulrafayaptech32@gmail.com', '0318-2489041', 'Order / Pickup Issue', 'i havent recieved my order', 'ok', '2026-09-24 22:44:27', 4, 'replied', '::1', '2026-09-24 22:43:32', '2026-09-24 22:44:27'),
(3, 'Fatima Chaudhry', 'fatima.chaudhry@hotmail.com', '0321-4445556', 'Farmer Registration Help', 'Assalam o Alaikum, I am a small mango orchard owner from Rahim Yar Khan. I have 5 acres of certified organic land and want to join MarketLink as a producer. What documents do I need and how long does approval take?', NULL, NULL, NULL, 'unread', '::1', '2026-09-24 23:06:48', '2026-09-24 23:06:48'),
(4, 'Hamid Awan', 'hamid.awan@live.com', '0311-0001112', 'Order / Pickup Issue', 'My order #ML-2026-05023 shows \"completed\" but I never actually received my items. I arrived at 9 AM sharp but the stall was empty. Can you please investigate and arrange a refund or replacement?', NULL, NULL, NULL, 'unread', '::1', '2026-09-24 23:06:48', '2026-09-24 23:06:48'),
(5, 'Nabila Javed', 'nabila.javed@gmail.com', '0300-3334445', 'General Question', 'Is there any option for home delivery? I am elderly and cannot visit markets easily. I would happily pay extra for a delivery fee if MarketLink can partner with a courier service.', NULL, NULL, NULL, 'unread', '::1', '2026-09-24 23:06:48', '2026-09-24 23:06:48'),
(6, 'Danish Ali', 'danish.ali@live.com', '0311-6667778', 'Feedback & Suggestions', 'Brilliant platform MashaAllah! One suggestion: please add a \"bundle/basket\" ordering feature where I can select products from multiple farmers and have them in one transaction. Would make shopping so much easier!', NULL, NULL, NULL, 'unread', '::1', '2026-09-24 23:06:48', '2026-09-24 23:06:48'),
(7, 'Saima Rizvi', 'saima.rizvi@yahoo.com', '0307-7778889', 'Partnership / Media', 'Hi, I am a food journalist for Dawn.com and would like to write a feature article about MarketLink and its impact on small-scale farmers. Could you arrange interviews with 2-3 farmers for next week?', NULL, NULL, NULL, 'unread', '::1', '2026-09-24 23:06:48', '2026-09-24 23:06:48');

-- --------------------------------------------------------

--
-- Table structure for table `customer_profiles`
--

CREATE TABLE `customer_profiles` (
  `customer_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `default_address` text DEFAULT NULL,
  `saved_latitude` decimal(10,8) DEFAULT NULL,
  `saved_longitude` decimal(11,8) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_profiles`
--

INSERT INTO `customer_profiles` (`customer_id`, `full_name`, `default_address`, `saved_latitude`, `saved_longitude`, `created_at`) VALUES
(1, 'Sara Ahmed', 'House 42, Green Street, City', NULL, NULL, '2026-09-23 16:31:31'),
(3, 'Bilal Hassan', NULL, NULL, NULL, '2026-09-23 16:36:32'),
(4, 'admin', NULL, NULL, NULL, '2026-09-26 12:02:54'),
(5, 'Ahzam', NULL, NULL, NULL, '2026-09-23 16:45:54'),
(7, 'Hamza Tariq', NULL, NULL, NULL, '2026-09-23 17:05:51'),
(8, 'ahzam', NULL, NULL, NULL, '2026-09-23 17:09:12'),
(22, 'Ali Raza', 'House 14-B, Garden Town, Lahore', 31.50770000, 74.33130000, '2026-09-24 23:06:46'),
(23, 'Fatima Chaudhry', 'Flat 3, Canal View, Lahore', 31.52040000, 74.36410000, '2026-09-24 23:06:46'),
(24, 'Omar Sheikh', '22-C, Gulberg III, Lahore', 31.51290000, 74.34830000, '2026-09-24 23:06:46'),
(25, 'Mehwish Khan', 'House 5, DHA Phase 2, Lahore', 31.47270000, 74.40320000, '2026-09-24 23:06:46'),
(26, 'Danish Ali', 'Bahria Orchard Phase 1, Lahore', 31.36310000, 74.17820000, '2026-09-24 23:06:46'),
(27, 'Sana Tariq', 'Model Town Block L, Lahore', 31.48940000, 74.31240000, '2026-09-24 23:06:46'),
(28, 'Bilal Ahmed', 'Johar Town Q Block, Lahore', 31.46510000, 74.27900000, '2026-09-24 23:06:46'),
(29, 'Amina Malik', 'Gulshan-e-Ravi, Lahore', 31.53290000, 74.31500000, '2026-09-24 23:06:46'),
(30, 'Usman Nawaz', 'Township Sector B, Lahore', 31.48700000, 74.29500000, '2026-09-24 23:06:46'),
(31, 'Rabia Qureshi', 'Valencia Housing Phase 2, Lahore', 31.43700000, 74.26200000, '2026-09-24 23:06:46'),
(32, 'Zain Butt', 'Wapda Town Phase 1, Lahore', 31.42850000, 74.26800000, '2026-09-24 23:06:46'),
(33, 'Saima Rizvi', 'Safari Garden, Lahore', 31.56200000, 74.25900000, '2026-09-24 23:06:46'),
(34, 'Hamid Awan', 'F-10 Markaz, Islamabad', 33.70540000, 73.03980000, '2026-09-24 23:06:46'),
(35, 'Nabila Javed', 'E-11, Islamabad', 33.72510000, 73.01140000, '2026-09-24 23:06:46'),
(36, 'Tariq Mehmood', 'G-11/1, Islamabad', 33.69120000, 73.02140000, '2026-09-24 23:06:47'),
(37, 'Abdul Rafay Khan', 'NORTH KARACHI', NULL, NULL, '2026-09-25 15:53:35');

-- --------------------------------------------------------

--
-- Table structure for table `farmer_market_stalls`
--

CREATE TABLE `farmer_market_stalls` (
  `stall_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `market_id` int(11) NOT NULL,
  `stall_number_location` varchar(50) DEFAULT NULL,
  `operating_days` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer_market_stalls`
--

INSERT INTO `farmer_market_stalls` (`stall_id`, `farmer_id`, `market_id`, `stall_number_location`, `operating_days`, `status`, `assigned_at`) VALUES
(1, 2, 1, 'Stall A-12 (North Aisle)', 'Saturday, Sunday', 'active', '2026-09-23 19:58:03'),
(2, 2, 2, 'Stall B-04 (Waterfront Row)', 'Friday, Saturday', 'active', '2026-09-23 19:58:03'),
(3, 6, 1, 'Stall C-08 (Fresh Produce Bay)', 'Saturday, Sunday', 'active', '2026-09-23 19:58:03'),
(4, 6, 3, 'Stall H-15 (Garden Entrance)', 'Wednesday, Sunday', 'active', '2026-09-23 19:58:03'),
(5, 9, 1, 'Stall 1', 'Saturday, Sunday', 'active', '2026-09-24 10:46:50'),
(6, 10, 10, 'Stall #20', 'Saturday,Sunday', 'active', '2026-09-24 23:06:47'),
(7, 10, 6, 'Stall #21', 'Friday,Saturday', 'active', '2026-09-24 23:06:47'),
(8, 11, 8, 'Stall #22', 'Saturday', 'active', '2026-09-24 23:06:47'),
(9, 11, 3, 'Stall #23', 'Sunday', 'active', '2026-09-24 23:06:47'),
(10, 12, 1, 'Stall #24', 'Saturday,Sunday', 'active', '2026-09-24 23:06:47'),
(11, 12, 5, 'Stall #25', 'Friday,Saturday', 'active', '2026-09-24 23:06:47'),
(12, 13, 4, 'Stall #26', 'Saturday', 'active', '2026-09-24 23:06:47'),
(13, 13, 12, 'Stall #27', 'Sunday', 'active', '2026-09-24 23:06:47'),
(14, 14, 2, 'Stall #28', 'Saturday,Sunday', 'active', '2026-09-24 23:06:47'),
(15, 14, 11, 'Stall #29', 'Friday,Saturday', 'active', '2026-09-24 23:06:47'),
(16, 15, 7, 'Stall #30', 'Saturday', 'active', '2026-09-24 23:06:47'),
(17, 15, 9, 'Stall #31', 'Sunday', 'active', '2026-09-24 23:06:47'),
(18, 16, 10, 'Stall #32', 'Saturday,Sunday', 'active', '2026-09-24 23:06:47'),
(19, 16, 6, 'Stall #33', 'Friday,Saturday', 'active', '2026-09-24 23:06:47'),
(20, 17, 8, 'Stall #34', 'Saturday', 'active', '2026-09-24 23:06:47'),
(21, 17, 3, 'Stall #35', 'Sunday', 'active', '2026-09-24 23:06:47'),
(22, 18, 1, 'Stall #36', 'Saturday,Sunday', 'active', '2026-09-24 23:06:47'),
(23, 18, 5, 'Stall #37', 'Friday,Saturday', 'active', '2026-09-24 23:06:47'),
(24, 19, 4, 'Stall #38', 'Saturday', 'active', '2026-09-24 23:06:47'),
(25, 19, 12, 'Stall #39', 'Sunday', 'active', '2026-09-24 23:06:47'),
(26, 20, 2, 'Stall #40', 'Saturday,Sunday', 'active', '2026-09-24 23:06:47'),
(27, 20, 11, 'Stall #41', 'Friday,Saturday', 'active', '2026-09-24 23:06:47'),
(28, 21, 7, 'Stall #42', 'Saturday', 'active', '2026-09-24 23:06:47'),
(29, 21, 9, 'Stall #43', 'Sunday', 'active', '2026-09-24 23:06:47'),
(30, 9, 10, 'Stall # 3', 'Saturday, Sunday', 'active', '2026-09-25 16:35:30'),
(31, 9, 13, 'Stall 5', 'Saturday, Sunday', 'active', '2026-09-25 16:46:42'),
(32, 18, 13, 'Stall 5', 'Thursday', 'active', '2026-09-27 10:46:22');

-- --------------------------------------------------------

--
-- Table structure for table `farmer_profiles`
--

CREATE TABLE `farmer_profiles` (
  `farmer_id` int(11) NOT NULL,
  `stall_name` varchar(120) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `business_phone` varchar(20) NOT NULL,
  `business_email` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `order_cutoff_time` time DEFAULT '18:00:00',
  `approval_status` enum('pending','approved','rejected','suspended') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer_profiles`
--

INSERT INTO `farmer_profiles` (`farmer_id`, `stall_name`, `contact_person`, `business_phone`, `business_email`, `description`, `address`, `latitude`, `longitude`, `order_cutoff_time`, `approval_status`, `created_at`, `updated_at`) VALUES
(2, 'Sunrise Organic Orchard', 'Tariq Mehmood', '03129876543', 'tariq@example.com', 'Dedicated organic grower cultivating non-GMO heirloom vegetables and fresh orchard fruits with zero synthetic pesticides.', 'Plot 88, Agri Zone Road', 24.86070000, 67.00110000, '18:00:00', 'approved', '2026-09-23 16:32:02', '2026-09-23 19:58:03'),
(6, 'Liaquat Bhai Farm', 'Muhammad Liaquat', '03400000000', 'liaquat@gmail.com', 'Family-run sustainable farm producing crisp greens, leafy herbs, organic free-range eggs, and artisanal farm honey.', 'Plot 32, Stall no 1 near UP more', 24.91800000, 67.09710000, '18:00:00', 'approved', '2026-09-23 16:59:38', '2026-09-23 19:58:03'),
(9, 'Wind vegetable farm', 'Muhamad Anees', '03444444444', 'anees@gmail.com', '', 'Farm Market Road, plot # 15', 24.91189700, 67.01319500, '18:00:00', 'approved', '2026-09-24 10:38:50', '2026-09-25 16:39:15'),
(10, 'Rafay Heritage Orchard', 'Abdul Rafay', '0300-1234567', 'rafay@farmermail.pk', 'Premium heirloom tomatoes, seasonal berries, and cold-pressed olive oil direct from our 20-acre certified organic estate.', 'Plot 14, Raiwind Road, Lahore', 31.42790000, 74.35580000, '17:00:00', 'approved', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(11, 'Zara\'s Hydroponic Garden', 'Zara Fatima', '0321-9876543', 'zara@organicpk.com', 'State-of-the-art hydroponic lettuce, cherry tomatoes, microgreens and fresh basil grown in controlled vertical farm conditions.', 'House 22, Canal Road, Lahore', 31.51040000, 74.36830000, '18:00:00', 'approved', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(12, 'Malik Pure Dairy & Creamery', 'Malik Ibrahim', '0333-5551234', 'malik@milkpk.net', 'A4 certified Holstein cows, grass-fed milk, artisan butter churned fresh every morning, farm eggs from free-range hens.', 'Bedian Road, Lahore Cantt', 31.48900000, 74.41200000, '06:00:00', 'approved', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(13, 'Nadia\'s Aromatic Herb Studio', 'Nadia Siddiqui', '0345-7778899', 'nadia@herbgarden.pk', 'Certified organic culinary herbs: Thai basil, French thyme, Moroccan mint, lemon verbena, fresh dill, and medicinal aloe vera.', 'Johar Town Block P, Lahore', 31.46510000, 74.27900000, '16:00:00', 'approved', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(14, 'Asif Citrus & Olive Grove', 'Muhammad Asif', '0311-4445566', 'asif@citrusbagh.com', 'Kinnow mandarins picked at peak brix, blood oranges, and extra-virgin cold-pressed olive oil from 600 mature trees.', 'Sahiwal bypass, District Sahiwal', 30.67060000, 73.10670000, '19:00:00', 'approved', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(15, 'Hina Apiary & Wild Honey Hive', 'Hina Qureshi', '0323-1122334', 'hina@purehoney.pk', 'Raw Sidr, Acacia, and Manuka-equivalent Pakistani wildflower honey direct from 80 active hives. Ethically harvested, unfiltered, unpasteurized.', 'Kotli Loharan, Sialkot', 32.49450000, 74.52290000, '18:00:00', 'approved', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(16, 'Kamran\'s Whole Grain Mill', 'Kamran Raza', '0312-6667778', 'kamran@grainfield.pk', 'Stone-ground whole wheat flour, heirloom basmati rice, ancient grain amaranth, and multi-seed organic granola milled on traditional chakki.', 'Faisalabad GT Road, District Fsd.', 31.41810000, 73.07930000, '16:00:00', 'approved', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(17, 'Ayesha\'s Artisan Jar Kitchen', 'Ayesha Mirza', '0334-9991122', 'ayesha@jarstore.pk', 'Hand-packed seasonal achaar, fermented kanji, mango murabba, date syrup, chili-lime relish and organic apple cider vinegar batches.', 'Gulberg II, Main Market, Lahore', 31.51290000, 74.34830000, '17:00:00', 'approved', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(18, 'Junaid Pure Vegetable Plots', 'Junaid Anwar', '0302-3334455', 'junaid@freshveg.pk', 'Seasonal spinach, methi, bathua, radish, turnips, bitter gourd, tinda, and arvi cultivated by a 3rd generation farming family.', 'Sheikhupura Ferozewala Rd, Lahore', 31.71670000, 74.00000000, '18:00:00', 'approved', '2026-09-24 23:06:45', '2026-09-27 10:48:51'),
(19, 'Samira Free-Range Poultry Farm', 'Samira Malik', '0316-8889900', 'samira@farmpoultrys.pk', 'Desi murgi raised on natural grains, organic eggs from free-roaming hens, and fresh farm-slaughtered poultry on pre-order.', 'Sundar Industrial Estate, Lahore', 31.39060000, 74.25310000, '14:00:00', 'approved', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(20, 'Hassan\'s Mushroom Cultivation', 'Hassan Nawaz', '0303-2223344', 'hassan@mycelium.pk', 'Oyster, Shiitake, Portobello and Lion\'s Mane mushrooms cultivated on organic wheat-straw substrate, harvested same morning as market day.', 'Raiwind Industrial Park, Lahore', 31.40760000, 74.34220000, '17:00:00', 'approved', '2026-09-24 23:06:46', '2026-09-25 12:24:59'),
(21, 'Imran\'s Superfood Seed Studio', 'Imran Butt', '0307-5556677', 'imran@superseeds.pk', 'Chia, flax, hemp seeds, moringa powder, dried mulberries, raw cacao nibs and Himalayan pink salt direct from source farmers across Pakistan.', 'Valencia Housing, Lahore', 31.43700000, 74.26200000, '18:00:00', 'approved', '2026-09-24 23:06:46', '2026-09-27 11:00:30');

-- --------------------------------------------------------

--
-- Table structure for table `farmer_reviews`
--

CREATE TABLE `farmer_reviews` (
  `review_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review_comment` text DEFAULT NULL,
  `farmer_response` text DEFAULT NULL,
  `response_date` datetime DEFAULT NULL,
  `is_moderated` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer_reviews`
--

INSERT INTO `farmer_reviews` (`review_id`, `order_id`, `customer_id`, `farmer_id`, `rating`, `review_comment`, `farmer_response`, `response_date`, `is_moderated`, `is_featured`, `created_at`) VALUES
(1, 1, 1, 2, 5, 'The organic heirloom tomatoes were exceptionally fresh and sweet! Stall was well-organized for fast pickup.', 'Thank you Sara! Looking forward to seeing you again this weekend.', '2026-09-22 19:58:36', 1, 1, '2026-09-22 19:58:36'),
(2, 4, 8, 9, 4, 'the location was not given properly', 'we will solve it', '2026-09-24 11:08:04', 1, 0, '2026-09-24 11:07:20'),
(3, 11, 27, 6, 5, 'The mushroom varieties were incredible. Shiitake fried in butter was restaurant-quality at home. Hassan ji is super knowledgeable about cultivation methods.', 'JazakAllah Khair for your kind words! It truly motivates us to keep farming with love and dedication. See you at the market next week inshAllah! 🌿', '2026-09-24 23:06:48', 1, 1, '2026-09-24 23:06:48'),
(4, 12, 22, 2, 3, 'Decent produce but the stall ran out of basil before I could collect my order. Understandable on busy days. Will try to arrive earlier next time.', 'We are truly blessed to have customers like you who appreciate authentic organic produce. Your feedback keeps us going through early mornings and long days! 🌱', '2026-09-24 23:06:48', 1, 0, '2026-09-24 23:06:48'),
(6, 24, 35, 18, 5, 'Microgreens were absolutely stunning — 40× nutrients sounds like marketing speak but you can literally TASTE the difference. Incredible!', 'JazakAllah for your wonderful review! This motivates us through every 4 AM market morning. See you next Saturday inshAllah! 🌿', '2026-09-24 23:08:11', 1, 1, '2026-09-24 23:08:11'),
(7, 25, 33, 14, 5, 'The Sidr honey is therapeutic. My entire family uses it now instead of processed sugar. Cannot recommend highly enough!', NULL, NULL, 1, 0, '2026-09-24 23:08:11'),
(8, 28, 24, 21, 5, 'I\'ve converted my entire family to MarketLink after this experience. The taste difference from supermarket produce is genuinely shocking.', 'JazakAllah for your wonderful review! This motivates us through every 4 AM market morning. See you next Saturday inshAllah! 🌿', '2026-09-24 23:08:11', 1, 1, '2026-09-24 23:08:11'),
(9, 29, 30, 2, 5, 'Best mushrooms I\'ve ever had in my life. Sautéed Lion\'s Mane in butter and fresh thyme — it tasted like scallops! Mind-blowing.', NULL, NULL, 1, 0, '2026-09-24 23:08:11'),
(10, 30, 24, 2, 4, 'Very professional and genuine farmer. Communication was prompt and the order was accurately packed. Will definitely reorder.', NULL, NULL, 1, 0, '2026-09-24 23:08:11'),
(11, 31, 8, 20, 5, 'The dairy products are exceptional — paneer was soft, white and absolutely fragrant. Biryani turned out restaurant-quality. Highly recommend!', NULL, NULL, 1, 0, '2026-09-24 23:08:11'),
(12, 34, 36, 10, 4, 'Very professional and genuine farmer. Communication was prompt and the order was accurately packed. Will definitely reorder.', 'Thank you so much! Your kind words are shared with the whole farm team. We\'re blessed to have customers who truly appreciate honest farming 💚', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(13, 35, 3, 14, 3, 'Product quality was good but arrived 15 minutes late to their pickup window. The farmer was apologetic so I\'ll give them another chance.', NULL, NULL, 1, 0, '2026-09-24 23:08:11'),
(14, 37, 26, 19, 3, 'Product quality was good but arrived 15 minutes late to their pickup window. The farmer was apologetic so I\'ll give them another chance.', NULL, NULL, 1, 0, '2026-09-24 23:08:11'),
(15, 40, 27, 21, 5, 'The dairy products are exceptional — paneer was soft, white and absolutely fragrant. Biryani turned out restaurant-quality. Highly recommend!', 'JazakAllah for your wonderful review! This motivates us through every 4 AM market morning. See you next Saturday inshAllah! 🌿', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(16, 41, 23, 14, 5, 'The Sidr honey is therapeutic. My entire family uses it now instead of processed sugar. Cannot recommend highly enough!', 'Thank you so much! Your kind words are shared with the whole farm team. We\'re blessed to have customers who truly appreciate honest farming 💚', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(17, 44, 23, 21, 5, 'Extraordinary quality! The produce was so fresh it still had morning dew. A true craftsman of organic farming.', 'Thank you so much! Your kind words are shared with the whole farm team. We\'re blessed to have customers who truly appreciate honest farming 💚', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(18, 45, 5, 13, 5, 'Best mushrooms I\'ve ever had in my life. Sautéed Lion\'s Mane in butter and fresh thyme — it tasted like scallops! Mind-blowing.', 'JazakAllah for your wonderful review! This motivates us through every 4 AM market morning. See you next Saturday inshAllah! 🌿', '2026-09-24 23:08:11', 1, 1, '2026-09-24 23:08:11'),
(19, 46, 34, 11, 5, 'The Sidr honey is therapeutic. My entire family uses it now instead of processed sugar. Cannot recommend highly enough!', NULL, NULL, 1, 0, '2026-09-24 23:08:11'),
(20, 51, 28, 20, 4, 'Excellent produce, fast response in chat. Would give 5 stars if I didn\'t have to wait 10 minutes at the stall — but understandable on busy mornings!', 'MashaAllah, your review made our day! We put heart and soul into every harvest. Come visit us at the market anytime 🌸', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(21, 53, 29, 15, 5, 'Every single item matched the listing photo perfectly. This is the gold standard for how a farmers market should operate.', NULL, NULL, 1, 0, '2026-09-24 23:08:11'),
(22, 55, 30, 15, 3, 'Product quality was good but arrived 15 minutes late to their pickup window. The farmer was apologetic so I\'ll give them another chance.', 'Thank you so much! Your kind words are shared with the whole farm team. We\'re blessed to have customers who truly appreciate honest farming 💚', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(23, 56, 25, 15, 5, 'The Sidr honey is therapeutic. My entire family uses it now instead of processed sugar. Cannot recommend highly enough!', 'MashaAllah, your review made our day! We put heart and soul into every harvest. Come visit us at the market anytime 🌸', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(24, 61, 23, 19, 5, 'Best mushrooms I\'ve ever had in my life. Sautéed Lion\'s Mane in butter and fresh thyme — it tasted like scallops! Mind-blowing.', 'Alhamdulillah! So happy you enjoyed. Please bring friends — together we grow a stronger local food community! 🌱', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(25, 64, 27, 13, 4, 'Very professional and genuine farmer. Communication was prompt and the order was accurately packed. Will definitely reorder.', 'MashaAllah, your review made our day! We put heart and soul into every harvest. Come visit us at the market anytime 🌸', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(26, 67, 25, 12, 5, 'The dairy products are exceptional — paneer was soft, white and absolutely fragrant. Biryani turned out restaurant-quality. Highly recommend!', 'Alhamdulillah! So happy you enjoyed. Please bring friends — together we grow a stronger local food community! 🌱', '2026-09-24 23:08:11', 1, 1, '2026-09-24 23:08:11'),
(27, 68, 27, 17, 5, 'Every single item matched the listing photo perfectly. This is the gold standard for how a farmers market should operate.', 'Alhamdulillah! So happy you enjoyed. Please bring friends — together we grow a stronger local food community! 🌱', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(28, 70, 30, 13, 5, 'Best mushrooms I\'ve ever had in my life. Sautéed Lion\'s Mane in butter and fresh thyme — it tasted like scallops! Mind-blowing.', 'JazakAllah for your wonderful review! This motivates us through every 4 AM market morning. See you next Saturday inshAllah! 🌿', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(29, 72, 31, 14, 5, 'Extraordinary quality! The produce was so fresh it still had morning dew. A true craftsman of organic farming.', 'MashaAllah, your review made our day! We put heart and soul into every harvest. Come visit us at the market anytime 🌸', '2026-09-24 23:08:11', 1, 0, '2026-09-24 23:08:11'),
(30, 105, 37, 18, 5, 'everthing was fine', 'thanks sir', '2026-09-27 10:55:17', 1, 0, '2026-09-27 10:53:56');

-- --------------------------------------------------------

--
-- Table structure for table `favorite_farmers`
--

CREATE TABLE `favorite_farmers` (
  `favorite_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorite_farmers`
--

INSERT INTO `favorite_farmers` (`favorite_id`, `customer_id`, `farmer_id`, `created_at`) VALUES
(1, 1, 2, '2026-09-23 19:58:36'),
(2, 1, 10, '2026-09-24 23:06:48'),
(3, 1, 15, '2026-09-24 23:06:48'),
(4, 1, 16, '2026-09-24 23:06:48'),
(5, 1, 9, '2026-09-24 23:06:48'),
(6, 3, 14, '2026-09-24 23:06:48'),
(7, 5, 11, '2026-09-24 23:06:48'),
(8, 5, 18, '2026-09-24 23:06:48'),
(9, 5, 12, '2026-09-24 23:06:48'),
(10, 7, 18, '2026-09-24 23:06:48'),
(11, 7, 19, '2026-09-24 23:06:48'),
(12, 7, 11, '2026-09-24 23:06:48'),
(13, 8, 2, '2026-09-24 23:06:48'),
(14, 8, 16, '2026-09-24 23:06:48'),
(15, 8, 15, '2026-09-24 23:06:48'),
(16, 8, 6, '2026-09-24 23:06:48'),
(17, 22, 15, '2026-09-24 23:06:48'),
(18, 22, 6, '2026-09-24 23:06:48'),
(19, 23, 21, '2026-09-24 23:06:48'),
(20, 23, 2, '2026-09-24 23:06:48'),
(21, 23, 18, '2026-09-24 23:06:48'),
(22, 23, 14, '2026-09-24 23:06:48'),
(23, 24, 15, '2026-09-24 23:06:48'),
(24, 24, 11, '2026-09-24 23:06:48'),
(25, 24, 9, '2026-09-24 23:06:48'),
(26, 24, 2, '2026-09-24 23:06:48'),
(27, 25, 14, '2026-09-24 23:06:48'),
(28, 26, 12, '2026-09-24 23:06:48'),
(29, 26, 21, '2026-09-24 23:06:48');

-- --------------------------------------------------------

--
-- Table structure for table `favorite_products`
--

CREATE TABLE `favorite_products` (
  `favorite_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorite_products`
--

INSERT INTO `favorite_products` (`favorite_id`, `customer_id`, `product_id`, `created_at`) VALUES
(1, 1, 1, '2026-09-23 19:58:36'),
(4, 1, 18, '2026-09-24 23:06:48'),
(5, 1, 35, '2026-09-24 23:06:48'),
(6, 1, 49, '2026-09-24 23:06:48'),
(7, 3, 30, '2026-09-24 23:06:48'),
(8, 3, 62, '2026-09-24 23:06:48'),
(9, 3, 55, '2026-09-24 23:06:48'),
(10, 3, 52, '2026-09-24 23:06:48'),
(11, 5, 15, '2026-09-24 23:06:48'),
(12, 5, 19, '2026-09-24 23:06:48'),
(13, 5, 56, '2026-09-24 23:06:48'),
(14, 5, 3, '2026-09-24 23:06:48'),
(15, 7, 20, '2026-09-24 23:06:48'),
(16, 7, 7, '2026-09-24 23:06:48'),
(17, 7, 39, '2026-09-24 23:06:48'),
(18, 7, 44, '2026-09-24 23:06:48'),
(19, 8, 40, '2026-09-24 23:06:48'),
(20, 8, 51, '2026-09-24 23:06:48'),
(21, 8, 42, '2026-09-24 23:06:48'),
(22, 8, 19, '2026-09-24 23:06:48'),
(23, 22, 64, '2026-09-24 23:06:48'),
(24, 22, 54, '2026-09-24 23:06:48'),
(25, 23, 48, '2026-09-24 23:06:48'),
(26, 23, 11, '2026-09-24 23:06:48'),
(27, 24, 44, '2026-09-24 23:06:48'),
(28, 24, 12, '2026-09-24 23:06:48'),
(29, 25, 29, '2026-09-24 23:06:48'),
(30, 25, 58, '2026-09-24 23:06:48'),
(31, 25, 23, '2026-09-24 23:06:48'),
(32, 25, 30, '2026-09-24 23:06:48'),
(33, 26, 17, '2026-09-24 23:06:48'),
(34, 26, 58, '2026-09-24 23:06:48'),
(35, 26, 55, '2026-09-24 23:06:48'),
(37, 37, 64, '2026-09-27 10:28:42');

-- --------------------------------------------------------

--
-- Table structure for table `markets`
--

CREATE TABLE `markets` (
  `market_id` int(11) NOT NULL,
  `market_name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(50) DEFAULT 'Local City',
  `state` varchar(50) DEFAULT 'State',
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `operating_days` varchar(100) NOT NULL,
  `operating_hours` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `markets`
--

INSERT INTO `markets` (`market_id`, `market_name`, `address`, `city`, `state`, `postal_code`, `latitude`, `longitude`, `operating_days`, `operating_hours`, `status`, `created_by`, `created_at`) VALUES
(1, 'Downtown Green Plaza Farmers Market', 'Plot 4-A, Civic Center Promenade, Central District', 'Metropolis', 'Sindh', '74200', 24.86096600, 67.01042700, 'Saturday, Sunday', '07:00 AM - 01:00 PM', 'inactive', NULL, '2026-09-23 19:58:03'),
(2, 'Riverside Organic Community Market', 'Pier 12, Waterfront Boardwalk, River Sector', 'Metropolis', 'Sindh', '74800', 24.83245000, 67.03450000, 'Friday, Saturday', '08:00 AM - 02:00 PM', 'active', NULL, '2026-09-23 19:58:03'),
(3, 'Hillside Heritage Open Market', 'Gate 3, Botanical Gardens Esplanade, North Heights', 'Metropolis', 'Sindh', '75300', 24.93510000, 67.09820000, 'Wednesday, Sunday', '06:30 AM - 12:30 PM', 'active', NULL, '2026-09-23 19:58:03'),
(4, 'Suburban Eco Farm Fair', 'Eco Square, Main Boulevard, Green Valley', 'Metropolis', 'Sindh', '75050', 24.90560000, 67.12340000, 'Tuesday, Thursday, Saturday', '07:30 AM - 01:30 PM', 'active', NULL, '2026-09-23 19:58:03'),
(5, 'Gulberg Organic Saturday Market', 'Main Boulevard, Gulberg III, Lahore', 'Lahore', 'State', NULL, 31.51290000, 74.34830000, 'Saturday,Sunday', '07:00 AM – 1:00 PM', 'active', 4, '2026-09-24 23:06:45'),
(6, 'DHA Phase 6 Weekly Green Bazaar', 'CCA Block, DHA Phase 6, Lahore', 'Lahore', 'State', NULL, 31.47270000, 74.40320000, 'Friday,Saturday', '06:30 AM – 12:30 PM', 'active', 4, '2026-09-24 23:06:45'),
(7, 'Model Town Sunrise Farmers Souk', 'Model Town Park Main Gate, Lahore', 'Lahore', 'State', NULL, 31.48940000, 74.31240000, 'Saturday', '05:30 AM – 11:00 AM', 'active', 4, '2026-09-24 23:06:45'),
(8, 'Johar Town Community Harvest Fair', 'Johar Town Park H Block, Lahore', 'Lahore', 'State', NULL, 31.46510000, 74.27900000, 'Sunday', '07:00 AM – 2:00 PM', 'active', 4, '2026-09-24 23:06:45'),
(9, 'Bahria Town Organic Hub', 'Sector C, Bahria Town, Lahore', 'Lahore', 'State', NULL, 31.36310000, 74.17820000, 'Saturday,Sunday', '06:00 AM – 1:00 PM', 'active', 4, '2026-09-24 23:06:45'),
(10, 'F-8 Islamabad Green Farmers Market', 'F-8 Markaz, Islamabad', 'Islamabad', 'State', NULL, 33.71690000, 73.04520000, 'Saturday', '07:00 AM – 1:00 PM', 'active', 4, '2026-09-24 23:06:45'),
(11, 'G-11 Margalla Fresh Bazaar', 'G-11 Markaz, Islamabad', 'Islamabad', 'State', NULL, 33.69120000, 73.02140000, 'Sunday', '07:30 AM – 1:30 PM', 'active', 4, '2026-09-24 23:06:45'),
(12, 'Karachi Defence Organic Market', 'Phase 2, DHA Karachi', 'Karachi', 'State', NULL, 24.80220000, 67.06390000, 'Saturday,Sunday', '06:00 AM – 11:30 AM', 'active', 4, '2026-09-24 23:06:45'),
(13, 'Defence Market', 'DHA PHASE 7', 'Islamabd', 'DHA', '75850', 37.09024000, 95.71289000, 'Wednesday,Thursday,Friday', '8:00 AM - 2:00 PM', 'active', 4, '2026-09-25 16:43:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('order_status','restock_alert','announcement','system') DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `notification_type`, `is_read`, `created_at`) VALUES
(1, 1, 'Harvest Ready for Pickup! 🥬', 'Your pre-order #ML-8592E580 has been harvested, sorted, and is ready for pickup at Sunrise Organic Orchard (Stall A-12).', 'order_status', 1, '2026-09-23 19:58:36'),
(2, 1, 'Weekend Market Opening Announcement', 'Downtown Green Plaza Farmers Market will open early this Saturday at 7:00 AM with 15+ verified growers.', 'announcement', 1, '2026-09-23 19:58:36'),
(3, 8, 'Pre-Order #ML-ECCEF6AC Placed Successfully', 'Your harvest pre-order of Rs. 280.00 has been submitted to the farmer for pickup on 2026-09-24. Payment is settled upon pickup.', 'order_status', 1, '2026-09-24 10:49:19'),
(4, 8, 'Pre-Order #ML-C614961E Placed Successfully', 'Your harvest pre-order of Rs. 650.00 has been submitted to the farmer for pickup on 2026-09-24. Payment is settled upon pickup.', 'order_status', 1, '2026-09-24 10:49:19'),
(5, 8, 'Pre-Order #ML-9B5633C6 Placed Successfully', 'Your harvest pre-order of Rs. 280.00 has been submitted to the farmer for pickup on 2026-09-24. Payment is settled upon pickup.', 'order_status', 1, '2026-09-24 10:49:39'),
(6, 8, 'Order #ML-9B5633C6 Cancelled', 'Your pre-order has been cancelled. Reason: Placed order by mistake', 'order_status', 1, '2026-09-24 10:49:50'),
(7, 8, 'Order Accepted! 🥕', 'Your pre-order #ML-C614961E has been accepted by Wind vegetable farm. It will be packed fresh for market pickup.', 'order_status', 1, '2026-09-24 11:01:56'),
(8, 8, 'Ready for Pickup! 🥬', 'Your pre-order #ML-C614961E is sorted and ready for collection at Wind vegetable farm. See you soon!', 'order_status', 1, '2026-09-24 11:02:00'),
(9, 8, 'Order Completed! 🎉', 'Your pre-order #ML-C614961E with Wind vegetable farm has been fulfilled. Thank you for supporting local growers!', 'order_status', 1, '2026-09-24 11:05:40'),
(10, 8, 'Response to your review from Wind vegetable farm 💬', 'Farmer Wind vegetable farm has responded to your feedback: \"we will solve it\"', 'system', 1, '2026-09-24 11:08:04'),
(11, 9, '🎉 Farmer Stall Approved!', 'Congratulations! Your farm profile has been officially verified and approved. You can now post weekly inventories, assign stalls, and start receiving pre-orders.', 'system', 0, '2026-09-24 12:02:58'),
(12, 9, 'Account Notice: Stall Suspended', 'Your farmer stall has been temporarily suspended by platform administration. Reason: he seems to be a fraud', 'system', 0, '2026-09-24 12:29:22'),
(13, 8, 'Pre-Order #ML-6C807157 Placed Successfully', 'Your harvest pre-order of Rs. 760.00 has been submitted to the farmer for pickup on 2026-09-24. Payment is settled upon pickup.', 'order_status', 1, '2026-09-24 13:14:17'),
(14, 6, 'New Message from ahzam', 'Order #ML-6C807157: hui', 'system', 0, '2026-09-24 13:15:05'),
(15, 6, 'New Message from ahzam', 'Order #ML-6C807157: Sent a photo', 'system', 0, '2026-09-24 13:15:14'),
(16, 9, '🎉 Farmer Stall Approved!', 'Congratulations! Your farm profile has been officially verified and approved. You can now post weekly inventories, assign stalls, and start receiving pre-orders.', 'system', 0, '2026-09-24 13:19:27'),
(17, 8, 'Order Accepted! 🥕', 'Your pre-order #ML-6C807157 has been accepted by Liaquat Bhai Farm. It will be packed fresh for market pickup.', 'order_status', 1, '2026-09-24 13:21:07'),
(18, 8, 'Order Accepted! 🥕', 'Your pre-order #ML-6C807157 has been accepted by Liaquat Bhai Farm. It will be packed fresh for market pickup.', 'order_status', 1, '2026-09-24 13:21:10'),
(19, 8, 'Ready for Pickup! 🥬', 'Your pre-order #ML-6C807157 is sorted and ready for collection at Liaquat Bhai Farm. See you soon!', 'order_status', 1, '2026-09-24 13:21:34'),
(20, 8, 'Order Completed! 🎉', 'Your pre-order #ML-6C807157 with Liaquat Bhai Farm has been fulfilled. Thank you for supporting local growers!', 'order_status', 1, '2026-09-24 13:21:40'),
(21, 2, 'New Message from ahzam', 'Order #ML-ECCEF6AC: hi', 'system', 0, '2026-09-24 13:45:58'),
(22, 2, 'New Message from ahzam', 'Order #ML-ECCEF6AC: ye hai', 'system', 0, '2026-09-24 13:46:06'),
(23, 4, '📢 Market Closed', 'Market may be closed on sunday', 'announcement', 0, '2026-09-24 13:47:33'),
(24, 2, '📢 Market Closed', 'Market may be closed on sunday', 'announcement', 0, '2026-09-24 13:47:33'),
(25, 6, '📢 Market Closed', 'Market may be closed on sunday', 'announcement', 0, '2026-09-24 13:47:33'),
(26, 9, '📢 Market Closed', 'Market may be closed on sunday', 'announcement', 0, '2026-09-24 13:47:33'),
(27, 1, '📢 Market Closed', 'Market may be closed on sunday', 'announcement', 0, '2026-09-24 13:47:33'),
(28, 3, '📢 Market Closed', 'Market may be closed on sunday', 'announcement', 0, '2026-09-24 13:47:34'),
(29, 5, '📢 Market Closed', 'Market may be closed on sunday', 'announcement', 0, '2026-09-24 13:47:34'),
(30, 7, '📢 Market Closed', 'Market may be closed on sunday', 'announcement', 0, '2026-09-24 13:47:34'),
(31, 8, '📢 Market Closed', 'Market may be closed on sunday', 'announcement', 1, '2026-09-24 13:47:34'),
(32, 2, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(33, 2, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(34, 2, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(35, 6, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 1, '2026-09-24 23:06:48'),
(36, 6, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 1, '2026-09-24 23:06:48'),
(37, 6, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(38, 6, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(39, 6, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 1, '2026-09-24 23:06:48'),
(40, 9, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(41, 9, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(42, 9, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 0, '2026-09-24 23:06:48'),
(43, 10, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 0, '2026-09-24 23:06:48'),
(44, 10, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 0, '2026-09-24 23:06:48'),
(45, 10, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(46, 10, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 0, '2026-09-24 23:06:48'),
(47, 10, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(48, 11, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(49, 11, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 0, '2026-09-24 23:06:48'),
(50, 11, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 0, '2026-09-24 23:06:48'),
(51, 12, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(52, 12, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 1, '2026-09-24 23:06:48'),
(53, 13, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 1, '2026-09-24 23:06:48'),
(54, 13, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(55, 13, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 0, '2026-09-24 23:06:48'),
(56, 13, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(57, 14, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 0, '2026-09-24 23:06:48'),
(58, 14, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 0, '2026-09-24 23:06:48'),
(59, 14, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 0, '2026-09-24 23:06:48'),
(60, 15, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(61, 15, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(62, 15, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(63, 15, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 1, '2026-09-24 23:06:48'),
(64, 16, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 0, '2026-09-24 23:06:48'),
(65, 16, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 0, '2026-09-24 23:06:48'),
(66, 16, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 0, '2026-09-24 23:06:48'),
(67, 16, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 1, '2026-09-24 23:06:48'),
(68, 17, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(69, 17, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 0, '2026-09-24 23:06:48'),
(70, 17, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 1, '2026-09-24 23:06:48'),
(71, 18, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(72, 18, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 1, '2026-09-24 23:06:48'),
(73, 18, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 0, '2026-09-24 23:06:48'),
(74, 19, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 1, '2026-09-24 23:06:48'),
(75, 19, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(76, 20, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 1, '2026-09-24 23:06:48'),
(77, 20, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 0, '2026-09-24 23:06:48'),
(78, 20, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 1, '2026-09-24 23:06:48'),
(79, 20, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(80, 21, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(81, 21, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 0, '2026-09-24 23:06:48'),
(82, 21, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(83, 1, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 0, '2026-09-24 23:06:48'),
(84, 1, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(85, 1, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 1, '2026-09-24 23:06:48'),
(86, 1, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(87, 3, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 0, '2026-09-24 23:06:48'),
(88, 3, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(89, 3, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 1, '2026-09-24 23:06:48'),
(90, 3, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(91, 5, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(92, 5, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 1, '2026-09-24 23:06:48'),
(93, 5, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 0, '2026-09-24 23:06:48'),
(94, 5, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(95, 5, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 0, '2026-09-24 23:06:48'),
(96, 7, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 0, '2026-09-24 23:06:48'),
(97, 7, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 1, '2026-09-24 23:06:48'),
(98, 7, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(99, 7, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(100, 7, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 0, '2026-09-24 23:06:48'),
(101, 8, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(102, 8, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(103, 8, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 1, '2026-09-24 23:06:48'),
(104, 8, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 1, '2026-09-24 23:06:48'),
(105, 8, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 1, '2026-09-24 23:06:48'),
(106, 22, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(107, 22, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(108, 23, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 0, '2026-09-24 23:06:48'),
(109, 23, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 1, '2026-09-24 23:06:48'),
(110, 23, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(111, 24, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 1, '2026-09-24 23:06:48'),
(112, 24, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 0, '2026-09-24 23:06:48'),
(113, 24, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(114, 24, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(115, 25, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 0, '2026-09-24 23:06:48'),
(116, 25, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 0, '2026-09-24 23:06:48'),
(117, 25, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(118, 26, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 1, '2026-09-24 23:06:48'),
(119, 26, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(120, 27, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(121, 27, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 0, '2026-09-24 23:06:48'),
(122, 27, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 0, '2026-09-24 23:06:48'),
(123, 27, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 0, '2026-09-24 23:06:48'),
(124, 27, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(125, 28, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(126, 28, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 1, '2026-09-24 23:06:48'),
(127, 29, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(128, 29, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(129, 30, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(130, 30, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(131, 30, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 1, '2026-09-24 23:06:48'),
(132, 30, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 0, '2026-09-24 23:06:48'),
(133, 30, 'Seasonal Arrival 🍊', 'Kinnow mandarins are now live! Order before Friday 6 PM cutoff to secure your allocation.', 'restock_alert', 0, '2026-09-24 23:06:48'),
(134, 31, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 1, '2026-09-24 23:06:48'),
(135, 31, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 1, '2026-09-24 23:06:48'),
(136, 32, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(137, 32, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 0, '2026-09-24 23:06:48'),
(138, 32, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(139, 32, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(140, 32, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 0, '2026-09-24 23:06:48'),
(141, 33, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 1, '2026-09-24 23:06:48'),
(142, 33, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 1, '2026-09-24 23:06:48'),
(143, 33, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(144, 33, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(145, 33, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 0, '2026-09-24 23:06:48'),
(146, 34, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 0, '2026-09-24 23:06:48'),
(147, 34, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 1, '2026-09-24 23:06:48'),
(148, 34, 'Welcome to MarketLink! 🌱', 'Discover fresh, certified local produce from our community of vetted farmers. Your first order awaits!', 'system', 0, '2026-09-24 23:06:48'),
(149, 34, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 0, '2026-09-24 23:06:48'),
(150, 34, 'Order Completed 🌿', 'Pickup confirmed! Thank you for supporting local farming. Please leave a review to help the community.', 'order_status', 1, '2026-09-24 23:06:48'),
(151, 35, 'New Stock Alert 🛒', 'Your favourite stall has restocked fresh cherry tomatoes and baby spinach for this Saturday!', 'restock_alert', 1, '2026-09-24 23:06:48'),
(152, 35, 'Order Confirmed ✅', 'Your pre-order has been accepted by the farmer. Your fresh produce will be ready for pickup!', 'order_status', 0, '2026-09-24 23:06:48'),
(153, 35, 'MarketLink Weekly Update 📢', 'New organic mushroom varieties have been listed by Hassan\'s Cultivation this week. Check it out!', 'announcement', 1, '2026-09-24 23:06:48'),
(154, 36, 'Platform Notice 📋', 'We\'ve updated our stall verification process. All farmers now display PFA certification badges.', 'announcement', 1, '2026-09-24 23:06:48'),
(155, 36, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 1, '2026-09-24 23:06:48'),
(156, 36, 'Ready for Pickup 🎉', 'Your order is packed and waiting at the stall. Please collect at your scheduled slot time.', 'order_status', 0, '2026-09-24 23:06:48'),
(157, 30, 'Order Completed! 🎉', 'Your pre-order #ML-2026-05077 with Wind vegetable farm has been fulfilled. Thank you for supporting local growers!', 'order_status', 0, '2026-09-25 00:36:11'),
(158, 8, 'Order Completed! 🎉', 'Your pre-order #ML-2026-05049 with Wind vegetable farm has been fulfilled. Thank you for supporting local growers!', 'order_status', 1, '2026-09-25 00:36:15'),
(159, 13, 'New Message from ahzam', 'Order #ML-2026-05058: hi', 'system', 0, '2026-09-25 11:19:37'),
(160, 8, 'Pre-Order #ML-CAED0B4C Placed Successfully', 'Your harvest pre-order of Rs. 732.00 has been submitted to the farmer for pickup on 2026-09-26. Payment is settled upon pickup.', 'order_status', 1, '2026-09-25 11:59:39'),
(161, 8, 'Order #ML-CAED0B4C Cancelled', 'Your pre-order has been cancelled. Reason: Schedule conflict on market day', 'order_status', 1, '2026-09-25 12:00:12'),
(162, 27, 'New Message from Hassan\'s Mushroom Cultivation', 'Order #ML-2026-05083: Sent a photo', 'system', 0, '2026-09-25 12:36:08'),
(163, 8, 'Pre-Order #ML-72166E3C Placed Successfully', 'Your harvest pre-order of Rs. 280.00 has been submitted to the farmer for pickup on 2026-09-26. Payment is settled upon pickup.', 'order_status', 1, '2026-09-25 12:57:45'),
(164, 8, 'Pre-Order #ML-1EB5CE0F Placed Successfully', 'Your harvest pre-order of Rs. 522.00 has been submitted to the farmer for pickup on 2026-09-26. Payment is settled upon pickup.', 'order_status', 1, '2026-09-25 12:59:48'),
(165, 8, 'Pre-Order #ML-1EB5CE0F Updated', 'Your item quantities and pickup reservation have been updated successfully.', 'order_status', 1, '2026-09-25 13:00:49'),
(166, 21, 'Pre-Order #ML-1EB5CE0F Updated', 'The customer updated item quantities or their pickup reservation before confirmation.', 'order_status', 0, '2026-09-25 13:00:49'),
(167, 8, 'Pre-Order #ML-1EB5CE0F Updated', 'Your item quantities and pickup reservation have been updated successfully.', 'order_status', 1, '2026-09-25 13:00:58'),
(168, 21, 'Pre-Order #ML-1EB5CE0F Updated', 'The customer updated item quantities or their pickup reservation before confirmation.', 'order_status', 0, '2026-09-25 13:00:58'),
(169, 22, 'Pre-Order #ML-2F848FF0 Placed Successfully', 'Your harvest pre-order of Rs. 223.00 has been submitted to the farmer for pickup on 2026-09-26. Payment is settled upon pickup.', 'order_status', 1, '2026-09-25 13:03:29'),
(170, 22, 'Pre-Order #ML-2F848FF0 Updated', 'Your item quantities and pickup reservation have been updated successfully.', 'order_status', 1, '2026-09-25 13:03:41'),
(171, 21, 'Pre-Order #ML-2F848FF0 Updated', 'The customer updated item quantities or their pickup reservation before confirmation.', 'order_status', 0, '2026-09-25 13:03:41'),
(172, 22, 'Order Accepted! 🥕', 'Your pre-order #ML-2F848FF0 has been accepted by Imran\'s Superfood Seed Studio. It will be packed fresh for market pickup.', 'order_status', 1, '2026-09-25 13:04:45'),
(173, 22, 'Pre-Order #ML-0CEA6434 Placed Successfully', 'Your harvest pre-order of Rs. 420.00 has been submitted to the farmer for pickup on 2026-09-26. Payment is settled upon pickup.', 'order_status', 1, '2026-09-25 13:09:02'),
(174, 37, 'Pre-Order #ML-4FDB1836 Placed Successfully', 'Your harvest pre-order of Rs. 420.00 has been submitted to the farmer for pickup on 2026-09-26. Payment is settled upon pickup.', 'order_status', 1, '2026-09-25 16:04:01'),
(175, 4, 'Pre-Order #ML-87CECE98 Placed Successfully', 'Your harvest pre-order of Rs. 155.00 has been submitted to the farmer for pickup on 2026-09-26. Payment is settled upon pickup.', 'order_status', 0, '2026-09-26 12:02:58'),
(176, 21, 'New Message from Imran\'s Superfood Seed Studio', 'Order #ML-87CECE98: hi', 'system', 0, '2026-09-26 12:04:50'),
(177, 9, 'Product Listing Removed', 'Your listing \'Tomato\' was removed by platform administration for: Violates platform product quality guidelines', 'system', 0, '2026-09-26 20:07:12'),
(178, 37, 'Pre-Order #ML-A0038835 Placed Successfully', 'Your harvest pre-order of Rs. 210.00 has been submitted to the farmer for pickup on 2026-09-30. Payment is settled upon pickup.', 'order_status', 1, '2026-09-27 10:38:46'),
(179, 37, 'Pre-Order #ML-248E0273 Placed Successfully', 'Your harvest pre-order of Rs. 137.00 has been submitted to the farmer for pickup on 2026-09-30. Payment is settled upon pickup.', 'order_status', 1, '2026-09-27 10:38:46'),
(180, 37, 'Pre-Order #ML-721BD11E Placed Successfully', 'Your harvest pre-order of Rs. 396.00 has been submitted to the farmer for pickup on 2026-09-30. Payment is settled upon pickup.', 'order_status', 1, '2026-09-27 10:38:46'),
(181, 37, 'Pre-Order #ML-DE621115 Placed Successfully', 'Your harvest pre-order of Rs. 432.00 has been submitted to the farmer for pickup on 2026-09-30. Payment is settled upon pickup.', 'order_status', 1, '2026-09-27 10:38:46'),
(182, 37, 'Pre-Order #ML-DE621115 Updated', 'Your item quantities and pickup reservation have been updated successfully.', 'order_status', 1, '2026-09-27 10:40:06'),
(183, 10, 'Pre-Order #ML-DE621115 Updated', 'The customer updated item quantities or their pickup reservation before confirmation.', 'order_status', 0, '2026-09-27 10:40:06'),
(184, 18, 'New Message from Abdul Rafay Khan', 'Order #ML-721BD11E: hi', 'system', 0, '2026-09-27 10:42:03'),
(185, 18, 'New Message from Abdul Rafay Khan', 'Order #ML-721BD11E: quickly shipped my order', 'system', 0, '2026-09-27 10:42:12'),
(186, 18, 'New Message from Abdul Rafay Khan', 'Order #ML-721BD11E: Sent a photo', 'system', 0, '2026-09-27 10:42:23'),
(187, 37, 'Order Accepted! 🥕', 'Your pre-order #ML-721BD11E has been accepted by Junaid Pure Vegetable Plots. It will be packed fresh for market pickup.', 'order_status', 0, '2026-09-27 10:44:38'),
(188, 37, 'Ready for Pickup! 🥬', 'Your pre-order #ML-721BD11E is sorted and ready for collection at Junaid Pure Vegetable Plots. See you soon!', 'order_status', 0, '2026-09-27 10:45:17'),
(189, 37, 'Order Completed! 🎉', 'Your pre-order #ML-721BD11E with Junaid Pure Vegetable Plots has been fulfilled. Thank you for supporting local growers!', 'order_status', 0, '2026-09-27 10:52:43'),
(190, 37, 'Response to your review from Junaid Pure Vegetable Plots 💬', 'Farmer Junaid Pure Vegetable Plots has responded to your feedback: \"thanks sir\"', 'system', 0, '2026-09-27 10:55:17'),
(191, 21, 'Account Notice: Stall Suspended', 'Your farmer stall has been temporarily suspended by platform administration. Reason: seems like a fraud', 'system', 0, '2026-09-27 10:58:45'),
(192, 21, '🎉 Farmer Stall Approved!', 'Congratulations! Your farm profile has been officially verified and approved. You can now post weekly inventories, assign stalls, and start receiving pre-orders.', 'system', 0, '2026-09-27 11:00:30'),
(193, 2, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(194, 6, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(195, 9, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(196, 10, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(197, 11, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(198, 12, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(199, 13, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(200, 14, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(201, 15, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(202, 16, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(203, 17, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(204, 18, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(205, 19, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(206, 20, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(207, 21, '📢 SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'announcement', 0, '2026-09-27 11:03:14'),
(208, 37, 'Pre-Order #ML-796A1AB7 Placed Successfully', 'Your harvest pre-order of Rs. 210.00 has been submitted to the farmer for pickup on 2026-09-27. Payment is settled upon pickup.', 'order_status', 0, '2026-09-27 11:28:23'),
(209, 37, 'Pre-Order #ML-54183599 Placed Successfully', 'Your harvest pre-order of Rs. 357.00 has been submitted to the farmer for pickup on 2026-09-27. Payment is settled upon pickup.', 'order_status', 0, '2026-09-27 11:28:23');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `market_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `pickup_slot_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('placed','accepted','ready_for_pickup','completed','cancelled','declined') DEFAULT 'placed',
  `pickup_date` date NOT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_number`, `customer_id`, `farmer_id`, `market_id`, `stall_id`, `pickup_slot_id`, `total_amount`, `order_status`, `pickup_date`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(1, 'ML-E8F5EDF4', 1, 2, 1, 1, 1, 390.00, 'completed', '2026-09-21', NULL, '2026-09-21 19:58:36', '2026-09-23 19:58:36'),
(2, 'ML-8592E580', 1, 2, 1, 1, 1, 420.00, 'ready_for_pickup', '2026-09-25', NULL, '2026-09-23 19:58:36', '2026-09-23 19:58:36'),
(3, 'ML-ECCEF6AC', 8, 2, 1, 1, 13, 280.00, 'placed', '2026-09-24', NULL, '2026-09-24 10:49:19', '2026-09-24 10:49:19'),
(4, 'ML-C614961E', 8, 9, 1, 5, 13, 650.00, 'completed', '2026-09-24', NULL, '2026-09-24 10:49:19', '2026-09-24 11:05:40'),
(5, 'ML-9B5633C6', 8, 2, 1, 1, 13, 280.00, 'cancelled', '2026-09-24', 'Placed order by mistake', '2026-09-24 10:49:39', '2026-09-24 10:49:50'),
(6, 'ML-6C807157', 8, 6, 1, 3, 19, 760.00, 'completed', '2026-09-24', NULL, '2026-09-24 13:14:17', '2026-09-24 13:21:40'),
(7, 'ML-2026-05007', 1, 6, 1, 3, 179, 85.00, 'placed', '2026-09-03', NULL, '2026-09-01 20:06:47', '2026-09-24 23:06:47'),
(8, 'ML-2026-05008', 23, 6, 1, 3, 184, 1260.00, 'accepted', '2026-08-14', NULL, '2026-08-12 20:06:47', '2026-09-24 23:06:47'),
(9, 'ML-2026-05009', 28, 6, 1, 3, 181, 1395.00, 'cancelled', '2026-09-19', 'Insufficient stock at the time of pickup', '2026-09-18 20:06:47', '2026-09-24 23:06:47'),
(10, 'ML-2026-05010', 1, 2, 2, 2, 155, 3140.00, 'declined', '2026-08-14', 'Customer no-show at pickup window', '2026-08-14 20:06:47', '2026-09-24 23:06:48'),
(11, 'ML-2026-05011', 27, 6, 3, 4, 195, 290.00, 'completed', '2026-09-05', NULL, '2026-09-02 20:06:48', '2026-09-24 23:06:48'),
(12, 'ML-2026-05012', 22, 2, 2, 2, 153, 530.00, 'completed', '2026-08-16', NULL, '2026-08-13 20:06:48', '2026-09-24 23:06:48'),
(13, 'ML-2026-05013', 28, 2, 1, 1, 13, 2860.00, 'cancelled', '2026-08-01', 'Farmer unwell — stall closed this week', '2026-07-29 20:06:48', '2026-09-24 23:06:48'),
(14, 'ML-2026-05014', 32, 6, 3, 4, 94, 170.00, 'cancelled', '2026-08-05', 'Customer requested cancellation before cutoff', '2026-08-04 20:06:48', '2026-09-24 23:06:48'),
(15, 'ML-2026-05015', 24, 6, 3, 4, 197, 120.00, 'cancelled', '2026-07-28', 'Duplicate order placed by error', '2026-07-28 20:06:48', '2026-09-24 23:06:48'),
(16, 'ML-2026-05016', 7, 6, 1, 3, 79, 500.00, 'cancelled', '2026-07-19', 'Duplicate order detected and removed', '2026-07-17 20:08:11', '2026-09-24 23:08:11'),
(17, 'ML-2026-05017', 36, 6, 1, 3, 19, 2400.00, 'cancelled', '2026-08-24', 'Customer changed delivery address after cutoff', '2026-08-20 20:08:11', '2026-09-24 23:08:11'),
(18, 'ML-2026-05018', 5, 11, 3, 9, 300, 222.00, 'completed', '2026-08-03', NULL, '2026-07-30 20:08:11', '2026-09-24 23:08:11'),
(19, 'ML-2026-05019', 1, 6, 3, 4, 118, 170.00, 'placed', '2026-08-06', NULL, '2026-08-05 20:08:11', '2026-09-24 23:08:11'),
(20, 'ML-2026-05020', 31, 6, 3, 4, 188, 760.00, 'declined', '2026-07-13', 'Stall closure due to weather conditions', '2026-07-10 20:08:11', '2026-09-24 23:08:11'),
(21, 'ML-2026-05021', 29, 17, 8, 20, 502, 2502.00, 'placed', '2026-09-01', NULL, '2026-08-28 20:08:11', '2026-09-24 23:08:11'),
(22, 'ML-2026-05022', 30, 17, 8, 20, 504, 2402.00, 'declined', '2026-09-17', 'Customer changed delivery address after cutoff', '2026-09-15 20:08:11', '2026-09-24 23:08:11'),
(23, 'ML-2026-05023', 22, 16, 10, 18, 470, 4444.00, 'ready_for_pickup', '2026-07-20', NULL, '2026-07-17 20:08:11', '2026-09-24 23:08:11'),
(24, 'ML-2026-05024', 35, 18, 5, 23, 577, 68.00, 'completed', '2026-08-11', NULL, '2026-08-09 20:08:11', '2026-09-24 23:08:11'),
(25, 'ML-2026-05025', 33, 14, 11, 15, 420, 217.00, 'completed', '2026-08-09', NULL, '2026-08-08 20:08:11', '2026-09-24 23:08:11'),
(26, 'ML-2026-05026', 27, 6, 3, 4, 185, 1850.00, 'placed', '2026-09-10', NULL, '2026-09-07 20:08:11', '2026-09-24 23:08:11'),
(27, 'ML-2026-05027', 31, 17, 3, 21, 537, 1549.00, 'cancelled', '2026-08-23', 'Duplicate order detected and removed', '2026-08-20 20:08:11', '2026-09-24 23:08:11'),
(28, 'ML-2026-05028', 24, 21, 7, 28, 669, 1071.00, 'completed', '2026-08-06', NULL, '2026-08-03 20:08:11', '2026-09-24 23:08:11'),
(29, 'ML-2026-05029', 30, 2, 2, 2, 18, 2470.00, 'completed', '2026-07-28', NULL, '2026-07-24 20:08:11', '2026-09-24 23:08:11'),
(30, 'ML-2026-05030', 24, 2, 1, 1, 139, 420.00, 'completed', '2026-09-02', NULL, '2026-08-31 20:08:11', '2026-09-24 23:08:11'),
(31, 'ML-2026-05031', 8, 20, 11, 27, 661, 696.00, 'completed', '2026-09-11', NULL, '2026-09-09 20:08:11', '2026-09-24 23:08:11'),
(32, 'ML-2026-05032', 3, 12, 1, 10, 313, 480.00, 'placed', '2026-08-17', NULL, '2026-08-14 20:08:11', '2026-09-24 23:08:11'),
(33, 'ML-2026-05033', 24, 14, 11, 15, 418, 3005.00, 'declined', '2026-07-21', 'Duplicate order detected and removed', '2026-07-18 20:08:11', '2026-09-24 23:08:11'),
(34, 'ML-2026-05034', 36, 10, 6, 7, 242, 2104.00, 'completed', '2026-09-22', NULL, '2026-09-19 20:08:11', '2026-09-24 23:08:11'),
(35, 'ML-2026-05035', 3, 14, 2, 14, 400, 4819.00, 'completed', '2026-09-02', NULL, '2026-08-29 20:08:11', '2026-09-24 23:08:11'),
(36, 'ML-2026-05036', 31, 17, 8, 20, 514, 430.00, 'ready_for_pickup', '2026-08-26', NULL, '2026-08-23 20:08:11', '2026-09-24 23:08:11'),
(37, 'ML-2026-05037', 26, 19, 4, 24, 597, 621.00, 'completed', '2026-09-24', NULL, '2026-09-21 20:08:11', '2026-09-24 23:08:11'),
(38, 'ML-2026-05038', 28, 12, 5, 11, 322, 240.00, 'declined', '2026-09-07', 'Customer requested cancellation before cutoff time', '2026-09-03 20:08:11', '2026-09-24 23:08:11'),
(39, 'ML-2026-05039', 23, 6, 1, 3, 19, 325.00, 'accepted', '2026-09-23', NULL, '2026-09-22 20:08:11', '2026-09-24 23:08:11'),
(40, 'ML-2026-05040', 27, 21, 7, 28, 665, 2999.00, 'completed', '2026-09-12', NULL, '2026-09-09 20:08:11', '2026-09-24 23:08:11'),
(41, 'ML-2026-05041', 23, 14, 2, 14, 394, 2638.00, 'completed', '2026-08-23', NULL, '2026-08-21 20:08:11', '2026-09-24 23:08:11'),
(42, 'ML-2026-05042', 28, 15, 7, 16, 439, 2120.00, 'cancelled', '2026-07-23', 'Customer no-show at pickup window', '2026-07-21 20:08:11', '2026-09-24 23:08:11'),
(43, 'ML-2026-05043', 34, 17, 8, 20, 506, 2454.00, 'placed', '2026-08-07', NULL, '2026-08-05 20:08:11', '2026-09-24 23:08:11'),
(44, 'ML-2026-05044', 23, 21, 9, 29, 699, 3159.00, 'completed', '2026-08-31', NULL, '2026-08-27 20:08:11', '2026-09-24 23:08:11'),
(45, 'ML-2026-05045', 5, 13, 12, 13, 381, 1567.00, 'completed', '2026-08-20', NULL, '2026-08-17 20:08:11', '2026-09-24 23:08:11'),
(46, 'ML-2026-05046', 34, 11, 8, 8, 265, 1131.00, 'completed', '2026-07-15', NULL, '2026-07-12 20:08:11', '2026-09-24 23:08:11'),
(47, 'ML-2026-05047', 3, 10, 6, 7, 244, 1944.00, 'placed', '2026-09-02', NULL, '2026-08-31 20:08:11', '2026-09-24 23:08:11'),
(48, 'ML-2026-05048', 5, 13, 12, 13, 378, 497.00, 'cancelled', '2026-08-14', 'Customer requested cancellation before cutoff time', '2026-08-13 20:08:11', '2026-09-24 23:08:11'),
(49, 'ML-2026-05049', 8, 9, 1, 5, 221, 650.00, 'completed', '2026-08-03', NULL, '2026-07-30 20:08:11', '2026-09-25 00:36:15'),
(50, 'ML-2026-05050', 22, 16, 6, 19, 489, 5413.00, 'accepted', '2026-07-17', NULL, '2026-07-14 20:08:11', '2026-09-24 23:08:11'),
(51, 'ML-2026-05051', 28, 20, 2, 26, 625, 434.00, 'completed', '2026-08-23', NULL, '2026-08-20 20:08:11', '2026-09-24 23:08:11'),
(52, 'ML-2026-05052', 23, 11, 3, 9, 301, 1294.00, 'accepted', '2026-08-06', NULL, '2026-08-02 20:08:11', '2026-09-24 23:08:11'),
(53, 'ML-2026-05053', 29, 15, 9, 17, 455, 626.00, 'completed', '2026-08-21', NULL, '2026-08-20 20:08:11', '2026-09-24 23:08:11'),
(54, 'ML-2026-05054', 8, 10, 6, 7, 248, 432.00, 'accepted', '2026-08-28', NULL, '2026-08-25 20:08:11', '2026-09-24 23:08:11'),
(55, 'ML-2026-05055', 30, 15, 9, 17, 454, 178.00, 'completed', '2026-08-21', NULL, '2026-08-18 20:08:11', '2026-09-24 23:08:11'),
(56, 'ML-2026-05056', 25, 15, 9, 17, 449, 749.00, 'completed', '2026-08-17', NULL, '2026-08-13 20:08:11', '2026-09-24 23:08:11'),
(57, 'ML-2026-05057', 24, 12, 1, 10, 318, 994.00, 'ready_for_pickup', '2026-08-03', NULL, '2026-08-02 20:08:11', '2026-09-24 23:08:11'),
(58, 'ML-2026-05058', 8, 13, 12, 13, 366, 2114.00, 'placed', '2026-08-22', NULL, '2026-08-19 20:08:11', '2026-09-24 23:08:11'),
(59, 'ML-2026-05059', 26, 10, 10, 6, 222, 2046.00, 'declined', '2026-06-28', 'Farmer unable to fulfill — insufficient stock after harvest', '2026-06-27 20:08:11', '2026-09-24 23:08:11'),
(60, 'ML-2026-05060', 23, 18, 5, 23, 573, 651.00, 'accepted', '2026-08-31', NULL, '2026-08-29 20:08:11', '2026-09-24 23:08:11'),
(61, 'ML-2026-05061', 23, 19, 4, 24, 584, 3708.00, 'completed', '2026-09-13', NULL, '2026-09-12 20:08:11', '2026-09-24 23:08:11'),
(62, 'ML-2026-05062', 24, 21, 9, 29, 686, 155.00, 'placed', '2026-09-22', NULL, '2026-09-21 20:08:11', '2026-09-24 23:08:11'),
(63, 'ML-2026-05063', 34, 18, 5, 23, 576, 219.00, 'accepted', '2026-09-08', NULL, '2026-09-05 20:08:11', '2026-09-24 23:08:11'),
(64, 'ML-2026-05064', 27, 13, 4, 12, 347, 598.00, 'completed', '2026-08-05', NULL, '2026-08-02 20:08:11', '2026-09-24 23:08:11'),
(65, 'ML-2026-05065', 28, 20, 11, 27, 659, 3610.00, 'ready_for_pickup', '2026-08-08', NULL, '2026-08-06 20:08:11', '2026-09-24 23:08:11'),
(66, 'ML-2026-05066', 26, 12, 1, 10, 312, 480.00, 'cancelled', '2026-07-10', 'Duplicate order detected and removed', '2026-07-08 20:08:11', '2026-09-24 23:08:11'),
(67, 'ML-2026-05067', 25, 12, 1, 10, 313, 1281.00, 'completed', '2026-09-06', NULL, '2026-09-05 20:08:11', '2026-09-24 23:08:11'),
(68, 'ML-2026-05068', 27, 17, 3, 21, 535, 1613.00, 'completed', '2026-08-29', NULL, '2026-08-28 20:08:11', '2026-09-24 23:08:11'),
(69, 'ML-2026-05069', 26, 12, 5, 11, 340, 1289.00, 'accepted', '2026-09-24', NULL, '2026-09-20 20:08:11', '2026-09-24 23:08:11'),
(70, 'ML-2026-05070', 30, 13, 4, 12, 360, 127.00, 'completed', '2026-09-07', NULL, '2026-09-06 20:08:11', '2026-09-24 23:08:11'),
(71, 'ML-2026-05071', 28, 16, 6, 19, 493, 4217.00, 'accepted', '2026-08-20', NULL, '2026-08-16 20:08:11', '2026-09-24 23:08:11'),
(72, 'ML-2026-05072', 31, 14, 11, 15, 414, 636.00, 'completed', '2026-08-14', NULL, '2026-08-11 20:08:11', '2026-09-24 23:08:11'),
(73, 'ML-2026-05073', 29, 18, 1, 22, 547, 1188.00, 'declined', '2026-08-24', 'Customer changed delivery address after cutoff', '2026-08-22 20:08:11', '2026-09-24 23:08:11'),
(74, 'ML-2026-05074', 36, 16, 6, 19, 491, 2849.00, 'ready_for_pickup', '2026-09-21', NULL, '2026-09-17 20:08:11', '2026-09-24 23:08:11'),
(75, 'ML-2026-05075', 7, 18, 1, 22, 548, 651.00, 'accepted', '2026-08-19', NULL, '2026-08-18 20:08:11', '2026-09-24 23:08:11'),
(76, 'ML-2026-05076', 23, 12, 1, 10, 307, 1796.00, 'cancelled', '2026-07-07', 'Customer requested cancellation before cutoff time', '2026-07-03 20:08:11', '2026-09-24 23:08:11'),
(77, 'ML-2026-05077', 30, 9, 1, 5, 204, 650.00, 'completed', '2026-07-15', NULL, '2026-07-13 20:08:11', '2026-09-25 00:36:11'),
(78, 'ML-2026-05078', 23, 19, 12, 25, 612, 1930.00, 'placed', '2026-07-14', NULL, '2026-07-12 20:08:11', '2026-09-24 23:08:11'),
(79, 'ML-2026-05079', 29, 6, 1, 3, 69, 2745.00, 'accepted', '2026-06-28', NULL, '2026-06-26 20:08:11', '2026-09-24 23:08:11'),
(80, 'ML-2026-05080', 25, 21, 9, 29, 696, 1971.00, 'completed', '2026-09-19', NULL, '2026-09-15 20:08:11', '2026-09-24 23:08:11'),
(81, 'ML-2026-05081', 26, 12, 1, 10, 310, 179.00, 'cancelled', '2026-08-24', 'Customer no-show at pickup window', '2026-08-21 20:08:11', '2026-09-24 23:08:11'),
(82, 'ML-2026-05082', 28, 10, 6, 7, 257, 2324.00, 'ready_for_pickup', '2026-07-20', NULL, '2026-07-18 20:08:11', '2026-09-24 23:08:11'),
(83, 'ML-2026-05083', 27, 20, 11, 27, 646, 464.00, 'placed', '2026-08-26', NULL, '2026-08-23 20:08:11', '2026-09-24 23:08:11'),
(84, 'ML-2026-05084', 22, 14, 2, 14, 395, 434.00, 'completed', '2026-07-13', NULL, '2026-07-12 20:08:11', '2026-09-24 23:08:11'),
(85, 'ML-2026-05085', 33, 2, 1, 1, 111, 3140.00, 'completed', '2026-09-14', NULL, '2026-09-12 20:08:11', '2026-09-24 23:08:11'),
(86, 'ML-2026-05086', 5, 16, 10, 18, 481, 1595.00, 'accepted', '2026-09-05', NULL, '2026-09-04 20:08:11', '2026-09-24 23:08:11'),
(87, 'ML-2026-05087', 35, 10, 10, 6, 228, 1100.00, 'declined', '2026-07-10', 'Farmer unable to fulfill — insufficient stock after harvest', '2026-07-07 20:08:11', '2026-09-24 23:08:11'),
(88, 'ML-2026-05088', 32, 10, 10, 6, 239, 1520.00, 'completed', '2026-07-24', NULL, '2026-07-20 20:08:11', '2026-09-24 23:08:11'),
(89, 'ML-2026-05089', 7, 16, 6, 19, 486, 1305.00, 'accepted', '2026-09-24', NULL, '2026-09-23 20:08:11', '2026-09-24 23:08:11'),
(90, 'ML-2026-05090', 25, 6, 1, 3, 81, 2135.00, 'declined', '2026-07-20', 'Farmer unable to fulfill — insufficient stock after harvest', '2026-07-16 20:08:11', '2026-09-24 23:08:11'),
(91, 'ML-2026-05091', 1, 10, 6, 7, 257, 660.00, 'accepted', '2026-07-15', NULL, '2026-07-13 20:08:11', '2026-09-24 23:08:11'),
(92, 'ML-2026-05092', 31, 6, 3, 4, 195, 450.00, 'placed', '2026-08-20', NULL, '2026-08-19 20:08:11', '2026-09-24 23:08:11'),
(93, 'ML-2026-05093', 35, 11, 8, 8, 278, 825.00, 'declined', '2026-09-16', 'Stall closure due to weather conditions', '2026-09-14 20:08:11', '2026-09-24 23:08:11'),
(94, 'ML-2026-05094', 32, 21, 9, 29, 689, 2401.00, 'accepted', '2026-09-06', NULL, '2026-09-05 20:08:11', '2026-09-24 23:08:11'),
(95, 'ML-2026-05095', 1, 13, 12, 13, 371, 510.00, 'accepted', '2026-08-15', NULL, '2026-08-14 20:08:11', '2026-09-24 23:08:11'),
(96, 'ML-CAED0B4C', 8, 21, 9, 29, 682, 732.00, 'cancelled', '2026-09-26', 'Schedule conflict on market day', '2026-09-25 11:59:39', '2026-09-25 12:00:12'),
(97, 'ML-72166E3C', 8, 20, 11, 27, 642, 280.00, 'placed', '2026-09-26', NULL, '2026-09-25 12:57:45', '2026-09-25 12:57:45'),
(98, 'ML-1EB5CE0F', 8, 21, 9, 29, 701, 522.00, 'placed', '2026-10-02', NULL, '2026-09-25 12:59:48', '2026-09-25 13:00:58'),
(99, 'ML-2F848FF0', 22, 21, 9, 29, 693, 223.00, 'accepted', '2026-10-10', NULL, '2026-09-25 13:03:29', '2026-09-25 13:04:45'),
(100, 'ML-0CEA6434', 22, 21, 9, 29, 682, 420.00, 'placed', '2026-09-26', NULL, '2026-09-25 13:09:02', '2026-09-25 13:09:02'),
(101, 'ML-4FDB1836', 37, 21, 9, 29, 684, 420.00, 'placed', '2026-09-26', NULL, '2026-09-25 16:04:01', '2026-09-25 16:04:01'),
(102, 'ML-87CECE98', 4, 21, 9, 29, 682, 155.00, 'placed', '2026-09-26', NULL, '2026-09-26 12:02:58', '2026-09-26 12:02:58'),
(103, 'ML-A0038835', 37, 21, 9, 29, 251, 210.00, 'placed', '2026-09-30', NULL, '2026-09-27 10:38:46', '2026-09-27 10:38:46'),
(104, 'ML-248E0273', 37, 19, 12, 25, 251, 137.00, 'placed', '2026-09-30', NULL, '2026-09-27 10:38:46', '2026-09-27 10:38:46'),
(105, 'ML-721BD11E', 37, 18, 5, 23, 251, 396.00, 'completed', '2026-09-30', NULL, '2026-09-27 10:38:46', '2026-09-27 10:52:43'),
(106, 'ML-DE621115', 37, 10, 6, 7, 251, 576.00, 'placed', '2026-10-10', NULL, '2026-09-27 10:38:46', '2026-09-27 10:40:06'),
(107, 'ML-796A1AB7', 37, 19, 12, 25, 606, 210.00, 'placed', '2026-09-27', NULL, '2026-09-27 11:28:23', '2026-09-27 11:28:23'),
(108, 'ML-54183599', 37, 21, 9, 29, 686, 357.00, 'placed', '2026-09-27', NULL, '2026-09-27 11:28:23', '2026-09-27 11:28:23');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 2, 140.00, 280.00),
(2, 1, 2, 1, 110.00, 110.00),
(3, 2, 1, 3, 140.00, 420.00),
(4, 3, 3, 1, 280.00, 280.00),
(5, 4, 11, 1, 650.00, 650.00),
(6, 5, 3, 1, 280.00, 280.00),
(7, 6, 8, 2, 380.00, 760.00),
(8, 7, 5, 1, 85.00, 85.00),
(9, 8, 7, 2, 450.00, 900.00),
(10, 8, 9, 3, 120.00, 360.00),
(11, 9, 5, 3, 85.00, 255.00),
(12, 9, 8, 3, 380.00, 1140.00),
(13, 10, 3, 1, 280.00, 280.00),
(14, 10, 2, 3, 110.00, 330.00),
(15, 10, 4, 3, 750.00, 2250.00),
(16, 10, 1, 2, 140.00, 280.00),
(17, 11, 5, 2, 85.00, 170.00),
(18, 11, 9, 1, 120.00, 120.00),
(19, 12, 3, 1, 280.00, 280.00),
(20, 12, 1, 1, 140.00, 140.00),
(21, 12, 2, 1, 110.00, 110.00),
(22, 13, 4, 3, 750.00, 2250.00),
(23, 13, 2, 3, 110.00, 330.00),
(24, 13, 1, 2, 140.00, 280.00),
(25, 14, 5, 2, 85.00, 170.00),
(26, 15, 9, 1, 120.00, 120.00),
(27, 16, 9, 1, 120.00, 120.00),
(28, 16, 8, 1, 380.00, 380.00),
(29, 17, 7, 3, 450.00, 1350.00),
(30, 17, 9, 1, 120.00, 120.00),
(31, 17, 5, 2, 85.00, 170.00),
(32, 17, 8, 2, 380.00, 760.00),
(33, 18, 21, 3, 74.00, 222.00),
(34, 19, 5, 2, 85.00, 170.00),
(35, 20, 8, 2, 380.00, 760.00),
(36, 21, 45, 2, 247.00, 494.00),
(37, 21, 48, 3, 534.00, 1602.00),
(38, 21, 44, 2, 203.00, 406.00),
(39, 22, 46, 1, 434.00, 434.00),
(40, 22, 44, 2, 203.00, 406.00),
(41, 22, 48, 2, 534.00, 1068.00),
(42, 22, 45, 2, 247.00, 494.00),
(43, 23, 43, 3, 540.00, 1620.00),
(44, 23, 40, 2, 916.00, 1832.00),
(45, 23, 42, 2, 313.00, 626.00),
(46, 23, 41, 1, 366.00, 366.00),
(47, 24, 50, 1, 68.00, 68.00),
(48, 25, 35, 1, 217.00, 217.00),
(49, 26, 8, 2, 380.00, 760.00),
(50, 26, 6, 2, 320.00, 640.00),
(51, 26, 7, 1, 450.00, 450.00),
(52, 27, 45, 1, 247.00, 247.00),
(53, 27, 46, 3, 434.00, 1302.00),
(54, 28, 64, 3, 357.00, 1071.00),
(55, 29, 2, 2, 110.00, 220.00),
(56, 29, 4, 3, 750.00, 2250.00),
(57, 30, 1, 3, 140.00, 420.00),
(58, 31, 59, 3, 232.00, 696.00),
(59, 32, 25, 2, 240.00, 480.00),
(60, 33, 33, 2, 212.00, 424.00),
(61, 33, 34, 1, 796.00, 796.00),
(62, 33, 32, 3, 595.00, 1785.00),
(63, 34, 13, 1, 230.00, 230.00),
(64, 34, 14, 2, 220.00, 440.00),
(65, 34, 16, 3, 144.00, 432.00),
(66, 34, 15, 3, 334.00, 1002.00),
(67, 35, 34, 3, 796.00, 2388.00),
(68, 35, 35, 2, 217.00, 434.00),
(69, 35, 33, 1, 212.00, 212.00),
(70, 35, 32, 3, 595.00, 1785.00),
(71, 36, 47, 2, 215.00, 430.00),
(72, 37, 55, 3, 137.00, 411.00),
(73, 37, 54, 1, 210.00, 210.00),
(74, 38, 25, 1, 240.00, 240.00),
(75, 39, 5, 1, 85.00, 85.00),
(76, 39, 9, 2, 120.00, 240.00),
(77, 40, 61, 2, 223.00, 446.00),
(78, 40, 62, 3, 522.00, 1566.00),
(79, 40, 63, 3, 210.00, 630.00),
(80, 40, 64, 1, 357.00, 357.00),
(81, 41, 32, 3, 595.00, 1785.00),
(82, 41, 35, 1, 217.00, 217.00),
(83, 41, 33, 3, 212.00, 636.00),
(84, 42, 37, 2, 129.00, 258.00),
(85, 42, 39, 2, 931.00, 1862.00),
(86, 43, 47, 1, 215.00, 215.00),
(87, 43, 48, 3, 534.00, 1602.00),
(88, 43, 44, 1, 203.00, 203.00),
(89, 43, 46, 1, 434.00, 434.00),
(90, 44, 61, 3, 223.00, 669.00),
(91, 44, 64, 2, 357.00, 714.00),
(92, 44, 63, 1, 210.00, 210.00),
(93, 44, 62, 3, 522.00, 1566.00),
(94, 45, 28, 1, 76.00, 76.00),
(95, 45, 31, 3, 497.00, 1491.00),
(96, 46, 19, 1, 71.00, 71.00),
(97, 46, 17, 2, 144.00, 288.00),
(98, 46, 21, 3, 74.00, 222.00),
(99, 46, 20, 2, 275.00, 550.00),
(100, 47, 12, 2, 236.00, 472.00),
(101, 47, 16, 1, 144.00, 144.00),
(102, 47, 14, 3, 220.00, 660.00),
(103, 47, 15, 2, 334.00, 668.00),
(104, 48, 31, 1, 497.00, 497.00),
(105, 49, 11, 1, 650.00, 650.00),
(106, 50, 41, 2, 366.00, 732.00),
(107, 50, 42, 1, 313.00, 313.00),
(108, 50, 43, 3, 540.00, 1620.00),
(109, 50, 40, 3, 916.00, 2748.00),
(110, 51, 56, 2, 217.00, 434.00),
(111, 52, 19, 2, 71.00, 142.00),
(112, 52, 18, 1, 183.00, 183.00),
(113, 52, 20, 3, 275.00, 825.00),
(114, 52, 17, 1, 144.00, 144.00),
(115, 53, 36, 2, 178.00, 356.00),
(116, 53, 38, 2, 135.00, 270.00),
(117, 54, 16, 3, 144.00, 432.00),
(118, 55, 36, 1, 178.00, 178.00),
(119, 56, 36, 2, 178.00, 356.00),
(120, 56, 38, 1, 135.00, 135.00),
(121, 56, 37, 2, 129.00, 258.00),
(122, 57, 23, 2, 322.00, 644.00),
(123, 57, 24, 1, 350.00, 350.00),
(124, 58, 27, 2, 127.00, 254.00),
(125, 58, 31, 3, 497.00, 1491.00),
(126, 58, 28, 2, 76.00, 152.00),
(127, 58, 29, 1, 217.00, 217.00),
(128, 59, 15, 2, 334.00, 668.00),
(129, 59, 12, 3, 236.00, 708.00),
(130, 59, 14, 2, 220.00, 440.00),
(131, 59, 13, 1, 230.00, 230.00),
(132, 60, 49, 3, 73.00, 219.00),
(133, 60, 52, 1, 228.00, 228.00),
(134, 60, 50, 3, 68.00, 204.00),
(135, 61, 55, 3, 137.00, 411.00),
(136, 61, 53, 3, 889.00, 2667.00),
(137, 61, 54, 3, 210.00, 630.00),
(138, 62, 60, 1, 155.00, 155.00),
(139, 63, 49, 3, 73.00, 219.00),
(140, 64, 27, 3, 127.00, 381.00),
(141, 64, 29, 1, 217.00, 217.00),
(142, 65, 57, 3, 280.00, 840.00),
(143, 65, 58, 3, 846.00, 2538.00),
(144, 65, 59, 1, 232.00, 232.00),
(145, 66, 25, 2, 240.00, 480.00),
(146, 67, 22, 1, 231.00, 231.00),
(147, 67, 24, 3, 350.00, 1050.00),
(148, 68, 46, 1, 434.00, 434.00),
(149, 68, 47, 3, 215.00, 645.00),
(150, 68, 48, 1, 534.00, 534.00),
(151, 69, 22, 1, 231.00, 231.00),
(152, 69, 24, 2, 350.00, 700.00),
(153, 69, 26, 2, 179.00, 358.00),
(154, 70, 27, 1, 127.00, 127.00),
(155, 71, 40, 2, 916.00, 1832.00),
(156, 71, 42, 3, 313.00, 939.00),
(157, 71, 43, 2, 540.00, 1080.00),
(158, 71, 41, 1, 366.00, 366.00),
(159, 72, 33, 3, 212.00, 636.00),
(160, 73, 51, 3, 396.00, 1188.00),
(161, 74, 43, 3, 540.00, 1620.00),
(162, 74, 42, 1, 313.00, 313.00),
(163, 74, 40, 1, 916.00, 916.00),
(164, 75, 52, 1, 228.00, 228.00),
(165, 75, 50, 3, 68.00, 204.00),
(166, 75, 49, 3, 73.00, 219.00),
(167, 76, 23, 3, 322.00, 966.00),
(168, 76, 25, 2, 240.00, 480.00),
(169, 76, 24, 1, 350.00, 350.00),
(170, 77, 11, 1, 650.00, 650.00),
(171, 78, 53, 1, 889.00, 889.00),
(172, 78, 54, 3, 210.00, 630.00),
(173, 78, 55, 3, 137.00, 411.00),
(174, 79, 5, 3, 85.00, 255.00),
(175, 79, 7, 3, 450.00, 1350.00),
(176, 79, 8, 3, 380.00, 1140.00),
(177, 80, 61, 1, 223.00, 223.00),
(178, 80, 64, 3, 357.00, 1071.00),
(179, 80, 62, 1, 522.00, 522.00),
(180, 80, 60, 1, 155.00, 155.00),
(181, 81, 26, 1, 179.00, 179.00),
(182, 82, 14, 3, 220.00, 660.00),
(183, 82, 15, 2, 334.00, 668.00),
(184, 82, 16, 2, 144.00, 288.00),
(185, 82, 12, 3, 236.00, 708.00),
(186, 83, 59, 2, 232.00, 464.00),
(187, 84, 35, 2, 217.00, 434.00),
(188, 85, 4, 3, 750.00, 2250.00),
(189, 85, 2, 3, 110.00, 330.00),
(190, 85, 3, 1, 280.00, 280.00),
(191, 85, 1, 2, 140.00, 280.00),
(192, 86, 41, 1, 366.00, 366.00),
(193, 86, 40, 1, 916.00, 916.00),
(194, 86, 42, 1, 313.00, 313.00),
(195, 87, 16, 3, 144.00, 432.00),
(196, 87, 15, 2, 334.00, 668.00),
(197, 88, 15, 2, 334.00, 668.00),
(198, 88, 16, 1, 144.00, 144.00),
(199, 88, 12, 3, 236.00, 708.00),
(200, 89, 42, 3, 313.00, 939.00),
(201, 89, 41, 1, 366.00, 366.00),
(202, 90, 8, 1, 380.00, 380.00),
(203, 90, 5, 1, 85.00, 85.00),
(204, 90, 6, 1, 320.00, 320.00),
(205, 90, 7, 3, 450.00, 1350.00),
(206, 91, 14, 3, 220.00, 660.00),
(207, 92, 7, 1, 450.00, 450.00),
(208, 93, 20, 3, 275.00, 825.00),
(209, 94, 63, 2, 210.00, 420.00),
(210, 94, 61, 1, 223.00, 223.00),
(211, 94, 62, 2, 522.00, 1044.00),
(212, 94, 64, 2, 357.00, 714.00),
(213, 95, 29, 2, 217.00, 434.00),
(214, 95, 28, 1, 76.00, 76.00),
(215, 96, 62, 1, 522.00, 522.00),
(216, 96, 63, 1, 210.00, 210.00),
(217, 97, 57, 1, 280.00, 280.00),
(218, 98, 62, 1, 522.00, 522.00),
(219, 99, 61, 1, 223.00, 223.00),
(220, 100, 63, 2, 210.00, 420.00),
(221, 101, 63, 2, 210.00, 420.00),
(222, 102, 60, 1, 155.00, 155.00),
(223, 103, 63, 1, 210.00, 210.00),
(224, 104, 55, 1, 137.00, 137.00),
(225, 105, 51, 1, 396.00, 396.00),
(226, 106, 16, 4, 144.00, 576.00),
(227, 107, 54, 1, 210.00, 210.00),
(228, 108, 64, 1, 357.00, 357.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_messages`
--

CREATE TABLE `order_messages` (
  `message_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_type` enum('image','document','none') DEFAULT 'none',
  `file_size` int(11) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_messages`
--

INSERT INTO `order_messages` (`message_id`, `order_id`, `sender_id`, `receiver_id`, `message_text`, `attachment_path`, `attachment_name`, `attachment_type`, `file_size`, `is_read`, `created_at`) VALUES
(1, 6, 8, 6, 'hui', NULL, NULL, 'none', 0, 1, '2026-09-24 13:15:05'),
(2, 6, 8, 6, NULL, 'uploads/chat/chat_6_1790237714_f57b0daf.jpg', 'Gemini_Generated_Image_teeof4teeof4teeo.jpg', 'image', 3009976, 1, '2026-09-24 13:15:14'),
(3, 3, 8, 2, 'hi', NULL, NULL, 'none', 0, 0, '2026-09-24 13:45:58'),
(4, 3, 8, 2, 'ye hai', 'uploads/chat/chat_3_1790239566_230a0af2.jpg', 'Gemini_Generated_Image_1ppnm1ppnm1ppnm1.jpg', 'image', 131669, 0, '2026-09-24 13:46:06'),
(5, 7, 1, 6, 'Assalam o Alaikum! I just placed my order — wanted to confirm the pickup time is 8:30 AM on Saturday?', NULL, NULL, 'none', 0, 1, '2026-09-21 20:06:48'),
(6, 7, 6, 1, 'Walaikum Assalam! Yes, your slot is confirmed for 8:30–10:00 AM. Please bring your order number and I\'ll have your box packed and ready 🌿', NULL, NULL, 'none', 0, 1, '2026-09-21 20:24:17'),
(7, 7, 1, 6, 'Perfect, JazakAllah! Should I bring my own bags or do you provide packaging?', NULL, NULL, 'none', 0, 1, '2026-09-21 21:20:05'),
(8, 7, 6, 1, 'We do provide eco-friendly jute bags! However if you have cotton tote bags please bring them — we love reducing packaging waste together 😊', NULL, NULL, 'none', 0, 1, '2026-09-21 21:50:23'),
(9, 7, 1, 6, 'Understood! One more question — is your olive oil cold-pressed or expeller-pressed?', NULL, NULL, 'none', 0, 1, '2026-09-21 22:37:55'),
(10, 7, 6, 1, 'It\'s first cold-press extraction at below 27°C, so maximum polyphenols are retained. Certified by Punjab Food Authority. The colour will be bright golden-green 🫒', NULL, NULL, 'none', 0, 1, '2026-09-21 22:55:53'),
(11, 7, 1, 6, 'That\'s amazing. I\'ll also add kale to my order if it\'s still available this week?', NULL, NULL, 'none', 0, 1, '2026-09-21 23:33:50'),
(12, 7, 6, 1, 'Yes we have fresh stock! I\'ll set aside an extra bunch for you. Just mention your order number when you arrive 👍', NULL, NULL, 'none', 0, 1, '2026-09-22 00:05:52'),
(13, 8, 23, 6, 'Assalam o Alaikum! I just placed my order — wanted to confirm the pickup time is 8:30 AM on Saturday?', NULL, NULL, 'none', 0, 1, '2026-09-20 20:06:48'),
(14, 8, 6, 23, 'Walaikum Assalam! Yes, your slot is confirmed for 8:30–10:00 AM. Please bring your order number and I\'ll have your box packed and ready 🌿', NULL, NULL, 'none', 0, 1, '2026-09-20 20:18:20'),
(15, 8, 23, 6, 'Perfect, JazakAllah! Should I bring my own bags or do you provide packaging?', NULL, NULL, 'none', 0, 1, '2026-09-20 21:02:15'),
(16, 8, 6, 23, 'We do provide eco-friendly jute bags! However if you have cotton tote bags please bring them — we love reducing packaging waste together 😊', NULL, NULL, 'none', 0, 1, '2026-09-20 21:34:28'),
(17, 11, 27, 6, 'Assalam o Alaikum! I just placed my order — wanted to confirm the pickup time is 8:30 AM on Saturday?', NULL, NULL, 'none', 0, 1, '2026-09-22 20:06:48'),
(18, 11, 6, 27, 'Walaikum Assalam! Yes, your slot is confirmed for 8:30–10:00 AM. Please bring your order number and I\'ll have your box packed and ready 🌿', NULL, NULL, 'none', 0, 1, '2026-09-22 20:17:04'),
(19, 11, 27, 6, 'Perfect, JazakAllah! Should I bring my own bags or do you provide packaging?', NULL, NULL, 'none', 0, 1, '2026-09-22 21:05:18'),
(20, 11, 6, 27, 'We do provide eco-friendly jute bags! However if you have cotton tote bags please bring them — we love reducing packaging waste together 😊', NULL, NULL, 'none', 0, 1, '2026-09-22 22:02:01'),
(21, 12, 22, 2, 'Assalam o Alaikum! I just placed my order — wanted to confirm the pickup time is 8:30 AM on Saturday?', NULL, NULL, 'none', 0, 1, '2026-09-22 20:06:48'),
(22, 12, 2, 22, 'Walaikum Assalam! Yes, your slot is confirmed for 8:30–10:00 AM. Please bring your order number and I\'ll have your box packed and ready 🌿', NULL, NULL, 'none', 0, 1, '2026-09-22 20:45:14'),
(23, 12, 22, 2, 'Perfect, JazakAllah! Should I bring my own bags or do you provide packaging?', NULL, NULL, 'none', 0, 1, '2026-09-22 21:03:30'),
(24, 12, 2, 22, 'We do provide eco-friendly jute bags! However if you have cotton tote bags please bring them — we love reducing packaging waste together 😊', NULL, NULL, 'none', 0, 1, '2026-09-22 21:41:39'),
(25, 18, 5, 11, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-20 09:59:47'),
(26, 18, 11, 5, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-20 10:44:18'),
(27, 18, 5, 11, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-20 10:53:22'),
(28, 18, 11, 5, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-20 12:45:39'),
(29, 18, 5, 11, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-20 13:10:27'),
(30, 18, 11, 5, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-20 14:44:28'),
(31, 18, 5, 11, 'Received everything — the honey is absolutely extraordinary. Pure liquid gold! How do you store the hives in summer heat?', NULL, NULL, 'none', 0, 1, '2026-09-20 16:29:31'),
(32, 19, 1, 6, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-22 07:50:06'),
(33, 19, 6, 1, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-22 08:22:37'),
(34, 19, 1, 6, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-22 10:08:23'),
(35, 19, 6, 1, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-22 11:35:44'),
(36, 19, 1, 6, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-22 11:43:24'),
(37, 19, 6, 1, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-22 12:39:11'),
(38, 19, 1, 6, 'Received everything — the honey is absolutely extraordinary. Pure liquid gold! How do you store the hives in summer heat?', NULL, NULL, 'none', 0, 1, '2026-09-22 14:07:32'),
(39, 19, 6, 1, 'Alhamdulillah, very kind of you! We keep hives in partial shade and harvest at dawn when bee activity is lowest. The summer Sidr harvest is actually our most prized batch!', NULL, NULL, 'none', 0, 1, '2026-09-22 15:34:31'),
(40, 24, 35, 18, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-22 15:42:59'),
(41, 24, 18, 35, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-22 16:15:46'),
(42, 24, 35, 18, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-22 17:18:32'),
(43, 25, 33, 14, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-23 05:57:58'),
(44, 25, 14, 33, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-23 06:16:01'),
(45, 25, 33, 14, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-23 08:14:19'),
(46, 25, 14, 33, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-23 08:33:46'),
(47, 25, 33, 14, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-23 10:10:32'),
(48, 28, 24, 21, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-20 13:11:34'),
(49, 28, 21, 24, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-20 13:43:45'),
(50, 28, 24, 21, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-20 15:33:17'),
(51, 28, 21, 24, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-20 15:57:20'),
(52, 34, 36, 10, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-19 23:11:10'),
(53, 34, 10, 36, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-19 23:23:05'),
(54, 34, 36, 10, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-19 23:31:14'),
(55, 34, 10, 36, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-20 00:45:10'),
(56, 34, 36, 10, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-20 02:12:35'),
(57, 43, 34, 17, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-18 02:55:46'),
(58, 43, 17, 34, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-18 03:41:55'),
(59, 43, 34, 17, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-18 03:50:15'),
(60, 43, 17, 34, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-18 04:02:26'),
(61, 43, 34, 17, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-18 04:36:44'),
(62, 43, 17, 34, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-18 05:29:19'),
(63, 43, 34, 17, 'Received everything — the honey is absolutely extraordinary. Pure liquid gold! How do you store the hives in summer heat?', NULL, NULL, 'none', 0, 1, '2026-09-18 05:53:42'),
(64, 45, 5, 13, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-21 01:36:20'),
(65, 45, 13, 5, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-21 01:52:39'),
(66, 45, 5, 13, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-21 02:40:20'),
(67, 45, 13, 5, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-21 03:45:54'),
(68, 51, 28, 20, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-22 07:23:57'),
(69, 51, 20, 28, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-22 09:03:44'),
(70, 51, 28, 20, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-22 10:57:31'),
(71, 52, 23, 11, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-20 23:53:09'),
(72, 52, 11, 23, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-21 00:17:13'),
(73, 53, 29, 15, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-22 14:32:28'),
(74, 53, 15, 29, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-22 15:19:02'),
(75, 53, 29, 15, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-22 16:39:07'),
(76, 53, 15, 29, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-22 17:45:41'),
(77, 53, 29, 15, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-22 18:12:28'),
(78, 53, 15, 29, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-22 19:08:41'),
(79, 54, 8, 10, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-19 21:07:27'),
(80, 54, 10, 8, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-19 23:07:23'),
(81, 54, 8, 10, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-19 23:56:28'),
(82, 54, 10, 8, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-20 00:36:42'),
(83, 54, 8, 10, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-20 02:13:59'),
(84, 54, 10, 8, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-20 02:27:41'),
(85, 54, 8, 10, 'Received everything — the honey is absolutely extraordinary. Pure liquid gold! How do you store the hives in summer heat?', NULL, NULL, 'none', 0, 1, '2026-09-20 03:06:23'),
(86, 54, 10, 8, 'Alhamdulillah, very kind of you! We keep hives in partial shade and harvest at dawn when bee activity is lowest. The summer Sidr harvest is actually our most prized batch!', NULL, NULL, 'none', 0, 1, '2026-09-20 04:51:16'),
(87, 55, 30, 15, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-22 22:35:41'),
(88, 55, 15, 30, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-23 00:22:19'),
(89, 55, 30, 15, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-23 01:55:15'),
(90, 56, 25, 15, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-20 04:49:31'),
(91, 56, 15, 25, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-20 06:31:11'),
(92, 57, 24, 12, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-22 23:05:09'),
(93, 57, 12, 24, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-22 23:26:51'),
(94, 57, 24, 12, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-23 00:58:39'),
(95, 57, 12, 24, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-23 01:20:15'),
(96, 57, 24, 12, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-23 03:16:11'),
(97, 57, 12, 24, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-23 04:02:25'),
(98, 57, 24, 12, 'Received everything — the honey is absolutely extraordinary. Pure liquid gold! How do you store the hives in summer heat?', NULL, NULL, 'none', 0, 1, '2026-09-23 04:25:27'),
(99, 60, 23, 18, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-23 10:37:31'),
(100, 60, 18, 23, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-23 11:40:40'),
(101, 60, 23, 18, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-23 12:36:19'),
(102, 60, 18, 23, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-23 12:42:42'),
(103, 60, 23, 18, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-23 13:12:54'),
(104, 60, 18, 23, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-23 13:46:55'),
(105, 60, 23, 18, 'Received everything — the honey is absolutely extraordinary. Pure liquid gold! How do you store the hives in summer heat?', NULL, NULL, 'none', 0, 1, '2026-09-23 14:02:05'),
(106, 60, 18, 23, 'Alhamdulillah, very kind of you! We keep hives in partial shade and harvest at dawn when bee activity is lowest. The summer Sidr harvest is actually our most prized batch!', NULL, NULL, 'none', 0, 1, '2026-09-23 14:18:35'),
(107, 63, 34, 18, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-19 16:35:58'),
(108, 63, 18, 34, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-19 18:00:28'),
(109, 63, 34, 18, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-19 19:33:20'),
(110, 63, 18, 34, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-19 20:52:32'),
(111, 63, 34, 18, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-19 22:32:21'),
(112, 63, 18, 34, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-19 23:59:26'),
(113, 63, 34, 18, 'Received everything — the honey is absolutely extraordinary. Pure liquid gold! How do you store the hives in summer heat?', NULL, NULL, 'none', 0, 1, '2026-09-20 01:50:49'),
(114, 64, 27, 13, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-19 19:09:16'),
(115, 64, 13, 27, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-19 20:38:00'),
(116, 64, 27, 13, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-19 22:07:02'),
(117, 64, 13, 27, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-19 22:53:04'),
(118, 67, 25, 12, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-20 16:19:16'),
(119, 67, 12, 25, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-20 17:33:38'),
(120, 67, 25, 12, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-20 18:29:22'),
(121, 71, 28, 16, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-19 08:14:24'),
(122, 71, 16, 28, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-19 08:38:05'),
(123, 71, 28, 16, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-19 09:30:56'),
(124, 71, 16, 28, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-19 11:06:23'),
(125, 72, 31, 14, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-21 01:32:27'),
(126, 72, 14, 31, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-21 03:00:12'),
(127, 72, 31, 14, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-21 04:00:01'),
(128, 72, 14, 31, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-21 05:51:09'),
(129, 72, 31, 14, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-21 06:22:55'),
(130, 72, 14, 31, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-21 06:47:27'),
(131, 72, 31, 14, 'Received everything — the honey is absolutely extraordinary. Pure liquid gold! How do you store the hives in summer heat?', NULL, NULL, 'none', 0, 1, '2026-09-21 07:08:04'),
(132, 75, 7, 18, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-21 06:45:35'),
(133, 75, 18, 7, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-21 07:10:30'),
(134, 75, 7, 18, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-21 08:02:30'),
(135, 75, 18, 7, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-21 09:11:53'),
(136, 75, 7, 18, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-21 10:58:45'),
(137, 75, 18, 7, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-21 12:20:31'),
(138, 77, 30, 9, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-22 04:28:15'),
(139, 77, 9, 30, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-22 05:27:42'),
(140, 77, 30, 9, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-22 07:06:13'),
(141, 77, 9, 30, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-22 08:13:10'),
(142, 82, 28, 10, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-19 14:58:14'),
(143, 82, 10, 28, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-19 15:27:23'),
(144, 85, 33, 2, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-23 11:51:03'),
(145, 85, 2, 33, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-23 13:45:34'),
(146, 85, 33, 2, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-23 15:26:59'),
(147, 85, 2, 33, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-23 16:38:26'),
(148, 85, 33, 2, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-23 18:26:04'),
(149, 85, 2, 33, 'Absolutely! I\'ll note that down and reserve it for you. Just remind me with your order number when you arrive.', NULL, NULL, 'none', 0, 1, '2026-09-23 19:29:51'),
(150, 85, 33, 2, 'Received everything — the honey is absolutely extraordinary. Pure liquid gold! How do you store the hives in summer heat?', NULL, NULL, 'none', 0, 1, '2026-09-23 19:35:14'),
(151, 86, 5, 16, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-22 03:53:07'),
(152, 86, 16, 5, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-22 05:46:09'),
(153, 88, 32, 10, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-20 06:27:43'),
(154, 88, 10, 32, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-20 07:21:14'),
(155, 88, 32, 10, 'JazakAllah! Should I bring exact change or do you accept card payments?', NULL, NULL, 'none', 0, 1, '2026-09-20 08:46:13'),
(156, 88, 10, 32, 'We prefer cash but do have a JazzCash QR code as backup. Exact change is always appreciated 😊', NULL, NULL, 'none', 0, 1, '2026-09-20 09:37:06'),
(157, 88, 32, 10, 'Perfect. Can I also add 1 extra kg of tomatoes to the order? I missed them in the original basket.', NULL, NULL, 'none', 0, 1, '2026-09-20 10:42:05'),
(158, 95, 1, 13, 'Assalam o Alaikum! My order is placed for Saturday — just confirming the pickup spot?', NULL, NULL, 'none', 0, 1, '2026-09-19 18:16:50'),
(159, 95, 13, 1, 'Walaikum Assalam! Yes, everything is confirmed 🌿 We\'re at Stall #{stall} near the main entrance. Look for the green MarketLink banner!', NULL, NULL, 'none', 0, 1, '2026-09-19 19:06:57'),
(160, 58, 8, 13, 'hi', NULL, NULL, 'none', 0, 0, '2026-09-25 11:19:37'),
(161, 83, 20, 27, NULL, 'uploads/chat/chat_83_1790321768_2404076e.jpg', 'Gemini_Generated_Image_1ppnm1ppnm1ppnm1.jpg', 'image', 131669, 0, '2026-09-25 12:36:08'),
(162, 102, 4, 21, 'hi', NULL, NULL, 'none', 0, 0, '2026-09-26 12:04:50'),
(163, 105, 37, 18, 'hi', NULL, NULL, 'none', 0, 1, '2026-09-27 10:42:03'),
(164, 105, 37, 18, 'quickly shipped my order', NULL, NULL, 'none', 0, 1, '2026-09-27 10:42:12'),
(165, 105, 37, 18, NULL, 'uploads/chat/chat_105_1790487743_a0794334.jpg', 'Gemini_Generated_Image_teeof4teeof4teeo.jpg', 'image', 3009976, 1, '2026-09-27 10:42:23');

-- --------------------------------------------------------

--
-- Table structure for table `pickup_slots`
--

CREATE TABLE `pickup_slots` (
  `pickup_slot_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_orders` int(11) DEFAULT 10,
  `status` enum('available','full','closed') DEFAULT 'available',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pickup_slots`
--

INSERT INTO `pickup_slots` (`pickup_slot_id`, `stall_id`, `slot_date`, `start_time`, `end_time`, `max_orders`, `status`, `created_at`) VALUES
(1, 1, '2026-09-23', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(2, 1, '2026-09-23', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(3, 1, '2026-09-23', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(4, 2, '2026-09-23', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(5, 2, '2026-09-23', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(6, 2, '2026-09-23', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(7, 3, '2026-09-23', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(8, 3, '2026-09-23', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(9, 3, '2026-09-23', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(10, 4, '2026-09-23', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(11, 4, '2026-09-23', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(12, 4, '2026-09-23', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(13, 1, '2026-09-24', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(14, 1, '2026-09-24', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(15, 1, '2026-09-24', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(16, 2, '2026-09-24', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(17, 2, '2026-09-24', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(18, 2, '2026-09-24', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(19, 3, '2026-09-24', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(20, 3, '2026-09-24', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(21, 3, '2026-09-24', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(22, 4, '2026-09-24', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(23, 4, '2026-09-24', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(24, 4, '2026-09-24', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(25, 1, '2026-09-25', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(26, 1, '2026-09-25', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(27, 1, '2026-09-25', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(28, 2, '2026-09-25', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(29, 2, '2026-09-25', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(30, 2, '2026-09-25', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(31, 3, '2026-09-25', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(32, 3, '2026-09-25', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(33, 3, '2026-09-25', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(34, 4, '2026-09-25', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(35, 4, '2026-09-25', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(36, 4, '2026-09-25', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(37, 1, '2026-09-26', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(38, 1, '2026-09-26', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(39, 1, '2026-09-26', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(40, 2, '2026-09-26', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(41, 2, '2026-09-26', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(42, 2, '2026-09-26', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(43, 3, '2026-09-26', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(44, 3, '2026-09-26', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(45, 3, '2026-09-26', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(46, 4, '2026-09-26', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(47, 4, '2026-09-26', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(48, 4, '2026-09-26', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(49, 1, '2026-09-27', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(50, 1, '2026-09-27', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(51, 1, '2026-09-27', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(52, 2, '2026-09-27', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(53, 2, '2026-09-27', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(54, 2, '2026-09-27', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(55, 3, '2026-09-27', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(56, 3, '2026-09-27', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(57, 3, '2026-09-27', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(58, 4, '2026-09-27', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(59, 4, '2026-09-27', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(60, 4, '2026-09-27', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(61, 1, '2026-09-28', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(62, 1, '2026-09-28', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(63, 1, '2026-09-28', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(64, 2, '2026-09-28', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(65, 2, '2026-09-28', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(66, 2, '2026-09-28', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(67, 3, '2026-09-28', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(68, 3, '2026-09-28', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(69, 3, '2026-09-28', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(70, 4, '2026-09-28', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(71, 4, '2026-09-28', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(72, 4, '2026-09-28', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(73, 1, '2026-09-29', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(74, 1, '2026-09-29', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(75, 1, '2026-09-29', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(76, 2, '2026-09-29', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(77, 2, '2026-09-29', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(78, 2, '2026-09-29', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(79, 3, '2026-09-29', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(80, 3, '2026-09-29', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(81, 3, '2026-09-29', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(82, 4, '2026-09-29', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(83, 4, '2026-09-29', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(84, 4, '2026-09-29', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(85, 1, '2026-09-30', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(86, 1, '2026-09-30', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(87, 1, '2026-09-30', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(88, 2, '2026-09-30', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(89, 2, '2026-09-30', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(90, 2, '2026-09-30', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(91, 3, '2026-09-30', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(92, 3, '2026-09-30', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(93, 3, '2026-09-30', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(94, 4, '2026-09-30', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(95, 4, '2026-09-30', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(96, 4, '2026-09-30', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(97, 1, '2026-10-01', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(98, 1, '2026-10-01', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(99, 1, '2026-10-01', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(100, 2, '2026-10-01', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(101, 2, '2026-10-01', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(102, 2, '2026-10-01', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(103, 3, '2026-10-01', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(104, 3, '2026-10-01', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(105, 3, '2026-10-01', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(106, 4, '2026-10-01', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(107, 4, '2026-10-01', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(108, 4, '2026-10-01', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(109, 1, '2026-10-02', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(110, 1, '2026-10-02', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(111, 1, '2026-10-02', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(112, 2, '2026-10-02', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(113, 2, '2026-10-02', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(114, 2, '2026-10-02', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(115, 3, '2026-10-02', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(116, 3, '2026-10-02', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(117, 3, '2026-10-02', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(118, 4, '2026-10-02', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(119, 4, '2026-10-02', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(120, 4, '2026-10-02', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(121, 1, '2026-10-03', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(122, 1, '2026-10-03', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(123, 1, '2026-10-03', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(124, 2, '2026-10-03', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(125, 2, '2026-10-03', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(126, 2, '2026-10-03', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(127, 3, '2026-10-03', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(128, 3, '2026-10-03', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(129, 3, '2026-10-03', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(130, 4, '2026-10-03', '07:30:00', '09:30:00', 12, 'available', '2026-09-23 19:58:03'),
(131, 4, '2026-10-03', '09:30:00', '11:30:00', 15, 'available', '2026-09-23 19:58:03'),
(132, 4, '2026-10-03', '11:30:00', '13:30:00', 10, 'available', '2026-09-23 19:58:03'),
(133, 5, '2026-09-25', '08:00:00', '10:30:00', 15, 'available', '2026-09-24 13:50:18'),
(134, 1, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(135, 1, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(136, 1, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(137, 1, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(138, 1, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(139, 1, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(140, 1, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(141, 1, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(142, 1, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(143, 1, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(144, 1, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(145, 1, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(146, 1, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(147, 1, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(148, 1, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(149, 1, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(150, 1, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(151, 2, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(152, 2, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(153, 2, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(154, 2, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(155, 2, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(156, 2, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(157, 2, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(158, 2, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(159, 2, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(160, 2, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(161, 2, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(162, 2, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(163, 2, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(164, 2, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(165, 2, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(166, 2, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(167, 2, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(168, 3, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(169, 3, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(170, 3, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(171, 3, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(172, 3, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(173, 3, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(174, 3, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(175, 3, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(176, 3, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(177, 3, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(178, 3, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(179, 3, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(180, 3, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(181, 3, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(182, 3, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(183, 3, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(184, 3, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(185, 4, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(186, 4, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(187, 4, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(188, 4, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(189, 4, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(190, 4, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(191, 4, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(192, 4, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(193, 4, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(194, 4, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(195, 4, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(196, 4, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(197, 4, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(198, 4, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(199, 4, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(200, 4, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(201, 4, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(202, 5, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(203, 5, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(204, 5, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(205, 5, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(206, 5, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(207, 5, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(208, 5, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(209, 5, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(210, 5, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(211, 5, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(212, 5, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(213, 5, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(214, 5, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(215, 5, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(216, 5, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(217, 5, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(218, 5, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(219, 5, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(220, 5, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(221, 5, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(222, 6, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(223, 6, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(224, 6, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(225, 6, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(226, 6, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(227, 6, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(228, 6, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(229, 6, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(230, 6, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(231, 6, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(232, 6, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(233, 6, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(234, 6, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(235, 6, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(236, 6, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(237, 6, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(238, 6, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(239, 6, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(240, 6, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(241, 6, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(242, 7, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(243, 7, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(244, 7, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(245, 7, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(246, 7, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(247, 7, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(248, 7, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(249, 7, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(250, 7, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(251, 7, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(252, 7, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(253, 7, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(254, 7, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(255, 7, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(256, 7, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(257, 7, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(258, 7, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(259, 7, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(260, 7, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(261, 7, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(262, 8, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(263, 8, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(264, 8, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(265, 8, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(266, 8, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(267, 8, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(268, 8, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(269, 8, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(270, 8, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(271, 8, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(272, 8, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(273, 8, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(274, 8, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(275, 8, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(276, 8, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(277, 8, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(278, 8, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(279, 8, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(280, 8, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(281, 8, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(282, 9, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(283, 9, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(284, 9, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(285, 9, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(286, 9, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(287, 9, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(288, 9, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(289, 9, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(290, 9, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(291, 9, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(292, 9, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(293, 9, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(294, 9, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(295, 9, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(296, 9, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(297, 9, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(298, 9, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(299, 9, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(300, 9, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(301, 9, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(302, 10, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(303, 10, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(304, 10, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(305, 10, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(306, 10, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(307, 10, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(308, 10, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(309, 10, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(310, 10, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(311, 10, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(312, 10, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(313, 10, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(314, 10, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(315, 10, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(316, 10, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(317, 10, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(318, 10, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(319, 10, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(320, 10, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(321, 10, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(322, 11, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(323, 11, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(324, 11, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(325, 11, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(326, 11, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(327, 11, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(328, 11, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(329, 11, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(330, 11, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(331, 11, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(332, 11, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(333, 11, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(334, 11, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(335, 11, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(336, 11, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(337, 11, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(338, 11, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(339, 11, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(340, 11, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(341, 11, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(342, 12, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(343, 12, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(344, 12, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(345, 12, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(346, 12, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(347, 12, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(348, 12, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(349, 12, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(350, 12, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(351, 12, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(352, 12, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(353, 12, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(354, 12, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(355, 12, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(356, 12, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(357, 12, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(358, 12, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(359, 12, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(360, 12, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(361, 12, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(362, 13, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(363, 13, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(364, 13, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(365, 13, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(366, 13, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(367, 13, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(368, 13, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(369, 13, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(370, 13, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(371, 13, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(372, 13, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(373, 13, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(374, 13, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(375, 13, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(376, 13, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(377, 13, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(378, 13, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(379, 13, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(380, 13, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(381, 13, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(382, 14, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(383, 14, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(384, 14, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(385, 14, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(386, 14, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(387, 14, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(388, 14, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(389, 14, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(390, 14, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(391, 14, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(392, 14, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(393, 14, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(394, 14, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(395, 14, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(396, 14, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(397, 14, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(398, 14, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(399, 14, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(400, 14, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(401, 14, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(402, 15, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(403, 15, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(404, 15, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(405, 15, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(406, 15, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(407, 15, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(408, 15, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(409, 15, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(410, 15, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(411, 15, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(412, 15, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(413, 15, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(414, 15, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(415, 15, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(416, 15, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(417, 15, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(418, 15, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(419, 15, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(420, 15, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(421, 15, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(422, 16, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(423, 16, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(424, 16, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(425, 16, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(426, 16, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(427, 16, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(428, 16, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(429, 16, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(430, 16, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(431, 16, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(432, 16, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(433, 16, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(434, 16, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(435, 16, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(436, 16, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(437, 16, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(438, 16, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(439, 16, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(440, 16, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(441, 16, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(442, 17, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(443, 17, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(444, 17, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(445, 17, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(446, 17, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(447, 17, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(448, 17, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(449, 17, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(450, 17, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(451, 17, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(452, 17, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(453, 17, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(454, 17, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(455, 17, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(456, 17, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(457, 17, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(458, 17, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(459, 17, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(460, 17, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(461, 17, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(462, 18, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(463, 18, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(464, 18, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(465, 18, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(466, 18, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(467, 18, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(468, 18, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(469, 18, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(470, 18, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(471, 18, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(472, 18, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(473, 18, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(474, 18, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(475, 18, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(476, 18, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(477, 18, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(478, 18, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(479, 18, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(480, 18, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(481, 18, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(482, 19, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(483, 19, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(484, 19, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(485, 19, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(486, 19, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(487, 19, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(488, 19, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(489, 19, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(490, 19, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(491, 19, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(492, 19, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(493, 19, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(494, 19, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(495, 19, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(496, 19, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(497, 19, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(498, 19, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(499, 19, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(500, 19, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(501, 19, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(502, 20, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(503, 20, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(504, 20, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(505, 20, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(506, 20, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(507, 20, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(508, 20, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(509, 20, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(510, 20, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(511, 20, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(512, 20, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(513, 20, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(514, 20, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(515, 20, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(516, 20, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(517, 20, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(518, 20, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(519, 20, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(520, 20, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(521, 20, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(522, 21, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(523, 21, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(524, 21, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(525, 21, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(526, 21, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(527, 21, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(528, 21, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(529, 21, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(530, 21, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(531, 21, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(532, 21, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(533, 21, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(534, 21, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(535, 21, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(536, 21, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(537, 21, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(538, 21, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(539, 21, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(540, 21, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(541, 21, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(542, 22, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(543, 22, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(544, 22, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(545, 22, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(546, 22, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(547, 22, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(548, 22, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(549, 22, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(550, 22, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(551, 22, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(552, 22, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(553, 22, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(554, 22, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(555, 22, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(556, 22, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(557, 22, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(558, 22, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(559, 22, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(560, 22, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(561, 22, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(562, 23, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(563, 23, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(564, 23, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(565, 23, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(566, 23, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(567, 23, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(568, 23, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(569, 23, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(570, 23, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(571, 23, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(572, 23, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(573, 23, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(574, 23, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(575, 23, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(576, 23, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(577, 23, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47');
INSERT INTO `pickup_slots` (`pickup_slot_id`, `stall_id`, `slot_date`, `start_time`, `end_time`, `max_orders`, `status`, `created_at`) VALUES
(578, 23, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(579, 23, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(580, 23, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(581, 23, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(582, 24, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(583, 24, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(584, 24, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(585, 24, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(586, 24, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(587, 24, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(588, 24, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(589, 24, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(590, 24, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(591, 24, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(592, 24, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(593, 24, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(594, 24, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(595, 24, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(596, 24, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(597, 24, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(598, 24, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(599, 24, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(600, 24, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(601, 24, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(602, 25, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(603, 25, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(604, 25, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(605, 25, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(606, 25, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(607, 25, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(608, 25, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(609, 25, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(610, 25, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(611, 25, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(612, 25, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(613, 25, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(614, 25, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(615, 25, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(616, 25, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(617, 25, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(618, 25, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(619, 25, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(620, 25, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(621, 25, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(622, 26, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(623, 26, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(624, 26, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(625, 26, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(626, 26, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(627, 26, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(628, 26, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(629, 26, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(630, 26, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(631, 26, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(632, 26, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(633, 26, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(634, 26, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(635, 26, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(636, 26, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(637, 26, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(638, 26, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(639, 26, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(640, 26, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(641, 26, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(642, 27, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(643, 27, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(644, 27, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(645, 27, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(646, 27, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(647, 27, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(648, 27, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(649, 27, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(650, 27, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(651, 27, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(652, 27, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(653, 27, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(654, 27, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(655, 27, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(656, 27, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(657, 27, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(658, 27, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(659, 27, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(660, 27, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(661, 27, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(662, 28, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(663, 28, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(664, 28, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(665, 28, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(666, 28, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(667, 28, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(668, 28, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(669, 28, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(670, 28, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(671, 28, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(672, 28, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(673, 28, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(674, 28, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(675, 28, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(676, 28, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(677, 28, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(678, 28, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(679, 28, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(680, 28, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(681, 28, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(682, 29, '2026-09-26', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(683, 29, '2026-09-26', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(684, 29, '2026-09-26', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(685, 29, '2026-09-26', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(686, 29, '2026-09-27', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(687, 29, '2026-09-27', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(688, 29, '2026-09-27', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(689, 29, '2026-09-27', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(690, 29, '2026-10-10', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(691, 29, '2026-10-10', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(692, 29, '2026-10-10', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(693, 29, '2026-10-10', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(694, 29, '2026-10-11', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(695, 29, '2026-10-11', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(696, 29, '2026-10-11', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(697, 29, '2026-10-11', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(698, 29, '2026-10-02', '07:00:00', '08:30:00', 10, 'available', '2026-09-24 23:06:47'),
(699, 29, '2026-10-02', '08:30:00', '10:00:00', 10, 'available', '2026-09-24 23:06:47'),
(700, 29, '2026-10-02', '10:00:00', '11:30:00', 10, 'available', '2026-09-24 23:06:47'),
(701, 29, '2026-10-02', '11:30:00', '13:00:00', 10, 'available', '2026-09-24 23:06:47'),
(702, 5, '2026-09-26', '08:00:00', '10:30:00', 15, 'available', '2026-09-25 16:35:40'),
(703, 30, '2026-09-26', '08:00:00', '10:30:00', 15, 'available', '2026-09-25 16:36:13'),
(704, 30, '2026-09-26', '08:00:00', '10:30:00', 15, 'available', '2026-09-25 16:36:22'),
(705, 31, '2026-09-26', '08:00:00', '10:30:00', 15, 'available', '2026-09-25 16:49:58'),
(706, 32, '2026-10-01', '08:00:00', '10:30:00', 15, 'available', '2026-09-27 10:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `platform_announcements`
--

CREATE TABLE `platform_announcements` (
  `announcement_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `target_role` enum('all','farmer','customer') DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_announcements`
--

INSERT INTO `platform_announcements` (`announcement_id`, `admin_id`, `title`, `content`, `target_role`, `is_active`, `created_at`) VALUES
(1, 4, 'Market Closed', 'Market may be closed on sunday', 'all', 1, '2026-09-24 13:47:33'),
(2, 4, 'New Seasonal Produce Alert: Mango Season Has Arrived! 🥭', 'Certified organic Anwar Ratol and Chaunsa mangoes are now available from our Sahiwal and Multan farm partners. Order before Friday 6 PM for guaranteed Saturday pickup.', 'all', 1, '2026-09-24 23:06:48'),
(3, 4, '10 Reasons to Choose MarketLink Over Supermarkets 🌿', 'Did you know your supermarket produce travels an average of 14 days before reaching your plate? MarketLink guarantees same-morning harvest. Read our blog for the full comparison.', 'customer', 1, '2026-09-24 23:06:48'),
(4, 4, 'Farmer Training Webinar: Soil Health & Organic Certification 📚', 'Join us this Thursday 7 PM for a free live session on Punjab Food Authority organic certification process and soil amendment strategies from expert agri-consultants.', 'farmer', 1, '2026-09-24 23:06:48'),
(5, 4, 'MarketLink Hits 1000 Pickup Orders Milestone! 🎊', 'Alhamdulillah! Our community has collectively purchased over 1,000 pre-orders worth of fresh local produce this season. Thank you farmers and families for making this possible!', 'all', 1, '2026-09-24 23:06:48'),
(6, 4, 'Stall Photography Day — Get Professional Product Photos FREE 📸', 'Our team will visit registered stalls over the next two weekends to take high-quality product and stall photos. Sign up through your farmer dashboard to book your slot.', 'farmer', 1, '2026-09-24 23:06:48'),
(7, 4, 'How to Store Your MarketLink Produce for Maximum Freshness 💡', 'Guide: Store leafy greens wrapped in damp newspaper. Keep citrus at room temp. Mushrooms in paper bags only — never plastic. Honey is shelf-stable indefinitely. Full guide on our blog.', 'customer', 1, '2026-09-24 23:06:48'),
(8, 4, 'SCAM ALERT', 'we dont share any otp to you all th otps that you are recieving are fake', 'farmer', 1, '2026-09-27 11:03:14');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(30) NOT NULL,
  `is_recurring_template` tinyint(1) DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `farmer_id`, `category_id`, `product_name`, `description`, `unit`, `is_recurring_template`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Farm Fresh Organic Heirloom Tomatoes', 'Vine-ripened, juicy heirloom tomatoes bursting with natural acidity and sweetness. Pesticide-free, harvested morning of market.', 'kg', 1, 'assets/images/products/catalog/tomatoes.jpg', '2026-09-23 19:58:03', '2026-09-25 14:12:39'),
(2, 2, 1, 'Crisp Farm-Grown Crunchy Carrots', 'Sweet, vibrant orange root carrots pulled straight from nutrient-rich organic soil. Bundled with fresh leafy tops.', 'kg', 1, 'assets/images/products/catalog/carrots.jpg', '2026-09-23 19:58:03', '2026-09-25 14:12:39'),
(3, 2, 2, 'Crisp Mountain Gala Apples', 'Direct from the orchard. Crisp, fragrant, and hand-selected for natural sweetness and crunch without wax coatings.', 'kg', 1, 'assets/images/products/catalog/apples.jpg', '2026-09-23 19:58:03', '2026-09-25 14:12:39'),
(4, 2, 5, 'Raw Acacia Wildflower Honey (500g)', 'Unpasteurized, pure cold-extracted honeycomb nectar with delicate floral aroma. Direct from farm apiary.', 'jar', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-23 19:58:03', '2026-09-25 14:12:39'),
(5, 6, 1, 'Tender Baby Spinach & Butterhead Lettuce', 'Hydro-rinsed delicate greens picked at sunrise. Crisp texture, ideal for farm-fresh salads and healthy green smoothies.', 'bunch', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-23 19:58:03', '2026-09-25 14:12:39'),
(6, 6, 3, 'Pasture-Raised Organic Brown Eggs (Dozen)', 'Golden yolks with rich omega content from happy hens roaming open green pastures. Packaged in biodegradable pulp cartons.', 'dozen', 1, 'assets/images/products/catalog/eggs.jpg', '2026-09-23 19:58:03', '2026-09-25 14:12:39'),
(7, 6, 3, 'Pure Grass-Fed Desi Cow Butter (250g)', 'Traditional slow-churned farmhouse butter with natural sweet cream aroma and golden color.', 'pack', 1, 'assets/images/products/catalog/dairy.jpg', '2026-09-23 19:58:03', '2026-09-25 14:12:39'),
(8, 6, 4, 'Rustic Sourdough Boule (Artisanal)', 'Naturally fermented 36-hour sourdough loaf baked on market dawn with a caramelized crust and soft open crumb.', 'loaf', 1, 'assets/images/products/catalog/bread.jpg', '2026-09-23 19:58:03', '2026-09-25 14:12:39'),
(9, 6, 6, 'Sweet Basil, Mint & Italian Coriander Trio', 'Fragrant live herb bouquet with root balls intact. Keep fresh in water or plant directly in your kitchen garden.', 'bundle', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-23 19:58:03', '2026-09-25 14:12:39'),
(11, 9, 5, 'Raw Honey', 'Very benifical honey', 'jar', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 10:48:06', '2026-09-25 14:12:39'),
(12, 10, 1, 'Heirloom Rainbow Tomatoes', 'Sun-ripened heirloom varieties — Brandywine, Green Zebra, Black Krim. Zero artificial fertilizers, harvested 5 AM market morning.', 'per kg', 1, 'assets/images/products/catalog/tomatoes.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(13, 10, 1, 'Bell Peppers Mixed Box', 'Crisp red, yellow and orange California Wonder bell peppers. Thick-walled, sweet and crunchy, excellent for stuffing or raw salads.', 'per 500g', 1, 'assets/images/products/catalog/tomatoes.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(14, 10, 2, 'Wild Mixed Berries Punnet', 'Seasonal mix of fresh strawberries, mulberries, and blackberries. Pesticide-free, sun-grown, unsweetened natural tartness.', 'per 250g', 1, 'assets/images/products/catalog/apples.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(15, 10, 5, 'Cold-Pressed Olive Oil', 'First cold-press extra virgin from Moroccan Picholine olives cultivated in the Pothohar region. Polyphenol-rich, bright grassy finish.', 'per 500ml', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(16, 10, 1, 'Organic Baby Spinach', 'Tender baby spinach leaves, pre-washed triple times. Harvested at 25-day maturity for peak iron and vitamin K content.', 'per 250g', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(17, 11, 1, 'Butter Lettuce Head', 'Hydroponic Buttercrunch lettuce with velvety soft leaves, no soil contact, grown in mineral-balanced nutrient solution.', 'per head', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(18, 11, 1, 'Cherry Tomato Cluster', 'Hydroponic cherry tomatoes on the vine — Sungold and Sweet 100 varieties. Brix 8+, naturally sweet, cracked-free harvest.', 'per 500g', 1, 'assets/images/products/catalog/tomatoes.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(19, 11, 6, 'Fresh Basil Bunch', 'Genovese basil harvested same morning. Vibrant deep green, intensely aromatic. Perfect for pesto, bruschetta or caprese.', 'per bunch', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(20, 11, 6, 'Microgreens Assorted Tray', 'Mixed microgreens — sunflower, radish, pea shoots, broccoli. Harvested at cotyledon stage for maximum nutrient density (40× more).', 'per 100g', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(21, 11, 1, 'Kale Bunch (Curly & Lacinato)', 'Fresh-cut kale — both curly and dinosaur varieties. Rich in Vitamin C, K and antioxidants. Stems intact, soil-free hydroponic root.', 'per bunch', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(22, 12, 3, 'Fresh Whole Milk (A4 Desi)', 'Unpasteurized raw milk from certified A4 Holstein-Friesian cows fed exclusively on organic grass. Collect before 7 AM.', 'per liter', 1, 'assets/images/products/catalog/dairy.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(23, 12, 3, 'Artisan Cultured Butter', 'Churned fresh from 48-hour cultured cream. European-style, 82% fat, deep golden colour from beta-carotene-rich grass diet.', 'per 200g', 1, 'assets/images/products/catalog/dairy.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(24, 12, 3, 'Farm-Fresh Desi Eggs (Dozen)', 'Free-range desi eggs from hens roaming 5 acres of pasture. Rich golden yolks, thick whites, naturally omega-3 enhanced diet.', 'per dozen', 1, 'assets/images/products/catalog/eggs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(25, 12, 3, 'Homemade Paneer Block', 'Fresh set paneer made daily from whole raw milk. Firm texture, mild sweet flavour. No preservatives or additives whatsoever.', 'per 500g', 1, 'assets/images/products/catalog/dairy.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(26, 12, 3, 'Greek-Style Dahi (Yogurt)', 'Thick-strained yogurt culture-set overnight. Protein-dense, probiotic-rich, 0% added sugar. Pairs with honey or granola perfectly.', 'per 500g', 1, 'assets/images/products/catalog/dairy.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(27, 13, 6, 'Thai Basil Bunch', 'Authentic Ocimum tenuiflorum with clove-anise aroma. Perfect for stir-fries, Vietnamese pho, and Southeast Asian curries.', 'per bunch', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(28, 13, 6, 'Fresh Mint Sprigs (Pudina)', 'Spearmint and peppermint varieties grown in certified organic clay soil. Intensely fragrant, ideal for raita, mojito, chutneys.', 'per bunch', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(29, 13, 6, 'Lemon Thyme Potted', 'French thyme with bright citrus notes — potted in recycled terracotta. Living herb plant, re-harvestable 8+ times.', 'per pot', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(30, 13, 6, 'Fresh Dill Bunch (Soa)', 'Tender feathery dill, freshly snipped. Traditional Pakistani dill used in gosht, fish curries, and cooling raita.', 'per bunch', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(31, 13, 6, 'Aloe Vera Gel Leaf', 'Mature 3-year Barbadensis aloe leaf, 40–50 cm. Scoop fresh gel for topical skincare, juice, or digestive remedy.', 'per piece', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(32, 14, 2, 'Kinnow Mandarin Box', 'Premium Grade A Kinnow mandarins from Sargodha orchards. Brix 12+, seedless segments, deep orange zest rich in flavonoids.', 'per 2kg', 1, 'assets/images/products/catalog/apples.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(33, 14, 2, 'Blood Orange Bag', 'Moro blood oranges with dramatic crimson flesh from anthocyanin content. Tangy-sweet, ideal for fresh-squeezed ruby juice.', 'per 1kg', 1, 'assets/images/products/catalog/apples.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(34, 14, 2, 'Pomegranate Whole', 'Large Wonderful-variety pomegranates from Balochistan highland orchards. 650+ arils per fruit, intensely crimson and jewel-like.', 'per piece', 1, 'assets/images/products/catalog/apples.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(35, 14, 2, 'Fresh Lemon Bag', 'Eureka lemons — thin skin, high juice content, zero wax coating. Squeeze-ready with bright acidic citrus oil in the zest.', 'per 1kg', 1, 'assets/images/products/catalog/apples.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(36, 15, 5, 'Raw Sidr Honey (Beri)', 'Single-origin raw Sidr honey from Thal Desert hives. Intensely thick, amber-dark, with distinct caramel-molasses undertones.', 'per 500g', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(37, 15, 5, 'Wild Acacia Flower Honey', 'Water-white runny Acacia honey — the mildest and most delicate in the collection. Naturally anti-crystallising, diabetic-friendly.', 'per 500g', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(38, 15, 5, 'Wildflower Multifloral Honey', 'Seasonal blend from 80 hives foraging on wild mustard, citrus blossom, and eucalyptus. Complex floral bouquet, medium sweetness.', 'per 500g', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(39, 15, 5, 'Royal Jelly Fresh (5g)', 'Freshly harvested royal jelly, refrigerated continuously. Protein-rich bee secretion with natural amino acids, vitamins, fatty acids.', 'per 5g', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(40, 16, 17, 'Stone-Ground Whole Wheat Atta', 'Traditional chakki-ground whole wheat from Chakwal district heritage wheat. High bran fibre content, low GI, natural nuttiness.', 'per 5kg', 1, 'assets/images/products/catalog/bread.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(41, 16, 17, 'Heirloom Brown Basmati Rice', 'Aged 2-year brown basmati from Kalar Syedan. Whole bran intact. Nutty, earthy aroma. Pair with biryani or pilaf for deep flavour.', 'per 1kg', 1, 'assets/images/products/catalog/bread.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(42, 16, 17, 'Ancient Grain Granola Mix', 'Oats, quinoa puffs, pumpkin seeds, dried mulberries, cinnamon. Baked fresh weekly — no refined sugar, sweetened with date syrup.', 'per 400g', 1, 'assets/images/products/catalog/bread.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(43, 16, 17, 'Multi-Grain Flour Blend', 'Equal blend of whole wheat, barley, millet (bajra) and oat flour. Ideal for diabetics, high-fibre rotis, pancakes and bread loaves.', 'per 2kg', 1, 'assets/images/products/catalog/bread.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(44, 17, 15, 'Mixed Vegetable Achaar', 'Sun-dried and mustard-oil-cured traditional achaar — mango, lime, carrot, green chili. No synthetic preservatives, 12-month cure.', 'per 500g', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(45, 17, 15, 'Mango Murabba (Kairi Preserve)', 'Raw kairi mango cooked in jaggery and spices. Traditional grandmother recipe — cardamom, saffron, rose water infused sweetness.', 'per 500g', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(46, 17, 15, 'Apple Cider Vinegar Raw', 'Unfiltered, unpasteurized ACV with visible mother-of-vinegar. Made from Murree hill-grown apple scrap fermentation. 5% acidity.', 'per 500ml', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(47, 17, 15, 'Chilli Lime Fermented Salsa', 'Lacto-fermented fresh salsa with roasted cumin, Thai chili and lime zest. Probiotic-rich, gut-friendly condiment. 14-day culture.', 'per 250g', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(48, 17, 5, 'Date Syrup & Black Seed Oil Kit', 'Ajwa date syrup (250ml) paired with cold-pressed kalonji oil (100ml). The traditional Prophetic medicine wellness combo.', 'per set', 1, 'assets/images/products/catalog/honey.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(49, 18, 6, 'Fresh Methi (Fenugreek) Bunch', 'Tender fenugreek leaves — the most flavourful of the season. Triple-washed, roots trimmed. Ready for methi gosht or parathas.', 'per bunch', 1, 'assets/images/products/catalog/herbs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(50, 18, 1, 'Baby Radish Bunch', 'Crisp French Breakfast radishes — elongated, mild sweetness. Sliced raw into salads or pickled quickly in rice vinegar.', 'per bunch', 1, 'assets/images/products/catalog/carrots.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(51, 18, 1, 'Bitter Gourd (Karela) Box', 'Premium slim karela, deep ridged and bright green — naturally more bitter than regular variety. Excellent for diabetic diet control.', 'kg', 0, 'assets/images/products/catalog/carrots.jpg', '2026-09-24 23:06:47', '2026-09-27 10:47:40'),
(52, 18, 1, 'Fresh Arvi (Taro Root)', 'Freshly dug taro corms from alluvial soil plots. Creamy starchy texture when cooked. No mud, pre-cleaned at harvest.', 'per 1kg', 1, 'assets/images/products/catalog/carrots.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(53, 19, 16, 'Free-Range Desi Murgi (Live wt)', 'Pure Aseel and Desi crossbreed hen, 1.2–1.8 kg live weight. Raised on grain, kitchen scraps, pasture insects. Pre-order required.', 'per piece', 1, 'assets/images/products/catalog/poultry.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(54, 19, 3, 'Pasture Eggs (Half Dozen)', 'Golden yolk eggs from hens with daily outdoor access. Omega-3 enhanced diet: flaxseed, marigold petals for deep orange colour.', 'per 6 pcs', 1, 'assets/images/products/catalog/eggs.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(55, 19, 16, 'Marinated Seekh Kebab Mix', 'Ground desi chicken breast marinated in house-blend roasted spices, fresh ginger and charcoal char flavour. Grill-ready.', 'per 500g', 1, 'assets/images/products/catalog/poultry.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(56, 20, 13, 'Oyster Mushroom Punnet', 'Fresh ivory Pearl and Golden oyster mushrooms harvested from certified organic wheat straw. Delicate, nutty, velvety texture.', 'per 250g', 1, 'assets/images/products/catalog/mushrooms.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(57, 20, 13, 'Shiitake Mushrooms', 'Premium lentinan-rich Shiitake — meaty texture, umami depth. Grown on oak-wood block substrate, high D-vitamin from UV exposure.', 'per 200g', 1, 'assets/images/products/catalog/mushrooms.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(58, 20, 13, 'Lion\'s Mane Whole', 'Single Lion\'s Mane cluster 150-250g. Brain-boosting hericenones and erinacines content. Tastes like crab meat when sautéed in butter.', 'per piece', 1, 'assets/images/products/catalog/mushrooms.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(59, 20, 13, 'Dried Mushroom Mix (Assorted)', 'Sun-dehydrated Portobello, Shiitake and Oyster blend. Rehydrates in 20 min, intensified umami. 8× shelf-stable concentrate.', 'per 100g', 1, 'assets/images/products/catalog/mushrooms.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(60, 21, 14, 'Chia Seeds Pouch', 'Certified organic white chia seeds from Cholistan farms. Complete plant protein with omega-3 ALA. Shelf stable 24 months.', 'per 250g', 1, 'assets/images/products/catalog/seeds.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(61, 21, 14, 'Moringa Leaf Powder', 'Cold-dehydrated Moringa oleifera leaf powder from Multan farms. Contains 92 nutrients, 46 antioxidants. Add to smoothies, atta, soup.', 'per 100g', 1, 'assets/images/products/catalog/seeds.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(62, 21, 14, 'Raw Cacao Nibs', 'Unroasted, stone-crushed Theobroma cacao nibs. 70%+ flavanols, zero sugar added. Intense bitter-chocolate crunch — smoothies or snacks.', 'per 150g', 1, 'assets/images/products/catalog/seeds.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(63, 21, 14, 'Hemp Seed Hearts', 'Hulled hemp hearts — mild nutty flavor, highest plant-based complete protein (33g/100g). Sprinkle on salads, yogurt, avocado toast.', 'per 250g', 1, 'assets/images/products/catalog/seeds.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39'),
(64, 21, 2, 'Dried Mulberries (Toot)', 'Sun-dried white mulberries from Baltistan highland orchards. Natural iron source, caramel-sweet, moreish superfood snack.', 'per 200g', 1, 'assets/images/products/catalog/apples.jpg', '2026-09-24 23:06:47', '2026-09-25 14:12:39');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `category_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`category_id`, `category_name`, `description`, `category_image`, `is_active`, `created_at`) VALUES
(1, 'Fresh Vegetables', 'Crisp, organically grown root and leafy vegetables picked daily.', 'assets/images/cat-vegetables.svg', 1, '2026-09-23 19:58:03'),
(2, 'Orchard Fruits', 'Sweet, sun-ripened tree fruits and seasonal berries.', 'assets/images/cat-fruits.svg', 1, '2026-09-23 19:58:03'),
(3, 'Dairy & Farm Eggs', 'Pasture-raised organic eggs, fresh cow milk, and cultured butter.', 'assets/images/cat-dairy.svg', 1, '2026-09-23 19:58:03'),
(4, 'Farm Bakery', 'Stone-ground sourdough bread, artisanal pies, and fresh wholewheat rolls.', 'assets/images/cat-bakery.svg', 1, '2026-09-23 19:58:03'),
(5, 'Honey & Preserves', 'Raw wildflower honey, pure bee pollen, and small-batch fruit jams.', 'assets/images/cat-honey.svg', 1, '2026-09-23 19:58:03'),
(6, 'Aromatic Herbs', 'Fresh-cut culinary herbs and fragrant seasoning greens.', 'assets/images/cat-herbs.svg', 1, '2026-09-23 19:58:03'),
(13, 'Mushrooms & Fungi', 'Exotic and gourmet mushrooms cultivated fresh', NULL, 1, '2026-09-24 23:06:47'),
(14, 'Superfoods & Seeds', 'Chia, flax, hemp, moringa and ancient grains', NULL, 1, '2026-09-24 23:06:47'),
(15, 'Artisan Preserves', 'Achaar, chutneys, jams and fermented goods', NULL, 1, '2026-09-24 23:06:47'),
(16, 'Free-Range Poultry', 'Farm eggs and free-range chicken', NULL, 1, '2026-09-24 23:06:47'),
(17, 'Whole Grains & Flour', 'Stone-ground flours, rice and millet', NULL, 1, '2026-09-24 23:06:47'),
(18, 'traf', 'auehgoueiouiaheoui', '', 1, '2026-09-26 13:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_url`, `is_primary`, `display_order`, `created_at`) VALUES
(1, 12, 'assets/images/products/catalog/tomatoes.jpg', 1, 1, '2026-09-24 23:06:47'),
(2, 13, 'assets/images/products/catalog/tomatoes.jpg', 1, 1, '2026-09-24 23:06:47'),
(3, 14, 'assets/images/products/catalog/apples.jpg', 1, 1, '2026-09-24 23:06:47'),
(4, 15, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(5, 16, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(6, 17, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(7, 18, 'assets/images/products/catalog/tomatoes.jpg', 1, 1, '2026-09-24 23:06:47'),
(8, 19, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(9, 20, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(10, 21, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(11, 22, 'assets/images/products/catalog/dairy.jpg', 1, 1, '2026-09-24 23:06:47'),
(12, 23, 'assets/images/products/catalog/dairy.jpg', 1, 1, '2026-09-24 23:06:47'),
(13, 24, 'assets/images/products/catalog/eggs.jpg', 1, 1, '2026-09-24 23:06:47'),
(14, 25, 'assets/images/products/catalog/dairy.jpg', 1, 1, '2026-09-24 23:06:47'),
(15, 26, 'assets/images/products/catalog/dairy.jpg', 1, 1, '2026-09-24 23:06:47'),
(16, 27, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(17, 28, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(18, 29, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(19, 30, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(20, 31, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(21, 32, 'assets/images/products/catalog/apples.jpg', 1, 1, '2026-09-24 23:06:47'),
(22, 33, 'assets/images/products/catalog/apples.jpg', 1, 1, '2026-09-24 23:06:47'),
(23, 34, 'assets/images/products/catalog/apples.jpg', 1, 1, '2026-09-24 23:06:47'),
(24, 35, 'assets/images/products/catalog/apples.jpg', 1, 1, '2026-09-24 23:06:47'),
(25, 36, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(26, 37, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(27, 38, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(28, 39, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(29, 40, 'assets/images/products/catalog/bread.jpg', 1, 1, '2026-09-24 23:06:47'),
(30, 41, 'assets/images/products/catalog/bread.jpg', 1, 1, '2026-09-24 23:06:47'),
(31, 42, 'assets/images/products/catalog/bread.jpg', 1, 1, '2026-09-24 23:06:47'),
(32, 43, 'assets/images/products/catalog/bread.jpg', 1, 1, '2026-09-24 23:06:47'),
(33, 44, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(34, 45, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(35, 46, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(36, 47, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(37, 48, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:06:47'),
(38, 49, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:06:47'),
(39, 50, 'assets/images/products/catalog/carrots.jpg', 1, 1, '2026-09-24 23:06:47'),
(40, 51, 'assets/images/products/catalog/carrots.jpg', 1, 1, '2026-09-24 23:06:47'),
(41, 52, 'assets/images/products/catalog/carrots.jpg', 1, 1, '2026-09-24 23:06:47'),
(42, 53, 'assets/images/products/catalog/poultry.jpg', 1, 1, '2026-09-24 23:06:47'),
(43, 54, 'assets/images/products/catalog/eggs.jpg', 1, 1, '2026-09-24 23:06:47'),
(44, 55, 'assets/images/products/catalog/poultry.jpg', 1, 1, '2026-09-24 23:06:47'),
(45, 56, 'assets/images/products/catalog/mushrooms.jpg', 1, 1, '2026-09-24 23:06:47'),
(46, 57, 'assets/images/products/catalog/mushrooms.jpg', 1, 1, '2026-09-24 23:06:47'),
(47, 58, 'assets/images/products/catalog/mushrooms.jpg', 1, 1, '2026-09-24 23:06:47'),
(48, 59, 'assets/images/products/catalog/mushrooms.jpg', 1, 1, '2026-09-24 23:06:47'),
(49, 60, 'assets/images/products/catalog/seeds.jpg', 1, 1, '2026-09-24 23:06:47'),
(50, 61, 'assets/images/products/catalog/seeds.jpg', 1, 1, '2026-09-24 23:06:47'),
(51, 62, 'assets/images/products/catalog/seeds.jpg', 1, 1, '2026-09-24 23:06:47'),
(52, 63, 'assets/images/products/catalog/seeds.jpg', 1, 1, '2026-09-24 23:06:47'),
(53, 64, 'assets/images/products/catalog/apples.jpg', 1, 1, '2026-09-24 23:06:47'),
(54, 1, 'assets/images/products/catalog/tomatoes.jpg', 1, 1, '2026-09-24 23:43:56'),
(55, 2, 'assets/images/products/catalog/carrots.jpg', 1, 1, '2026-09-24 23:43:56'),
(56, 3, 'assets/images/products/catalog/apples.jpg', 1, 1, '2026-09-24 23:43:56'),
(57, 4, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:43:56'),
(58, 5, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:43:56'),
(59, 6, 'assets/images/products/catalog/eggs.jpg', 1, 1, '2026-09-24 23:43:56'),
(60, 7, 'assets/images/products/catalog/dairy.jpg', 1, 1, '2026-09-24 23:43:56'),
(61, 8, 'assets/images/products/catalog/bread.jpg', 1, 1, '2026-09-24 23:43:56'),
(62, 9, 'assets/images/products/catalog/herbs.jpg', 1, 1, '2026-09-24 23:43:56'),
(63, 11, 'assets/images/products/catalog/honey.jpg', 1, 1, '2026-09-24 23:43:56'),
(64, 65, 'uploads/products/product_9_1790337655_2868.jpg', 1, 1, '2026-09-25 17:00:55');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `is_moderated` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`review_id`, `order_id`, `product_id`, `customer_id`, `rating`, `comment`, `is_moderated`, `created_at`) VALUES
(1, 1, 2, 1, 5, 'Crispest carrots I have had in months. Highly recommended!', 1, '2026-09-22 19:58:36'),
(2, 4, 11, 8, 5, 'taste was good', 1, '2026-09-24 11:07:20'),
(3, 11, 5, 27, 5, 'The microgreens were the freshest I\'ve ever seen outside of a restaurant kitchen. Nutritional powerhouse in a tiny punnet!', 1, '2026-09-24 23:06:48'),
(4, 11, 9, 27, 5, 'The microgreens were the freshest I\'ve ever seen outside of a restaurant kitchen. Nutritional powerhouse in a tiny punnet!', 1, '2026-09-24 23:06:48'),
(5, 12, 3, 22, 5, 'The mango murabba recipe is divine. Could taste saffron and rose water in the background. This belongs in a gourmet food shop, not just a farmers market!', 1, '2026-09-24 23:06:48'),
(6, 12, 1, 22, 5, 'The microgreens were the freshest I\'ve ever seen outside of a restaurant kitchen. Nutritional powerhouse in a tiny punnet!', 1, '2026-09-24 23:06:48'),
(7, 12, 2, 22, 5, 'Lion\'s Mane mushroom sautéed in olive oil and thyme — tasted exactly like perfectly cooked seafood. Absolutely magical ingredient.', 1, '2026-09-24 23:06:48'),
(8, 18, 21, 5, 4, 'Fresh basil smelled incredible right out of the bag. Only 4 stars because a few leaves had slight browning at the edges.', 1, '2026-09-24 23:08:11'),
(9, 24, 50, 35, 5, 'These are the most flavourful tomatoes I have ever eaten in Pakistan. Heirloom varieties deserve all the hype and more.', 1, '2026-09-24 23:08:11'),
(10, 25, 35, 33, 4, 'Good quality kinnow, slightly less sweet than last week\'s batch but very fresh with no wax coating as promised.', 1, '2026-09-24 23:08:11'),
(11, 28, 64, 24, 5, 'Chia seeds beautifully packaged, clearly labelled with origin. Mixed into overnight oats — perfect texture and mild earthy taste.', 1, '2026-09-24 23:08:11'),
(12, 29, 2, 30, 5, 'Moringa powder genuinely boosted my energy within 2 weeks. Added to morning smoothies. Third reorder and counting!', 1, '2026-09-24 23:08:11'),
(13, 29, 4, 30, 5, 'Chia seeds beautifully packaged, clearly labelled with origin. Mixed into overnight oats — perfect texture and mild earthy taste.', 1, '2026-09-24 23:08:11'),
(14, 30, 1, 24, 4, 'Good quality kinnow, slightly less sweet than last week\'s batch but very fresh with no wax coating as promised.', 1, '2026-09-24 23:08:11'),
(15, 31, 59, 8, 5, 'Chia seeds beautifully packaged, clearly labelled with origin. Mixed into overnight oats — perfect texture and mild earthy taste.', 1, '2026-09-24 23:08:11'),
(16, 34, 13, 36, 5, 'Moringa powder genuinely boosted my energy within 2 weeks. Added to morning smoothies. Third reorder and counting!', 1, '2026-09-24 23:08:11'),
(17, 34, 14, 36, 4, 'Good quality kinnow, slightly less sweet than last week\'s batch but very fresh with no wax coating as promised.', 1, '2026-09-24 23:08:11'),
(18, 34, 16, 36, 4, 'Good quality kinnow, slightly less sweet than last week\'s batch but very fresh with no wax coating as promised.', 1, '2026-09-24 23:08:11'),
(19, 34, 15, 36, 5, 'Oyster mushrooms had the most delicate texture. Pan-fried in olive oil with garlic — restaurant quality at home. Absolutely outstanding.', 1, '2026-09-24 23:08:11'),
(20, 35, 34, 3, 4, 'Fresh basil smelled incredible right out of the bag. Only 4 stars because a few leaves had slight browning at the edges.', 1, '2026-09-24 23:08:11'),
(21, 35, 35, 3, 5, 'Oyster mushrooms had the most delicate texture. Pan-fried in olive oil with garlic — restaurant quality at home. Absolutely outstanding.', 1, '2026-09-24 23:08:11'),
(22, 35, 33, 3, 5, 'These are the most flavourful tomatoes I have ever eaten in Pakistan. Heirloom varieties deserve all the hype and more.', 1, '2026-09-24 23:08:11'),
(23, 35, 32, 3, 5, 'Raw honey unlike anything available in any store. Thick, dark, intensely floral — nature\'s perfect sweetener.', 1, '2026-09-24 23:08:11'),
(24, 37, 55, 26, 4, 'Fresh basil smelled incredible right out of the bag. Only 4 stars because a few leaves had slight browning at the edges.', 1, '2026-09-24 23:08:11'),
(25, 37, 54, 26, 5, 'Artisan butter is a revelation. Spread on fresh roti it melts instantly and the flavour is incomparably rich. Never going back to supermarket butter.', 1, '2026-09-24 23:08:11'),
(26, 40, 61, 27, 4, 'Fresh basil smelled incredible right out of the bag. Only 4 stars because a few leaves had slight browning at the edges.', 1, '2026-09-24 23:08:11'),
(27, 40, 62, 27, 5, 'Raw honey unlike anything available in any store. Thick, dark, intensely floral — nature\'s perfect sweetener.', 1, '2026-09-24 23:08:11'),
(28, 40, 63, 27, 5, 'Raw honey unlike anything available in any store. Thick, dark, intensely floral — nature\'s perfect sweetener.', 1, '2026-09-24 23:08:11'),
(29, 40, 64, 27, 5, 'These are the most flavourful tomatoes I have ever eaten in Pakistan. Heirloom varieties deserve all the hype and more.', 1, '2026-09-24 23:08:11'),
(30, 41, 32, 23, 5, 'These are the most flavourful tomatoes I have ever eaten in Pakistan. Heirloom varieties deserve all the hype and more.', 1, '2026-09-24 23:08:11'),
(31, 41, 35, 23, 5, 'Oyster mushrooms had the most delicate texture. Pan-fried in olive oil with garlic — restaurant quality at home. Absolutely outstanding.', 1, '2026-09-24 23:08:11'),
(32, 41, 33, 23, 5, 'Raw honey unlike anything available in any store. Thick, dark, intensely floral — nature\'s perfect sweetener.', 1, '2026-09-24 23:08:11'),
(33, 44, 61, 23, 5, 'Chia seeds beautifully packaged, clearly labelled with origin. Mixed into overnight oats — perfect texture and mild earthy taste.', 1, '2026-09-24 23:08:11'),
(34, 44, 64, 23, 5, 'Artisan butter is a revelation. Spread on fresh roti it melts instantly and the flavour is incomparably rich. Never going back to supermarket butter.', 1, '2026-09-24 23:08:11'),
(35, 44, 63, 23, 5, 'Artisan butter is a revelation. Spread on fresh roti it melts instantly and the flavour is incomparably rich. Never going back to supermarket butter.', 1, '2026-09-24 23:08:11'),
(36, 44, 62, 23, 5, 'These are the most flavourful tomatoes I have ever eaten in Pakistan. Heirloom varieties deserve all the hype and more.', 1, '2026-09-24 23:08:11'),
(37, 45, 28, 5, 5, 'Moringa powder genuinely boosted my energy within 2 weeks. Added to morning smoothies. Third reorder and counting!', 1, '2026-09-24 23:08:11'),
(38, 45, 31, 5, 5, 'Oyster mushrooms had the most delicate texture. Pan-fried in olive oil with garlic — restaurant quality at home. Absolutely outstanding.', 1, '2026-09-24 23:08:11'),
(39, 46, 19, 34, 5, 'Raw honey unlike anything available in any store. Thick, dark, intensely floral — nature\'s perfect sweetener.', 1, '2026-09-24 23:08:11'),
(40, 46, 17, 34, 5, 'Artisan butter is a revelation. Spread on fresh roti it melts instantly and the flavour is incomparably rich. Never going back to supermarket butter.', 1, '2026-09-24 23:08:11'),
(41, 46, 21, 34, 5, 'These are the most flavourful tomatoes I have ever eaten in Pakistan. Heirloom varieties deserve all the hype and more.', 1, '2026-09-24 23:08:11'),
(42, 46, 20, 34, 4, 'Good quality kinnow, slightly less sweet than last week\'s batch but very fresh with no wax coating as promised.', 1, '2026-09-24 23:08:11'),
(43, 51, 56, 28, 5, 'Moringa powder genuinely boosted my energy within 2 weeks. Added to morning smoothies. Third reorder and counting!', 1, '2026-09-24 23:08:11'),
(44, 53, 36, 29, 4, 'Fresh basil smelled incredible right out of the bag. Only 4 stars because a few leaves had slight browning at the edges.', 1, '2026-09-24 23:08:11'),
(45, 53, 38, 29, 5, 'These are the most flavourful tomatoes I have ever eaten in Pakistan. Heirloom varieties deserve all the hype and more.', 1, '2026-09-24 23:08:11'),
(46, 55, 36, 30, 5, 'Chia seeds beautifully packaged, clearly labelled with origin. Mixed into overnight oats — perfect texture and mild earthy taste.', 1, '2026-09-24 23:08:11'),
(47, 56, 36, 25, 4, 'Good quality kinnow, slightly less sweet than last week\'s batch but very fresh with no wax coating as promised.', 1, '2026-09-24 23:08:11'),
(48, 56, 38, 25, 5, 'Raw honey unlike anything available in any store. Thick, dark, intensely floral — nature\'s perfect sweetener.', 1, '2026-09-24 23:08:11'),
(49, 56, 37, 25, 5, 'These are the most flavourful tomatoes I have ever eaten in Pakistan. Heirloom varieties deserve all the hype and more.', 1, '2026-09-24 23:08:11'),
(50, 61, 55, 23, 4, 'Good quality kinnow, slightly less sweet than last week\'s batch but very fresh with no wax coating as promised.', 1, '2026-09-24 23:08:11'),
(51, 61, 53, 23, 5, 'Moringa powder genuinely boosted my energy within 2 weeks. Added to morning smoothies. Third reorder and counting!', 1, '2026-09-24 23:08:11'),
(52, 61, 54, 23, 5, 'Artisan butter is a revelation. Spread on fresh roti it melts instantly and the flavour is incomparably rich. Never going back to supermarket butter.', 1, '2026-09-24 23:08:11'),
(53, 64, 27, 27, 4, 'Good quality kinnow, slightly less sweet than last week\'s batch but very fresh with no wax coating as promised.', 1, '2026-09-24 23:08:11'),
(54, 64, 29, 27, 5, 'These are the most flavourful tomatoes I have ever eaten in Pakistan. Heirloom varieties deserve all the hype and more.', 1, '2026-09-24 23:08:11'),
(55, 67, 22, 25, 4, 'Fresh basil smelled incredible right out of the bag. Only 4 stars because a few leaves had slight browning at the edges.', 1, '2026-09-24 23:08:11'),
(56, 67, 24, 25, 5, 'Moringa powder genuinely boosted my energy within 2 weeks. Added to morning smoothies. Third reorder and counting!', 1, '2026-09-24 23:08:11'),
(57, 68, 46, 27, 5, 'Oyster mushrooms had the most delicate texture. Pan-fried in olive oil with garlic — restaurant quality at home. Absolutely outstanding.', 1, '2026-09-24 23:08:11'),
(58, 68, 47, 27, 5, 'Artisan butter is a revelation. Spread on fresh roti it melts instantly and the flavour is incomparably rich. Never going back to supermarket butter.', 1, '2026-09-24 23:08:11'),
(59, 68, 48, 27, 4, 'Fresh basil smelled incredible right out of the bag. Only 4 stars because a few leaves had slight browning at the edges.', 1, '2026-09-24 23:08:11'),
(60, 70, 27, 30, 5, 'Oyster mushrooms had the most delicate texture. Pan-fried in olive oil with garlic — restaurant quality at home. Absolutely outstanding.', 1, '2026-09-24 23:08:11'),
(61, 72, 33, 31, 4, 'Fresh basil smelled incredible right out of the bag. Only 4 stars because a few leaves had slight browning at the edges.', 1, '2026-09-24 23:08:11'),
(62, 105, 51, 37, 5, 'fresh vegetables', 1, '2026-09-27 10:53:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','farmer','customer') NOT NULL,
  `status` enum('active','suspended','inactive') DEFAULT 'active',
  `phone_number` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `status`, `phone_number`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'sara_customer', 'sara@example.com', '$2y$10$LMMNmczxixT1t3qrQxUhPu29lqcASCnXqv1SbUNO74vBI8XX/R0WS', 'customer', 'active', '03001234567', NULL, '2026-09-23 16:31:31', '2026-09-23 20:18:53'),
(2, 'tariq_farmer', 'tariq@example.com', '$2y$10$syRk1c94eS0v5xRZaXdYu.MlHcjpyG24Kj3RlMoxP1VXJoecC5giq', 'farmer', 'active', '03129876543', 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=400&q=80', '2026-09-23 16:32:02', '2026-09-24 23:44:28'),
(3, 'bilal_market', 'bilal@example.com', '$2y$10$.py80MGNPiSkFr3kz9G6N.UKiX8YTb9AVx0oc8NAz4CurfXarXCHa', 'customer', 'active', NULL, NULL, '2026-09-23 16:36:32', '2026-09-23 16:36:32'),
(4, 'admin', 'admin@marketlink.com', '$2y$10$/y4SQfLeURu/WsDHKJDKrObPn4wXTWPNe3c2z3VRiv.DKsgA8uCD6', 'admin', 'active', '03000000000', NULL, '2026-09-23 16:37:33', '2026-09-24 11:44:54'),
(5, 'ahzam123', 'muhammadahzam1344@gmail.com', '$2y$10$elVfOkB.e8/HhUJor6R7bOZfR9HE0XdbhCItvrmls79VrTJ4VmYGO', 'customer', 'active', '0344440090', NULL, '2026-09-23 16:45:54', '2026-09-23 16:45:54'),
(6, 'liaquat222', 'liaquat@gmail.com', '$2y$10$b.LyCaAxl8yiCSCVP.LMoOUXSt8NvyHLNKcjLpF67191O3VxPxBge', 'farmer', 'active', '03400000000', 'https://images.unsplash.com/photo-1552058544-f2b08422138a?w=400&q=80', '2026-09-23 16:59:38', '2026-09-24 23:44:28'),
(7, 'hamza_tariq', 'hamza@example.com', '$2y$10$/jP9oVgRn9O./4ZJWpYlQe7ruU/E92kLuzxVKkk8sx.ghZOkhIKEC', 'customer', 'active', '03021234567', NULL, '2026-09-23 17:05:51', '2026-09-23 17:05:51'),
(8, 'ahzam997', 'ahzam@gmail.com', '$2y$10$G5kc8v35rHqqJlViBwlh7.FmNEAYB3GCrEZ8mHg04dfJ4OehCoFAO', 'customer', 'active', '+923012547696', NULL, '2026-09-23 17:09:12', '2026-09-23 17:09:12'),
(9, 'muhamadanees', 'anees@gmail.com', '$2y$10$oY40BSMXdNJ0OHi26JXQ/.fow2wm/U/VBvXIB10rttKjDswfoylyq', 'farmer', 'active', '03444444444', 'uploads/profiles/farmer_9_1790229647.jpg', '2026-09-24 10:38:50', '2026-09-24 11:00:47'),
(10, 'rafay_farms', 'rafay@farmermail.pk', '$2y$10$pQN6Y4YMS6DQlCa1k4fm.uH1pUxXTfWmusqhQo8hzghQ4jHxgvsVW', 'farmer', 'active', '0300-1234567', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(11, 'zara_greens', 'zara@organicpk.com', '$2y$10$PbTqTab59R0.rucIcF/GHOSyuJbLe/JcYUFKIOHcZ3JzlMvIo36n.', 'farmer', 'active', '0321-9876543', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(12, 'malik_dairy', 'malik@milkpk.net', '$2y$10$6TUr9/0zr1MjglIPBpPeU.ja0YOxcjIsEH9u4rUAvzVxlB9loUt8a', 'farmer', 'active', '0333-5551234', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&q=80', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(13, 'nadia_herbs', 'nadia@herbgarden.pk', '$2y$10$EYss.UuktuhomiKAEw00p.1Zsuph0Uo0E.3ahfttjMu1e1OwO9w0.', 'farmer', 'active', '0345-7778899', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&q=80', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(14, 'asif_citrus', 'asif@citrusbagh.com', '$2y$10$OKJ7wS67t5tcrhfceDouReY.NF7i4AcSIg.X222a0FHTxq5GkoiLG', 'farmer', 'active', '0311-4445566', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(15, 'hina_honey', 'hina@purehoney.pk', '$2y$10$1ehogCjyhiGZRQ/FI2xERuQBD1nJf57xQfxhFEEdJ40T1SKaIrOnO', 'farmer', 'active', '0323-1122334', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&q=80', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(16, 'kamran_grain', 'kamran@grainfield.pk', '$2y$10$Kl3E1YinNEZP.7fYdJ7Q8usEY1vhtaSWcEdhCJMYAylgtD.ndEJrC', 'farmer', 'active', '0312-6667778', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&q=80', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(17, 'ayesha_pickle', 'ayesha@jarstore.pk', '$2y$10$.nNONS7B1GsbWBeZ77g0D.gR.H5C58dMRiC37piHIk8EbHJEfi.6W', 'farmer', 'suspended', '0334-9991122', 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400&q=80', '2026-09-24 23:06:45', '2026-09-27 10:59:16'),
(18, 'junaid_veg', 'junaid@freshveg.pk', '$2y$10$KwTHfp2D1LhnaVAToeuf/uMEMYi11bTmYYSJEBtEhnpM4c/2xVDTW', 'farmer', 'active', '0302-3334455', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&q=80', '2026-09-24 23:06:45', '2026-09-24 23:06:45'),
(19, 'samira_poultry', 'samira@farmpoultrys.pk', '$2y$10$vDH3v4Eh9CwTz/3EswygB.guoPICogLIQ.RkBzSUvb3kAr2OhG0Gi', 'farmer', 'active', '0316-8889900', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(20, 'hassan_mushroom', 'hassan@mycelium.pk', '$2y$10$WsRJpp9ac.bfVCpAhJqmE.wNSCLFXH.ZmqbTpSbgRd6kJB9ZgOPDO', 'farmer', 'active', '0303-2223344', 'uploads/profiles/farmer_20_1790321097.jpg', '2026-09-24 23:06:46', '2026-09-25 12:24:57'),
(21, 'imran_superfoods', 'imran@superseeds.pk', '$2y$10$PpQlVTuNrv6hTso9rvAISuYGzpjvPl86rO2aln/qvQqeAzMM2JPcS', 'farmer', 'active', '0307-5556677', 'uploads/profiles/farmer_21_1790334571.jpg', '2026-09-24 23:06:46', '2026-09-25 16:09:31'),
(22, 'ali_raza99', 'ali.raza@gmail.com', '$2y$10$Q7qtaysnb.ZP57FJ4jaXDeiBWHgv0npUTuLYTh0uF/5lwF6k0RY1m', 'customer', 'active', '0300-1112223', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80&sig=1', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(23, 'fatima_ch', 'fatima.chaudhry@hotmail.com', '$2y$10$NC50P/rMJKhrYLk5fapLWu1Wq/0SnqMdr67AQdGLI0U91DX9E2PMm', 'customer', 'active', '0321-4445556', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80&sig=2', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(24, 'omar_sheikh', 'omar.sheikh@yahoo.com', '$2y$10$Sv7ksgK4C5OEBTs.kw.nKOQ2FMNO5Y6hx1KN/keFf8/nBc1R/YwO.', 'customer', 'active', '0333-7778889', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&q=80&sig=3', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(25, 'mehwish_k', 'mehwish.khan@gmail.com', '$2y$10$AabImPWodHnfmObhVgDWm.cxWxABVvWK4Rls31kmiMcNIb/J.v0OW', 'customer', 'active', '0345-2223334', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&q=80&sig=4', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(26, 'danish_ali', 'danish.ali@live.com', '$2y$10$HTuxwDGsKeOgkxIxIl7AiOarNnReqIezivWWAojkvkks3YozPXav.', 'customer', 'active', '0311-6667778', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80&sig=5', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(27, 'sana_tariq', 'sana.tariq@outlook.com', '$2y$10$6HTKuZ3Yj1l.aBufbBnFCemcIvHm5k/yvQhQTYKB9kqolf0AoEMEa', 'customer', 'active', '0323-9990011', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&q=80&sig=6', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(28, 'bilal_ahmed', 'bilal.ahmed@gmail.com', '$2y$10$ysgME61GEXwUJp5cNyCYb.n2R1l42U7rLT7gZLEywduAGJUrXfNLq', 'customer', 'active', '0312-3334445', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&q=80&sig=7', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(29, 'amina_malik', 'amina.malik@gmail.com', '$2y$10$/BVOw4AiK7mQ0RlLL0DUie99f1beEtkYX7zrw7y6NeccKa.xnwzCu', 'customer', 'active', '0334-5556667', 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400&q=80&sig=8', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(30, 'usman_nawaz', 'usman.nawaz@hotmail.com', '$2y$10$IrK8lLBLflI5CcbKHscZLehxsfkpfrKyDmAz6ZjNPyZxHfA8VdLOa', 'customer', 'active', '0302-8889900', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&q=80&sig=9', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(31, 'rabia_qureshi', 'rabia.qureshi@gmail.com', '$2y$10$RrKst3k2jGwQ6TC8RHOoseNT.Wp.lcAAZq6RWQNTPs94PU5MChF72', 'customer', 'active', '0316-1112223', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80&sig=10', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(32, 'zain_butt', 'zain.butt@gmail.com', '$2y$10$tls5eKXlpV.wPGGXiutTKe5R5xTxrK4bm9OLlanF0qZMh5qxDQ9hi', 'customer', 'active', '0303-4445556', 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=400&q=80&sig=11', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(33, 'saima_rizvi', 'saima.rizvi@yahoo.com', '$2y$10$8ja25coY6tCKzSXjPnKXG.BN6.FHgMFn2vYwBjCiVFV1an96bQn0i', 'customer', 'active', '0307-7778889', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400&q=80&sig=12', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(34, 'hamid_awan', 'hamid.awan@live.com', '$2y$10$sZR0lxS6kQu71ZnCoAJU0edsAO0dNWrVQHQg7bpCAtFiXAYM/M3fe', 'customer', 'active', '0311-0001112', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80&sig=13', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(35, 'nabila_javed', 'nabila.javed@gmail.com', '$2y$10$2TRdpFlNZH7eTl8Nu.1zrOOtMW6Gt98UsfgXaaj/y5t9/yNWafghK', 'customer', 'active', '0300-3334445', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80&sig=14', '2026-09-24 23:06:46', '2026-09-24 23:06:46'),
(36, 'tariq_mehmood', 'tariq.mehmood2@gmail.com', '$2y$10$uhPvJKzeLhCTlAKRnD8aneC4kMPPVgy2wr3qGoGUTizJ4eJ4RLIwe', 'customer', 'suspended', '0321-6667778', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&q=80&sig=15', '2026-09-24 23:06:47', '2026-09-27 10:59:04'),
(37, 'abdul_rafay', 'abdulrafayaptech32@gmail.com', '$2y$10$/j0NxVSjrgPttleacQV89OEZk.3h28O7KInWcPFK5KCzpZpIifxQ6', 'customer', 'active', '+923182489041', NULL, '2026-09-25 15:53:35', '2026-09-26 19:55:12');

-- --------------------------------------------------------

--
-- Table structure for table `weekly_inventory`
--

CREATE TABLE `weekly_inventory` (
  `inventory_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `stock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weekly_inventory`
--

INSERT INTO `weekly_inventory` (`inventory_id`, `product_id`, `stall_id`, `day_of_week`, `stock_quantity`, `price`, `is_available`, `updated_at`) VALUES
(1, 1, 1, 'Saturday', 44.00, 140.00, 1, '2026-09-23 19:58:03'),
(2, 1, 1, 'Sunday', 70.00, 140.00, 1, '2026-09-23 19:58:03'),
(3, 1, 1, 'Friday', 50.00, 140.00, 1, '2026-09-23 19:58:03'),
(4, 1, 1, 'Wednesday', 55.00, 140.00, 1, '2026-09-23 19:58:03'),
(5, 1, 2, 'Saturday', 53.00, 140.00, 1, '2026-09-23 19:58:03'),
(6, 1, 2, 'Sunday', 29.00, 140.00, 1, '2026-09-23 19:58:03'),
(7, 1, 2, 'Friday', 78.00, 140.00, 1, '2026-09-23 19:58:03'),
(8, 1, 2, 'Wednesday', 55.00, 140.00, 1, '2026-09-23 19:58:03'),
(9, 2, 1, 'Saturday', 68.00, 110.00, 1, '2026-09-23 19:58:03'),
(10, 2, 1, 'Sunday', 57.00, 110.00, 1, '2026-09-23 19:58:03'),
(11, 2, 1, 'Friday', 30.00, 110.00, 1, '2026-09-23 19:58:03'),
(12, 2, 1, 'Wednesday', 53.00, 110.00, 1, '2026-09-23 19:58:03'),
(13, 2, 2, 'Saturday', 56.00, 110.00, 1, '2026-09-23 19:58:03'),
(14, 2, 2, 'Sunday', 38.00, 110.00, 1, '2026-09-23 19:58:03'),
(15, 2, 2, 'Friday', 76.00, 110.00, 1, '2026-09-23 19:58:03'),
(16, 2, 2, 'Wednesday', 78.00, 110.00, 1, '2026-09-23 19:58:03'),
(17, 3, 1, 'Saturday', 34.00, 280.00, 1, '2026-09-23 19:58:03'),
(18, 3, 1, 'Sunday', 64.00, 280.00, 1, '2026-09-23 19:58:03'),
(19, 3, 1, 'Friday', 30.00, 280.00, 1, '2026-09-23 19:58:03'),
(20, 3, 1, 'Wednesday', 77.00, 280.00, 1, '2026-09-23 19:58:03'),
(21, 3, 2, 'Saturday', 46.00, 280.00, 1, '2026-09-23 19:58:03'),
(22, 3, 2, 'Sunday', 71.00, 280.00, 1, '2026-09-23 19:58:03'),
(23, 3, 2, 'Friday', 63.00, 280.00, 1, '2026-09-23 19:58:03'),
(24, 3, 2, 'Wednesday', 49.00, 280.00, 1, '2026-09-23 19:58:03'),
(25, 4, 1, 'Saturday', 26.00, 750.00, 1, '2026-09-23 19:58:03'),
(26, 4, 1, 'Sunday', 72.00, 750.00, 1, '2026-09-23 19:58:03'),
(27, 4, 1, 'Friday', 67.00, 750.00, 1, '2026-09-23 19:58:03'),
(28, 4, 1, 'Wednesday', 59.00, 750.00, 1, '2026-09-23 19:58:03'),
(29, 4, 2, 'Saturday', 72.00, 750.00, 1, '2026-09-23 19:58:03'),
(30, 4, 2, 'Sunday', 72.00, 750.00, 1, '2026-09-23 19:58:03'),
(31, 4, 2, 'Friday', 77.00, 750.00, 1, '2026-09-23 19:58:03'),
(32, 4, 2, 'Wednesday', 32.00, 750.00, 1, '2026-09-23 19:58:03'),
(33, 5, 3, 'Saturday', 61.00, 85.00, 1, '2026-09-23 19:58:03'),
(34, 5, 3, 'Sunday', 53.00, 85.00, 1, '2026-09-23 19:58:03'),
(35, 5, 3, 'Friday', 37.00, 85.00, 1, '2026-09-23 19:58:03'),
(36, 5, 3, 'Wednesday', 36.00, 85.00, 1, '2026-09-23 19:58:03'),
(37, 5, 4, 'Saturday', 38.00, 85.00, 1, '2026-09-23 19:58:03'),
(38, 5, 4, 'Sunday', 57.00, 85.00, 1, '2026-09-23 19:58:03'),
(39, 5, 4, 'Friday', 31.00, 85.00, 1, '2026-09-23 19:58:03'),
(40, 5, 4, 'Wednesday', 57.00, 85.00, 1, '2026-09-23 19:58:03'),
(41, 6, 3, 'Saturday', 74.00, 320.00, 1, '2026-09-23 19:58:03'),
(42, 6, 3, 'Sunday', 37.00, 320.00, 1, '2026-09-23 19:58:03'),
(43, 6, 3, 'Friday', 52.00, 320.00, 1, '2026-09-23 19:58:03'),
(44, 6, 3, 'Wednesday', 30.00, 320.00, 1, '2026-09-23 19:58:03'),
(45, 6, 4, 'Saturday', 69.00, 320.00, 1, '2026-09-23 19:58:03'),
(46, 6, 4, 'Sunday', 64.00, 320.00, 1, '2026-09-23 19:58:03'),
(47, 6, 4, 'Friday', 48.00, 320.00, 1, '2026-09-23 19:58:03'),
(48, 6, 4, 'Wednesday', 53.00, 320.00, 1, '2026-09-23 19:58:03'),
(49, 7, 3, 'Saturday', 30.00, 450.00, 1, '2026-09-23 19:58:03'),
(50, 7, 3, 'Sunday', 27.00, 450.00, 1, '2026-09-23 19:58:03'),
(51, 7, 3, 'Friday', 61.00, 450.00, 1, '2026-09-23 19:58:03'),
(52, 7, 3, 'Wednesday', 44.00, 450.00, 1, '2026-09-23 19:58:03'),
(53, 7, 4, 'Saturday', 80.00, 450.00, 1, '2026-09-23 19:58:03'),
(54, 7, 4, 'Sunday', 78.00, 450.00, 1, '2026-09-23 19:58:03'),
(55, 7, 4, 'Friday', 66.00, 450.00, 1, '2026-09-23 19:58:03'),
(56, 7, 4, 'Wednesday', 60.00, 450.00, 1, '2026-09-23 19:58:03'),
(57, 8, 3, 'Saturday', 47.00, 380.00, 1, '2026-09-23 19:58:03'),
(58, 8, 3, 'Sunday', 49.00, 380.00, 1, '2026-09-23 19:58:03'),
(59, 8, 3, 'Friday', 48.00, 380.00, 1, '2026-09-23 19:58:03'),
(60, 8, 3, 'Wednesday', 28.00, 380.00, 1, '2026-09-23 19:58:03'),
(61, 8, 4, 'Saturday', 26.00, 380.00, 1, '2026-09-23 19:58:03'),
(62, 8, 4, 'Sunday', 26.00, 380.00, 1, '2026-09-23 19:58:03'),
(63, 8, 4, 'Friday', 35.00, 380.00, 1, '2026-09-23 19:58:03'),
(64, 8, 4, 'Wednesday', 47.00, 380.00, 1, '2026-09-23 19:58:03'),
(65, 9, 3, 'Saturday', 58.00, 120.00, 1, '2026-09-23 19:58:03'),
(66, 9, 3, 'Sunday', 78.00, 120.00, 1, '2026-09-23 19:58:03'),
(67, 9, 3, 'Friday', 66.00, 120.00, 1, '2026-09-23 19:58:03'),
(68, 9, 3, 'Wednesday', 77.00, 120.00, 1, '2026-09-23 19:58:03'),
(69, 9, 4, 'Saturday', 41.00, 120.00, 1, '2026-09-23 19:58:03'),
(70, 9, 4, 'Sunday', 42.00, 120.00, 1, '2026-09-23 19:58:03'),
(71, 9, 4, 'Friday', 71.00, 120.00, 1, '2026-09-23 19:58:03'),
(72, 9, 4, 'Wednesday', 52.00, 120.00, 1, '2026-09-23 19:58:03'),
(77, 11, 5, 'Saturday', 30.00, 650.00, 1, '2026-09-24 10:48:06'),
(78, 11, 5, 'Sunday', 30.00, 650.00, 1, '2026-09-24 10:48:06'),
(79, 11, 5, 'Friday', 30.00, 650.00, 1, '2026-09-24 10:48:06'),
(80, 11, 5, 'Wednesday', 30.00, 650.00, 1, '2026-09-24 10:48:06'),
(81, 12, 7, 'Friday', 43.00, 236.00, 1, '2026-09-24 23:06:47'),
(82, 12, 7, 'Saturday', 40.00, 236.00, 1, '2026-09-24 23:06:47'),
(83, 12, 7, 'Sunday', 25.00, 236.00, 1, '2026-09-24 23:06:47'),
(84, 13, 7, 'Friday', 34.00, 230.00, 1, '2026-09-24 23:06:47'),
(85, 13, 7, 'Saturday', 21.00, 230.00, 1, '2026-09-24 23:06:47'),
(86, 13, 7, 'Sunday', 12.00, 230.00, 1, '2026-09-24 23:06:47'),
(87, 14, 7, 'Friday', 34.00, 220.00, 1, '2026-09-24 23:06:47'),
(88, 14, 7, 'Saturday', 15.00, 220.00, 1, '2026-09-24 23:06:47'),
(89, 14, 7, 'Sunday', 50.00, 220.00, 1, '2026-09-24 23:06:47'),
(90, 15, 7, 'Friday', 49.00, 334.00, 1, '2026-09-24 23:06:47'),
(91, 15, 7, 'Saturday', 10.00, 334.00, 1, '2026-09-24 23:06:47'),
(92, 15, 7, 'Sunday', 43.00, 334.00, 1, '2026-09-24 23:06:47'),
(93, 16, 7, 'Friday', 34.00, 144.00, 1, '2026-09-24 23:06:47'),
(94, 16, 7, 'Saturday', 40.00, 144.00, 1, '2026-09-24 23:06:47'),
(95, 16, 7, 'Sunday', 40.00, 144.00, 1, '2026-09-24 23:06:47'),
(96, 17, 9, 'Friday', 44.00, 144.00, 1, '2026-09-24 23:06:47'),
(97, 17, 9, 'Saturday', 28.00, 144.00, 1, '2026-09-24 23:06:47'),
(98, 17, 9, 'Sunday', 49.00, 144.00, 1, '2026-09-24 23:06:47'),
(99, 18, 9, 'Friday', 22.00, 183.00, 1, '2026-09-24 23:06:47'),
(100, 18, 9, 'Saturday', 31.00, 183.00, 1, '2026-09-24 23:06:47'),
(101, 18, 9, 'Sunday', 23.00, 183.00, 1, '2026-09-24 23:06:47'),
(102, 19, 9, 'Friday', 16.00, 71.00, 1, '2026-09-24 23:06:47'),
(103, 19, 9, 'Saturday', 11.00, 71.00, 1, '2026-09-24 23:06:47'),
(104, 19, 9, 'Sunday', 55.00, 71.00, 1, '2026-09-24 23:06:47'),
(105, 20, 9, 'Friday', 30.00, 275.00, 1, '2026-09-24 23:06:47'),
(106, 20, 9, 'Saturday', 34.00, 275.00, 1, '2026-09-24 23:06:47'),
(107, 20, 9, 'Sunday', 57.00, 275.00, 1, '2026-09-24 23:06:47'),
(108, 21, 9, 'Friday', 24.00, 74.00, 1, '2026-09-24 23:06:47'),
(109, 21, 9, 'Saturday', 23.00, 74.00, 1, '2026-09-24 23:06:47'),
(110, 21, 9, 'Sunday', 36.00, 74.00, 1, '2026-09-24 23:06:47'),
(111, 22, 11, 'Friday', 17.00, 231.00, 1, '2026-09-24 23:06:47'),
(112, 22, 11, 'Saturday', 41.00, 231.00, 1, '2026-09-24 23:06:47'),
(113, 22, 11, 'Sunday', 17.00, 231.00, 1, '2026-09-24 23:06:47'),
(114, 23, 11, 'Friday', 59.00, 322.00, 1, '2026-09-24 23:06:47'),
(115, 23, 11, 'Saturday', 12.00, 322.00, 1, '2026-09-24 23:06:47'),
(116, 23, 11, 'Sunday', 38.00, 322.00, 1, '2026-09-24 23:06:47'),
(117, 24, 11, 'Friday', 35.00, 350.00, 1, '2026-09-24 23:06:47'),
(118, 24, 11, 'Saturday', 58.00, 350.00, 1, '2026-09-24 23:06:47'),
(119, 24, 11, 'Sunday', 58.00, 350.00, 1, '2026-09-24 23:06:47'),
(120, 25, 11, 'Friday', 43.00, 240.00, 1, '2026-09-24 23:06:47'),
(121, 25, 11, 'Saturday', 38.00, 240.00, 1, '2026-09-24 23:06:47'),
(122, 25, 11, 'Sunday', 40.00, 240.00, 1, '2026-09-24 23:06:47'),
(123, 26, 11, 'Friday', 48.00, 179.00, 1, '2026-09-24 23:06:47'),
(124, 26, 11, 'Saturday', 35.00, 179.00, 1, '2026-09-24 23:06:47'),
(125, 26, 11, 'Sunday', 33.00, 179.00, 1, '2026-09-24 23:06:47'),
(126, 27, 13, 'Friday', 11.00, 127.00, 1, '2026-09-24 23:06:47'),
(127, 27, 13, 'Saturday', 25.00, 127.00, 1, '2026-09-24 23:06:47'),
(128, 27, 13, 'Sunday', 23.00, 127.00, 1, '2026-09-24 23:06:47'),
(129, 28, 13, 'Friday', 37.00, 76.00, 1, '2026-09-24 23:06:47'),
(130, 28, 13, 'Saturday', 37.00, 76.00, 1, '2026-09-24 23:06:47'),
(131, 28, 13, 'Sunday', 56.00, 76.00, 1, '2026-09-24 23:06:47'),
(132, 29, 13, 'Friday', 47.00, 217.00, 1, '2026-09-24 23:06:47'),
(133, 29, 13, 'Saturday', 59.00, 217.00, 1, '2026-09-24 23:06:47'),
(134, 29, 13, 'Sunday', 41.00, 217.00, 1, '2026-09-24 23:06:47'),
(135, 30, 13, 'Friday', 29.00, 77.00, 1, '2026-09-24 23:06:47'),
(136, 30, 13, 'Saturday', 39.00, 77.00, 1, '2026-09-24 23:06:47'),
(137, 30, 13, 'Sunday', 18.00, 77.00, 1, '2026-09-24 23:06:47'),
(138, 31, 13, 'Friday', 48.00, 497.00, 1, '2026-09-24 23:06:47'),
(139, 31, 13, 'Saturday', 42.00, 497.00, 1, '2026-09-24 23:06:47'),
(140, 31, 13, 'Sunday', 28.00, 497.00, 1, '2026-09-24 23:06:47'),
(141, 32, 15, 'Friday', 47.00, 595.00, 1, '2026-09-24 23:06:47'),
(142, 32, 15, 'Saturday', 12.00, 595.00, 1, '2026-09-24 23:06:47'),
(143, 32, 15, 'Sunday', 31.00, 595.00, 1, '2026-09-24 23:06:47'),
(144, 33, 15, 'Friday', 19.00, 212.00, 1, '2026-09-24 23:06:47'),
(145, 33, 15, 'Saturday', 14.00, 212.00, 1, '2026-09-24 23:06:47'),
(146, 33, 15, 'Sunday', 19.00, 212.00, 1, '2026-09-24 23:06:47'),
(147, 34, 15, 'Friday', 56.00, 796.00, 1, '2026-09-24 23:06:47'),
(148, 34, 15, 'Saturday', 53.00, 796.00, 1, '2026-09-24 23:06:47'),
(149, 34, 15, 'Sunday', 39.00, 796.00, 1, '2026-09-24 23:06:47'),
(150, 35, 15, 'Friday', 29.00, 217.00, 1, '2026-09-24 23:06:47'),
(151, 35, 15, 'Saturday', 22.00, 217.00, 1, '2026-09-24 23:06:47'),
(152, 35, 15, 'Sunday', 34.00, 217.00, 1, '2026-09-24 23:06:47'),
(153, 36, 17, 'Friday', 40.00, 178.00, 1, '2026-09-24 23:06:47'),
(154, 36, 17, 'Saturday', 37.00, 178.00, 1, '2026-09-24 23:06:47'),
(155, 36, 17, 'Sunday', 29.00, 178.00, 1, '2026-09-24 23:06:47'),
(156, 37, 17, 'Friday', 10.00, 129.00, 1, '2026-09-24 23:06:47'),
(157, 37, 17, 'Saturday', 32.00, 129.00, 1, '2026-09-24 23:06:47'),
(158, 37, 17, 'Sunday', 32.00, 129.00, 1, '2026-09-24 23:06:47'),
(159, 38, 17, 'Friday', 38.00, 135.00, 1, '2026-09-24 23:06:47'),
(160, 38, 17, 'Saturday', 21.00, 135.00, 1, '2026-09-24 23:06:47'),
(161, 38, 17, 'Sunday', 59.00, 135.00, 1, '2026-09-24 23:06:47'),
(162, 39, 17, 'Friday', 25.00, 931.00, 1, '2026-09-24 23:06:47'),
(163, 39, 17, 'Saturday', 52.00, 931.00, 1, '2026-09-24 23:06:47'),
(164, 39, 17, 'Sunday', 18.00, 931.00, 1, '2026-09-24 23:06:47'),
(165, 40, 19, 'Friday', 17.00, 916.00, 1, '2026-09-24 23:06:47'),
(166, 40, 19, 'Saturday', 32.00, 916.00, 1, '2026-09-24 23:06:47'),
(167, 40, 19, 'Sunday', 24.00, 916.00, 1, '2026-09-24 23:06:47'),
(168, 41, 19, 'Friday', 31.00, 366.00, 1, '2026-09-24 23:06:47'),
(169, 41, 19, 'Saturday', 29.00, 366.00, 1, '2026-09-24 23:06:47'),
(170, 41, 19, 'Sunday', 35.00, 366.00, 1, '2026-09-24 23:06:47'),
(171, 42, 19, 'Friday', 24.00, 313.00, 1, '2026-09-24 23:06:47'),
(172, 42, 19, 'Saturday', 32.00, 313.00, 1, '2026-09-24 23:06:47'),
(173, 42, 19, 'Sunday', 38.00, 313.00, 1, '2026-09-24 23:06:47'),
(174, 43, 19, 'Friday', 33.00, 540.00, 1, '2026-09-24 23:06:47'),
(175, 43, 19, 'Saturday', 55.00, 540.00, 1, '2026-09-24 23:06:47'),
(176, 43, 19, 'Sunday', 45.00, 540.00, 1, '2026-09-24 23:06:47'),
(177, 44, 21, 'Friday', 48.00, 203.00, 1, '2026-09-24 23:06:47'),
(178, 44, 21, 'Saturday', 39.00, 203.00, 1, '2026-09-24 23:06:47'),
(179, 44, 21, 'Sunday', 14.00, 203.00, 1, '2026-09-24 23:06:47'),
(180, 45, 21, 'Friday', 22.00, 247.00, 1, '2026-09-24 23:06:47'),
(181, 45, 21, 'Saturday', 26.00, 247.00, 1, '2026-09-24 23:06:47'),
(182, 45, 21, 'Sunday', 35.00, 247.00, 1, '2026-09-24 23:06:47'),
(183, 46, 21, 'Friday', 20.00, 434.00, 1, '2026-09-24 23:06:47'),
(184, 46, 21, 'Saturday', 11.00, 434.00, 1, '2026-09-24 23:06:47'),
(185, 46, 21, 'Sunday', 60.00, 434.00, 1, '2026-09-24 23:06:47'),
(186, 47, 21, 'Friday', 35.00, 215.00, 1, '2026-09-24 23:06:47'),
(187, 47, 21, 'Saturday', 25.00, 215.00, 1, '2026-09-24 23:06:47'),
(188, 47, 21, 'Sunday', 50.00, 215.00, 1, '2026-09-24 23:06:47'),
(189, 48, 21, 'Friday', 28.00, 534.00, 1, '2026-09-24 23:06:47'),
(190, 48, 21, 'Saturday', 37.00, 534.00, 1, '2026-09-24 23:06:47'),
(191, 48, 21, 'Sunday', 40.00, 534.00, 1, '2026-09-24 23:06:47'),
(192, 49, 23, 'Friday', 12.00, 73.00, 1, '2026-09-24 23:06:47'),
(193, 49, 23, 'Saturday', 38.00, 73.00, 1, '2026-09-24 23:06:47'),
(194, 49, 23, 'Sunday', 14.00, 73.00, 1, '2026-09-24 23:06:47'),
(195, 50, 23, 'Friday', 57.00, 68.00, 1, '2026-09-24 23:06:47'),
(196, 50, 23, 'Saturday', 24.00, 68.00, 1, '2026-09-24 23:06:47'),
(197, 50, 23, 'Sunday', 48.00, 68.00, 1, '2026-09-24 23:06:47'),
(198, 51, 23, 'Friday', 20.00, 396.00, 1, '2026-09-27 10:47:42'),
(199, 51, 23, 'Saturday', 20.00, 396.00, 1, '2026-09-27 10:47:42'),
(200, 51, 23, 'Sunday', 20.00, 396.00, 1, '2026-09-27 10:47:42'),
(201, 52, 23, 'Friday', 44.00, 228.00, 1, '2026-09-24 23:06:47'),
(202, 52, 23, 'Saturday', 26.00, 228.00, 1, '2026-09-24 23:06:47'),
(203, 52, 23, 'Sunday', 35.00, 228.00, 1, '2026-09-24 23:06:47'),
(204, 53, 25, 'Friday', 50.00, 889.00, 1, '2026-09-24 23:06:47'),
(205, 53, 25, 'Saturday', 54.00, 889.00, 1, '2026-09-24 23:06:47'),
(206, 53, 25, 'Sunday', 47.00, 889.00, 1, '2026-09-24 23:06:47'),
(207, 54, 25, 'Friday', 48.00, 210.00, 1, '2026-09-24 23:06:47'),
(208, 54, 25, 'Saturday', 50.00, 210.00, 1, '2026-09-24 23:06:47'),
(209, 54, 25, 'Sunday', 42.00, 210.00, 1, '2026-09-24 23:06:47'),
(210, 55, 25, 'Friday', 21.00, 137.00, 1, '2026-09-24 23:06:47'),
(211, 55, 25, 'Saturday', 39.00, 137.00, 1, '2026-09-24 23:06:47'),
(212, 55, 25, 'Sunday', 37.00, 137.00, 1, '2026-09-24 23:06:47'),
(213, 56, 27, 'Friday', 24.00, 217.00, 1, '2026-09-24 23:06:47'),
(214, 56, 27, 'Saturday', 23.00, 217.00, 1, '2026-09-24 23:06:47'),
(215, 56, 27, 'Sunday', 16.00, 217.00, 1, '2026-09-24 23:06:47'),
(216, 57, 27, 'Friday', 20.00, 280.00, 1, '2026-09-24 23:06:47'),
(217, 57, 27, 'Saturday', 11.00, 280.00, 1, '2026-09-24 23:06:47'),
(218, 57, 27, 'Sunday', 58.00, 280.00, 1, '2026-09-24 23:06:47'),
(219, 58, 27, 'Friday', 36.00, 846.00, 1, '2026-09-24 23:06:47'),
(220, 58, 27, 'Saturday', 10.00, 846.00, 1, '2026-09-24 23:06:47'),
(221, 58, 27, 'Sunday', 13.00, 846.00, 1, '2026-09-24 23:06:47'),
(222, 59, 27, 'Friday', 27.00, 232.00, 1, '2026-09-24 23:06:47'),
(223, 59, 27, 'Saturday', 28.00, 232.00, 1, '2026-09-24 23:06:47'),
(224, 59, 27, 'Sunday', 41.00, 232.00, 1, '2026-09-24 23:06:47'),
(225, 60, 29, 'Friday', 41.00, 155.00, 1, '2026-09-24 23:06:47'),
(226, 60, 29, 'Saturday', 42.00, 155.00, 1, '2026-09-24 23:06:47'),
(227, 60, 29, 'Sunday', 19.00, 155.00, 1, '2026-09-24 23:06:47'),
(228, 61, 29, 'Friday', 34.00, 223.00, 1, '2026-09-24 23:06:47'),
(229, 61, 29, 'Saturday', 41.00, 223.00, 1, '2026-09-24 23:06:47'),
(230, 61, 29, 'Sunday', 23.00, 223.00, 1, '2026-09-24 23:06:47'),
(231, 62, 29, 'Friday', 25.00, 522.00, 1, '2026-09-24 23:06:47'),
(232, 62, 29, 'Saturday', 49.00, 522.00, 1, '2026-09-24 23:06:47'),
(233, 62, 29, 'Sunday', 46.00, 522.00, 1, '2026-09-24 23:06:47'),
(234, 63, 29, 'Friday', 35.00, 210.00, 1, '2026-09-24 23:06:47'),
(235, 63, 29, 'Saturday', 55.00, 210.00, 1, '2026-09-24 23:06:47'),
(236, 63, 29, 'Sunday', 43.00, 210.00, 1, '2026-09-24 23:06:47'),
(237, 64, 29, 'Friday', 34.00, 357.00, 1, '2026-09-24 23:06:47'),
(238, 64, 29, 'Saturday', 47.00, 357.00, 1, '2026-09-24 23:06:47'),
(239, 64, 29, 'Sunday', 33.00, 357.00, 1, '2026-09-24 23:06:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_chatbot_faqs`
--
ALTER TABLE `ai_chatbot_faqs`
  ADD PRIMARY KEY (`faq_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD UNIQUE KEY `uq_customer_cart_item` (`customer_id`,`product_id`,`stall_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `stall_id` (`stall_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `farmer_market_stalls`
--
ALTER TABLE `farmer_market_stalls`
  ADD PRIMARY KEY (`stall_id`),
  ADD UNIQUE KEY `uq_farmer_market` (`farmer_id`,`market_id`),
  ADD KEY `market_id` (`market_id`);

--
-- Indexes for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  ADD PRIMARY KEY (`farmer_id`),
  ADD KEY `idx_farmer_approval` (`approval_status`),
  ADD KEY `idx_farmer_coords` (`latitude`,`longitude`);

--
-- Indexes for table `farmer_reviews`
--
ALTER TABLE `farmer_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_farmer_rating` (`farmer_id`,`rating`);

--
-- Indexes for table `favorite_farmers`
--
ALTER TABLE `favorite_farmers`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `uq_customer_farmer_fav` (`customer_id`,`farmer_id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `favorite_products`
--
ALTER TABLE `favorite_products`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `uq_customer_product_fav` (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `markets`
--
ALTER TABLE `markets`
  ADD PRIMARY KEY (`market_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_market_status` (`status`),
  ADD KEY `idx_market_coords` (`latitude`,`longitude`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_notifications` (`user_id`,`is_read`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `market_id` (`market_id`),
  ADD KEY `stall_id` (`stall_id`),
  ADD KEY `pickup_slot_id` (`pickup_slot_id`),
  ADD KEY `idx_order_customer` (`customer_id`),
  ADD KEY `idx_order_farmer` (`farmer_id`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_pickup_date` (`pickup_date`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_messages`
--
ALTER TABLE `order_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_sender_receiver` (`sender_id`,`receiver_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `pickup_slots`
--
ALTER TABLE `pickup_slots`
  ADD PRIMARY KEY (`pickup_slot_id`),
  ADD KEY `idx_slot_search` (`stall_id`,`slot_date`,`status`);

--
-- Indexes for table `platform_announcements`
--
ALTER TABLE `platform_announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_product_farmer` (`farmer_id`),
  ADD KEY `idx_product_category` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_role` (`role`),
  ADD KEY `idx_user_status` (`status`);

--
-- Indexes for table `weekly_inventory`
--
ALTER TABLE `weekly_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD UNIQUE KEY `uq_product_stall_day` (`product_id`,`stall_id`,`day_of_week`),
  ADD KEY `stall_id` (`stall_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_chatbot_faqs`
--
ALTER TABLE `ai_chatbot_faqs`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `farmer_market_stalls`
--
ALTER TABLE `farmer_market_stalls`
  MODIFY `stall_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `farmer_reviews`
--
ALTER TABLE `farmer_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `favorite_farmers`
--
ALTER TABLE `favorite_farmers`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `favorite_products`
--
ALTER TABLE `favorite_products`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `markets`
--
ALTER TABLE `markets`
  MODIFY `market_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `order_messages`
--
ALTER TABLE `order_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `pickup_slots`
--
ALTER TABLE `pickup_slots`
  MODIFY `pickup_slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=707;

--
-- AUTO_INCREMENT for table `platform_announcements`
--
ALTER TABLE `platform_announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `weekly_inventory`
--
ALTER TABLE `weekly_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer_profiles` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`stall_id`) REFERENCES `farmer_market_stalls` (`stall_id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD CONSTRAINT `customer_profiles_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `farmer_market_stalls`
--
ALTER TABLE `farmer_market_stalls`
  ADD CONSTRAINT `farmer_market_stalls_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`farmer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `farmer_market_stalls_ibfk_2` FOREIGN KEY (`market_id`) REFERENCES `markets` (`market_id`) ON DELETE CASCADE;

--
-- Constraints for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  ADD CONSTRAINT `farmer_profiles_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `farmer_reviews`
--
ALTER TABLE `farmer_reviews`
  ADD CONSTRAINT `farmer_reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `farmer_reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer_profiles` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `farmer_reviews_ibfk_3` FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`farmer_id`) ON DELETE CASCADE;

--
-- Constraints for table `favorite_farmers`
--
ALTER TABLE `favorite_farmers`
  ADD CONSTRAINT `favorite_farmers_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer_profiles` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorite_farmers_ibfk_2` FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`farmer_id`) ON DELETE CASCADE;

--
-- Constraints for table `favorite_products`
--
ALTER TABLE `favorite_products`
  ADD CONSTRAINT `favorite_products_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer_profiles` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorite_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `markets`
--
ALTER TABLE `markets`
  ADD CONSTRAINT `markets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer_profiles` (`customer_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`farmer_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`market_id`) REFERENCES `markets` (`market_id`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`stall_id`) REFERENCES `farmer_market_stalls` (`stall_id`),
  ADD CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`pickup_slot_id`) REFERENCES `pickup_slots` (`pickup_slot_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `pickup_slots`
--
ALTER TABLE `pickup_slots`
  ADD CONSTRAINT `pickup_slots_ibfk_1` FOREIGN KEY (`stall_id`) REFERENCES `farmer_market_stalls` (`stall_id`) ON DELETE CASCADE;

--
-- Constraints for table `platform_announcements`
--
ALTER TABLE `platform_announcements`
  ADD CONSTRAINT `platform_announcements_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`farmer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`);

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customer_profiles` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `weekly_inventory`
--
ALTER TABLE `weekly_inventory`
  ADD CONSTRAINT `weekly_inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `weekly_inventory_ibfk_2` FOREIGN KEY (`stall_id`) REFERENCES `farmer_market_stalls` (`stall_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
