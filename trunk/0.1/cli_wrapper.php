<?php
/*
 * Created on Jul 1, 2009
 * 
 * SVN INFORMATION:::
 * ------------------
 * SVN Signature::::::: $Id$
 * Last Author::::::::: $Author$ 
 * Current Revision:::: $Revision$
 * Repository Location: $HeadURL$ 
 * Last Updated:::::::: $Date$
 */

//TODO: check requirements for running (libraries, constants, environment vars, etc)
//TODO: require an environment variable for the XML config file...
//TODO: The script's version will become important if this is installed on different hosts, as version numbers might be different...
//TODO: consider using the cs-webdblogger library for handling ALL logging.

if(isset($_ENV['LIBDIR'])) {
	$includePath = $_ENV['LIBDIR'];
	$requiredLibs = array(
		'Database Layer (CS-Content:cs_phpDB)'	=> "/cs-content/cs_phpDB.class.php",
		'Version Parser'						=> "/cs-versionparse/cs_version.abstract.class.php",
	);
	
	foreach($requiredLibs as $desc=>$file) {
		$fullPath = $includePath . $file;
		if(file_exists($fullPath)) {
			require_once($fullPath);
		}
		else {
			throw new exception(__METHOD__ .": required library '". $desc ."' unavailable at [". $fullPath ."]");
		}
	}
}
else {
	throw new exception(__FILE__ ." - ". __LINE__ .": failed to locate required 'LIBDIR' environment setting");
}

//TODO: determine required constants/environment vars

// Instantiating the class is all that is needed to get the script to run.
$obj = new cliWrapper();



class cliWrapper {
	
	/** Database object */
	private $dbObj;
	
	/** The full command that was performed... */
	private $fullCommand;
	
	//-------------------------------------------------------------------------
	/**
	 * Handle everything here: if there's something missing, an exception will 
	 * be thrown and things will stop running.
	 */
	public function __construct() {
		$this->get_settings();
		$this->parse_parameters();
		$this->run_script();
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Run the script here...
	 */
	public function run_script() {
		$scriptId = $this->get_script_id();
		$hostId = $this->get_host_id();
		
		//TODO: call the script here (fork?)
		
		//TODO: log the script's output into the database.
	}//end run_script()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Rip out parameters meant for this wrapper script (vs. the script that it 
	 * is wrapping).
	 */
	private function parse_parameters() {
	}//end parse_parameters()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Connect the internal database object.
	 */
	private function connect_db() {
	}//end connect_db()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Determine what the ID of the script is (for database logging).
	 */
	private function get_script_id() {
	}//end get_script_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Get the ID of the host it's running on (for database logging).
	 */
	private function get_host_id() {
	}//end get_host_id()
	//-------------------------------------------------------------------------
	
	
}


?>
