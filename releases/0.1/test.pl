#!/usr/bin/perl

use overload q(<) => sub {};
my %h;
print STDERR __FILE__ .": Error #1: (testing)";
for (my $i=0; $i<50000; $i++) {
	$h{$i} = bless [ ] => 'main';
	print STDOUT '.' if $i % 1000 == 0;
}
print STDOUT __FILE__ .": OUTPUT: '<--Testing data cleansing...\n";

print STDERR __FILE__ .": Error #2: Script failed (testing)";
exit 5;
