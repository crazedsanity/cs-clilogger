<?php
/*
 * Created on Jan 13, 2009
 * 
 * FILE INFORMATION:
 * 
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedBy$
 * $LastChangedRevision$
 */




//=============================================================================
class testOfCSCLILogger extends UnitTestCase {
	
	//-------------------------------------------------------------------------
	function setUp() {
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		
		//forge stuff into the server's arguments array.
		$_SERVER['argv'] = array(
			0	=> __FILE__,
			1	=> "",
			2	=> "test.pl"
		);
		$_SERVER['argc'] = count($_SERVER['argv']);
		
		$this->cli = new _test_cliIntermediary();
		
		//load schema.
		$schemaFile = dirname(__FILE__) .'/../schema/schema.'. $this->cli->dbType .'.sql';
		if($this->assertTrue(file_exists($schemaFile))) {
			//if there are tables or a user already, make sure they get dropped.
			$preUpdates = array(
				"DROP TABLE cli_host_table, cli_internal_log_table, cli_log_table, cli_script_table cascade",
				"REVOKE ALL ON SCHEMA public FROM cli",
				"DROP ROLE IF EXISTS cli;"
			);
			foreach($preUpdates as $sql) {
				try {
					$this->cli->dbObj->run_update($sql, true);
				}
				catch(exception $e) {
					//
					$this->gfObj->debug_print(__METHOD__ ." [NOTICE]: pre statement failed ::: ". $e->getMessage());
				}
			}
			$this->cli->doTrans();
			$this->assertFalse($this->cli->dbObj->run_update("CREATE USER cli", true));
			$this->assertTrue($this->cli->dbObj->run_update(file_get_contents($schemaFile), true));
		}
		else {
			throw new exception(__METHOD__ .": schema file missing (". $schemaFile .")");
		}
		
	}//end setUp()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function test_basics() {
		
		$this->assertEqual($_SERVER['argv'][2], $this->cli->fullCommand);
		$this->assertEqual($this->cli->fullCommand, $this->cli->get_full_command());
		$this->assertEqual(1, $this->cli->get_host_id());
		$this->assertEqual(1, $this->cli->get_script_id());
		
		//make sure checkin works...
		$this->assertEqual(1, $this->cli->checkin());
		$this->assertEqual(2, $this->cli->checkin());
		
		
		$this->assertEqual(1, $this->cli->log_script_end(null, null, 0));
	}//end test_basics()
	//-------------------------------------------------------------------------
	
}//end testOfCSCLILogger{}
//=============================================================================



//=============================================================================
class _test_cliIntermediary extends cli_logger {
	public function __construct() {
		parent::__construct();
		//$this->dbObj->beginTrans();
	}//end __construct()
	
	
	public function doTrans() {
		$this->dbObj->beginTrans();
	}//end doTrans()
	
	
	public function get_script_id() {
		return(parent::get_script_id());
	}//end get_script_id()
	
	public function get_host_id() {
		return(parent::get_host_id());
	}//end get_host_id()
	
}//end _test_cliIntermediary
//=============================================================================
?>
