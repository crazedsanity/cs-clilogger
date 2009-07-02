--
-- SVN INFORMATION:
-- 
-- SVN Signature::::::: $Id$
-- Last Author::::::::: $Author$ 
-- Current Revision:::: $Revision$
-- Repository Location: $HeadURL$ 
-- Last Updated:::::::: $Date$
--

--
-- HOST Table
--
CREATE TABLE `cli_logger`.`cli_host_table` (
  `host_id` int  NOT NULL AUTO_INCREMENT,
  `host_name` text  NOT NULL,
  PRIMARY KEY (`host_id`)
)
ENGINE = InnoDB;



--
-- Internal logging table
--
CREATE TABLE `cli_logger`.`cli_internal_log_table` (
  `internal_log_id` int  NOT NULL AUTO_INCREMENT,
  `log_timestamp` TIMESTAMP  NOT NULL DEFAULT NOW(),
  `log_data` text  NOT NULL,
  PRIMARY KEY (`internal_log_id`)
)
ENGINE = InnoDB;




--
-- Scripts table
-- NOTE: it may be important, when calculating max/average run times, to link
--	the script to a specific host?  (maybe it should be different when running
--	on a different server... probably a fringe case)
--
CREATE TABLE `cli_logger`.`cli_script_table` (
  `script_id` int  NOT NULL AUTO_INCREMENT,
  `script_name` text  NOT NULL,
  `max_run_time` int  NOT NULL,
  `average_run_time` int  NOT NULL,
  PRIMARY KEY (`script_id`)
)
ENGINE = InnoDB;




--
-- Log Table
--
CREATE TABLE `cli_logger`.`cli_log_table` (
  `log_id` int  NOT NULL AUTO_INCREMENT,
  `script_id` int  NOT NULL,
  `full_command` text  NOT NULL,
  `host_id` int  NOT NULL,
  `start_time` timestamp  NOT NULL DEFAULT NOW(),
  `end_time` timestamp ,
  `output` text ,
  `errors` text ,
  `exit_code` integer ,
  PRIMARY KEY (`log_id`),
  CONSTRAINT `cli_log_table_script_id_fkey` FOREIGN KEY `cli_log_table_script_id_fkey` (`script_id`)
    REFERENCES `cli_script_table` (`script_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `cli_log_table_host_id_fkey` FOREIGN KEY `cli_log_table_host_id_fkey` (`host_id`)
    REFERENCES `cli_host_table` (`host_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT
)
ENGINE = InnoDB;

