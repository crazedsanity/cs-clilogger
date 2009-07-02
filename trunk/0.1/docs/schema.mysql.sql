--
-- SVN INFORMATION:
-- 
-- SVN Signature::::::: $Id$
-- Last Author::::::::: $Author$ 
-- Current Revision:::: $Revision$
-- Repository Location: $HeadURL$ 
-- Last Updated:::::::: $Date$
--

-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- Generation Time: Jul 02, 2009 at 01:43 PM
-- Server version: 5.0.22
-- PHP Version: 5.1.6

SET FOREIGN_KEY_CHECKS=0;

SET AUTOCOMMIT=0;
START TRANSACTION;

-- 
-- Database: `cli_logger`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `cli_host_table`
-- 

CREATE TABLE `cli_host_table` (
  `host_id` int(11) NOT NULL auto_increment,
  `host_name` text NOT NULL,
  PRIMARY KEY  (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `cli_internal_log_table`
-- 

CREATE TABLE `cli_internal_log_table` (
  `internal_log_id` int(11) NOT NULL auto_increment,
  `log_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `log_data` text NOT NULL,
  PRIMARY KEY  (`internal_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `cli_log_table`
-- 

CREATE TABLE `cli_log_table` (
  `log_id` int(11) NOT NULL auto_increment,
  `script_id` int(11) NOT NULL,
  `full_command` text NOT NULL,
  `host_id` int(11) NOT NULL,
  `start_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `output` text,
  `errors` text,
  `exit_code` int(11) default NULL,
  PRIMARY KEY  (`log_id`),
  KEY `cli_log_table_script_id_fkey` (`script_id`),
  KEY `cli_log_table_host_id_fkey` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `cli_script_table`
-- 

CREATE TABLE `cli_script_table` (
  `script_id` int(11) NOT NULL auto_increment,
  `script_name` text NOT NULL,
  `max_run_time` int(11) NOT NULL,
  `average_run_time` int(11) NOT NULL,
  PRIMARY KEY  (`script_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `cli_log_table`
-- 
ALTER TABLE `cli_log_table`
  ADD CONSTRAINT `cli_log_table_script_id_fkey` FOREIGN KEY (`script_id`) REFERENCES `cli_script_table` (`script_id`),
  ADD CONSTRAINT `cli_log_table_host_id_fkey` FOREIGN KEY (`host_id`) REFERENCES `cli_host_table` (`host_id`);

SET FOREIGN_KEY_CHECKS=1;

COMMIT;