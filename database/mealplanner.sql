-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 01:32 PM
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
-- Database: `mealplanner`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `competition_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `winner_token` varchar(64) DEFAULT NULL,
  `announced_at` datetime DEFAULT NULL,
  `winning_recipe_id` int(11) DEFAULT NULL,
  `winner_announced_at` datetime DEFAULT NULL,
  `leading_recipe_id` int(11) DEFAULT NULL,
  `prize` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(50) DEFAULT 'active',
  `winner_announced` tinyint(1) DEFAULT 0,
  `winner_recipe_id` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `winner_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`competition_id`, `title`, `description`, `start_date`, `end_date`, `winner_id`, `winner_token`, `announced_at`, `winning_recipe_id`, `winner_announced_at`, `leading_recipe_id`, `prize`, `image_url`, `created_at`, `updated_at`, `status`, `winner_announced`, `winner_recipe_id`, `is_featured`, `winner_name`) VALUES
(1, 'Ramadhan Special Recipe Contest', 'Join our festive competition and share your best buka puasa dishes! The top 3 winners will be featured on our homepage.', '2025-04-03', '2025-04-30', 6, NULL, NULL, NULL, '2025-04-16 13:48:56', 2, 'RM300 cash + Gift Hamper + Featured Badge', '../uploads/competitions/1743691865_competition_banner1.png', '2025-04-03 14:51:05', '2025-04-16 05:48:56', 'active', 1, 2, 0, 'Test Winner'),
(5, 'Sample Competition', NULL, '2025-04-16', '2025-05-16', 6, NULL, NULL, 12, NULL, NULL, NULL, NULL, '2025-04-16 06:16:00', '2025-04-16 06:21:31', 'completed', 0, NULL, 0, 'John Doe'),
(6, 'Culinary Showdown', NULL, '2025-01-01', '2025-01-15', 6, NULL, NULL, 12, NULL, NULL, NULL, NULL, '2025-04-16 06:21:40', '2025-04-16 06:21:40', 'completed', 0, NULL, 0, 'John Doe'),
(9, 'hello', 'mee goreng', '2025-04-16', '2025-04-17', NULL, NULL, NULL, NULL, NULL, NULL, '10000', 'uploads/competitions/5ef5b84600fb8359.png', '2025-04-16 03:49:13', '2025-04-16 03:49:13', 'active', 0, NULL, 0, NULL),
(10, 'Summer Recipe Challenge', 'Submit your best summer dishes!', '2024-06-01', '2024-07-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://example.com/summer-competition.jpg', '2025-04-16 05:47:59', '2025-04-16 05:47:59', 'completed', 0, NULL, 1, NULL),
(11, 'Test Competition', NULL, '0000-00-00', '2024-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-16 05:51:02', '2025-04-16 05:51:02', 'completed', 0, NULL, 0, NULL),
(12, 'Test Competition', NULL, '0000-00-00', '2024-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-16 05:52:43', '2025-04-16 05:52:43', 'completed', 0, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `competition_entries`
--

CREATE TABLE `competition_entries` (
  `entry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'submitted',
  `submitted_at` datetime DEFAULT current_timestamp(),
  `winner_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_entries`
--

INSERT INTO `competition_entries` (`entry_id`, `user_id`, `competition_id`, `recipe_id`, `status`, `submitted_at`, `winner_name`) VALUES
(1, 1, 1, 2, 'approved', '2025-04-04 00:45:43', 'Pow Kah Meon'),
(2, 1, 9, 2, 'approved', '2025-04-16 12:03:42', NULL),
(13, 6, 5, 12, 'winner', '2025-04-16 14:17:51', NULL),
(14, 1, 5, 2, 'approved', '2025-04-16 14:18:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'pow kah meon', 'khmeon058@1utar.my', '', 'Testing', '2025-04-03 22:10:37');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `message`, `created_at`, `status`) VALUES
(1, 'pow kah meon', 'khmeon058@1utar.my', 'Improve UI', '2025-04-03 13:46:46', 'new');

-- --------------------------------------------------------

--
-- Table structure for table `meal_plans`
--

CREATE TABLE `meal_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `meal_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ingredients` longtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `steps` longtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `cuisine` varchar(100) DEFAULT NULL,
  `prep_time` int(11) DEFAULT NULL,
  `cook_time` int(11) DEFAULT NULL,
  `total_time` int(11) DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT NULL,
  `nutrition` longtext CHARACTER SET utf8 COLLATE utf8_unicode_520_ci DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected') DEFAULT 'pending',
  `is_public` tinyint(1) DEFAULT 1,
  `view_count` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `admin_note` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `title`, `description`, `ingredients`, `steps`, `cuisine`, `prep_time`, `cook_time`, `total_time`, `difficulty`, `nutrition`, `status`, `is_public`, `view_count`, `created_by`, `created_at`, `updated_at`, `admin_note`, `image_url`, `instructions`) VALUES
(2, 'Nasi Kerabu', 'Nasi Kerabu is a traditional Malaysian dish featuring blue-colored rice, usually dyed with butterfly pea flowers. It is served with a variety of fresh herbs, salted egg, fried fish or chicken, and a flavorful coconut-based sambal sauce. This dish is popular in Kelantan and Terengganu, offering a unique mix of flavors and textures.', '[\"For the Blue Rice:\",\"2 cups white rice (Jasmine or Basmati)\",\"10 dried butterfly pea flowers (bunga telang)\",\"2 cups water\",\"1 pandan leaf (knotted)\",\"1\\/2 tsp salt\",\"For the Garnishes:\",\"1 cup bean sprouts (blanched)\",\"1\\/2 cup shredded cabbage\",\"1\\/2 cup finely sliced torch ginger flower (bunga kantan)\",\"1\\/2 cup fresh herbs (mint, basil, ulam raja, or daun kesum)\",\"1\\/4 cup kerisik (toasted grated coconut)\",\"1 salted egg (cut into halves)\",\"1 fried fish or grilled chicken\",\"For the Sambal Kelapa (Coconut Sambal):\",\"1 cup grated coconut (toasted)\",\"3 shallots (finely chopped)\",\"2 cloves garlic (minced)\",\"1 tbsp dried shrimp (pounded)\",\"1 tsp turmeric powder\",\"1\\/2 tsp salt\",\"1 tbsp sugar\"]', '[\"Step 1: Prepare the Blue Rice\",\"1.\\tBoil butterfly pea flowers in 2 cups of water for 5 minutes. Strain and keep the blue water.\",\"2.\\tWash the rice and place it in a rice cooker. Add the blue water, pandan leaf, and salt.\",\"3.\\tCook the rice as usual until fluffy.\",\"Step 2: Make the Coconut Sambal\",\"1.\\tHeat a pan and dry-toast the grated coconut until golden brown.\",\"2.\\tAdd shallots, garlic, dried shrimp, turmeric powder, salt, and sugar. Stir well and set aside.\",\"Step 3: Prepare the Garnishes\",\"1.\\tBlanch the bean sprouts in boiling water for 30 seconds. Drain.\",\"2.\\tSlice all the fresh herbs and vegetables thinly.\",\"Step 4: Assemble the Dish\",\"1.\\tPlace a serving of blue rice on a plate.\",\"2.\\tArrange the fresh herbs, bean sprouts, cabbage, and bunga kantan around the rice.\",\"3.\\tAdd a spoonful of sambal kelapa, a piece of fried fish or grilled chicken, and a salted egg.\",\"4.\\tOptionally, serve with budu (fermented anchovy sauce) for extra flavor.\"]', 'Malaysian (Kelantanese)', 60, 50, 110, '', 'Calories: ~500 kcal\r\nCarbohydrates: 80g\r\nProtein: 20g\r\nFat: 10g\r\nFiber: 5g', 'approved', 1, 0, 1, '2025-04-02 18:49:54', '2025-04-03 10:09:54', NULL, NULL, NULL),
(3, 'Penang Laksa (Asam Laksa)', 'Penang Laksa, also known as Asam Laksa, is a famous Malaysian noodle dish with a tangy, spicy, and flavorful fish broth. Unlike curry laksa, Penang Laksa uses a tamarind-based broth, giving it a sour taste, and is topped with fresh herbs, shredded fish, and pineapple. It is a signature dish from Penang, loved for its refreshing yet bold flavors.', '[\"For the Broth:\",\"500g mackerel (ikan kembung), cleaned\",\"2 liters water\",\"4 tbsp tamarind paste (asam jawa)\",\"2 stalks lemongrass (bruised)\",\"3 daun kesum (Vietnamese coriander) or mint leaves\",\"1 tbsp sugar\",\"1 tsp salt\",\"For the Spice Paste (Blend Together):\",\"5 dried chilies (soaked and deseeded)\",\"3 fresh red chilies\",\"5 shallots\",\"2 cloves garlic\",\"2 tsp shrimp paste (belacan)\",\"1 tbsp turmeric powder\",\"1 tbsp cooking oil\",\"For the Laksa Noodles & Toppings:\",\"400g thick rice noodles (laksa noodles)\",\"½ cucumber (julienned)\",\"½ pineapple (thinly sliced)\",\"1 red onion (thinly sliced)\",\"1 small bunch of mint leaves\",\"1 red chili (thinly sliced)\",\"1\\/2 cup torch ginger flower (bunga kantan), finely sliced\",\"½ cup thick shrimp paste sauce (petis udang)\"]', '[\"Step 1: Prepare the Fish Broth\",\"1.\\tBring 2 liters of water to a boil and add the cleaned mackerel. Cook for 10 minutes until the fish is fully cooked.\",\"2.\\tRemove the fish, let it cool, then debone and flake the flesh. Set aside.\",\"3.\\tStrain the fish broth to remove any impurities.\",\"Step 2: Cook the Laksa Broth\",\"1.\\tHeat a pot with 1 tbsp oil and sauté the blended spice paste until fragrant.\",\"2.\\tPour in the strained fish broth, then add lemongrass, daun kesum, tamarind paste, sugar, and salt.\",\"3.\\tSimmer for 20–30 minutes. Add the flaked fish back into the broth and continue simmering.\",\"Step 3: Prepare the Laksa Noodles\",\"1.\\tCook the laksa noodles according to the package instructions. Drain and set aside.\",\"Step 4: Assemble the Dish\",\"1.\\tPlace cooked laksa noodles in a bowl.\",\"2.\\tLadle the hot broth over the noodles.\",\"3.\\tGarnish with cucumber, pineapple, red onion, mint leaves, bunga kantan, and red chili slices.\",\"4.\\tDrizzle with thick shrimp paste sauce (petis udang) before serving.\"]', 'Malaysian (Penang)', 50, 50, 100, '', 'Calories: ~450 kcal\r\nCarbohydrates: 80g\r\nProtein: 25g\r\nFat: 5g\r\nFiber: 6g', 'approved', 1, 0, 1, '2025-04-02 19:51:20', '2025-04-03 09:58:03', NULL, NULL, NULL),
(4, 'Test Winning Recipe', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 1, 0, 6, '2025-04-16 05:51:02', '2025-04-16 05:51:02', NULL, NULL, NULL),
(5, 'Test Winning Recipe', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 1, 0, 6, '2025-04-16 05:52:51', '2025-04-16 05:52:51', NULL, NULL, NULL),
(12, 'Winning Recipe Title', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 1, 0, NULL, '2025-04-16 06:17:10', '2025-04-16 06:17:10', NULL, 'path_to_recipe_image', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `recipe_images`
--

CREATE TABLE `recipe_images` (
  `id` int(11) NOT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `image_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_images`
--

INSERT INTO `recipe_images` (`id`, `recipe_id`, `image_url`) VALUES
(3, 2, '../uploads/1743619794_nasi-kerabu1.jpg'),
(4, 2, '../uploads/1743619794_nasi-kerabu.jpg'),
(6, 3, '../uploads/1743623480_penang-laksa.jpg'),
(7, 3, '../uploads/1743624745_penang-laksa2.jpg'),
(8, 3, '../uploads/1743624745_penang-laksa1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_tags`
--

CREATE TABLE `recipe_tags` (
  `recipe_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_tags`
--

INSERT INTO `recipe_tags` (`recipe_id`, `tag_id`) VALUES
(2, 1),
(2, 3),
(3, 1),
(3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `recipe_views`
--

CREATE TABLE `recipe_views` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipe_votes`
--

CREATE TABLE `recipe_votes` (
  `vote_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `vote` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_votes`
--

INSERT INTO `recipe_votes` (`vote_id`, `user_id`, `recipe_id`, `vote`) VALUES
(1, 1, 2, 1),
(2, 4, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `structured_ingredients`
--

CREATE TABLE `structured_ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`) VALUES
(4, 'Gluten-Free'),
(3, 'Quick Meal'),
(5, 'Seafood'),
(1, 'Spicy'),
(6, 'Sweet'),
(2, 'Vegetarian');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','pending','inactive') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `avatar_url`, `password`, `role`, `created_at`, `status`) VALUES
(1, 'Pow Kah Meon', 'khmeon058@1utar.my', NULL, '$2y$10$8lWMm8bnrcEqk1PGez1eM.ot6BQT9i7PmizKtiYLs7cxSYdAzMI5S', 'user', '2025-03-28 05:30:54', 'active'),
(2, 'Carmen', 'khmeon058@gmail.com', NULL, '$2y$10$tl3lNM6lFuetmDKWD9x9oOf/naPp6/tPMlgGdssBNjDd2kWFjQGpm', 'admin', '2025-04-03 03:15:52', 'active'),
(3, 'SONG JIA SENG', 'jiaseng2106@gmail.com', NULL, '$2y$10$Is8U2Qr4coa7XCREJU.P7eAEvXviHZjNdzK.Z6pNHDuEAjWLFJEnS', 'user', '2025-04-04 00:16:20', 'active'),
(4, 'admin', 'admin@gmail.com', NULL, '$2y$10$M9m4jPxGsu8vCQT7bfhLC.O3ZwkgsNfm7an3NSNAzySDsOdwZuNYS', 'admin', '2025-04-05 05:38:58', 'active'),
(5, 'user', 'user@example.com', NULL, '$2y$10$KJvBxcYaenbWG4SiKr8Y1.ahJ89/j2WpCw2am5xaTjab.rleitAVC', 'user', '2025-04-11 06:44:04', 'pending'),
(6, 'Test Winner', 'winner@example.com', 'https://example.com/avatar.jpg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2025-04-16 05:42:41', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`competition_id`);

--
-- Indexes for table `competition_entries`
--
ALTER TABLE `competition_entries`
  ADD PRIMARY KEY (`entry_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `recipe_images`
--
ALTER TABLE `recipe_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `recipe_tags`
--
ALTER TABLE `recipe_tags`
  ADD PRIMARY KEY (`recipe_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `recipe_views`
--
ALTER TABLE `recipe_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `recipe_votes`
--
ALTER TABLE `recipe_votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `structured_ingredients`
--
ALTER TABLE `structured_ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `competition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `competition_entries`
--
ALTER TABLE `competition_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `recipe_images`
--
ALTER TABLE `recipe_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `recipe_views`
--
ALTER TABLE `recipe_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipe_votes`
--
ALTER TABLE `recipe_votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `structured_ingredients`
--
ALTER TABLE `structured_ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_entries`
--
ALTER TABLE `competition_entries`
  ADD CONSTRAINT `competition_entries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_entries_ibfk_2` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`competition_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_entries_ibfk_3` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD CONSTRAINT `meal_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `meal_plans_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`);

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `recipe_images`
--
ALTER TABLE `recipe_images`
  ADD CONSTRAINT `recipe_images_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_tags`
--
ALTER TABLE `recipe_tags`
  ADD CONSTRAINT `recipe_tags_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_views`
--
ALTER TABLE `recipe_views`
  ADD CONSTRAINT `recipe_views_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_views_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_votes`
--
ALTER TABLE `recipe_votes`
  ADD CONSTRAINT `recipe_votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_votes_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `structured_ingredients`
--
ALTER TABLE `structured_ingredients`
  ADD CONSTRAINT `structured_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
