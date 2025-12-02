-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 08:25 PM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.0.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `btech_timetable`
--

-- --------------------------------------------------------

--
-- Table structure for table `course_allotment`
--

CREATE TABLE `course_allotment` (
  `allotment_id` int(11) NOT NULL,
  `subject_code` varchar(10) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `section` varchar(5) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course_allotment`
--

INSERT INTO `course_allotment` (`allotment_id`, `subject_code`, `faculty_id`, `section`) VALUES
(1, 'CS101', 1, 'A'),
(2, 'CS102', 2, 'A'),
(3, 'CS103', 3, 'A'),
(4, 'CS104', 1, 'A'),
(5, 'CS105', 2, 'A'),
(6, 'CS_L1', 4, 'A'),
(7, 'CS_L2', 4, 'A'),
(8, 'AI101', 5, 'A'),
(9, 'AI102', 6, 'A'),
(10, 'AI103', 5, 'A'),
(11, 'AI104', 1, 'A'),
(12, 'AI105', 13, 'A'),
(13, 'AI_L1', 6, 'A'),
(14, 'AI_L2', 6, 'A'),
(15, 'DS101', 7, 'A'),
(16, 'DS102', 7, 'A'),
(17, 'DS103', 13, 'A'),
(18, 'DS104', 2, 'A'),
(19, 'DS105', 5, 'A'),
(20, 'DS_L1', 7, 'A'),
(21, 'DS_L2', 7, 'A'),
(22, 'CY101', 8, 'A'),
(23, 'CY102', 2, 'A'),
(24, 'CY103', 8, 'A'),
(25, 'CY104', 8, 'A'),
(26, 'CY105', 1, 'A'),
(27, 'CY_L1', 8, 'A'),
(28, 'CY_L2', 4, 'A'),
(29, 'EC101', 9, 'A'),
(30, 'EC102', 10, 'A'),
(31, 'EC103', 11, 'A'),
(32, 'EC104', 9, 'A'),
(33, 'EC105', 13, 'A'),
(34, 'EC_L1', 12, 'A'),
(35, 'EC_L2', 12, 'A'),
(36, 'ME101', 13, 'A'),
(37, 'ME102', 14, 'A'),
(38, 'ME103', 15, 'A'),
(39, 'ME104', 13, 'A'),
(40, 'ME105', 14, 'A'),
(41, 'ME_L1', 16, 'A'),
(42, 'ME_L2', 16, 'A'),
(43, 'SH101', 17, 'A'),
(44, 'SH102', 18, 'A'),
(45, 'SH103', 19, 'A'),
(46, 'SH104', 18, 'A'),
(47, 'SH105', 19, 'A'),
(48, 'SH_L1', 18, 'A'),
(49, 'SH_L2', 19, 'A'),
(50, 'CS105', 2, 'B'),
(51, 'CS105', 2, 'C'),
(52, 'CS101', 1, 'B'),
(53, 'CS101', 1, 'C'),
(54, 'CS102', 2, 'B'),
(55, 'CS102', 2, 'C'),
(56, 'CS_L1', 4, 'B'),
(57, 'CS_L1', 4, 'C');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `designation` varchar(50) DEFAULT NULL,
  `department` varchar(50) NOT NULL,
  `availability` enum('MORNING_ONLY','ALL_DAY') DEFAULT 'ALL_DAY'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `faculty_name`, `designation`, `department`, `availability`) VALUES
(1, 'Dr. Alan Turing', 'Professor', 'CSE', 'ALL_DAY'),
(2, 'Prof. Ada Lovelace', 'Asst. Prof', 'CSE', 'ALL_DAY'),
(3, 'Dr. Grace Hopper', 'Assoc. Prof', 'CSE', 'MORNING_ONLY'),
(4, 'Prof. Linus Torvalds', 'Lab Asst', 'CSE', 'ALL_DAY'),
(5, 'Dr. Andrew Ng', 'Professor', 'CSE(AIML)', 'ALL_DAY'),
(6, 'Prof. Yann LeCun', 'Asst. Prof', 'CSE(AIML)', 'ALL_DAY'),
(7, 'Dr. Geoffrey Hinton', 'Professor', 'CSE(DS)', 'ALL_DAY'),
(8, 'Prof. Kevin Mitnick', 'Asst. Prof', 'CSE(CYBER)', 'ALL_DAY'),
(9, 'Dr. Maxwell', 'Professor', 'ECE', 'ALL_DAY'),
(10, 'Prof. Hertz', 'Asst. Prof', 'ECE', 'ALL_DAY'),
(11, 'Dr. Marconi', 'Assoc. Prof', 'ECE', 'MORNING_ONLY'),
(12, 'Prof. Bose', 'Lab Asst', 'ECE', 'ALL_DAY'),
(13, 'Dr. Nikola Tesla', 'Professor', 'MECH', 'ALL_DAY'),
(14, 'Prof. James Watt', 'Asst. Prof', 'MECH', 'ALL_DAY'),
(15, 'Dr. Diesel', 'Assoc. Prof', 'MECH', 'ALL_DAY'),
(16, 'Prof. Ford', 'Lab Asst', 'MECH', 'ALL_DAY'),
(17, 'Dr. Ramanujan', 'Professor', 'S&H', 'ALL_DAY'),
(18, 'Dr. CV Raman', 'Professor', 'S&H', 'ALL_DAY'),
(19, 'Prof. Shakespeare', 'Asst. Prof', 'S&H', 'ALL_DAY');

-- --------------------------------------------------------

--
-- Table structure for table `generated_timetable`
--

CREATE TABLE `generated_timetable` (
  `id` int(11) NOT NULL,
  `day` varchar(10) DEFAULT NULL,
  `slot_id` int(11) DEFAULT NULL,
  `subject_code` varchar(10) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `room_no` varchar(10) DEFAULT 'Classroom',
  `section` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `generated_timetable`
--

INSERT INTO `generated_timetable` (`id`, `day`, `slot_id`, `subject_code`, `faculty_id`, `room_no`, `section`) VALUES
(51, 'Wednesday', 1, 'ME102', 14, 'Classroom', 'A'),
(52, 'Thursday', 1, 'ME102', 14, 'Classroom', 'A'),
(53, 'Friday', 1, 'ME102', 14, 'Classroom', 'A'),
(54, 'Monday', 1, 'ME102', 14, 'Classroom', 'A'),
(55, 'Monday', 2, 'ME104', 13, 'Classroom', 'A'),
(56, 'Thursday', 2, 'ME104', 13, 'Classroom', 'A'),
(57, 'Tuesday', 1, 'ME104', 13, 'Classroom', 'A'),
(58, 'Wednesday', 2, 'ME104', 13, 'Classroom', 'A'),
(59, 'Monday', 3, 'ME103', 15, 'Classroom', 'A'),
(60, 'Wednesday', 3, 'ME103', 15, 'Classroom', 'A'),
(61, 'Tuesday', 2, 'ME103', 15, 'Classroom', 'A'),
(62, 'Thursday', 3, 'ME103', 15, 'Classroom', 'A'),
(63, 'Friday', 2, 'ME101', 13, 'Classroom', 'A'),
(64, 'Wednesday', 4, 'ME101', 13, 'Classroom', 'A'),
(65, 'Monday', 4, 'ME101', 13, 'Classroom', 'A'),
(66, 'Thursday', 4, 'ME101', 13, 'Classroom', 'A'),
(67, 'Thursday', 5, 'ME105', 14, 'Classroom', 'A'),
(68, 'Tuesday', 3, 'ME105', 14, 'Classroom', 'A'),
(69, 'Friday', 3, 'ME105', 14, 'Classroom', 'A'),
(70, 'Wednesday', 5, 'ME105', 14, 'Classroom', 'A'),
(71, 'Tuesday', 5, 'ME_L1', 16, 'Lab', 'A'),
(72, 'Tuesday', 6, 'ME_L1', 16, 'Lab', 'A'),
(73, 'Tuesday', 7, 'ME_L1', 16, 'Lab', 'A'),
(74, 'Friday', 5, 'ME_L2', 16, 'Lab', 'A'),
(75, 'Friday', 6, 'ME_L2', 16, 'Lab', 'A'),
(76, 'Friday', 7, 'ME_L2', 16, 'Lab', 'A'),
(77, 'Wednesday', 4, 'CS105', 2, 'Classroom', 'C'),
(78, 'Monday', 5, 'CS105', 2, 'Classroom', 'C'),
(79, 'Tuesday', 3, 'CS105', 2, 'Classroom', 'C'),
(80, 'Thursday', 2, 'CS105', 2, 'Classroom', 'C'),
(81, 'Monday', 1, 'CS101', 1, 'Classroom', 'C'),
(82, 'Friday', 3, 'CS101', 1, 'Classroom', 'C'),
(83, 'Wednesday', 3, 'CS101', 1, 'Classroom', 'C'),
(84, 'Thursday', 4, 'CS101', 1, 'Classroom', 'C'),
(85, 'Friday', 5, 'CS102', 2, 'Classroom', 'C'),
(86, 'Wednesday', 6, 'CS102', 2, 'Classroom', 'C'),
(87, 'Monday', 6, 'CS102', 2, 'Classroom', 'C'),
(88, 'Thursday', 3, 'CS102', 2, 'Classroom', 'C'),
(89, 'Monday', 2, 'CS_L1', 4, 'Lab 1/2', 'C'),
(90, 'Monday', 3, 'CS_L1', 4, 'Lab 1/2', 'C'),
(91, 'Monday', 4, 'CS_L1', 4, 'Lab 1/2', 'C'),
(118, 'Friday', 2, 'CS105', 2, 'Classroom', 'B'),
(119, 'Wednesday', 1, 'CS105', 2, 'Classroom', 'B'),
(120, 'Thursday', 1, 'CS105', 2, 'Classroom', 'B'),
(121, 'Tuesday', 1, 'CS105', 2, 'Classroom', 'B'),
(122, 'Wednesday', 5, 'CS101', 1, 'Classroom', 'B'),
(123, 'Thursday', 3, 'CS101', 1, 'Classroom', 'B'),
(124, 'Monday', 4, 'CS101', 1, 'Classroom', 'B'),
(125, 'Tuesday', 4, 'CS101', 1, 'Classroom', 'B'),
(126, 'Thursday', 6, 'CS102', 2, 'Classroom', 'B'),
(127, 'Tuesday', 2, 'CS102', 2, 'Classroom', 'B'),
(128, 'Monday', 1, 'CS102', 2, 'Classroom', 'B'),
(129, 'Wednesday', 2, 'CS102', 2, 'Classroom', 'B'),
(130, 'Tuesday', 5, 'CS_L1', 4, 'Lab 1/2', 'B'),
(131, 'Tuesday', 6, 'CS_L1', 4, 'Lab 1/2', 'B'),
(132, 'Tuesday', 7, 'CS_L1', 4, 'Lab 1/2', 'B'),
(133, 'Monday', 6, 'CS104', 1, 'Classroom', 'A'),
(134, 'Tuesday', 6, 'CS104', 1, 'Classroom', 'A'),
(135, 'Wednesday', 6, 'CS104', 1, 'Classroom', 'A'),
(136, 'Thursday', 6, 'CS104', 1, 'Classroom', 'A'),
(137, 'Tuesday', 3, 'CS103', 3, 'Classroom', 'A'),
(138, 'Monday', 3, 'CS103', 3, 'Classroom', 'A'),
(139, 'Friday', 3, 'CS103', 3, 'Classroom', 'A'),
(140, 'Wednesday', 3, 'CS103', 3, 'Classroom', 'A'),
(141, 'Wednesday', 7, 'CS101', 1, 'Classroom', 'A'),
(142, 'Friday', 5, 'CS101', 1, 'Classroom', 'A'),
(143, 'Thursday', 5, 'CS101', 1, 'Classroom', 'A'),
(144, 'Monday', 5, 'CS101', 1, 'Classroom', 'A'),
(145, 'Friday', 4, 'CS105', 2, 'Classroom', 'A'),
(146, 'Wednesday', 5, 'CS105', 2, 'Classroom', 'A'),
(147, 'Tuesday', 4, 'CS105', 2, 'Classroom', 'A'),
(148, 'Monday', 4, 'CS105', 2, 'Classroom', 'A'),
(149, 'Tuesday', 7, 'CS102', 2, 'Classroom', 'A'),
(150, 'Thursday', 7, 'CS102', 2, 'Classroom', 'A'),
(151, 'Monday', 2, 'CS102', 2, 'Classroom', 'A'),
(152, 'Friday', 7, 'CS102', 2, 'Classroom', 'A'),
(153, 'Thursday', 1, 'CS_L1', 4, 'Lab', 'A'),
(154, 'Thursday', 2, 'CS_L1', 4, 'Lab', 'A'),
(155, 'Thursday', 3, 'CS_L1', 4, 'Lab', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_code` varchar(10) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `course_type` enum('THEORY','LAB') NOT NULL DEFAULT 'THEORY',
  `semester` int(11) NOT NULL,
  `department` varchar(50) NOT NULL,
  `lecture_hours_per_week` int(11) NOT NULL DEFAULT '4'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_code`, `subject_name`, `course_type`, `semester`, `department`, `lecture_hours_per_week`) VALUES
('AI101', 'Intro to AI', 'THEORY', 3, 'CSE(AIML)', 4),
('AI102', 'Machine Learning', 'THEORY', 3, 'CSE(AIML)', 4),
('AI103', 'Neural Networks', 'THEORY', 3, 'CSE(AIML)', 4),
('AI104', 'Python for DS', 'THEORY', 3, 'CSE(AIML)', 4),
('AI105', 'Statistics for AI', 'THEORY', 3, 'CSE(AIML)', 4),
('AI_L1', 'Python Lab', 'LAB', 3, 'CSE(AIML)', 3),
('AI_L2', 'ML Lab', 'LAB', 3, 'CSE(AIML)', 3),
('CS101', 'Compiler Design', 'THEORY', 5, 'CSE', 4),
('CS102', 'Computer Networks', 'THEORY', 5, 'CSE', 4),
('CS103', 'Software Engineering', 'THEORY', 5, 'CSE', 4),
('CS104', 'Web Technologies', 'THEORY', 5, 'CSE', 4),
('CS105', 'Automata Theory', 'THEORY', 5, 'CSE', 4),
('CS_L1', 'Web Tech Lab', 'LAB', 5, 'CSE', 3),
('CS_L2', 'Networks Lab', 'LAB', 5, 'CSE', 3),
('CY101', 'Cryptography', 'THEORY', 3, 'CSE(CYBER)', 4),
('CY102', 'Network Security', 'THEORY', 3, 'CSE(CYBER)', 4),
('CY103', 'Ethical Hacking', 'THEORY', 3, 'CSE(CYBER)', 4),
('CY104', 'Cyber Forensics', 'THEORY', 3, 'CSE(CYBER)', 4),
('CY105', 'Blockchain Basics', 'THEORY', 3, 'CSE(CYBER)', 4),
('CY_L1', 'Ethical Hacking Lab', 'LAB', 3, 'CSE(CYBER)', 3),
('CY_L2', 'Cryptography Lab', 'LAB', 3, 'CSE(CYBER)', 3),
('DS101', 'Data Mining', 'THEORY', 3, 'CSE(DS)', 4),
('DS102', 'Big Data Analytics', 'THEORY', 3, 'CSE(DS)', 4),
('DS103', 'R Programming', 'THEORY', 3, 'CSE(DS)', 4),
('DS104', 'NoSQL Databases', 'THEORY', 3, 'CSE(DS)', 4),
('DS105', 'Data Vizualization', 'THEORY', 3, 'CSE(DS)', 4),
('DS_L1', 'R Programming Lab', 'LAB', 3, 'CSE(DS)', 3),
('DS_L2', 'Big Data Lab', 'LAB', 3, 'CSE(DS)', 3),
('EC101', 'Analog Circuits', 'THEORY', 3, 'ECE', 4),
('EC102', 'Digital Electronics', 'THEORY', 3, 'ECE', 4),
('EC103', 'Signals & Systems', 'THEORY', 3, 'ECE', 4),
('EC104', 'Control Systems', 'THEORY', 3, 'ECE', 4),
('EC105', 'Electromagnetics', 'THEORY', 3, 'ECE', 4),
('EC_L1', 'Analog Circuits Lab', 'LAB', 3, 'ECE', 3),
('EC_L2', 'Digital Lab', 'LAB', 3, 'ECE', 3),
('ME101', 'Thermodynamics', 'THEORY', 3, 'MECH', 4),
('ME102', 'Fluid Mechanics', 'THEORY', 3, 'MECH', 4),
('ME103', 'Kinematics', 'THEORY', 3, 'MECH', 4),
('ME104', 'Strength of Materials', 'THEORY', 3, 'MECH', 4),
('ME105', 'Manufacturing Proc.', 'THEORY', 3, 'MECH', 4),
('ME_L1', 'Fluid Mechanics Lab', 'LAB', 3, 'MECH', 3),
('ME_L2', 'Workshop Practice', 'LAB', 3, 'MECH', 3),
('SH101', 'Engineering Math I', 'THEORY', 1, 'S&H', 4),
('SH102', 'Engineering Physics', 'THEORY', 1, 'S&H', 4),
('SH103', 'Communicative English', 'THEORY', 1, 'S&H', 4),
('SH104', 'Engineering Chemistry', 'THEORY', 1, 'S&H', 4),
('SH105', 'Env. Science', 'THEORY', 1, 'S&H', 4),
('SH_L1', 'Physics Lab', 'LAB', 1, 'S&H', 3),
('SH_L2', 'English Comm Lab', 'LAB', 1, 'S&H', 3);

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `slot_id` int(11) NOT NULL,
  `start_time` varchar(20) DEFAULT NULL,
  `end_time` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`slot_id`, `start_time`, `end_time`) VALUES
(1, '09:20', '10:10'),
(2, '10:10', '11:00'),
(3, '11:20', '12:10'),
(4, '12:10', '13:00'),
(5, '13:50', '14:40'),
(6, '14:40', '15:30'),
(7, '15:30', '16:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course_allotment`
--
ALTER TABLE `course_allotment`
  ADD PRIMARY KEY (`allotment_id`),
  ADD KEY `subject_code` (`subject_code`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`);

--
-- Indexes for table `generated_timetable`
--
ALTER TABLE `generated_timetable`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slot` (`day`,`slot_id`,`faculty_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_code`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`slot_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `course_allotment`
--
ALTER TABLE `course_allotment`
  MODIFY `allotment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `generated_timetable`
--
ALTER TABLE `generated_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_allotment`
--
ALTER TABLE `course_allotment`
  ADD CONSTRAINT `course_allotment_ibfk_1` FOREIGN KEY (`subject_code`) REFERENCES `subjects` (`subject_code`),
  ADD CONSTRAINT `course_allotment_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
