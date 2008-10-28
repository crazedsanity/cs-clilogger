#!/usr/bin/perl

use overload q(<) => sub {};
my %h;
print STDERR "First error (testing)";
for (my $i=0; $i<50000; $i++) {
	$h{$i} = bless [ ] => 'main';
	print STDOUT '.' if $i % 1000 == 0;
}
print STDOUT "'<--Testing data cleansing...\n";

print STDERR "Script failed (testing)";
exit 5;
