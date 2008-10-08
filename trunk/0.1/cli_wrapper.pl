
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
##

use Date::Parse;
use Data::Dumper;
use DBI;




#$ENV{'DBI_DRIVER'} = 'Pg';

#our $dbi = DBI->connect("dbi:Pg:dbname=cli_logger;host=localhost;user=cli;password=%%dbPass%%");
connect_db();
parse_parameters();
run_script();


#------------------------------------------------------------------------------
sub parse_parameters {
	
	## Pull in our arguments...
	our $internalArgs = shift(@ARGV);
	
	## get & check the script name.
	our $scriptName = shift(@ARGV);
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
	our $commandArgs = $command;
	
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
	# The AutoCommit attribute should always be explicitly set
	#TODO: call read_config() to get these parameters...
	our $sth;
	our $dbh = DBI->connect("dbi:Pg:dbname=cli_logger;user=cli", '', '', {AutoCommit => 1});
	
	# For some advanced uses you may need PostgreSQL type values:
	use DBD::Pg qw(:pg_types);
	
	# For asynchronous calls, import the async constants:
	use DBD::Pg qw(:async);
	
	
	## Log that we connected, for sanity.
	$dbh->do("INSERT INTO cli_internal_log_table (log_data) VALUES ('Successful connection')");
	
	if(!$dbh) {
		die("FATAL: unable to connect to database...\n");
	}
	
} ## END connect_db()
#------------------------------------------------------------------------------



#------------------------------------------------------------------------------
sub get_script_id {
	($scriptId, $dbScriptName) = $dbh->selectrow_array("SELECT * FROM cli_script_table WHERE script_name='". $scriptName ."'");
	
	
	if($scriptId > 0) {
		$retval = $scriptId;
	}
	else {
		if(!$dbh->do("INSERT INTO cli_script_table (script_name) VALUES ('". $scriptName ."')")) {
			die "FATAL: unable to create new script_id\n";
		}
		else {
			$retval = get_script_id();
		}
	}
	
	return($retval);
} ## END get_script_id()
#------------------------------------------------------------------------------



#------------------------------------------------------------------------------
sub run_script {
	my $scriptId = get_script_id();
	my $hostId = get_host_id();
	
	print "Script_id=(". $scriptId ."), host_id=(". $hostId .")\n";
	
	
	if($dbh->do("INSERT INTO cli_log_table (script_id, full_command, host_id) "
		."VALUES ($scriptId, '', $hostId)")) {
		$logId = $dbh->last_insert_id('pg_global', 'public', 'cli_log_table', 'log_id');
		
		print "Log_id=(". $logId .")\n";
	}
	else {
		die "FATAL: unable to log start of script...\n";
	}
	
} ## END run_script()
#------------------------------------------------------------------------------



#------------------------------------------------------------------------------
sub get_host_id {
	if(-e '/bin/hostname') {
		$host = `/bin/hostname`;
		chomp($host);
		
		## Now see if it's in the database...
		($hostId, $dbHostname) = $dbh->selectrow_array("SELECT * FROM "
			."cli_host_table WHERE host_name='". $host ."'");
		
		if($hostId > 0) {
			$retval = $hostId;
		}
		else {
			if(!$dbh->do("INSERT INTO cli_host_table (host_name) VALUES ('". $host ."')")) {
				die "FATAL: can't get host_id...\n";
			}
			$retval = get_host_id();
		}
	}
	else {
		die "FATAL: can't get hostname...\n";
	}
	
	return($retval);
} ## END get_host_id()
#------------------------------------------------------------------------------
