
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

use DBI;
use IO::CaptureOutput;


connect_db();
parse_parameters();
handle_fork();


#------------------------------------------------------------------------------
sub parse_parameters {
	
	## Pull in our arguments...
	our $internalArgs = shift(@ARGV);
	
	## get & check the script name.
	our $scriptName = shift(@ARGV);
	if(!length($scriptName)) {
		die "FATAL: no script named!\n";
	}
	elsif(!-f $scriptName) {
		die "FATAL: script doesn't exist";
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
	our $fullCommand = $scriptName ." ". $commandArgs;
	
	$command = $scriptName ." ". $command;
	
		
} ## END parse_parameters()
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
sub run_sql {
	my $tbl, my $pkey, my $retval;
	my $sql = shift(@_);
	chomp($sql);
	
	if($sql =~ /^insert /i) {
		if($sql =~ /^insert into (\S+) \(.+/i) {
			$tbl = $1;
			$pkey = get_table_pkey($tbl);
			if(!length($pkey) || !length($tbl)) {
				die "FATAL: run_sql() failed to retrieve pkey for tbl=(". $tbl .")\n";
			}
		}
		else {
			die "FATAL: run_sql() failed to get tableName from::: ". $sql .")\n";
		}
	}
	
	if($dbh->do($sql)) {
		$retval = true;
		if(length($pkey)) {
			$retval = $dbh->last_insert_id('pg_global', 'public', $tbl, $pkey);
		}
	}
	else {
		die "FATAL: run_sql() failed to execute statement::: ". $sql ."\n";
	}
	
	return($retval);
} ## END run_sql()
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
	
	## Use two single quotes in place of one single quote... because SQL is ghey like that.
	$dbFullCommand = $fullCommand;
	$dbFullCommand = clean_sql_text($dbFullCommand);
	
	if(run_sql("INSERT INTO cli_log_table (script_id, full_command, host_id) "
		."VALUES ($scriptId, '". $dbFullCommand ."', $hostId)")) {
		$logId = $dbh->last_insert_id('pg_global', 'public', 'cli_log_table', 'log_id');
		
		## This captures the STDERR and STDOUT separately, so it can be logged as such...
		($output, $stderr) = IO::CaptureOutput::capture_exec($fullCommand);
		
		## Clean text so it's safe for insertion.
		$output = clean_sql_text($output);
		$stderr = clean_sql_text($stderr);
		
		
		## finalize; set set the end_time, output, errors, and exit_code.
		$myRes = run_sql("UPDATE cli_log_table SET end_time=NOW(), output='". $output ."', "
			."exit_code=". $? .", errors='". $stderr ."' WHERE log_id=". $logId);
	}
	else {
		
		die "FATAL: unable to log start of script...\n";
	}
	
} ## END run_script()
#------------------------------------------------------------------------------



#------------------------------------------------------------------------------
sub get_host_id {
	my $host, my $hostId, my $retval;
	if(-e '/bin/hostname') {
		my $host = `/bin/hostname`;
		chomp($host);
		
		## Now see if it's in the database...
		($hostId, $dbHostname) = $dbh->selectrow_array("SELECT * FROM "
			."cli_host_table WHERE host_name='". $host ."'");
		
		if($hostId > 0) {
			$retval = $hostId;
		}
		else {
			$hostId = run_sql("INSERT INTO cli_host_table (host_name) VALUES ('". $host ."')");
			if(!$hostId =~ /[0-9]+/ || $hostId < 1) {
				die "FATAL: can't get host_id (". $hostId .")...\n";
			}
			$retval = $hostId;
		}
	}
	else {
		die "FATAL: can't get hostname...\n";
	}
	
	return($retval);
} ## END get_host_id()
#------------------------------------------------------------------------------



#------------------------------------------------------------------------------
sub get_table_pkey {
	my $table = shift(@_);
	if(!length($table)) {
		die "get_table_pkey(): no table specified (". $table .")\n";
	}
	
	($retval) = $dbh->primary_key('pg_global', 'public', $table);
	if(!$retval =~ /\w{1,}/) {
		die "FATAL: get_table_pkey(): failed to get valid data (". $retval .")";
	}
	
	return($retval);
} ## END get_table_pkey()
#------------------------------------------------------------------------------



##-----------------------------------------------------------------------------
sub handle_fork {
	
	# This will (eventually) handle forking one or more processes to run scripts.
	
	my $numChildren = 0;
	my %children;
	
	##for now, hard-code this value...
	my $maxChildren = 1;
	
	if(!$pid || $pid > 0) {
		while($numChildren < $maxChildren) {
			$numChildren++;
			$pid = fork();
			
			if($pid == 0) {
				run_script();
				exit;
			}
			
			$children{$numChildren}=$pid;
		}
	}
	
	
	my $childCount = keys(%children);
	
	while($childCount > 0) {
		foreach my $key (keys %children) {
			$value = $children{$key};
			
			if(!$value =~ /^[0-9]{1,}$/) {
				die "Invalid PID (". $value .")!\n";
			}
			
			$childStatus = waitpid($value, WNOHANG);
			
			if($childStatus > 0) {
				## Remove the child from our array.
				delete($children{$key});
				$childCount--;
			}
		}
		
		## sleep (for half a second).
		select(undef,undef,undef,.5);
	}
	
	
} ## END handle_fork()
##-----------------------------------------------------------------------------



##-----------------------------------------------------------------------------
sub clean_sql_text() {
	$text = shift(@_);
	$text =~ s/'/''/g;
	return($text);
}#end clean_sql_text()
##-----------------------------------------------------------------------------