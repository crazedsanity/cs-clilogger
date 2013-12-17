<?php
/*
 * Created on Jul 1, 2009
 */


class cli_scriptRunner extends cs_multiProcAbstract {
	
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
	public function __construct() {
		
		$this->csLog = new cli_logger();
		
		//TODO: csLog should have been able to pull the location of the LOCKFILEDIR: pass that to the call below.
		//TODO: root path should be configurable.
		//TODO: find a good way to determine script name when prefixed with interpretter (i.e. "perl test.pl").
		
		$args = $this->csLog->parse_parameters();
		
		$myScriptName = $args[0];
		if(preg_match('/php$/', $args[0]) || preg_match('/php5$/', $args[0]) || preg_match('/phpcgi$/', $args[0])) {
			$myScriptName = $args[0] . ' '. $args[1];
		}
		
		$myScriptName = preg_replace('/[^aA-zZ0-9.-]/', '_', $myScriptName);
		parent::__construct(dirname(__FILE__) .'/../../rw/', $myScriptName, 1);
		
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
