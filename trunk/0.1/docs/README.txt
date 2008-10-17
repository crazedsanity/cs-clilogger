$Id$

PARAMETERS (example script usage):
	/usr/bin/perl cli_wrapper.pl ""  myScript.bash param1=x param2=y crazedsanity@users.sourceforge.net
   ^^^^^^^^^^^   ^^^^^^^^^^^^  ^^   ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	  1               2           3                            4

	1.) Must be run through Perl; left out of script for portability.
	2.) This script.
	3.) RESERVED: this is a placeholder for future options for this script.
	4.) This is how the script is normally run.  All parameters are passed to the script as-is.


== Ideas For Future Implementation ==

API for (returns are base64-encoded, possibly encrypted):
	* Retrieving Data
		-- Scripts:
			+ most recent scripts
			+ all running scripts
			+ most recently finished scripts
		-- Reports
		-- Current Configuration
		-- Output Format Options:
			+ local template (HTML)
			+ CSV (with header column)
			+ XML
	* Updating Data
		-- configuration
		-- script-specific settings

Script Running Thoughts:
	* Can run all the time
		-- config file lists scripts to run
		-- daemon constantly checks config for changes
		-- other instances trying to daemonize will die (if configured to use
		the same *.pid/*.lock file)
	* Single instance runs separate process for script
		-- parent process does check-ins
		-- child process gives raw output (into database)
		-- special table holds statistics for main process "uptime"

Config Thoughts:
	* Options:
		-- Specify maximum global concurrency
		-- Specify maximum per-script concurrency
		-- Handling queueing (when script has too many instances, etc)
	* Database Storage
		-- store running config in DB
		-- allow updates via DB (special table to denote incoming changes)
		-- updates to config file written to DB

Handling DB Down Problems:
	* log inserts (with timestamps specified) to a file if DB is down
	* constantly check to see if DB is up; when it is, run SQL scripts