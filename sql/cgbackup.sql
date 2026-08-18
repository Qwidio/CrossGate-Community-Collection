-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2026 at 05:12 AM
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
-- Database: `cgbackup`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `apiId` varchar(255) NOT NULL,
  `og_identification` varchar(1000) NOT NULL,
  `useScope` varchar(100) DEFAULT NULL,
  `hashedKeys` varchar(255) DEFAULT NULL,
  `addedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`apiId`, `og_identification`, `useScope`, `hashedKeys`, `addedDate`, `active`) VALUES
('3a0741edc4b7725d2473e2f9e887eba2', 'POROSIVE', 'Production', '9310aef665af94f5e6f40ac3f71708ba03c6231c9705ac530dda434ab2b951b0', '2026-06-14 02:40:00', 1),
('95cc48d97a428b87725e53b707aaa930', 'POROSIVE', 'Development', '62966fe03aab8f61997833c4faf9e325238abc1efa646b41842c2678c70feae2', '2026-06-14 02:40:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `badgegroup`
--

CREATE TABLE `badgegroup` (
  `bgIds` int(255) NOT NULL,
  `groupRefs` varchar(127) NOT NULL,
  `libsIds` varchar(200) NOT NULL,
  `badgeGroupTitle` varchar(500) NOT NULL,
  `badgeGroupDesc` varchar(2000) NOT NULL,
  `badgeList` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]',
  `icons` text NOT NULL,
  `state` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `badgegroup`
--

INSERT INTO `badgegroup` (`bgIds`, `groupRefs`, `libsIds`, `badgeGroupTitle`, `badgeGroupDesc`, `badgeList`, `icons`, `state`) VALUES
(1, 'CGCC', 'CGCC', 'CGCC 2026', 'Official 2026 Badges ', '[\"1\"]', '', 'publics');

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `badgeIds` varchar(100) NOT NULL,
  `badgeName` varchar(1000) NOT NULL,
  `badgeType` varchar(50) NOT NULL,
  `badgeRefs` varchar(127) NOT NULL,
  `badgeDesc` varchar(1000) NOT NULL,
  `icon` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`badgeIds`, `badgeName`, `badgeType`, `badgeRefs`, `badgeDesc`, `icon`) VALUES
('1', 'CROSSGATER', 'profile', 'CGCC', 'Have CrossGate on yo Desktop', 'cgcclogo.png');

-- --------------------------------------------------------

--
-- Table structure for table `categorys`
--

CREATE TABLE `categorys` (
  `categoryIds` varchar(100) NOT NULL,
  `categoryTitles` text NOT NULL,
  `categorytype` varchar(20) NOT NULL,
  `categoryState` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorys`
--

INSERT INTO `categorys` (`categoryIds`, `categoryTitles`, `categorytype`, `categoryState`) VALUES
('Apps0', 'Apps', 'category', 'publics'),
('cat001', 'Productivity', 'apps', 'publics'),
('cat006', 'Music & Audio', 'apps', 'publics'),
('cat007', 'Photography', 'apps', 'publics'),
('cat101', 'Action', 'games', 'publics'),
('cat102', 'Adventure', 'games', 'publics'),
('cat103', 'Arcade', 'games', 'publics'),
('cat104', 'Puzzle', 'games', 'publics'),
('cat105', 'Racing', 'games', 'publics'),
('cat106', 'Role Playing (RPG)', 'games', 'publics'),
('cat107', 'Simulation', 'games', 'publics'),
('cat108', 'Sports', 'games', 'publics'),
('cat109', 'Strategy', 'games', 'publics'),
('cat110', 'Casual', 'games', 'publics'),
('GamesNugg', 'Games', 'category', 'publics'),
('Tooledoeverythin', 'Tools & Utility', 'category', 'publics');

-- --------------------------------------------------------

--
-- Table structure for table `forumcomments`
--

CREATE TABLE `forumcomments` (
  `CommentIds` varchar(100) NOT NULL,
  `ForumIds` varchar(100) NOT NULL,
  `profileTags` varchar(1000) NOT NULL,
  `profileNames` text NOT NULL,
  `Comments` text NOT NULL,
  `CommentDates` varchar(10) NOT NULL,
  `CmVs` int(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `replyThreadId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forumcomments`
--

INSERT INTO `forumcomments` (`CommentIds`, `ForumIds`, `profileTags`, `profileNames`, `Comments`, `CommentDates`, `CmVs`, `type`, `replyThreadId`) VALUES
('06012026dda58441fcfb9daf135a5d4f', '29052026fc5fd02f31b3d6cb4946cbbb', 'taka21', 'Taka', 'Racism and discrimination is not tolerated here and generally anywhere else', '06/01/2026', 0, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `forums`
--

CREATE TABLE `forums` (
  `ForumIds` varchar(500) NOT NULL,
  `ForumTitles` varchar(1000) NOT NULL,
  `ForumCreator` varchar(500) NOT NULL,
  `ForumTopics` varchar(500) NOT NULL,
  `ForumDates` varchar(10) NOT NULL,
  `ForumContents` text NOT NULL,
  `ForumState` varchar(20) NOT NULL,
  `ForumHighlight` varchar(10) NOT NULL,
  `ForumAttachment` text NOT NULL DEFAULT 'empty.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forums`
--

INSERT INTO `forums` (`ForumIds`, `ForumTitles`, `ForumCreator`, `ForumTopics`, `ForumDates`, `ForumContents`, `ForumState`, `ForumHighlight`, `ForumAttachment`) VALUES
('29052026fc5fd02f31b3d6cb4946cbbb', 'welcome message & friendly reminder', 'taka21', 'CrossGateBugnFeedback-2362025', '05/29/2026', 'Regardless where you\'re coming from, all of you are welcomed here. I cannot ask more than to keep yourself mostly friendly here aight.', 'Publics', 'TRUE', 'empty.png');

-- --------------------------------------------------------

--
-- Table structure for table `groupaccess`
--

CREATE TABLE `groupaccess` (
  `ga_id` int(255) NOT NULL,
  `profileTags` varchar(500) NOT NULL,
  `passkeys` text NOT NULL,
  `roles` varchar(30) NOT NULL,
  `og_identification` varchar(600) NOT NULL,
  `accountState` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groupaccess`
--

INSERT INTO `groupaccess` (`ga_id`, `profileTags`, `passkeys`, `roles`, `og_identification`, `accountState`) VALUES
(1, 'taka21', '3342ddebdc78ca54a1ee6434aafe9dac', 'founder', 'POROSIVE', 'approved'),
(11, 'raka_NoynAOGKBx_1808', 'e5b2a975d9b73165bcc8b5e63ce488ff', 'administrator', 'POROSIVE', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `groupinvite`
--

CREATE TABLE `groupinvite` (
  `iv_id` int(255) NOT NULL,
  `inviteToken` varchar(255) NOT NULL,
  `profileTags` varchar(500) NOT NULL,
  `og_identification` varchar(600) NOT NULL,
  `custom_msg` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groupsession`
--

CREATE TABLE `groupsession` (
  `gs_id` int(255) NOT NULL,
  `token` varchar(100) NOT NULL,
  `profileTags` varchar(500) NOT NULL,
  `og_identification` varchar(600) NOT NULL,
  `addrss` varchar(255) NOT NULL,
  `osids` varchar(100) NOT NULL,
  `client` varchar(200) NOT NULL,
  `expirationDate` varchar(11) NOT NULL,
  `lastlogs` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groupsession`
--

INSERT INTO `groupsession` (`gs_id`, `token`, `profileTags`, `og_identification`, `addrss`, `osids`, `client`, `expirationDate`, `lastlogs`) VALUES
(93, '2aa28f5333f7da2d460b61772761ad3da142599b80f290670675d0afd095da1a', 'raka_NoynAOGKBx_1808', 'POROSIVE', '127.0.0.1', 'Windows 10', '', '2026/08/28', '13/08/2026 07:50'),
(100, '021ea19fc5fc33632e304b2cb7f643b5f365be10ede4c7db547d450c93c96c98', 'taka21', 'POROSIVE', '127.0.0.1', 'Windows 10', '', '2026/09/02', '18/08/2026 04:56');

-- --------------------------------------------------------

--
-- Table structure for table `libslist`
--

CREATE TABLE `libslist` (
  `libsIds` varchar(200) NOT NULL,
  `libsPublisher` varchar(600) NOT NULL,
  `libsVT` varchar(30) NOT NULL,
  `libsAttachs` text NOT NULL,
  `libsBanners` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`libsBanners`)),
  `libsTitles` varchar(1000) NOT NULL,
  `libsDesc` varchar(2000) NOT NULL,
  `repolink` varchar(1000) NOT NULL,
  `libsMD` text NOT NULL,
  `extlink` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`extlink`)),
  `addedDates` varchar(10) NOT NULL,
  `cltNumbs` int(20) NOT NULL,
  `libsType` varchar(100) NOT NULL DEFAULT 'software',
  `libsCategorys` varchar(500) NOT NULL,
  `fdrLibs` text NOT NULL,
  `rollbacks` text NOT NULL,
  `detailData` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT ' { "fdrLibs":{"executables": "", "uninst": "none", "ver": ""}, "rollbacks":{"executables": "", "uninst": "none", "ver": ""}, "theme": "light" }',
  `recspecs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{ "cpu":"2 core/2 thread", "ram":"4GB DDR4", "gpu":"GTX 960 2GB/ RX 460 2GB", "win":"10 or Newer", "linux":"not supported", "mac":"not supported" }',
  `libsForum` text NOT NULL,
  `devstats` varchar(50) NOT NULL,
  `devstatdesc` text NOT NULL,
  `libsState` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `libslist`
--

INSERT INTO `libslist` (`libsIds`, `libsPublisher`, `libsVT`, `libsAttachs`, `libsBanners`, `libsTitles`, `libsDesc`, `repolink`, `libsMD`, `extlink`, `addedDates`, `cltNumbs`, `libsType`, `libsCategorys`, `fdrLibs`, `rollbacks`, `detailData`, `recspecs`, `libsForum`, `devstats`, `devstatdesc`, `libsState`) VALUES
('NIIE393a570e02062026', 'POROSIVE', '', 'NIIE393a570e02062026_1781874301_86737ff0ed6038c4_NIElogotrsp.png', '[\"1_1780357641_0c238f46e42f2d87_NIEbanner.png\"]', 'N//E', 'Upcoming project soon to be made', 'https://github.com/Qwidio/NIE', 'https://raw.githubusercontent.com/Qwidio/CrossGate-Community-Collection/refs/heads/main/README.md', '{\"official website\":\"cgcc.porosive.com/client.php\"}', '02/06/2026', 0, 'game', 'GamesNugg', '1785084210_NIIE393a570e02062026_b5020ecd_NamelessLow.zip', '1785084210_NIIE393a570e02062026_b5020ecd_NamelessLow.zip', '{\"fdrLibs\":{\"executables\":\"NamelessLow.exe\",\"uninst\":\"none\",\"ver\":\"0.1.0\"},\"rollbacks\":{\"executables\":\"NamelessLow.exe\",\"uninst\":\"none\",\"ver\":\"0.1.0\"},\"theme\":\"dark\"}', '{\"cpu\":\"2 core/2 thread\",\"ram\":\"4GB DDR4\",\"gpu\":\"GTX 960 2GB/ RX 460 2GB\",\"win\":\"10 or Newer\",\"linux\":\"not supported\",\"mac\":\"not supported\"}', 'NIIE_topic_402e931d', 'earlyaccess', '-', 'publics');

-- --------------------------------------------------------

--
-- Table structure for table `ogroup`
--

CREATE TABLE `ogroup` (
  `og_id` int(255) NOT NULL,
  `identification` varchar(600) NOT NULL,
  `names` varchar(400) NOT NULL,
  `about` text NOT NULL,
  `founded` varchar(10) NOT NULL,
  `founder` varchar(500) NOT NULL,
  `members` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`members`)),
  `logo` text NOT NULL,
  `banner` text NOT NULL,
  `sites` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ogroup`
--

INSERT INTO `ogroup` (`og_id`, `identification`, `names`, `about`, `founded`, `founder`, `members`, `logo`, `banner`, `sites`) VALUES
(1, 'POROSIVE', 'POROSIVE STUDIO', 'Lurking in hidden rows', '12/12/2025', 'taka21', '[\"taka21\",\"raka_NoynAOGKBx_1808\"]', '1782035651_f67a6221eb44cb2f_prsvlogobglight.png', 'porosive.png', '[{\"site\":\"porosive.com\",\"yt\":\"www.youtube.com/@porosive\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `prms`
--

CREATE TABLE `prms` (
  `prmsIds` varchar(100) NOT NULL,
  `bannerRefImg` text NOT NULL,
  `prmsArr` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`prmsArr`)),
  `type` varchar(100) NOT NULL,
  `refLinks` text NOT NULL,
  `bannerDates` varchar(11) NOT NULL,
  `prmState` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prms`
--

INSERT INTO `prms` (`prmsIds`, `bannerRefImg`, `prmsArr`, `type`, `refLinks`, `bannerDates`, `prmState`) VALUES
('clientCrgs', 'crossgateprms.png', '[]', 'client', 'homepage', '6/6/2026', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `profileTags` varchar(500) NOT NULL,
  `profileAttachs` varchar(1000) NOT NULL DEFAULT 'empty',
  `profileNames` varchar(255) NOT NULL,
  `profileBios` text NOT NULL,
  `profileJDates` varchar(10) NOT NULL,
  `Badge` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}',
  `oState` varchar(10) NOT NULL DEFAULT 'Offline',
  `mkot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`mkot`)),
  `allowInvite` varchar(10) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`profileTags`, `profileAttachs`, `profileNames`, `profileBios`, `profileJDates`, `Badge`, `oState`, `mkot`, `allowInvite`) VALUES
('raka_NoynAOGKBx_1808', 'empty', 'raka', '', '18/07/2026', '{\"1\":\"14/08/2026 05:37\"}', 'Offline', '{\"marked\":{\"NIIE393a570e02062026\":{\"libsIds\":\"NIIE393a570e02062026\",\"Hours\":126.24,\"lastLog\":\"13/08/2026 02:22:45\"}},\"private\":false,\"favbadge\":\"1\",\"themes\":\"4\"}', 'active'),
('taka21', '1782035884_5a4f290b1d2b698b_cgcclogo.png', 'Taka', 'the first boi in here', '19/6/2025', '{\"1\":\"24/3/2026 01:23\"}', 'Offline', '{\"lastLogin\":\"20-1-2026 09:11\",\"marked\":{\"CrossGates\":{\"libsIds\":\"CrossGates\",\"Hours\":5252,\"lastLog\":\"20/1/2025 10:15\"},\"NIIE393a570e02062026\":{\"libsIds\":\"NIIE393a570e02062026\",\"Hours\":0,\"lastLog\":\"3/7/2026 11:12\"}},\"private\":false,\"favbadge\":\"none\",\"themes\":\"4\"}', 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `reportIds` int(255) NOT NULL,
  `reporters` varchar(1000) NOT NULL,
  `reportedIds` varchar(1000) NOT NULL,
  `reportSource` varchar(255) NOT NULL,
  `reportReason` varchar(255) NOT NULL,
  `fullcontext` text NOT NULL,
  `dates` varchar(20) NOT NULL DEFAULT current_timestamp(),
  `capture` varchar(2000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessionlogs`
--

CREATE TABLE `sessionlogs` (
  `logids` int(255) NOT NULL,
  `profileTags` varchar(255) NOT NULL,
  `sessiontokens` varchar(100) NOT NULL,
  `addrss` varchar(255) NOT NULL DEFAULT 'unset',
  `osids` varchar(100) NOT NULL DEFAULT 'unset',
  `client` varchar(200) NOT NULL DEFAULT 'unset',
  `expirationDate` varchar(11) NOT NULL,
  `lastlogs` varchar(20) NOT NULL DEFAULT 'unset',
  `isRunningClts` tinyint(1) NOT NULL DEFAULT 0,
  `lastCltsRun` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `topicIds` varchar(100) NOT NULL,
  `topicTitles` varchar(500) NOT NULL,
  `topicDates` varchar(10) NOT NULL,
  `topicContents` varchar(2000) NOT NULL,
  `topicState` varchar(20) NOT NULL,
  `topicAttachs` text NOT NULL DEFAULT 'empty.png',
  `topicType` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`topicIds`, `topicTitles`, `topicDates`, `topicContents`, `topicState`, `topicAttachs`, `topicType`) VALUES
('codinghelp', 'Coding Help', '13/6/2026', 'Ask questions and get assistance with programming challenges', 'Publics', '\'empty.png\'', 'all'),
('cplusplus', 'C++ Programming', '13/6/2026', 'Topics covering C++ programming, algorithms, and software development', 'Publics', '\'empty.png\'', 'all'),
('CrossGateBugnFeedback-2362025', 'CrossGate Bug & Feedback', '23/6/2025', 'Dedicated topic for those stuff, if you encounter one please share it there about the detail and how can you encounter it ', 'Publics', 'empty.png', 'all'),
('cybersecurity', 'Cyber Security', '13/6/2026', 'Discussions about security, ethical hacking, and data protection', 'Publics', '\'empty.png\'', 'all'),
('databaseworld', 'Database Systems', '13/6/2026', 'Discussion about MySQL, PostgreSQL, database design, and optimization', 'Publics', '\'empty.png\'', 'all'),
('esportsarena', 'Esports Arena', '13/6/2026', 'Competitive gaming discussions, tournaments, and esports news', 'Publics', '\'empty.png\'', 'all'),
('gamedev', 'Game Development', '13/6/2026', 'Creating games using various engines, tools, and programming languages', 'Publics', '\'empty.png\'', 'all'),
('hellogaming', 'Gaming', '23/6/2025', 'Topic covering about gaming in general', 'Publics', '\'empty.png\'', 'all'),
('indiegames', 'Indie Games', '13/6/2026', 'Exploring independent game development and indie game releases', 'Publics', '\'empty.png\'', 'all'),
('javascriptdev', 'JavaScript Development', '13/6/2026', 'Learning and discussing JavaScript for web and application development', 'Publics', '\'empty.png\'', 'all'),
('mobilegaming', 'Mobile Gaming', '13/6/2026', 'Topics related to Android and iOS gaming experiences', 'Publics', '\'empty.png\'', 'all'),
('NIIE_topic_402e931d', 'N//E announcement', '02/06/2026', 'N//Eannouncement topics', 'Publics', 'empty.png', 'publisherOnly'),
('opensource', 'Open Source', '13/6/2026', 'Sharing and contributing to open-source software projects', 'Publics', '\'empty.png\'', 'all'),
('pcgaming', 'PC Gaming', '13/6/2026', 'Discussion about PC gaming, hardware, and game performance', 'Publics', '\'empty.png\'', 'all'),
('pythoncoding', 'Python Coding', '13/6/2026', 'Programming discussions, projects, and tips using Python', 'Publics', '\'empty.png\'', 'all'),
('retrogaming', 'Retro Gaming', '13/6/2026', 'Discussion about classic consoles, arcade games, and retro gaming culture', 'Publics', '\'empty.png\'', 'all'),
('webdev', 'Web Development', '13/6/2026', 'Frontend and backend web development discussions and tutorials', 'Publics', '\'empty.png\'', 'all');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `profileTags` varchar(500) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `userState` varchar(100) NOT NULL DEFAULT 'approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `profileTags`, `username`, `password`, `Email`, `userState`) VALUES
(99, 'taka21', 'taka', '3342ddebdc78ca54a1ee6434aafe9dac', 'qwidqwudpro@gmail.com', 'approved'),
(118, 'raka_NoynAOGKBx_1808', 'raka', 'e5b2a975d9b73165bcc8b5e63ce488ff', 'rakaraka@raka.com', 'approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`apiId`);

--
-- Indexes for table `badgegroup`
--
ALTER TABLE `badgegroup`
  ADD PRIMARY KEY (`bgIds`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`badgeIds`);

--
-- Indexes for table `categorys`
--
ALTER TABLE `categorys`
  ADD PRIMARY KEY (`categoryIds`);

--
-- Indexes for table `forumcomments`
--
ALTER TABLE `forumcomments`
  ADD PRIMARY KEY (`CommentIds`),
  ADD KEY `ForumIds` (`ForumIds`);

--
-- Indexes for table `forums`
--
ALTER TABLE `forums`
  ADD UNIQUE KEY `ForumIds` (`ForumIds`),
  ADD KEY `ForumTopics` (`ForumTopics`);

--
-- Indexes for table `groupaccess`
--
ALTER TABLE `groupaccess`
  ADD PRIMARY KEY (`ga_id`),
  ADD KEY `profileTags` (`profileTags`);

--
-- Indexes for table `groupinvite`
--
ALTER TABLE `groupinvite`
  ADD PRIMARY KEY (`iv_id`);

--
-- Indexes for table `groupsession`
--
ALTER TABLE `groupsession`
  ADD PRIMARY KEY (`gs_id`),
  ADD KEY `profileTags` (`profileTags`);

--
-- Indexes for table `libslist`
--
ALTER TABLE `libslist`
  ADD PRIMARY KEY (`libsIds`),
  ADD KEY `libsCategorys` (`libsCategorys`);

--
-- Indexes for table `ogroup`
--
ALTER TABLE `ogroup`
  ADD PRIMARY KEY (`og_id`),
  ADD UNIQUE KEY `identification` (`identification`);

--
-- Indexes for table `prms`
--
ALTER TABLE `prms`
  ADD PRIMARY KEY (`prmsIds`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD UNIQUE KEY `profileTags` (`profileTags`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`reportIds`);

--
-- Indexes for table `sessionlogs`
--
ALTER TABLE `sessionlogs`
  ADD PRIMARY KEY (`logids`),
  ADD KEY `profileTags` (`profileTags`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`topicIds`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `badgegroup`
--
ALTER TABLE `badgegroup`
  MODIFY `bgIds` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `groupaccess`
--
ALTER TABLE `groupaccess`
  MODIFY `ga_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `groupinvite`
--
ALTER TABLE `groupinvite`
  MODIFY `iv_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `groupsession`
--
ALTER TABLE `groupsession`
  MODIFY `gs_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `ogroup`
--
ALTER TABLE `ogroup`
  MODIFY `og_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `reportIds` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessionlogs`
--
ALTER TABLE `sessionlogs`
  MODIFY `logids` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `groupaccess`
--
ALTER TABLE `groupaccess`
  ADD CONSTRAINT `groupaccess_ibfk_1` FOREIGN KEY (`profileTags`) REFERENCES `profiles` (`profileTags`);

--
-- Constraints for table `sessionlogs`
--
ALTER TABLE `sessionlogs`
  ADD CONSTRAINT `sessionlogs_ibfk_1` FOREIGN KEY (`profileTags`) REFERENCES `profiles` (`profileTags`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
