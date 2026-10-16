-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2026 at 10:26 PM
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
-- Database: `hospital_helpdesk`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'Hardware', 'Physical equipment issues — PCs, printers, monitors, peripherals', '2026-10-15 13:37:21'),
(2, 'Software', 'Application errors, software installation, licence problems', '2026-10-15 13:37:21'),
(3, 'Network', 'Connectivity, Wi-Fi, VPN, internet access issues', '2026-10-15 13:37:21'),
(4, 'Access', 'Login problems, account lockouts, permission requests', '2026-10-15 13:37:21'),
(5, 'Email', 'Email delivery, configuration, or mailbox issues', '2026-10-15 13:37:21'),
(6, 'Security', 'Antivirus, malware, suspicious activity, data security', '2026-10-15 13:37:21'),
(7, 'Other', 'Issues that do not fit other categories', '2026-10-15 13:37:21');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `is_support` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `first_name`, `last_name`, `email`, `phone`, `department`, `role`, `is_support`, `created_at`) VALUES
(1, 'James', 'O Connor', 'james.oconnor@hospital.ie', '086 100 1001', 'IT Support', 'Support Analyst', 1, '2026-10-15 13:37:21'),
(2, 'Mary', 'Walsh', 'mary.walsh@hospital.ie', '086 100 1002', 'IT Support', 'Senior Support Analyst', 1, '2026-10-15 13:37:21'),
(3, 'Sean', 'Murphy', 'sean.murphy@hospital.ie', '086 100 1003', 'IT Support', 'Support Analyst', 1, '2026-10-15 13:37:21'),
(4, 'Aoife', 'Kelly', 'aoife.kelly@hospital.ie', '086 100 1004', 'IT Support', 'Support Technician', 1, '2026-10-15 13:37:21'),
(5, 'John', 'Brennan', 'john.brennan@hospital.ie', '087 200 2001', 'Cardiology', 'Consultant', 0, '2026-10-15 13:37:21'),
(6, 'Sinead', 'Byrne', 'sinead.byrne@hospital.ie', '087 200 2002', 'Cardiology', 'Nurse', 0, '2026-10-15 13:37:21'),
(7, 'Patrick', 'Doyle', 'patrick.doyle@hospital.ie', '087 200 2003', 'Emergency', 'Doctor', 0, '2026-10-15 13:37:21'),
(8, 'Emma', 'Clarke', 'emma.clarke@hospital.ie', '087 200 2004', 'Emergency', 'Nurse', 0, '2026-10-15 13:37:21'),
(9, 'Michael', 'Ryan', 'michael.ryan@hospital.ie', '087 200 2005', 'Radiology', 'Radiographer', 0, '2026-10-15 13:37:21'),
(10, 'Catherine', 'Dunne', 'catherine.dunne@hospital.ie', '087 200 2006', 'Radiology', 'Consultant', 0, '2026-10-15 13:37:21'),
(11, 'David', 'Fitzgerald', 'david.fitzgerald@hospital.ie', '087 200 2007', 'ICU', 'Doctor', 0, '2026-10-15 13:37:21'),
(12, 'Sarah', 'Carthy', 'sarah.carthy@hospital.ie', '087 200 2008', 'ICU', 'Nurse', 0, '2026-10-15 13:37:21'),
(13, 'Thomas', 'Quinn', 'thomas.quinn@hospital.ie', '087 200 2009', 'Administration', 'Administrator', 0, '2026-10-15 13:37:21'),
(14, 'Laura', 'O Brien', 'laura.obrien@hospital.ie', '087 200 2010', 'Administration', 'Receptionist', 0, '2026-10-15 13:37:21'),
(15, 'Daniel', 'Lynch', 'daniel.lynch@hospital.ie', '087 200 2011', 'Pharmacy', 'Pharmacist', 0, '2026-10-15 13:37:21'),
(16, 'Rachel', 'Molloy', 'rachel.molloy@hospital.ie', '087 200 2012', 'Pharmacy', 'Technician', 0, '2026-10-15 13:37:21'),
(17, 'Kevin', 'Flynn', 'kevin.flynn@hospital.ie', '087 200 2013', 'Laboratory', 'Lab Technician', 0, '2026-10-15 13:37:21'),
(18, 'Helen', 'Daly', 'helen.daly@hospital.ie', '087 200 2014', 'Outpatients', 'Nurse', 0, '2026-10-15 13:37:21');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `status` enum('Open','In Progress','Resolved','Closed') NOT NULL DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `staff_id`, `category_id`, `assigned_to`, `subject`, `description`, `priority`, `status`, `created_at`, `resolved_at`) VALUES
(1, 5, 1, 1, 'Cardiology ward PC will not boot', 'The PC at the cardiology nurses station keeps showing a blue screen on startup. This is affecting patient record access.', 'High', 'Open', '2026-10-12 13:37:21', NULL),
(2, 6, 3, 2, 'Cannot connect to hospital Wi-Fi', 'Wi-Fi connection keeps dropping on the ward. Multiple staff affected in Cardiology.', 'Medium', 'Open', '2026-10-13 01:37:21', NULL),
(3, 7, 4, 1, 'Locked out of patient records system', 'I entered my password wrong three times and now I am locked out. Need urgent access restored.', 'High', 'Open', '2026-10-13 06:37:21', NULL),
(4, 8, 2, 3, 'Patient triage software crashing', 'The triage application in Emergency closes unexpectedly when printing patient labels.', 'Critical', 'In Progress', '2026-10-13 11:37:21', NULL),
(5, 9, 1, 4, 'X-ray printer not responding', 'The printer in the Radiology department is offline. We cannot print X-ray images for patients.', 'High', 'Open', '2026-10-13 12:37:21', NULL),
(6, 11, 3, 2, 'ICU monitors losing network connection', 'The patient monitoring system keeps losing its network connection. This is critical for patient safety.', 'Critical', 'In Progress', '2026-10-13 09:37:21', NULL),
(7, 13, 5, 1, 'Outlook will not open on admin PC', 'Microsoft Outlook crashes immediately on launch. Need email access for hospital correspondence.', 'Medium', 'Open', '2026-10-12 15:37:21', NULL),
(8, 14, 4, 3, 'Need access to the HR portal', 'I am a new receptionist and I do not have access to the HR booking system. Need permissions set up.', 'Low', 'Open', '2026-10-13 21:37:21', NULL),
(9, 15, 2, 4, 'Pharmacy inventory system slow', 'The pharmacy stock system takes over two minutes to load. Happens every morning.', 'Medium', 'Open', '2026-10-15 07:37:21', NULL),
(10, 16, 3, 2, 'VPN keeps disconnecting from home', 'When working remotely, the VPN disconnects every 15 minutes. I need stable access to pharmacy records.', 'Medium', 'Open', '2026-10-15 01:37:21', NULL),
(11, 10, 5, 1, 'Missing emails in shared mailbox', 'The Radiology shared mailbox is missing emails from yesterday. Important referral was not received.', 'High', 'In Progress', '2026-10-14 19:37:21', NULL),
(12, 17, 1, 4, 'Lab analyser PC frozen', 'The PC connected to the blood analyser in the laboratory has frozen. Needs a restart or repair.', 'High', 'Open', '2026-10-15 10:37:21', NULL),
(13, 5, 1, 1, 'Monitor flickering in cardiology', 'The monitor at station 3 keeps flickering making it hard to read patient charts.', 'Medium', 'Resolved', '2026-09-17 13:37:21', '2026-09-18 13:37:21'),
(14, 6, 2, 2, 'Excel not responding on ward PC', 'Excel freezes when opening large spreadsheets with patient data.', 'Low', 'Resolved', '2026-09-20 13:37:21', '2026-09-21 13:37:21'),
(15, 7, 3, 1, 'Emergency department internet down', 'No internet access in the emergency department. All staff affected.', 'Critical', 'Resolved', '2026-09-23 13:37:21', '2026-09-23 13:37:21'),
(16, 8, 4, 3, 'Cannot log into PACS system', 'My PACS login was rejected. Need it to view X-ray images.', 'High', 'Resolved', '2026-09-25 13:37:21', '2026-09-26 13:37:21'),
(17, 9, 5, 1, 'Outlook search not working', 'Email search returns no results even though the emails exist in the inbox.', 'Low', 'Resolved', '2026-09-27 13:37:21', '2026-09-28 13:37:21'),
(18, 10, 2, 4, 'Radiology viewer software will not install', 'The new image viewer software fails during installation on the consultant workstation.', 'Medium', 'Resolved', '2026-09-30 13:37:21', '2026-10-01 13:37:21'),
(19, 11, 3, 2, 'ICU network switch failed', 'Network switch in the ICU server cabinet has failed. No connectivity on the unit.', 'Critical', 'Resolved', '2026-10-03 13:37:21', '2026-10-03 13:37:21'),
(20, 12, 4, 3, 'Password expired for ICU nurse', 'My password expired and I cannot change it. Need access to patient monitoring.', 'Medium', 'Resolved', '2026-10-05 13:37:21', '2026-10-05 13:37:21'),
(21, 13, 5, 1, 'Admin email signature not showing', 'The email signature I configured in Outlook is not appearing on sent emails.', 'Low', 'Resolved', '2026-10-07 13:37:21', '2026-10-07 13:37:21'),
(22, 14, 6, 4, 'Suspicious email received', 'Received a phishing email claiming to be from IT support asking for my password.', 'High', 'Resolved', '2026-10-10 13:37:21', '2026-10-11 13:37:21'),
(23, 15, 1, 1, 'Barcode scanner not working in pharmacy', 'The barcode scanner for medication labelling is not being detected by the PC.', 'Medium', 'Resolved', '2026-10-11 13:37:21', '2026-10-12 13:37:21'),
(24, 16, 2, 2, 'Pharmacy software licence expired', 'The pharmacy management software says the licence has expired.', 'High', 'Resolved', '2026-10-12 13:37:21', '2026-10-13 13:37:21'),
(25, 5, 3, 1, 'Old network card replacement', 'Network card failed in cardiology workstation. Replaced and reconnected.', 'Medium', 'Resolved', '2026-08-31 13:37:21', '2026-09-01 13:37:21'),
(26, 8, 2, 3, 'Emergency printer driver issue', 'Printer driver corruption caused label printing to fail. Reinstalled driver.', 'Low', 'Resolved', '2026-08-26 13:37:21', '2026-08-27 13:37:21'),
(27, 17, 1, 4, 'Lab PC hard drive replaced', 'The hard drive in the laboratory analyser PC failed. Replaced with SSD and restored software.', 'High', 'Closed', '2026-09-10 13:37:21', '2026-09-11 13:37:21'),
(28, 18, 5, 1, 'Outpatient clinic email setup', 'New outpatient clinic needed a shared email mailbox created and configured.', 'Low', 'Closed', '2026-09-15 13:37:21', '2026-09-16 13:37:21'),
(29, 13, 2, 1, 'Admin PC running very slow', 'The administration PC takes five minutes to boot and applications are slow.', 'Medium', 'Resolved', '2026-09-05 13:37:21', '2026-09-06 13:37:21'),
(30, 13, 2, 1, 'Admin PC freezing during reports', 'Same admin PC freezes when generating monthly reports in the finance system.', 'Medium', 'Resolved', '2026-09-08 13:37:21', '2026-09-09 13:37:21'),
(31, 13, 2, 1, 'Admin PC blue screen error', 'The admin PC that was slow and freezing now shows a blue screen error with memory management message.', 'High', 'Resolved', '2026-09-11 13:37:21', '2026-09-12 13:37:21'),
(32, 7, 3, 2, 'Emergency dept Wi-Fi not working', 'Wi-Fi is down again in the emergency department waiting area.', 'High', 'Resolved', '2026-09-25 13:37:21', '2026-09-26 13:37:21'),
(33, 7, 3, 2, 'Emergency dept network slow', 'Network speed in the emergency department has dropped significantly.', 'Medium', 'Resolved', '2026-09-28 13:37:21', '2026-09-29 13:37:21'),
(34, 7, 3, 2, 'Emergency dept VPN keeps dropping', 'VPN from the emergency department to the main server keeps disconnecting every few minutes.', 'Medium', 'Resolved', '2026-09-30 13:37:21', '2026-10-01 13:37:21');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_updates`
--

CREATE TABLE `ticket_updates` (
  `update_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_updates`
--

INSERT INTO `ticket_updates` (`update_id`, `ticket_id`, `staff_id`, `message`, `old_status`, `new_status`, `created_at`) VALUES
(1, 4, 3, 'Investigating the crash. Checking Windows Event logs for the error.', 'Open', 'In Progress', '2026-10-15 13:37:21'),
(2, 4, 3, 'Found that the print spooler is causing the crash. Testing a fix.', NULL, NULL, '2026-10-15 13:37:21'),
(3, 6, 2, 'Checked the network switch in ICU. Port 12 is showing errors. Replacing cable.', 'Open', 'In Progress', '2026-10-15 13:37:21'),
(4, 6, 2, 'Cable replaced. Monitoring the connection for stability over the next hour.', NULL, NULL, '2026-10-15 13:37:21'),
(5, 11, 1, 'Checking Exchange server logs for the missing emails.', 'Open', 'In Progress', '2026-10-15 13:37:21'),
(6, 13, 1, 'Replaced the DisplayPort cable. Monitor is stable now.', 'Open', 'Resolved', '2026-10-15 13:37:21'),
(7, 15, 1, 'Router had failed. Replaced the router and restored connectivity within 30 minutes.', 'Open', 'Resolved', '2026-10-15 13:37:21'),
(8, 19, 2, 'Replaced the failed network switch with a spare. All ICU connectivity restored.', 'Open', 'Resolved', '2026-10-15 13:37:21'),
(9, 22, 4, 'Confirmed the email was a phishing attempt. Blocked the sender domain and reported to security team.', 'Open', 'Resolved', '2026-10-15 13:37:21'),
(10, 29, 1, 'Ran disk cleanup and defragmentation. Also removed unnecessary startup programs.', 'Open', 'Resolved', '2026-10-15 13:37:21'),
(11, 30, 1, 'Found faulty RAM module. Running memory diagnostics to confirm.', NULL, NULL, '2026-10-15 13:37:21'),
(12, 30, 1, 'Replaced the faulty RAM. PC is stable now. Will monitor.', NULL, 'Resolved', '2026-10-15 13:37:21'),
(13, 31, 1, 'Blue screen was caused by the same faulty RAM that was freezing the PC. RAM was replaced in previous ticket.', 'Open', 'Resolved', '2026-10-15 13:37:21'),
(14, 32, 2, 'Access point in the waiting area had failed. Replaced and reconfigured.', 'Open', 'Resolved', '2026-10-15 13:37:21'),
(15, 34, 2, 'VPN profile was corrupted. Recreated the VPN connection profile and tested.', 'Open', 'Resolved', '2026-10-15 13:37:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_staff_department` (`department`),
  ADD KEY `idx_staff_support` (`is_support`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `idx_tickets_status` (`status`),
  ADD KEY `idx_tickets_staff` (`staff_id`),
  ADD KEY `idx_tickets_category` (`category_id`),
  ADD KEY `idx_tickets_assigned` (`assigned_to`),
  ADD KEY `idx_tickets_created` (`created_at`),
  ADD KEY `idx_tickets_priority` (`priority`);

--
-- Indexes for table `ticket_updates`
--
ALTER TABLE `ticket_updates`
  ADD PRIMARY KEY (`update_id`),
  ADD KEY `idx_updates_ticket` (`ticket_id`),
  ADD KEY `idx_updates_staff` (`staff_id`),
  ADD KEY `idx_updates_created` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `ticket_updates`
--
ALTER TABLE `ticket_updates`
  MODIFY `update_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`staff_id`);

--
-- Constraints for table `ticket_updates`
--
ALTER TABLE `ticket_updates`
  ADD CONSTRAINT `ticket_updates_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_updates_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
