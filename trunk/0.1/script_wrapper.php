<?php
// FOR A LIST OF LIMITATIONS, SEE docs/README.txt
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


function exception_handler(exception $e) {
	$message = $e->getMessage();
	
	if(!preg_match('/FATAL/', $message)) {
		$message = 'FATAL: '. $message;
	}
	echo "\n                ". $message ."\n\n";
	exit(1);
}

set_exception_handler('exception_handler');

if(isset($_ENV['LIBDIR'])) {
	if(!defined('LIBDIR')) {
		define('LIBDIR', $_ENV['LIBDIR']);
	}
	$includePath = $_ENV['LIBDIR'];
	$requiredLibs = array(
		'Database Layer (CS-WebAppLibs:cs_phpDB)'	=> "/cs-webapplibs/cs_phpDB.class.php",
		'Global Functions'							=> "/cs-content/cs_globalFunctions.class.php",
		'Site Config (CS-WebAppLibs:cs_siteConfig)'	=> "/cs-webapplibs/cs_siteConfig.class.php",
		'PHP XML - Parser'							=> "/cs-phpxml/cs_phpxmlParser.class.php"
	);
	
	foreach($requiredLibs as $desc=>$file) {
		$fullPath = $includePath . $file;
		if(file_exists($fullPath)) {
			require_once($fullPath);
		}
		else {
			throw new exception("required library '". $desc ."' unavailable at [". $fullPath ."]");
		}
	}
	
	//if there's an environment variable for the config file, use that: otherwise assume it's in "./config/wrapper.xml"
	if(isset($_ENV['CONFIGFILE']) && file_exists($_ENV['CONFIGFILE'])) {
		$configFile = $_ENV['CONFIGFILE'];
	}
	elseif(file_exists(dirname(__FILE__) .'/config/wrapper.xml')) {
		$configFile = dirname(__FILE__) .'/config/wrapper.xml';
	}
	else {
		throw new exception("couldn't find a config file");
	}
}
else {
	throw new exception("failed to locate required 'LIBDIR' environment setting");
}



require_once(dirname(__FILE__) .'/cli_logger.class.php');
require_once(dirname(__FILE__) .'/cli_scriptRunner.class.php');
$obj = new cli_scriptRunner($configFile);
?>
