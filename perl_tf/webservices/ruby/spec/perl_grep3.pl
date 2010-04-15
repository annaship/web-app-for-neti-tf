#! /usr/bin/perl
open FILE, "/Users/anna/work/web_app/perl_tf/webservices/ruby/spec/americanseashell-no-index.txt" or die $!;
@text = <FILE>;
close FILE;

# print @text;

while(<STDIN>)
  {
	  my($line) = $_;
		chomp($line);
		$b = "americanseashell-no-index.txt";
		$cmd = "GREP_COLOR=32; export GREP_COLOR; grep -n --color=always \"$line\\b\" $b";
		system $cmd;
	}
	
	# open FILE, ">/Users/anna/work/perl/grep_in_tname.txt" or die $!; 
	# print FILE @grepNames; 
	# close FILE;
	

# 

