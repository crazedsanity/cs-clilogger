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
	
	/** Database type (for creating dbObj) */
	private $dbType=null;
	
	/** The full command that was performed... */
	private $fullCommand;
	
	/** Internal parameter list */
	private $internalParams;
	
	/** Name of the actual script. */
	private $scriptName;
	
	/** Parameters (from the config) used to connect to the database */
	private $dbParams=null;
	
	/** ID we've created the entry under, so we can handle checking in. */
	private $logId;
	
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
		$this->gfObj->debugRemoveHr=1;
		
		if(!file_exists($configFile)) {
			throw new exception("missing configuration file");
		}
		
		$this->parse_parameters($configFile);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Log the script's output here.
	 */
	public function log_script_end($stdout, $stderr, $returnVal) {
		if(!$this->dbObj->ping()) {
			$this->connect_db();
		}
		try {
			$sql = "UPDATE cli_log_table SET end_time=CURRENT_TIMESTAMP, output='" . 
					$this->gfObj->cleanString($stdout, 'sql') ."', errors='". 
					$this->gfObj->cleanString($stderr, 'sql') ."', " .
					"exit_code=". $returnVal ." WHERE log_id=". $this->logId;
			$this->dbObj->run_update($sql);
		}
		catch(exception $e) {
			throw new exception("failed to log final output::: ". $e->getMessage() ."\nSQL::: ". $sql);
		}
	}//end log_script_end()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Rip out parameters meant for this wrapper script (vs. the script that it 
	 * is wrapping).
	 */
	private function parse_parameters($configFile) {
		
		if(count($_SERVER['argv']) >= 3) {
			$myArgs = $_SERVER['argv'];
			$thisFile = array_shift($myArgs);
			$this->internalParameters = array_shift($myArgs);
			
			//all that is left in the array is what we refer to as the "full command".
			$this->fullCommand = $this->gfObj->string_from_array($myArgs, null, ' ');
			
			
			//TODO: check if an interpreter was used (i.e. "/usr/bin/perl -w ./script.pl")
			$this->scriptName = $myArgs[0];
			
			
		}
		else {
			throw new exception(__METHOD__ .": not enough arguments");
		}
		
		$xmlParser = new cs_phpxmlParser(file_get_contents($configFile));
		$allData = $xmlParser->get_tree(true);
		if(isset($allData[$xmlParser->get_root_element()]['DBCONNECT'])) {
			$dbParams = $allData[$xmlParser->get_root_element()]['DBCONNECT'];
			$dbType = $dbParams['DBTYPE'];
			unset($dbParams['DBTYPE']);
			
			$params = array();
			foreach($dbParams as $i=>$v) {
				$params[strtolower($i)] = $v;
			}
			$this->dbType = $dbType;
			$this->dbParams = $params;
			$this->connect_db();
		}
		else {
			throw new exception(__METHOD__ .": could not find database parameters in config file");
		}
		
	}//end parse_parameters()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Connect the internal database object.
	 */
	private function connect_db() {
		if(!is_null($this->dbType) && !is_null($this->dbParams) && is_array($this->dbParams)) {
			try {
				$this->dbObj = new cs_phpDB($this->dbType);
				$this->dbObj->connect($this->dbParams, true);
			}
			catch(exception $e) {
				throw new exception(__METHOD__ .": fatal error while connecting database::: ". $e->getMessage());
			}
		}
		else {
			throw new exception("required items (dbType and dbParams) not set");
		}
	}//end connect_db()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Determine what the ID of the script is (for database logging).
	 */
	private function get_script_id() {
		
		$scriptName = $this->gfObj->cleanString($this->scriptName,'sql_insert');
		$sql = "SELECT script_id FROM cli_script_table WHERE script_name='". $scriptName ."'";
		
		try {
			$data = $this->dbObj->run_query($sql);
			
			if($data == false) {
				//no script yet: create one.
				$sql = "INSERT INTO cli_script_table (script_name) VALUES ('". $scriptName ."')";
				
				$scriptId = $this->dbObj->run_insert($sql);
			}
			elseif(is_array($data) && count($data) == 1) {
				$scriptId = $data['script_id'];
			}
			else {
				throw new exception(__METHOD__ .": no data, too much data, or unknown error");
			}
		}
		catch(exception $e) {
			throw new exception(__METHOD__ .": failed to retrieve script_id for '". $this->scriptName ."'");
		}
		
		return($scriptId);
	}//end get_script_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Get the ID of the host it's running on (for database logging).
	 */
	private function get_host_id() {
		
		if(file_exists('/bin/hostname')) {
			$hostname = strtolower(exec('/bin/hostname --long'));
		}
		else {
			throw new exception(__METHOD__ .": unable to determine hostname of machine");
		}
		
		//now let's retrieve the ID associated with that one.
		try {
			$sql = "SELECT host_id FROM cli_host_table WHERE host_name='". $hostname ."'";
			
			$data = $this->dbObj->run_query($sql);
			
			if($data == false) {
				$sql = "INSERT INTO cli_host_table (host_name) VALUES ('". $hostname ."')";
				
				$hostId = $this->dbObj->run_insert($sql);	
			}
			elseif(is_array($data) && count($data) == 1) {
				$hostId = $data['host_id'];
			}
			else {
				throw new exception(__METHOD__ .": invalid data, too much, not enough, or unknown error");
			}
		}
		catch(exception $e) {
			throw new exception(__METHOD__ .": failed to retrieve/insert host_id for (". $hostname .")... ". $e->getMessage());
		}
		
		return($hostId);
	}//end get_host_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function checkin() {
		if(!$this->dbObj->ping()) {
			$this->connect_db();
		}
		if(!is_numeric($this->logId)) {
			$hostId = $this->get_host_id();
			$scriptId = $this->get_script_id();
			$sql = "INSERT INTO cli_log_table (script_id, host_id, full_command, start_time) " .
					"VALUES (". $scriptId .", ". $hostId .", '". 
					$this->gfObj->cleanString($this->fullCommand, 'sql_insert') ."', CURRENT_TIMESTAMP)";
			
			try {
				$this->logId = $this->dbObj->run_insert($sql);
				$checkinResult = true;
			}
			catch(exception $e) {
				throw new exception("failed to do initial checkin::: ". $e->getMessage());
			}
		}
		else {
			try {
				$sql = "UPDATE cli_log_table SET last_checkin=CURRENT_TIMESTAMP WHERE log_id=". $this->logId;
				$this->dbObj->run_update($sql);
				$checkinResult = true;
			}
			catch(exception $e) {
				throw new exception("failed to perform checkin::: ". $e->getMessage());
			}
		}
		
		return($checkinResult);
	}//end checkin()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_full_command() {
		return($this->fullCommand);
	}//end get_full_command()
	//-------------------------------------------------------------------------
	
	
}

?>
