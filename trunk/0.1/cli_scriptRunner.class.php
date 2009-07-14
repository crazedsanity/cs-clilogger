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


require_once(dirname(__FILE__) ."/../cs-multithread/multiThread.abstract.class.php");


class cli_scriptRunner extends multiThreadAbstract {
	
	/** Logging system */
	private $csLog;
	
	/** Global functions class */
	protected $gfObj;
	
	/** Location of configuration file. */
	private $configFile;
	
	//-------------------------------------------------------------------------
	/**
	 * Handle everything here: if there's something missing, an exception will 
	 * be thrown and things will stop running.
	 */
	public function __construct($configFile) {
		
		$this->configFile = $configFile;
		
		$this->csLog = new cli_logger($configFile);
		
		//TODO: csLog should have been able to pull the location of the LOCKFILEDIR: pass that to the call below.
		parent::__construct(null, 'test.pl', 1);
		
		//set the version file location, a VERY important part of this system.
		$this->set_version_file_location($configFile);
		
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		$this->gfObj->debugRemoveHr=1;
		
		//if all goes well, everything will be logged by dead_child_handler().
		$this->set_checkin_delay(5);
		$this->run_script($this->csLog->get_full_command());
		$this->csLog->checkin();
		
		$this->finished();
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Required method from the parent class.
	 */
	protected function dead_child_handler($childNum, $exitStatus, array $output) {
		$this->message_handler(__METHOD__, ": running... childNum=(". $childNum .")");
		$this->csLog->log_script_end($output['stdout'], $output['stderr'], $exitStatus);
	}//end dead_child_handler()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function checkin() {
		$this->csLog->checkin();
	}//end checkin()
	//-------------------------------------------------------------------------
	
}

?>
