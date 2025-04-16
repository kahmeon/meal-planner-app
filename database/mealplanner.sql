-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 04:38 PM
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

INSERT INTO `recipes` (`id`, `title`, `description`, `ingredients`, `steps`, `cuisine`, `prep_time`, `cook_time`, `total_time`, `difficulty`, `nutrition`, `status`, `is_public`, `view_count`, `created_by`, `created_at`, `updated_at`, `admin_note`) VALUES
(2, 'Nasi Kerabu', 'Nasi Kerabu is a traditional Malaysian dish featuring blue-colored rice, usually dyed with butterfly pea flowers. It is served with a variety of fresh herbs, salted egg, fried fish or chicken, and a flavorful coconut-based sambal sauce. This dish is popular in Kelantan and Terengganu, offering a unique mix of flavors and textures.', '[\"For the Blue Rice:\",\"2 cups white rice (Jasmine or Basmati)\",\"10 dried butterfly pea flowers (bunga telang)\",\"2 cups water\",\"1 pandan leaf (knotted)\",\"1\\/2 tsp salt\",\"For the Garnishes:\",\"1 cup bean sprouts (blanched)\",\"1\\/2 cup shredded cabbage\",\"1\\/2 cup finely sliced torch ginger flower (bunga kantan)\",\"1\\/2 cup fresh herbs (mint, basil, ulam raja, or daun kesum)\",\"1\\/4 cup kerisik (toasted grated coconut)\",\"1 salted egg (cut into halves)\",\"1 fried fish or grilled chicken\",\"For the Sambal Kelapa (Coconut Sambal):\",\"1 cup grated coconut (toasted)\",\"3 shallots (finely chopped)\",\"2 cloves garlic (minced)\",\"1 tbsp dried shrimp (pounded)\",\"1 tsp turmeric powder\",\"1\\/2 tsp salt\",\"1 tbsp sugar\"]', '[\"Step 1: Prepare the Blue Rice\",\"Boil butterfly pea flowers in 2 cups of water for 5 minutes. Strain and keep the blue water.\",\"Wash the rice and place it in a rice cooker. Add the blue water, pandan leaf, and salt.\",\"Cook the rice as usual until fluffy.\",\"Step 2: Make the Coconut Sambal\",\"Heat a pan and dry-toast the grated coconut until golden brown.\",\"Add shallots, garlic, dried shrimp, turmeric powder, salt, and sugar. Stir well and set aside.\",\"Step 3: Prepare the Garnishes\",\"Blanch the bean sprouts in boiling water for 30 seconds. Drain.\",\"Slice all the fresh herbs and vegetables thinly.\",\"Step 4: Assemble the Dish\",\"Place a serving of blue rice on a plate.\",\"Arrange the fresh herbs, bean sprouts, cabbage, and bunga kantan around the rice.\",\"Add a spoonful of sambal kelapa, a piece of fried fish or grilled chicken, and a salted egg.\",\"Optionally, serve with budu (fermented anchovy sauce) for extra flavor.\"]', 'Malaysian (Kelantanese)', 60, 50, 110, 'easy', 'Calories: ~500 kcal\r\nCarbohydrates: 80g\r\nProtein: 20g\r\nFat: 10g\r\nFiber: 5g', 'approved', 1, 0, 1, '2025-04-02 18:49:54', '2025-04-07 08:48:32', NULL),
(3, 'Penang Laksa (Asam Laksa)', 'Penang Laksa, also known as Asam Laksa, is a famous Malaysian noodle dish with a tangy, spicy, and flavorful fish broth. Unlike curry laksa, Penang Laksa uses a tamarind-based broth, giving it a sour taste, and is topped with fresh herbs, shredded fish, and pineapple. It is a signature dish from Penang, loved for its refreshing yet bold flavors.', '[\"For the Broth:\",\"500g mackerel (ikan kembung), cleaned\",\"2 liters water\",\"4 tbsp tamarind paste (asam jawa)\",\"2 stalks lemongrass (bruised)\",\"3 daun kesum (Vietnamese coriander) or mint leaves\",\"1 tbsp sugar\",\"1 tsp salt\",\"For the Spice Paste (Blend Together):\",\"5 dried chilies (soaked and deseeded)\",\"3 fresh red chilies\",\"5 shallots\",\"2 cloves garlic\",\"2 tsp shrimp paste (belacan)\",\"1 tbsp turmeric powder\",\"1 tbsp cooking oil\",\"For the Laksa Noodles & Toppings:\",\"400g thick rice noodles (laksa noodles)\",\"½ cucumber (julienned)\",\"½ pineapple (thinly sliced)\",\"1 red onion (thinly sliced)\",\"1 small bunch of mint leaves\",\"1 red chili (thinly sliced)\",\"1\\/2 cup torch ginger flower (bunga kantan), finely sliced\",\"½ cup thick shrimp paste sauce (petis udang)\"]', '[\"Step 1: Prepare the Fish Broth\",\"Bring 2 liters of water to a boil and add the cleaned mackerel. Cook for 10 minutes until the fish is fully cooked.\",\"Remove the fish, let it cool, then debone and flake the flesh. Set aside.\",\"Strain the fish broth to remove any impurities.\",\"Step 2: Cook the Laksa Broth\",\"Heat a pot with 1 tbsp oil and sauté the blended spice paste until fragrant.\",\"Pour in the strained fish broth, then add lemongrass, daun kesum, tamarind paste, sugar, and salt.\",\"Simmer for 20–30 minutes. Add the flaked fish back into the broth and continue simmering.\",\"Step 3: Prepare the Laksa Noodles\",\"Cook the laksa noodles according to the package instructions. Drain and set aside.\",\"Step 4: Assemble the Dish\",\"Place cooked laksa noodles in a bowl.\",\"Ladle the hot broth over the noodles.\",\"Garnish with cucumber, pineapple, red onion, mint leaves, bunga kantan, and red chili slices.\",\"Drizzle with thick shrimp paste sauce (petis udang) before serving.\"]', 'Malaysian (Penang)', 50, 50, 100, 'easy', 'Calories: ~450 kcal\r\nCarbohydrates: 80g\r\nProtein: 25g\r\nFat: 5g\r\nFiber: 6g', 'approved', 1, 0, 1, '2025-04-02 19:51:20', '2025-04-07 05:26:29', NULL),
(4, 'Nasi Lemak', 'A traditional Malaysian coconut rice dish served with spicy sambal, anchovies, peanuts, and boiled eggs.', '[\"Rice:\",\"2 cups jasmine rice\",\"200ml coconut milk\",\"2 pandan leaves\",\"1 tsp salt\",\"Sambal:\",\"2 tbsp oil\",\"1 onion, blended\",\"2 cloves garlic\",\"10 dried chilies\",\"1 tbsp tamarind juice\",\"1 tsp sugar\",\"Salt to taste\",\"Condiments:\",\"Boiled eggs\",\"Fried anchovies\",\"Roasted peanuts\",\"Cucumber slices\"]', '[\"Wash rice and drain.\",\"Cook with coconut milk, pandan, and salt.\",\"Heat oil and sauté onion, garlic, chilies.\",\"Add tamarind, sugar, and salt. Simmer sambal.\",\"Plate rice with sambal, egg, peanuts, anchovies, and cucumber.\"]', 'Malaysian', 20, 30, 50, 'easy', 'Calories: 640 per serving', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 05:03:32', NULL),
(5, 'Char Kuey Teow', 'Stir-fried flat rice noodles with prawns, egg, and bean sprouts in rich soy sauce.', '[\"200g flat rice noodles\",\"2 cloves garlic, minced\",\"2 tbsp soy sauce\",\"1 tbsp dark soy sauce\",\"1 tsp chili paste\",\"1 egg\",\"6 prawns\",\"1\\/2 cup bean sprouts\",\"2 stalks chives\",\"Fish cake slices\",\"2 tbsp oil\"]', '[\"Heat oil in wok, fry garlic and prawns.\",\"Push aside, scramble egg.\",\"Add noodles, soy sauces, chili paste.\",\"Toss with bean sprouts and chives.\",\"Serve hot with lime.\"]', 'Malaysian', 15, 10, 25, 'medium', 'Calories: 550, Protein: 20g, Carbs: 60g', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 05:45:10', NULL),
(6, 'Burger Special', 'Juicy beef burger with lettuce, tomato, and cheese in a toasted bun.', '[\"1 beef patty\",\"1 burger bun\",\"Lettuce leaves\",\"2 tomato slices\",\"Cheddar cheese slice\",\"1 tbsp mayonnaise\",\"1 tbsp ketchup\"]', '[\"Grill the patty until cooked through.\",\"Toast bun halves on skillet.\",\"Spread mayo and ketchup.\",\"Assemble with lettuce, tomato, cheese, and patty.\"]', 'Western', 10, 10, 20, 'easy', 'Calories: 480', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 06:33:51', NULL),
(7, 'Chinese Fried Rice', 'Quick and flavorful fried rice with egg, vegetables, and soy sauce.', '[\"2 cups cooked rice\", \"2 eggs\", \"1/2 cup mixed veggies\", \"1 tbsp soy sauce\", \"1 clove garlic, minced\", \"2 spring onions, chopped\"]', '[\"Scramble eggs, set aside.\", \"Stir-fry garlic, add veggies.\", \"Add rice and mix well.\", \"Return eggs, season with soy sauce.\", \"Garnish with spring onions.\"]', 'Chinese', 10, 10, 20, 'easy', '', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 02:41:50', NULL),
(8, 'Tom Yum Soup', 'Hot and sour Thai soup with shrimp, herbs, and mushrooms.', '[\"10 prawns\", \"3 cups water\", \"1 stalk lemongrass\", \"3 slices galangal\", \"4 kaffir lime leaves\", \"1 cup mushrooms\", \"2 tbsp tom yum paste\", \"2 tsp fish sauce\", \"2 tbsp lime juice\", \"2 bird’s eye chilies\"]', '[\"Boil water with lemongrass, galangal, lime leaves.\", \"Add mushrooms and tom yum paste.\", \"Add prawns and cook until pink.\", \"Season with fish sauce and lime juice.\", \"Serve hot with chili.\"]', 'Thai', 15, 15, 30, 'medium', 'Calories: 210', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 02:41:50', NULL),
(9, 'Chicken Biryani', 'Spiced basmati rice layered with marinated chicken and cooked to perfection.', '[\"2 cups basmati rice\", \"500g chicken\", \"1 cup yogurt\", \"1 onion, fried\", \"Biryani spice mix\", \"Saffron milk\", \"Mint and coriander leaves\"]', '[\"Marinate chicken with yogurt and spices.\", \"Parboil rice with whole spices.\", \"Layer rice and chicken, top with fried onion, saffron milk.\", \"Cook on low heat for 25 mins (dum).\"]', 'Indian', 30, 40, 70, 'hard', '', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 02:41:50', NULL),
(10, 'Roti Canai', 'Crispy and flaky Malaysian flatbread served with dhal or curry.', '[\"2 cups flour\", \"1/2 tsp salt\", \"2 tbsp condensed milk\", \"3/4 cup water\", \"2 tbsp oil\", \"Butter for frying\"]', '[\"Mix and knead dough, rest 4 hrs.\", \"Divide, stretch and coil dough.\", \"Flatten and fry on griddle until golden brown.\"]', 'Malaysian', 10, 10, 20, 'medium', '', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 02:41:50', NULL),
(11, 'Spaghetti Carbonara', 'Classic creamy pasta with egg, parmesan, and beef bacon.', '[\"200g spaghetti\", \"2 egg yolks\", \"1 whole egg\", \"50g parmesan\", \"100g beef bacon\", \"Salt and black pepper\"]', '[\"Cook pasta al dente.\", \"Fry bacon, set aside.\", \"Whisk eggs with cheese.\", \"Toss hot pasta with egg mix and bacon.\", \"Serve with black pepper.\"]', 'Italian', 10, 15, 25, 'medium', '', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 02:41:50', NULL),
(12, 'Ayam Masak Merah', 'Malaysian red tomato chicken stew with aromatic spices.', '[\"500g chicken pieces\", \"1 onion, blended\", \"2 cloves garlic\", \"3 tbsp tomato puree\", \"1 cinnamon stick\", \"1/2 cup coconut milk\", \"Salt and sugar to taste\"]', '[\"Fry chicken until golden, set aside.\", \"Sauté onion, garlic, and tomato puree.\", \"Add cinnamon, return chicken.\", \"Pour in coconut milk and simmer.\"]', 'Malaysian', 15, 30, 45, 'medium', '', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 02:41:50', NULL),
(13, 'Vegetable Stir Fry', 'Colorful vegetables tossed in soy-sesame sauce for a healthy dish.', '[\"1 cup broccoli florets\", \"1 carrot, sliced\", \"1/2 bell pepper\", \"1/2 cup snow peas\", \"1 tbsp soy sauce\", \"1 tsp sesame oil\", \"2 cloves garlic\"]', '[\"Heat oil and sauté garlic.\", \"Add vegetables and stir-fry 5 mins.\", \"Add soy sauce and sesame oil.\", \"Toss well and serve.\"]', 'Chinese', 10, 10, 20, 'easy', '', 'approved', 1, 0, 1, '2025-04-07 02:41:50', '2025-04-07 02:41:50', NULL),
(14, 'tt', 'ss', '[\"1\"]', '[\"1\"]', 'ss', 1, 1, 1, 'easy', '1', 'approved', 1, 0, 1, '2025-04-07 03:07:00', '2025-04-16 03:28:19', NULL),
(15, 'uu', 'wwwww', '[\"www\"]', '[\"www\"]', 'ww', 22, 22, 22, 'easy', 'www', 'rejected', 1, 0, 1, '2025-04-07 03:20:33', '2025-04-16 03:47:27', 'the recipes is unclear'),
(17, 'dwqe', 'efwef', '[\"edqw\"]', '[\"ewfef\"]', 'f32f', 33, 33, 33, '', 'ewfewf', 'pending', 1, 0, 2, '2025-04-07 05:06:09', '2025-04-07 05:06:09', NULL),
(18, 'qwfdwqf', 'ewfef', '[\"dcvsda\"]', '[\"wefaef\"]', 'ewfewf', 22, 22, 22, 'medium', 'ewfwf', 'pending', 1, 0, 2, '2025-04-07 05:12:17', '2025-04-07 05:12:17', NULL),
(19, 'egver', 'gregwe', '[\"egfwes\"]', '[\"ewfw\"]', 'eswe', 77, 77, 77, 'medium', 'ewffw', 'pending', 1, 0, 2, '2025-04-07 05:30:55', '2025-04-07 05:30:55', NULL),
(20, 'gerger', 'ewfwef', '[\"32e23q\"]', '[\"r32r\"]', 'ewffw', 44, 44, 44, 'hard', 'r3qwr', 'draft', 1, 0, 1, '2025-04-07 06:13:16', '2025-04-07 06:13:16', NULL),
(21, 'private', 'egrger', '[\"ergerg\"]', '[\"refer\"]', 'revgerg', 55, 55, 55, 'easy', 'wersgfe', 'approved', 0, 0, 1, '2025-04-07 06:15:17', '2025-04-07 06:15:34', NULL),
(22, 'ttt', 'ttt', '[\"wwwew\"]', '[\"wwww\"]', 'ttt', 50, 50, 100, 'medium', 'yyyy', 'pending', 1, 0, 3, '2025-04-07 09:07:34', '2025-04-07 09:07:52', NULL);

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
(21, 14, 'uploads/recipes/1743996731_nasi-lemak.webp'),
(22, 4, 'uploads/recipes/1743996773_nasi-lemak.webp'),
(23, 17, '/uploads/recipes/1744002369_nasi-lemak.webp'),
(24, 18, '/uploads/recipes/1744002737_nasi-lemak.webp'),
(25, 2, '/uploads/recipes/1744003544_nasi-kerabu1.jpg'),
(26, 2, '/uploads/recipes/1744003544_nasi-kerabu.jpg'),
(27, 3, '/uploads/recipes/1744003565_penang-laksa2.jpg'),
(28, 3, '/uploads/recipes/1744003565_penang-laksa1.jpg'),
(29, 3, '/uploads/recipes/1744003565_penang-laksa.jpg'),
(30, 19, '/uploads/recipes/1744003855_char-koay-teow.jpg'),
(31, 19, '/uploads/recipes/1744003874_char-kway-teow-15.jpg'),
(32, 19, '/uploads/recipes/1744003874_char-koay-teow1.jpg'),
(33, 19, '/uploads/recipes/1744003874_char-koay-teow.jpg'),
(34, 5, '/uploads/recipes/1744004710_char-kway-teow-15.jpg'),
(35, 5, '/uploads/recipes/1744004710_char-koay-teow1.jpg'),
(36, 5, '/uploads/recipes/1744004710_char-koay-teow.jpg'),
(37, 6, '/uploads/recipes/1744005793_burger-special2.jpg'),
(38, 6, '/uploads/recipes/1744005793_burger-special1.jpg'),
(39, 6, '/uploads/recipes/1744005793_burger-special.jpg'),
(40, 21, '/uploads/recipes/1744006517_nasi-lemak.webp'),
(41, 22, '/uploads/recipes/1744016854_burger-special2.jpg'),
(42, 22, '/uploads/recipes/1744016854_burger-special1.jpg'),
(43, 22, '/uploads/recipes/1744016854_burger-special.jpg'),
(44, 15, '/uploads/recipes/1744741106_burger-special2.jpg');

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
(3, 5),
(4, 1),
(4, 3),
(6, 3),
(15, 3),
(17, 3),
(18, 5),
(19, 1),
(20, 1),
(21, 3),
(22, 3);

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
-- Table structure for table `saved_recipes`
--

CREATE TABLE `saved_recipes` (
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_recipes`
--

INSERT INTO `saved_recipes` (`user_id`, `recipe_id`, `saved_at`) VALUES
(1, 2, '2025-04-15 18:11:11'),
(1, 4, '2025-04-15 18:09:25'),
(2, 2, '2025-04-15 16:30:54'),
(3, 2, '2025-04-16 14:26:53');

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Pow Kah Meon', 'khmeon058@1utar.my', '$2y$10$8lWMm8bnrcEqk1PGez1eM.ot6BQT9i7PmizKtiYLs7cxSYdAzMI5S', 'user', '2025-03-28 05:30:54'),
(2, 'Carmen', 'khmeon058@gmail.com', '$2y$10$tl3lNM6lFuetmDKWD9x9oOf/naPp6/tPMlgGdssBNjDd2kWFjQGpm', 'admin', '2025-04-03 03:15:52'),
(3, 'User', 'user@example.com', '$2y$10$ZruuRnCgeLzPZ9kh3funheJyP.I.wmwy5g3a4Os2h57mCVXoIeYQm', 'user', '2025-04-06 20:44:40'),
(4, 'lennon tan', 'lennontan1232@gmail.com', '$2y$10$tkKh2kE5wPCo4n/ms6971u0oEK457UkfzECufPQ9WR3x6PSpWoSEa', 'admin', '2025-04-12 00:55:21');

-- --------------------------------------------------------

--
-- Table structure for table `user_community`
--

CREATE TABLE `user_community` (
  `user_comm_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_avatar_id` int(11) NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_community`
--

INSERT INTO `user_community` (`user_comm_id`, `user_id`, `user_avatar_id`, `update_at`) VALUES
(1, 4, 3, '2025-04-12 18:06:03');

-- --------------------------------------------------------

--
-- Table structure for table `user_favor`
--

CREATE TABLE `user_favor` (
  `id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
>>>>>>> 4efdf603a00a9f0623a827973dc1fb469d85881a

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
-- Indexes for table `saved_recipes`
--
ALTER TABLE `saved_recipes`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `fk_recipe` (`recipe_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `recipe_images`
--
ALTER TABLE `recipe_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Constraints for table `saved_recipes`
--
ALTER TABLE `saved_recipes`
  ADD CONSTRAINT `fk_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_recipes_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `structured_ingredients`
--
ALTER TABLE `structured_ingredients`
  ADD CONSTRAINT `structured_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
