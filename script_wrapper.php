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

//attempt to locate the __autoload.php script from cs-content...
if(file_exists(dirname(__FILE__) .'/../cs-content/__autoload.php')) {
	require_once(dirname(__FILE__) .'/../cs-content/__autoload.php');
}

//now try to find a couple of configs.
$siteConfig = null;
$configFile = null;
$baseDir = dirname(__FILE__) .'/../..';
$tryThese = array(
	'rw/siteConfig.xml',
	'rw/config.xml',
	'config/siteConfig.xml',
	'config/config.xml'
);
foreach($tryThese as $pathPart) {
	if(file_exists($baseDir .'/'. $pathPart)) {
		$configFile = $baseDir .'/'. $pathPart;
		$siteConfig = new cs_siteConfig($configFile);
		break;
	}
}
if(is_null($siteConfig)) {
	throw new exception("FATAL: unable to locate cs_siteConfig or a config file (". $configFile .")...");
}

if(!defined('LIBDIR') && isset($_ENV['LIBDIR'])) {
	define('LIBDIR', $_ENV['LIBDIR']);
}


if(!defined('LIBDIR')) {
	throw new exception("failed to locate required 'LIBDIR' environment setting");
}



require_once(dirname(__FILE__) .'/cli_logger.class.php');
require_once(dirname(__FILE__) .'/cli_scriptRunner.class.php');
$obj = new cli_scriptRunner();
?>
