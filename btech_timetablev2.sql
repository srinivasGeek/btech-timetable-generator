-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 09:17 PM
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
  `subject_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `section` varchar(5) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course_allotment`
--

INSERT INTO `course_allotment` (`allotment_id`, `subject_id`, `faculty_id`, `section`, `dept_id`, `room_id`) VALUES
(23, 13, 3, 'A', 2, 9),
(24, 13, 3, 'B', 2, 8),
(25, 13, 3, 'C', 2, 7),
(26, 14, 12, 'A', 2, 9),
(27, 14, 12, 'B', 2, 8),
(28, 14, 12, 'C', 2, 7);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `dept_id` int(11) NOT NULL,
  `dept_name` varchar(50) NOT NULL,
  `dept_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `dept_name`, `dept_code`) VALUES
(1, 'Computer Science', 'CSE'),
(2, 'Electronics', 'ECE'),
(3, 'Mechanical Engineering', 'MECH'),
(5, 'COMPUTER SCINCE AND ENGINEERING(AIML)', 'CSE(AIML)');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','hod','staff') DEFAULT 'staff',
  `dept_id` int(11) DEFAULT NULL,
  `designation` varchar(50) DEFAULT NULL,
  `availability` enum('MORNING','FULL_DAY') DEFAULT 'FULL_DAY'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `name`, `email`, `password`, `role`, `dept_id`, `designation`, `availability`) VALUES
(1, 'System Admin', 'admin@college.edu', 'admin123', 'admin', NULL, 'Administrator', 'FULL_DAY'),
(3, 'Miss M Sravanthi', 'hod_ece@drkist.edu', '123456', 'hod', 2, 'asst Professor', 'FULL_DAY'),
(4, 'Dr. K Durgaprasad', 'hod_aiml@drkist.edu', '123456', 'hod', 5, 'Professor', 'FULL_DAY'),
(5, 'Dr. B. Pavan', 'hod_mech@drkist.edu', '123456', 'hod', 3, 'associate Professor', 'FULL_DAY'),
(12, 'Mr M sreenivas Rao', 'sreenivas@drkis.edu', '123', 'staff', 2, 'associate Professor', 'FULL_DAY'),
(13, 'srinivas', 'admin@drkist.edu', '123', 'staff', 3, 'asst Professor', 'FULL_DAY');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_no` varchar(20) NOT NULL,
  `capacity` int(11) NOT NULL,
  `room_type` enum('classroom','lab') DEFAULT 'classroom'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_no`, `capacity`, `room_type`) VALUES
(3, '006', 60, 'lab'),
(4, '107', 60, 'classroom'),
(5, '108', 60, 'classroom'),
(6, '109', 60, 'classroom'),
(7, '112', 60, 'classroom'),
(8, '113', 60, 'classroom'),
(9, '115', 60, 'classroom'),
(10, '004', 60, 'lab'),
(11, '005', 60, 'lab');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `semester` varchar(10) DEFAULT NULL,
  `hours_per_week` int(11) NOT NULL DEFAULT '4',
  `is_lab` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `subject_code`, `dept_id`, `semester`, `hours_per_week`, `is_lab`) VALUES
(13, 'adc', 'ece101pc', 2, 'II-II', 4, 0),
(14, 'digitall image proccesing', 'ec203', 2, 'II-II', 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `time_slot` varchar(20) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `section` varchar(5) DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `day`, `time_slot`, `subject_id`, `faculty_id`, `room_id`, `section`) VALUES
(2115, 'Thursday', '12:10-01:00', 13, 3, 9, 'A'),
(2116, 'Monday', '10:10-11:00', 13, 3, 9, 'A'),
(2117, 'Friday', '02:40-03:30', 13, 3, 9, 'A'),
(2118, 'Wednesday', '11:20-12:10', 13, 3, 9, 'A'),
(2119, 'Friday', '10:10-11:00', 14, 12, 9, 'A'),
(2120, 'Tuesday', '02:40-03:30', 14, 12, 9, 'A'),
(2121, 'Wednesday', '09:20-10:10', 14, 12, 9, 'A'),
(2122, 'Thursday', '03:30-04:20', 14, 12, 9, 'A'),
(2123, 'Monday', '01:50-02:40', 13, 3, 8, 'B'),
(2124, 'Friday', '10:10-11:00', 13, 3, 8, 'B'),
(2125, 'Tuesday', '11:20-12:10', 13, 3, 8, 'B'),
(2126, 'Wednesday', '12:10-01:00', 13, 3, 8, 'B'),
(2127, 'Wednesday', '01:50-02:40', 14, 12, 8, 'B'),
(2128, 'Friday', '09:20-10:10', 14, 12, 8, 'B'),
(2129, 'Monday', '11:20-12:10', 14, 12, 8, 'B'),
(2130, 'Thursday', '10:10-11:00', 14, 12, 8, 'B'),
(2131, 'Thursday', '02:40-03:30', 13, 3, 7, 'C'),
(2132, 'Tuesday', '12:10-01:00', 13, 3, 7, 'C'),
(2133, 'Monday', '11:20-12:10', 13, 3, 7, 'C'),
(2134, 'Friday', '01:50-02:40', 13, 3, 7, 'C'),
(2135, 'Thursday', '09:20-10:10', 14, 12, 7, 'C'),
(2136, 'Tuesday', '10:10-11:00', 14, 12, 7, 'C'),
(2137, 'Friday', '02:40-03:30', 14, 12, 7, 'C'),
(2138, 'Monday', '12:10-01:00', 14, 12, 7, 'C');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course_allotment`
--
ALTER TABLE `course_allotment`
  ADD PRIMARY KEY (`allotment_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dept_id`),
  ADD UNIQUE KEY `dept_name` (`dept_name`),
  ADD UNIQUE KEY `dept_code` (`dept_code`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_faculty_dept` (`dept_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_no` (`room_no`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `fk_subject_dept` (`dept_id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tt_subject` (`subject_id`),
  ADD KEY `fk_tt_faculty` (`faculty_id`),
  ADD KEY `fk_tt_room` (`room_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `course_allotment`
--
ALTER TABLE `course_allotment`
  MODIFY `allotment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2139;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_allotment`
--
ALTER TABLE `course_allotment`
  ADD CONSTRAINT `course_allotment_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_allotment_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty`
--
ALTER TABLE `faculty`
  ADD CONSTRAINT `fk_faculty_dept` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE SET NULL;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subject_dept` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE CASCADE;

--
-- Constraints for table `timetable`
--
ALTER TABLE `timetable`
  ADD CONSTRAINT `fk_tt_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tt_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tt_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
