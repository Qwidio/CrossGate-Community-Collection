-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2026 at 11:35 AM
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
-- Table structure for table `achievement`
--

CREATE TABLE `achievement` (
  `libsIds` varchar(11) NOT NULL,
  `achievementIds` varchar(11) NOT NULL,
  `achievementName` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `achieverlist`
--

CREATE TABLE `achieverlist` (
  `achieverIds` varchar(100) NOT NULL,
  `libsIds` varchar(11) NOT NULL,
  `profileTags` text NOT NULL,
  `achievementIds` varchar(11) NOT NULL,
  `achievementName` text NOT NULL,
  `dates` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('29052026fc5fd02f31b3d6cb4946cbbb', 'welcome message & friendly reminder', 'taka21', 'CrossGateBugnFeedback-2362025', '05/29/2026', 'Regardless where you\'re coming from, all of you are welcomed here. I cannot ask more than to keep yourself mostly friendly here aight.', 'Publics', 'TRUE', 'empty.png'),
('812569tTnUhekKJdwoZasGlfrviNFLSDRzAHEjYWpxMqPX', 'test', 'taka21', 'CrossGate-9999', '2026-06-06', 'test', 'Deleted', 'NOs', 'empty.png');

-- --------------------------------------------------------

--
-- Table structure for table `groupaccess`
--

CREATE TABLE `groupaccess` (
  `ga_id` int(255) NOT NULL,
  `profileTags` varchar(500) NOT NULL,
  `passkeys` text NOT NULL,
  `roles` varchar(30) NOT NULL,
  `og_identification` varchar(1000) NOT NULL,
  `accountState` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groupaccess`
--

INSERT INTO `groupaccess` (`ga_id`, `profileTags`, `passkeys`, `roles`, `og_identification`, `accountState`) VALUES
(1, 'taka21', '3342ddebdc78ca54a1ee6434aafe9dac', 'founder', 'POROSIVE', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `groupinvite`
--

CREATE TABLE `groupinvite` (
  `iv_id` int(255) NOT NULL,
  `inviteToken` varchar(255) NOT NULL,
  `profileTags` varchar(500) NOT NULL,
  `og_identification` varchar(1000) NOT NULL,
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
  `og_identification` varchar(1000) NOT NULL,
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
(53, 'e154095a13d9bc1d2bd000e329e128f4764c7af48f3219e5de51a61a370886fd', 'taka21', 'POROSIVE', '127.0.0.1', 'Windows 10', '', '2026/06/26', '20/06/2026 08:11');

-- --------------------------------------------------------

--
-- Table structure for table `libslist`
--

CREATE TABLE `libslist` (
  `libsIds` varchar(100) NOT NULL,
  `libsPublisher` varchar(100) NOT NULL,
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
  `libsForum` text NOT NULL,
  `recspecs` varchar(100) NOT NULL DEFAULT '24a2w11u22',
  `devstats` varchar(50) NOT NULL,
  `devstatdesc` text NOT NULL,
  `libsState` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `libslist`
--

INSERT INTO `libslist` (`libsIds`, `libsPublisher`, `libsVT`, `libsAttachs`, `libsBanners`, `libsTitles`, `libsDesc`, `repolink`, `libsMD`, `extlink`, `addedDates`, `cltNumbs`, `libsType`, `libsCategorys`, `fdrLibs`, `libsForum`, `recspecs`, `devstats`, `devstatdesc`, `libsState`) VALUES
('CrossGates', 'POROSIVE', '', 'CrossGates_1781863366_c81581e6fa1b179d_cgcclogotrsp.png', '[\"cgbanner.png\"]', 'CGCC Launcher', 'Open-Source client for software &amp; games listed on CrossGate Website Library', 'https://github.com/Qwidio/CrossGate', 'https://raw.githubusercontent.com/Qwidio/CrossGate/refs/heads/main/README.md', '{\"official website\":[\"cgcc.porosive.com/client.php\"]}', '2/1/2026', 199, 'software', 'Apps0', '', 'CrossGate-9999', '48a4w10u22', 'earlyaccess', '-', 'publics'),
('NIIE393a570e02062026', 'POROSIVE', '', 'NIIE393a570e02062026_1781874301_86737ff0ed6038c4_NIElogotrsp.png', '[\"1_1780357641_0c238f46e42f2d87_NIEbanner.png\"]', 'N//E', 'Upcoming project soon to be made', 'https://github.com/Qwidio/NIE', 'https://raw.githubusercontent.com/Qwidio/NIE/refs/heads/main/README.md', '{\"linkname1\":[\"http://porosive.com/crossgate-web-utility/Library/core/view.php?type=clts&ids=NIE\"]}', '02/06/2026', 0, 'game', 'GamesNugg', '1780357679_NIIE393a570e02062026_20b8279a_cgccB1.zip', 'NIIE_topic_402e931d', '24a2w11u22', 'earlyaccess', '-', 'publics');

-- --------------------------------------------------------

--
-- Table structure for table `ogroup`
--

CREATE TABLE `ogroup` (
  `og_id` int(255) NOT NULL,
  `identification` varchar(1000) NOT NULL,
  `names` varchar(500) NOT NULL,
  `about` text NOT NULL,
  `founded` varchar(10) NOT NULL,
  `founder` varchar(500) NOT NULL,
  `admins` varchar(500) NOT NULL,
  `members` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`members`)),
  `logo` text NOT NULL,
  `banner` text NOT NULL,
  `sites` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `role_publish` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ogroup`
--

INSERT INTO `ogroup` (`og_id`, `identification`, `names`, `about`, `founded`, `founder`, `admins`, `members`, `logo`, `banner`, `sites`, `role_publish`) VALUES
(1, 'POROSIVE', 'POROSIVE STUDIO', 'Lurking in hidden rows', '12/12/2025', 'taka21', 'taka21', '[\"taka21\"]', 'prsvlogolight.png', 'porosive.png', '[{\"site\":\"porosive.com\",\"yt\":\"www.youtube.com/@porosive\"}]', 'C0rals');

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
('clientCrgs', 'crossgateprms.png', '[]', 'client', 'homepage', '6/6/2026', 'active'),
('crgs', 'crossgateprms.png', '[]', 'sftprms', 'TGCrossGate', '15/7/2026', 'inactive');

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
('taka21', '1779474730_8a3e57c0cd1ef300_cgcclogos.png', 'Taka', 'the first boi in there', '19/6/2025', '{}', 'Offline', '{\"lastLogin\":\"20-1-2026 09:11\",\"marked\":{\"CrossGates\":{\"libsIds\":\"CrossGates\",\"Hours\":5252,\"lastLog\":\"20-1-2025 10:15\"}},\"private\":true}', 'inactive');

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
  `lastlogs` varchar(20) NOT NULL DEFAULT 'unset'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessionlogs`
--

INSERT INTO `sessionlogs` (`logids`, `profileTags`, `sessiontokens`, `addrss`, `osids`, `client`, `expirationDate`, `lastlogs`) VALUES
(112, 'taka21', 'f81b8a269721d79ce05769dc08f2f5e6822855baddef56291ec2f68eb1e4fc13', '127.0.0.1', 'Windows 10', 'unset', '2026/06/26', '20/06/2026 08:34'),
(118, 'taka21', '8731833ef016877f24c2f0e7fb398f07e61b4f9ed875ed2cc2efa81e3e42f8eb', '::1', 'Ubuntu', 'unset', '2026/07/03', '18/06/2026 08:55');

-- --------------------------------------------------------

--
-- Table structure for table `specialbadge`
--

CREATE TABLE `specialbadge` (
  `badgeIds` int(100) NOT NULL,
  `badgeName` varchar(1000) NOT NULL,
  `badgeType` varchar(50) NOT NULL,
  `badgeRefs` varchar(100) NOT NULL,
  `badgeDesc` varchar(1000) NOT NULL,
  `icon` varchar(1000) NOT NULL
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
('CrossGate-9999', 'CrossGate', '5/7/2025', 'Crossgate official forum topic', 'Publics', 'empty.png', 'publisherOnly'),
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
(99, 'taka21', 'taka', '3342ddebdc78ca54a1ee6434aafe9dac', 'uk@gmail.com', 'approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievement`
--
ALTER TABLE `achievement`
  ADD PRIMARY KEY (`achievementIds`);

--
-- Indexes for table `achieverlist`
--
ALTER TABLE `achieverlist`
  ADD PRIMARY KEY (`achieverIds`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`apiId`);

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
  ADD UNIQUE KEY `identification` (`identification`) USING HASH;

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
-- Indexes for table `specialbadge`
--
ALTER TABLE `specialbadge`
  ADD PRIMARY KEY (`badgeIds`);

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
-- AUTO_INCREMENT for table `groupaccess`
--
ALTER TABLE `groupaccess`
  MODIFY `ga_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `groupinvite`
--
ALTER TABLE `groupinvite`
  MODIFY `iv_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `groupsession`
--
ALTER TABLE `groupsession`
  MODIFY `gs_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `ogroup`
--
ALTER TABLE `ogroup`
  MODIFY `og_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `reportIds` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessionlogs`
--
ALTER TABLE `sessionlogs`
  MODIFY `logids` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `specialbadge`
--
ALTER TABLE `specialbadge`
  MODIFY `badgeIds` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `groupaccess`
--
ALTER TABLE `groupaccess`
  ADD CONSTRAINT `groupaccess_ibfk_1` FOREIGN KEY (`profileTags`) REFERENCES `profiles` (`profileTags`);

--
-- Constraints for table `groupsession`
--
ALTER TABLE `groupsession`
  ADD CONSTRAINT `groupsession_ibfk_2` FOREIGN KEY (`profileTags`) REFERENCES `profiles` (`profileTags`);

--
-- Constraints for table `sessionlogs`
--
ALTER TABLE `sessionlogs`
  ADD CONSTRAINT `sessionlogs_ibfk_1` FOREIGN KEY (`profileTags`) REFERENCES `profiles` (`profileTags`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
