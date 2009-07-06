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
		parent::__construct();
		
		//set the version file location, a VERY important part of this system.
		$this->set_version_file_location($configFile);
		
		
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
			
			$exitVal = null;
			$command = $this->csLog->get_full_command();
			$this->message_handler(__METHOD__, "COMMAND::: ". $command,1);
			$output = passthru($this->csLog->get_full_command(), $exitVal);
			$this->message_handler(__METHOD__, "output of command::: ". $output);
			
			$this->csLog->log_script_end($output, $exitVal);
			
			$this->finished();
		}
		elseif($this->is_parent()) {
			while($this->get_num_children() > 0) {
				try {
					$this->csLog->checkin();
				}
				catch(exception $e) {
					$this->message_handler(__METHOD__, "IGNORNING exception: ". $e->getMessage());
				}
				$numKids = $this->get_num_children();
				if($numKids < 1) {
					break;
				}
				else {
					$this->message_handler(__METHOD__, "numChildren=(". $numKids .")");
					sleep(1);
				}
			}
			$this->finished();
		}
		else {
			throw new exception("failed to spawn new thread/fork");
		}
		
	}//end run_script()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Required method from the parent class.
	 */
	protected function dead_child_handler($childNum, $qName, $exitStatus) {
		#$this->gfObj->debug_print($this,1);
		#$this->csLog->checkin();
		$this->message_handler(__METHOD__, ": running... childNum=(". $childNum ."), qName=(". $qName ."), exitStatus=(". $exitStatus .")");
	}//end dead_child_handler()
	//-------------------------------------------------------------------------
	
}

?>
