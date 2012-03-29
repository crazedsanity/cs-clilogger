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
CREATE TABLE cli_host_table (
	host_id serial NOT NULL PRIMARY KEY,
	host_name text NOT NULL
);

--
-- Internal logging table
--
CREATE TABLE cli_internal_log_table (
	internal_log_id serial NOT NULL PRIMARY KEY,
	log_timestamp timestamp NOT NULL DEFAULT NOW(),
	log_data text NOT NULL
);


--
-- Scripts table
-- NOTE: it may be important, when calculating max/average run times, to link
--	the script to a specific host?  (maybe it should be different when running
--	on a different server... probably a fringe case)
--
CREATE TABLE cli_script_table (
	script_id serial NOT NULL PRIMARY KEY,
	script_name text NOT NULL UNIQUE,
	max_run_time integer,
	average_run_time integer
);

--
-- Log Table
--
CREATE TABLE cli_log_table (
	log_id serial NOT NULL PRIMARY KEY,
	script_id integer NOT NULL REFERENCES cli_script_table(script_id),
	full_command text NOT NULL,
	host_id integer NOT NULL REFERENCES cli_host_table(host_id),
	start_time timestamp NOT NULL DEFAULT NOW(),
	last_checkin timestamp,
	end_time timestamp,
	output text,
	errors text,
	exit_code integer
);


--
-- Create our user (NOTE::: when loading this, the password should be modified
--		to be whatever is set during setup.
--
--CREATE USER cli WITH UNENCRYPTED PASSWORD '%%dbPass%%';

GRANT ALL ON SCHEMA public TO cli;
GRANT ALL ON TABLE cli_host_table TO cli;
GRANT ALL ON TABLE cli_internal_log_table TO cli;
GRANT ALL ON TABLE cli_log_table TO cli;
GRANT ALL ON TABLE cli_script_table TO cli;


INSERT INTO cli_internal_log_table (log_data) VALUES ('Database initialized, cli_logger v0.1');

