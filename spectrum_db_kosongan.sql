-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 04:16 AM
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
-- Database: `spectrum_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_spectrum_evidence`
--

CREATE TABLE `wp_spectrum_evidence` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `submitter_id` bigint(20) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `unit_code` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `justification` text DEFAULT NULL,
  `link_url` text DEFAULT NULL,
  `numeric_value` decimal(20,2) DEFAULT NULL,
  `attachment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metric_category` enum('MANDATORY','RECOMMENDED','GENERAL') DEFAULT NULL,
  `status` enum('DRAFT','SUBMITTED','APPROVED','REJECTED') NOT NULL DEFAULT 'DRAFT',
  `submitted_at` datetime DEFAULT NULL,
  `last_reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_spectrum_evidence_log`
--

CREATE TABLE `wp_spectrum_evidence_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `evidence_id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `from_status` varchar(32) DEFAULT NULL,
  `to_status` varchar(32) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_spectrum_evidence_metric`
--

CREATE TABLE `wp_spectrum_evidence_metric` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `evidence_id` bigint(20) UNSIGNED NOT NULL,
  `metric_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_spectrum_function_metric_assignment`
--

CREATE TABLE `wp_spectrum_function_metric_assignment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metric_id` bigint(20) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `category` enum('MANDATORY','RECOMMENDED') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_spectrum_metric`
--

CREATE TABLE `wp_spectrum_metric` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sdg_number` tinyint(3) UNSIGNED NOT NULL,
  `metric_code` varchar(10) NOT NULL,
  `metric_type` enum('numeric','initiatives','policy') NOT NULL,
  `metric_title` varchar(255) NOT NULL,
  `metric_question` text DEFAULT NULL,
  `metric_points` longtext DEFAULT NULL,
  `metric_note` longtext DEFAULT NULL,
  `is_active_default` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_spectrum_metric_no_data`
--

CREATE TABLE `wp_spectrum_metric_no_data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_code` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `metric_id` bigint(20) UNSIGNED NOT NULL,
  `submitter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_spectrum_reviewer_scope`
--

CREATE TABLE `wp_spectrum_reviewer_scope` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `sdg_number` tinyint(3) UNSIGNED DEFAULT NULL,
  `metric_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit_code` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_spectrum_sdg`
--

CREATE TABLE `wp_spectrum_sdg` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `sdg_number` tinyint(3) UNSIGNED NOT NULL,
  `sdg_title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_spectrum_year_metric`
--

CREATE TABLE `wp_spectrum_year_metric` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `metric_id` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `weight` decimal(5,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

ALTER TABLE `wp_spectrum_evidence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evidence_submitter` (`submitter_id`),
  ADD KEY `idx_evidence_year_status` (`year`,`status`),
  ADD KEY `idx_evidence_unit` (`unit_code`),
  ADD KEY `idx_evidence_status` (`status`),
  ADD KEY `idx_evidence_metric_category` (`metric_category`);

--
-- Indexes for table `wp_spectrum_evidence_log`
--
ALTER TABLE `wp_spectrum_evidence_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_evidence` (`evidence_id`),
  ADD KEY `idx_log_actor` (`actor_id`),
  ADD KEY `evidence_id` (`evidence_id`),
  ADD KEY `actor_id` (`actor_id`);

--
-- Indexes for table `wp_spectrum_evidence_metric`
--
ALTER TABLE `wp_spectrum_evidence_metric`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_evidence_metric` (`evidence_id`,`metric_id`),
  ADD KEY `idx_evmetric_evidence` (`evidence_id`),
  ADD KEY `idx_evmetric_metric` (`metric_id`);

--
-- Indexes for table `wp_spectrum_function_metric_assignment`
--
ALTER TABLE `wp_spectrum_function_metric_assignment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_unit_metric_year` (`unit_code`,`metric_id`,`year`),
  ADD KEY `idx_fma_unit_year` (`unit_code`,`year`),
  ADD KEY `idx_fma_category` (`category`),
  ADD KEY `idx_fma_metric` (`metric_id`);

--
-- Indexes for table `wp_spectrum_metric`
--
ALTER TABLE `wp_spectrum_metric`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_metric_sdg` (`sdg_number`),
  ADD KEY `idx_metric_code` (`metric_code`),
  ADD KEY `idx_metric_type` (`metric_type`);

--
-- Indexes for table `wp_spectrum_metric_no_data`
--
ALTER TABLE `wp_spectrum_metric_no_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_unit_year_metric` (`unit_code`,`year`,`metric_id`),
  ADD KEY `idx_metric` (`metric_id`),
  ADD KEY `idx_unit_year` (`unit_code`,`year`);

ALTER TABLE `wp_spectrum_reviewer_scope`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_scope_reviewer` (`reviewer_id`),
  ADD KEY `idx_scope_sdg` (`sdg_number`),
  ADD KEY `idx_scope_metric` (`metric_id`),
  ADD KEY `idx_scope_unit` (`unit_code`);

--
-- Indexes for table `wp_spectrum_sdg`
--
ALTER TABLE `wp_spectrum_sdg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sdg_number` (`sdg_number`);

--
-- Indexes for table `wp_spectrum_year_metric`
--
ALTER TABLE `wp_spectrum_year_metric`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_year_metric` (`year`,`metric_id`),
  ADD KEY `idx_year_active` (`year`,`is_active`),
  ADD KEY `fk_year_metric_metric` (`metric_id`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `wp_spectrum_evidence`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_spectrum_evidence_log`
--
ALTER TABLE `wp_spectrum_evidence_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_spectrum_evidence_metric`
--
ALTER TABLE `wp_spectrum_evidence_metric`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_spectrum_function_metric_assignment`
--
ALTER TABLE `wp_spectrum_function_metric_assignment`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_spectrum_metric`
--
ALTER TABLE `wp_spectrum_metric`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_spectrum_metric_no_data`
--
ALTER TABLE `wp_spectrum_metric_no_data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `wp_spectrum_reviewer_scope`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_spectrum_year_metric`
--
ALTER TABLE `wp_spectrum_year_metric`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

ALTER TABLE `wp_spectrum_evidence_metric`
  ADD CONSTRAINT `fk_evmetric_evidence` FOREIGN KEY (`evidence_id`) REFERENCES `wp_spectrum_evidence` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_evmetric_metric` FOREIGN KEY (`metric_id`) REFERENCES `wp_spectrum_metric` (`id`) ON DELETE CASCADE;

ALTER TABLE `wp_spectrum_year_metric`
  ADD CONSTRAINT `fk_year_metric_metric` FOREIGN KEY (`metric_id`) REFERENCES `wp_spectrum_metric` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
