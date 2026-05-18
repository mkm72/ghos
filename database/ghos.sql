-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 18, 2026 at 04:51 PM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ghos`
--

-- --------------------------------------------------------

--
-- Table structure for table `Business_Applications`
--

CREATE TABLE `Business_Applications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `business_name` varchar(200) NOT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `business_email` varchar(200) DEFAULT NULL,
  `website` varchar(300) DEFAULT NULL,
  `sales_volume` varchar(100) DEFAULT NULL,
  `key_source` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Business_Applications`
--

INSERT INTO `Business_Applications` (`id`, `user_id`, `business_name`, `reason`, `status`, `created_at`, `reviewed_at`, `first_name`, `last_name`, `business_email`, `website`, `sales_volume`, `key_source`) VALUES
(1, 8, 'sony', '', 'approved', '2026-05-11 20:28:10', '2026-05-11 20:29:09', 'yousef', 'ali', 'yousef@gmail.com', 'https://www.sony-mea.com/', 'Less than $1,000', 'Retail Box Scans'),
(2, 11, 'the-best-seller', '', 'approved', '2026-05-13 15:36:36', '2026-05-13 15:36:56', 'Mohammed', 'Almuallem', 'seller@ghos.com', '', 'Less than $1,000', 'Official Publisher / Developer');

-- --------------------------------------------------------

--
-- Table structure for table `Cart`
--

CREATE TABLE `Cart` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `game_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Cart`
--

INSERT INTO `Cart` (`id`, `user_id`, `game_id`, `quantity`, `session_id`) VALUES
(16, NULL, 28154, 1, 'obf6o8mpd4f9cv2hchhegmr9e8'),
(24, 10, 4544, 1, NULL),
(26, NULL, 28199, 1, 'jnhpi247ud4gef1fegcvdnnv5v'),
(33, NULL, 727319, 5, 'a0m2l6m37kib4r74dc3lr03vr2'),
(60, NULL, 43050, 1, '0kdh908qsptb86gpdtf4jed21b');

-- --------------------------------------------------------

--
-- Table structure for table `Games`
--

CREATE TABLE `Games` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `platform` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `genres` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `seller_id` int DEFAULT NULL,
  `min_requirements` text COLLATE utf8mb4_unicode_ci,
  `recommended_requirements` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Games`
--

INSERT INTO `Games` (`id`, `name`, `description`, `price`, `platform`, `genres`, `rating`, `seller_id`, `min_requirements`, `recommended_requirements`) VALUES
(28, 'Red Dead Redemption 2', 'America, 1899. The end of the wild west era has begun as lawmen hunt down the last remaining outlaw gangs. Those who will not surrender or succumb are killed. \r\n\r\nAfter a robbery goes badly wrong in the western town of Blackwater, Arthur Morgan and the Van der Linde gang are forced to flee. With federal agents and the best bounty hunters in the nation massing on their heels, the gang must rob, steal and fight their way across the rugged heartland of America in order to survive.', 14.99, 'PC, Xbox One, PlayStation 4', 'Action', 4.59, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 7 - Service Pack 1 (6.1.7601)\r\nProcessor: Intel® Core™ i5-2500K / AMD FX-6300\r\nMemory: 8 GB RAM\r\nGraphics: Nvidia GeForce GTX 770 2GB / AMD Radeon R9 280 3GB\r\nNetwork: Broadband Internet connection\r\nStorage: 150 GB available space\r\nSound Card: Direct X Compatible', NULL),
(33, 'Final Fantasy XII: The Zodiac Age', 'Day-1 Edition\r\nPurchase at launch to receive three audio tracks and exclusive wallpaper created by original game artist Isamu Kamikokuryo plus a 20% launch discount (Offer ends 8th Feb 2018)\r\nAbout the GameFINAL FANTASY XII THE ZODIAC AGE - This revered classic returns, now fully remastered for the first time for PC, featuring all new and enhanced gameplay.\r\nRETURN TO THE WORLD OF IVALICE\r\nEnter an era of war within the world of Ivalice.', 49.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'RPG', 4.23, NULL, '', ''),
(39, 'Prey', 'Prey (2017) is a single-player sci-fi first-person shooter developed by Arkane Studios Austin and published by Bethesda Softworks. While it is technically a reboot of the 2006 game of the same name developed by Human Head Studios, of which the sequel suffered a fall into development hell before the license was sold to Bethesda Softworks, there is no relation between the stories and universes of the two games.\r\n\r\nThe game is set in the near future of an alternate reality.', 29.99, 'PC, Xbox One, PlayStation 4', 'Shooter, Action, RPG', 4.28, NULL, '', ''),
(42, 'What Remains of Edith Finch', 'The Finch\'s family, also known as \"America\'s most unfortunate family\", believes that the family is being pursued by a deadly curse. Each generation has only one child who survived to give birth to the next one.\r\n\r\nThe player begins to act as Edith Finch, who arrives in an orderly abandoned family mansion to find out what opens the key that she received from her mother along with the will.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS', 'Indie, Adventure', 4.38, NULL, '7 / 8 / 10Processor: Intel i3 2125 3.30 GHz or laterMemory: 2 GB RAMGraphics: GeForce GTX 750/AMD Radeon 7790 or laterStorage: 5 GB available spaceMouse, Keyboard', ''),
(111, 'Forgotten Memories: Remastered Edition', 'Finalist in the CVA videogames awards 2015. Nominations: Best Audio, Best Original Music, Best Indie Game and Best iOS Game!\r\n\"The most exciting horror game of 2015.\" - AppSpy\r\n\"The developers have managed to craft a genuinely unsettling game with a moody atmosphere and masterfully engineered scares.\" - AppSpy\r\n\"Forgotten Memories is an atmospheric tale of horror\" - 148Apps\r\n\"It\'s a well put-together tribute to survival horror games of yesteryear.', 7.99, 'PC, Nintendo Switch, iOS, Android', 'Indie, Adventure, Action, Simulation', 4.25, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10+\r\nProcessor: Intel Core 2 Duo Q6867\r\nMemory: 2 GB RAM\r\nGraphics: DirectX 9/OpenGL 4.1 capable GPU\r\nDirectX: Version 9.0\r\nStorage: 300 MB available space', ''),
(115, 'Zero Escape: The Nonary Games', 'Kidnapped and taken to an unfamiliar location, nine people find themselves forced to participate in a diabolical Nonary Game by an enigmatic mastermind called Zero. Why were they there? Why were they chosen to put their lives on the line as part of a dangerous life and death game? Who can be trusted? Tensions rise as the situation becomes more and more dire, and the nine strangers must figure out how to escape before they wind up dead.', 2.99, 'PC, Xbox One, PlayStation 4, PS Vita', 'Adventure', 4.38, NULL, 'Minimum:\r\nOS: Windows 7\r\nProcessor: Intel Core i3-530 CPU 2.93 GHz or better\r\nMemory: 4 GB RAM\r\nGraphics: DirectX 9.0 compatible GPU with at least 1GB of VRAM\r\nDirectX: Version 9.0\r\nStorage: 4 GB available space\r\nSound Card: DirectX 9.0c compatible sound card', NULL),
(250, 'The Binding of Isaac: Rebirth', 'The Binding of Isaac: Rebirth is a remake of The Binding of Isaac.\r\n\r\nThe plot is based on a biblical story. Little Isaac and his mother live happily in a small house on the hill. And suddenly the mother heard a voice, which told her that her son is defiled by sins and must be saved. The voice asks the woman to remove all evil from Isaac to save him. There are 12 endings in the game.', 14.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, Nintendo 3DS, macOS, Linux, PS Vita, Wii U', 'Action, RPG', 4.32, NULL, 'Minimum:\r\nOS: XP\r\nProcessor: Core 2 Duo\r\nMemory: 2 GB RAM\r\nGraphics: Discreet video card\r\nStorage: 449 MB available space\r\nSound Card: Yes', ''),
(320, 'Night in the Woods', 'Night in the Woods is a quest game developed by Infinite Fall.\r\n\r\n###Plot\r\nNight in the Woods takes place in a universe of anthropomorphic animals. A cat named May returns back to her hometown of Possum Springs because of a dissociative disorder that does not let her recognize other people something other than rectangular shapes. May had dropped out of college and she does not know what to do with her life yet.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, macOS, Linux', 'Adventure', 4.25, NULL, 'Minimum:\r\nOS: Windows 7\r\nProcessor: Intel i5 Quad-Core\r\nMemory: 4 GB RAM\r\nGraphics: Intel HD 4000\r\nStorage: 8 GB available space\r\nAdditional Notes: 32-bit systems must use virtual memory to get over 2GB.', ''),
(455, 'Threes!', 'Threes is tiny puzzle that grows on you. This is the ad-free version.\r\n~ Apple Game of the Year 2014!!\r\n~ Apple Design Award 2014 Winner\r\n∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞∞\r\n“You might as well delete Candy Crush Saga now.” ~ Pocket Gamer\r\n“It\'s surprisingly adorable, for a game starring numbers.” ~ Joystiq\r\n“It’s the kind of game that embosses the rules on your brain within 30 seconds, but then compels you to spend the next two hours playing.', 5.99, 'PC, Xbox One, iOS, Android, macOS', 'Educational, Puzzle, Casual, Card, Indie', 4.29, NULL, 'Minimum:\r\nOS *: Windows 7 or later\r\nProcessor: Intel Core 2 Duo / AMD Athlon 64 or equivalent\r\nMemory: 2 GB RAM\r\nGraphics: DirectX 10 compatible GPU\r\nDirectX: Version 10\r\nStorage: 500 MB available space', ''),
(591, 'Monument Valley', '** Apple Game of the Year 2014 **\r\n** Winner of Apple Design Award 2014 **\r\nIn Monument Valley you will manipulate impossible architecture and guide a silent princess through a stunningly beautiful world.\r\nMonument Valley is a surreal exploration through fantastical architecture and impossible geometry. Guide the silent princess Ida through mysterious monuments, uncovering hidden paths, unfolding optical illusions and outsmarting the enigmatic Crow People.\r\nIda\'s Dream now available.', 2.71, 'PC, iOS, Android', 'Casual, Adventure, Puzzle', 4.40, NULL, NULL, NULL),
(622, 'XCOM: Enemy Within', '***NOTE: Compatible with iPad 3, iPad mini 2, iPhone 5 and up. WILL NOT be able to run on earlier generations, despite being able to purchase them on those devices***\r\nXCOM®: Enemy Within is a standalone expansion to the 2012 strategy game of the year XCOM: Enemy Unknown and it\'s now available on iOS devices!  Enemy Within features the core gameplay of Enemy Unknown plus more exciting content.', 5.99, 'PC, Xbox One, iOS, Android, Xbox 360, PlayStation 3, PS Vita', 'Strategy, Action, Simulation', 4.46, NULL, NULL, NULL),
(654, 'Stardew Valley', 'The hero (in the beginning you can choose gender, name and appearance) - an office worker who inherited an abandoned farm. The landscape of the farm can also be selected. For example, you can decide whether there will be a river nearby for fishing.\r\nThe farm area needs to be cleared, and it will take time.', 7.48, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, Android, macOS, Linux, PS Vita', 'Indie, RPG, Simulation', 4.39, NULL, NULL, NULL),
(857, 'Halo: The Master Chief Collection', 'Halo: The Master Chief Collection is a bundle of Halo remasters developed by Bungie and 343 industries.\r\n\r\nThe bundle features Halo: Combat Evolved Anniversary, Halo 2 Anniversary, Halo 3 and Halo 4. All four games are distributed on one disc and are accessible through a unified interface. It is possible to play any mission from all four games right from the beginning of the game.', 39.99, 'PC, Xbox One', 'Adventure, Action', 4.25, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Microsoft Windows\r\nNetwork: Broadband Internet connection', ''),
(923, 'Titanfall 2', 'Now Titanfall 2 has a full-scale story campaign. The main plot is the confrontation of the people\'s Militia against the IMC Corporation, which seeks to destroy the rebels of the Frontier, a region of star systems that will allow them to get control over their resources.\r\n\r\nYou play as Jack Cooper, a soldier who dreams of becoming an elite pilot with advanced technology and a personal Titan - a fighting machine. Captain Tai Lastimosa trained Cooper and prepared for his candidacy.', 29.99, 'PC, Xbox One, PlayStation 4', 'Shooter, Action', 4.31, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Win 7/8/8.1/10 64bit\r\nProcessor: Intel Core i3-6300t or equivalent [4 or more hardware threads]\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA Geforce GTX 660 2GB or AMD Radeon HD 7850 2GB\r\nDirectX: Version 11\r\nStorage: 45 GB available space', ''),
(1299, 'Mount & Blade: Warband', 'In a land torn asunder by incessant warfare, it is time to assemble your own band of hardened warriors and enter the fray. Lead your men into battle, expand your realm, and claim the ultimate prize: the throne of Calradia!\r\nMount & Blade: Warband is the eagerly anticipated stand alone expansion pack for the game that brought medieval battlefields to life with its realistic mounted combat and detailed fighting system.', 19.99, 'PC, Xbox One, PlayStation 4, Android, macOS, Linux', 'Strategy, Action, RPG', 4.36, NULL, 'Minimum:\r\nOS: Windows® XP\r\nProcessor: Intel Pentium 4 2.0 GHz or AMD 2.5 GHz\r\nMemory: 512MB RAM\r\nGraphics: 3D graphics card with 64MB RAM\r\nHard Drive: 100 MB available space \r\nSound: Standard audio\r\n', ''),
(1358, 'Papers, Please', 'The creator of the game often travelled through Asia and made the observation that the work of an immigration officer checking documents for entry is simultaneously very monotonous and very responsible. The game reproduces this work - but scammers and unusual situations occur in it much more often than in reality. The task of the player-officer is not to make a mistake, not to let an unwanted guest into the country. He has power, directories, translucent devices, etc.', 9.99, 'PC, iOS, Android, macOS, Linux, PS Vita', 'Educational, Indie, Simulation, Puzzle', 4.37, NULL, 'Minimum:\r\nOS: Windows XP or later\r\nProcessor: 1.5 GHz Core2Duo\r\nMemory: 2 GB RAM\r\nGraphics: OpenGL 1.4 or better\r\nStorage: 100 MB available space\r\nAdditional Notes: Minimum 1280x720 screen resolution', NULL),
(1450, 'INSIDE', 'INSIDE is a platform adventure game that transfers the atmosphere of a dystopic world. Players assume the role of a lonely boy, who walks through the monochromatic 2.5D environment and solves various puzzles. By the time main antagonists of the character pursue him throughout the whole world. The main storyline follows the unnamed boy through the in-game world locations including a forest, a farm, and a fictional laboratory, where experiments on bodies are held.', 2.48, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, macOS', 'Adventure, Action, Puzzle, Indie, Platformer', 4.38, NULL, 'Minimum:\r\nOS: Windows 7/10\r\nProcessor: Dual Core\r\nMemory: 512 MB RAM\r\nGraphics: Supported\r\nStorage: 10 MB available space', NULL),
(1458, 'Bug Princess', '###　BUG PRINCESS ###\r\nThe legendary arcade shooter \"Bug Princess\" (Mushihimesama) arrives for iPhone, iPod touch and iPad!\r\nTake control of Princess Reco and dodge through massive bullet storms to save the village of Hoshifuri!\r\n* Please note this application is the arcade version.\r\n### GAME FEATURES ###\r\n● THREE UNIQUE GAME MODES, FOUR DIFFICULTIES\r\n- Original Mode: Moderate difficulty.\r\n- Maniac Mode  : Intense and frenetic gameplay.\r\n- Ultra Mode   : Sheer bullet hell.', 9.99, 'PC, Nintendo Switch, iOS', 'Casual, Arcade, Action', 4.46, NULL, 'Minimum:\r\nOS: Windows 7/8/8.1/10\r\nProcessor: Intel Core i3 2GHz or higher.\r\nMemory: 2 GB RAM\r\nGraphics: Intel HD Graphics 4000, Geforce 9500GT, Radeon HD 3650 or better\r\nDirectX: Version 9.0c\r\nStorage: 1500 MB available space\r\nSound Card: DirectSound-compatible sound card', ''),
(1682, 'The Wolf Among Us', 'The Wolf Among Us is a five-part episodic game relying heavily on dialogues and choices of the player. The game is considered a prequel to Bill Willingham\'s \'Fables\' comic book and features usual TellTale stylistics: cartoon-like graphics, comparing your choices to the decisions of the other players and QTEs. \'The Wolf\' is the first part of the series with a promised expansion to the second season coming out in 2019.', 8.99, 'PC, PlayStation 4, Xbox One, iOS, Android, macOS, Xbox 360, PS Vita', 'Adventure', 4.39, NULL, 'Minimum:\r\nOS: Windows XP Service Pack 3\r\nProcessor: Core 2 Duo 2GHz or equivalent\r\nMemory: 3 GB RAM\r\nGraphics: ATI or NVidia card w/ 512 MB RAM\r\nDirectX: Version 9.0c\r\nStorage: 2 GB available space\r\nSound Card: Direct X 9.0c sound device\r\nAdditional Notes: Not recommended for Intel integrated graphics', NULL),
(1758, 'Valiant Hearts: The Great War', 'Valiant Hearts is a sidescroller adventure with a significant amount of attention paid to artwork and historical authenticity. While being not so demanding on a software side, it was released on the mobile platforms as well as on the big consoles. Such variety of platforms, however, limits the gameplay peculiarities that can be featured within a game. \r\nThe player takes a role of one of four characters on the Belgian, American, French and German side of the Great War conflict — World War I.', 3.74, 'PC, PlayStation 4, Xbox One, Nintendo Switch, iOS, Android, Xbox 360, PlayStation 3', 'Adventure, Puzzle', 4.36, NULL, '', ''),
(2020, 'The Room', 'Fall into a world of bizarre contraptions and alchemical machinery with The Room, a BAFTA award-winning 3D puzzler from Fireproof Games. Follow a trail of cryptic letters and solve many unique devices in ever more extraordinary places, on a time-spanning journey where machinery meets myth.\r\nThe Room PC is a fully-enhanced HD release of Apple\'s 2012 iPad Game Of The Year, including the \'EPILOGUE\' DLC that adds 20% more content and play time to the original release.', 4.99, 'PC, Nintendo Switch, iOS, Android', 'Indie, Puzzle', 4.23, NULL, 'Minimum:\r\nOS: WindowsXP SP2 or higher\r\nProcessor: 2.0 GHz Dual Core Processor\r\nMemory: 2 GB RAM\r\nGraphics: Video card with 512MB of VRAM\r\nDirectX: Version 9.0\r\nStorage: 1 GB available space', ''),
(2188, 'The Room Three', 'Continuing the critically acclaimed ‘The Room’ game series, Fireproof Games are proud to bring the third instalment to PC.\r\nThe Room Three continues the tactile puzzle-solving gameplay of its predecessors while considerably expanding the world for the player to explore. Once again, Fireproof Games have re-built, re-textured and re-lit every asset and environment to bring the mysterious world of The Room to life.', 5.99, 'PC, iOS, Android', 'Indie, Adventure, Puzzle', 4.34, NULL, 'Minimum:\r\nOS: Windows 7 or higher\r\nProcessor: 2.5 GHz Dual Core Processor\r\nMemory: 4 GB RAM\r\nGraphics: Video card with 1024MB of VRAM\r\nDirectX: Version 10\r\nStorage: 4 GB available space', ''),
(2454, 'DOOM (2016)', 'Return of the classic FPS, Doom (2016) acts as a reboot of the series and brings back the Doomslayer, protagonist of the original Doom games. In order to solve the energy crisis, humanity learned to harvest the energy from Hell, and when something went wrong and a demon invasion has started, it’s up to the player to control the Doomslayer and destroy the evil.', 3.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Shooter, Action', 4.38, NULL, 'Minimum:\r\nOS: Windows7,Windows8,Windows10\r\nProcessor: Intel cpu i3\r\nMemory: 4 GB RAM\r\nGraphics: GTX 650\r\nStorage: 2 GB available space\r\nSound Card: Realtek', NULL),
(2551, 'Dark Souls III', 'Dark Souls III is the fourth installment in the Dark Souls series, now introducing the players to the world of Lothric, a kingdom which has suffered the fate similar to its counterparts from the previous games, descending from its height to utter darkness. A new tale of dark fantasy offers to create and guide the path of game’s protagonist, the Ashen One, through the dangers of the world before him.', 59.98, 'PC, Xbox One, PlayStation 4', 'Action, RPG', 4.40, NULL, 'Minimum:\r\nOS: Windows 7 SP1 64bit, Windows 8.1 64bit Windows 10 64bit\r\nProcessor: Intel Core i3-2100 / AMD® FX-6300\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA® GeForce GTX 750 Ti / ATI Radeon HD 7950\r\nDirectX: Version 11\r\nNetwork: Broadband Internet connection\r\nStorage: 25 GB available space\r\nSound Card: DirectX 11 sound device\r\nAdditional Notes: Internet connection required for online play and product activation', NULL),
(3287, 'Batman: Arkham Knight', 'Batman: Arkham Knight is the final instalment for the Arkham series by now. Joining forces with Bruce Wayne for the last time, we have to oppose Scarecrow and other iconic villains such as The Riddler, Harleen Quinzel a.k.a. Harley Quinn, Penguin and others.\r\n\r\nThe story continued after events in Arkham City when Joker died due to infection in his blood. Now, Scarecrow tries to release a new fear toxin, meanwhile new mysterious Arkham Knight plots against Batman as well.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Action', 4.24, NULL, 'Minimum:\r\nOS: Win 7 SP1, Win 8.1 (64-bit Operating System Required)\r\nProcessor: Intel Core i5-750, 2.67 GHz | AMD Phenom II X4 965, 3.4 GHz\r\nMemory: 6 GB RAM\r\nGraphics: Graphics: NVIDIA GeForce GTX 660 (2 GB Memory Minimum)  | AMD Radeon HD 7870 (2 GB Memory Minimum)\r\nDirectX: Version 11\r\nNetwork: Broadband Internet connection\r\nStorage: 45 GB available space', ''),
(3328, 'The Witcher 3: Wild Hunt', 'The third game in a series, it holds nothing back from the player. Open world adventures of the renowned monster slayer Geralt of Rivia are now even on a larger scale. Following the source material more accurately, this time Geralt is trying to find the child of the prophecy, Ciri while making a quick coin from various contracts on the side. Great attention to the world building above all creates an immersive story, where your decisions will shape the world around you.', 7.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, macOS', 'Action, RPG', 4.64, NULL, NULL, NULL),
(3332, 'FINAL FANTASY X/X-2 HD Remaster', 'FINAL FANTASY X tells the story of a star blitzball player, Tidus, who journeys with a young and beautiful summoner named Yuna on her quest to save the world of Spira from an endless cycle of destruction wrought by the colossal menace Sin.\r\nFINAL FANTASY X-2 returns to the world of Spira two years after the beginning of the Eternal Calm.', 29.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, PlayStation 3, PS Vita', 'RPG', 4.34, NULL, 'Minimum:\r\nOS: Windows Vista or later\r\nProcessor: 2GHz Dual Core CPU\r\nMemory: 1 GB RAM\r\nGraphics: NVIDIA Geforce 9600GT VRAM 512MB or later / ATI Radeon HD 2600XT VRAM 512MB or later\r\nStorage: 37 GB available space\r\nSound Card: DirectX Compatible Sound Card', ''),
(3363, 'Shovel Knight', 'Shovel Knight: Treasure Trove is the full and complete edition of Shovel Knight, a sweeping classic action adventure game series with awesome gameplay, memorable characters, and an 8-bit retro aesthetic! Become Shovel Knight, wielder of the Shovel Blade, as he runs, jumps, and battles in a quest for his lost beloved. Take down the nefarious knights of the Order of No Quarter and their menacing leader, The Enchantress.\r\nBut that’s not everything!', 39.99, 'PC, PlayStation 4, Xbox One, Nintendo Switch, Nintendo 3DS, macOS, Linux, PlayStation 3, PS Vita, Wii U', 'Indie, Platformer, Adventure, Action', 4.29, NULL, 'Minimum:\r\nOS: Windows XP SP2\r\nProcessor: Intel Core 2 Duo 2.1 ghz or equivalent\r\nMemory: 2 GB RAM\r\nGraphics: 2nd Generation Intel Core HD Graphics (2000/3000), 512MB\r\nDirectX: Version 9.0\r\nStorage: 250 MB available space', ''),
(3364, 'Shovel Knight: Treasure Trove', 'Shovel Knight: Treasure Trove is an indie platformer game developed by Yacht Club Games. The development of the game was funded through Kickstarter.\r\n\r\n###Treasure Trove Edition features\r\nTreasure Trove is a complete Shovel Knight package. This edition features all the campaigns released to this day. \r\n\r\n###Setting\r\nThe base campaign named Shovel Of Hope tells the tale of a vigilant Shovel Knight. He got separated from his lover, Shield Knight while exploring the mysterious Tower of Destiny.', 39.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, Android, Nintendo 3DS, macOS, Linux, PS Vita, Wii U', 'Platformer, Adventure, Action', 4.31, NULL, 'Windows XP SP2 Processor: Intel Core 2 Duo 2.1 ghz or equivalent Memory: 2 GB RAM Graphics: 2nd Generation Intel Core HD Graphics (2000/3000), 256MB DirectX: Version 9.0 Hard Drive: 250 MB available space', ''),
(3408, 'Hotline Miami 2: Wrong Number', 'Hotline Miami 2: Wrong number is a sequel to Hotline Miami.\r\n\r\n1985. There is a Cold war between the Soviet Union and the USA, which gradually turns into a hot war. In Hawaii, there are military operations. In the centre of the event, the squad \"Ghost Wolves\", which includes Dan Smith named \"Beard\", Jacket (the main character of the first part), and two soldiers. The United States, for this moment, are losing. The squad was ordered to capture the most dangerous Soviet zone.', 14.99, 'PC, PlayStation 4, Nintendo Switch, iOS, Android, macOS, Linux, PlayStation 3, PS Vita', 'Indie, Shooter, Arcade, Action', 4.29, NULL, 'Minimum:\r\nOS: Microsoft® Windows® Vista / 7 / 8\r\nProcessor: 2.4 GHz Intel Core 2 Duo or better\r\nMemory: 1 GB RAM\r\nGraphics: OpenGL 3.2 compatible GPU with at least 256MB of VRAM\r\nDirectX: Version 9.0c\r\nStorage: 600 MB available space\r\nAdditional Notes: PS4 or Xbox 360 Controller or Direct Input compatible controller', ''),
(3498, 'Grand Theft Auto V', 'Rockstar Games went bigger, since their previous installment of the series. You get the complicated and realistic world-building from Liberty City of GTA4 in the setting of lively and diverse Los Santos, from an old fan favorite GTA San Andreas. 561 different vehicles (including every transport you can operate) and the amount is rising with every update.', 14.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Xbox 360, PlayStation 3', 'Action', 4.47, NULL, 'Minimum:OS: Windows 10 64 Bit, Windows 8.1 64 Bit, Windows 8 64 Bit, Windows 7 64 Bit Service Pack 1, Windows Vista 64 Bit Service Pack 2* (*NVIDIA video card recommended if running Vista OS)Processor: Intel Core 2 Quad CPU Q6600 @ 2.40GHz (4 CPUs) / AMD Phenom 9850 Quad-Core Processor (4 CPUs) @ 2.5GHzMemory: 4 GB RAMGraphics: NVIDIA 9800 GT 1GB / AMD HD 4870 1GB (DX 10, 10.1, 11)Storage: 72 GB available spaceSound Card: 100% DirectX 10 compatibleAdditional Notes: Over time downloadable content and programming changes will change the system requirements for this game.  Please refer to your hardware manufacturer and www.rockstargames.com/support for current compatibility information. Some system components such as mobile chipsets, integrated, and AGP graphics cards may be incompatible. Unlisted specifications may not be supported by publisher.     Other requirements:  Installation and online play requires log-in to Rockstar Games Social Club (13+) network; internet connection required for activation, online play, and periodic entitlement verification; software installations required including Rockstar Games Social Club platform, DirectX , Chromium, and Microsoft Visual C++ 2008 sp1 Redistributable Package, and authentication software that recognizes certain hardware attributes for entitlement, digital rights management, system, and other support purposes.     SINGLE USE SERIAL CODE REGISTRATION VIA INTERNET REQUIRED; REGISTRATION IS LIMITED TO ONE ROCKSTAR GAMES SOCIAL CLUB ACCOUNT (13+) PER SERIAL CODE; ONLY ONE PC LOG-IN ALLOWED PER SOCIAL CLUB ACCOUNT AT ANY TIME; SERIAL CODE(S) ARE NON-TRANSFERABLE ONCE USED; SOCIAL CLUB ACCOUNTS ARE NON-TRANSFERABLE.  Partner Requirements:  Please check the terms of service of this site before purchasing this software.', NULL),
(3727, 'FINAL FANTASY XIV: A Realm Reborn', 'FINAL FANTASY XIV: A Realm Reborn is a massively multiplayer RPG developed by Square Enix. It is the direct continuation of FINAL FANTASY XIV.\r\n\r\nFFXIV: A Realm Reborn is a heavily updated version of the original FINAL FANTASY XIV with a different graphics engine, netcode, and storyline. The game was made because the game sold poorly and wasn\'t met with critical acclaim.\r\n\r\n###Plot\r\nThe game takes place in  Eorzea five years after the events of the original game.', 19.99, 'PC, PlayStation 5, PlayStation 4, macOS, PlayStation 3', 'Massively Multiplayer, RPG', 4.22, NULL, 'Minimum:\r\nOS: Windows® 7 32/64 bit, Windows® 8.1 32/64 bit, Windows® 10 32/64 bit\r\nProcessor: Intel® Core™i5 2.4GHz or higher\r\nMemory: 3 GB RAM\r\nGraphics: 1280 x 720: NVIDIA® Geforce® GTX750 or higher, AMD Radeon™ R7 260X or higher\r\nDirectX: Version 9.0c\r\nNetwork: Broadband Internet connection\r\nStorage: 60 GB available space\r\nSound Card: DirectSound® sound card (DirectX® 9.0c or higher)\r\nAdditional Notes: System Requirements may be subject to change. If you are using a router, please set up your ports so that the below packets can pass through. [Ports that may be used] TCP：80, 443, 54992～54994, 55006～55007, 55021～55040', ''),
(4062, 'BioShock Infinite', 'The third game in the series, Bioshock takes the story of the underwater confinement within the lost city of Rapture and takes it in the sky-city of Columbia. Players will follow Booker DeWitt, a private eye with a military past; as he will attempt to wipe his debts with the only skill he’s good at – finding people. Aside from obvious story and style differences, this time Bioshock protagonist has a personality, character, and voice, no longer the protagonist is a silent man, trying to survive.', 7.48, 'PC, Xbox One, PlayStation 4, Nintendo Switch, Linux, Xbox 360, PlayStation 3', 'Shooter, Action', 4.38, NULL, 'Minimum:\r\nOS: Windows Vista Service Pack 2 32-bit\r\nProcessor: Intel Core 2 DUO 2.4 GHz / AMD Athlon X2 2.7 GHz\r\nMemory: 2GB\r\nHard Disk Space: 20 GB free\r\nVideo Card: DirectX10 Compatible ATI Radeon HD 3870 / NVIDIA 8800 GT / Intel HD 3000 Integrated Graphics\r\nVideo Card Memory: 512 MB\r\nSound: DirectX Compatible', NULL),
(4101, 'Bayonetta', 'Bayonetta is a slasher game developed by Platinum Games.\r\n\r\nThe game is set in a fictional European city of Vigrid. The main character is a witch named Bayonetta who fights angels with pistols and magic wishing only one thing: to recall everything happened to her.\r\n\r\nBayonetta is a third-person game. The player controls Bayonetta and uses close to medium range attacks, complicated combos and a wide variety of weaponry.', 19.99, 'PC, PlayStation 4, Xbox One, Nintendo Switch, Xbox 360, PlayStation 3, Wii U', 'Adventure, Action', 4.30, NULL, 'Minimum:\r\nOS: Microsoft Windows 7 / 8 (8.1)/ 10\r\nProcessor: Core i3 3220\r\nMemory: 4 GB RAM\r\nGraphics: Radeon HD6950 / GeForce  GTX 570 (VRAM 768MB)\r\nDirectX: Version 9.0c\r\nStorage: 20 GB available space', ''),
(4166, 'Mass Effect', 'Mass Effect was the very start of the trilogy about Commander Shepard in his journey to save the universe from Reapers - an old civilisation that wants to kill every possible rational being in order to prevail any wars. You play as Shepard. With flexible backstory and different classes you travel to Eden Prime with Captain Anderson and Nihlus Kryik, you and your team must discover the mystery behind the attack on the human colony.', 7.48, 'PC, Xbox One, Xbox 360, PlayStation 3', 'Action, RPG', 4.39, NULL, 'Supported OS: Microsoft Windows® XP with SP2 or Windows Vista*                    \r\nProcessor: Intel P4 2.4 Ghz or faster / AMD 2.0 Ghz                    \r\nMemory: 1.0 GB RAM or more (2.0 GB for Vista)                    \r\nGraphics: DirectX 9.0c compatible, ATI X1300 XT or greater (ATI X1300, X1300 Pro, X1600 Pro, Radeon 2600  HD, and HD 2400 are below minimum system requirements); NVidia GeForce 6800 or greater (7300, 7600 GS, 8500 are below minimum system requirements)                    \r\nHard Drive: 12.0 GB or more free hard drive space                     \r\nSound: DirectX 9.0c compatible                    \r\nDirectX®: 9.0c                    * WINDOWS VISTA OR WINDOWS 7 USERS: Launching “Mass Effect” from Steam requires the setting “Run as Administrator”. If the User Account Control feature of Windows Vista is enabled, launching “Mass Effect” from Steam will result in failure. For users with User Account Control enabled, launch Steam using the “Run as Administrator” option or launch from the windows shortcut.                    \r\nINTERNET CONNECTION AND END USER LICENSE AGREEMENT REQUIRED TO PLAY.  MORE INFORMATION IS AVAILABLE AT WWW.EA.COM.', NULL),
(4186, 'Persona 4 Golden', 'Persona 4 Golden is the jRPG and the remake of the Persona 4, released four years after the original. This is the fifth part of Persona series which is at the same time a sub-series to an even bigger franchise called Shin Megami Tensei. The remake is released exclusively for PS Vita handheld. \r\n\r\n###Gameplay\r\nThere is a similar pattern to all Persona games: the gameplay is divided into the usual school life and the underworld dungeon crawler part.', 9.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, PS Vita', 'RPG', 4.44, NULL, 'Minimum:\r\nOS: Windows 8.1\r\nProcessor: Intel Core 2 Duo E8400 | AMD Phenom II X2 550\r\nMemory: 2 GB RAM\r\nGraphics: Nvidia GeForce GTS 450 | AMD Radeon HD 5770\r\nDirectX: Version 11\r\nStorage: 14 GB available space', NULL),
(4200, 'Portal 2', 'Portal 2 is a first-person puzzle game developed by Valve Corporation and released on April 19, 2011 on Steam, PS3 and Xbox 360. It was published by Valve Corporation in digital form and by Electronic Arts in physical form. \r\n\r\nIts plot directly follows the first game\'s, taking place in the Half-Life universe. You play as Chell, a test subject in a research facility formerly ran by the company Aperture Science, but taken over by an evil AI that turned upon its creators, GladOS.', 1.99, 'PC, Xbox One, macOS, Linux, Xbox 360, PlayStation 3', 'Shooter, Puzzle', 4.58, NULL, NULL, NULL),
(4234, 'Devil May Cry HD Collection', 'The popular stylish action games Devil May Cry, Devil May Cry 2, and Devil May Cry 3 Special Edition return in one collection! As Dante, the ultimate devil hunter, you\'ll join forces with appealing characters such as Trish, Lady, and Lucia and enjoy incredible action for the first time in blistering 60fps.\r\nDevil May Cry: The first appearance of Dante, the ultimate devil hunter!', 4.49, 'PC, Xbox One, PlayStation 4, Xbox 360, PlayStation 3', 'Adventure, Action', 4.22, NULL, 'Minimum:\r\nOS: WINDOWS® 7 (64bit)\r\nProcessor: Intel® Core™ i3 series (dual-core) or AMD equivalent or better\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA® GeForce® GTX 760 or AMD Radeon™ R7 260x\r\nDirectX: Version 9.0\r\nStorage: 12 GB available space\r\nSound Card: DirectSound (DirectX® 9.0c or better)\r\nAdditional Notes: *Recommended Controller Xbox 360 Controller (Windows®7/8/8.1) Xbox One Wireless Controller (Windows®10) \r\n\r\n*Internet connection required for game activation.  \r\n\r\n*Non-multi-thread supported CPUs are not guaranteed to operate correctly.', ''),
(4248, 'Dishonored', 'Dishonored is the game about stealth. Or action and killing people. It is you who will decide what to do with your enemies. You play as Corvo Attano, Empress\' bodyguard, a masterful assassin and a combat specialist. All of a sudden, a group of assassins kill the Empress and kidnaps her daughter Emily. Being accused of murder and waiting for execution in a cell, Corvo still manages to escape with the help of the Loyalists and their leader Admiral Havelock.', 2.48, 'PC, Xbox One, PlayStation 4, Xbox 360, PlayStation 3', 'Adventure, Action, RPG', 4.38, NULL, 'Minimum:\r\nOS: Windows Vista / Windows 7\r\nProcessor: 3.0 GHz dual core or better\r\nMemory: 3 GB system RAM\r\nHard Disk Space: 9 GB\r\nVideo Card: DirectX 9 compatible with 512 MB video RAM or better (NVIDIA GeForce GTX 460 / ATI Radeon HD 5850)\r\nSound: Windows compatible sound card', NULL),
(4265, 'Persona 3 Portable', 'Terrible creatures lurk in the dark, preying on those who wander into the hidden hour between one day and the next. As a member of a secret school club, you must wield your inner power—Persona—and protect humanity from impending doom. Will you live to see the light of day?\r\nHailed by critics and players alike for breathing new life into the RPG genre, Persona®3 now finds a new home on your PSP® (PlayStation®Portable) system.', 9.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, PSP', 'Strategy, Adventure, RPG', 4.49, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 or higher\r\nProcessor: Intel Core i3-540 or AMD Phenom II X4 940\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA GeForce GT 730, 1 GB or AMD Radeon R7 240, 1 GB\r\nDirectX: Version 11\r\nStorage: 10 GB available space\r\nAdditional Notes: Low 720p @ 60 FPS.', NULL),
(4439, 'Mass Effect 3', 'Mass Effect 3 is the final part of the trilogy of the same name, created by BioWare. It is an action RPG with wide customization opportunities and several endings that depend on your choices during the game. There are side quests you can complete, and a relationship system that opens new ways to fulfill tasks and lets to romance some characters. The game follows traditions of the cosmic opera genre and features interstellar travel, space fights, and interaction with various alien races.', 7.48, 'PC, Xbox One, Xbox 360, PlayStation 3, Wii U', 'Action, RPG', 4.40, NULL, NULL, NULL),
(4527, 'Call of Duty: Modern Warfare 2', 'Continuation of the sensational first-person shooter from Infinity Ward and Activision. It is rather difficult to maintain a high level of games every year, but it worked out in the sixth part of the series. The game continues the storyline of the previous part.\r\n\r\nThe game consists of numerous fast-paced gunfights. Classic hit points are not here anymore - after receiving damage, the player only needs to sit tight in safety waiting for health to come back.', 14.99, 'PC, Xbox One, macOS, Xbox 360, PlayStation 3', 'Shooter', 4.27, NULL, 'OS: Microsoft Windows XP or Windows Vista (Windows 95/98/ME/2000 are unsupported)\r\nProcessor: Intel Pentium 4 3.2 GHz or AMD Athlon 64 3200+ processor or better supported\r\nMemory: 1 GB RAM\r\nGraphics: 256 MB NVIDIA GeForce 6600GT or better or ATI Radeon 1600XT or better\r\nDirectX®: Microsoft DirectX(R) 9.0c\r\nHard Drive: 12GB of free hard drive space\r\nSound: 100% DirectX 9.0c compatible sound card\r\nInternet: Broadband connection and service required for Multiplayer Connectivity. Internet Connection required for activation.\r\nCo-op/Multiplayer Hosting: To host Co-op or MP matches, a 2Ghz dual-core or better processor is recommended.\r\n', ''),
(4535, 'Call of Duty 4: Modern Warfare', 'The fourth installment of a popular series, Call of Duty 4: Modern Warfare is split into two different, gameplay-wise, parts. The single-player campaign invites players to go through the episodic story, where players control six different characters. And even though the stories are taking place in different locations, the events of the campaign are happening simultaneously, creating the sense of urgency and painting a large-scale picture of the events.', 9.99, 'PC, Xbox One, PlayStation 4, Nintendo DS, macOS, Xbox 360, PlayStation 3, Wii', 'Shooter, Action', 4.39, NULL, 'Minimum: \r\nSupported OS: Microsoft® Windows® XP/Vista (Windows 95/98/ME/2000 are unsupported)\r\nDirectX Version: Microsoft DirectX 9.0c (included)\r\nProcessor: Intel® Pentium® 4 2.4 Ghz / AMD(R) 64 (TM)  2800+ / Intel® and AMD® 1.8 Ghz Dual Core Processor or better supported\r\nMemory: 512MB RAM (Windows® XP), 768MB RAM (Vista®)\r\nGraphics: NVIDIA Geforce 6600 or better or ATI Radeon® 9800Pro or better\r\nSound: 100% DirectX 9.0c compatible sound card\r\nHard Drive: 8GB of free hard drive space\r\nInternet: Broadband connection and service required for multiplayer connectivity', NULL),
(4544, 'Red Dead Redemption', 'Red Dead Redemption is a third-person open-world adventure game which implements the Wild West at its best: it is very much GTA-clone but in bizarre stylistics and the very beginning of the twentieth century. This is the second title of a franchise, being preceded by Red Dead Revolver and followed by Red Dead Redemption 2 coming out in late 2018. \r\nWe play as John Marston who gradually takes down and take out criminals and those, who crosses his path.', 24.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, Xbox 360, PlayStation 3', 'Shooter, Action', 4.42, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 64-Bit\r\nProcessor: Intel® Core™ i5-4670 / AMD FX-9590\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA GeForce GTX 960 / AMD Radeon R7 360\r\nDirectX: Version 12\r\nStorage: 12 GB available space\r\nSound Card: Direct X Compatible', NULL),
(4550, 'Dead Space 2', 'Dead Space 2 is a third-person action shooter including both survival and horror elements. The game is a direct sequel to the first chapter of the Dead Space franchise. The game is set in the space environment of the future. The story of the second part begins in 2511, 3 years after the events held in the first game. The main character Isaac Clarke is awakened after 3-year coma (after the escape from Aegis VII) in an insane asylum in the Sprawl on Titan.', 3.99, 'PC, Xbox One, Xbox 360, PlayStation 3', 'Shooter, Action', 4.38, NULL, 'Processor: 2.8 GHz processor or equivalent\r\nMemory: 1 GB RAM (XP), 2 GB RAM (Vista or Windows 7)\r\nHard Disk Space: At least 10GB of hard drive space for installation, plus additional space for saved games\r\nVideo Card: NVIDIA GeForce 6800 or better (7300, 7600 GS, and 8500 are below minimum system requirements), ATI X1600 Pro or better (X1300, X1300 Pro and HD2400 are below minimum system requirements), 256MB Video Card and Shader Model 3.0 required\r\nDirectX®: DirectX 9.0c', NULL),
(4570, 'Dead Space (2008)', 'Dead Space is a third-person shooter with horror elements. Playing as Isaac Clarke, the systems engineer, players will be isolated on the spaceship USG Ishimura after the crew was slaughtered by mindless Necromorphs after the failed investigation of the distress signal. Now Isaac not only has to escape but uncover the dark secrets of Ishimura, while looking for the clues about the whereabouts of his girlfriend Nicole.', 7.99, 'PC, Xbox One, Xbox 360, PlayStation 3', 'Shooter, Action', 4.39, NULL, 'Supported OS: Microsoft Windows® XP SP2 or Vista                    \r\nProcessor: 2.8 GHz or faster                    \r\nMemory: 1 GB RAM or more for Windows XP (2 GB for Vista)                    \r\nGraphics: DirectX® 9.0c compatible video card, Shader Model 3.0 required, 256 MB or better, NVIDIA GeForce 6800 or better (7300, 7600 GS, and 8500 are below minimum system requirements), ATI X1600 Pro or better (X1300, X1300 Pro and HD2400 are below minimum system requirements)                     \r\nHard Drive: 7.5 GB free space                    \r\nSound: DirectX® 9.0c compatible sound card                     INTERNET CONNECTION, ONLINE AUTHENTICATION AND ACCEPTANCE OF END USER LICENSE AGREEMENT REQUIRED TO PLAY. EA MAY RETIRE ONLINE FEATURES AFTER 30 DAYS NOTICE POSTED ON WWW.EA.COM.', NULL),
(5115, 'FINAL FANTASY VIII', 'Final Fantasy VIII is a Japanese role-playing game, the eight main installments in Final Fantasy series.  \r\n\r\n###Plot and setting \r\nLike most other Final Fantasy entries, the game takes place in an entirely new world that is not connected with previous games in the series. It can be described as the mix of modern Europe and futuristic setting with high fantasy influences.', 11.99, 'PC, PlayStation 4, PlayStation', 'RPG', 4.28, NULL, 'Minimum:\r\nOS: Microsoft Windows XP/Vista/7/8 (32/64-bit)\r\nProcessor: 1Core CPU 2GHz or faster\r\nMemory: 1 GB RAM\r\nGraphics: DirectX 9.0c compatible card\r\nDirectX: Version 9.0c\r\nStorage: 4 GB available space\r\nSound Card: Integrated sound chip or more', ''),
(5563, 'Fallout: New Vegas', 'Fallout: New Vegas is the second instalment after the reboot of the Fallout series and a fourth instalment in the franchise itself. Being a spin-off and developed by a different studio, Obsidian Entertainment, Fallout: New Vegas follows the Courier as he\'s ambushed by a gang lead by Benny, stealing a Platinum Chip and heavily wounded, practically left for dead. As he wakes up, he minds himself in the company of Doc Mitchell who saved our protagonist and patches him up.', 0.99, 'PC, Xbox One, PlayStation 4, Xbox 360, PlayStation 3', 'Shooter, Action, RPG', 4.43, NULL, 'OS: Windows 7/Vista/XP \r\nProcessor: Dual Core 2.0GHz\r\nMemory: 2GB RAM\r\nHard Disk Space: 10GB free space\r\nVideo Card: NVIDIA GeForce 6 series, ATI 1300XT series\r\n', NULL),
(5679, 'The Elder Scrolls V: Skyrim', 'The fifth game in the series, Skyrim takes us on a journey through the coldest region of Cyrodiil. Once again player can traverse the open world RPG armed with various medieval weapons and magic, to become a hero of Nordic legends –Dovahkiin, the Dragonborn. After mandatory character creation players will have to escape not only imprisonment but a fire-breathing dragon. Something Skyrim hasn’t seen in centuries.', 19.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, Xbox 360, PlayStation 3', 'Action, RPG', 4.42, NULL, 'Minimum:\r\nOS: Windows 7/Vista/XP PC (32 or 64 bit)\r\nProcessor: Dual Core 2.0GHz or equivalent processor\r\nMemory: 2GB System RAM\r\nHard Disk Space: 6GB free HDD Space\r\nVideo Card: Direct X 9.0c compliant video card with 512 MB of RAM\r\nSound: DirectX compatible sound card\r\n', NULL),
(5916, 'The Room Two', 'Following the release of The Room in 2014, Fireproof Games are proud to bring the critically acclaimed sequel to PC.\r\nThe Room Two continues the time-spanning journey of its predecessor while significantly expanding its unique puzzle gameplay. Follow a trail of cryptic letters from an enigmatic scientist known only as “AS” into a compelling world of challenging mystery and tactile exploration.', 4.99, 'PC, iOS, Android', 'Indie, Puzzle', 4.30, NULL, 'Minimum:\r\nOS: Windows 7 or higher\r\nProcessor: 2.0 GHz Dual Core Processor\r\nMemory: 2 GB RAM\r\nGraphics: Video card with 512MB of VRAM\r\nDirectX: Version 9.0\r\nStorage: 2 GB available space', ''),
(9687, 'CrossCode', 'This retro-inspired 2D Action RPG might outright surprise you. CrossCode combines 16-bit SNES-style graphics with butter-smooth physics, a fast-paced combat system, and engaging puzzle mechanics, served with a gripping sci-fi story.\r\nCrossCode is all about how it plays! That\'s why there is a free Steam demo! Go give it a try!\r\nTake the best out of two popular genres, find a good balance between them and make a great game. That’s what CrossCode does. And it works pretty well.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS, Linux', 'Action, RPG, Puzzle', 4.27, NULL, 'Minimum:\r\nOS: Windows XP\r\nProcessor: 2 GHz dual core\r\nMemory: 2 GB RAM\r\nGraphics: Hardware Accelerated Graphics with dedicated memory, 1GB memory recommended', ''),
(9767, 'Hollow Knight', 'Hollow Knight is a Metroidvania-type game developed by an indie studio named Team Cherry.\r\n\r\nMost of the game\'s story is told through the in-world items, tablets, and thoughts of other characters. Many plot aspects are told to the player indirectly or through the secret areas that provide a bit of lore in addition to an upgrade. At the beginning of the game, the player visits a town of Dirtmouth. A town built above the ruins of Hallownest.', 7.48, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS, Linux', 'Platformer, Indie, Action', 4.40, NULL, 'Minimum:\r\nOS: Windows 7\r\nProcessor: Intel Core 2 Duo E5200\r\nMemory: 4 GB RAM\r\nGraphics: GeForce 9800GTX+ (1GB)\r\nDirectX: Version 10\r\nStorage: 9 GB available space\r\nAdditional Notes: 1080p, 16:9 recommended', NULL),
(9981, 'Total War: WARHAMMER II', 'Millennia ago, besieged by a Chaos invasion, a conclave of High Elf mages forged a vast, arcane vortex. Its purpose was to draw the Winds of Magic from the world as a sinkhole drains an ocean, and blast the Daemonic hordes back to the Realm of Chaos. Now the Great Vortex falters, and the world again stands at the brink of ruin.\r\nPowerful forces move to heal the maelstrom and avert catastrophe. Yet others seek to harness its terrible energies for their own bitter purpose.', 59.99, 'PC, macOS, Linux', 'Strategy, Action', 4.34, NULL, 'Minimum:\r\nOS: Windows 7 64Bit\r\nProcessor: Intel® Core™ 2 Duo 3.0Ghz\r\nMemory: 5 GB RAM\r\nGraphics: NVIDIA GTX 460 1GB | AMD Radeon HD 5770 1GB | Intel HD4000 @720p\r\nStorage: 60 GB available space', ''),
(10037, 'Europa Universalis IV', 'Europa Universalis IV is a global strategy game developed by Paradox Interactive. It is the fifth game in the series, not counting the add-ons.\r\n\r\n###Gameplay\r\nThe game can take place at any time starting from the late Middle Age to the Napolean Wars era. The player can control any country there is in the game. It is possible to choose any starting date from November 11th, 1444 to January 1st, 1821.\r\n\r\nThe player controls every aspect of life in his country.', 49.99, 'PC, macOS, Linux', 'Strategy', 4.23, NULL, 'Minimum:\r\nOS:Windows 7/Windows 8\r\nProcessor:Intel® Pentium® IV 2.4 GHz eller AMD 3500+\r\nMemory:2 GB RAM\r\nGraphics:NVIDIA® GeForce 8800 or ATI Radeon® X1900, 512mb video memory required\r\nDirectX®:9.0c\r\nHard Drive:2 GB HD space\r\nSound:Direct X- compatible soundcard \r\nOther Requirements:Broadband Internet connection\r\nAdditional:Controller support: 3-button mouse, keyboard and speakers. Internet Connection or LAN for multiplayer', ''),
(10064, 'Assassin’s Creed Brotherhood', 'Digital Deluxe Edition2 Exclusive Single-Player Maps: The Trajan Market & The Aqueduct\r\n2 Multiplayer Characters:The Officer - Death\'s Finest Ally: Few have seen him. Even fewer have lived to tell about it. Fear is his friend, and Death is his ally.\r\nThe Harlequin - The Walking Nightmare: Behind the gaudy costume and twisted smile lies the soul of a merciless Assassin.img.', 19.99, 'PC, Xbox One, PlayStation 4, macOS, Xbox 360, PlayStation 3', 'Action', 4.28, NULL, 'Minimum\r\nOS: Windows® XP (32-64 bits) /Windows Vista®(32-64 bits)/Windows 7® (32-64 bits) \r\nProcessor: Intel Core® 2 Duo 1.8 GHZ or AMD Athlon X2 64 2.4GHZ\r\nMemory: 1.5 GB Windows® XP / 2 GB Windows Vista® - Windows 7®\r\nGraphics: 256 MB DirectX® 9.0–compliant card with Shader Model 3.0 or higher (see supported list*)\r\nDirectX®: 9.0\r\nHard Drive: 8 GB\r\nSound: DirectX 9.0 –compliant sound card\r\nPeripherals: Keyboard, mouse, optional controller\r\nSupported Video Cards: ATI® RADEON®  HD 2000/3000/4000/5000/6000 series, NVIDIA GeForce® 8/9/100/200/300/400/500 series\r\nNote* * This product does not support Windows® 98/ME/2000/NT\r\nRequires a Uplay account.\r\n', ''),
(10069, 'Mount & Blade II: Bannerlord', 'The horns sound, the ravens gather. An empire is torn by civil war. Beyond its borders, new kingdoms rise. Gird on your sword, don your armour, summon your followers and ride forth to win glory on the battlefields of Calradia. Establish your hegemony and create a new world out of the ashes of the old.\r\nMount & Blade II: Bannerlord is the eagerly awaited sequel to the acclaimed medieval combat simulator and role-playing game Mount & Blade: Warband.', 49.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X', 'Strategy, Action, RPG, Simulation', 4.22, NULL, 'Minimum:\r\nProcessor: Intel i3-2100 / AMD FX-6300\r\nMemory: 4 GB RAM\r\nGraphics: Intel HD 4600 / Nvidia GT730 / AMD R7 240\r\nStorage: 40 GB available space\r\nAdditional Notes: These estimates may change during final release', '');
INSERT INTO `Games` (`id`, `name`, `description`, `price`, `platform`, `genres`, `rating`, `seller_id`, `min_requirements`, `recommended_requirements`) VALUES
(10073, 'Divinity: Original Sin 2', 'The Divine is dead. The Void approaches. And the powers latent within you are soon to awaken. The battle for Divinity has begun. Choose wisely and trust sparingly; darkness lurks within every heart.\r\n\r\nWho will you be? A flesh-eating elf; an imperial lizard; an undead risen from the grave? Choose your race and origin story - or create your own! Discover how the world reacts differently to who - and what - you are.It’s time for a new Divinity!', 11.24, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Strategy, RPG', 4.38, NULL, 'Minimum:\r\nOS: Windows 7 SP1 64-bit or Windows 8.1 64-bit or Windows 10 64-bit\r\nProcessor: Intel Core i5 or equivalent\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA® GeForce® GTX 550 or ATI™ Radeon™ HD 6XXX or higher\r\nDirectX: Version 11\r\nStorage: 25 GB available space\r\nAdditional Notes: Minimum requirements may change during development.', NULL),
(10141, 'NieR:Automata', 'NieR: Automata is an action RPG, a sequel to Nier and a spin-off to the Drakenguard series. The story is set in the middle of the war between humans and machines where you take on the role of an android warrior called 2B. The story develops around the theme of androids\' ability to feel and make their own decisions.', 15.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Action, RPG', 4.37, NULL, 'Minimum:\r\nOS: Windows 7 /8.1 /10 64bit\r\nProcessor: Intel Core i3 2100 or AMD A8-6500\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA GeForce GTX 770 VRAM 2GB or AMD Radeon R9 270X VRAM 2GB\r\nDirectX: Version 11\r\nNetwork: Broadband Internet connection\r\nStorage: 50 GB available space\r\nSound Card: DirectX® 11 supported\r\nAdditional Notes: Mouse, keyboard and game pad (XInput only). Screen resolution: 1280x720. This product only supports MS-IME keyboard input. There is a possibility that other IME will not function correctly with it.', NULL),
(10389, 'Gothic II: Gold Edition', 'The second game from the series Gothic.\r\nIn the first game, people who inhabit a fantasy kingdom, lose the war to the orcs. To win, the king needs magical ore mined in local mines. The king decides to send to the mines everyone who is accused of any crime and create a magical Barrier around the mines so that no one can escape from the mines. However, Barrier surrounded a much larger area and the prisoners became masters inside the Barrier.', 4.99, 'PC', 'Action, RPG', 4.40, NULL, 'OS: Windows XP/2000/ME/98/Vista\r\nProcessor: Intel Pentium III 700 MHz\r\nMemory: 256 MB Ram or higher\r\nGraphics: 3D graphics card with 32 MB Ram\r\nDirectX®: 8.1\r\nHard Drive: 4 GB\r\nSound: DirectX compatible\r\n', NULL),
(10579, 'Planescape: Torment: Enhanced Edition', 'The original Planescape: Torment was released in 1999 to widespread critical acclaim. It won RPG of the Year from multiple outlets for its unconventional story, characters, and amazing soundtrack. Since then, millions of Planescape: Torment fans have enjoyed exploring the strange and dangerous city of Sigil and surrounding planes through the Nameless One\'s eyes.\r\nDiscover an incredibly rich story and a unique setting unlike anything else in fantasy.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, Android, macOS, Linux', 'Strategy, Adventure, RPG', 4.37, NULL, 'Minimum:\r\nOS: Windows XP, Vista, 7, 8, or 10\r\nProcessor: 1 GHZ\r\nMemory: 512 MB RAM\r\nGraphics: OpenGL 2.0 compatible\r\nStorage: 2 GB available space', ''),
(10926, 'Factorio', 'Factorio is an isometric space real-time strategy developed by Wube Software. \r\n\r\n###Story\r\nAn astronaut is stranded on the surface of a distant unknown planet, that is full with low life forms and fossil fuels. His main objective now is to survive and build a rocket to leave the planet. Luckily, the main character has a scientific background, hence why he can build any machinery from steam engines to exoskeletons, power grids, and oil plants.', 35.00, 'PC, Nintendo Switch, macOS, Linux', 'Casual, Strategy, Indie, Simulation', 4.36, NULL, 'Minimum:\r\nOS: Windows 10, 8, 7, Vista (64 Bit)\r\nProcessor: Dual core 3Ghz+\r\nMemory: 4 GB RAM\r\nGraphics: 512MB Video Memory\r\nStorage: 1 GB available space\r\nAdditional Notes: Low sprite resolution and Low VRAM usage.', ''),
(10992, 'OneShot', 'Localization Incoming!Great news! OneShot is about to receive a localization into several new languages. We look forward to sharing the additional languages with players new and old!\r\nsteamcommunity.com/games/420530/announcements/detail/1255787772106545794\r\nAbout the Gamewww.oneshot-game.com\r\nA surreal puzzle adventure game with unique mechanics / capabilities.\r\nYou are to guide a child through a mysterious world on a mission to restore its long-dead sun.\r\n...Of course, things are never that simple.', 9.99, 'PC, macOS', 'Casual, Indie, Adventure', 4.24, NULL, 'Minimum:\r\nOS: Windows Vista or later\r\nMemory: 4 GB RAM\r\nGraphics: OpenGL 2.1 compatible', ''),
(11498, 'The Legend of Heroes: Trails in the Sky SC', 'The coup d’état that threatened to shake the foundation of the Liberl Kingdom has now come to a close and Her Majesty the Queen’s birthday celebrations are in full swing throughout the streets of Grancel. During that same night, a boy who vowed to make amends for his past disappeared before the girl he loved. Clutched in the girl’s hand was the one thing he left for her to remember him by: a harmonica.', 19.48, 'PC, PSP', 'RPG', 4.44, NULL, 'Minimum:\r\nOS: Windows XP\r\nProcessor: Pentium III 550 MHz\r\nMemory: 512 MB RAM\r\nGraphics: 32 MB VRAM, 3D accelerator compatible w/ DirectX 9.0c\r\nDirectX: Version 9.0c\r\nStorage: 4 GB available space\r\nSound Card: Compatible with DirectX 9.0c', NULL),
(11726, 'Dead Cells', 'Dead Cells is a roguelike adventure title developed by Motion Twin.\r\n\r\n###Story\r\nNot much story is present in the game, as only bits of any information are given out to the players. The game takes place at a remote island, where the players take place of the Prisoner. The Prisoner does not speak, yet he can express confusion and frustration using his body language.', 24.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, macOS, Linux', 'Platformer, Indie, Action, RPG', 4.23, NULL, '7 / 8 / 10Processor: Intel i5+Memory: 4 GB RAMGraphics: Nvidia GTX 460 / Radeon HD 7800 or betterStorage: 500 MB available spaceMouse, KeyboardAdditional Notes: OpenGL 3.2+', ''),
(11868, 'Nancy Drew: Last Train to Blue Moon Canyon', 'Nancy Drew®: Last Train to Blue Moon Canyon is a first-person perspective, point-and-click adventure game.  The player is Nancy Drew and has to solve a mystery. Explore rich environments for clues, interrogate suspects, and solve puzzles and mini-games.\r\nThe Hardy Boys have invited you, as Nancy Drew, on a train ride out West hosted by beautiful and prominent socialite, Lori Girard.', 9.99, 'PC', 'Adventure', 4.23, NULL, 'OS: Windows® 98/ME/2000/XP/Vista\r\nProcessor: 1GHz CPU\r\nMemory: 128MB RAM\r\nGraphics: 32-bit DirectX 8.0 compatible video card\r\nDirectX®: 8.0 or higher\r\nHard Drive: 650MB of free space\r\nSound: 16-bit DirectX compatible sound card\r\n', ''),
(11874, 'Nancy Drew: Legend of the Crystal Skull', 'Nancy Drew®: Legend of the Crystal Skull is a first-person perspective, point-and-click adventure game.  The player is Nancy Drew and has to solve a mystery. Explore rich environments for clues, interrogate suspects, and solve puzzles and mini-games.\r\nBruno Bolet was the proud owner of the Whisperer, a crystal skull rumored to protect its holder from almost any cause of death  except murder.', 9.99, 'PC', 'Adventure', 4.26, NULL, 'OS: Windows® XP/Vista\r\n                        Processor: 1 GHz or greater Pentium or equivalent class CPU\r\n                        Memory: 128 MB of RAM\r\n                        Graphics: 32 MB DirectX 9.0 compatible video card\r\n                        DirectX: DirectX 9.0\r\n                        Hard Drive: 1 GB or more of hard drive space\r\n                        Sound: 16 bit DirectX compatible sound card\r\n                        ', ''),
(11970, 'Star Wars Jedi Knight: Jedi Academy', 'New students are arriving at Jedi Academy on distant Yavin IV but unknown enemy shots down their shuttle. As soon-to-be Jedi approach the Academy on foot they stumble upon two stormtroopers and Dark Jedi that seems to be the reason for the calamity.\r\nThe game is the sequel to Jedi Outcast and a part of the series of Jedi Knight RPGs from Raven Software. It continues the tradition of sparkling third-person action gameplay adding new stuff to destroy, new missions to complete and vehicles to drive.', 9.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS, Xbox 360, Xbox', 'Shooter, Action', 4.34, NULL, 'OS: Windows 2000, XP or Vista\r\nProcessor: Pentium II or Athlon 450 MHz\r\nMemory: 128 MB\r\nGraphics: 32 MB OpenGL compatible\r\nDirectX®: 9.0a\r\nHard Drive: 1.3 GB\r\nSound: 16 bit Direct x 9.0a\r\n       Multiplayer Requirements: Pentium II or Athlon 450MHz\r\n', ''),
(11971, 'Space Rangers HD: A War Apart', '###The reimagined classic\r\nA remastered version of the classic space strategy in real time from three studios: SNK Games, Katauri Interactive and original developers from Elemental Games. The first game of the series was released in 2002 but almost did not find fans outside of its region of release. The gameplay is often compared to games like Elite and Star Control 2.', 2.24, 'PC', 'Strategy, Adventure, RPG, Simulation', 4.37, NULL, 'Minimum:\r\nOS:Windows XP SP3\r\nProcessor:Intel Pentium 4 2.5 GHz / AMD Athlon XP 3200+ (2200 MHz)\r\nMemory:1 GB RAM\r\nGraphics:GeForce 7800 GT 512 Mb / Radeon 1800 Pro 512 Mb or similar\r\nDirectX®:9.0c\r\nHard Drive:2 GB HD space', NULL),
(12074, 'Monkey Island 2 Special Edition: LeChuck’s Revenge', '###Golden Age\r\nA game from the golden age of the studio and publisher LucasArts Entertainment and from the legendary team led by Ron Gilbert and Tim Shafer. The game was the sixth to use the graphics engine SCUMM: it was modified and improved audio capabilities. This allowed smoothly change the music themes of locations and creating a more immersive experience for players. The game was reissued in July 2010.', 9.99, 'PC, Xbox 360, PlayStation 3', 'Adventure, Action', 4.25, NULL, 'OS: Windows XP® or Windows Vista®\r\nProcessor: Intel Pentium 4 3GHz or AMD Athlon 64 3000+\r\nMemory: 256 MB RAM, 512 MB for Vista\r\nHard Disk Space: 1.8GB free hard drive space\r\nGraphics: 128 MB with Shader Model 2.0 capability\r\nSound Card: DirectX® 9.0c compliant sound card\r\nController Support: Xbox 360 controller', ''),
(12130, 'RimWorld', 'RimWorld is a sci-fi colony sim driven by an intelligent AI storyteller. Inspired by Dwarf Fortress, Firefly, and Dune.\r\nYou begin with three survivors of a shipwreck on a distant world.\r\nManage colonists\' moods, needs, wounds, and illnesses.\r\nFashion structures, weapons, and apparel from metal, wood, stone, cloth, or futuristic materials.\r\nTame and train cute pets, productive farm animals, and deadly attack beasts.', 34.99, 'PC, macOS, Linux', 'Strategy, Indie, Simulation', 4.36, NULL, 'Minimum:\r\nOS: Windows XP\r\nProcessor: Core 2 Duo\r\nMemory: 4 GB RAM\r\nGraphics: Intel HD Graphics 3000 with 384 MB of RAM\r\nStorage: 500 MB available space', ''),
(12447, 'The Elder Scrolls V: Skyrim Special Edition', 'The Elder Scrolls V: Skyrim Special Edition is the 2016 reinstallment of the open world fantasy RPG, developed by Bethesda Game Studios. Following the original release of 2011, Special Edition focuses on reshaping every sword and ax, polishing every stone in the high castles and the suburbs of the low, overall bringing a renewed experience to its fans and newcomer players.', 9.99, 'PC, Xbox One, PlayStation 4', 'Action, RPG', 4.45, NULL, 'Minimum:\r\nOS: Windows 7/8.1/10 (64-bit Version)\r\nProcessor: Intel i5-750/AMD Phenom II X4-945\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA GTX 470 1GB /AMD HD 7870 2GB\r\nStorage: 12 GB available space', NULL),
(12965, 'LISA', 'A game about survival, sacrifice, and perverts...\r\nLisa is a quirky side-scrolling RPG set in a post-apocalyptic wasteland. Beneath the charming and funny exterior is a world full of disgust and moral destruction. Players will learn what kind of person they are by being FORCED to make choices. These choices permanently effect the game play. If you want to save a party member from death, you will have to sacrifice the strength of your character.', 19.99, 'PC, macOS, Linux', 'Indie, Adventure, RPG', 4.26, NULL, 'Minimum:\r\nOS: Microsoft® Windows® XP / Vista / 7 (32-bit/64-bit)\r\nProcessor: Intel® Pentium® 4 2.0 GHz equivalent or faster processor\r\nMemory: 512 MB RAM\r\nGraphics: 1024 x 768 pixels or higher desktop resolution\r\nStorage: 800 MB available space', ''),
(13194, 'To the Moon', 'Eva Rosalyn and Neil Watts give people a chance to live their lives \"one more time\" - they are operators of the machine capable of penetrating into the human consciousness and rewriting memory, fulfilling unrealised dreams in minds.\r\nHowever, they do this only for those who are close to death, because such manipulation irreversibly cripples the body.\r\nTheir dying client now is old Johnny.', 9.99, 'PC, Nintendo Switch, iOS, Android, macOS, Linux', 'Adventure, Action, RPG, Casual, Indie', 4.29, NULL, 'Minimum:\r\nOS: Windows 10\r\nProcessor: Intel Core i5-12400F 2.5GHz\r\nMemory: 100 MB RAM\r\nGraphics: GTX 1060 3GB\r\nDirectX: Version 5.2\r\nStorage: 50 MB available space\r\nSound Card: none\r\nVR Support: none\r\nAdditional Notes: none', ''),
(13404, 'VA-11 Hall-A: Cyberpunk Bartender Action', 'VA-11 HALL-A: Cyberpunk Bartender Action is a booze em\' up about waifus, technology, and post-dystopia life.\r\nIn this world, corporations reign supreme, all human life is infected with nanomachines designed to oppress them, and the terrifying White Knights ensure that everyone obeys the laws.\r\nBut, this is not about those people.\r\nYou are a bartender at VA-11 HALL-A, affectionately nicknamed \"Valhalla.', 14.99, 'PC, PlayStation 4, Nintendo Switch, macOS, Linux, PS Vita', 'Adventure, Simulation', 4.37, NULL, 'Minimum:\r\nOS: Windows 7/8/10\r\nProcessor: 1.6 Ghz\r\nMemory: 1 GB RAM\r\nGraphics: 256mb\r\nStorage: 250 MB available space', ''),
(13536, 'Portal', 'Every single time you click your mouse while holding a gun, you expect bullets to fly and enemies to fall. But here you will try out the FPS game filled with environmental puzzles and engaging story. \r\nSilent template for your adventures, Chell, wakes up in a testing facility. She’s a subject of experiments on instant travel device, supervised by snarky and hostile GLaDOS.\r\nPlayers will have to complete the tests, room by room, expecting either reward, freedom or more tests.', 1.99, 'PC, Nintendo Switch, Android, macOS, Linux, Xbox 360, PlayStation 3', 'Action, Puzzle', 4.49, NULL, 'Minimum: 1.7 GHz Processor, 512MB RAM, DirectX&reg; 8.1 level Graphics Card (Requires support for SSE), Windows&reg; 7 (32/64-bit)/Vista/XP, Mouse, Keyboard, Internet Connection\r\nRecommended: Pentium 4 processor (3.0GHz, or better), 1GB RAM, DirectX&reg; 9 level Graphics Card, Windows&reg; 7 (32/64-bit)/Vista/XP, Mouse, Keyboard, Internet Connection', NULL),
(13537, 'Half-Life 2', 'Gordon Freeman became the most popular nameless and voiceless protagonist in gaming history. He is painted as the most famous scientist and a hero within the world of Half-Life, and for a good reason. In the first game he saved the planet from alien invasion, this time, when the invasion is already begun, the world needs his help one more time. And you, as a player, will help this world to survive.', 1.99, 'PC, Android, macOS, Linux, Xbox 360, Xbox', 'Shooter, Action', 4.48, NULL, 'Minimum:\r\n\r\nOS: Windows 7, Vista, XP\r\n\r\nProcessor: 1.7 Ghz\r\n\r\nMemory: 512 MB RAM\r\n\r\nGraphics: DirectX 8.1 level Graphics Card (requires support for SSE)\r\n\r\nStorage: 6500 MB available space', NULL),
(13566, 'Into the Breach', 'The remnants of human civilization are threatened by gigantic creatures breeding beneath the earth. You must control powerful mechs from the future to hold off this alien threat. Each attempt to save the world presents a new randomly generated challenge in this turn-based strategy game from the makers of FTL.\r\n\r\n### Key Features:\r\nDefend the Cities: Civilian buildings power your mechs. Defend them from the Vek and watch your fire!', 14.99, 'PC, Nintendo Switch, Android, macOS', 'Strategy, Indie, RPG', 4.30, NULL, '7 / 8 / 10Processor: 1.7+ GHz or betterMemory: 1 GB RAMGraphics: Intel HD 3000 or betterStorage: 300 MB available spaceMouse, Keyboard', ''),
(13627, 'Undertale', 'Undertale is an independent role-playing game developed by Toby Fox.\r\n\r\nOnce upon a time, there were two races on Earth: monsters and humans, but a war broke out between them and the latter won. Seven greatest mages sealed the monsters underground and left one entrance through a hole in the Ebott mountain. A lot of time passed since the war, but a human child accidentally falls down the mountain. Its goal is to get back out.', 9.99, 'PC, PlayStation 4, Xbox One, Xbox Series S/X, Nintendo Switch, macOS, Linux, PS Vita', 'Indie, RPG', 4.35, NULL, 'Minimum:\r\nOS: Windows XP, Vista, 7, 8, or 10\r\nMemory: 2 GB RAM\r\nGraphics: 128MB\r\nStorage: 200 MB available space', ''),
(13820, 'The Elder Scrolls III: Morrowind', 'The Elder Scrolls III: Morrowind® Game of the Year Edition includes Morrowind plus all of the content from the Bloodmoon and Tribunal expansions. The original Mod Construction Set is not included in this package.\r\n\r\nAn epic, open-ended single-player RPG, Morrowind allows you to create and play any kind of character imaginable.', 5.99, 'PC, Xbox 360, Xbox', 'RPG', 4.37, NULL, 'OS: Windows ME/98/XP/2000\r\nProcessor: 500 MHz Intel Pentium III, Celeron, or AMD Athlon\r\nMemory: 256 MB\r\nGraphics: 32MB Direct3D Compatible video card with 32-bit color support and DirectX 8.1 \r\nDirectX®: 8.1\r\nHard Drive: 1GB free hard disk space\r\nSound: DirectX 8.1 compatible sound card\r\n', NULL),
(13833, 'Stronghold Crusader HD', 'The game is an addition to the game-strategy Stronghold.\r\n\r\n###The main thing about the series of games Stronghold\r\n\r\nThe game Stronghold - a simulator of three activities:\r\nthe construction of a medieval castle,\r\nmilitary actions - assault of the castle or its defense,\r\ncreation and management of the village.\r\n\r\n###The atmosphere and situation of the first game\r\n\r\nBriefly, this is the stylization of the Middle Ages.', 9.99, 'PC, macOS', 'Strategy, Simulation', 4.24, NULL, 'Minimum:\r\nOS: Windows XP/Vista/7/8/10 with latest service packs\r\nProcessor: Intel Pentium 4 1.6Ghz or equivalent\r\nMemory: 512 MB RAM\r\nGraphics: 64MB DirectX 8.1 Compatible\r\nDirectX: Version 8.1\r\nStorage: 850 MB available space\r\nSound Card: DirectX 8.1 Compatible\r\nAdditional Notes: GameRanger software will be installed for mutiplayer matchmaking support', ''),
(13856, 'Katana ZERO', 'Katana ZERO is a fast paced neo-noir action platformer, focusing on tight, instant-death acrobatic combat, and a dark 80\'s neon aesthetic. Aided with your trusty katana, the time manipulation drug Chronos and the rest of your assassin\'s arsenal, fight your way through a fractured city, and take back what\'s rightfully yours.\r\nRun, sneak, walljump, grapple hook, roll, slash bullets, toss pottery, and slow down time to complete levels.\r\nNo procedural generation. No backtracking.', 8.99, 'PC, Xbox One, Nintendo Switch, macOS', 'Platformer, Indie, Action', 4.39, NULL, 'Minimum:\r\nOS: Windows 7 and above\r\nProcessor: 1.2 ghz\r\nMemory: 4 GB RAM\r\nGraphics: 512 mb video memory\r\nDirectX: Version 10\r\nStorage: 200 MB available space', NULL),
(13858, 'Fran Bow', 'Fran Bow is a creepy adventure game that tells the story of Fran, a young girl struggling with a mental disorder and an unfair destiny.\r\nAfter witnessing the gruesome and mysterious loss of her parents, found dismembered at their home, Fran rushes into the woods, together with her only friend, Mr. Midnight, a black cat that Fran had previously received as a present from her parents.', 14.99, 'PC, macOS, Linux', 'Indie, Adventure', 4.29, NULL, 'Minimum:\r\nOS: 7+\r\nProcessor: 1.7 GHz Dual Core\r\nMemory: 2 GB RAM\r\nGraphics: NVIDIA GeForce GTX 260, ATI Radeon 4870 HD, or equivalent card with at least 512 MB VRAM\r\nStorage: 600 MB available space\r\nSound Card: DirectX Compatible Sound Card', ''),
(13909, 'Prince of Persia: The Sands of Time', 'Prince of Persia: The Sands of Time is an action game with platforming and puzzle solving elements. You take on the role of the Prince who gets two powerful artifacts: the Dagger of Time and the Sands of Time. Tricked by the Vizier, he releases the Sands that turn citizens into monsters. Now Prince needs to fix his mistake.\r\n\r\nThe key feature of the game is Prince’s ability to rewind time using the Dagger.', 9.99, 'PC, Xbox, PlayStation 3, PlayStation 2, GameCube', 'Adventure, Action', 4.22, NULL, '', ''),
(13925, 'Prince of Persia: Warrior Within', 'Prince of Persia: Warrior Within is an action adventure game and a part of the vast Prince of Persia series which includes ten titles overall. Sands of Time precede it and followed by The Two Thrones\r\n\r\n###Gameplay\r\nAll the big titles in the series feature pretty much the same mechanics: 3D platforming with a heavy focus on the fighting elements and parkour.', 1.99, 'PC, iOS, Xbox, PlayStation 3, PlayStation 2, GameCube', 'Adventure, Action', 4.40, NULL, 'Minimum: \r\n\r\nSupported OS: Windows&reg; 2000/XP (only)\r\nProcessor: 1 GHz Pentium&reg; III, AMD Athlon&trade;, or equivalent\r\nMemory: 256 MB (512 MB recommended)\r\nGraphics: NVIDIA GeForce 3 or higher, ATI Radeon 7500 or higher, Intel 915G (NVIDIA GeForce 4 or ATI Radeon 9500, or higher recommended)*\r\nSound: DirectX 9.0-compliant sound card\r\nDirectX: DirectX 9.0c\r\nHard Disk Space: 1.5 GB available space for minimum installation, 2.2 GB available space for full installation\r\nSupported peripherals: Windows-compatible mouse (required), Dual analog gamepad\r\n*Note: For an up-to-date list of supported chipsets, video cards, and operating systems, please visit the FAQ for this game at: http://support.ubi.com.', NULL),
(14331, 'Call of Duty 2', 'Call of Duty 2 is a first-person shooter, a second installment in the Call of Duty series.\r\n\r\n###Plot and location\r\nThe game is set during one of the most troubling periods of the 20 century: the 2nd World War. The player can take command of various soldiers from Allied armies, and take part in various campaigns in Europe and Africa.', 14.99, 'PC, Xbox One, iOS, macOS, Xbox 360', 'Shooter', 4.21, NULL, 'Minimum: 3D hardware accelerator card required - 100% DirectX 9.0c compatible 64MB hardware accelerator video card and the latest drivers*, Microsoft Windows 2000/XP, Pentium IV 1.4GHz or AMD Athlon XP 1700+ processor or higher, 256MB RAM (512MB RAM recommended), 100% DirectX 9.0c compatible 16-bit sound card and latest drivers, 100% Windows 2000/XP compatible mouse, keyboard and latest drivers, 4.0GB of uncompressed free hard disk space (plus 600MB for Windows 2000/XP swap file)\r\nMulti-player Requirements:\r\nInternet (TCP/IP) and LAN (TCP/IP) play supported\r\nInternet play requires broadband connection and latest drivers\r\nLAN play requires network interface card and latest driversImportant Notice: *Some 3D accelerator cards with the chipset listed here may not be compatible with the 3D acceleration features utilized by Call of Duty. Please refer to your hardware manufacturer for 100% DirectX 9.0c compatibility.\r\nSupported Chipsets\r\nATI Radeon 8500\r\nATI Radeon 9000\r\nATI Radeon 9200\r\nATI Radeon 9500\r\nATI Radeon 9600\r\nATI Radeon 9700\r\nATI Radeon 9800\r\nATI Radeon X300\r\nATI Radeon X550\r\nATI Radeon X600\r\nATI Radeon X700\r\nATI Radeon X800\r\nATI Radeon X850\r\nnVidia GeForce 2 Ultra\r\nAll nVidia GeForce 3/Ti Series\r\nAll nVidia GeForce 4/Ti Series\r\nAll nVidia GeForce FX Series\r\nAll nVidia GeForce 6 Series\r\nRecommend nVidia GeForce 7 Series or higher', ''),
(14491, 'Downfall', '\"Everything was perfect... until then.\"\r\nFaust, the protagonist, had a successful life due to his dealings with the devil, but now he suffers from terrible dreams each night. To find what lies at the end of those dreams—to end it all—he decides to plunge into those nightmares and face the monsters that lie within...\r\nDownfall is a roguelike hack-and-slash game where you develop your character by stylishly defeating the endless waves of enemies within your nightmare.', 8.99, 'PC', 'Casual, Indie, Adventure, Action', 4.29, NULL, 'Minimum:\r\nOS: Windows 10 64bit required\r\nProcessor: 2.6 GHz Intel® Core™ i5-750 or 3.2 GHz AMD Phenom™ II X4 955\r\nMemory: 8 GB RAM\r\nGraphics: Intel UHD Graphic 630 or newer\r\nSound Card: DirectX 11 sound device', ''),
(14927, 'Medieval II: Total War Kingdoms', 'Medieval II: Total War Kingdoms is the official expansion to last year\'s award-winning Medieval II: Total War, presenting players with all-new territories to explore, troops to command, and enemies to conquer.\r\nKingdoms is the most content-rich expansion ever produced for a Total War game, with four new entire campaigns centered on expanded maps of the British Isles, Teutonic Northern Europe, the Middle East, and the Americas.', 24.99, 'PC, macOS, Linux', 'Strategy', 4.33, NULL, 'Minimum: \r\nSupported OS: Windows 2000/XP\r\nProcessor: Celeron 1.5GHz Pentium 4® (1500MHz) or equivalent AMD® processor. (2.4 GHz P4 Recommended)\r\nRAM: 512 MB of RAM  (1 GB RAM recommended)\r\nDisk Space: 5 GB of uncompressed hard drive space \r\nGraphics Card*: 128MB Hardware Accelerated video card with Shader 1 supportand the latest drivers. Must be 100% DirectX® 9.0c compatible. (256 MB NVIDIA® GeForce™ 7300 or greater or ATI® Radeon® X1600 or greater recommended) \r\nDisplay Resolution: 1024 x 768\r\nSound Card: 100% DirectX® 9.0c compatible 16-bit sound card and latest drivers \r\nDirectX Version: DirectX® 9.0c\r\nInput Devices: 100% Windows® 2000/XP compatible mouse, keyboard and latest drivers \r\nMultiplayer: Internet (TCP / IP) play supported; Internet play requires broadband connection and latest drivers; LAN play requires Network card.\r\n*Note:Some cards may not be compatible with the 3D acceleration features utilized by Medieval II: Total War. Please refer to your hardware manufacturer for 100% DirectX® 9.0c compatibility.', ''),
(14935, 'Total War: Shogun 2 - Fall of the Samurai', 'Special EditionTotal War: SHOGUN 2 – Fall of the Samurai Steam Special Edition includes:The Steam exclusive Tsu faction pack, \"The Emperor’s Cunning\" - Rising from humble roots, the people of Tsu are wise, artful and astute strategists. Their use of Ninja is unsurpassed on the battlefield and in covert operations.   This additional in-game faction is only available in the Steam Special Edition.\r\n\r\nThe game original soundtrack - selected songs from the original game soundtrack by Jeff van Dyck.', 29.99, 'PC, macOS', 'Strategy', 4.39, NULL, 'Minimum:\r\nOS: Windows 7 / Vista / XP\r\nProcessor: 2 GHz Intel Dual Core processor / 2.6 GHz Intel Single Core processor, or AMD equivalent (with SSE2)\r\nMemory: 1GB RAM (XP), 2GB RAM (Vista / Windows7) \r\nGraphics: 256 MB DirectX 9.0c compatible card (shader model 3)  \r\nDirectX®: DirectX 9.0c\r\nHard Drive: 32GB free hard disk space\r\nScreen Resolution: 1024x768 minimum\r\n', ''),
(15002, 'The Stanley Parable', 'The Stanley Parable is a first-person interactive story game. Being initially released in 2011 as a modification for Half-Live 2, it was entirely remade in 2013, featuring updated graphics and more content.  \r\nThe game follows Stanley, an office worker whose job boils down to pressing buttons on the keyboard depending on what he sees on his office computer screen. One day the screen goes black and Stanley, not knowing what to do, decides to investigate the building.', 14.99, 'PC, macOS, Linux', 'Indie, Adventure', 4.37, NULL, 'Minimum:\r\nOS: Windows XP/Vista/7/8\r\nProcessor: 3.0 GHz P4, Dual Core 2.0 (or higher) or AMD64X2 (or higher)\r\nMemory: 2 GB RAM\r\nGraphics: Video card must be 128 MB or more and should be a DirectX 9-compatible with support for Pixel Shader 2.0b (ATI Radeon X800 or higher / NVIDIA GeForce 7600 or higher / Intel HD Graphics 2000 or higher - *NOT* an Express graphics card).\r\nStorage: 3 GB available space\r\nSound Card: DirectX 9.0c compatible', ''),
(15642, 'The Elder Scrolls IV: Oblivion Game of the Year Edition', 'Oblivion is the fourth part of the acclaimed The Elder Scrolls series. It is set in the high fantasy world of Tamriel, in the Septim Empire. The emperor was recently killed along with his sons, except one that was hidden far from the capital. Before he died, the emperor gave his Amulet of Kings to the protagonist, a prisoner whom the emperor believed to be a future hero he saw in dreams. \r\nThe main character is fully customizable.', 14.99, 'PC, Xbox 360, PlayStation 3', 'RPG', 4.32, NULL, 'OS: Windows XP, Windows 2000, Windows XP 64-Bit\r\nProcessor: 2 Ghz Intel Pentium 4 or equivalent\r\nMemory: 512 MB\r\nGraphics: 128 MB Direct3D compatible video card and DirectX 9.0 compatible driver\r\nDirectX®: DirectX 9.0c\r\nHard Drive: 4.6 GB \r\nSound: DirectX 8.1 compatible \r\n', ''),
(15859, 'Star Wars: Knights of the Old Republic', 'The game takes place in the world of the epic \"Star Wars\".\r\nWhen creating a character, first select a class - Soldier, Scoundrel or Scout. Classes differ in the proportion of strength, health and cunning. Later, three more classes will open, each of which will be a Jedi: Jedi Guardian, Jedi Consular, and Jedi Sentinel. This will also be the choice in favour of different proportions of strength, intelligence and skills.', 9.99, 'PC, Xbox One, Nintendo Switch, iOS, Android, macOS, Xbox 360, Xbox', 'Action, RPG', 4.35, NULL, 'OS: Windows XP and Windows Vista\r\nProcessor: Intel Pentium 3 1Ghz or AMD Athlon 1GHz\r\nMemory: 256 RAM\r\nGraphics: 32 MB with Hardware T&amp;L\r\nDirectX®: Directx 9.0b or better\r\nHard Drive: 3.5 GB\r\nSound: Directx 9.0b compatible\r\n', ''),
(16359, 'Divinity: Original Sin - Enhanced Edition', 'In the fantasy world of the game, there is a confrontation between the Order of the Sourcerers, the adepts of the magic of the \"Source\" and the Order of Source Hunters. Hunters believe that the Source\'s magic is dangerous and consider it their duty to destroy it.\r\n\r\nAt the beginning of the game, two characters are created, which the player will control. You can choose the gender, appearance, features and skills of the characters.\r\nThe game world is totally interactive.', 39.99, 'PC, Xbox One, PlayStation 4, macOS, Linux', 'Indie, RPG', 4.25, NULL, '', ''),
(17572, 'Batman: Arkham Asylum Game of the Year Edition', 'Batman: Arkham Asylum is the first game in Warner Brothers’ action-adventure franchise Batman: Arkham. The game takes places in fictional Asylum on Arkham Island near Gotham City where dangerous and mentally unstable criminals are kept.  \r\nThe story follows Batman as he captures Joker after his assault on Gotham City Hall. The game starts when Batman accompanies convoy that transfers Joker to the Arkham Asylum.', 2.99, 'PC, PlayStation 4, Xbox 360, PlayStation 3', 'Platformer, Adventure, Action', 4.39, NULL, 'OS: Vista/XP\r\n                    Processor: 3Ghz Intel or AMD or any Dual Core\r\n                    Memory: 1GB Ram(XP)/2GB Ram\r\n                    Graphics: PCI Express SM3 NVidia 6600/ ATI 1300\r\n                    DirectX®: 9\r\n                    Hard Drive: 8GB free space\r\n                    Sound: Any onboard sound card\r\n   ', NULL),
(17604, 'Return to Castle Wolfenstein', 'Return to Castle Wolfenstein is an FPS developed within Wolfenstein franchise in 2001. It rebooted the series and was the fifth part of the intellectual property. The game functions on the Quake III: Arena engine.\r\n\r\n###Plot\r\nThe whole series and the Return, in particular, depict the events of the World War II.', 4.99, 'PC, macOS, Linux, PlayStation 2', 'Shooter', 4.23, NULL, '3-D Hardware Accelerator (with 16MB VRAM with full OpenGL® support; Pentium® II 400 Mhz processor or Athlon® processor; English version of Windows® 2000/XP Operating System; 128 MB RAM; 16-bit high color video mode; 800 MB of uncompressed hard disk space for game files (Minimum Install), plus 300 MB for the Windows swap file; a 100% Windows® 2000/XP compatible computer system (including compatible 32-bit drivers for video card, sound card and input devices); 100% DirectX® 8.0a (included); 100% DirectX 3.0 or higher compatible sound card and drivers; 100% Microsoft-compatible mouse/keyboard and driver\r\nMultiplayer Requirements: Internet (TCP/IP) and LAN (TCP/IP and IPX) play supported, Internet play requires a 100% Windows 2000/XP compatible 56.6 Kbps (or faster) modem', ''),
(17620, 'Heroes of Might & Magic III - HD Edition', 'Heroes of Might and Magic III is a part of Might & Magic franchise and the third installment in its series. It is one of the most popular turn-based strategies ever created.\r\n\r\n###Plot\r\nThe game takes place in the fantasy world of Enroth. The story mode follows a large-scale war for the country of Erathia. Its queen Catherine defends her homeland from invading dark forces. The game\'s seven campaigns allow the player to participate in this war on various sides.', 14.99, 'PC, iOS', 'Strategy', 4.23, NULL, 'Minimum:\r\nOS: Windows 7 SP1, Windows 8, Windows 8.1 - both 32/64bit versions\r\nProcessor: Intel Core2 Duo E4400 @ 2.0 GHz or AMD Athlon64 X2 3800+ @ 2.0 GHz (or better)\r\nMemory: 2 GB RAM\r\nGraphics: nVidia GeForce 8800GT or AMD Radeon HD2900 (256MB VRAM or more with Shader Model 4.0)\r\nDirectX: Version 10\r\nNetwork: Broadband Internet connection\r\nSound Card: DirectX Compatible Sound Card with latest drivers\r\nAdditional Notes: Windows-compatible keyboard and mouse required  * This product does not support Windows® 98/ME/2000/NT4.0 * Windows XP and Vista are not officially supported for this title although they may run the game properly.', ''),
(17959, 'Ori and the Blind Forest: Definitive Edition', 'NEW IN THE DEFINITIVE EDITION\r\n• Packed with new and additional content: New areas, new secrets, new abilities, more story sequences, multiple difficulty modes, full backtracking support and much more!\r\n• Discover Naru’s past in two brand new environments.\r\n• Master two powerful new abilities – Dash and Light Burst.\r\n• Find new secret areas and explore Nibel faster by teleporting between Spirit Wells.\r\nThe forest of Nibel is dying.', 4.99, 'PC, Xbox One, Nintendo Switch, Xbox 360', 'Platformer, Adventure', 4.42, NULL, 'Minimum:\r\nOS: Windows 7\r\nProcessor: Intel Core 2 Duo E4500 @ 2.2GHz or AMD Athlon 64 X2 5600+ @ 2.8 GHz\r\nMemory: 4 GB RAM\r\nGraphics: GeForce 240 GT or Radeon HD 6570 – 1024 MB (1 gig)\r\nDirectX: Version 9.0c\r\nStorage: 11 GB available space', NULL),
(18080, 'Half-Life', 'Half-Life is the original game in the series. Being a revolutionary at the time, we follow the story of Gordon Freeman - a silent scientist at the facility called Black Mesa. Arriving late at work and hastily doing his routine he runs into the experiment field. However, the experiment goes completely wrong and opens a portal to a completely different dimension called Xen.', 1.99, 'PC, macOS, Linux, PlayStation 2, Dreamcast', 'Shooter, Action', 4.38, NULL, 'Minimum: 500 mhz processor, 96mb ram, 16mb video card, Windows XP, Mouse, Keyboard, Internet Connection\r\nRecommended: 800 mhz processor, 128mb ram, 32mb+ video card, Windows XP, Mouse, Keyboard, Internet Connection', NULL),
(18628, 'Arcanum: Of Steamworks and Magick Obscura', 'An Industrial Revolution in a World of Magick\r\nImagine a place of wonder, where magick and technology coexist in an uneasy balance, and an adventurer might just as easily wield a flintlock pistol as a flaming sword.  A place where great industrial cities house castle keeps and factories, home to Dwarves, Humans, Orcs and Elves alike.  A place of Ancient runes and steamworks, of magick and machines, of sorcery and science.  Welcome to the land of Arcanum.', 3.89, 'PC', 'RPG', 4.35, NULL, 'Minimum:\r\nOS: Windows XP / Vista / 7 / 8\r\nProcessor: 1.0 GHz\r\nMemory: 256 MB RAM\r\nGraphics: DirectX 7 Compatible 3D Card\r\nDirectX: Version 7.0\r\nStorage: 1200 MB available space\r\nSound Card: DirectX Compatible', ''),
(18726, 'Gothic', 'Gothic is a CRPG developed by Piranha Bytes. It is the first installment of the series.\r\n\r\nIn a fictional kingdom of Mirtana, the armies of king Robar the Second encountered orcs and lost battle after battle. The only mean of fighting back was the special equipment made from magical ore. This ore was mined by the prisoners regardless of their crimes. The game revolves around an unnamed protagonist and the story of his escape from the mine.', 19.99, 'PC, Nintendo Switch', 'Action, RPG', 4.36, NULL, 'OS: Windows XP/2000/ME/98/Vista\r\nProcessor: Intel Pentium III 700 MHz\r\nMemory: 256 MB Ram or higher\r\nGraphics: 3D graphics card with 32 MB Ram\r\nDirectX®: 8.1\r\nHard Drive: 4 GB\r\nSound: DirectX compatible\r\n', ''),
(18743, 'Desperados: Wanted Dead or Alive', '\"Desperados: Wanted Dead or Alive\" is the first strategy game ever to combine a movie-based and story-driven atmosphere of an adventure game with the intellectual challenge of a real time tactic game.\r\nIn this western-style title, discover a game of strategy and tactics played out in exceptional real time. You\'re in charge of a team of 6 mercenaries and must find a way to complete your missions, be it infiltrating an enemy fortress, saving a team member or escaping an ambush...', 1.24, 'PC, macOS, Linux', 'Strategy, Action', 4.21, NULL, 'Minimum:\r\nOS: Windows XP/7\r\nProcessor: 1 GHz Processor\r\nMemory: 256 MB RAM\r\nGraphics: 3D graphics card compatible with DirectX 7.1', ''),
(18905, 'Manifold Garden', 'Manifold Garden is a game that reimagines the laws of physics.\r\nRediscover gravity and explore an Escher-esque world of impossible architecture. Witness infinity in first-person, and master its rules to solve physics-defying puzzles. Cultivate a garden to open new paths forward, where an eternal expanse awaits.\r\nIn November 2012, William Chyr began working on Manifold Garden. Initially the project was called Relativity, and it drew inspiration from various physics thought experiments.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, macOS', 'Indie, Adventure', 4.23, NULL, 'Minimum:\r\nOS: Windows 7', ''),
(19056, 'S.T.A.L.K.E.R.: Call of Pripyat', 'With over 2 million copies sold, the new episode of the most internationally successful S.T.A.L.K.E.R. series seamlessly connects to the first part of the Shadow of Chernobyl.\r\n\r\nS.T.A.L.K.E.R.: Call of Pripyat takes PC gamers once again into the vicinity of the Chernobyl nuclear reactor that exploded in 1986. This so-called \"Zone\" is a highly contaminated area cordoned off by the military and now is combed through by the so-called stalkers, modern fortune hunters, in search of unique artifacts.', 19.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Shooter, RPG', 4.28, NULL, 'Minimum:\r\nOS: Windows XP, SP2\r\nProcessor: 2.2 GHz Intel Pentium 4/ 2.2. GHz AMD Athlon XP 2200+\r\nMemory: 768 MB RAM\r\nGraphics: nVidia GeForce 5900 128MB / AMD Radeon 9600 XT 128 MB\r\nDirectX®: DirectX 9 compatible\r\nHard Drive: 6 GB of free space\r\nSound: DirectX 9 compatible\r\n', ''),
(19309, 'Plants vs. Zombies GOTY Edition', 'This is a casual tower defense game from the PopCap Games studio. The main characters are homeowners who, with the help of plants, protect their garden from the invasion of zombies. The game was ported to all kinds of consoles, including mobile platforms.\r\n\r\nIn Plants vs. Zombies player places on the playing field of tower-plants, which automatically bombard attacking zombies. The main goal is not to let the undead get to the house.', 4.99, 'PC, PlayStation 4, iOS, Android, Nintendo DS, Nintendo DSi, macOS, Xbox 360, PlayStation 3, PS Vita', 'Strategy', 4.33, NULL, 'OS: Windows XP/Vista/7\r\n                    Processor: 1.2GHz+ processor\r\n                    Memory: 1GB of RAM\r\n                    Graphics: 128MB of video memory, 16-bit or 32-bit color quality\r\n                    DirectX: DirectX 8 or later\r\n                    Hard Drive: 65+MB of free hard drive space\r\n                    Sound: DirectX-compatible sound\r\n                    ', ''),
(19380, 'Dark Messiah of Might and Magic', 'Discover a new breed of Action-RPG game powered by an enhanced version of the Source™ Engine by Valve. Set in the Might & Magic® universe, players will experience ferocious combat in a dark and immersive fantasy environment.  Swords, Stealth, Sorcery. Choose your way to kill.\r\nCutting-edge technology: Experience an enhanced version of the famous Source™ Engine created by Valve.', 9.99, 'PC, Xbox 360', 'Action, RPG', 4.30, NULL, 'Minimum: AMD Athlon&trade;, Pentium&reg; 2.4 GHz, 512MB RAM, 128MB video card, 7GB HDD Space, Windows XP, Mouse, Keyboard, Internet Connection\r\n\r\nRecommended: AMD Athlon&trade;, Pentium&reg; 3.0 GHz, 256mb dx9 video card, 7GB HDD Space, Windows XP, Mouse, Keyboard, Internet Connection\r\n', ''),
(19406, 'Trackmania United Forever Star Edition', 'TrackMania is the most entertaining car racing game ever. Millions of players play it in single or multiplayer modes. TrackMania United Forever is the ultimate TrackMania edition thanks to the numerous additions and innovations it has to offer. This version brings together all the environments of the series and comes with a huge, enriched single-player campaign including 4 different modes and 420 progressively difficult tracks.\r\nDrive at mind-blowing speed on today\'s most spectacular tracks.', 29.99, 'PC', 'Racing', 4.30, NULL, 'Minimum: \r\nSupported OS:  Windows 2000/XP/XP-x64/Vista\r\nProcessor:  Pentium IV 1.6GHz / AthlonXP 1600+\r\nGraphics:  3D accelerator 16 MB DirectX 9.0c compatible graphics card\r\nDirectX Version:  DirectX 9.0c or better\r\nMemory:  256 MB (512 MB with Vista)\r\nSound:  16 bit DirectX compatible sound card\r\nHard Drive:  1.5 GB free disk space', ''),
(19441, 'Silent Hunter III', 'The king of submarine simulations returns with an all-new 3D engine, new crew command features, and more realistic WWII naval action than ever before. The movie-like graphics and simple, tension-filled gameplay make this the perfect game for the realism fanatic and the casual gamer.\r\nRealistic and immersive environment: The suspense-filled atmosphere includes realistic 3D water modeling, day/night cycles, and historically accurate subs, ships, and aircraft.', 9.99, 'PC', 'Strategy, Simulation', 4.25, NULL, '\r\nOS: Windows 2000 / XP(only)\r\nProcessor: Pentium&reg; III 1.4 GHz or AMD Athlon&trade; 1.4 GHz (Pentium 4 2.0 GHz or AMD Athlon 2.0 GHz recommended)\r\nMemory: 512 MB\r\nGraphics: 64 MB (128 MB video card recommended)(see supported list*)\r\nDirectX Version: DirectX 9.0\r\nSound: Direct X 9.0 compliant PCI card\r\nHard Drive: 1.5 GB\r\n*Supported Video Cards at Time of Release Nvidia&reg; GeForce&trade; 3/4/FX series (GeForce 4 MX NOT supported)ATI&reg; Radeon&reg; 8500/9000 families or newer\r\nLaptop versions of these chipsets may work but are not supported. These chipsets are currently the only ones that will run this game.', ''),
(19445, 'Disciples II: Gallean\'s Return', 'Disciples II: Gallean\'s Return is a compilation edition that includes the base game, Disciples II: Dark Prophecy, plus the two standalone expansions Disciples II: Guardians of the Light and Disciples II: Servants of the Dark.\r\n\r\nDisciples II: Guardians of the Light is a stand-alone expansion that lets you dive into the fantastical world of Disciples II as either the Empire or the Mountain Clans.', 1.19, 'PC', 'Strategy', 4.48, NULL, 'Minimum Configuration: Windows XP, Pentium II 233 Mhz, 32 Mb RAM, 1200 MB hard disk space, DirectX 7.1, 16-bit sound card, Video Card with 8 MB RAM\r\n             Recommended Configuration: Windows XP, Pentium II 300 Mhz, 64 MB RAM, 1400 MB hard disk space, DirectX 7.1, 16-bit sound card, Video Card with 16 MB RAM', NULL),
(19452, 'F.E.A.R.', 'Following the plot of the game, you play as a Point Man, who works for F. E. A. R. - a fictional special forces unit, which is forced to resist the rebel squad of cloned supersoldiers. He also faces a supernatural threat - Alma, a ghostly creature in the form of a little girl. The game consists of 11 episodes with an epilogue. As for the plot, it is completely confused until the end.\r\n\r\nOne of the primary abilities of the player is to slow down time.', 9.99, 'PC, Xbox 360, PlayStation 3', 'Shooter, Action', 4.27, NULL, 'Minimum:\r\nOperating System: Windows® XP, x64 or 2000 with latest service pack installed\r\nProcessor: Pentium® 4 1.7 GHz or equivalent processor\r\nMemory: 512 MB of RAM or more\r\nGraphics: 64 MB GeForce™ 4 Ti or Radeon® 9000 video card; Monitor that can display in 4:3 aspect ratio\r\nDirectX®: 9.0c (August Edition) or higher\r\nHard Drive: 17 GB free Hard Drive Space for installation; Additional hard drive space for a swap file and saved game files\r\nSound: 16-bit DirectX® 9.0 compliant sound card with support for EAX™ 2.0\r\nMultiplayer Requirements: Broadband or LAN connection for multiplayer games\r\n', ''),
(19457, 'Disciples II: Rise of the Elves', 'The award-winning series Disciples introduced a milestone in the game\'s very successful history; the introduction of a new race: The Elves. The Elven Race added a new dimension to the game and added countless hours of gameplay.\r\nNow that series has expanded further with Disciples II: Rise Of The Elves. This version features all of the campaigns found in the original and a brand new campaign that continues the storyline of the Elven Race.', 1.19, 'PC', 'Strategy', 4.48, NULL, 'Minimum Configuration: Windows XP, Pentium II 233 Mhz, 32 Mb RAM, 1200 MB hard disk space, DirectX 7.1, 16-bit sound card, Video Card with 8 MB RAM\r\n             Recommended Configuration: Windows XP, Pentium II 300 Mhz, 64 MB RAM, 1400 MB hard disk space, DirectX 7.1, 16-bit sound card, Video Card with 16 MB RAM\r\n', NULL),
(19628, 'FlatOut 2', 'The second part of the FlatOut series offers a variety of racing options. This part of the series has street racing as its main theme. \r\n\r\n###Gameplay\r\nThe game includes 34 cars divided by three types: \"derby\", \"race\" and \"street\". As the player\'s career progresses, he or she can unlock more cars that include everything up to school buses. Each car has its unique characteristics and may require different driving styles.', 9.99, 'PC, Linux, Xbox, PlayStation 2', 'Racing, Arcade', 4.33, NULL, 'Minimum: Windows XP/2000 XP SP2 / 2000 SP4, 256 MB RAM, 64 MB* video card, 2.0 GHz Pentium&reg; 4 or AMD&reg; 2000+ processor, DirectX compatible sound card, 3.5 GB free hard drive space, DirectX 9.0c, TCP/IP required for LAN play, 512kbs minimum, Broadband connection required\r\nFlatOut 2 supports gamepads. Gamepad with 8 buttons minimum (to support all configurable game commands) is recommended.\r\n* FlatOut 2 supports the following Chipsets, nVidia Geforce FX 5/6/7 series, ATI Radeon 9600 Pro/XT and above, ATI Radeon X200 and above. On-board/ integrated graphics cards and laptops not supported.', ''),
(19635, 'Fable: The Lost Chapters', 'Fable: The Lost Chapters is a re-release for the personal computers of the Fable game, originally created for the Xbox. This release includes content that is not included in the release of the game for Xbox.\r\nThe main character is personalized in great detail. Everything that he does affects him. He can get obesity from a plentiful meal, and if he drinks alcohol, he will become poorly oriented and eventually will vomit.', 3.29, 'PC, macOS, Xbox', 'Adventure, Action, RPG', 4.44, NULL, 'OS: Windows XP or later\r\nProcessor: 1.4 GHz equivalent or greater\r\nMemory: 256MB\r\nHard Disk Space: 3GB of available hard disk space\r\nVideo Card: 64 MB shader-capable video card\r\nSound: Sound card and either a set of speakers or a set of headphones are required for audio\r\n', NULL),
(19654, 'Samurai Gunn', 'Samurai Gunn is a lightning-fast Bushido brawler for two to four players. Each samurai is armed with a sword and gun, with only 3 bullets to a life. Discipline and quick reflexes are the key to deflecting bullets and sending your opponents’ heads rolling.', 0.99, 'PC, macOS', 'Action', 4.42, NULL, 'Minimum:\r\nOS: Windows 7\r\nMemory: 2 GB RAM\r\nStorage: 201 MB available space', NULL);
INSERT INTO `Games` (`id`, `name`, `description`, `price`, `platform`, `genres`, `rating`, `seller_id`, `min_requirements`, `recommended_requirements`) VALUES
(20709, 'Tom Clancy\'s Splinter Cell Chaos Theory', '###Instant classic\r\nThe stealth-action, which became a real classic and well-known among gamers. Excellent reviews and 92/100 score on Metacritic is a serious indicator. In the Steam community, there are still enthusiastic nostalgic reviews of almost everything that concerns Tom Clancy\'s Splinter Cell Chaos Theory. And there is an explanation for this. The game was released in 2005 and became the third in the Splinter Cell series.', 2.48, 'PC, Xbox One, Nintendo 3DS, Nintendo DS, Xbox 360, Xbox, PlayStation 3, PlayStation 2, GameCube', 'Adventure, Action', 4.38, NULL, 'Supported OS: Microsoft Windows® 2000/XP\r\nProcessor: Intel Pentium III or AMD Athlon, 1.4 GHz (Pentium IV or Athlon 2.2 GHz recommended)\r\nSystem Memory: 256 MB of RAM or above (512 MB recommended)\r\nVideo Card: 64 MB DirectX 9.0c compliant graphics card (128 MB recommended) \r\nSound Card: DirectX 9.0c compliant sound card (EAX 2.0 or higher recommended)\r\nDirectX Version: DirectX® version 9.0c or higher\r\nHard Disk: 4 GB available hard disk space\r\nSupported Peripherals: Windows compatible mouse and keyboard, joystick for Solo and Co-op modes\r\nMultiplay: Broadband with 64 Kbps data transfer upload rate (128 kbps recommended)\r\nNote: For the most up-to-date minimum requirement listings, please visit the FAQ for this game on our support website at: http://support.ubi.com.', NULL),
(21974, 'The Legend of Heroes: Trails in the Sky the 3rd', 'Half a year after the events of Trails in the Sky Second Chapter, Liberl has settled into peace once again—but even during peaceful times, there are many among the distinguished and fortunate burning with greed thanks to the influence of ancient artifacts.', 22.48, 'PC, PSP', 'RPG', 4.44, NULL, 'Minimum:\r\nOS: Windows XP\r\nProcessor: Pentium III 550 MHz\r\nMemory: 512 MB RAM\r\nGraphics: 32 MB VRAM, 3D accelerator compatible w/ DirectX 9.0c\r\nDirectX: Version 9.0c\r\nStorage: 5 GB available space\r\nSound Card: Compatible with DirectX 9.0c', NULL),
(22121, 'Celeste', 'Celeste is a platformer about climbing a mountain, from the creators of TowerFall.\r\nExplore a sprawling mountain with over 500 levels bursting with secrets, across 8 unique areas. Unlock a hardcore Remix for each area, with completely new levels that will push your climbing skills to the limit.\r\nMadeline can air-dash and climb any surface to gain ground. Controls are simple and accessible, but super tight and expressive with layers of depth to master. Deaths are sudden and respawns are fast.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS, Linux', 'Platformer, Indie', 4.37, NULL, 'Minimum:\r\nOS: Windows 7 or newer\r\nProcessor: Intel Core i3 M380\r\nMemory: 2 GB RAM\r\nGraphics: Intel HD 4000\r\nDirectX: Version 10\r\nStorage: 400 MB available space', ''),
(23741, 'Monument Valley 2', 'Guide a mother and her child as they embark on a journey through magical architecture, discovering illusionary pathways and delightful puzzles as you learn the secrets of the Sacred Geometry.\r\n\r\nSequel to the Apple Game of the Year 2014, Monument Valley 2 presents a brand new adventure set in a beautiful and impossible world.\r\n\r\nHelp Ro as she teaches her child about the mysteries of the valley, exploring stunning environments and manipulating architecture to guide them on their way.', 2.79, 'PC, iOS, Android', 'Casual, Adventure, Puzzle', 4.38, NULL, NULL, NULL),
(24442, 'No More Heroes 2: Desperate Struggle', 'The action takes place 3 years after Travis got the meaningless ranked\r\n1st title. Travis still lives in the same motel until he receives a\r\nmysterious parcel. Bishop, the only best friend Travis had, is dead.\r\nCoincidentally, the beautiful and cold Sylvia reappears in front of him\r\nto report the new assassin ranking of #51. Armed with a second beam\r\nkatana Travis is back to start his long journey that will lead him to\r\nthe path of revenge…', 19.99, 'PC, Nintendo Switch, Wii', 'Action', 4.27, NULL, 'Minimum:\r\nOS: Windows 8.1 or later\r\nProcessor: Intel Core i5-4460\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA GeForce GTX750Ti\r\nDirectX: Version 11\r\nStorage: 4 GB available space', ''),
(25373, 'No More Heroes', 'No More Heroes is the story of Travis Touchdown. He has received orders\r\nto kill a vagabond. In front of him appears the handsome assassin Helter\r\nSkelter. After a fierce skirmish, Travis eliminates Skelter, upon which\r\nSilvia Christel arrives. She informs Travis that his victory was done\r\nwithout UAA permission, but he nonetheless becomes the 11th best\r\nassassin. And so Travis’s journey begins.', 19.99, 'PC, Nintendo Switch, PlayStation 3, Wii', 'Action', 4.21, NULL, 'Minimum:\r\nOS: Windows 8.1 or later\r\nProcessor: Intel Core i5-4460\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA GeForce GTX750Ti\r\nDirectX: Version 11\r\nStorage: 4 GB available space', ''),
(28121, 'Slay the Spire', 'We fused card games and roguelikes together to make the best single player deckbuilder we could. Craft a unique deck, encounter bizarre creatures, discover relics of immense power, and Slay the Spire!\r\n\r\nFeatures:\r\n\r\n- Dynamic Deck Building: Choose your cards wisely! Discover hundreds of cards to add to your deck with each attempt at climbing the Spire. Select cards that work together to efficiently dispatch foes and reach the top.', 24.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, Android, macOS, Linux', 'Card, Strategy, Indie, RPG', 4.36, NULL, 'Minimum:\r\nOS: Windows XP, Vista, 7, 8/8.1, 10\r\nProcessor: 2.0 Ghz\r\nMemory: 2 GB RAM\r\nGraphics: 128mb Video Memory, capable of OpenGL 2.0+ support\r\nStorage: 250 MB available space', ''),
(28154, 'Cuphead', 'Hand-drawn 2D platformer in the style of 30s cartoons. 2D Dark Souls as the fans refer to the difficulty of this one. It took developers 6 years to create and polish their magnum opus. Cuphead is a classic run and gun adventure that heavily emphasizes on boss battles.\r\n\r\nPlay as Cuphead or his brother Mugman that signed a deal with the devil and know needs to bring the master souls of its debtors.', 13.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS', 'Platformer, Indie, Action', 4.37, NULL, NULL, NULL),
(28199, 'Ori and the Will of the Wisps', 'A New Journey Begins\r\n\r\nEmbark on an adventure with all new combat and customization options while exploring a vast, exotic world encountering larger than life enemies and challenging puzzles. Seek help from discoverable allies on your path to unravel Ori’s true destiny.\r\n\r\nExplore a vast, beautiful, and immersive world\r\nExplore a vast, beautiful, immersive, and dangerous world filled with gripping enemy encounters, challenging puzzles and thrilling escape sequences.', 7.48, 'PC, Xbox One, Xbox Series S/X, Nintendo Switch', 'Platformer, Adventure, Action', 4.41, NULL, 'Minimum:\r\nOS: Windows 10 Version 18362.0 or higher\r\nProcessor: AMD Athlon X4 | Intel Core i5 4460\r\nMemory: 8 GB RAM\r\nGraphics: Nvidia GTX 950 | AMD R7 370\r\nDirectX: Version 11\r\nStorage: 20 GB available space', NULL),
(28568, 'Assassin\'s Creed II', 'Assassin\'s Creed II is the second installment in the AC series, the center of which is stealths kills, exploring the world and enemy encounters. It is the straight sequel to the first part of the series and the beginning of the Ezio — the protagonist — trilogy, followed by \'Brotherhood\' and \'Revelation.\'\r\nThe events take place in Rome, during the Italian Renaissance (1476-1499), we play as Ezio Auditore and are fighting against Knight Templar, being the Assassins.', 4.99, 'PC, Xbox One, PlayStation 4, macOS, Xbox 360, PlayStation 3', 'Action', 4.43, NULL, NULL, NULL),
(28623, 'Batman: Arkham City', 'The plot of Arkham City begins one and a half years after the events of Arkham Asylum. Quincy Sharp, former superintendent of the Arkham Psychiatric Hospital, became mayor of Gotham and created the prison \"Arkham City\". Prisoners of Arkham City are not controlled by anyone in its borders, they are only forbidden from running away ...\r\nThere are all the regular characters in the game - Joker, Two-Face, Catwoman, Ra\'s al Ghul, James Gordon and others.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS, Xbox 360, PlayStation 3', 'Action', 4.41, NULL, '', ''),
(29153, 'Max Payne 2: The Fall of Max Payne', 'Max Payne 2: The Fall of Max Payne is a third-person shooter, serving as a direct sequel to the first game. The player follows the story of an NYPD detective, tasked with resolving crimes and answering many questions of its predecessor’s story. The game is set in noir tones, providing with main character’s monologues and various cutscenes.\r\nThe titular protagonist, Max Payne, is a traumatized officer of the police.', 2.48, 'PC, Xbox, PlayStation 2', 'Shooter, Action', 4.43, NULL, NULL, NULL),
(29177, 'Detroit: Become Human', 'In the future world, androids do almost everything that people do - they even start to think and feel. Although no one taught them this.\r\n\r\nThe plot of \"Become Human\" is built around three characters, each with a separate storyline, but they eventually merge into a single picture.\r\n\r\nConnor is a police investigator and android. He has a simple task - to find androids, deviated from the path given by humans. He has a special vision, it allows him to see events as they happened.', 39.99, 'PC, PlayStation 4', 'Adventure, Action', 4.34, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 (64 bit)\r\nProcessor: Intel Core i5-2300 @ 2.8 GHz or AMD Ryzen 3 1200 @ 3.1GHz or AMD FX-8350 @ 4.2GHz\r\nMemory: 8 GB RAM\r\nGraphics: Nvidia GeForce GTX 780 or AMD HD 7950 with 3GB VRAM minimum (Support of Vulkan 1.1 required)\r\nStorage: 55 GB available space', ''),
(29642, 'Silent Hill 2 (2001)', 'In the sequel to Silent Hill, Silent Hill 2 follows James Sunderland, whose life is shattered when his young wife Mary suffers a tragic death. Three years later, a mysterious letter arrives from Mary, beckoning him to return to their sanctuary of memories, the dark realm of Silent Hill. You must guide James through all-new environments and creepy new areas closed off in the original game. Real-time weather effects, fog, morphing, and shadows set the stage for heart-stopping frights.', 34.99, 'PC, Xbox, PlayStation 2', 'Adventure, Action', 4.38, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 x64\r\nProcessor: Intel Core i7-6700K | AMD Ryzen 5 3600\r\nMemory: 16 GB RAM\r\nGraphics: NVIDIA® GeForce® GTX 1070 Ti or AMD Radeon™ RX 5700 or Intel®  Arc™  A750\r\nDirectX: Version 12\r\nStorage: 50 GB available space\r\nSound Card: Windows Compatible Audio Device.\r\nAdditional Notes: Playing on minimum requirements should enable to play on Low/Medium quality settings in FullHD (1080p) in stable 30 FPS. SSD is recommended.', NULL),
(32029, 'Stronghold', 'The original castle sim, Stronghold HD allows you to design, build and destroy historical castles. Engage in medieval warfare against the AI in one of two single player campaigns or online with up to 8 players.\r\nWith 21 missions to test your mettle and four renegade lords to defeat, it is up to you to reunite medieval England and take back your lands from the treacherous Rat, Pig, Snake and Wolf.', 5.99, 'PC', 'Strategy', 4.36, NULL, '', ''),
(41494, 'Cyberpunk 2077', 'Cyberpunk 2077 is a science fiction game loosely based on the role-playing game Cyberpunk 2020.\r\n\r\n###Setting\r\nThe game is set in the year 2077 in a fictional futuristic metropolis Night City in California. In the world of the game, there are developed cybernetic augmentations that enhance people\'s strength, agility, and memory. The city is governed by corporations. Many jobs are taken over by the robots, leaving a lot of people poor and homeless.', 59.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Shooter, Action, RPG', 4.23, NULL, 'Minimum:\r\n\r\n\r\nOS: Windows 7 or 10 (64-bit)\r\n\r\nCPU: Intel Core i5-3570K or AMD FX-8310\r\n\r\nRAM: 8GB\r\n\r\nGPU: Nvidia GeForce GTX 780 3GB or AMD Radeon RX 470\r\n\r\nVRAM: 3GB\r\n\r\nDirect X: Version 12\r\n\r\nAvailable Storage Space: 70GB HDD \r\n\r\nGFX Setting Game Can Be Played On: Low', ''),
(42303, 'BioShock Infinite: Burial at Sea - Episode Two', '', 3.74, 'PC, PlayStation 4, Xbox 360, PlayStation 3', 'Shooter, Action', 4.43, NULL, NULL, NULL),
(43050, 'The Witcher 3: Wild Hunt – Hearts of Stone', 'Hearts of Stone is the first official expansion pack for The Witcher 3: Wild Hunt—an award-winning role-playing game set in a vast fantasy open world. Become Geralt of Rivia, a professional monster slayer hired to defeat a ruthless bandit captain, Olgierd von Everec, a man who possesses the power of immortality. Hearts of Stone packs over 10 hours of new adventures, introducing new characters, powerful monsters, unique romance and a brand new storyline shaped by your choices.', 1.99, 'PC, Xbox One, PlayStation 4', 'RPG', 4.76, NULL, NULL, NULL),
(43252, 'The Witcher 3: Wild Hunt – Blood and Wine', 'Welcome to the land of summer, a remote valley untouched by war. The land of wandering knights, noble ladies and magnificent wineries. What better time to visit than now, when this kingdom of virtue is torn apart by a series of savage massacres! Geralt of Rivia, a legendary monster slayer, takes on his last great contract. Blood and Wine offers over 30 hours of adventure, where beauty clashes with horror, and love dances with deceit.', 3.99, 'PC, Xbox One, PlayStation 4', 'RPG', 4.81, NULL, NULL, NULL),
(43737, 'Dark Souls III: Ashes of Ariandel', 'You, are the unkindled.  As part of the Dark Souls™ III Season Pass, expand your Dark Souls™ III experience with the Ashes of Ariandel™ DLC pack.  Journey to the snowy world of Ariandel and encounter new areas, bosses, enemies, weapons, armor set, magic spells and more.  Will you accept the challenge and embrace the darkness once more?', 14.99, 'PC, Xbox One, PlayStation 4', 'Action, RPG', 4.46, NULL, NULL, NULL),
(44295, 'West of Loathing', 'Say howdy to West of Loathing -- a single-player slapstick comedy adventure role-playing game set in the wild west of the Kingdom of Loathing universe.  Traverse snake-infested gulches, punch skeletons wearing cowboy hats, grapple with demon cows, and investigate a wide variety of disgusting spittoons.\r\nTalk your way out of trouble as a silver-tongued Snake Oiler, plumb the refried mysteries of the cosmos as a wise and subtle Beanslinger, or let your fists do the talking as a fierce Cow Puncher.', 10.99, 'PC, Nintendo Switch, iOS, Android, macOS, Linux', 'Indie, Adventure, RPG', 4.22, NULL, '7 / 8 / 10Processor: 3.3 GHz Intel® Core™2 Duo or betterMemory: 4 GB RAMGraphics: 1 GBHard Drive Space: 4 GBMouse, Keyboard', ''),
(45958, 'XCOM 2: War of the Chosen', 'XCOM® 2: War of the Chosen, is the expansion to the 2016 award-winning strategy game of the year. \r\n\r\nXCOM® 2: War of the Chosen adds extensive new content in the fight against ADVENT when additional resistance factions form in order to eliminate the alien threat on Earth. In response, a new enemy, known as the “Chosen,” emerges with one goal: recapture the Commander.', 3.99, 'PC, Xbox One, PlayStation 4, macOS, Linux', 'Strategy', 4.40, NULL, NULL, NULL),
(46508, 'Return Of The Obra Dinn', 'In 1802, the merchant ship \"Obra Dinn\" set out from London for the Orient with over 200 tons of trade goods. Six months later it hadn\'t met its rendezvous point at the Cape of Good Hope and was declared lost at sea.  \r\n \r\nEarly this morning of October 14th, 1807, the Obra Dinn drifted into port with sails damaged and no visible crew. As insurance adjustor for the East India Company\'s London Office, find means to board the ship and recover the Crew Muster Roll book for assessment.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS', 'Indie, Adventure', 4.33, NULL, 'Minimum:\r\nOS: Windows 7 or better\r\nProcessor: 2 GHz Intel i5 or better\r\nMemory: 4 GB RAM\r\nGraphics: Discrete GPU\r\nStorage: 2 GB available space\r\nAdditional Notes: Requires 720p or higher output resolution', ''),
(49428, 'The Red Strings Club', 'The Red Strings Club is a cyberpunk narrative experience about fate and happiness featuring the extensive use of pottery, bartending and impersonating people on the phone to take down a corporate conspiracy.\r\nThe professed altruistic corporation Supercontinent Ltd is on the verge of releasing Social Psyche Welfare: a system that will eliminate depression, anger and fear from society.', 14.99, 'PC, Nintendo Switch, macOS, Linux', 'Indie, Adventure', 4.21, NULL, '7 / 8 / 10Processor: Intel Pentium D 915 (2800 MHz), AMD Athlon 64 4000+ (2600 MHz) or equivalentMemory: 1 GB RAMGraphics: GeForce 7600GS (256 MB) or Radeon HD 2600 PRO (256 MB)Storage: 400 MB available space', ''),
(50734, 'Sekiro: Shadows Die Twice', 'Sekiro: Shadows Die Twice is a game about a ninja (or shinobi, as they call it), who is seeking revenge in the Sengoku era Japan.\r\n\r\n###Plot\r\nThe game is set in the 16th century in a fictionalized version of Japan. The main protagonist is a member of a shinobi clan. A samurai from the rival Ashina clan captured the protagonist\'s master, and the protagonist himself lost his arm trying to protect his leader.', 29.99, 'PC, Xbox One, PlayStation 4', 'Action, RPG', 4.38, NULL, NULL, NULL),
(50839, 'Baba Is You', 'Baba Is You is a puzzle game where you can change the rules by which you play. In every level, the rules themselves are lying as blocks you can interact with; by manipulating them, you can change how the level works and cause surprising, unexpected interactions! With some simple block-pushing you can turn yourself into a rock, turn patches of grass into dangerously hot obstacles, and even change the goal you need to reach to something entirely different.', 14.99, 'PC, Nintendo Switch, iOS, Android, macOS, Linux', 'Indie, Puzzle', 4.39, NULL, 'Minimum:\r\nOS: Windows 7\r\nProcessor: Intel Core i5-421U\r\nMemory: 100 MB RAM\r\nGraphics: NVIDIA GeForce 820M\r\nStorage: 50 MB available space\r\nSound Card: Realtek High Definition Audio', NULL),
(51431, 'The Room 4: Old Sins', 'Enter The Room: Old Sins and be transported to a place where tactile exploration meets challenging puzzles and a captivating story.\r\nThe sudden disappearance of an ambitious engineer and his high-society wife provokes the hunt for a precious artefact. The trail leads to the attic of their home, and the discovery of an old, peculiar dollhouse…\r\nExplore unsettling locations, follow obscure clues and manipulate bizarre contraptions as you uncover the mysteries within Waldegrave Manor.', 8.99, 'PC, iOS, Android', 'Adventure, Puzzle', 4.37, NULL, '', ''),
(51610, 'Dark Souls: Remastered', 'Then, there was fire. Re-experience the critically acclaimed, genre-defining game that started it all. Beautifully remastered, return to Lordran in stunning high-definition detail running at 60fps.\r\nDark Souls Remastered includes the main game plus the Artorias of the Abyss DLC.', 39.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Action, RPG', 4.48, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 7 64-bit, Service Pack 1\r\nProcessor: Intel Core i5-2300 2.8 GHz / AMD FX-6300, 3.5 GHz\r\nMemory: 6 GB RAM\r\nGraphics: GeForce GTX 460, 1 GB / Radeon HD 6870, 1 GB\r\nDirectX: Version 11\r\nStorage: 8 GB available space\r\nSound Card: DirectX 11 sound device\r\nAdditional Notes: Low Settings, 60 FPS @ 1080p', NULL),
(52201, 'Yakuza 6: The Song of Life', 'How far will you go for family? Three years after the events of Yakuza 5, Kazuma Kiryu, the Dragon of Dojima, returns in Yakuza 6: The Song of Life with the dream of living a quiet life. Upon his arrival, he discovers Haruka has been involved in an accident and has slipped into a coma, leaving her young son, Haruto, without care. To protect this child, Kiryu takes Haruto to the last place Haruka was spotted, Onomichi, Hiroshima.', 10.99, 'PC, Xbox One, PlayStation 4', 'Shooter, Adventure, Action', 4.41, NULL, 'Minimum:\r\nOS: Windows 10\r\nProcessor: Intel Core i5-3470 | AMD FX-6300\r\nMemory: 4 GB RAM\r\nGraphics: Nvidia GeForce GTX 660, 2 GB | AMD Radeon HD 7870, 2 GB\r\nDirectX: Version 11\r\nStorage: 40 GB available space\r\nAdditional Notes: Requires a CPU which supports the AVX and SSE4.2 instruction set', NULL),
(52383, 'The Banner Saga 3', 'Banner Saga 3 is the epic conclusion to a sweeping viking saga six years in the making. This strategic RPG, acclaimed for its strong story and compelling characters has won over 20 awards and been nominated for 4 BAFTA awards. \r\n\r\nAs the world crumbles around you, how will you survive when the Darkness draws near, and who will you trust with the fate of the world?', 24.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, Android, macOS', 'Strategy, Indie, RPG', 4.24, NULL, 'Minimum:\r\nOS: Windows 7 SP1\r\nMemory: 2 GB RAM\r\nStorage: 8 GB available space', ''),
(52884, 'DOOM', 'Doom (typeset as DOOM in official documents) is a 1993 science fiction horror-themed first-person shooter (FPS) video game by id Software. It is considered one of the most significant and influential titles in video game history, for having helped to pioneer the now-ubiquitous first-person shooter. The original game was divided into three nine-level episodes and was distributed via shareware and mail order.', 3.99, 'PC, PlayStation 4, Xbox One, Nintendo Switch, Android, Linux, Xbox 360, PlayStation 3, PlayStation, Game Boy Advance, SNES, SEGA Saturn, SEGA 32X, 3DO, Jaguar', 'Shooter, Action', 4.39, NULL, NULL, NULL),
(54491, 'Quake', '###The roots\r\nOne of the classic representatives of the first-person shooter genre. Designed and released in 1996 by the authors of the groundbreaking game Doom - iD Software. This is the first game to start the still ongoing series. Unlike its famous predecessor, the game uses an engine capable of rendering full-fledged 3D: the game supports an earlier version of 3D acceleration through OpenGL.', 9.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, macOS, Linux, Xbox, Nintendo 64, Classic Macintosh, Commodore / Amiga, SEGA Saturn', 'Shooter, Action', 4.26, NULL, 'Minimum: A 100% Windows XP/Vista-compatible computer system', ''),
(56088, 'killer7', 'Killer7  (stylized as killer7) is a 2005 action-adventure video game for the GameCube and PlayStation 2, developed by Grasshopper Manufacture and published by Capcom. The game was written and directed by Goichi Suda, also known by the nickname Suda51, and produced by Hiroyuki Kobayashi.\r\nThe game follows an elite group of assassins called the \"killer7\". The assassins, physical manifestations of a man named Harman Smith, perform hits on behalf of the United States government.', 19.99, 'PC, PlayStation 2, GameCube', 'Adventure, Action', 4.29, NULL, '', ''),
(56184, 'Resident Evil 4 (2005)', 'Resident Evil 4 is a third-person shooter game developed by Capcom Production Studio 4 and published by Capcom. The sixth major installment in the Resident Evil series, it was originally released for the GameCube in 2005. Players control U.S. government special agent Leon S. Kennedy, who is sent on a mission to rescue the U.S. president\'s daughter Ashley Graham, who has been kidnapped by a cult.', 4.99, 'PC, PlayStation 4, iOS, Android, PlayStation 2, Wii, GameCube', 'Shooter, Action', 4.40, NULL, NULL, NULL),
(58134, 'Marvel\'s Spider-Man', 'Marvel\'s Spider-Man offers the player to take on the role of the most famous Marvel superhero.\r\n\r\n###Plot\r\nThe game introduces Spider-Man as an already experienced superhero. By the time the game begins, Peter has captured the infamous Kingpin as well as several other supervillains. Now, a gang that goes by the name of Demons poses a new danger to New York. Meanwhile, Peter is working for the scientist Otto Octavius, who didn\'t yet become Dr. Octopus, on their science project.', 23.99, 'PC, PlayStation 5, PlayStation 4', 'Action', 4.45, NULL, NULL, NULL),
(58175, 'God of War (2018)', 'It is a new beginning for Kratos. Living as a man outside the shadow of the gods, he ventures into the brutal Norse wilds with his son Atreus, fighting to fulfill a deeply personal quest. \r\n\r\nHis vengeance against the Gods of Olympus years behind him, Kratos now lives as a man in the realm of Norse Gods and monsters. It is in this harsh, unforgiving world that he must fight to survive… And teach his son to do the same.', 19.99, 'PC, PlayStation 4', 'Action', 4.54, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 64-bit\r\nProcessor: Intel i5-2500k (4 core 3.3 GHz) or AMD Ryzen 3 1200 (4 core 3.1 GHz)\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA GTX 960 (4 GB) or AMD R9 290X (4 GB)\r\nDirectX: Version 11\r\nStorage: 70 GB available space\r\nAdditional Notes: DirectX feature level 11_1 required', NULL),
(58209, 'Dwarf Fortress', 'Prepare for the deepest, most intricate simulation of a world that has ever been created.\r\nNot just generated geometry -- a whole simulated world. Generated rise and fall of civilizations, personalities, creatures, cultures, etc. Infinite hours of gameplay.\r\nNow with graphics! (Optional ASCII mode available.', 29.99, 'PC, macOS, Linux', 'Strategy, Indie, RPG, Simulation', 4.33, NULL, 'Minimum:\r\nOS: XP SP3 or later\r\nProcessor: Dual Core CPU - 2.4GHz+\r\nMemory: 4 GB RAM\r\nGraphics: 1GB of VRAM: Intel HD 3000 GPU / AMD HD 5450 / Nvidia 9400 GT\r\nStorage: 500 MB available space', ''),
(58388, 'ZONE OF THE ENDERS: The 2nd Runner - M∀RS', 'JEHUTY lives. And there, ANUBIS thrives. ZONE OF THE ENDERS: The 2nd Runner returns with 4K and VR support.\r\nRelive the experience ZONE OF THE ENDERS: The 2nd Runner -  M∀RS as a full-length remaster of the classic fast-paced 3D robot action game, recreated in VR, native 4K and in full surround sound. Enter JEHUTY’s cockpit and fly through Martian skies!', 5.99, 'PC, PlayStation 4', 'Action', 4.47, NULL, NULL, NULL),
(58550, 'Ghost of Tsushima', 'The year is 1274. Samurai warriors are the legendary defenders of Japan--until the fearsome Mongol Empire invades the island of Tsushima, wreaking havoc and conquering the local population. As one of the last surviving samurai, you rise from the ashes to fight back. But, honorable tactics won\'t lead you to victory. You must move beyond your samurai traditions to forge a new way of fighting--the way of the Ghost--as you wage an unconventional war for the freedom of Japan.', 35.99, 'PC, PlayStation 5, PlayStation 4', 'Adventure, Action, RPG', 4.41, NULL, NULL, NULL),
(58755, 'Devil May Cry 5', 'Devil May Cry 5 is the sixth game in the Devil May Cry franchise and the fifth in its main series. \r\n\r\n###Plot\r\nThe game continues the plot of Devil May Cry 2. The demonic threat seems to have been forgotten, but the demons return, and there are new accidents around the world. Nero and Dante have parted ways, and Nero established his own agency. He also received a new robotic arm made by his engineer Nico. It replaces his Devil Bringer prosthetic that was stolen by a villain.', 4.49, 'PC, PlayStation 5, Xbox One, PlayStation 4', 'Action', 4.25, NULL, '', ''),
(58764, 'Outer Wilds', 'Outer Wilds is an open world mystery about a solar system trapped in an endless time loop.\r\nWelcome to the Space Program!\r\nYou\'re the newest recruit of Outer Wilds Ventures, a fledgling space program searching for answers in a strange, constantly evolving solar system.\r\n\r\nMysteries of the Solar System...\r\nWhat lurks in the heart of the ominous Dark Bramble? Who built the alien ruins on the Moon? Can the endless time loop be stopped? Answers await you in the most dangerous reaches of space.', 24.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Indie, Adventure, Puzzle', 4.36, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10', ''),
(58777, 'DOOM Eternal', 'As the DOOM Slayer, you return to find Earth has suffered a demonic invasion. Raze Hell and discover the Slayer’s origins and his enduring mission to rip and tear…until it is done.\r\n\r\nExperience the ultimate combination of speed and power as you battle your way across dimensions with the next leap in push-forward, first-person combat.', 39.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Nintendo Switch', 'Shooter, Action', 4.37, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system', ''),
(58813, 'Resident Evil 2', 'Resident Evil 2 is the remake of the 1998 game of the same name. \r\n\r\n###Plot\r\nThe plot of the remake is identical to that of the original game. The story follows the survivors of a zombie virus outbreak in the fictional Raccoon City. There are two protagonists: Claire Redfield, a high school student, and Leon Kennedy, a policeman. They both search for the ways to escape the infested city. Companions, such as Ada Wong and Sherry, occasionally follow the protagonists.', 13.79, 'PC, PlayStation 5, Xbox One, PlayStation 4, Nintendo Switch, macOS', 'Adventure, Action', 4.50, NULL, 'OS: WINDOWS® 7, 8, 8.1, 10 (64-BIT Required)\r\nProcessor: Intel® Core™ i5-4460, 2.70GHz or AMD FX™-6300 or better\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA® GeForce® GTX 760 or AMD Radeon™ R7 260x with 2GB Video RAM\r\nDirectX: Version 11', NULL),
(58890, 'Need For Speed: Most Wanted', 'Wake up to the smell of burnt asphalt as the scent of illicit street\r\nracing permeates the air. Need for Speed Most Wanted challenges you to\r\nbecome the most notorious and elusive street racer.\r\n\r\nFeatures\r\n\r\n• Master the art of cop evasion in Barricade Runner and other new race\r\nmodes.\r\n• Modify your ride to beat any tuner, muscle, or exotic.\r\n•\r\nCustomize the look of your car to elude police pursuit.\r\n• Win races,\r\nclimb the Blacklist, become the Most Wanted.', 3.99, 'PC, Xbox 360, Xbox, PlayStation 3, PlayStation 2, PSP, GameCube', 'Racing, Arcade', 4.45, NULL, NULL, NULL),
(59199, 'Divinity: Original Sin 2 - Definitive Edition', 'The Divine is dead. The Void approaches. And the powers lying dormant within you are soon to awaken. The battle for Divinity has begun. Choose wisely and trust sparingly; darkness lurks within every heart.\r\n\r\nWho will you be?\r\nA flesh-eating Elf, an Imperial Lizard or an Undead, risen from the grave? Discover how the world reacts differently to who - or what - you are.\r\nIt’s time for a new Divinity!\r\n\r\nGather your party and develop relationships with your companions.', 11.24, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, macOS', 'RPG', 4.52, NULL, NULL, NULL),
(59314, 'Anno 1800', 'Anno 1800 is the seventh installment of its franchise of real-time economic strategies. Like its predecessors, it is based around building cities.\r\n\r\n###Setting\r\nUnlike the previous two installments of the series, which were set in the future, Anno 1800 returns to the series\' roots and is based on real history. The game is set in the 19th century and explores the first twenty years of Industrial Revolution era. The industrialization and rising capitalism greatly influence the gameplay.', 14.99, 'PC', 'Strategy, Simulation', 4.23, NULL, 'Minimum:\r\nOS: Windows 7 SP1, Windows 8.1 or Windows 10', ''),
(59346, 'Desperados III', 'Desperados III is a modern real-time tactics game set in a ruthless Wild West scenario.\r\nYou take control of a ragtag band becoming a highly functional group of unlikely heroes and heroines. The very different strong personalities struggle to cooperate at first, but ultimately join forces to combine their distinctive specialties and challenge a seemingly superior foe. Hunted by ruthless bandits and corrupt lawmen, the Desperados III need to turn the tables with every mission.', 9.99, 'PC, Xbox One, PlayStation 4, macOS', 'Strategy, Action', 4.35, NULL, 'Minimum:\r\nOS: Windows 7 64-bit or higher\r\nProcessor: Intel i3 4th-Generation 3.5GHz, AMD Quad-Core 3.9GHz\r\nMemory: 4 GB RAM\r\nGraphics: Nvidia GTX 570, AMD Radeon HD 6950, 2GB Vram\r\nDirectX: Version 11\r\nStorage: 20 GB available space\r\nSound Card: DirectX 9.0c Compatible Sound Card with Latest Drivers\r\nAdditional Notes: These are preliminary system specs that can and will change!', ''),
(61206, 'Yuppie Psycho', 'First day at a new job? What a nightmare!\r\nJoin Brian Pasternack, a young man with no future in a dystopian 90s society, on his first day at one of the world’s largest companies, Sintracorp. Uncertain, unprepared, and massively unqualified, will Pasternack have what it takes to shine in Sintracorp’s hierarchy? It all depends on how he performs on his first assignment… and whether he survives it.', 16.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Indie, Adventure, Action', 4.30, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows XP\r\nProcessor: Core 2 Duo\r\nMemory: 2 GB RAM\r\nGraphics: Intel HD 3000', ''),
(254545, 'Cuphead: The Delicious Last Course', 'In Cuphead: The Delicious Last Course, Cuphead and Mugman are joined by Ms. Chalice for a DLC add-on adventure on a brand new island! With new weapons, new charms, and Ms. Chalice\'s brand new abilities, take on a new cast of multi-faceted, screen-filling bosses to assist Chef Saltbaker in Cuphead\'s final challenging quest.\r\n\r\nFeaturing Ms. Chalice as a brand new playable character with a modified moveset and new abilities. Once acquired, Ms.', 5.59, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Platformer', 4.50, NULL, NULL, NULL),
(257192, 'Psychonauts 2', 'Razputin Aquato, trained acrobat and powerful young psychic, has realized his life long dream of joining the international psychic espionage organization known as the Psychonauts! But these psychic super spies are in trouble. Their leader hasn\'t been the same since he was kidnapped, and what\'s worse, there\'s a mole hiding in headquarters. Raz must use his powers to stop the mole before they execute their secret plan--to bring the murderous psychic villain, Maligula, back from the dead!', 11.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X, macOS, Linux', 'Platformer, Adventure, Action', 4.36, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Requires a 64-bit processor and operating system\n\nAdditional Notes: TBD', NULL),
(259801, 'Final Fantasy VII', 'The world is under the control of Shinra, a corporation controlling the planet\'s life force as mako energy. In the city of Midgar, Cloud Strife, former member of Shinra\'s elite SOLDIER unit now turned mercenary lends his aid to the Avalanche resistance group, unaware of the epic consequences that await him.\r\n\r\nFINAL FANTASY VII REMAKE is a reimagining of the iconic original with unforgettable characters, a mind-blowing story, and epic battles.', 39.99, 'PC, PlayStation 5, PlayStation 4', 'Adventure, Action, RPG', 4.37, NULL, '', ''),
(262382, 'Disco Elysium', 'Disco Elysium is a groundbreaking blend of hardboiled cop show and isometric RPG. Solve a massive, open ended case in a unique urban fantasy setting. Kick in doors, interrogate suspects, or just get lost exploring the gorgeously rendered city of Revachol and unraveling its mysteries. Tough choices need to be made. What kind of cop you are — is up to you.', 9.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, macOS', 'Indie, Adventure, RPG', 4.37, NULL, 'Minimum:\r\n\r\nCPU: Intel i5-7500 or AMD 1500 equivalent\r\n\r\nRAM: 4 GB\r\n\r\nVIDEO CARD: Integrated Intel HD620 or equivalent\r\n\r\nPIXEL SHADER: 5.0\r\n\r\nVERTEX SHADER: 5.0\r\n\r\nOS: Windows 7/8/10\r\n\r\nFREE DISK SPACE: 22 GB', NULL),
(265332, 'Nightshade (2016)', 'A romance visual novel game made in collaboration with D3P Otomebu and\r\nRed Entertainment, two companies known for their creative range and\r\nproduction of heavyweight visual novel games.\r\n\r\nCharacter design and illustrations are by the popular illustrator Teita.\r\n\r\nThe romance revolves around Ninjas who have lived through the Sengoku\r\nPeriod.\r\n\r\nThe story is set in Japan soon after the Sengoku Period.', 34.99, 'PC, Nintendo Switch, PS Vita', 'Adventure', 4.25, NULL, 'Minimum:\r\nOS: Windows 7\r\nProcessor: Core2Duo 2.66 GHz\r\nMemory: 4 GB RAM\r\nGraphics: Resolution:1280x720以上\r\nDirectX: Version 9.0c\r\nStorage: 4 GB available space\r\nAdditional Notes: Need DirectX End-User Runtimes.', ''),
(274755, 'Hades', 'Hades is a rogue-like dungeon crawler that combines the best aspects of Supergiant\'s critically acclaimed titles, including the fast-paced action of Bastion, the rich atmosphere and depth of Transistor, and the character-driven storytelling of Pyre.', 6.24, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Indie, Adventure, Action, RPG', 4.42, NULL, 'Minimum:\r\nOS: Windows 7 SP1\r\nProcessor: Dual Core 3.0ghz\r\nGraphics: 1GB VRAM / OpenGL 2.1+ support\r\nStorage: 10 GB available space', NULL),
(274757, 'Sayonara Wild Hearts', 'Sayonara Wild Hearts is a euphoric music video dream about being awesome, riding motorcycles, skateboarding, dance battling, shooting lasers, wielding swords, and breaking hearts at 200 mph.\r\nAs the heart of a young woman breaks, the balance of the universe is disturbed. A diamond butterfly appears in her dreams and leads her through a highway in the sky, where she finds her other self: the masked biker called The Fool.', 6.48, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, macOS', 'Casual, Indie, Action', 4.38, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 7\r\nProcessor: Intel Core2 Duo E8300 or AMD Athlon X2 Dual Core 6000+\r\nMemory: 2 GB RAM\r\nGraphics: NVIDIA GeForce 8800 GT or ATI Radeon HD 3870 or Intel HD Graphics 630\r\nDirectX: Version 9.0c\r\nStorage: 2 GB available space', NULL),
(274758, 'The Stanley Parable: Ultra Deluxe', 'When The Stanley Parable came out, a lot of people asked us for more endings and more content.\r\nWe told them it didn\'t need more content, that it was fine just the way it was, that it already had the perfect number of endings.\r\nWhat a sorry sack of lies that was.\r\nWe knew it. We knew it were lying and we did it anyway. We’ve carried that shame around with us for years, a burden weighing on every moment of every day.\r\nEnough is enough.\r\nIt’s time to fix this, to unburden our shame.', 24.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, macOS, Linux', 'Casual, Indie, Adventure', 4.37, NULL, 'Minimum:\r\nOS: Windows 7 or higher 64bit\r\nProcessor: Intel Core i3 2.00 GHz or AMD equivalent\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA GeForce 450 or higher with 1GB Memory\r\nStorage: 5 GB available space', ''),
(297208, 'NieR:Automata Game of the YoRHa Edition', 'The NieR:Automata™ Game of the YoRHa Edition includes the game itself and comes packed with DLC and bonus content for the full experience of the award-winning post-apocalyptic action RPG, including:\r\n3C3C1D119440927 DLC\r\nValve Character Accessory\r\nCardboard Pod Skin\r\nRetro Grey Pod Skin\r\nRetro Red Pod Skin\r\nGrimoire Weiss Pod\r\nMachine Mask Accessory\r\nExclusive set of wallpapers in the following sizes: 1024 x 768, 1280 x 1024, 1920 x 1080, 2560 x 1600', 39.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Action', 4.26, NULL, '', ''),
(302836, 'AI: The Somnium Files', 'In a near-future Tokyo, detective Kaname Date is on the case of a mysterious serial killer. Date must investigate crime scenes as well as dreams on the hunt for clues. From the mind of Kotaro Uchikoshi (Zero Escape series director), with character design by the Yusuke Kozaki (NO MORE HEROES, Fire Emblem series), a thrilling neo-noir detective adventure is about to unfold.\r\n\r\nSTORY\r\n\r\nOne rainy night in November, a woman\'s body is found at an abandoned theme park, mounted on a merry-go-round horse.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Adventure', 4.26, NULL, 'Minimum:\r\nOS: 64-bit Windows 7\r\nProcessor: Intel Core i7-3770 @ 3.40 GHz or better\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA@ GeForce@ GTX 460 or better\r\nDirectX: Version 11\r\nStorage: 30 GB available space\r\nSound Card: DirectX compatible soundcard or onboard chipset\r\nAdditional Notes: 2 GB VRAM', ''),
(304187, 'Five Nights at Freddy’s: Help Wanted', 'Five Nights at Freddy’s: Help Wanted is a collection of classic and original mini-games set in the Five Nights universe. Survive terrifying encounters with your favorite killer animatronics in a collection of new and classic FIVE NIGHTS AT FREDDY’S™ experiences. “Where fantasy and fun come to life!”\r\n\r\nA VR headset is NOT required to play.\r\n\r\n    YOU’RE HIRED - Time to get your hands dirty.', 29.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, Android', 'Casual, Adventure, Action', 4.25, NULL, 'Minimum:\r\nOS: Windows 8\r\nProcessor: Intel i5-4590 or greater / AMD FX 8350 or greater\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA GeForce GTX 970 / AMD R9 390\r\nStorage: 11 GB available space', ''),
(304247, 'A Short Hike', 'Hike, climb, and soar through the peaceful mountainside landscapes of Hawk Peak Provincial Park.\r\nFollow the marked trails or explore the backcountry as you make your way to the summit.\r\nAlong the way, meet other hikers, discover hidden treasures, and take in the world around you.\r\n\r\nKey Features:\r\n- Explore the island any way you like. Choose your own path to follow and see where it leads you. You never know what you might stumble into!', 7.99, 'PC, PlayStation 4, Nintendo Switch, macOS, Linux', 'Indie, Adventure', 4.35, NULL, 'Minimum:\r\nOS: Windows 7 SP1+ (or later)\r\nProcessor: Intel or AMD Dual Core at 2 GHz or better\r\nMemory: 2 GB RAM\r\nGraphics: Intel Graphics 4400 or better\r\nDirectX: Version 11\r\nStorage: 300 MB available space', ''),
(304922, 'Astalon: Tears of the Earth', 'Three explorers wander through a post-apocalyptic desert to find a way to save the people in their village. A dark, twisted tower has been pushed up from the depths of the Earth... but does it hold the answers they seek?\r\nAstalon: Tears Of The Earth is LABS Works\' love letter to the games of the 80s, but what may seem like a simple action-platformer has several surprises under the hood!\r\nUse the unique skills and personalities of three different characters to explore an evil tower.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Adventure, Action', 4.31, NULL, '最低配置:\r\n操作系统: Windows 10', ''),
(307137, 'Live A Live', 'Live A Live (ライブ・ア・ライブ) is a RPG video game published by SquareSoft released on September 2nd, 1994 for the SNES. The game was directed by Takashi Tokita, known for his work in Final Fantasy IV and Chrono Trigger. The soundtrack was composed by Yoko Shimomura, being her first full project at square.', 19.99, 'PC, SNES', 'Adventure, RPG', 4.45, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows® 10 / 11 64-bit\r\nProcessor: AMD A8-7600 / Intel® Core™ i3-3210\r\nMemory: 8 GB RAM\r\nGraphics: AMD Radeon™ RX 460 / NVIDIA® GeForce® GTX 750\r\nDirectX: Version 12\r\nStorage: 8 GB available space\r\nAdditional Notes: 1280x720, Preset &quot;Low&quot;, 30FPS', NULL),
(324997, 'Baldur\'s Gate III', 'Gather your party, and return to the Forgotten Realms in a tale of fellowship and betrayal, sacrifice and survival, and the lure of absolute power.\r\n\r\nMysterious abilities are awakening inside you, drawn from a Mind Flayer parasite planted in your brain. Resist, and turn darkness against itself. Or embrace corruption, and become ultimate evil.\r\n\r\nFrom the creators of Divinity: Original Sin 2 comes a next-generation RPG, set in the world of Dungeons and Dragons.', 44.99, 'PC, PlayStation 5, Xbox Series S/X, macOS', 'Strategy, Adventure, RPG', 4.44, NULL, 'Minimum:\r\n\r\nRequires a 64-bit processor and operating system\r\n\r\nOS: Windows 7 SP1 64-bit\r\n\r\nProcessor: Intel i5-4690 / AMD FX 4350\r\n\r\nMemory: 8 GB RAM\r\n\r\nGraphics: Nvidia GTX 780 / AMD Radeon R9 280X\r\n\r\nDirectX: Version 11\r\n\r\nStorage: 150 GB available space\r\n\r\nAdditional Notes: Default API is Vulkan 1.1. Directx11 API also provided. The minimum requirements might decrease over the course of Early Access, as performance improves.', NULL),
(326243, 'Elden Ring', 'The Golden Order has been broken.\r\n\r\nRise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between.\r\n\r\nIn the Lands Between ruled by Queen Marika the Eternal, the Elden Ring, the source of the Erdtree, has been shattered.\r\n\r\nMarika\'s offspring, demigods all, claimed the shards of the Elden Ring known as the Great Runes, and the mad taint of their newfound strength triggered a war: The Shattering.', 38.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X', 'Action, RPG', 4.39, NULL, 'Minimum:\r\nOS: Windows 10\r\nProcessor: INTEL CORE I5-8400 or AMD RYZEN 3 3300X\r\nMemory: 12 GB RAM\r\nGraphics: NVIDIA GEFORCE GTX 1060 3 GB or AMD RADEON RX 580 4 GB\r\nDirectX: Version 12\r\nStorage: 60 GB available space\r\nSound Card: Windows Compatible Audio Device\r\nAdditional Notes: ', NULL),
(326253, 'Age of Empires II: Definitive Edition', 'Age of Empires II: Definitive Edition celebrates the 20th anniversary of one of the most popular strategy games ever with stunning 4K Ultra HD graphics, a new and fully remastered soundtrack, and brand-new content, “The Last Khans” with 3 new campaigns and 4 new civilizations.\r\n\r\nExplore all the original campaigns like never before as well as the best-selling expansions, spanning over 200 hours of gameplay and 1,000 years of human history.', 34.99, 'PC, Xbox Series S/X', 'Strategy', 4.29, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 64bit\r\nProcessor: Intel Core 2 Duo or AMD Athlon 64x2 5600+\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA® GeForce® GT 420 or ATI™ Radeon™ HD 6850 or Intel® HD Graphics 3000 or better\r\nDirectX: Version 11\r\nNetwork: Broadband Internet connection\r\nStorage: 30 GB available space', ''),
(329552, 'Resident evil 7 Banned Footage Vol.2', 'This newly unearthed footage exposes further secrets from the dark past of the Baker family.<br/>\r\n<br/>\r\n- 21: Take part in a twisted card game where the stakes are your life.<br/>\r\n- Daughters: Experience the events that unfolded on the night it all started, when the downfall of the Bakers was set in motion.<br/>\r\nIncludes the extra bonus content Jack\'s 55th Birthday.', 14.99, 'PC', 'Action', 4.27, NULL, '', ''),
(339958, 'Persona 5 Royal', 'Wear the mask.  Reveal your truth.\r\nPrepare for an all-new RPG experience in Persona®5 Royal based in the universe of the award-winning series, Persona®! Don the mask of Joker and join the Phantom Thieves of Hearts. Break free from the chains of modern society and stage grand heists to infiltrate the minds of the corrupt and make them change their ways! Persona®5 Royal is packed with new characters, story depth, new locations to explore, & a new grappling hook mechanic for access to new areas.', 17.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Adventure, RPG', 4.75, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10\r\nProcessor: Intel Core i7-4790, 3.4 GHz | AMD Ryzen 5 1500X, 3.5 GHz\r\nMemory: 8 GB RAM\r\nGraphics: Nvidia GeForce GTX 650 Ti, 2 GB | AMD Radeon R7 360, 2 GB\r\nDirectX: Version 11\r\nStorage: 41 GB available space\r\nAdditional Notes: Low 720p @ 60 FPS. Requires a CPU which supports the AVX and SSE4.2 instruction set.', NULL),
(366881, 'Little Nightmares II', 'Return to the world of charming horror that has terrified over 1 million fans. Face a completely new set of distorted enemies as Mono, and learn how to be as courageous as a child.', 9.89, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Platformer, Adventure, Action', 4.37, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10\r\nProcessor: Intel Core i5-2300 | AMD FX-4350\r\nMemory: 4 GB RAM\r\nGraphics: Nvidia GeForce GTX 570, 1 GB | AMD Radeon HD 7850, 2 GB\r\nDirectX: Version 11', NULL),
(366885, 'Dragon Age: Origins - Ultimate Edition', 'Get the ultimate Dragon Age™ experience! Dragon Age™: Origins – Ultimate Edition includes:\r\n\r\n~ Dragon Age™: Origins\r\nDiscover the groundbreaking RPG, winner of more than 50 awards including more than 30 \'Game of the Year\' awards! You are a Grey Warden, one of the last of a legendary order of guardians. With the return of mankind\'s ancient foe and the kingdom engulfed in civil war, you have been chosen by fate to unite the shattered lands and slay the archdemon once and for all.', 7.48, 'PC, Xbox 360, PlayStation 3', 'RPG', 4.44, NULL, 'Minimum:\r\nOS: Windows XP (SP3) or Windows Vista (SP1) or Windows 7\r\nProcessor: Intel Core 2 Single 1.6 Ghz Processor (or equivalent) or AMD 64 2.0 GHz Processor (or equivalent)\r\nMemory: 1GB (1.5 GB Vista and Windows 7)\r\nGraphics: ATI Radeon X850 256MB or NVIDIA GeForce 6600 GT 128MB or greater (Windows Vista: Radeon X1550 256 MB or NVidia GeForce 7600GT 256MB)\r\nDirectX®: DirectX (November 2007)\r\nHard Drive: 20 GB HD space\r\nSound: Direct X Compatible Sound Card\r\n', NULL);
INSERT INTO `Games` (`id`, `name`, `description`, `price`, `platform`, `genres`, `rating`, `seller_id`, `min_requirements`, `recommended_requirements`) VALUES
(366889, 'Monster Hunter World: Iceborne', 'A diverse locale, rich with endemic life.\r\nNumerous monsters that prey on each other and get into turf wars.\r\nA new hunting experience, making use of the densely packed environment.\r\nMonster Hunter: World, the game that brought you a new style of hunting action,\r\nis about to get even bigger with the massive Monster Hunter World: Iceborne expansion!\r\n\r\n- All-new Hunting Mechanics\r\n\r\nAll 14 weapon types have new moves and combos. Each weapon has more unique actions than ever before.', 39.99, 'PC, Xbox One, PlayStation 4', 'Massively Multiplayer, Action, RPG', 4.46, NULL, NULL, NULL),
(369157, 'Yakuza: Like a Dragon', 'RISE LIKE A DRAGON\r\n\r\nIchiban Kasuga, a low-ranking grunt of a low-ranking yakuza family in Tokyo, faces an 18-year prison sentence after taking the fall for a crime he didn\'t commit. Never losing faith, he loyally serves his time and returns to society to discover that no one was waiting for him on the outside, and his clan has been destroyed by the man he respected most.', 19.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X', 'Adventure, Action, RPG', 4.35, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10\r\nProcessor: Intel Core i5-3470 | AMD FX-8350\r\nMemory: 8 GB RAM\r\nGraphics: Nvidia GeForce GTX 660, 2 GB | AMD Radeon HD 7870, 2 GB\r\nStorage: 40 GB available space\r\nAdditional Notes: Requires a CPU which supports the AVX and SSE4.2 instruction set', ''),
(374507, 'Guilty Gear -Strive', 'Discover the Smell of the Game with Guilty Gear -Strive-! Immerse yourself in new gameplay mechanics designed to be simple and welcoming for fighting game newcomers, yet deep and creative for veterans. Ride the Fire into a heavy metal inspired alternate future full of over-the-top action, style and fun! Blazing!\r\n\r\n“Guilty Gear -Strive-“ is the latest entry in the critically acclaimed Guilty Gear fighting game franchise.', 39.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X', 'Action, Fighting', 4.24, NULL, 'Minimum:\r\nOS: Windows 8/10 (64-bit OS required)\r\nProcessor: AMD FX-4350, 4.2 GHz / Intel Core i5-3450, 3.10 GHz\r\nMemory: 4 GB RAM\r\nGraphics: Radeon HD 6870, 1 GB / GeForce GTX 650 Ti, 1 GB\r\nDirectX: Version 11\r\nNetwork: Broadband Internet connection\r\nStorage: 20 GB available space\r\nSound Card: DirectX compatible soundcard or onboard chipset', ''),
(376934, 'Pistol Whip', 'Inspired by God-mode action movies like John Wick and Equilibrium, Pistol Whip throws you gun-first into an explosive batch of hand-crafted action sequences each set to their own breakneck soundtrack. But unlike traditional music games, Pistol Whip has no line in the sand; you have complete freedom to shoot, melee, and dodge targets to the rhythm YOU see fit.\r\nFeaturesPair the pulse-pounding pace of an FPS with the flow-state energy of a music game in a cinematic symphony of violence.', 29.99, 'PC, PlayStation 5, PlayStation 4', 'Shooter, Indie, Action', 4.26, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 (64-bit)\r\nProcessor: Intel Core i5-4590 or equivalent\r\nMemory: 8 GB RAM\r\nGraphics: Geforce GTX 970 or equivalent\r\nDirectX: Version 11\r\nStorage: 800 MB available space\r\nSound Card: Required\r\nAdditional Notes: Headphones recommended', ''),
(384567, 'Crusader Kings III', 'An Heir is Born in Crusader Kings III\r\nCrusader Kings III is the newest generation of Paradox Development Studio’s beloved medieval role-playing grand strategy game. Expand and improve your realm, whether a mighty kingdom or modest county. Use marriage, diplomacy and war to increase your power and prestige in a meticulously detailed map that stretches from Spain to India, Scandinavia to Central Africa.\r\nBut uneasy lies the head that wears a crown!', 49.99, 'PC, PlayStation 5, Xbox Series S/X, macOS, Linux', 'Strategy, RPG, Simulation', 4.31, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: TBC\r\nProcessor: TBC\r\nGraphics: TBC', ''),
(385406, 'Dark Souls III: The Ringed City', 'Fear not, the dark, ashen one.  <br/>\r\nThe Ringed City is the final DLC pack for Dark Souls III – an award-winning, genre-defining Golden Joystick Awards 2016 Game of the year RPG.  Journey to the world’s end to search for the Ringed City and encounter new lands, new bosses, new enemies with new armor, magic and items.  Experience the epic final chapter of a dark world that could only be created by the mind of Hidetaka Miyazaki.       <br/>\r\nA New World.  One Last Journey.', 14.99, 'PC, Xbox One, PlayStation 4', 'Action, RPG', 4.60, NULL, NULL, NULL),
(392019, 'Half-Life: Alyx', 'Half-Life: Alyx is Valve’s VR return to the Half-Life series. It’s the story of an impossible fight against a vicious alien race known as the Combine, set between the events of Half-Life and Half-Life 2.\r\n\r\nPlaying as Alyx Vance, you are humanity’s only chance for survival. The Combine’s control of the planet since the Black Mesa incident has only strengthened as they corral the remaining population in cities. Among them are some of Earth’s greatest scientists: you and your father, Dr. Eli Vance.', 59.99, 'PC', 'Shooter, Adventure, Action', 4.35, NULL, 'Minimum:\r\nOS: Windows 10\r\nProcessor: Core i5-7500 / Ryzen 5 1600\r\nMemory: 12 GB RAM\r\nGraphics: GTX 1060 / RX 580 - 6GB VRAM', ''),
(406445, 'Wide Ocean Big Jacket', 'An aunt and uncle take their middle-school niece and her boyfriend on an overnight camping trip in WIDE OCEAN BIG JACKET\r\n\r\nTake part in a classic camping trip: Roast hot dogs on the fire, go birdwatching, tell ghost stories, grab a beverage from the cooler and do cartwheels on the beach.\r\n\r\nWOBJ is a short story game including 20 chapters, 4 playable characters, 10,000 words of dialog and 8 explorable areas, all rendered in a beautiful 2D/3D art style.', 7.99, 'PC, Nintendo Switch, iOS, macOS', 'Casual, Indie, Adventure, Simulation', 4.31, NULL, 'Minimum:\r\nOS: Windows 7 SP1+\r\nProcessor: SSE2 instruction set support\r\nGraphics: Graphics card with DX10 (shader model 4.0) capabilities\r\nAdditional Notes: Made With Unity', ''),
(409575, 'Pathfinder: Wrath of the Righteous', 'Embark on a journey to a realm overrun by demons in a new epic RPG from the creators of the critically acclaimed Pathfinder: Kingmaker. Explore the nature of good and evil, learn the true cost of power, and rise as a Mythic Hero capable of deeds beyond mortal expectations.\r\nYour path will take you to the Worldwound, where the opening of a rift to the Abyss has unleashed all-consuming terror across the land.', 19.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS', 'RPG', 4.24, NULL, 'Minimum:\r\n\r\nRequires a 64-bit processor and operating system\r\n\r\nOS: Windows 7\r\n\r\nProcessor: Intel(R) Core(TM) i3-2310M CPU @ 2.10GHz\r\n\r\nMemory: 6 GB RAM\r\n\r\nGraphics: NVIDIA GeForce 940M\r\n\r\nStorage: 50 GB available space', ''),
(411611, 'Yakuza 5 Remastered', 'Experience the fifth chapter of the Kazuma Kiryu saga in 1080p and 60fps.\r\n\r\nIn December 2012, Kazuma Kiryu left his past as a \"\"legendary yakuza\"\" and his place of peace in Okinawa.\r\n\r\nHe now spends his days as a cab driver in a corner of Fukuoka\'s red-light district, hiding his true identity. All for the sake of fulfilling the \"\"dream\"\" of an important person.', 19.99, 'PC, Xbox One, PlayStation 4', 'Action', 4.21, NULL, 'Minimum:SO: Windows 7Processor: Intel Core i3-2100 | AMD FX-4350Memory: 4 GB RAMGraphics: Nvidia GeForce GTS 450, 1 GB | AMD Radeon HD 5770, 1 GBAdditional Notes: 32.5 GB of available space', ''),
(422859, 'NieR Replicant', 'A thousand-year lie that would live on for eternity...\r\n\r\nNieR Replicant ver.1.22474487139... is an updated version of NieR Replicant, previously only released in Japan.\r\n\r\nDiscover the one-of-a-kind prequel to the critically-acclaimed masterpiece NieR:Automata. Now with a modern upgrade, experience masterfully revived visuals, a fascinating storyline and more!\r\n\r\nThe protagonist is a kind young man living in a remote village.', 23.99, 'PC, Xbox One, PlayStation 4', 'Adventure, Action, RPG', 4.38, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows® 10 64-bit\r\nProcessor: AMD Ryzen™ 3 1300X; Intel® Core™ i5-6400\r\nMemory: 8 GB RAM\r\nGraphics: AMD Radeon™ R9 270X; NVIDIA® GeForce® GTX 960\r\nDirectX: Version 11\r\nStorage: 26 GB available space\r\nSound Card: DirectX Compatible Sound Card\r\nAdditional Notes: 60 FPS @ 1280x720', NULL),
(427679, 'Atelier Escha & Logy: Alchemists of the Dusk Sky DX', 'Atelier Escha & Logy is the sequel to Atelier Ayesha. Building upon the mythos introduced in Ayesha,\r\nthis game features an even more mysterious atmosphere and supernatural environments in order to create a strong sense of the fantastic.\r\n\r\nChoose between two heroes, who will have different perspectives on events in the game, as well as having unique personal events throughout.', 39.99, 'PC, PlayStation 4, Nintendo Switch', 'Adventure, RPG', 4.29, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows® 8.1, Windows® 10 (64bit required)\r\nProcessor: Core i5 2.6GHz or better\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA GeForce GTX660 or better,1280x720 (Graphic Memory 2GB or better)\r\nDirectX: Version 11\r\nNetwork: Broadband Internet connection\r\nStorage: 17 GB available space\r\nSound Card: 16bit Stereo 48kHzWAVE', ''),
(428664, 'A Space for the Unbound', 'Check out the free prologue chapter here!https://store.steampowered.com/app/1201280/\r\nAbout the GameHigh school is ending and the world is ending with it\r\nA Space For The Unbound is a slice-of-life adventure game with beautiful pixel art set in the late 90s rural Indonesia that tells a story about overcoming anxiety, depression, and the relationship between a boy and a girl with supernatural powers.', 9.99, 'PC, Nintendo Switch', 'Indie, Adventure', 4.42, NULL, 'Minimum:\r\nOS: Windows 7\r\nProcessor: 2 GHz or higher\r\nMemory: 1 GB RAM\r\nStorage: 500 MB available space', NULL),
(442854, 'Mafia: Definitive Edition', 'Part one of the Mafia crime saga - 1930s, Lost Heaven, IL\r\nRe-made from the ground up, rise through the ranks of the Mafia during the Prohibition era of organized crime. After a run-in with the mob, cab driver Tommy Angelo is thrust into a deadly underworld. Initially uneasy about falling in with the Salieri crime family, Tommy soon finds that the rewards are too big to ignore.\r\n\r\nPlay a Mob Movie:\r\nLive the life of a Prohibition-era gangster and rise through the ranks of the Mafia.', 39.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X', 'Shooter, Action', 4.24, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nAdditional Notes: TBD', ''),
(452649, 'Resident Evil: Village', 'Experience survival horror like never before in the eighth major installment in the storied Resident Evil franchise - Resident Evil Village.\r\n\r\nSet a few years after the horrifying events in the critically acclaimed Resident Evil 7 biohazard, the all-new storyline begins with Ethan Winters and his wife Mia living peacefully in a new location, free from their past nightmares. Just as they are building their new life together, tragedy befalls them once again.', 39.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, iOS', 'Adventure, Action', 4.39, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 (64 bit)\r\nProcessor: AMD Ryzen 3 1200  ／ Intel Core i5-7500\r\nMemory: 8 GB RAM\r\nGraphics: AMD Radeon RX 560 with 4GB VRAM ／ NVIDIA GeForce GTX 1050 Ti with 4GB VRAM\r\nDirectX: Version 12\r\nAdditional Notes: Estimated performance (when set to Prioritize Performance): 1080p/60fps. ・Framerate might drop in graphics-intensive scenes. ・AMD Radeon RX 6700 XT or NVIDIA GeForce RTX 2060 required to support ray tracing. System requirements subject to change during game development.', NULL),
(455597, 'It Takes Two', 'Bring your favorite co-op partner and together step into the shoes of May and Cody. As the couple is going through a divorce, through unknown means their minds are transported into two dolls which their daughter, Rose, made to represent them. Now they must reluctantly find a way to get back into their bodies, a quest which takes them through the most wild, unexpected and fantastical journey imaginable.', 7.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Platformer, Adventure, Action', 4.46, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 8.1 64-bit or Windows 10 64-bit\r\nProcessor: Intel Core i3-2100T or AMD FX 6100\r\nMemory: 8 GB RAM\r\nGraphics: Nvidia GeForce GTX 660 or AMD R7 260x\r\nDirectX: Version 11\r\nNetwork: Broadband Internet connection\r\nStorage: 50 GB available space', NULL),
(479694, 'Inscryption', 'From the creator of Pony Island and The Hex comes the latest mind melting, self-destructing love letter to video games. Inscryption is an inky black card-based odyssey that blends the deckbuilding roguelike, escape-room style puzzles, and psychological horror into a blood-laced smoothie. Darker still are the secrets inscrybed upon the cards...\r\nIn Inscryption you will...', 7.99, 'PC, PlayStation 5, PlayStation 4, Nintendo Switch, macOS, Linux', 'Indie, Strategy, Adventure', 4.38, NULL, 'Minimum:\r\nOS: Windows 7\r\nProcessor: Intel Core i5-760 (4 * 2800); AMD Athlon II X4 645 AM3 (4 * 3100)\r\nMemory: 4 GB RAM\r\nGraphics: GeForce GTX 550 Ti (3072 VRAM); Radeon HD 6850 (1024 VRAM)\r\nStorage: 2 GB available space', NULL),
(479695, 'Control Ultimate Edition', '###Control Ultimate Edition\r\n\r\nA corruptive presence has invaded the Federal Bureau of Control…Only you have the power to stop it. The world is now your weapon in an epic fight to annihilate an ominous enemy through deep and unpredictable environments. Containment has failed, humanity is at stake. Will you regain control?\r\n\r\nWinner of over 80 awards, Control is a visually stunning third-person action-adventure that will keep you on the edge of your seat.', 39.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, macOS', 'Shooter, Adventure, Action', 4.31, NULL, '', ''),
(484816, 'DRAGON QUEST XI S: Echoes of an Elusive Age - Definitive Edition', 'DEFINITIVE EDITION CONTENT\r\nIncludes the critically acclaimed DRAGON QUEST XI, as well as an array of new content, features and quality of life improvements.\r\nEnjoy the massive content of the base game as well as new character-specific scenarios, which offer the possibility to learn more about some of your favourite companions.\r\nPlay as you want - switch between 3D HD or 2D 16-bit modes, original soundtrack or orchestral version of the music, and English or Japanese audio.', 39.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Adventure, RPG', 4.26, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: TBC', ''),
(484828, 'There Is No Game: Wrong Dimension', '\"There is no game: Wrong dimension\" is a Point&Click comedy adventure (and Point&Click only!) that will take you on a journey you never asked to go on, through silly and unexpected video game universes.\r\nWill you be able to play along with the \"Game\" to find your way home?\r\nWe sincerely think NOT.\r\nA Point&Click comedy adventure. You can go ahead and put your controller back up on the shelf.\r\nIncredible 3D graphics that are flat. Completely flat. And very pixelated.\r\nAlmost fully voiced.', 12.99, 'PC, Nintendo Switch, iOS, Android, macOS', 'Casual, Indie, Adventure, Puzzle', 4.34, NULL, 'Minimum:\r\nOS: Windows 7 SP1+, 10\r\nProcessor: x86, x64 architecture with SSE2 instruction set support.\r\nMemory: 4 GB RAM\r\nGraphics: Intel HD 4000\r\nDirectX: Version 10\r\nStorage: 950 MB available space\r\nSound Card: Built In', ''),
(484971, 'Chained Echoes', 'Take up your sword, channel your magic or board your Mech. Chained Echoes is a 16-bit SNES style RPG set in a fantasy world where dragons are as common as piloted mechanical suits. Follow a group of heroes as they explore a land filled to the brim with charming characters, fantastic landscapes and vicious foes. Can you bring peace to a continent where war has been waged for generations and betrayal lurks around every corner?', 9.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, macOS, Linux', 'Indie, RPG', 4.31, NULL, 'Minimum:\r\nOS: Windows 7 or newer', NULL),
(494393, 'Monster Hunter Rise', 'Set in the ninja-inspired land of Kamura Village, explore lush ecosystems and battle fearsome monsters to become the ultimate hunter. It’s been half a century since the last calamity struck, but a terrifying new monster has reared its head and threatens to plunge the land into chaos once again.\r\nHunt solo or in a party with friends to earn rewards that you can use to craft a huge variety of weapons and armor.', 39.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Action, RPG', 4.21, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 （64-bit）\r\nProcessor: Intel® Core™ i3-4130 or Core™ i5-3470 or AMD FX™-6100\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA® GeForce® GT 1030 (DDR4) or AMD Radeon™ RX 550\r\nDirectX: Version 12\r\nNetwork: Broadband Internet connection\r\nStorage: 23 GB available space\r\nAdditional Notes: 1080p/30fps when graphics settings are set to &quot;Low&quot;. System requirements subject to change during game development.', ''),
(505871, 'The Jackbox Party Pack 7', 'Five new incredible party games to bring the fun!\r\n1.	The say-anything threequel Quiplash 3 (3-8 players). Get big laughs answering the quirkiest prompts.\r\n2.	The collaborative chaos game The Devils and the Details (3-8 players). Can you survive the daily torture of human life?\r\n3.	The drawing fighting game Champ’d Up (3-8 players) . Can you take down the heavy favorite?\r\n4.	The on-the-spot speech game Talking Points (3-8 players). Just keep talking whether it makes sense or not.\r\n5.', 29.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, Android, macOS, Linux', 'Casual, Indie', 4.30, NULL, 'Minimum:\r\nOS: Windows 7+\r\nProcessor: 2.66 Ghz Core 2 Duo or Greater\r\nMemory: 4 GB RAM\r\nGraphics: GeForce 500+ / Radeon 5000+ or Greater\r\nNetwork: Broadband Internet connection\r\nStorage: 3 GB available space', ''),
(516111, 'Mass Effect: Legendary Edition', 'One person is all that stands between humanity and the greatest threat it’s ever faced. Relive the legend of Commander Shepard in the highly acclaimed Mass Effect trilogy with the Mass Effect™ Legendary Edition. Includes single-player base content and DLC from Mass Effect, Mass Effect 2, and Mass Effect 3, plus promo weapons, armors, and packs - all remastered and optimized for 4k Ultra HD.', 5.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X', 'Shooter, Adventure, Action, RPG', 4.57, NULL, NULL, NULL),
(517399, 'Overcooked! All You Can Eat', 'Overcooked!, Overcooked! 2 and all additional content are blended together and remastered in this delicious definitive edition!\r\nEnjoy hundreds of levels of cooperative cooking chaos across increasingly perilous and obscure kitchens.\r\nOvercooked! Goes Online\r\nFor the first time ever, online multiplayer has been fully integrated into Overcooked! Revisit your favourite kitchens from the first game in stunning 4K and ONLINE!\r\nA Visual Feast!', 39.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Action, Casual, Strategy, Simulation, Family, Indie', 4.21, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: WIN7-64 bit\r\nProcessor: Intel Core 2 Quad Q6600 or AMD Phenom II X3 720\r\nMemory: 4 GB RAM\r\nGraphics: NVIDIA GeForce GTS 450, 1 GB / AMD Radeon HD 5750, 1 GB\r\nDirectX: Version 11\r\nStorage: 8 GB available space', ''),
(545015, 'Disco Elysium: Final Cut', 'The final cut will be available at no extra cost to all current owners of disco elysium. original players expand their experience for free while new players can enjoy the new content from their first playthrough.', 9.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, iOS, macOS', 'Adventure, RPG', 4.66, NULL, NULL, NULL),
(558972, 'TRIANGLE STRATEGY', 'Three nations battle for control of the dwindling resources of salt and iron\r\nCommand a group of warriors as Serenoa, heir to the Kingdom of Glenbrook in a tangled plot where your decisions make all the difference. Key choices you make will bolster one of three convictions—Utility, Morality, Liberty—which together make up Serenoa’s world view and influence how the story will unfold.', 59.99, 'PC, Nintendo Switch', 'Strategy, Adventure, RPG, Simulation', 4.26, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows® 8.1 / 10 64-bit\r\nProcessor: AMD A8-7600 / Intel® Core™ i3-3210\r\nMemory: 4 GB RAM\r\nGraphics: AMD Radeon™ RX 460 / NVIDIA® GeForce® GTX 950 / Intel® Iris® Xe Graphics G7\r\nDirectX: Version 11\r\nStorage: 10 GB available space\r\nSound Card: DirectX Compatible Sound Card\r\nAdditional Notes: 60 FPS @ 1280x720', ''),
(558980, 'Neon White', 'A lightning fast first-person action platformer set beyond the pearly gates of Heaven.\r\nNeon White is a lightning fast first-person action game about exterminating demons in Heaven. You are White, an assassin handpicked from Hell to compete with other demon slayers for a chance to live permanently in Heaven. The other assassins seem familiar, though… did you know them in a past life?', 24.99, 'PC, PlayStation 5, PlayStation 4, Nintendo Switch', 'Indie, Shooter, Adventure, Action', 4.35, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10, 64-bit\r\nProcessor: Intel Core 2 Duo E6750, 2.66 GHz | AMD Phenom II X3 720, 2.8 GHz (w/ at least 3-threads)\r\nMemory: 6 GB RAM\r\nGraphics: NVIDIA GeForce GTS 450, 1 GB | AMD Radeon HD 5750, 1 GB\r\nStorage: 6 GB available space', ''),
(630676, 'Judgment', '“We’re gonna chase the truth as far as we can.”\r\n\r\nKamurocho isn’t exactly known for being the safest place in Japan, but even for this red-light district, a string of violent serial murderers has the entire city on edge. It’s up to private detective Takayuki Yagami and his partner Kaito to track down the truth using whatever tools they can.', 39.99, 'PC, PlayStation 5, PlayStation 4, Xbox Series S/X', 'Adventure, Action', 4.36, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10\r\nProcessor: Intel Core i5-3470, 3.2 GHz or AMD Ryzen 3 1200, 3.1 GHz\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA GeForce GTX 960, 2 GB or AMD Radeon RX 460, 2 GB\r\nStorage: 40 GB available space\r\nAdditional Notes: 1080p Low @ 30 FPS w/ Balanced FSR 1.0, requires a CPU which supports the AVX and SSE3 instruction set', ''),
(667350, 'Deltarune (itch)', 'Here to download the game? Download Chapter 1 & 2 for free now using the orange \"Download\" button at the bottom.\r\nChapter 1&2をダウンロードしたい方は、このページの下にあるオレンジ色の「Download」ボタンをクリックして下さい。\r\nOK, now onto the description...\r\nThe next adventure in the UNDERTALE series has appeared!\r\nFight (or spare) alongside new characters in UNDERTALE\'s parallel story, DELTARUNE...!\r\nFeaturing\r\nA massive soundtrack and story written by Toby Fox!', 24.99, 'PC, PlayStation 4, Nintendo Switch, macOS', 'Adventure, RPG', 4.35, NULL, '', ''),
(727315, 'Ratchet & Clank: Rift Apart', 'BLAST YOUR WAY THROUGH AN INTERDIMENSIONAL ADVENTURE\r\nThe intergalactic adventurers are back with a bang. Help them stop a robotic emperor intent on conquering cross-dimensional worlds, with their own universe next in the firing line.\r\n- Blast your way home with an arsenal of outrageous weaponry.\r\n- Experience the shuffle of dimensional rifts and dynamic gameplay.\r\n- Explore never-before-seen planets and alternate dimension versions of old favorites.', 23.99, 'PC, PlayStation 5', 'Adventure, Action', 4.39, NULL, 'Minimum:\r\nRequires a 64-bit processor and operating system\r\nOS: Windows 10 (version 1909 or higher)\r\nProcessor: Intel Core i3-8100 or AMD Ryzen 3 3100\r\nMemory: 8 GB RAM\r\nGraphics: NVIDIA GeForce GTX 960 or AMD Radeon RX 470\r\nStorage: 75 GB available space\r\nAdditional Notes: SSD Recommended', NULL),
(727317, 'Subnautica 2', 'this is test remove it later', 28.78, '', '', 0.00, 11, NULL, NULL),
(727318, 'ARK: Survival Evolved', 'Stranded on the shores of a mysterious island, you must learn to survive. Use your cunning to kill or tame the primeval creatures roaming the land, and encounter other players to survive, dominate... and escape!', 8.50, 'PC', 'action', 0.00, 8, NULL, NULL),
(727319, 'ARK: Survival Evolved', 'Stranded on the shores of a mysterious island, you must learn to survive. Use your cunning to kill or tame the primeval creatures roaming the land, and encounter other players to survive, dominate... and escape!', 0.96, 'PC', 'action', 0.00, 11, NULL, NULL),
(727322, 'Dark Souls III: The Ringed City', 'Fear not, the dark, ashen one.  <br/>\r\nThe Ringed City is the final DLC pack for Dark Souls III – an award-winning, genre-defining Golden Joystick Awards 2016 Game of the year RPG.  Journey to the world’s end to search for the Ringed City and encounter new lands, new bosses, new enemies with new armor, magic and items.  Experience the epic final chapter of a dark world that could only be created by the mind of Hidetaka Miyazaki.       <br/>\r\nA New World.  One Last Journey.', 9.60, 'PC, Xbox One, PlayStation 4', 'Action, RPG', 0.00, 11, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Game_Images`
--

CREATE TABLE `Game_Images` (
  `id` int NOT NULL,
  `game_id` int NOT NULL,
  `is_cover` tinyint(1) DEFAULT '0',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Game_Images`
--

INSERT INTO `Game_Images` (`id`, `game_id`, `is_cover`, `filename`) VALUES
(1, 43252, 1, '/images/The Witcher 3: Wild Hunt – Blood and Wine/cover.jpg'),
(2, 43050, 1, '/images/The Witcher 3: Wild Hunt – Hearts of Stone/cover.jpg'),
(3, 339958, 1, '/images/Persona 5 Royal/cover.jpg'),
(4, 545015, 1, '/images/Disco Elysium: Final Cut/cover.jpg'),
(5, 3328, 1, '/images/The Witcher 3: Wild Hunt/cover.jpg'),
(6, 385406, 1, '/images/Dark Souls III: The Ringed City/cover.jpg'),
(7, 28, 1, '/images/red dead redemption 2/cover.jpg'),
(8, 4200, 1, '/images/Portal 2/cover.jpg'),
(9, 516111, 1, '/images/Mass Effect: Legendary Edition/cover.jpg'),
(10, 58175, 1, '/images/God of War (2018)/cover.jpg'),
(12, 59199, 1, '/images/Divinity: Original Sin 2 - Definitive Edition/cover.jpg'),
(13, 254545, 1, '/images/Cuphead: The Delicious Last Course/cover.jpg'),
(14, 58813, 1, '/images/Resident Evil 2/cover.jpg'),
(15, 4265, 1, '/images/Persona 3 Portable/cover.jpg'),
(16, 13536, 1, '/images/Portal/cover.jpg'),
(17, 19457, 1, '/images/Disciples II: Rise of the Elves/cover.jpg'),
(18, 19445, 1, '/images/Disciples II Gallean\'s Return/cover.jpg'),
(19, 13537, 1, '/images/Half-Life 2/cover.jpg'),
(20, 58388, 1, '/images/ZONE OF THE ENDERS: The 2nd Runner - M∀RS/cover.jpg'),
(21, 455597, 1, '/images/It Takes Two/cover.jpg'),
(22, 3498, 1, '/images/Grand Theft Auto V/cover.jpg'),
(23, 51610, 1, '/images/Dark Souls: Remastered/cover.jpg'),
(24, 43737, 1, '/images/Dark Souls III: Ashes of Ariandel/cover.jpg'),
(25, 366889, 1, '/images/Monster Hunter World: Iceborne/cover.jpg'),
(27, 58890, 1, '/images/Need For Speed: Most Wanted/cover.jpg'),
(28, 622, 1, '/images/XCOM: Enemy Within/cover.jpg'),
(29, 307137, 1, '/images/Live A Live/cover.jpg'),
(30, 12447, 1, '/images/The Elder Scrolls V: Skyrim Special Edition/cover.jpg'),
(31, 58134, 1, '/images/Marvel\'s Spider-Man/cover.jpg'),
(32, 19635, 1, '/images/Fable: The Lost Chapters/cover.jpg'),
(33, 21974, 1, '/images/The Legend of Heroes: Trails in the Sky the 3rd/cover.jpg'),
(34, 11498, 1, '/images/The Legend of Heroes: Trails in the Sky SC/cover.jpg'),
(35, 4186, 1, '/images/Persona 4 Golden/cover.jpg'),
(36, 366885, 1, '/images/Dragon Age: Origins - Ultimate Edition/cover.jpg'),
(37, 324997, 1, '/images/Baldur\'s Gate III/cover.jpg'),
(38, 28568, 1, '/images/assassins-creed-ii/cover.jpg'),
(39, 42303, 1, '/images/BioShock Infinite: Burial at Sea - Episode Two/cover.jpg'),
(40, 5563, 1, '/images/Fallout: New Vegas/cover.jpg'),
(41, 19654, 1, '/images/Samurai Gunn/cover.jpg'),
(42, 5679, 1, '/images/The Elder Scrolls V: Skyrim/cover.jpg'),
(43, 274755, 1, '/images/Hades/cover.jpg'),
(44, 4544, 1, '/images/Red Dead Redemption/cover.jpg'),
(45, 28199, 1, '/images/Ori and the Will of the Wisps/cover.jpg'),
(46, 17959, 1, '/images/Ori and the Blind Forest: Definitive Edition/cover.jpg'),
(47, 428664, 1, '/images/A Space for the Unbound/cover.jpg'),
(48, 29153, 1, '/images/Max Payne 2: The Fall of Max Payne/cover.jpg'),
(50, 58550, 1, '/images/Ghost of Tsushima/cover.jpeg'),
(51, 52201, 1, '/images/Yakuza 6: The Song of Life/cover.jpg'),
(52, 9767, 1, '/images/Hollow Knight/cover.jpg'),
(53, 4439, 1, '/images/Mass Effect 3/cover.jpg'),
(54, 591, 1, '/images/Monument Valley/cover.jpg'),
(56, 56184, 1, '/images/Resident Evil 4 (2005)/cover.jpg'),
(57, 10389, 1, '/images/Gothic II: Gold Edition/cover.jpg'),
(58, 2551, 1, '/images/Dark Souls III/cover.jpg'),
(59, 45958, 1, '/images/XCOM 2: War of the Chosen/cover.jpg'),
(60, 4166, 1, '/images/Mass Effect/cover.jpg'),
(61, 452649, 1, '/images/Resident Evil: Village/cover.jpg'),
(63, 4535, 1, '/images/Call of Duty 4: Modern Warfare/cover.jpg'),
(64, 727315, 1, '/images/Ratchet & Clank: Rift Apart/cover.jpg'),
(65, 52884, 1, '/images/DOOM/cover.jpg'),
(66, 654, 1, '/images/Stardew Valley/cover.jpg'),
(67, 13856, 1, '/images/Katana ZERO/cover.jpg'),
(68, 1682, 1, '/images/The Wolf Among Us/cover.jpg'),
(69, 13925, 1, '/images/Prince of Persia: Warrior Within/cover.jpg'),
(70, 274757, 1, '/images/Sayonara Wild Hearts/cover.jpg'),
(71, 50839, 1, '/images/Baba Is You/cover.jpg'),
(72, 4570, 1, '/images/Dead Space (2008)/cover.jpg'),
(73, 326243, 1, '/images/Elden Ring/cover.jpg'),
(74, 484971, 1, '/images/Chained Echoes/cover.jpg'),
(75, 10073, 1, '/images/Divinity: Original Sin 2/cover.jpg'),
(76, 479694, 1, '/images/Inscryption/cover.jpg'),
(77, 50734, 1, '/images/Sekiro: Shadows Die Twice/cover.jpg'),
(78, 29642, 1, '/images/Silent Hill 2 (2001)/cover.jpg'),
(79, 18080, 1, '/images/Half-Life/cover.jpg'),
(80, 4062, 1, '/images/BioShock Infinite/cover.jpg'),
(82, 115, 1, '/images/Zero Escape: The Nonary Games/cover.jpg'),
(83, 20709, 1, '/images/Tom Clancy\'s Splinter Cell Chaos Theory/cover.jpg'),
(84, 422859, 1, '/images/NieR Replicant/cover.jpg'),
(86, 1358, 1, '/images/Papers, Please/cover.jpg'),
(87, 23741, 1, '/images/Monument Valley 2/cover.jpg'),
(88, 17572, 1, '/images/Batman: Arkham Asylum Game of the Year Edition/cover.jpg'),
(89, 1450, 1, '/images/INSIDE/cover.jpg'),
(90, 4550, 1, '/images/Dead Space 2/cover.jpg'),
(92, 4248, 1, '/images/Dishonored/cover.jpg'),
(93, 2454, 1, '/images/DOOM (2016)/cover.jpg'),
(94, 11971, 1, '/images/Space Rangers HD: A War Apart/cover.jpg'),
(95, 28154, 1, '/images/Cuphead/cover.jpg'),
(96, 366881, 1, '/images/Little Nightmares II/cover.jpg'),
(97, 262382, 1, '/images/Disco Elysium/cover.jpg'),
(98, 10141, 1, '/images/NieR:Automata/cover.jpg'),
(99, 257192, 1, '/images/Psychonauts 2/cover.jpg'),
(100, 13820, 1, '/images/The Elder Scrolls III: Morrowind/cover.jpg'),
(103, 727318, 1, 'images/games/game_727318_1778683561.jpg'),
(104, 727319, 1, 'images/games/game_727318_1778683561.jpg'),
(106, 727322, 1, '/images/Dark Souls III: The Ringed City/cover.jpg'),
(107, 727317, 1, 'images/games/game_727317_1778844836.jpg'),
(108, 1458, 1, '/images/Bug Princess/cover.jpg'),
(109, 28623, 1, '/images/Batman: Arkham City/cover.jpg'),
(110, 14935, 1, '/images/Total War: Shogun 2 - Fall of the Samurai/cover.jpg'),
(112, 42, 1, '/images/What Remains of Edith Finch/cover.jpg'),
(113, 51431, 1, '/images/The Room 4: Old Sins/cover.jpg'),
(114, 13404, 1, '/images/VA-11 Hall-A: Cyberpunk Bartender Action/cover.jpg'),
(115, 10579, 1, '/images/Planescape: Torment: Enhanced Edition/cover.jpg'),
(116, 274758, 1, '/images/The Stanley Parable: Ultra Deluxe/cover.jpg'),
(117, 15002, 1, '/images/The Stanley Parable/cover.jpg'),
(118, 58777, 1, '/images/DOOM Eternal/cover.jpg'),
(119, 22121, 1, '/images/Celeste/cover.jpg'),
(120, 259801, 1, '/images/Final Fantasy VII/cover.jpg'),
(121, 1299, 1, '/images/Mount & Blade: Warband/cover.jpg'),
(122, 28121, 1, '/images/Slay the Spire/cover.jpg'),
(123, 630676, 1, '/images/Judgment/cover.jpg'),
(124, 32029, 1, '/images/Stronghold/cover.jpg'),
(125, 1758, 1, '/images/Valiant Hearts: The Great War/cover.jpg'),
(126, 58764, 1, '/images/Outer Wilds/cover.jpg'),
(127, 12130, 1, '/images/RimWorld/cover.jpg'),
(128, 18726, 1, '/images/Gothic/cover.jpg'),
(129, 10926, 1, '/images/Factorio/cover.jpg'),
(130, 59346, 1, '/images/Desperados III/cover.jpg'),
(131, 667350, 1, '/images/Deltarune (itch)/cover.jpg'),
(132, 369157, 1, '/images/Yakuza: Like a Dragon/cover.jpg'),
(133, 392019, 1, '/images/Half-Life: Alyx/cover.jpg'),
(134, 18628, 1, '/images/Arcanum: Of Steamworks and Magick Obscura/cover.jpg'),
(135, 304247, 1, '/images/A Short Hike/cover.jpg'),
(136, 13627, 1, '/images/Undertale/cover.jpg'),
(137, 15859, 1, '/images/Star Wars: Knights of the Old Republic/cover.jpg'),
(138, 558980, 1, '/images/Neon White/cover.jpg'),
(139, 29177, 1, '/images/Detroit: Become Human/cover.jpg'),
(140, 2188, 1, '/images/The Room Three/cover.jpeg'),
(141, 11970, 1, '/images/Star Wars Jedi Knight: Jedi Academy/cover.jpg'),
(142, 9981, 1, '/images/Total War: WARHAMMER II/cover.jpg'),
(143, 3332, 1, '/images/FINAL FANTASY X-X-2 HD Remaster/cover.jpg'),
(144, 484828, 1, '/images/There Is No Game: Wrong Dimension/cover.jpg'),
(145, 14927, 1, '/images/Medieval II: Total War Kingdoms/cover.jpg'),
(146, 46508, 1, '/images/Return Of The Obra Dinn/cover.jpg'),
(147, 58209, 1, '/images/Dwarf Fortress/cover.jpg'),
(148, 19628, 1, '/images/FlatOut 2/cover.jpg'),
(149, 19309, 1, '/images/Plants vs. Zombies GOTY Edition/cover.jpg'),
(150, 15642, 1, '/images/The Elder Scrolls IV: Oblivion Game of the Year Edition/cover.jpg'),
(151, 250, 1, '/images/The Binding of Isaac: Rebirth/cover.jpg'),
(152, 406445, 1, '/images/Wide Ocean Big Jacket/cover.jpg'),
(153, 384567, 1, '/images/Crusader Kings III/cover.jpg'),
(155, 923, 1, '/images/Titanfall 2/cover.jpg'),
(156, 304922, 1, '/images/Astalon: Tears of the Earth/cover.jpg'),
(157, 3364, 1, '/images/Shovel Knight: Treasure Trove/cover.jpg'),
(158, 479695, 1, '/images/Control Ultimate Edition/cover.jpg'),
(159, 5916, 1, '/images/The Room Two/cover.jpg'),
(160, 19380, 1, '/images/Dark Messiah of Might and Magic/cover.jpg'),
(161, 19406, 1, '/images/Trackmania United Forever Star Edition/cover.jpg'),
(162, 4101, 1, '/images/Bayonetta/cover.jpg'),
(163, 13566, 1, '/images/Into the Breach/cover.jpg'),
(164, 505871, 1, '/images/The Jackbox Party Pack 7/cover.jpg'),
(165, 61206, 1, '/images/Yuppie Psycho/cover.jpg'),
(166, 13858, 1, '/images/Fran Bow/cover.jpg'),
(167, 56088, 1, '/images/killer7/cover.jpg'),
(168, 455, 1, '/images/Threes!/cover.jpeg'),
(169, 3363, 1, '/images/Shovel Knight/cover.jpg'),
(170, 427679, 1, '/images/Atelier Escha & Logy: Alchemists of the Dusk Sky DX/cover.jpg'),
(171, 14491, 1, '/images/Downfall/cover.jpg'),
(172, 326253, 1, '/images/Age of Empires II: Definitive Edition/cover.jpg'),
(174, 13194, 1, '/images/To the Moon/cover.jpg'),
(175, 3408, 1, '/images/Hotline Miami 2: Wrong Number/cover.jpg'),
(176, 39, 1, '/images/Prey/cover.jpg'),
(177, 10064, 1, '/images/Assassin’s Creed Brotherhood/cover.jpg'),
(178, 5115, 1, '/images/FINAL FANTASY VIII/cover.jpg'),
(179, 19056, 1, '/images/S.T.A.L.K.E.R.: Call of Pripyat/cover.jpg'),
(180, 329552, 1, '/images/Resident evil 7 Banned Footage Vol.2/cover.jpg'),
(181, 9687, 1, '/images/CrossCode/cover.jpg'),
(182, 4527, 1, '/images/Call of Duty: Modern Warfare 2/cover.jpg'),
(183, 19452, 1, '/images/F.E.A.R./cover.jpg'),
(184, 24442, 1, '/images/No More Heroes 2: Desperate Struggle/cover.jpg'),
(185, 11874, 1, '/images/Nancy Drew: Legend of the Crystal Skull/cover.jpg'),
(186, 54491, 1, '/images/Quake/cover.jpg'),
(187, 302836, 1, '/images/AI: The Somnium Files/cover.jpg'),
(188, 297208, 1, '/images/NieR:Automata Game of the YoRHa Edition/cover.jpg'),
(189, 12965, 1, '/images/LISA/cover.jpg'),
(190, 484816, 1, '/images/DRAGON QUEST XI S: Echoes of an Elusive Age - Definitive Edition/cover.jpg'),
(191, 558972, 1, '/images/TRIANGLE STRATEGY/cover.jpg'),
(193, 376934, 1, '/images/Pistol Whip/cover.jpg'),
(194, 265332, 1, '/images/Nightshade (2016)/cover.jpg'),
(195, 19441, 1, '/images/Silent Hunter III/cover.jpg'),
(196, 304187, 1, '/images/Five Nights at Freddy’s: Help Wanted/cover.jpg'),
(197, 58755, 1, '/images/Devil May Cry 5/cover.jpg'),
(198, 111, 1, '/images/Forgotten Memories: Remastered Edition/cover.jpg'),
(199, 320, 1, '/images/Night in the Woods/cover.jpg'),
(200, 16359, 1, '/images/Divinity: Original Sin - Enhanced Edition/cover.jpg'),
(201, 857, 1, '/images/Halo: The Master Chief Collection/cover.jpg'),
(202, 12074, 1, '/images/Monkey Island 2 Special Edition: LeChuck’s Revenge/cover.jpg'),
(203, 409575, 1, '/images/Pathfinder: Wrath of the Righteous/cover.jpg'),
(204, 52383, 1, '/images/The Banner Saga 3/cover.jpg'),
(205, 442854, 1, '/images/Mafia: Definitive Edition/cover.jpg'),
(206, 374507, 1, '/images/Guilty Gear -Strive/cover.jpg'),
(207, 13833, 1, '/images/Stronghold Crusader HD/cover.jpg'),
(208, 3287, 1, '/images/Batman: Arkham Knight/cover.jpg'),
(209, 10992, 1, '/images/OneShot/cover.jpg'),
(210, 41494, 1, '/images/cyberpunk 2077/cover.jpg'),
(211, 59314, 1, '/images/Anno 1800/cover.jpg'),
(212, 2020, 1, '/images/The Room/cover.jpg'),
(214, 33, 1, '/images/Final Fantasy XII: The Zodiac Age/cover.jpeg'),
(215, 10037, 1, '/images/Europa Universalis IV/cover.jpg'),
(216, 11726, 1, '/images/Dead Cells/cover.jpg'),
(217, 17620, 1, '/images/Heroes of Might & Magic III - HD Edition/cover.jpeg'),
(218, 11868, 1, '/images/Nancy Drew: Last Train to Blue Moon Canyon/cover.jpg'),
(219, 18905, 1, '/images/Manifold Garden/cover.jpg'),
(220, 17604, 1, '/images/Return to Castle Wolfenstein/cover.jpg'),
(221, 4234, 1, '/images/Devil May Cry HD Collection/cover.jpg'),
(223, 13909, 1, '/images/Prince of Persia: The Sands of Time/cover.jpg'),
(224, 10069, 1, '/images/Mount & Blade II: Bannerlord/cover.jpg'),
(225, 44295, 1, '/images/West of Loathing/cover.jpeg'),
(226, 3727, 1, '/images/FINAL FANTASY XIV: A Realm Reborn/cover.jpg'),
(227, 517399, 1, '/images/Overcooked! All You Can Eat/cover.jpg'),
(228, 494393, 1, '/images/Monster Hunter Rise/cover.jpg'),
(229, 14331, 1, '/images/Call of Duty 2/cover.jpg'),
(230, 18743, 1, '/images/Desperados: Wanted Dead or Alive/cover.jpg'),
(231, 49428, 1, '/images/The Red Strings Club/cover.jpg'),
(232, 25373, 1, '/images/No More Heroes/cover.jpg'),
(233, 411611, 1, '/images/Yakuza 5 Remastered/cover.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `Game_Keys`
--

CREATE TABLE `Game_Keys` (
  `id` int NOT NULL,
  `game_id` int NOT NULL,
  `key_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sold` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Game_Keys`
--

INSERT INTO `Game_Keys` (`id`, `game_id`, `key_code`, `is_sold`) VALUES
(1, 28, 'RDR2A-B3C4D-E5F6G', 0),
(2, 28, 'RDR2X-Y7Z8W-V9U0T', 1),
(3, 28, 'RDR2P-Q1R2S-T3U4V', 0),
(6, 654, 'FARM1-S2T3U-V4W5X', 1),
(7, 654, 'FARM2-Y6Z7A-B8C9D', 0),
(8, 654, 'FARM3-E0F1G-H2I3J', 0),
(9, 3498, 'GTAV1-K4L5M-N6O7P', 0),
(10, 3498, 'GTAV2-Q8R9S-T0U1V', 1),
(11, 3328, 'WILDH-W2X3Y-Z4A5B', 0),
(12, 3328, 'WILDH-C6D7E-F8G9H', 0),
(13, 3328, 'WILDH-I0J1K-L2M3N', 1),
(14, 4200, 'PORT2-O4P5Q-R6S7T', 1),
(15, 4200, 'PORT2-U8V9W-X0Y1Z', 0),
(16, 9767, 'HOLLW-A2B3C-D4E5F', 1),
(17, 9767, 'HOLLW-G6H7I-J8K9L', 0),
(18, 274755, 'HADES-M0N1O-P2Q3R', 0),
(19, 274755, 'HADES-S4T5U-V6W7X', 0),
(20, 326243, 'ELDEN-Y8Z9A-B0C1D', 1),
(21, 326243, 'ELDEN-E2F3G-H4I5J', 0),
(22, 326243, 'ELDEN-K6L7M-N8O9P', 0),
(23, 115, 'KEY-115-A1B2C', 0),
(24, 591, 'KEY-591-D3E4F', 1),
(25, 622, 'KEY-622-G5H6I', 1),
(26, 1358, 'KEY-1358-J7K8L', 0),
(27, 1450, 'KEY-1450-M9N0O', 0),
(30, 1682, 'KEY-1682-V5W6X', 0),
(31, 2454, 'KEY-2454-Y7Z8A', 0),
(32, 2551, 'KEY-2551-B9C0D', 0),
(34, 4062, 'KEY-4062-H3I4J', 0),
(35, 4166, 'KEY-4166-K5L6M', 0),
(36, 4186, 'KEY-4186-N7O8P', 1),
(37, 4248, 'KEY-4248-Q9R0S', 0),
(38, 4265, 'KEY-4265-T1U2V', 0),
(39, 4439, 'KEY-4439-W3X4Y', 0),
(40, 4535, 'KEY-4535-Z5A6B', 0),
(41, 4544, 'KEY-4544-C7D8E', 0),
(42, 4550, 'KEY-4550-F9G0H', 0),
(43, 4570, 'KEY-4570-I1J2K', 0),
(44, 5563, 'KEY-5563-L3M4N', 1),
(45, 5679, 'KEY-5679-O5P6Q', 0),
(46, 10073, 'KEY-10073-R7S8T', 0),
(47, 10141, 'KEY-10141-U9V0W', 1),
(48, 10389, 'KEY-10389-X1Y2Z', 0),
(49, 11498, 'KEY-11498-A3B4C', 0),
(50, 11971, 'KEY-11971-D5E6F', 0),
(51, 12447, 'KEY-12447-G7H8I', 0),
(52, 13536, 'KEY-13536-J9K0L', 0),
(53, 13537, 'KEY-13537-M1N2O', 1),
(54, 13820, 'KEY-13820-P3Q4R', 0),
(55, 13856, 'KEY-13856-S5T6U', 0),
(56, 13925, 'KEY-13925-V7W8X', 0),
(58, 17572, 'KEY-17572-B1C2D', 0),
(60, 17959, 'KEY-17959-H5I6J', 0),
(61, 18080, 'KEY-18080-K7L8M', 0),
(62, 19445, 'KEY-19445-N9O0P', 0),
(63, 19457, 'KEY-19457-Q1R2S', 1),
(64, 19635, 'KEY-19635-T3U4V', 0),
(65, 19654, 'KEY-19654-W5X6Y', 0),
(66, 20709, 'KEY-20709-Z7A8B', 0),
(67, 21974, 'KEY-21974-C9D0E', 0),
(68, 23741, 'KEY-23741-F1G2H', 0),
(69, 28154, 'KEY-28154-I3J4K', 1),
(70, 28199, 'KEY-28199-L5M6N', 0),
(71, 28568, 'KEY-28568-O7P8Q', 0),
(73, 29153, 'KEY-29153-U1V2W', 0),
(74, 29642, 'KEY-29642-X3Y4Z', 1),
(75, 42303, 'KEY-42303-A5B6C', 0),
(76, 43050, 'KEY-43050-D7E8F', 0),
(77, 43252, 'KEY-43252-G9H0I', 0),
(78, 43737, 'KEY-43737-J1K2L', 0),
(79, 45958, 'KEY-45958-M3N4O', 0),
(80, 50734, 'KEY-50734-P5Q6R', 0),
(81, 50839, 'KEY-50839-S7T8U', 0),
(82, 51610, 'KEY-51610-V9W0X', 0),
(83, 52201, 'KEY-52201-Y1Z2A', 0),
(84, 52884, 'KEY-52884-B3C4D', 0),
(85, 56184, 'KEY-56184-E5F6G', 1),
(86, 58134, 'KEY-58134-H7I8J', 0),
(87, 58175, 'KEY-58175-K9L0M', 0),
(88, 58388, 'KEY-58388-N1O2P', 0),
(89, 58550, 'KEY-58550-Q3R4S', 0),
(90, 58813, 'KEY-58813-T5U6V', 0),
(91, 58890, 'KEY-58890-W7X8Y', 0),
(92, 59199, 'KEY-59199-Z9A0B', 0),
(93, 254545, 'KEY-254545-C1D2E', 1),
(94, 257192, 'KEY-257192-F3G4H', 0),
(95, 262382, 'KEY-262382-I5J6K', 0),
(96, 274757, 'KEY-274757-L7M8N', 0),
(97, 307137, 'KEY-307137-O9P0Q', 0),
(98, 324997, 'KEY-324997-R1S2T', 1),
(99, 339958, 'KEY-339958-U3V4W', 0),
(100, 366881, 'KEY-366881-X5Y6Z', 0),
(101, 366885, 'KEY-366885-A7B8C', 0),
(102, 366889, 'KEY-366889-D9E0F', 0),
(103, 385406, 'KEY-385406-G1H2I', 0),
(104, 422859, 'KEY-422859-J3K4L', 1),
(105, 428664, 'KEY-428664-M5N6O', 0),
(106, 452649, 'KEY-452649-P7Q8R', 0),
(107, 455597, 'KEY-455597-S9T0U', 1),
(108, 479694, 'KEY-479694-V1W2X', 0),
(109, 484971, 'KEY-484971-Y3Z4A', 0),
(110, 516111, 'KEY-516111-B5C6D', 0),
(111, 545015, 'KEY-545015-E7F8G', 0),
(113, 727315, 'KEY-727315-K1L2M', 1),
(114, 591, 'sdfhsdfs-sdfsdfh-awehas', 1),
(115, 3328, 'xxxxxxxxxxxxxxxxx', 0),
(162, 19457, 'KEY-4186-N7O3P', 0),
(164, 19457, 'KEY-4186-N5O8P', 0),
(197, 428664, 'KEY-4186-N73O8P', 0),
(199, 428664, 'KEY-4186-N74O8P', 0),
(200, 428664, 'KEY-4186-N7O48P', 0),
(204, 428664, 'KEY-24186-N7O8P', 0),
(205, 428664, 'KEY-44186-N7O8P', 0),
(206, 428664, 'KEY-54186-N7O8P', 0),
(207, 326243, 'KEY-45186-N738P', 0),
(209, 326243, 'KEY-4186-N37O8P', 0),
(210, 326243, 'KEY-41586-N7O8P', 0),
(212, 727317, 'test_key', 1),
(213, 727318, 'key-xx-xxx-xxx-xx', 1),
(214, 727319, 'sdfsdf', 1),
(225, 727319, 'sdfsdfsdfd', 1),
(226, 727318, '6011624436146', 1),
(227, 727318, '6011624362146', 0),
(228, 727318, '6011624336146', 0),
(229, 727318, '6016162436145', 0),
(230, 727318, '6011624361462', 0),
(231, 727318, '6011624361461', 0),
(232, 727318, '6011624361465', 0),
(233, 727318, '6011624361468', 0),
(234, 727318, '6101162436146', 0),
(235, 727318, '6011622436146', 0),
(236, 727318, '6011632436146', 0),
(237, 727322, 'sdfe', 1),
(238, 727319, 'sdfsddaasda', 0),
(239, 727319, 'help-me', 0),
(240, 727319, '10/10', 0),
(242, 28568, 'KEY-28568-O27P8Q', 0),
(243, 28568, 'KEY-284568-O7P8Q', 0),
(244, 28568, 'KEY-28568-O7P38Q', 0),
(245, 28568, 'KEY-28568-O7P48Q', 0),
(246, 28568, 'KEY-283568-O7P8Q', 0),
(247, 28568, 'KEY-268568-O7P8Q', 0),
(449, 1458, 'KEY-1458-SYNC', 0),
(450, 28623, 'KEY-28623-SYNC', 0),
(451, 14935, 'KEY-14935-SYNC', 0),
(453, 42, 'KEY-42-SYNC', 0),
(454, 51431, 'KEY-51431-SYNC', 0),
(455, 13404, 'KEY-13404-SYNC', 0),
(456, 10579, 'KEY-10579-SYNC', 0),
(457, 274758, 'KEY-274758-SYNC', 0),
(458, 15002, 'KEY-15002-SYNC', 0),
(459, 58777, 'KEY-58777-SYNC', 0),
(460, 22121, 'KEY-22121-SYNC', 0),
(461, 259801, 'KEY-259801-SYNC', 0),
(462, 1299, 'KEY-1299-SYNC', 0),
(463, 28121, 'KEY-28121-SYNC', 0),
(464, 630676, 'KEY-630676-SYNC', 0),
(465, 32029, 'KEY-32029-SYNC', 0),
(466, 1758, 'KEY-1758-SYNC', 0),
(467, 58764, 'KEY-58764-SYNC', 0),
(468, 12130, 'KEY-12130-SYNC', 0),
(469, 18726, 'KEY-18726-SYNC', 0),
(470, 10926, 'KEY-10926-SYNC', 0),
(471, 59346, 'KEY-59346-SYNC', 0),
(472, 667350, 'KEY-667350-SYNC', 0),
(473, 369157, 'KEY-369157-SYNC', 0),
(474, 392019, 'KEY-392019-SYNC', 0),
(475, 18628, 'KEY-18628-SYNC', 0),
(476, 304247, 'KEY-304247-SYNC', 0),
(477, 13627, 'KEY-13627-SYNC', 0),
(478, 15859, 'KEY-15859-SYNC', 0),
(479, 558980, 'KEY-558980-SYNC', 0),
(480, 29177, 'KEY-29177-SYNC', 0),
(481, 2188, 'KEY-2188-SYNC', 0),
(482, 11970, 'KEY-11970-SYNC', 0),
(483, 9981, 'KEY-9981-SYNC', 0),
(484, 3332, 'KEY-3332-SYNC', 0),
(485, 484828, 'KEY-484828-SYNC', 0),
(486, 14927, 'KEY-14927-SYNC', 0),
(487, 46508, 'KEY-46508-SYNC', 0),
(488, 58209, 'KEY-58209-SYNC', 0),
(489, 19628, 'KEY-19628-SYNC', 0),
(490, 19309, 'KEY-19309-SYNC', 0),
(491, 15642, 'KEY-15642-SYNC', 0),
(492, 250, 'KEY-250-SYNC', 0),
(493, 406445, 'KEY-406445-SYNC', 0),
(494, 384567, 'KEY-384567-SYNC', 0),
(496, 923, 'KEY-923-SYNC', 0),
(497, 304922, 'KEY-304922-SYNC', 0),
(498, 3364, 'KEY-3364-SYNC', 0),
(499, 479695, 'KEY-479695-SYNC', 0),
(500, 5916, 'KEY-5916-SYNC', 0),
(501, 19380, 'KEY-19380-SYNC', 0),
(502, 19406, 'KEY-19406-SYNC', 0),
(503, 4101, 'KEY-4101-SYNC', 0),
(504, 13566, 'KEY-13566-SYNC', 0),
(505, 505871, 'KEY-505871-SYNC', 0),
(506, 61206, 'KEY-61206-SYNC', 0),
(507, 13858, 'KEY-13858-SYNC', 0),
(508, 56088, 'KEY-56088-SYNC', 0),
(509, 455, 'KEY-455-SYNC', 0),
(510, 3363, 'KEY-3363-SYNC', 0),
(511, 427679, 'KEY-427679-SYNC', 0),
(512, 14491, 'KEY-14491-SYNC', 0),
(513, 326253, 'KEY-326253-SYNC', 0),
(515, 13194, 'KEY-13194-SYNC', 0),
(516, 3408, 'KEY-3408-SYNC', 0),
(517, 39, 'KEY-39-SYNC', 0),
(518, 10064, 'KEY-10064-SYNC', 0),
(519, 5115, 'KEY-5115-SYNC', 0),
(520, 19056, 'KEY-19056-SYNC', 0),
(521, 329552, 'KEY-329552-SYNC', 0),
(522, 9687, 'KEY-9687-SYNC', 0),
(523, 4527, 'KEY-4527-SYNC', 0),
(524, 19452, 'KEY-19452-SYNC', 0),
(525, 24442, 'KEY-24442-SYNC', 0),
(526, 11874, 'KEY-11874-SYNC', 0),
(527, 54491, 'KEY-54491-SYNC', 0),
(528, 302836, 'KEY-302836-SYNC', 0),
(529, 297208, 'KEY-297208-SYNC', 0),
(530, 12965, 'KEY-12965-SYNC', 0),
(531, 484816, 'KEY-484816-SYNC', 0),
(532, 558972, 'KEY-558972-SYNC', 0),
(534, 376934, 'KEY-376934-SYNC', 0),
(535, 265332, 'KEY-265332-SYNC', 0),
(536, 19441, 'KEY-19441-SYNC', 0),
(537, 304187, 'KEY-304187-SYNC', 0),
(538, 58755, 'KEY-58755-SYNC', 0),
(539, 111, 'KEY-111-SYNC', 0),
(540, 320, 'KEY-320-SYNC', 0),
(541, 16359, 'KEY-16359-SYNC', 0),
(542, 857, 'KEY-857-SYNC', 0),
(543, 12074, 'KEY-12074-SYNC', 0),
(544, 409575, 'KEY-409575-SYNC', 0),
(545, 52383, 'KEY-52383-SYNC', 0),
(546, 442854, 'KEY-442854-SYNC', 0),
(547, 374507, 'KEY-374507-SYNC', 0),
(548, 13833, 'KEY-13833-SYNC', 0),
(549, 3287, 'KEY-3287-SYNC', 0),
(550, 10992, 'KEY-10992-SYNC', 0),
(551, 41494, 'KEY-41494-SYNC', 0),
(552, 59314, 'KEY-59314-SYNC', 0),
(553, 2020, 'KEY-2020-SYNC', 0),
(555, 33, 'KEY-33-SYNC', 0),
(556, 10037, 'KEY-10037-SYNC', 0),
(557, 11726, 'KEY-11726-SYNC', 0),
(558, 17620, 'KEY-17620-SYNC', 0),
(559, 11868, 'KEY-11868-SYNC', 0),
(560, 18905, 'KEY-18905-SYNC', 0),
(561, 17604, 'KEY-17604-SYNC', 0),
(562, 4234, 'KEY-4234-SYNC', 0),
(564, 13909, 'KEY-13909-SYNC', 0),
(565, 10069, 'KEY-10069-SYNC', 0),
(566, 44295, 'KEY-44295-SYNC', 0),
(567, 3727, 'KEY-3727-SYNC', 0),
(568, 517399, 'KEY-517399-SYNC', 0),
(569, 494393, 'KEY-494393-SYNC', 0),
(570, 14331, 'KEY-14331-SYNC', 0),
(571, 18743, 'KEY-18743-SYNC', 0),
(572, 49428, 'KEY-49428-SYNC', 0),
(573, 25373, 'KEY-25373-SYNC', 0),
(574, 411611, 'KEY-411611-SYNC', 0),
(575, 727322, 'sdfasfsdasdafafafafaf', 0),
(576, 302836, 'KEY-3226253-SYNC', 0),
(577, 302836, 'KEY-3265253-SYNC', 0),
(578, 302836, 'KEY-3726253-SYNC', 0),
(579, 302836, 'KEY-3286253-SYNC', 0),
(580, 479695, 'KEY-3262563-SYNC', 0),
(582, 479695, 'KEY-326253-SuNC', 0),
(584, 41494, 'KEY-3262543-SYNC', 0),
(585, 41494, 'KEY-3262553-SYNC', 0),
(586, 41494, 'KEY-3326253-SYNC', 0),
(587, 41494, 'KEY-3262573-SYNC', 0),
(588, 41494, 'KEY-126253-SYNC', 0),
(589, 41494, 'KEY-3246253-SYNC', 0),
(592, 2551, 'KEY-326251-SYNC', 0),
(593, 2551, 'KEY-326252-SYNC', 0),
(594, 2551, 'KEY-326255-SYNC', 0),
(596, 254545, 'KEY-32629653-SYNC', 0),
(597, 254545, 'KEY-3269253-SYNC', 0),
(598, 254545, 'KEY-32692653-SYNC', 0),
(600, 254545, 'KEY-3268253-SYNC', 0),
(601, 10064, 'mpr64vvf1-6u', 0),
(602, 10064, 'mpr3vvf1-76u', 0),
(603, 10064, 'mpr8vvf1-eu', 0),
(604, 10064, 'mpr6vvf18u-6fg', 0),
(605, 727322, 'asdfjklasdjlfa', 0),
(606, 727322, 'asdklfjasdfk', 0),
(607, 727322, 'asdkl,fjasldjfkasjdkf', 0),
(608, 727322, 'sad', 0),
(611, 727322, 'sads', 0),
(614, 727317, 'sdfsdfa', 0);

-- --------------------------------------------------------

--
-- Table structure for table `Orders`
--

CREATE TABLE `Orders` (
  `id` int NOT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `guest_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Orders`
--

INSERT INTO `Orders` (`id`, `order_date`, `user_id`, `total_price`, `payment_method`, `status`, `guest_email`) VALUES
(1, '2026-05-03 05:33:33', 4, 2.79, 'card', 'completed', NULL),
(2, '2026-05-03 06:09:26', NULL, 1.99, 'card', 'completed', 'test@guest.com'),
(3, '2026-05-03 06:12:30', NULL, 4.99, 'card', 'completed', 'test@guest.com'),
(4, '2026-05-03 06:18:40', NULL, 23.99, 'card', 'completed', 'test@guest.com'),
(5, '2026-05-03 06:20:52', NULL, 2.71, 'card', 'completed', 'test@guest.com'),
(6, '2026-05-03 06:22:53', NULL, 15.99, 'card', 'completed', 'test@guest.com'),
(7, '2026-05-03 09:41:15', 5, 21.17, 'card', 'completed', NULL),
(8, '2026-05-03 16:42:30', 5, 9.99, 'card', 'completed', NULL),
(9, '2026-05-07 19:05:27', 8, 0.99, 'card', 'completed', NULL),
(10, '2026-05-07 19:06:47', 8, 34.99, 'card', 'completed', NULL),
(11, '2026-05-09 19:48:30', 16, 7.99, 'card', 'completed', NULL),
(12, '2026-05-11 13:37:38', 15, 23.99, 'card', 'completed', NULL),
(13, '2026-05-13 15:49:24', 18, 20.00, 'card', 'completed', 'brevo.magnetic960@passmail.com'),
(14, '2026-05-15 16:50:03', 17, 44.99, 'card', 'completed', NULL),
(15, '2026-05-15 17:47:41', 5, 10.00, 'card', 'completed', NULL),
(16, '2026-05-16 18:59:34', 5, 8.50, 'card', 'completed', NULL),
(17, '2026-05-18 12:16:09', 15, 1.99, 'card', 'completed', NULL),
(18, '2026-05-18 12:20:24', 15, 8.50, 'card', 'completed', NULL),
(19, '2026-05-18 12:26:38', 15, 0.96, 'card', 'completed', NULL),
(20, '2026-05-18 12:28:57', NULL, 0.96, 'card', 'completed', 'mkm7294@gmail.com'),
(21, '2026-05-18 12:31:52', 15, 5.59, 'card', 'completed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Order_Items`
--

CREATE TABLE `Order_Items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `game_id` int NOT NULL,
  `key_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Order_Items`
--

INSERT INTO `Order_Items` (`id`, `order_id`, `game_id`, `key_id`, `quantity`, `unit_price`) VALUES
(1, 1, 591, 24, 1, 2.79),
(2, 2, 13537, 53, 1, 1.99),
(3, 3, 56184, 85, 1, 4.99),
(4, 4, 422859, 104, 1, 23.99),
(5, 5, 591, 114, 1, 2.71),
(6, 6, 10141, 47, 1, 15.99),
(7, 7, 28154, 69, 1, 13.99),
(8, 7, 19457, 63, 1, 1.19),
(9, 7, 622, 25, 1, 5.99),
(10, 8, 4186, 36, 1, 9.99),
(11, 9, 5563, 44, 1, 0.99),
(12, 10, 29642, 74, 1, 34.99),
(13, 11, 455597, 107, 1, 7.99),
(14, 12, 727315, 113, 1, 23.99),
(15, 13, 727317, 212, 1, 20.00),
(16, 14, 324997, 98, 1, 44.99),
(17, 15, 727322, 237, 1, 10.00),
(18, 16, 727318, 213, 1, 8.50),
(19, 17, 4200, 14, 1, 1.99),
(20, 18, 727318, 226, 1, 8.50),
(21, 19, 727319, 214, 1, 0.96),
(22, 20, 727319, 225, 1, 0.96),
(23, 21, 254545, 93, 1, 5.59);

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `2fa_enabled` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `email`, `password`, `role`, `is_active`, `2fa_enabled`) VALUES
(4, 'test@test.me', '$2y$10$0dkPtM3Njzrv2YXQCbppZuf0WWkwi.vwObXSpuIDLWQ/5HjeNCKLO', 'user', 0, 0),
(5, 'admin@gamestore.com', '$2y$10$i8eA0nHRNNaVF4B2bKQW1eRDsGHUHOgCDy8XGU24r0fJZsC1ypxC.', 'admin', 1, 0),
(6, 'ali@ghos.com', '$2y$10$lKGVu4yLIDuh2.A5SJYP3.jzamwZt6FBmLqxP8clZ1mDf.kbQunfG', 'user', 1, 0),
(7, 'test@guest.com', '$2y$10$ayytZh9zfpmupPv7TchNFeyExtRHRx8xw0P32tqzkTE.MLn5dlLAi', 'user', 1, 0),
(8, 'yousef@gmail.com', '$2y$10$hkDEMu9u0Et2Xsm3CM1bJeM6WMagM6TidAfX6zO6BpKRUKAq9mvCO', 'business', 1, 0),
(9, 'abdullah988@gmail.com', '$2y$10$8UH27geLPV3kYPbxy/DZ1OyhdhG22BUXo.M1T1LOMCVdK2UWKqLAm', 'user', 1, 0),
(10, 'abuali14q@gmail.com', '$2y$10$gWsG7BCFyo/oL1t8tK5hsuPpAmAe4qLIh4gY3YU7W1369FCHvdmrS', 'user', 1, 0),
(11, 'seller@ghos.com', '$2y$10$ymE6bN/G59TjgJNbEpx/PugckrDldXTmJqO6ldPKfIpTriMUseEpy', 'business', 1, 0),
(15, 'awdstfrfhhg@gmail.com', '$2y$10$P8jaEoJFj0Giys9rKP3Og.Ptz5nfBtkCm16gjmhUA1i3h66kXUMhe', 'user', 1, 1),
(16, 'abcb1240009m@gmail.com', '$2y$10$MqF/DjnFqAd6a0YOjLJRA.qIr8NeByeIPbUHkwGJ0hWZcuFLv8qcC', 'user', 1, 1),
(17, 'blitzcraftnetwork@gmail.com', '$2y$10$ieQDHogseteCjmfbkDuc6OpUnSYGdN3HH0TMs8aqkHikosqg7jYke', 'user', 1, 0),
(18, 'brevo.magnetic960@passmail.com', '$2y$10$d1gOgpA8/16O7tw1s6aLmufA/jIF.OGEOTV/RiTjllENas1qRD57a', 'user', 1, 0),
(19, 'mkm7294@proton.me', '$2y$10$otb7VNetHEBCM6Ou7dtAAu6hqhXk4szZnGnLeXyAWVKBss9XYXhR2', 'user', 1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Business_Applications`
--
ALTER TABLE `Business_Applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Cart`
--
ALTER TABLE `Cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cart_user` (`user_id`),
  ADD KEY `fk_cart_game` (`game_id`);

--
-- Indexes for table `Games`
--
ALTER TABLE `Games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_games_seller` (`seller_id`);

--
-- Indexes for table `Game_Images`
--
ALTER TABLE `Game_Images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_images_game` (`game_id`);

--
-- Indexes for table `Game_Keys`
--
ALTER TABLE `Game_Keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_code` (`key_code`),
  ADD KEY `fk_keys_game` (`game_id`);

--
-- Indexes for table `Orders`
--
ALTER TABLE `Orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_user` (`user_id`);

--
-- Indexes for table `Order_Items`
--
ALTER TABLE `Order_Items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `key_id` (`key_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Business_Applications`
--
ALTER TABLE `Business_Applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Cart`
--
ALTER TABLE `Cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `Games`
--
ALTER TABLE `Games`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=727324;

--
-- AUTO_INCREMENT for table `Game_Images`
--
ALTER TABLE `Game_Images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT for table `Game_Keys`
--
ALTER TABLE `Game_Keys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=615;

--
-- AUTO_INCREMENT for table `Orders`
--
ALTER TABLE `Orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `Order_Items`
--
ALTER TABLE `Order_Items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Business_Applications`
--
ALTER TABLE `Business_Applications`
  ADD CONSTRAINT `Business_Applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `Cart`
--
ALTER TABLE `Cart`
  ADD CONSTRAINT `fk_cart_game` FOREIGN KEY (`game_id`) REFERENCES `Games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `Games`
--
ALTER TABLE `Games`
  ADD CONSTRAINT `fk_games_seller` FOREIGN KEY (`seller_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `Game_Images`
--
ALTER TABLE `Game_Images`
  ADD CONSTRAINT `fk_images_game` FOREIGN KEY (`game_id`) REFERENCES `Games` (`id`);

--
-- Constraints for table `Game_Keys`
--
ALTER TABLE `Game_Keys`
  ADD CONSTRAINT `fk_keys_game` FOREIGN KEY (`game_id`) REFERENCES `Games` (`id`);

--
-- Constraints for table `Orders`
--
ALTER TABLE `Orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `Order_Items`
--
ALTER TABLE `Order_Items`
  ADD CONSTRAINT `Order_Items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `Orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Order_Items_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `Games` (`id`),
  ADD CONSTRAINT `Order_Items_ibfk_3` FOREIGN KEY (`key_id`) REFERENCES `Game_Keys` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
