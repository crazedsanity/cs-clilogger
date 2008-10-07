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
	script_name text NOT NULL,
	max_run_time integer,
	average_run_time integer
);

--
-- Log Table
--
CREATE TABLE cli_log_table (
	log_id serial NOT NULL PRIMARY KEY,
	script_id integer NOT NULL REFERENCES cli_script_table(script_id),
	host_id integer NOT NULL REFERENCES cli_host_table(host_id),
	start_time timestamp NOT NULL DEFAULT NOW,
	end_time timestamp,
	output text,
	exit_code integer
);