-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 05:26 AM
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
-- Database: `eventhorizan`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','club_admin') NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `role`, `club_id`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NULL, '2025-12-17 04:23:30', '2025-12-17 04:23:30'),
(2, 'cata123', '$2y$10$UA/oEtoPGG/XQqBgH938o.2Mw3HiK7/f4ahVPwlc0IGo/bAo7X9G6', 'club_admin', 22, '2025-12-17 04:27:26', '2025-12-17 04:27:26'),
(4, 'ineshkavinda@gmail.com', '$2y$10$UYEuNgENtmbD0kCvOHo.z.WTqDMNtdcPFjCLR5CjsTGUR5NY4Hp0m', 'club_admin', 23, '2025-12-18 05:23:28', '2025-12-18 05:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL,
  `club_name` varchar(255) NOT NULL,
  `club_description` text DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `club_main_image` varchar(255) DEFAULT NULL,
  `club_extra_image_1` varchar(255) DEFAULT NULL,
  `club_extra_image_2` varchar(255) DEFAULT NULL,
  `club_extra_image_3` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `contact_description_1` varchar(255) DEFAULT NULL,
  `contact_number_1` varchar(20) DEFAULT NULL,
  `contact_number_2` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `club_name`, `club_description`, `short_description`, `club_main_image`, `club_extra_image_1`, `club_extra_image_2`, `club_extra_image_3`, `created_at`, `contact_description_1`, `contact_number_1`, `contact_number_2`) VALUES
(1, 'Photography Club', 'A creative community for students passionate about photography. The club organizes photo walks, competitions, and workshops on lighting, editing, and storytelling through images. Members get the chance to showcase their work in exhibitions and collaborate on visual projects across the campus.', 'Capturing creativity through lenses, lighting, and storytelling.', 'photography_main.jpg', 'photography_event1.jpg', 'photography_event2.jpg', 'photography_event3.jpg', '2025-11-07 10:51:18', 'For membership, event details, or photo submissions, please contact the club coordinator.', '+94 75 869 0018', '+94 77 345 6789'),
(5, 'Tech Innovators Club', 'A student-led community at APIIT Sri Lanka focused on developing innovative software and hardware projects. The club conducts coding workshops, hackathons, and tech talks to help members enhance their technical and teamwork skills while exploring the latest trends in technology.', 'Inspiring innovation through coding, teamwork, and technology exploration.', '1763965978_1737655587363.jpg', '1763965978_photo-of-techshop-a-community-workshop-in-menlo-park-calif.webp', '1763966038_a63566_f3a830d1b4154b70892c11ec608e5d72~mv2.png', '1763966038_innovation.png', '2025-11-11 12:10:22', 'For membership, event details, or collaborations, please reach out to our club coordinator.', '+94 71 456 7890', '+94 77 234 5678'),
(16, 'Rotract club', 'A student–led organization focused on leadership, community service, and professional development through meaningful projects and events.', 'Leading students in service, leadership, and community development through impactful Rotaract initiatives.', '1763966593_rotaract_club_of_thane_suburban_cover.jpg', '1763966593_super-rotaract.jpg', '1763966593_1-A-Night-of-Legacy-and-Leadership-_-The-6th-Installation-Ceremony-of-the-Rotaract-Club-of-APIIT.jpg', NULL, '2025-11-24 12:13:13', 'For inquiries, membership details, and event collaborations, reach out to our Rotaract coordination team.', '077 123 4567', '071 987 6543'),
(17, 'Student Activity Club', 'The Student Activity Club of APIIT Campus aims to represent the entire student population of APIIT by enhancing them in extracurricular activities and building social-cultural bond among each one of them KSAC represents the entire student community at APIIT Kandy Campus and acts as a forum to express their opinions and concerns. Creating a direct link between KSAC and students, many social, sports and community events are organized and guided by KSAC. All the sports function under the KSAC.', 'Enhances student life via sports, culture, and social engagement.', '1763998594_APIIT-Kandy-Student-Activity-Club.jpg', '1763998594_after-school-activities-isolated-concept-260nw-2430956813.webp', '1763998594_volunteer-work-community-service-isolated-cartoon-vector-illustration-high-school-activity-student-club-ecological-261545339.webp', '1763998594_wordle.jpg', '2025-11-24 21:06:34', 'A club that organizes inter-university sports events like Extravaganza', '011-9876-543', '011-9876-543'),
(18, 'Entrepreneurship Club (E-Club)', 'The club cultivates entrepreneurial skills, helping students develop startup ideas, run pitching competitions, and connect with mentors.', 'Support & mentor student entrepreneurs.', '1763998839_The-Entrepreneurship-Club.jpg.webp', '1763998839_transparentsquare.webp', '1763998839_eclublogo_color.png', '1763998839_channels4_profile.jpg', '2025-11-24 21:10:39', 'Hosts events like “Sandbox” pitching, movie nights, and collaborations.', '011-4455-990', '011-3344-221'),
(19, 'Toastmasters’ Club', 'Part of Toastmasters International; helps students build public speaking, presentation, and leadership skills.', 'Improves communication & leadership.', '1763999038_302049915_499362005526900_374590494539617144_n.jpg.webp', '1763999038_ti-club-meeting-7.jpg', '1763999038_01-Main-Photo.jpg', '1763999038_Image-4-Toastmasters-1024x1024.jpg', '2025-11-24 21:13:58', 'Meets regularly.  Toastmasters International', '011-7788-112', '011-8899-223'),
(20, 'AIESEC APIIT', 'AIESEC is a global youth organisation offering international internships, volunteer projects, and leadership training.', 'Global youth leadership and exchange.', '1763999215_AIESEC-APIIT.jpg', '1763999215_hqdefault.jpg', '1763999215_214.jpg', '1763999215_maxresdefault.jpg', '2025-11-24 21:16:55', 'Helps students gain international exposure and professional experience.', '011-6677-334', '011-5566-112'),
(21, 'Fullstack Computer Society (FCS)', 'A tech club focused on software engineering, innovation, peer learning, and bridging academic theory to real-world practice.', 'Coding, innovation & community for IT students.', '1763999415_Kandy-Computer-Society-.jpg', '1763999415_logo.png', '1763999415_Artboard-32-copy-13.webp', '1763999415_1743757297196.jpg', '2025-11-24 21:20:15', 'Organizes workshops, hackathons, “Coffee & Code” sessions, and peer-learning.', '011-9900-221', '011-1122-334'),
(22, 'Catalyst Club', 'This is a creative / film-making club at APIIT; they recently launched “Kagura” — a short film festival.', 'Platform for film and creative storytelling.', '1763999657_Catalyst.jpg', '1763999657_image_47a6023aed.jpg', '1763999657_WhatsApp Image 2025-11-24 at 9.23.50 PM.jpeg', '1763999657_The_Catalyst_Club_of_APIIT_Sri_Lanka_unveils_Kagura_2026_inter-university_short_film_festival_Image.jpg', '2025-11-24 21:24:17', 'Organises “Kagura” short film festival to promote student filmmaking.', '011-4433-556', '011-2244-667'),
(23, 'Hot Inesh Fans', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-17 11:49:00', NULL, NULL, NULL),
(26, 'Dream society', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-25 10:35:27', NULL, NULL, NULL),
(27, 'tech kids', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-25 10:50:58', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `club_highlights`
--

CREATE TABLE `club_highlights` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text NOT NULL,
  `main_image` varchar(255) NOT NULL,
  `extra_image_1` varchar(255) DEFAULT NULL,
  `extra_image_2` varchar(255) DEFAULT NULL,
  `extra_image_3` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_highlights`
--

INSERT INTO `club_highlights` (`id`, `club_id`, `event_title`, `event_description`, `main_image`, `extra_image_1`, `extra_image_2`, `extra_image_3`, `created_at`) VALUES
(9, 23, 'cookery class', 'Cook Club is a creative student community that brings together food lovers who enjoy cooking, learning, and sharing culinary skills. The club encourages members to explore different cuisines, improve cooking techniques, and bond through hands-on sessions, workshops, and food-related events.', '1766372813_Screenshot 2025-05-16 155604.png', '1766372813_Screenshot 2025-05-29 114032.png', '1766372813_Screenshot 2025-05-21 113420.png', '1766372813_Screenshot 2025-06-03 104037.png', '2025-12-22 03:06:53'),
(10, 23, 'gvg', 'kugduf ufbfn fj db cb', '1766902496_IMG_E3142.JPG', NULL, NULL, NULL, '2025-12-28 06:14:56');

-- --------------------------------------------------------

--
-- Table structure for table `club_positions`
--

CREATE TABLE `club_positions` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_positions`
--

INSERT INTO `club_positions` (`id`, `club_id`, `name`, `role`, `photo`, `description`, `created_at`) VALUES
(1, 23, 'Inesh Kavinda', NULL, '6948af5518b64_leader_23.png', 'President\r\n\r\ndhbf uhbd duibd iub diubi dij vdnm fxkijnrvkj veijv ekn vi knr rvihbvoj', '2025-12-22 02:39:17'),
(2, 23, 'dilum peramuna', 'principal', '6948af8549f07_leader_23.png', 'Secretory', '2025-12-22 02:40:05'),
(3, 23, 'nethan', 'Ahdbd', '6948b3b50b576_leader_23.png', 'gdbhdbhbhdbhdbb gu dug duh duh duybdu dugd hud ud hybduh d', '2025-12-22 02:57:57'),
(4, 23, 'jhun doe', 'kdjbdijn dkddddd', '69512bb9e5610_leader_23.jpg', 'hkfghc', '2025-12-22 02:58:58');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `ticket_url` varchar(255) DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `extra_image_1` varchar(255) DEFAULT NULL,
  `extra_image_2` varchar(255) DEFAULT NULL,
  `extra_image_3` varchar(255) DEFAULT NULL,
  `club_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `venue`, `price`, `ticket_url`, `main_image`, `extra_image_1`, `extra_image_2`, `extra_image_3`, `club_id`, `created_at`) VALUES
(21, 'APIIT Talent Fest 2025', 'A vibrant showcase of talent including singing, dancing, stand-up comedy, short drama acts, and creative performances by APIIT students. The event brings together entertainment and creativity, offering a platform for students to express themselves. Winners receive recognition awards and performance opportunities at future APIIT events.', '2025-11-30 00:30:00', 'BMICH', 3000.00, 'https://www.google.com/search?q=inesh+is+hot&oq=inesh+is+hot+&gs_lcrp=EgZjaHJvbWUyBggAEEUYOTIHCAEQIRigATIHCAIQIRigATIHCAMQIRiPAjIHCAQQIRiPAtIBCTE0MjQ1ajBqN6gCCLACAfEFb6akfzhUKw8&sourceid=chrome&ie=UTF-8', '1764043023_e2.0.avif', '1764043023_e2.1.jpg', '1764043023_e2.2.webp', '1764043023_e2.3.jpg', 17, '2025-11-25 09:27:03'),
(22, 'APIIT Acoustic Night 2025', 'An intimate evening filled with live acoustic performances, original songs, and musical collaborations by talented APIIT musicians. The event features a relaxed atmosphere with soft lighting, open mic sessions, and opportunities for new artists to take the stage. A perfect event to unwind and enjoy soulful music.', '2025-12-03 09:31:00', 'Auditorium', 1000.00, 'https://www.google.com/search?q=inesh+is+hot&oq=inesh+is+hot+&gs_lcrp=EgZjaHJvbWUyBggAEEUYOTIHCAEQIRigATIHCAIQIRigATIHCAMQIRiPAjIHCAQQIRiPAtIBCTE0MjQ1ajBqN6gCCLACAfEFb6akfzhUKw8&sourceid=chrome&ie=UTF-8', '1764043902_e3.0.png', '1764043902_e3.2.jpg', '1764043902_e3.1.jpg', '1764043902_e3.3.jpg', 16, '2025-11-25 09:41:42'),
(23, 'APIIT Mobile Legends Trophy', 'A fast-paced Mobile Legends tournament where teams compete in an intense 5v5 format for glory and cash prizes. The event includes live commentary, spectator seating, and fun side challenges. Open to beginners and competitive players who want to showcase their teamwork and gaming strategy.', '2025-11-29 10:45:00', 'APIIT E-Sports Arena – Colombo Campus', 0.00, 'https://www.google.com/search?q=inesh+is+hot&oq=inesh+is+hot+&gs_lcrp=EgZjaHJvbWUyBggAEEUYOTIHCAEQIRigATIHCAIQIRigATIHCAMQIRiPAjIHCAQQIRiPAtIBCTE0MjQ1ajBqN6gCCLACAfEFb6akfzhUKw8&sourceid=chrome&ie=UTF-8', '1764044265_e4.0.jpg', '1764044265_e4.1.jpg', '1764044265_e4.2.jpg', '1764044265_e4.3.jpg', 22, '2025-11-25 09:47:45'),
(24, 'Mindfulness & Stress Relief Workshop', 'A calming and interactive session focused on mental well-being, stress management, and relaxation techniques for students. The workshop includes guided meditation, breathing exercises, and tips for balancing academic and personal life. Led by a certified mindfulness instructor.', '2025-11-29 12:00:00', 'L4CR5', 0.00, 'https://www.google.com/search?q=inesh+is+hot&oq=inesh+is+hot+&gs_lcrp=EgZjaHJvbWUyBggAEEUYOTIHCAEQIRigATIHCAIQIRigATIHCAMQIRiPAjIHCAQQIRiPAtIBCTE0MjQ1ajBqN6gCCLACAfEFb6akfzhUKw8&sourceid=chrome&ie=UTF-8', '1764044482_e5.0.jpg', '1764044482_e5.1.jpg', '1764044482_e5.2.jpg', '1764044482_e5.3.jpg', 21, '2025-11-25 09:51:22'),
(25, 'Drone Building & Flight Training Session', 'A comprehensive workshop where students learn to assemble, configure, and fly beginner-level drones. The session covers drone components, safety protocols, flight techniques, and troubleshooting common issues. Participants also get hands-on practice flying drones on the rooftop under expert supervision.', '2025-11-29 16:30:00', 'APIIT Innovation Lab & Rooftop', 0.00, 'https://www.google.com/search?q=inesh+is+hot&oq=inesh+is+hot+&gs_lcrp=EgZjaHJvbWUyBggAEEUYOTIHCAEQIRigATIHCAIQIRigATIHCAMQIRiPAjIHCAQQIRiPAtIBCTE0MjQ1ajBqN6gCCLACAfEFb6akfzhUKw8&sourceid=chrome&ie=UTF-8', '1764045000_e6.0.jpg', '1764045000_e6.1.jpg', '1764045000_e6.2.jpg', '1764045000_e6.3.jpg', 20, '2025-11-25 10:00:00'),
(29, 'inesh event 1', 'Cook Club is a creative student community that brings together food lovers who enjoy cooking, learning, and sharing culinary skills. The club encourages members to explore different cuisines, improve cooking techniques, and bond through hands-on sessions, workshops, and food-related events.', '2026-01-08 10:00:00', 'ineshes home', 1300.00, 'https://www.bing.com/search?pglt=297&q=inesh+is+hot&cvid=bd1ec754f1d74839b93deb61cd7d8d7e&gs_lcrp=EgRlZGdlKgYIABBFGDkyBggAEEUYOTIGCAEQABhAMgYIAhAAGEAyBggDEAAYQDIGCAQQABhAMgYIBRAAGEDSAQg1OTcyajBqMagCALACAA&FORM=ANNTA1&ucpdpc=UCPD&PC=U531', '1766374007_Screenshot 2025-05-16 155547.png', '1766374007_Screenshot 2025-05-16 155456.png', '1766374007_Screenshot 2025-05-16 155340.png', '1766374007_Screenshot 2025-05-16 155437.png', 23, '2025-12-22 08:56:47'),
(30, 'homer scout', 'Tech Club is a student-driven community focused on technology, innovation, and problem-solving. The club provides a platform for members to learn, collaborate, and explore areas such as software development, emerging technologies, and digital creativity through workshops, projects, and events.', '2026-01-12 22:30:00', 'hootel transelvenia', 20.00, 'https://www.bing.com/search?pglt=297&q=inesh+is+hot&cvid=bd1ec754f1d74839b93deb61cd7d8d7e&gs_lcrp=EgRlZGdlKgYIABBFGDkyBggAEEUYOTIGCAEQABhAMgYIAhAAGEAyBggDEAAYQDIGCAQQABhAMgYIBRAAGEDSAQg1OTcyajBqMagCALACAA&FORM=ANNTA1&ucpdpc=UCPD&PC=U531', '1766385317_Screenshot 2025-05-16 155516.png', '1766385317_IMG_E3142.JPG', '1766385317_Screenshot 2025-05-16 155604.png', '1766385317_Screenshot 2025-06-03 110558.png', 23, '2025-12-22 12:05:17');

-- --------------------------------------------------------

--
-- Table structure for table `event_likes`
--

CREATE TABLE `event_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_likes`
--

INSERT INTO `event_likes` (`id`, `user_id`, `event_id`, `created_at`) VALUES
(1, 5, 30, '2025-12-22 06:38:57'),
(6, 5, 29, '2025-12-28 13:23:44'),
(9, 5, 22, '2025-12-28 13:26:59');

-- --------------------------------------------------------

--
-- Table structure for table `event_reviews`
--

CREATE TABLE `event_reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_reviews`
--

INSERT INTO `event_reviews` (`id`, `user_id`, `event_id`, `review_text`, `created_at`) VALUES
(2, 5, 22, 'kjnfondjn', '2025-12-25 06:47:26'),
(4, 5, 23, 'hbdiybd im inesh', '2025-12-25 06:51:23'),
(5, 6, 23, 'hbsiunsijn undjundn', '2025-12-25 06:53:36'),
(6, 6, 22, 'hgdgytdtg', '2025-12-28 06:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reminders`
--

INSERT INTO `reminders` (`id`, `user_id`, `event_id`, `created_at`) VALUES
(24, 1, 23, '2025-11-25 04:40:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created_at`) VALUES
(1, 'ineshfernando643@gmail.com', '$2y$10$LBtPstSlUxrt97Tr2LNUne2gs/311HxAVYzop.AR8faPrIRg3xOH6', '2025-11-06 14:05:45'),
(3, 'fernando@gmail.com', '$2y$10$lIO4u9alICFgTJsl7tZWpuerM/ItVdUTydiWaib8o.9GMRrmRM/u.', '2025-11-13 11:24:12'),
(4, 'fernando22@gmail.com', '$2y$10$9x8AyVRMDBAjYgbOMO4CoON2P/ovr.M6kw6iezoXLAKbnuX5gy2HK', '2025-11-24 04:53:38'),
(5, 'ineshkavinda@gmail.com', '$2y$10$aJRFX56p1sxBz43TtiPy2eHCFmuc2DFcwxLdgq/wbJOeWgOa1sLf2', '2025-12-18 05:14:35'),
(6, 'jijdpojdpok@jdjd.com', '$2y$10$UM01QU5kkdsultyndmAp7.TTf17NPKS.gG2jA2VgPxxlquRwBX4Dq', '2025-12-25 06:53:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_admin_role` (`role`),
  ADD KEY `idx_admin_club` (`club_id`);

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `club_highlights`
--
ALTER TABLE `club_highlights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `club_positions`
--
ALTER TABLE `club_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_events_clubs` (`club_id`);

--
-- Indexes for table `event_likes`
--
ALTER TABLE `event_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_event_unique` (`user_id`,`event_id`),
  ADD KEY `fk_event_likes_event` (`event_id`);

--
-- Indexes for table `event_reviews`
--
ALTER TABLE `event_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_review` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `club_highlights`
--
ALTER TABLE `club_highlights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `club_positions`
--
ALTER TABLE `club_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `event_likes`
--
ALTER TABLE `event_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `event_reviews`
--
ALTER TABLE `event_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD CONSTRAINT `fk_admin_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_highlights`
--
ALTER TABLE `club_highlights`
  ADD CONSTRAINT `club_highlights_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_positions`
--
ALTER TABLE `club_positions`
  ADD CONSTRAINT `fk_club_leaders_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_clubs` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_likes`
--
ALTER TABLE `event_likes`
  ADD CONSTRAINT `fk_event_likes_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_event_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_reviews`
--
ALTER TABLE `event_reviews`
  ADD CONSTRAINT `event_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `event_reviews_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reminders_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
