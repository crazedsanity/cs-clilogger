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
	
	//-------------------------------------------------------------------------
	/**
	 * Handle everything here: if there's something missing, an exception will 
	 * be thrown and things will stop running.
	 */
	public function __construct($configFile) {
		//set the version file location, a VERY important part of this system.
		$this->set_version_file_location($configFile);
		
		$this->csLog = new cli_logger($configFile);
		
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		$this->gfObj->debugRemoveHr=1;
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Run the script here...
	 */
	public function run_script() {
		
		// Log script's initial start here...
		$this->csLog->checkin();
		
		$this->set_max_children(1);
		$this->spawn();
		if($this->is_child()) {
			$this->message_handler(__METHOD__, "I'm just a child... (". $this->get_myPid() ."), parentPid=(". $this->get_parentPid() .")", 'NOTICE');
			sleep(10);
			$this->finished();
		}
		elseif($this->is_parent()) {
			$this->message_handler(__METHOD__, "This is the parent... (". $this->get_myPid() .")", 'FATAL');
			$this->gfObj->debug_print($this,1);
			$this->finished();
		}
		else {
			throw new exception("failed to spawn new thread/fork");
		}
		
		#//TODO: call the script here (fork?)
		#$returnVal = null;
		#$output = system($this->fullCommand, $returnVal);
		
	}//end run_script()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Required method from the parent class.
	 */
	protected function dead_child_handler($childNum, $qName, $exitStatus) {
		$this->gfObj->debug_print($this,1);
	}//end dead_child_handler()
	//-------------------------------------------------------------------------
	
	
}

?>
