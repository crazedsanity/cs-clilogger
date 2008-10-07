
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

#our $dbi = DBI->connect("dbi:Pg:dbname=cli_logger;host=localhost;user=cli;password=%%dbPass%%");
connect_db();
parse_parameters();


#------------------------------------------------------------------------------
sub parse_parameters {
	
	## Pull in our arguments...
	our $internalArgs = shift(@ARGV);
	
	## get & check the script name.
	my $scriptName = shift(@ARGV);
	if(!length($scriptName)) {
		die "FATAL: no script named!\n";
	}
	
	## okay, build the command string.
	our $command = "";
	while(@ARGV) {
		my $addThis = shift(@ARGV);
		if(length($command)) {
			$command .= " ";
		}
		
		## re-add quotes as needed (attempts to handle single quotes)...
		if($addThis =~ /\s+/) {
			if($addThis =~ /"/) {
				$addThis = "'". $addThis ."'";
			}
			else {
				$addThis = '"'. $addThis .'"';
			}
		}
		$command .= $addThis;
	}
	
	$command = $scriptName ." ". $command;
	
	print "MY ARGS: $internalArgs\nCOMMAND: $command\n";
		
} ## END parse_parameters()
#------------------------------------------------------------------------------



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
	our $dbi = DBI->connect("dbi:Pg:dbname=cli_logger;host=localhost;user=cli;password=%%dbPass%%");
} ## END connect_db()
#------------------------------------------------------------------------------




