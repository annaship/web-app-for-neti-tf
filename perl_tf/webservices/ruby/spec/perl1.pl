#! /usr/bin/perl

while(<STDIN>)                                                                                        
  {                                                                                                   
    my($line) = $_;                                                                             
          chomp($line);                                                                         
          if ($line =~ m/<name>([^<][^\/]+)<\/name>/)                                       
          {                                                                                     
                  $t_name = $1;                                                                 
                  $t_name =~ s/<[^>]+>//g;                                                      
                                                                                                
                  print "$t_name\n";                                                            
          }                                                                                     
   }
	# open FILE, ">/Users/anna/work/perl/grep_in_tname.txt" or die $!; 
	# print FILE @grepNames; 
	# close FILE;
	

# 

