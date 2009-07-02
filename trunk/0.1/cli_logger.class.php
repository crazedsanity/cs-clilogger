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

//TODO: The script's version will become important if this is installed on different hosts, as version numbers might be different...
//TODO: consider using the cs-webdblogger library for handling ALL logging.




class cli_logger extends cs_versionAbstract {
	
	/** Database object */
	private $dbObj;
	
	/** The full command that was performed... */
	private $fullCommand;
	
	/** Internal parameter list */
	private $internalParams;
	
	//-------------------------------------------------------------------------
	/**
	 * Handle everything here: if there's something missing, an exception will 
	 * be thrown and things will stop running.
	 */
	public function __construct($configFile) {
		//set the version file location, a VERY important part of this system.
		$this->set_version_file_location($configFile);
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		
		if(file_exists($configFile)) {
			$xmlParser = new cs_phpxmlParser(file_get_contents($configFile));
		}
		else {
			throw new exception("missing configuration file");
		}
		
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
		
		if(count($_SERVER['argv']) >= 3) {
			$myArgs = $_SERVER['argv'];
			$thisFile = array_shift($myArgs);
			$this->internalParameters = array_shift($myArgs);
			
			//all that is left in the array is what we refer to as the "full command".
			$this->fullCommand = $this->gfObj->string_from_array($myArgs, null, ' ');
			$this->gfObj->debug_print(__METHOD__ .": fullCommand::: ". $this->fullCommand ."\nARRAY::: ". $this->gfObj->debug_print($myArgs,0));
		}
		else {
			throw new exception(__METHOD__ .": not enough arguments");
		}
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
