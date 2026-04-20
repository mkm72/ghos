-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 20, 2026 at 02:01 PM
-- Server version: 12.2.2-MariaDB
-- PHP Version: 8.5.5

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
CREATE DATABASE IF NOT EXISTS `ghos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ghos`;

-- --------------------------------------------------------

--
-- Table structure for table `Cart`
--

CREATE TABLE `Cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Games`
--

CREATE TABLE `Games` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `platform` varchar(255) DEFAULT NULL,
  `genres` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Games`
--

INSERT INTO `Games` (`id`, `name`, `description`, `price`, `platform`, `genres`) VALUES
(28, 'Red Dead Redemption 2', 'America, 1899. The end of the wild west era has begun as lawmen hunt down the last remaining outlaw gangs. Those who will not surrender or succumb are killed. \r\n\r\nAfter a robbery goes badly wrong in the western town of Blackwater, Arthur Morgan and the Van der Linde gang are forced to flee. With federal agents and the best bounty hunters in the nation massing on their heels, the gang must rob, steal and fight their way across the rugged heartland of America in order to survive.', 14.99, 'PC, Xbox One, PlayStation 4', 'Action'),
(42, 'What Remains of Edith Finch', 'The Finch\'s family, also known as America\'s most unfortunate family, believes that the family is being pursued by a deadly curse. Each generation has only one child who survived to give birth to the next one.\r\n\r\nThe player begins to act as Edith Finch, who arrives in an orderly abandoned family mansion to find out what opens the key that she received from her mother along with the will.', 4.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS', 'Indie, Adventure'),
(115, 'Zero Escape: The Nonary Games', 'Kidnapped and taken to an unfamiliar location, nine people find themselves forced to participate in a diabolical Nonary Game by an enigmatic mastermind called Zero. Why were they there? Why were they chosen to put their lives on the line as part of a dangerous life and death game? Who can be trusted? Tensions rise as the situation becomes more and more dire, and the nine strangers must figure out how to escape before they wind up dead.', 2.99, 'PC, Xbox One, PlayStation 4, PS Vita', 'Adventure'),
(591, 'Monument Valley', '** Apple Game of the Year 2014 **\r\n** Winner of Apple Design Award 2014 **\r\nIn Monument Valley you will manipulate impossible architecture and guide a silent princess through a stunningly beautiful world.\r\nMonument Valley is a surreal exploration through fantastical architecture and impossible geometry. Guide the silent princess Ida through mysterious monuments, uncovering hidden paths, unfolding optical illusions and outsmarting the enigmatic Crow People.\r\nIda\'s Dream now available.', 2.79, 'PC, iOS, Android', 'Casual, Adventure, Puzzle'),
(622, 'XCOM: Enemy Within', '***NOTE: Compatible with iPad 3, iPad mini 2, iPhone 5 and up. WILL NOT be able to run on earlier generations, despite being able to purchase them on those devices***\r\nXCOM®: Enemy Within is a standalone expansion to the 2012 strategy game of the year XCOM: Enemy Unknown and it\'s now available on iOS devices!  Enemy Within features the core gameplay of Enemy Unknown plus more exciting content.', 5.99, 'PC, Xbox One, iOS, Android, Xbox 360, PlayStation 3, PS Vita', 'Strategy, Action, Simulation'),
(654, 'Stardew Valley', 'The hero (in the beginning you can choose gender, name and appearance) - an office worker who inherited an abandoned farm. The landscape of the farm can also be selected. For example, you can decide whether there will be a river nearby for fishing.\r\nThe farm area needs to be cleared, and it will take time.', 7.49, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, Android, macOS, Linux, PS Vita', 'Indie, RPG, Simulation'),
(1358, 'Papers, Please', 'The creator of the game often travelled through Asia and made the observation that the work of an immigration officer checking documents for entry is simultaneously very monotonous and very responsible. The game reproduces this work - but scammers and unusual situations occur in it much more often than in reality. The task of the player-officer is not to make a mistake, not to let an unwanted guest into the country. He has power, directories, translucent devices, etc.', 9.99, 'PC, iOS, Android, macOS, Linux, PS Vita', 'Educational, Indie, Simulation, Puzzle'),
(1450, 'INSIDE', 'INSIDE is a platform adventure game that transfers the atmosphere of a dystopic world. Players assume the role of a lonely boy, who walks through the monochromatic 2.5D environment and solves various puzzles. By the time main antagonists of the character pursue him throughout the whole world. The main storyline follows the unnamed boy through the in-game world locations including a forest, a farm, and a fictional laboratory, where experiments on bodies are held.', 2.49, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, macOS', 'Adventure, Action, Puzzle, Indie, Platformer'),
(1452, 'Phoenix Wright: Ace Attorney Trilogy', 'Defend the innocent and save the day! Experience the original trilogy in clear, retina-quality HD graphics, and choose to play horizontally for larger backgrounds or vertically for one-handed ease!\r\nPhoenix Wright: Ace Attorney Trilogy HD includes Phoenix Wright: Ace Attorney, Phoenix Wright: Ace Attorney - Justice For All, and Phoenix Wright: Ace Attorney - Trials and Tribulations. Each of these three games can be purchased separately or all together in one bundle.', 29.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, Nintendo 3DS', 'Adventure, Simulation'),
(1458, 'Bug Princess', '###　BUG PRINCESS ###\r\nThe legendary arcade shooter Bug Princess (Mushihimesama) arrives for iPhone, iPod touch and iPad!\r\nTake control of Princess Reco and dodge through massive bullet storms to save the village of Hoshifuri!\r\n* Please note this application is the arcade version.\r\n### GAME FEATURES ###\r\n● THREE UNIQUE GAME MODES, FOUR DIFFICULTIES\r\n- Original Mode: Moderate difficulty.\r\n- Maniac Mode  : Intense and frenetic gameplay.\r\n- Ultra Mode   : Sheer bullet hell.', 10.99, 'PC, Nintendo Switch, iOS', 'Casual, Arcade, Action'),
(1682, 'The Wolf Among Us', 'The Wolf Among Us is a five-part episodic game relying heavily on dialogues and choices of the player. The game is considered a prequel to Bill Willingham\'s \'Fables\' comic book and features usual TellTale stylistics: cartoon-like graphics, comparing your choices to the decisions of the other players and QTEs. \'The Wolf\' is the first part of the series with a promised expansion to the second season coming out in 2019.', 8.99, 'PC, PlayStation 4, Xbox One, iOS, Android, macOS, Xbox 360, PS Vita', 'Adventure'),
(2454, 'DOOM (2016)', 'Return of the classic FPS, Doom (2016) acts as a reboot of the series and brings back the Doomslayer, protagonist of the original Doom games. In order to solve the energy crisis, humanity learned to harvest the energy from Hell, and when something went wrong and a demon invasion has started, it’s up to the player to control the Doomslayer and destroy the evil.', 3.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Shooter, Action'),
(2551, 'Dark Souls III', 'Dark Souls III is the fourth installment in the Dark Souls series, now introducing the players to the world of Lothric, a kingdom which has suffered the fate similar to its counterparts from the previous games, descending from its height to utter darkness. A new tale of dark fantasy offers to create and guide the path of game’s protagonist, the Ashen One, through the dangers of the world before him.', 59.99, 'PC, Xbox One, PlayStation 4', 'Action, RPG'),
(3328, 'The Witcher 3: Wild Hunt', 'The third game in a series, it holds nothing back from the player. Open world adventures of the renowned monster slayer Geralt of Rivia are now even on a larger scale. Following the source material more accurately, this time Geralt is trying to find the child of the prophecy, Ciri while making a quick coin from various contracts on the side. Great attention to the world building above all creates an immersive story, where your decisions will shape the world around you.', 7.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, macOS', 'Action, RPG'),
(3498, 'Grand Theft Auto V', 'Rockstar Games went bigger, since their previous installment of the series. You get the complicated and realistic world-building from Liberty City of GTA4 in the setting of lively and diverse Los Santos, from an old fan favorite GTA San Andreas. 561 different vehicles (including every transport you can operate) and the amount is rising with every update.', 14.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Xbox 360, PlayStation 3', 'Action'),
(3612, 'Hotline Miami', 'Several chapters of top-down shooter action, Hotline Miami is a violent game, where the player takes control of the unnamed man, that receives orders to clear out stages from bandits and mobsters, using every weapon he can get. Over the course of the game, the player will be able to collect the masks that provide buffs and abilities. Fluid and tight combat includes various melee and ranged weapons, that can be used as intended or just thrown at the enemy.', 1.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, Linux, PS Vita', 'Indie, Shooter, Arcade, Action'),
(4062, 'BioShock Infinite', 'The third game in the series, Bioshock takes the story of the underwater confinement within the lost city of Rapture and takes it in the sky-city of Columbia. Players will follow Booker DeWitt, a private eye with a military past; as he will attempt to wipe his debts with the only skill he’s good at – finding people. Aside from obvious story and style differences, this time Bioshock protagonist has a personality, character, and voice, no longer the protagonist is a silent man, trying to survive.', 7.49, 'PC, Xbox One, PlayStation 4, Nintendo Switch, Linux, Xbox 360, PlayStation 3', 'Shooter, Action'),
(4166, 'Mass Effect', 'Mass Effect was the very start of the trilogy about Commander Shepard in his journey to save the universe from Reapers - an old civilisation that wants to kill every possible rational being in order to prevail any wars. You play as Shepard. With flexible backstory and different classes you travel to Eden Prime with Captain Anderson and Nihlus Kryik, you and your team must discover the mystery behind the attack on the human colony.', 7.49, 'PC, Xbox One, Xbox 360, PlayStation 3', 'Action, RPG'),
(4186, 'Persona 4 Golden', 'Persona 4 Golden is the jRPG and the remake of the Persona 4, released four years after the original. This is the fifth part of Persona series which is at the same time a sub-series to an even bigger franchise called Shin Megami Tensei. The remake is released exclusively for PS Vita handheld. \r\n\r\n###Gameplay\r\nThere is a similar pattern to all Persona games: the gameplay is divided into the usual school life and the underworld dungeon crawler part.', 9.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, PS Vita', 'RPG'),
(4200, 'Portal 2', 'Portal 2 is a first-person puzzle game developed by Valve Corporation and released on April 19, 2011 on Steam, PS3 and Xbox 360. It was published by Valve Corporation in digital form and by Electronic Arts in physical form. \r\n\r\nIts plot directly follows the first game\'s, taking place in the Half-Life universe. You play as Chell, a test subject in a research facility formerly ran by the company Aperture Science, but taken over by an evil AI that turned upon its creators, GladOS.', 1.99, 'PC, Xbox One, macOS, Linux, Xbox 360, PlayStation 3', 'Shooter, Puzzle'),
(4248, 'Dishonored', 'Dishonored is the game about stealth. Or action and killing people. It is you who will decide what to do with your enemies. You play as Corvo Attano, Empress\' bodyguard, a masterful assassin and a combat specialist. All of a sudden, a group of assassins kill the Empress and kidnaps her daughter Emily. Being accused of murder and waiting for execution in a cell, Corvo still manages to escape with the help of the Loyalists and their leader Admiral Havelock.', 2.49, 'PC, Xbox One, PlayStation 4, Xbox 360, PlayStation 3', 'Adventure, Action, RPG'),
(4265, 'Persona 3 Portable', 'Terrible creatures lurk in the dark, preying on those who wander into the hidden hour between one day and the next. As a member of a secret school club, you must wield your inner power—Persona—and protect humanity from impending doom. Will you live to see the light of day?\r\nHailed by critics and players alike for breathing new life into the RPG genre, Persona®3 now finds a new home on your PSP® (PlayStation®Portable) system.', 9.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, PSP', 'Strategy, Adventure, RPG'),
(4439, 'Mass Effect 3', 'Mass Effect 3 is the final part of the trilogy of the same name, created by BioWare. It is an action RPG with wide customization opportunities and several endings that depend on your choices during the game. There are side quests you can complete, and a relationship system that opens new ways to fulfill tasks and lets to romance some characters. The game follows traditions of the cosmic opera genre and features interstellar travel, space fights, and interaction with various alien races.', 7.49, 'PC, Xbox One, Xbox 360, PlayStation 3, Wii U', 'Action, RPG'),
(4535, 'Call of Duty 4: Modern Warfare', 'The fourth installment of a popular series, Call of Duty 4: Modern Warfare is split into two different, gameplay-wise, parts. The single-player campaign invites players to go through the episodic story, where players control six different characters. And even though the stories are taking place in different locations, the events of the campaign are happening simultaneously, creating the sense of urgency and painting a large-scale picture of the events.', 9.99, 'PC, Xbox One, PlayStation 4, Nintendo DS, macOS, Xbox 360, PlayStation 3, Wii', 'Shooter, Action'),
(4544, 'Red Dead Redemption', 'Red Dead Redemption is a third-person open-world adventure game which implements the Wild West at its best: it is very much GTA-clone but in bizarre stylistics and the very beginning of the twentieth century. This is the second title of a franchise, being preceded by Red Dead Revolver and followed by Red Dead Redemption 2 coming out in late 2018. \r\nWe play as John Marston who gradually takes down and take out criminals and those, who crosses his path.', 24.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, Xbox 360, PlayStation 3', 'Shooter, Action'),
(4550, 'Dead Space 2', 'Dead Space 2 is a third-person action shooter including both survival and horror elements. The game is a direct sequel to the first chapter of the Dead Space franchise. The game is set in the space environment of the future. The story of the second part begins in 2511, 3 years after the events held in the first game. The main character Isaac Clarke is awakened after 3-year coma (after the escape from Aegis VII) in an insane asylum in the Sprawl on Titan.', 3.99, 'PC, Xbox One, Xbox 360, PlayStation 3', 'Shooter, Action'),
(4570, 'Dead Space (2008)', 'Dead Space is a third-person shooter with horror elements. Playing as Isaac Clarke, the systems engineer, players will be isolated on the spaceship USG Ishimura after the crew was slaughtered by mindless Necromorphs after the failed investigation of the distress signal. Now Isaac not only has to escape but uncover the dark secrets of Ishimura, while looking for the clues about the whereabouts of his girlfriend Nicole.', 7.99, 'PC, Xbox One, Xbox 360, PlayStation 3', 'Shooter, Action'),
(5563, 'Fallout: New Vegas', 'Fallout: New Vegas is the second instalment after the reboot of the Fallout series and a fourth instalment in the franchise itself. Being a spin-off and developed by a different studio, Obsidian Entertainment, Fallout: New Vegas follows the Courier as he\'s ambushed by a gang lead by Benny, stealing a Platinum Chip and heavily wounded, practically left for dead. As he wakes up, he minds himself in the company of Doc Mitchell who saved our protagonist and patches him up.', 0.99, 'PC, Xbox One, PlayStation 4, Xbox 360, PlayStation 3', 'Shooter, Action, RPG'),
(5679, 'The Elder Scrolls V: Skyrim', 'The fifth game in the series, Skyrim takes us on a journey through the coldest region of Cyrodiil. Once again player can traverse the open world RPG armed with various medieval weapons and magic, to become a hero of Nordic legends –Dovahkiin, the Dragonborn. After mandatory character creation players will have to escape not only imprisonment but a fire-breathing dragon. Something Skyrim hasn’t seen in centuries.', 19.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, Xbox 360, PlayStation 3', 'Action, RPG'),
(9767, 'Hollow Knight', 'Hollow Knight is a Metroidvania-type game developed by an indie studio named Team Cherry.\r\n\r\nMost of the game\'s story is told through the in-world items, tablets, and thoughts of other characters. Many plot aspects are told to the player indirectly or through the secret areas that provide a bit of lore in addition to an upgrade. At the beginning of the game, the player visits a town of Dirtmouth. A town built above the ruins of Hallownest.', 7.49, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS, Linux', 'Platformer, Indie, Action'),
(10073, 'Divinity: Original Sin 2', 'The Divine is dead. The Void approaches. And the powers latent within you are soon to awaken. The battle for Divinity has begun. Choose wisely and trust sparingly; darkness lurks within every heart.\r\n\r\nWho will you be? A flesh-eating elf; an imperial lizard; an undead risen from the grave? Choose your race and origin story - or create your own! Discover how the world reacts differently to who - and what - you are.It’s time for a new Divinity!', 11.24, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Strategy, RPG'),
(10141, 'NieR:Automata', 'NieR: Automata is an action RPG, a sequel to Nier and a spin-off to the Drakenguard series. The story is set in the middle of the war between humans and machines where you take on the role of an android warrior called 2B. The story develops around the theme of androids\' ability to feel and make their own decisions.', 15.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Action, RPG'),
(10389, 'Gothic II: Gold Edition', 'The second game from the series Gothic.\r\nIn the first game, people who inhabit a fantasy kingdom, lose the war to the orcs. To win, the king needs magical ore mined in local mines. The king decides to send to the mines everyone who is accused of any crime and create a magical Barrier around the mines so that no one can escape from the mines. However, Barrier surrounded a much larger area and the prisoners became masters inside the Barrier.', 4.99, 'PC', 'Action, RPG'),
(11498, 'The Legend of Heroes: Trails in the Sky SC', 'The coup d’état that threatened to shake the foundation of the Liberl Kingdom has now come to a close and Her Majesty the Queen’s birthday celebrations are in full swing throughout the streets of Grancel. During that same night, a boy who vowed to make amends for his past disappeared before the girl he loved. Clutched in the girl’s hand was the one thing he left for her to remember him by: a harmonica.', 19.49, 'PC, PSP', 'RPG'),
(11971, 'Space Rangers HD: A War Apart', '###The reimagined classic\r\nA remastered version of the classic space strategy in real time from three studios: SNK Games, Katauri Interactive and original developers from Elemental Games. The first game of the series was released in 2002 but almost did not find fans outside of its region of release. The gameplay is often compared to games like Elite and Star Control 2.', 2.24, 'PC', 'Strategy, Adventure, RPG, Simulation'),
(12447, 'The Elder Scrolls V: Skyrim Special Edition', 'The Elder Scrolls V: Skyrim Special Edition is the 2016 reinstallment of the open world fantasy RPG, developed by Bethesda Game Studios. Following the original release of 2011, Special Edition focuses on reshaping every sword and ax, polishing every stone in the high castles and the suburbs of the low, overall bringing a renewed experience to its fans and newcomer players.', 9.99, 'PC, Xbox One, PlayStation 4', 'Action, RPG'),
(13536, 'Portal', 'Every single time you click your mouse while holding a gun, you expect bullets to fly and enemies to fall. But here you will try out the FPS game filled with environmental puzzles and engaging story. \r\nSilent template for your adventures, Chell, wakes up in a testing facility. She’s a subject of experiments on instant travel device, supervised by snarky and hostile GLaDOS.\r\nPlayers will have to complete the tests, room by room, expecting either reward, freedom or more tests.', 1.99, 'PC, Nintendo Switch, Android, macOS, Linux, Xbox 360, PlayStation 3', 'Action, Puzzle'),
(13537, 'Half-Life 2', 'Gordon Freeman became the most popular nameless and voiceless protagonist in gaming history. He is painted as the most famous scientist and a hero within the world of Half-Life, and for a good reason. In the first game he saved the planet from alien invasion, this time, when the invasion is already begun, the world needs his help one more time. And you, as a player, will help this world to survive.', 1.99, 'PC, Android, macOS, Linux, Xbox 360, Xbox', 'Shooter, Action'),
(13820, 'The Elder Scrolls III: Morrowind', 'The Elder Scrolls III: Morrowind® Game of the Year Edition includes Morrowind plus all of the content from the Bloodmoon and Tribunal expansions. The original Mod Construction Set is not included in this package.\r\n\r\nAn epic, open-ended single-player RPG, Morrowind allows you to create and play any kind of character imaginable.', 5.99, 'PC, Xbox 360, Xbox', 'RPG'),
(13856, 'Katana ZERO', 'Katana ZERO is a fast paced neo-noir action platformer, focusing on tight, instant-death acrobatic combat, and a dark 80\'s neon aesthetic. Aided with your trusty katana, the time manipulation drug Chronos and the rest of your assassin\'s arsenal, fight your way through a fractured city, and take back what\'s rightfully yours.\r\nRun, sneak, walljump, grapple hook, roll, slash bullets, toss pottery, and slow down time to complete levels.\r\nNo procedural generation. No backtracking.', 8.99, 'PC, Xbox One, Nintendo Switch, macOS', 'Platformer, Indie, Action'),
(13925, 'Prince of Persia: Warrior Within', 'Prince of Persia: Warrior Within is an action adventure game and a part of the vast Prince of Persia series which includes ten titles overall. Sands of Time precede it and followed by The Two Thrones\r\n\r\n###Gameplay\r\nAll the big titles in the series feature pretty much the same mechanics: 3D platforming with a heavy focus on the fighting elements and parkour.', 1.99, 'PC, iOS, Xbox, PlayStation 3, PlayStation 2, GameCube', 'Adventure, Action'),
(14935, 'Total War: Shogun 2 - Fall of the Samurai', 'Special EditionTotal War: SHOGUN 2 – Fall of the Samurai Steam Special Edition includes:The Steam exclusive Tsu faction pack, The Emperor’s Cunning - Rising from humble roots, the people of Tsu are wise, artful and astute strategists. Their use of Ninja is unsurpassed on the battlefield and in covert operations.   This additional in-game faction is only available in the Steam Special Edition.\r\n\r\nThe game original soundtrack - selected songs from the original game soundtrack by Jeff van Dyck.', 7.49, 'PC, macOS', 'Strategy'),
(17572, 'Batman: Arkham Asylum Game of the Year Edition', 'Batman: Arkham Asylum is the first game in Warner Brothers’ action-adventure franchise Batman: Arkham. The game takes places in fictional Asylum on Arkham Island near Gotham City where dangerous and mentally unstable criminals are kept.  \r\nThe story follows Batman as he captures Joker after his assault on Gotham City Hall. The game starts when Batman accompanies convoy that transfers Joker to the Arkham Asylum.', 2.99, 'PC, PlayStation 4, Xbox 360, PlayStation 3', 'Platformer, Adventure, Action'),
(17576, 'Batman: Arkham City - Game of the Year Edition', 'Batman: Arkham City is the second game in the Arkham series. Joker has escaped from Arkham Asylum, and Doctor Hugo Strange is capturing Bruce Wayne. Strange discovers the true identity behind the Batman and tries to kill him, but Wayne escapes from the prison and saves the Catwoman from Two-Face. Trying to acknowledge the mysterious Protocol 10 he finds Joker in devastating state - fatal infection slowly kills. Joker infects Batman and every Gotham hospital.', 3.99, 'PC, Xbox One, PlayStation 4, macOS, Xbox 360, PlayStation 3', 'Adventure, Action'),
(17959, 'Ori and the Blind Forest: Definitive Edition', 'NEW IN THE DEFINITIVE EDITION\r\n• Packed with new and additional content: New areas, new secrets, new abilities, more story sequences, multiple difficulty modes, full backtracking support and much more!\r\n• Discover Naru’s past in two brand new environments.\r\n• Master two powerful new abilities – Dash and Light Burst.\r\n• Find new secret areas and explore Nibel faster by teleporting between Spirit Wells.\r\nThe forest of Nibel is dying.', 4.99, 'PC, Xbox One, Nintendo Switch, Xbox 360', 'Platformer, Adventure'),
(18080, 'Half-Life', 'Half-Life is the original game in the series. Being a revolutionary at the time, we follow the story of Gordon Freeman - a silent scientist at the facility called Black Mesa. Arriving late at work and hastily doing his routine he runs into the experiment field. However, the experiment goes completely wrong and opens a portal to a completely different dimension called Xen.', 1.99, 'PC, macOS, Linux, PlayStation 2, Dreamcast', 'Shooter, Action'),
(19445, 'Disciples II: Gallean\'s Return', 'Disciples II: Gallean\'s Return is a compilation edition that includes the base game, Disciples II: Dark Prophecy, plus the two standalone expansions Disciples II: Guardians of the Light and Disciples II: Servants of the Dark.\r\n\r\nDisciples II: Guardians of the Light is a stand-alone expansion that lets you dive into the fantastical world of Disciples II as either the Empire or the Mountain Clans.', 1.19, 'PC', 'Strategy'),
(19457, 'Disciples II: Rise of the Elves', 'The award-winning series Disciples introduced a milestone in the game\'s very successful history; the introduction of a new race: The Elves. The Elven Race added a new dimension to the game and added countless hours of gameplay.\r\nNow that series has expanded further with Disciples II: Rise Of The Elves. This version features all of the campaigns found in the original and a brand new campaign that continues the storyline of the Elven Race.', 1.19, 'PC', 'Strategy'),
(19635, 'Fable: The Lost Chapters', 'Fable: The Lost Chapters is a re-release for the personal computers of the Fable game, originally created for the Xbox. This release includes content that is not included in the release of the game for Xbox.\r\nThe main character is personalized in great detail. Everything that he does affects him. He can get obesity from a plentiful meal, and if he drinks alcohol, he will become poorly oriented and eventually will vomit.', 3.29, 'PC, macOS, Xbox', 'Adventure, Action, RPG'),
(19654, 'Samurai Gunn', 'Samurai Gunn is a lightning-fast Bushido brawler for two to four players. Each samurai is armed with a sword and gun, with only 3 bullets to a life. Discipline and quick reflexes are the key to deflecting bullets and sending your opponents’ heads rolling.', 0.99, 'PC, macOS', 'Action'),
(20709, 'Tom Clancy\'s Splinter Cell Chaos Theory', '###Instant classic\r\nThe stealth-action, which became a real classic and well-known among gamers. Excellent reviews and 92/100 score on Metacritic is a serious indicator. In the Steam community, there are still enthusiastic nostalgic reviews of almost everything that concerns Tom Clancy\'s Splinter Cell Chaos Theory. And there is an explanation for this. The game was released in 2005 and became the third in the Splinter Cell series.', 2.49, 'PC, Xbox One, Nintendo 3DS, Nintendo DS, Xbox 360, Xbox, PlayStation 3, PlayStation 2, GameCube', 'Adventure, Action'),
(21974, 'The Legend of Heroes: Trails in the Sky the 3rd', 'Half a year after the events of Trails in the Sky Second Chapter, Liberl has settled into peace once again—but even during peaceful times, there are many among the distinguished and fortunate burning with greed thanks to the influence of ancient artifacts.', 22.49, 'PC, PSP', 'RPG'),
(23741, 'Monument Valley 2', 'Guide a mother and her child as they embark on a journey through magical architecture, discovering illusionary pathways and delightful puzzles as you learn the secrets of the Sacred Geometry.\r\n\r\nSequel to the Apple Game of the Year 2014, Monument Valley 2 presents a brand new adventure set in a beautiful and impossible world.\r\n\r\nHelp Ro as she teaches her child about the mysteries of the valley, exploring stunning environments and manipulating architecture to guide them on their way.', 2.79, 'PC, iOS, Android', 'Casual, Adventure, Puzzle'),
(28154, 'Cuphead', 'Hand-drawn 2D platformer in the style of 30s cartoons. 2D Dark Souls as the fans refer to the difficulty of this one. It took developers 6 years to create and polish their magnum opus. Cuphead is a classic run and gun adventure that heavily emphasizes on boss battles.\r\n\r\nPlay as Cuphead or his brother Mugman that signed a deal with the devil and know needs to bring the master souls of its debtors.', 13.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS', 'Platformer, Indie, Action'),
(28199, 'Ori and the Will of the Wisps', 'A New Journey Begins\r\n\r\nEmbark on an adventure with all new combat and customization options while exploring a vast, exotic world encountering larger than life enemies and challenging puzzles. Seek help from discoverable allies on your path to unravel Ori’s true destiny.\r\n\r\nExplore a vast, beautiful, and immersive world\r\nExplore a vast, beautiful, immersive, and dangerous world filled with gripping enemy encounters, challenging puzzles and thrilling escape sequences.', 7.49, 'PC, Xbox One, Xbox Series S/X, Nintendo Switch', 'Platformer, Adventure, Action'),
(28568, 'Assassin\'s Creed II', 'Assassin\'s Creed II is the second installment in the AC series, the center of which is stealths kills, exploring the world and enemy encounters. It is the straight sequel to the first part of the series and the beginning of the Ezio — the protagonist — trilogy, followed by \'Brotherhood\' and \'Revelation.\'\r\nThe events take place in Rome, during the Italian Renaissance (1476-1499), we play as Ezio Auditore and are fighting against Knight Templar, being the Assassins.', 4.99, 'PC, Xbox One, PlayStation 4, macOS, Xbox 360, PlayStation 3', 'Action'),
(28623, 'Batman: Arkham City', 'The plot of Arkham City begins one and a half years after the events of Arkham Asylum. Quincy Sharp, former superintendent of the Arkham Psychiatric Hospital, became mayor of Gotham and created the prison Arkham City. Prisoners of Arkham City are not controlled by anyone in its borders, they are only forbidden from running away ...\r\nThere are all the regular characters in the game - Joker, Two-Face, Catwoman, Ra\'s al Ghul, James Gordon and others.', 3.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch, macOS, Xbox 360, PlayStation 3', 'Action'),
(29153, 'Max Payne 2: The Fall of Max Payne', 'Max Payne 2: The Fall of Max Payne is a third-person shooter, serving as a direct sequel to the first game. The player follows the story of an NYPD detective, tasked with resolving crimes and answering many questions of its predecessor’s story. The game is set in noir tones, providing with main character’s monologues and various cutscenes.\r\nThe titular protagonist, Max Payne, is a traumatized officer of the police.', 2.49, 'PC, Xbox, PlayStation 2', 'Shooter, Action'),
(29642, 'Silent Hill 2 (2001)', 'In the sequel to Silent Hill, Silent Hill 2 follows James Sunderland, whose life is shattered when his young wife Mary suffers a tragic death. Three years later, a mysterious letter arrives from Mary, beckoning him to return to their sanctuary of memories, the dark realm of Silent Hill. You must guide James through all-new environments and creepy new areas closed off in the original game. Real-time weather effects, fog, morphing, and shadows set the stage for heart-stopping frights.', 34.99, 'PC, Xbox, PlayStation 2', 'Adventure, Action'),
(42303, 'BioShock Infinite: Burial at Sea - Episode Two', '', 3.74, 'PC, PlayStation 4, Xbox 360, PlayStation 3', 'Shooter, Action'),
(43050, 'The Witcher 3: Wild Hunt – Hearts of Stone', 'Hearts of Stone is the first official expansion pack for The Witcher 3: Wild Hunt—an award-winning role-playing game set in a vast fantasy open world. Become Geralt of Rivia, a professional monster slayer hired to defeat a ruthless bandit captain, Olgierd von Everec, a man who possesses the power of immortality. Hearts of Stone packs over 10 hours of new adventures, introducing new characters, powerful monsters, unique romance and a brand new storyline shaped by your choices.', 1.99, 'PC, Xbox One, PlayStation 4', 'RPG'),
(43252, 'The Witcher 3: Wild Hunt – Blood and Wine', 'Welcome to the land of summer, a remote valley untouched by war. The land of wandering knights, noble ladies and magnificent wineries. What better time to visit than now, when this kingdom of virtue is torn apart by a series of savage massacres! Geralt of Rivia, a legendary monster slayer, takes on his last great contract. Blood and Wine offers over 30 hours of adventure, where beauty clashes with horror, and love dances with deceit.', 3.99, 'PC, Xbox One, PlayStation 4', 'RPG'),
(43737, 'Dark Souls III: Ashes of Ariandel', 'You, are the unkindled.  As part of the Dark Souls™ III Season Pass, expand your Dark Souls™ III experience with the Ashes of Ariandel™ DLC pack.  Journey to the snowy world of Ariandel and encounter new areas, bosses, enemies, weapons, armor set, magic spells and more.  Will you accept the challenge and embrace the darkness once more?', 14.99, 'PC, Xbox One, PlayStation 4', 'Action, RPG'),
(45958, 'XCOM 2: War of the Chosen', 'XCOM® 2: War of the Chosen, is the expansion to the 2016 award-winning strategy game of the year. \r\n\r\nXCOM® 2: War of the Chosen adds extensive new content in the fight against ADVENT when additional resistance factions form in order to eliminate the alien threat on Earth. In response, a new enemy, known as the “Chosen,” emerges with one goal: recapture the Commander.', 3.99, 'PC, Xbox One, PlayStation 4, macOS, Linux', 'Strategy'),
(50734, 'Sekiro: Shadows Die Twice', 'Sekiro: Shadows Die Twice is a game about a ninja (or shinobi, as they call it), who is seeking revenge in the Sengoku era Japan.\r\n\r\n###Plot\r\nThe game is set in the 16th century in a fictionalized version of Japan. The main protagonist is a member of a shinobi clan. A samurai from the rival Ashina clan captured the protagonist\'s master, and the protagonist himself lost his arm trying to protect his leader.', 29.99, 'PC, Xbox One, PlayStation 4', 'Action, RPG'),
(50839, 'Baba Is You', 'Baba Is You is a puzzle game where you can change the rules by which you play. In every level, the rules themselves are lying as blocks you can interact with; by manipulating them, you can change how the level works and cause surprising, unexpected interactions! With some simple block-pushing you can turn yourself into a rock, turn patches of grass into dangerously hot obstacles, and even change the goal you need to reach to something entirely different.', 14.99, 'PC, Nintendo Switch, iOS, Android, macOS, Linux', 'Indie, Puzzle'),
(51610, 'Dark Souls: Remastered', 'Then, there was fire. Re-experience the critically acclaimed, genre-defining game that started it all. Beautifully remastered, return to Lordran in stunning high-definition detail running at 60fps.\r\nDark Souls Remastered includes the main game plus the Artorias of the Abyss DLC.', 39.99, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Action, RPG'),
(52201, 'Yakuza 6: The Song of Life', 'How far will you go for family? Three years after the events of Yakuza 5, Kazuma Kiryu, the Dragon of Dojima, returns in Yakuza 6: The Song of Life with the dream of living a quiet life. Upon his arrival, he discovers Haruka has been involved in an accident and has slipped into a coma, leaving her young son, Haruto, without care. To protect this child, Kiryu takes Haruto to the last place Haruka was spotted, Onomichi, Hiroshima.', 10.99, 'PC, Xbox One, PlayStation 4', 'Shooter, Adventure, Action'),
(52884, 'DOOM', 'Doom (typeset as DOOM in official documents) is a 1993 science fiction horror-themed first-person shooter (FPS) video game by id Software. It is considered one of the most significant and influential titles in video game history, for having helped to pioneer the now-ubiquitous first-person shooter. The original game was divided into three nine-level episodes and was distributed via shareware and mail order.', 3.99, 'PC, PlayStation 4, Xbox One, Nintendo Switch, Android, Linux, Xbox 360, PlayStation 3, PlayStation, Game Boy Advance, SNES, SEGA Saturn, SEGA 32X, 3DO, Jaguar', 'Shooter, Action'),
(56184, 'Resident Evil 4 (2005)', 'Resident Evil 4 is a third-person shooter game developed by Capcom Production Studio 4 and published by Capcom. The sixth major installment in the Resident Evil series, it was originally released for the GameCube in 2005. Players control U.S. government special agent Leon S. Kennedy, who is sent on a mission to rescue the U.S. president\'s daughter Ashley Graham, who has been kidnapped by a cult.', 4.99, 'PC, PlayStation 4, iOS, Android, PlayStation 2, Wii, GameCube', 'Shooter, Action'),
(58134, 'Marvel\'s Spider-Man', 'Marvel\'s Spider-Man offers the player to take on the role of the most famous Marvel superhero.\r\n\r\n###Plot\r\nThe game introduces Spider-Man as an already experienced superhero. By the time the game begins, Peter has captured the infamous Kingpin as well as several other supervillains. Now, a gang that goes by the name of Demons poses a new danger to New York. Meanwhile, Peter is working for the scientist Otto Octavius, who didn\'t yet become Dr. Octopus, on their science project.', 23.99, 'PC, PlayStation 5, PlayStation 4', 'Action'),
(58175, 'God of War (2018)', 'It is a new beginning for Kratos. Living as a man outside the shadow of the gods, he ventures into the brutal Norse wilds with his son Atreus, fighting to fulfill a deeply personal quest. \r\n\r\nHis vengeance against the Gods of Olympus years behind him, Kratos now lives as a man in the realm of Norse Gods and monsters. It is in this harsh, unforgiving world that he must fight to survive… And teach his son to do the same.', 19.99, 'PC, PlayStation 4', 'Action'),
(58388, 'ZONE OF THE ENDERS: The 2nd Runner - M∀RS', 'JEHUTY lives. And there, ANUBIS thrives. ZONE OF THE ENDERS: The 2nd Runner returns with 4K and VR support.\r\nRelive the experience ZONE OF THE ENDERS: The 2nd Runner -  M∀RS as a full-length remaster of the classic fast-paced 3D robot action game, recreated in VR, native 4K and in full surround sound. Enter JEHUTY’s cockpit and fly through Martian skies!', 5.99, 'PC, PlayStation 4', 'Action'),
(58550, 'Ghost of Tsushima', 'The year is 1274. Samurai warriors are the legendary defenders of Japan--until the fearsome Mongol Empire invades the island of Tsushima, wreaking havoc and conquering the local population. As one of the last surviving samurai, you rise from the ashes to fight back. But, honorable tactics won\'t lead you to victory. You must move beyond your samurai traditions to forge a new way of fighting--the way of the Ghost--as you wage an unconventional war for the freedom of Japan.', 35.99, 'PC, PlayStation 5, PlayStation 4', 'Adventure, Action, RPG'),
(58813, 'Resident Evil 2', 'Resident Evil 2 is the remake of the 1998 game of the same name. \r\n\r\n###Plot\r\nThe plot of the remake is identical to that of the original game. The story follows the survivors of a zombie virus outbreak in the fictional Raccoon City. There are two protagonists: Claire Redfield, a high school student, and Leon Kennedy, a policeman. They both search for the ways to escape the infested city. Companions, such as Ada Wong and Sherry, occasionally follow the protagonists.', 13.79, 'PC, PlayStation 5, Xbox One, PlayStation 4, Nintendo Switch, macOS', 'Adventure, Action'),
(58890, 'Need For Speed: Most Wanted', 'Wake up to the smell of burnt asphalt as the scent of illicit street\r\nracing permeates the air. Need for Speed Most Wanted challenges you to\r\nbecome the most notorious and elusive street racer.\r\n\r\nFeatures\r\n\r\n• Master the art of cop evasion in Barricade Runner and other new race\r\nmodes.\r\n• Modify your ride to beat any tuner, muscle, or exotic.\r\n•\r\nCustomize the look of your car to elude police pursuit.\r\n• Win races,\r\nclimb the Blacklist, become the Most Wanted.', 3.99, 'PC, Xbox 360, Xbox, PlayStation 3, PlayStation 2, PSP, GameCube', 'Racing, Arcade'),
(59199, 'Divinity: Original Sin 2 - Definitive Edition', 'The Divine is dead. The Void approaches. And the powers lying dormant within you are soon to awaken. The battle for Divinity has begun. Choose wisely and trust sparingly; darkness lurks within every heart.\r\n\r\nWho will you be?\r\nA flesh-eating Elf, an Imperial Lizard or an Undead, risen from the grave? Discover how the world reacts differently to who - or what - you are.\r\nIt’s time for a new Divinity!\r\n\r\nGather your party and develop relationships with your companions.', 11.24, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, macOS', 'RPG'),
(254545, 'Cuphead: The Delicious Last Course', 'In Cuphead: The Delicious Last Course, Cuphead and Mugman are joined by Ms. Chalice for a DLC add-on adventure on a brand new island! With new weapons, new charms, and Ms. Chalice\'s brand new abilities, take on a new cast of multi-faceted, screen-filling bosses to assist Chef Saltbaker in Cuphead\'s final challenging quest.\r\n\r\nFeaturing Ms. Chalice as a brand new playable character with a modified moveset and new abilities. Once acquired, Ms.', 5.59, 'PC, Xbox One, PlayStation 4, Nintendo Switch', 'Platformer'),
(257192, 'Psychonauts 2', 'Razputin Aquato, trained acrobat and powerful young psychic, has realized his life long dream of joining the international psychic espionage organization known as the Psychonauts! But these psychic super spies are in trouble. Their leader hasn\'t been the same since he was kidnapped, and what\'s worse, there\'s a mole hiding in headquarters. Raz must use his powers to stop the mole before they execute their secret plan--to bring the murderous psychic villain, Maligula, back from the dead!', 11.99, 'PC, Xbox One, PlayStation 4, Xbox Series S/X, macOS, Linux', 'Platformer, Adventure, Action'),
(262382, 'Disco Elysium', 'Disco Elysium is a groundbreaking blend of hardboiled cop show and isometric RPG. Solve a massive, open ended case in a unique urban fantasy setting. Kick in doors, interrogate suspects, or just get lost exploring the gorgeously rendered city of Revachol and unraveling its mysteries. Tough choices need to be made. What kind of cop you are — is up to you.', 9.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, macOS', 'Indie, Adventure, RPG'),
(274755, 'Hades', 'Hades is a rogue-like dungeon crawler that combines the best aspects of Supergiant\'s critically acclaimed titles, including the fast-paced action of Bastion, the rich atmosphere and depth of Transistor, and the character-driven storytelling of Pyre.', 6.24, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Indie, Adventure, Action, RPG'),
(274757, 'Sayonara Wild Hearts', 'Sayonara Wild Hearts is a euphoric music video dream about being awesome, riding motorcycles, skateboarding, dance battling, shooting lasers, wielding swords, and breaking hearts at 200 mph.\r\nAs the heart of a young woman breaks, the balance of the universe is disturbed. A diamond butterfly appears in her dreams and leads her through a highway in the sky, where she finds her other self: the masked biker called The Fool.', 6.49, 'PC, Xbox One, PlayStation 4, Nintendo Switch, iOS, macOS', 'Casual, Indie, Action'),
(307137, 'Live A Live', 'Live A Live (ライブ・ア・ライブ) is a RPG video game published by SquareSoft released on September 2nd, 1994 for the SNES. The game was directed by Takashi Tokita, known for his work in Final Fantasy IV and Chrono Trigger. The soundtrack was composed by Yoko Shimomura, being her first full project at square.', 19.99, 'PC, SNES', 'Adventure, RPG'),
(324997, 'Baldur\'s Gate III', 'Gather your party, and return to the Forgotten Realms in a tale of fellowship and betrayal, sacrifice and survival, and the lure of absolute power.\r\n\r\nMysterious abilities are awakening inside you, drawn from a Mind Flayer parasite planted in your brain. Resist, and turn darkness against itself. Or embrace corruption, and become ultimate evil.\r\n\r\nFrom the creators of Divinity: Original Sin 2 comes a next-generation RPG, set in the world of Dungeons and Dragons.', 44.99, 'PC, PlayStation 5, Xbox Series S/X, macOS', 'Strategy, Adventure, RPG'),
(326243, 'Elden Ring', 'The Golden Order has been broken.\r\n\r\nRise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between.\r\n\r\nIn the Lands Between ruled by Queen Marika the Eternal, the Elden Ring, the source of the Erdtree, has been shattered.\r\n\r\nMarika\'s offspring, demigods all, claimed the shards of the Elden Ring known as the Great Runes, and the mad taint of their newfound strength triggered a war: The Shattering.', 38.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X', 'Action, RPG'),
(339958, 'Persona 5 Royal', 'Wear the mask.  Reveal your truth.\r\nPrepare for an all-new RPG experience in Persona®5 Royal based in the universe of the award-winning series, Persona®! Don the mask of Joker and join the Phantom Thieves of Hearts. Break free from the chains of modern society and stage grand heists to infiltrate the minds of the corrupt and make them change their ways! Persona®5 Royal is packed with new characters, story depth, new locations to explore, & a new grappling hook mechanic for access to new areas.', 17.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Adventure, RPG'),
(366881, 'Little Nightmares II', 'Return to the world of charming horror that has terrified over 1 million fans. Face a completely new set of distorted enemies as Mono, and learn how to be as courageous as a child.', 9.89, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Platformer, Adventure, Action'),
(366885, 'Dragon Age: Origins - Ultimate Edition', 'Get the ultimate Dragon Age™ experience! Dragon Age™: Origins – Ultimate Edition includes:\r\n\r\n~ Dragon Age™: Origins\r\nDiscover the groundbreaking RPG, winner of more than 50 awards including more than 30 \'Game of the Year\' awards! You are a Grey Warden, one of the last of a legendary order of guardians. With the return of mankind\'s ancient foe and the kingdom engulfed in civil war, you have been chosen by fate to unite the shattered lands and slay the archdemon once and for all.', 7.49, 'PC, Xbox 360, PlayStation 3', 'RPG'),
(366889, 'Monster Hunter World: Iceborne', 'A diverse locale, rich with endemic life.\r\nNumerous monsters that prey on each other and get into turf wars.\r\nA new hunting experience, making use of the densely packed environment.\r\nMonster Hunter: World, the game that brought you a new style of hunting action,\r\nis about to get even bigger with the massive Monster Hunter World: Iceborne expansion!\r\n\r\n- All-new Hunting Mechanics\r\n\r\nAll 14 weapon types have new moves and combos. Each weapon has more unique actions than ever before.', 39.99, 'PC, Xbox One, PlayStation 4', 'Massively Multiplayer, Action, RPG'),
(385406, 'Dark Souls III: The Ringed City', 'Fear not, the dark, ashen one.  <br/>\r\nThe Ringed City is the final DLC pack for Dark Souls III – an award-winning, genre-defining Golden Joystick Awards 2016 Game of the year RPG.  Journey to the world’s end to search for the Ringed City and encounter new lands, new bosses, new enemies with new armor, magic and items.  Experience the epic final chapter of a dark world that could only be created by the mind of Hidetaka Miyazaki.       <br/>\r\nA New World.  One Last Journey.', 14.99, 'PC, Xbox One, PlayStation 4', 'Action, RPG'),
(422859, 'NieR Replicant v1.22474487139...', 'A thousand-year lie that would live on for eternity...\r\n\r\nNieR Replicant ver.1.22474487139... is an updated version of NieR Replicant, previously only released in Japan.\r\n\r\nDiscover the one-of-a-kind prequel to the critically-acclaimed masterpiece NieR:Automata. Now with a modern upgrade, experience masterfully revived visuals, a fascinating storyline and more!\r\n\r\nThe protagonist is a kind young man living in a remote village.', 23.99, 'PC, Xbox One, PlayStation 4', 'Adventure, Action, RPG'),
(428664, 'A Space for the Unbound', 'Check out the free prologue chapter here!https://store.steampowered.com/app/1201280/\r\nAbout the GameHigh school is ending and the world is ending with it\r\nA Space For The Unbound is a slice-of-life adventure game with beautiful pixel art set in the late 90s rural Indonesia that tells a story about overcoming anxiety, depression, and the relationship between a boy and a girl with supernatural powers.', 9.99, 'PC, Nintendo Switch', 'Indie, Adventure'),
(452649, 'Resident Evil: Village', 'Experience survival horror like never before in the eighth major installment in the storied Resident Evil franchise - Resident Evil Village.\r\n\r\nSet a few years after the horrifying events in the critically acclaimed Resident Evil 7 biohazard, the all-new storyline begins with Ethan Winters and his wife Mia living peacefully in a new location, free from their past nightmares. Just as they are building their new life together, tragedy befalls them once again.', 39.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, iOS', 'Adventure, Action');
INSERT INTO `Games` (`id`, `name`, `description`, `price`, `platform`, `genres`) VALUES
(455597, 'It Takes Two', 'Bring your favorite co-op partner and together step into the shoes of May and Cody. As the couple is going through a divorce, through unknown means their minds are transported into two dolls which their daughter, Rose, made to represent them. Now they must reluctantly find a way to get back into their bodies, a quest which takes them through the most wild, unexpected and fantastical journey imaginable.', 7.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch', 'Platformer, Adventure, Action'),
(479694, 'Inscryption', 'From the creator of Pony Island and The Hex comes the latest mind melting, self-destructing love letter to video games. Inscryption is an inky black card-based odyssey that blends the deckbuilding roguelike, escape-room style puzzles, and psychological horror into a blood-laced smoothie. Darker still are the secrets inscrybed upon the cards...\r\nIn Inscryption you will...', 7.99, 'PC, PlayStation 5, PlayStation 4, Nintendo Switch, macOS, Linux', 'Indie, Strategy, Adventure'),
(484971, 'Chained Echoes', 'Take up your sword, channel your magic or board your Mech. Chained Echoes is a 16-bit SNES style RPG set in a fantasy world where dragons are as common as piloted mechanical suits. Follow a group of heroes as they explore a land filled to the brim with charming characters, fantastic landscapes and vicious foes. Can you bring peace to a continent where war has been waged for generations and betrayal lurks around every corner?', 9.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, macOS, Linux', 'Indie, RPG'),
(516111, 'Mass Effect: Legendary Edition', 'One person is all that stands between humanity and the greatest threat it’s ever faced. Relive the legend of Commander Shepard in the highly acclaimed Mass Effect trilogy with the Mass Effect™ Legendary Edition. Includes single-player base content and DLC from Mass Effect, Mass Effect 2, and Mass Effect 3, plus promo weapons, armors, and packs - all remastered and optimized for 4k Ultra HD.', 5.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X', 'Shooter, Adventure, Action, RPG'),
(545015, 'Disco Elysium: Final Cut', 'The final cut will be available at no extra cost to all current owners of disco elysium. original players expand their experience for free while new players can enjoy the new content from their first playthrough.', 9.99, 'PC, PlayStation 5, Xbox One, PlayStation 4, Xbox Series S/X, Nintendo Switch, iOS, macOS', 'Adventure, RPG'),
(566445, 'Gnosia', 'The Gnosia lie. Pretending to be human, they’ll get in close, trick and deceive, and then eliminate one victim at a time...\r\nThe crew of a drifting spaceship, facing off against a mysterious and deadly threat known as the “Gnosia” and having no idea who among them is really the enemy, formulate a desperate plan for survival. The most suspicious among them will be put into “cold sleep” one by one, in an effort to rid the ship of Gnosia.', 17.49, 'PC, PlayStation 5, PlayStation 4, Xbox One, Xbox Series S/X, Nintendo Switch, PS Vita', 'Adventure, RPG'),
(727315, 'Ratchet & Clank: Rift Apart', 'BLAST YOUR WAY THROUGH AN INTERDIMENSIONAL ADVENTURE\r\nThe intergalactic adventurers are back with a bang. Help them stop a robotic emperor intent on conquering cross-dimensional worlds, with their own universe next in the firing line.\r\n- Blast your way home with an arsenal of outrageous weaponry.\r\n- Experience the shuffle of dimensional rifts and dynamic gameplay.\r\n- Explore never-before-seen planets and alternate dimension versions of old favorites.', 23.99, 'PC, PlayStation 5', 'Adventure, Action');

-- --------------------------------------------------------

--
-- Table structure for table `Game_Images`
--

CREATE TABLE `Game_Images` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `is_cover` tinyint(1) DEFAULT 0,
  `filename` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Game_Images`
--

INSERT INTO `Game_Images` (`id`, `game_id`, `is_cover`, `filename`) VALUES
(1, 43252, 1, '/images/the-witcher-3-wild-hunt--blood-and-wine/cover.jpg'),
(2, 43050, 1, '/images/the-witcher-3-wild-hunt--hearts-of-stone/cover.jpg'),
(3, 339958, 1, '/images/persona-5-royal/cover.jpg'),
(4, 545015, 1, '/images/disco-elysium-final-cut/cover.jpg'),
(5, 3328, 1, '/images/the-witcher-3-wild-hunt/cover.jpg'),
(6, 385406, 1, '/images/dark-souls-iii-the-ringed-city/cover.jpg'),
(7, 28, 1, '/images/red-dead-redemption-2/cover.jpg'),
(8, 4200, 1, '/images/portal-2/cover.jpg'),
(9, 516111, 1, '/images/mass-effect-legendary-edition/cover.jpg'),
(10, 58175, 1, '/images/god-of-war-2018/cover.jpg'),
(11, 1452, 1, '/images/phoenix-wright-ace-attorney-trilogy/cover.jpg'),
(12, 59199, 1, '/images/divinity-original-sin-2---definitive-edition/cover.jpg'),
(13, 254545, 1, '/images/cuphead-the-delicious-last-course/cover.jpg'),
(14, 58813, 1, '/images/resident-evil-2/cover.jpg'),
(15, 4265, 1, '/images/persona-3-portable/cover.jpg'),
(16, 13536, 1, '/images/portal/cover.jpg'),
(17, 19457, 1, '/images/disciples-ii-rise-of-the-elves/cover.jpg'),
(18, 19445, 1, '/images/disciples-ii-galleans-return/cover.jpg'),
(19, 13537, 1, '/images/half-life-2/cover.jpg'),
(20, 58388, 1, '/images/zone-of-the-enders-the-2nd-runner---mrs/cover.jpg'),
(21, 455597, 1, '/images/it-takes-two/cover.jpg'),
(22, 3498, 1, '/images/grand-theft-auto-v/cover.jpg'),
(23, 51610, 1, '/images/dark-souls-remastered/cover.jpg'),
(24, 43737, 1, '/images/dark-souls-iii-ashes-of-ariandel/cover.jpg'),
(25, 366889, 1, '/images/monster-hunter-world-iceborne/cover.jpg'),
(26, 1458, 1, '/images/bug-princess/cover.jpg'),
(27, 58890, 1, '/images/need-for-speed-most-wanted/cover.jpg'),
(28, 622, 1, '/images/xcom-enemy-within/cover.jpg'),
(29, 307137, 1, '/images/live-a-live/cover.jpg'),
(30, 12447, 1, '/images/the-elder-scrolls-v-skyrim-special-edition/cover.jpg'),
(31, 58134, 1, '/images/marvels-spider-man/cover.jpg'),
(32, 19635, 1, '/images/fable-the-lost-chapters/cover.jpg'),
(33, 21974, 1, '/images/the-legend-of-heroes-trails-in-the-sky-the-3rd/cover.jpg'),
(34, 11498, 1, '/images/the-legend-of-heroes-trails-in-the-sky-sc/cover.jpg'),
(35, 4186, 1, '/images/persona-4-golden/cover.jpg'),
(36, 366885, 1, '/images/dragon-age-origins---ultimate-edition/cover.jpg'),
(37, 324997, 1, '/images/baldurs-gate-iii/cover.jpg'),
(38, 28568, 1, '/images/assassins-creed-ii/cover.jpg'),
(39, 42303, 1, '/images/bioshock-infinite-burial-at-sea---episode-two/cover.jpg'),
(40, 5563, 1, '/images/fallout-new-vegas/cover.jpg'),
(41, 19654, 1, '/images/samurai-gunn/cover.jpg'),
(42, 5679, 1, '/images/the-elder-scrolls-v-skyrim/cover.jpg'),
(43, 274755, 1, '/images/hades/cover.jpg'),
(44, 4544, 1, '/images/red-dead-redemption/cover.jpg'),
(45, 28199, 1, '/images/ori-and-the-will-of-the-wisps/cover.jpg'),
(46, 17959, 1, '/images/ori-and-the-blind-forest-definitive-edition/cover.jpg'),
(47, 428664, 1, '/images/a-space-for-the-unbound/cover.jpg'),
(48, 29153, 1, '/images/max-payne-2-the-fall-of-max-payne/cover.jpg'),
(49, 28623, 1, '/images/batman-arkham-city/cover.jpg'),
(50, 58550, 1, '/images/ghost-of-tsushima/cover.jpg'),
(51, 52201, 1, '/images/yakuza-6-the-song-of-life/cover.jpg'),
(52, 9767, 1, '/images/hollow-knight/cover.jpg'),
(53, 4439, 1, '/images/mass-effect-3/cover.jpg'),
(54, 591, 1, '/images/monument-valley/cover.jpg'),
(55, 17576, 1, '/images/batman-arkham-city---game-of-the-year-edition/cover.jpg'),
(56, 56184, 1, '/images/resident-evil-4-2005/cover.jpg'),
(57, 10389, 1, '/images/gothic-ii-gold-edition/cover.jpg'),
(58, 2551, 1, '/images/dark-souls-iii/cover.jpg'),
(59, 45958, 1, '/images/xcom-2-war-of-the-chosen/cover.jpg'),
(60, 4166, 1, '/images/mass-effect/cover.jpg'),
(61, 452649, 1, '/images/resident-evil-village/cover.jpg'),
(62, 14935, 1, '/images/total-war-shogun-2---fall-of-the-samurai/cover.jpg'),
(63, 4535, 1, '/images/call-of-duty-4-modern-warfare/cover.jpg'),
(64, 727315, 1, '/images/ratchet--clank-rift-apart/cover.jpg'),
(65, 52884, 1, '/images/doom/cover.jpg'),
(66, 654, 1, '/images/stardew-valley/cover.jpg'),
(67, 13856, 1, '/images/katana-zero/cover.jpg'),
(68, 1682, 1, '/images/the-wolf-among-us/cover.jpg'),
(69, 13925, 1, '/images/prince-of-persia-warrior-within/cover.jpg'),
(70, 274757, 1, '/images/sayonara-wild-hearts/cover.jpg'),
(71, 50839, 1, '/images/baba-is-you/cover.jpg'),
(72, 4570, 1, '/images/dead-space-2008/cover.jpg'),
(73, 326243, 1, '/images/elden-ring/cover.jpg'),
(74, 484971, 1, '/images/chained-echoes/cover.jpg'),
(75, 10073, 1, '/images/divinity-original-sin-2/cover.jpg'),
(76, 479694, 1, '/images/inscryption/cover.jpg'),
(77, 50734, 1, '/images/sekiro-shadows-die-twice/cover.jpg'),
(78, 29642, 1, '/images/silent-hill-2-2001/cover.jpg'),
(79, 18080, 1, '/images/half-life/cover.jpg'),
(80, 4062, 1, '/images/bioshock-infinite/cover.jpg'),
(81, 3612, 1, '/images/hotline-miami/cover.jpg'),
(82, 115, 1, '/images/zero-escape-the-nonary-games/cover.jpg'),
(83, 20709, 1, '/images/tom-clancys-splinter-cell-chaos-theory/cover.jpg'),
(84, 422859, 1, '/images/nier-replicant-v122474487139/cover.jpg'),
(85, 566445, 1, '/images/gnosia/cover.jpg'),
(86, 1358, 1, '/images/papers-please/cover.jpg'),
(87, 23741, 1, '/images/monument-valley-2/cover.jpg'),
(88, 17572, 1, '/images/batman-arkham-asylum-game-of-the-year-edition/cover.jpg'),
(89, 1450, 1, '/images/inside/cover.jpg'),
(90, 4550, 1, '/images/dead-space-2/cover.jpg'),
(91, 42, 1, '/images/what-remains-of-edith-finch/cover.jpg'),
(92, 4248, 1, '/images/dishonored/cover.jpg'),
(93, 2454, 1, '/images/doom-2016/cover.jpg'),
(94, 11971, 1, '/images/space-rangers-hd-a-war-apart/cover.jpg'),
(95, 28154, 1, '/images/cuphead/cover.jpg'),
(96, 366881, 1, '/images/little-nightmares-ii/cover.jpg'),
(97, 262382, 1, '/images/disco-elysium/cover.jpg'),
(98, 10141, 1, '/images/nierautomata/cover.jpg'),
(99, 257192, 1, '/images/psychonauts-2/cover.jpg'),
(100, 13820, 1, '/images/the-elder-scrolls-iii-morrowind/cover.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `Game_Keys`
--

CREATE TABLE `Game_Keys` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `key_code` varchar(100) NOT NULL,
  `is_sold` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Orders`
--

CREATE TABLE `Orders` (
  `id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `key_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'customer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `email`, `password`, `role`, `is_active`) VALUES
(1, 'admin@ghos.com', '123456', 'admin', 1),
(2, 'seller@ghos.com', '123456', 'business', 1),
(3, 'gamer@ghos.com', '123456', 'customer', 1);

--
-- Indexes for dumped tables
--

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
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `fk_orders_user` (`user_id`),
  ADD KEY `fk_orders_key` (`key_id`),
  ADD KEY `fk_orders_game` (`game_id`);

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
-- AUTO_INCREMENT for table `Cart`
--
ALTER TABLE `Cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Games`
--
ALTER TABLE `Games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=727316;

--
-- AUTO_INCREMENT for table `Game_Images`
--
ALTER TABLE `Game_Images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `Game_Keys`
--
ALTER TABLE `Game_Keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Orders`
--
ALTER TABLE `Orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Cart`
--
ALTER TABLE `Cart`
  ADD CONSTRAINT `fk_cart_game` FOREIGN KEY (`game_id`) REFERENCES `Games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_orders_game` FOREIGN KEY (`game_id`) REFERENCES `Games` (`id`),
  ADD CONSTRAINT `fk_orders_key` FOREIGN KEY (`key_id`) REFERENCES `Game_Keys` (`id`),
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
