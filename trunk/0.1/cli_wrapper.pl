
##
#
# Created by
#	Dan Falconer
#		on
#	October 06, 2008
#
#
#
# * SVN INFORMATION:::
# * ------------------
# * SVN Signature::::::: $Id$
# * Last Author::::::::: $Author$ 
# * Current Revision:::: $Revision$
# * Repository Location: $HeadURL$ 
# * Last Updated:::::::: $Date$
#
#
#
# PARAMETERS (example script usage):
#	/usr/bin/perl cli_wrapper.pl ""  myScript.bash param1=x param2=y crazedsanity@users.sourceforge.net
#    ^^^^^^^^^^^   ^^^^^^^^^^^^  ^^   ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
#	  1               2           3                            4
#
#	1.) Must be run through Perl; left out of script for portability.
#	2.) This script.
#	3.) RESERVED: this is a placeholder for future options for this script.
#	4.) This is how the script is normally run.  All parameters are passed to the script as-is.
#
##

use Date::Parse;
use Data::Dumper;
use DBD::Pg;


#$ENV{'DBI_DRIVER'} = 'Pg';

our $dbi = DBI->connect("dbi:Pg:dbname=cli_logger;host=localhost;user=cli;password=%%dbPass%%");



## Pull in our arguments...
$myArgs = shift(@ARGV);


#print Dumper(@ARGV) ."\n";

$scriptName = shift(@ARGV);

if(!length($scriptName)) {
	die "FATAL: no script named!\n";
}

while(@ARGV) {
	$addThis = shift(@ARGV);
	if(length($command)) {
		$command .= " ";
	}
	
	## re-add quotes as needed (this doesn't handle single quotes... problematic?)
	if($addThis =~ /\s+/) {
		$addThis = '"'. $addThis .'"';
	}
	$command .= $addThis;
}

$command = $scriptName ." ". $command;

print "MY ARGS: $myArgs\nCOMMAND: $command\n";


#------------------------------------------------------------------------------
sub read_config {
	# NOTE: this is HARD-CODED
	%config = {
		'host'		=> "localhost",
		'port'		=> "5432",
		'dbname'	=> 'cli_logger',
		'user'		=> "cli",
		'pass'		=> "%%dbPass%%"
	};
	
	return(%config);
} ## END read_config()
#------------------------------------------------------------------------------



#------------------------------------------------------------------------------
sub connect_db {
	
} ## END connect_db()
#------------------------------------------------------------------------------




