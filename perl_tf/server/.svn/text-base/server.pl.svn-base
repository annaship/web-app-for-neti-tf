#!/usr/bin/env perl
$|++;

#system "cls";

use IO::Socket qw(:DEFAULT :crlf);
use IO::Select;


#use Math::Round;
use utf8;
$SIG{CHLD} = sub {wait ()};

print "\n\nCreating listener...\n\n";

my $main_socket = new IO::Socket::INET (
        LocalHost => '127.0.0.1',
        LocalPort => '1234',
        Proto => 'tcp',
        Listen => 10,
        Reuse => 1,
    );
die "Could not create socket: $!\n" unless $main_socket;


print "Loading wordlists...\n\n";
&loadWordLists();
print "Wordlists loaded.\n\n";


$readable_handles = new IO::Select();
$readable_handles->add($main_socket);
while(1) 
{
    #print "start\n";
    ($new_readable) = IO::Select->select($readable_handles, undef, undef, 0);
    
    $clientNumber = 0;
    
    foreach $thisclient (@$new_readable)
    {
        $clientNumber++;
        #print "foreach - $numClients\n";
        if($thisclient == $main_socket)
        {
            print "Reloading wordlists...\n";
            reloadWordLists();
            print "Wordlists reloaded\n";
            print "Connecting\n";
            $new_sock = $thisclient->accept();
            $readable_handles->add($new_sock);
            undef($lastGenus[$clientNumber]);
        }else
        {
            $inputString = <$thisclient>;
            if($inputString)
            {
    	        $inputString = trim($inputString);
    	        
    	        if($inputString eq "ykF7RYlQFHKxetrZCUgVB0WtgzIU - reload dictionaries")
    	        {
    	            print "Reloading wordlists...\n";
    	            reloadWordLists();
    	            print "Wordlists reloaded\n";
    	            print $thisclient "Wordlists reloaded\n";
    	            next;
    	        }
    	        
    	        if($inputString eq "ykF7RYlQFHKxetrZCUgVB0WtgzIU - reload genera")
    	        {
    	            print "Reloading genera...\n";
    	            undef($lastGenus[$clientNumber]);
    	            print "Genera reloaded\n";
    	            print $thisclient "Genera reloaded\n";
    	            next;
    	        }
    	        
                # parse string into parts
                $inputString =~ s/\n/ /g;
                $inputString =~ s/\r/ /g;
                if($inputString =~ /^(.*)\|([^\|]*)\|([^\|]*)\|([^\|]*)\|([^\|]*)$/)
                {
                    $candidateWord = trim($1);
                    $currentString = $2;
                    $currentStringState = $3;
                    $wordListMatches = $4;
                    $checkNameModels = $5;
                    $candidateWord =~ s/\|/ /g;
                }else
                {
                    print $thisclient "||0||-1||\n";
                    next;
                }
                
                
                # clear out spaces in input string
                #$inputString =~ s/ //g;
                
                $candidateWord = trim($candidateWord);
                $cleanCandidateWord = clean($candidateWord);
                $lcCandidateWord = lc($cleanCandidateWord);
                $currentString = trim($currentString);
                $currentStringState = trim($currentStringState);
                $wordListMatches = trim($wordListMatches);
                
                
                # Has no letters, no need to search dictionaries
                if (!$cleanCandidateWord)
                {
                    scoreReturnString();
                    if($thisclient)
                    {
                        print $thisclient "||0|$currentString|$wordListMatches||\n";
                    }
                    next;
                }
                
                #### LOOK UP WORD IN WORDLISTS
                
                # current name string is genus
                if ($currentStringState eq "genus")
                {
                    # This word is a Species
                    $score = isSpecies($candidateWord, $lcCandidateWord, $cleanCandidateWord, $currentString);
                    if ($score ne '-1')
                    {
                        # Currently have potential abbreviated genus
                        if ($currentString =~ /^([A-Z-][a-z]?)$/)
                        {
                            # Found match for initial
                            if ($lastGenus[$clientNumber]{$1})
                            {
                                #$currentString = "[".$lastGenus[$clientNumber]{$1}."]";
                                $currentString = $1."[".substr($lastGenus[$clientNumber]{$1},length($1))."]";
                                
                                if($candidateWord =~ /[,;\.\)\]][^A-Za-z]*$/i)
                                {
                                    $currentString = "$currentString $cleanCandidateWord";
                                    $wordListMatches = $wordListMatches.$score;
                                    scoreReturnString();
                                    print $thisclient "||0|$currentString|$wordListMatches||\n";
                                }else
                                {
                                    print $thisclient "$currentString $cleanCandidateWord|species|$wordListMatches"."$score||-1||\n";
                                }
                            }
                            
                            #No match for initial, return blank string
                            else
                            {
                                #$currentString = "$1.";
                                print $thisclient "||0||-1||\n";
                            }
                        }else
                        {
                            if($candidateWord =~ /[,;\.\)\]][^A-Za-z]*$/i)
                            {
                                $currentString = "$currentString $cleanCandidateWord";
                                $wordListMatches = $wordListMatches.$score;
                                scoreReturnString();
                                print $thisclient "||0|$currentString|$wordListMatches||\n";
                            }else
                            {
                                print $thisclient "$currentString $cleanCandidateWord|species|$wordListMatches"."$score||-1||\n";
                            }
                        }
                        next;
                    }
                    
                    
                    # possible abbreviated genus
                    if ($candidateWord =~ /^[A-Z][a-z]?\.$/)
                    {
                        scoreReturnString();
                        print $thisclient "$cleanCandidateWord|genus|0|$currentString|$wordListMatches||\n";
                        next;
                    }
                    
                    $scoreG = isGenus($lcCandidateWord, $cleanCandidateWord);
                    $scoreF = isFamily($lcCandidateWord, $cleanCandidateWord);
                    if ($scoreG eq "G" || $scoreG eq "g" || ($scoreG eq "4" && $scoreF ne "F" && $scoreF ne "f"))
                    {
                        # Subgenus
                        if ($candidateWord =~ /^\(.*\)$/)
                        {
                            # Currently have potential abbreviated genus
                            if ($currentString =~ /^([A-Z-][a-z]?)$/)
                            {
                                # Found match for initial
                                if ($lastGenus[$clientNumber]{$1})
                                {
                                    #$currentString = "[".$lastGenus[$clientNumber]{$1}."]";
                                    $currentString = $1."[".substr($lastGenus[$clientNumber]{$1},length($1))."]";
                                    print $thisclient "$currentString ($cleanCandidateWord)|genus|$wordListMatches"."$scoreG||-1||\n";
                                }
                                
                                #No match for initial, return this genus string
                                else
                                {
                                    $currentString = "$1.";
                                    print $thisclient "$cleanCandidateWord|genus|$scoreG||-1||\n";
                                }
                            }else
                            {
                                print $thisclient "$currentString ($cleanCandidateWord)|genus|$wordListMatches"."$scoreG||-1||\n";
                            }
                            next;
                        }
                        
                        # Just another genus
                        else
                        {
                            if (length($currentString)<=2)
                            {
                                $currentString = "";
                            }
                            scoreReturnString();
                            print $thisclient "$cleanCandidateWord|genus|$scoreG|$currentString|$wordListMatches||\n";
                            next;
                        }
                    }
                    
                    if ($scoreF ne "-1")
                    {
                        if (length($currentString)<=2)
                        {
                            $currentString = "";
                        }
                        scoreReturnString();
                        $toReturn = "||0|$currentString|$wordListMatches";
                        
                        $currentString = ucfirst($lcCandidateWord);
                        $wordListMatches = $scoreF;
                        scoreReturnString();
                        print $thisclient $toReturn."|$currentString|$wordListMatches\n";
                        next;
                    }
                }
                
                # current name string is supra
                elsif ($currentStringState eq "species")
                {
                    # Species
                    $score = isSpecies($candidateWord, $lcCandidateWord, $cleanCandidateWord, $currentString);
                    if ($score ne '-1')
                    {
                        print $thisclient "$currentString $cleanCandidateWord|species|$wordListMatches"."$score||-1||\n";
                        next;
                    }
                    
                    # Infra-specific rank
                    $score = isRank($lcCandidateWord, $cleanCandidateWord);
                    if ($score ne '-1')
                    {
                        print $thisclient "$currentString $candidateWord|rank|$wordListMatches"."$score||-1||\n";
                        next;
                    }
                    
                    # possible abbreviated genus
                    if ($candidateWord =~ /^[A-Z][a-z]?\.$/)
                    {
                        scoreReturnString();
                        print $thisclient "$cleanCandidateWord|genus|0|$currentString|$wordListMatches||\n";
                        next;
                    }
                    
                    $scoreG = isGenus($lcCandidateWord, $cleanCandidateWord);
                    $scoreF = isFamily($lcCandidateWord, $cleanCandidateWord);
                    if ($scoreG eq "G" || $scoreG eq "g" || ($scoreG eq "4" && $scoreF ne "F" && $scoreF ne "f"))
                    {
                        scoreReturnString();
                        print $thisclient "$cleanCandidateWord|genus|$scoreG|$currentString|$wordListMatches||\n";
                        next;
                    }
                    
                    if ($scoreF ne "-1")
                    {
                        scoreReturnString();
                        $toReturn = "||0|$currentString|$wordListMatches";
                        
                        $currentString = ucfirst($lcCandidateWord);
                        $wordListMatches = $scoreF;
                        scoreReturnString();
                        print $thisclient $toReturn."|$currentString|$wordListMatches\n";
                        next;
                    }
                }
                
                # current name string is supra
                elsif ($currentStringState eq "rank")
                {
                    # Species
                    $score = isSpecies($candidateWord, $lcCandidateWord, $cleanCandidateWord, $currentString);
                    if ($score ne '-1')
                    {
                        print $thisclient "$currentString $cleanCandidateWord|species|$wordListMatches"."$score||-1||\n";
                        next;
                    }
                    
                    # possible abbreviated genus
                    if ($candidateWord =~ /^[A-Z][a-z]?\.$/)
                    {
                        scoreReturnString();
                        print $thisclient "$cleanCandidateWord|genus|0|$currentString|$wordListMatches||\n";
                        next;
                    }
                    
                    $scoreG = isGenus($lcCandidateWord, $cleanCandidateWord);
                    $scoreF = isFamily($lcCandidateWord, $cleanCandidateWord);
                    if ($scoreG eq "G" || $scoreG eq "g" || ($scoreG eq "4" && $scoreF ne "F" && $scoreF ne "f"))
                    {
                        scoreReturnString();
                        print $thisclient "$cleanCandidateWord|genus|$scoreG|$currentString|$wordListMatches||\n";
                        next;
                    }
                    
                    if ($scoreF ne "-1")
                    {
                        scoreReturnString();
                        $toReturn = "||0|$currentString|$wordListMatches";
                        
                        $currentString = ucfirst($lcCandidateWord);
                        $wordListMatches = $scoreF;
                        scoreReturnString();
                        print $thisclient $toReturn."|$currentString|$wordListMatches\n";
                        next;
                    }
                }
                
                # current name string is not specified (new)
                else
                {
                    # Abbreviation
                    if ($candidateWord =~ /^[A-Z][a-z]?\.$/)
                    {
                        print $thisclient "$cleanCandidateWord|genus|0||-1||\n";
                        next;
                    }
                    
                    $scoreG = isGenus($lcCandidateWord, $cleanCandidateWord);
                    $scoreF = isFamily($lcCandidateWord, $cleanCandidateWord);
                    if ($scoreG eq "G" || $scoreG eq "g" || ($scoreG eq "4" && $scoreF ne "F" && $scoreF ne "f"))
                    {
                        if($scoreG ne "4" && $cleanCandidateWord =~ /^([A-Z])([A-Z])/i)
                        {
                            $lastGenus[$clientNumber]{$1} = $cleanCandidateWord;
                            $lastGenus[$clientNumber]{$1.lc($2)} = $cleanCandidateWord;
                        }
                        if($candidateWord =~ /[,;\.\)\]][^A-Za-z]*$/i)
                        {
                            $currentString = ucfirst($lcCandidateWord);
                            $wordListMatches = $scoreG;
                            scoreReturnString();
                            print $thisclient "||0|$currentString|$wordListMatches||\n";
                        }else
                        {
                            print $thisclient "$cleanCandidateWord|genus|$scoreG||-1||\n";
                        }
                        next;
                    }
                    
                    if ($scoreF ne "-1")
                    {
                        $currentString = ucfirst($lcCandidateWord);
                        $wordListMatches = $scoreF;
                        scoreReturnString();
                        print $thisclient "||0|$currentString|$wordListMatches||\n";
                        next;
                    }
                }
            	#### END LOOKUP IN WORDLISTS
            	
            	
            	
                scoreReturnString();
            	print $thisclient "||0|$currentString|$wordListMatches||\n";
            }else
            {
                print "Closing connection $pid\n";
                $readable_handles->remove($thisclient);
                close($thisclient);
            }
        }
    }
    
    $numClients = $readable_handles->count();
    #print "Checking - $numClients\n";
    if($numClients==1)
    {
        print "Waiting for connection...\n";
        $new_sock = $main_socket->accept();
        print "Reloading wordlists...\n";
        reloadWordLists();
        print "Wordlists reloaded\n";
        print "Connecting\n";
        $readable_handles->add($new_sock);
        undef($lastGenus[1]);
    }
}
close($main_socket);




sub scoreReturnString
{
    if (length($currentString)<=2)
    {
        $currentString = "";
    }
    if(!$currentString)
    {
        $wordListMatches = '-1';
    }else
    {
        #$currentString =~ s/\[//g;
        #$currentString =~ s/\]//g;
        if($currentString =~ /^([A-Z])([A-Z\[\]-]*)( |$)(.*$)/)
        {
            $currentString = $1.lc($2).$3.$4;
        }
        if($currentString =~ /^(.+ \()([A-Z])([A-Z\[\]-]*)(\) |\)$)(.*$)/)
        {
            $currentString = $1.$2.lc($3).$4.$5;
        }
        while($currentString =~ /^(.+ )([A-Z\[\]-]*)( |$)(.*$)/)
        {
            $currentString = $1.lc($2).$3.$4;
        }
        if ($currentString =~ /^(.*) ([^ ]+)$/ && $rankHash{lc(clean($2))})
        {
            $currentString = $1;
            $wordListMatches = substr($wordListMatches,0,-1)
        }
        if($wordListMatches !~ /[FGS]/ || $badHash{lc(currentString)})
        {
            #print "********************************\n";
            $currentString = "";
            $wordListMatches = '-1';
        }
    }
}

sub isSpecies
{
    local($candidateWord, $lcCandidateWord, $cleanCandidateWord, $currentString) = ($_[0], $_[1], $_[2], $_[3]);
    if($candidateWord =~ /^[^A-Za-z]*[\(\.\[;,]/) { return '-1'; }
    if($badSpeciesHash{$lcCandidateWord}) { return '-1'; }
    if($cleanCandidateWord =~ /[0-9]/) { return '-1'; }
    if(length($currentString)>2 && $currentString =~ /^[A-Z-\(\)]+$/)
    {
         if($cleanCandidateWord =~ /[a-z]/) { return -1; }
    }else
    {
        if($cleanCandidateWord =~ /[A-Z]/) { return -1; }
    }
    
    if($speciesHash{$lcCandidateWord}) { return 'S'; }
    
    if($checkNameModels)
    {
        if(score_word_species($lcCandidateWord)) { return '3'; }
    }
    return '-1';
}

sub isGenus
{
    local($lcCandidateWord, $cleanCandidateWord) = ($_[0], $_[1]);
    if(length($cleanCandidateWord)<=2) { return '-1'; }
    if($cleanCandidateWord !~ /^[A-Z][a-z-]+$/ && $cleanCandidateWord !~ /^[A-Z-]+$/) { return '-1'; }
    if($cleanCandidateWord =~ /[0-9]/) { return '-1'; }
    if($overlapHash{$lcCandidateWord}) { return '-1'; }
    if($genusHash{$lcCandidateWord})
    {
        if($ambigHash{$lcCandidateWord}) { return 'g'; }
        else { return 'G'; }
    }
    if($checkNameModels)
    {
        if(score_word_genus($lcCandidateWord)) { return '4'; }
    }
    return '-1';
}

sub isFamily
{
    local($lcCandidateWord, $cleanCandidateWord) = ($_[0], $_[1]);
    if(length($cleanCandidateWord)<=2) { return '-1'; }
    if($cleanCandidateWord !~ /^[A-Z][a-z-]+$/ && $cleanCandidateWord !~ /^[A-Z-]+$/) { return '-1'; }
    if($cleanCandidateWord =~ /[0-9]/) { return '-1'; }
    if($overlapHash{$lcCandidateWord}) { return '-1'; }
    if($familyHash{$lcCandidateWord})
    {
        if($ambigHash{$lcCandidateWord}) { return 'f'; }
        else { return 'F'; }
    }
    if($checkNameModels)
    {
        if(score_word_family($lcCandidateWord)) { return '5'; }
    }
    return '-1';
}

sub isRank
{
    local($lcCandidateWord, $cleanCandidateWord) = ($_[0], $_[1]);
    if($candidateWord =~ /^[^A-Za-z]*[\(\.\[;,]/) { return '-1'; }
    if($cleanCandidateWord =~ /[A-Z]/) { return '-1'; }
    if($rankHash{$lcCandidateWord}) { return 'R'; }
    return '-1';
}







#s###
#s# loadWordLists
#s#   loads hashes from word lists
#s#
sub loadWordLists ()
{
	$wordListDir = "wordLists";
	
	$familyFileName  = "$wordListDir/family.txt";
	$familyNewFileName  = "$wordListDir/family_new.txt";
	$genusFileName   = "$wordListDir/genera.txt";
	$genusNewFileName   = "$wordListDir/genera_new.txt";
	$speciesFileName = "$wordListDir/species.txt";
	$speciesNewFileName = "$wordListDir/species_new.txt";
	$rankFileName    = "$wordListDir/ranks.txt";
	$overlapFileName = "$wordListDir/language_overlap.txt";
	$overlapNewFileName = "$wordListDir/overlap_new.txt";
	$overlapExceptionFileName = "$wordListDir/overlap_exception.txt";
	$badSpeciesFileName = "$wordListDir/species_bad.txt";
	$dictAmbig = "$wordListDir/dict_ambig.txt";
	$dictTooAmbig = "$wordListDir/dict_too_ambig.txt";
	$species3 = "$wordListDir/stats_species.txt";
	$species4 = "$wordListDir/stats_species_4.txt";
	$genera3 = "$wordListDir/stats_genera.txt";
	$genera4 = "$wordListDir/stats_genera_4.txt";
	$family3 = "$wordListDir/stats_family.txt";
	$family4 = "$wordListDir/stats_family_4.txt";
	$language3 = "$wordListDir/stats_language.txt";
	$language4 = "$wordListDir/stats_language_4.txt";


	#  1- family words
	open (FILE, $familyFileName);
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $familyHash{lc($_)}++ };
	}
	close (FILE);
	open (FILE, $familyNewFileName);
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $familyHash{lc($_)}++ };
	}
	close (FILE);
	open (FILE, "wordLists/genera_family.txt");
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $familyHash{lc($_)}++ };
	}
	close (FILE);

	#  2- genus words
	open (FILE, $genusFileName);
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $genusHash{lc($_)}++ };
	}
	close (FILE);
	open (FILE, $genusNewFileName);
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $genusHash{lc($_)}++ };
	}
	close (FILE);
	open (FILE, "wordLists/genera_family.txt");
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $genusHash{lc($_)}=0 };
	}
	close (FILE);
	
	#  3- species words
	open (FILE, $speciesFileName);
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $speciesHash{lc($_)}++ };
	}
	close (FILE);
	open (FILE, $speciesNewFileName);
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $speciesHash{lc($_)}++ };
	}
	close (FILE);
	
	#  4- rank words
	open (FILE, $rankFileName);
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $rankHash{lc($_)}++ };
	}
	close (FILE);
	
	open (FILE, $overlapNewFileName);
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $overlapHash{lc($_)}++ };
	}
	close (FILE);
	
    #  6- bad species words
    open (FILE, $badSpeciesFileName);
    while (<FILE>)
    {
    	$_ = trim($_);
    	if ($_) { $badSpeciesHash{lc($_)}++ };
    }
    close (FILE);
    
    open (FILE, $dictAmbig);
    while (<FILE>)
    {
    	$_ = trim($_);
    	if ($_) { $ambigHash{lc($_)}++ };
    }
    close (FILE);
    
    open (FILE, "wordLists/dict_bad.txt");
    while (<FILE>)
    {
    	$_ = trim($_);
    	if ($_) { $badHash{lc($_)}++ };
    }
    close (FILE);
    




#	#  1- family words
#	open (FILE, "wordLists/linkitFamily.txt");
#	while (<FILE>)
#	{
#		$_ = trim($_);
#		if ($_) { $familyHash{lc($_)}++ };
#	}
#	close (FILE);
#
#	#  2- genus words
#	open (FILE, "wordLists/linkitGenus.txt");
#	while (<FILE>)
#	{
#		$_ = trim($_);
#		if ($_) { $genusHash{lc($_)}++ };
#	}
#	close (FILE);
#	
#	#  3- species words
#	open (FILE, "wordLists/linkitSpecies.txt");
#	while (<FILE>)
#	{
#		$_ = trim($_);
#		if ($_) { $speciesHash{lc($_)}++ };
#	}
#	close (FILE);
#	
#	#  4- rank words
#	open (FILE, "wordLists/ranks.txt");
#	while (<FILE>)
#	{
#		$_ = trim($_);
#		if ($_) { $rankHash{lc($_)}++ };
#	}
#	close (FILE);
#	
#	open (FILE, "wordLists/linkitBad.txt");
#	while (<FILE>)
#	{
#		$_ = trim($_);
#		if ($_) { $overlapHash{lc($_)}++ };
#	}
#	close (FILE);
#	
#	open (FILE, "wordLists/linkitAmbigSpecies.txt");
#	while (<FILE>)
#	{
#		$_ = trim($_);
#		if ($_) { $badSpeciesHash{lc($_)}++ };
#	}
#	close (FILE);
#	
#	open (FILE, "wordLists/linkitAmbig.txt");
#	while (<FILE>)
#	{
#		$_ = trim($_);
#		if ($_) { $ambigHash{lc($_)}++ };
#	}
#	close (FILE);
#	
#	open (FILE, "wordLists/linkitTooAmbig.txt");
#	while (<FILE>)
#	{
#		$_ = trim($_);
#		if ($_) { $tooAmbigHash{lc($_)}++ };
#	}
#	close (FILE);
	
	
	
	
    open(FILE, "$wordListDir/stats_species.txt");
    while(<FILE>)
    {
        chop;
        if($_ && $_ !~ /^\t/)
        {
            @parts = split(/\t/,trim($_));
            $freq{'species3'}{$parts[0]} = $parts[1];
            $starts{'species3'}{$parts[0]} = $parts[2];
            $ends{'species3'}{$parts[0]} = $parts[3];
            $prefix = $parts[0];
        }else
        {
            @parts = split(/\t/,trim($_));
            $after{'species3'}{$prefix}{$parts[0]} = $parts[1];
        }
    }
    close(FILE);
    
    open(FILE, "$wordListDir/stats_species_4.txt");
    while(<FILE>)
    {
        chop;
        if($_ && $_ !~ /^\t/)
        {
            @parts = split(/\t/,trim($_));
            $freq{'species4'}{$parts[0]} = $parts[1];
            $starts{'species4'}{$parts[0]} = $parts[2];
            $ends{'species4'}{$parts[0]} = $parts[3];
            $prefix = $parts[0];
        }else
        {
            @parts = split(/\t/,trim($_));
            $after{'species4'}{$prefix}{$parts[0]} = $parts[1];
        }
    }
    close(FILE);
    
    open(FILE, "$wordListDir/stats_genera.txt");
    while(<FILE>)
    {
        chop;
        if($_ && $_ !~ /^\t/)
        {
            @parts = split(/\t/,trim($_));
            $freq{'genera3'}{$parts[0]} = $parts[1];
            $starts{'genera3'}{$parts[0]} = $parts[2];
            $ends{'genera3'}{$parts[0]} = $parts[3];
            $prefix = $parts[0];
        }else
        {
            @parts = split(/\t/,trim($_));
            $after{'genera3'}{$prefix}{$parts[0]} = $parts[1];
        }
    }
    close(FILE);
    
    open(FILE, "$wordListDir/stats_genera_4.txt");
    while(<FILE>)
    {
        chop;
        if($_ && $_ !~ /^\t/)
        {
            @parts = split(/\t/,trim($_));
            $freq{'genera4'}{$parts[0]} = $parts[1];
            $starts{'genera4'}{$parts[0]} = $parts[2];
            $ends{'genera4'}{$parts[0]} = $parts[3];
            $prefix = $parts[0];
        }else
        {
            @parts = split(/\t/,trim($_));
            $after{'genera4'}{$prefix}{$parts[0]} = $parts[1];
        }
    }
    close(FILE);
    
    open(FILE, "$wordListDir/stats_family.txt");
    while(<FILE>)
    {
        chop;
        if($_ && $_ !~ /^\t/)
        {
            @parts = split(/\t/,trim($_));
            $freq{'family3'}{$parts[0]} = $parts[1];
            $starts{'family3'}{$parts[0]} = $parts[2];
            $ends{'family3'}{$parts[0]} = $parts[3];
            $prefix = $parts[0];
        }else
        {
            @parts = split(/\t/,trim($_));
            $after{'family3'}{$prefix}{$parts[0]} = $parts[1];
        }
    }
    close(FILE);
    
    open(FILE, "$wordListDir/stats_family_4.txt");
    while(<FILE>)
    {
        chop;
        if($_ && $_ !~ /^\t/)
        {
            @parts = split(/\t/,trim($_));
            $freq{'family4'}{$parts[0]} = $parts[1];
            $starts{'family4'}{$parts[0]} = $parts[2];
            $ends{'family4'}{$parts[0]} = $parts[3];
            $prefix = $parts[0];
        }else
        {
            @parts = split(/\t/,trim($_));
            $after{'family4'}{$prefix}{$parts[0]} = $parts[1];
        }
    }
    close(FILE);
}





sub reloadWordLists
{
    $wordListDir = "wordLists";
    
	open (FILE, "$wordListDir/family_new.txt");
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $familyHash{lc($_)}++ };
	}
	close (FILE);
	
	open (FILE, "$wordListDir/genera_family.txt");
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $familyHash{lc($_)}++ };
	}
	close (FILE);
	
	open (FILE, "$wordListDir/genera_new.txt");
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $genusHash{lc($_)}++ };
	}
	close (FILE);
	
	open (FILE, "$wordListDir/genera_family.txt");
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $genusHash{lc($_)}=0 };
	}
	close (FILE);
	
	open (FILE, "$wordListDir/species_new.txt");
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $speciesHash{lc($_)}++ };
	}
	close (FILE);
	
	open (FILE, "$wordListDir/overlap_new.txt");
	while (<FILE>)
	{
		$_ = trim($_);
		if ($_) { $overlapHash{lc($_)}++ };
	}
	close (FILE);
	
    open (FILE, "$wordListDir/species_bad.txt");
    while (<FILE>)
    {
    	$_ = trim($_);
    	if ($_) { $badSpeciesHash{lc($_)}++ };
    }
    close (FILE);
    
    open (FILE, "$wordListDir/dict_ambig.txt");
    while (<FILE>)
    {
    	$_ = trim($_);
    	if ($_) { $ambigHash{lc($_)}++ };
    }
    close (FILE);
    
    open (FILE, "$wordListDir/dict_bad.txt");
    while (<FILE>)
    {
    	$_ = trim($_);
    	if ($_) { $badHash{lc($_)}++ };
    }
    close (FILE);
    
    open (FILE, "$wordListDir/ranks.txt");
    while (<FILE>)
    {
    	$_ = trim($_);
    	if ($_) { $rankHash{lc($_)}++ };
    }
    close (FILE);
}





sub trim 
{
    local $_ = shift;
    s/^[\s\n\r]*//;
    s/[\s\n\r]*$//;
    return $_;
}


sub clean 
{
    local $_ = shift;
    s/^[^0-9A-Za-z]+//;
    s/[^0-9A-Za-z]+$//;
    return $_;
}

sub utf8_to_ascii
{
    local $nameString = shift;
    $nameString =~ s/[ÀÂÅÃÄÁẤẠ]/A/gx;
    $nameString =~ s/[ÉÈÊË]/E/g;
    $nameString =~ s/[ÍÌÎÏ]/I/g;
    $nameString =~ s/[ÓÒÔØÕÖỚỔ]/O/g;
    $nameString =~ s/[ÚÙÛÜ]/U/g;
    $nameString =~ s/[Ý]/Y/g;
    $nameString =~ s/Æ/AE/g;
    $nameString =~ s/[CÇ]/C/g;
    $nameString =~ s/[ŠS]/S/g;
    $nameString =~ s/[Ð]/D/g;
    $nameString =~ s/Ž/Z/g;
    $nameString =~ s/Ñ/N/g;
    $nameString =~ s/Œ/OE/g;
    $nameString =~ s/ß/B/g;
    $nameString =~ s/Ķ/K/g;
    $nameString =~ s/[áàâåãäăãắảạậầằ]/a/g;
    $nameString =~ s/[éèêëĕěếệểễềẻ]/e/g;
    $nameString =~ s/[íìîïǐĭīĩỉï]/i/g;
    $nameString =~ s/[óòôøõöŏỏỗộơọỡốơồờớổ]/o/g;
    $nameString =~ s/[úùûüůưừựủứụ]/u/g;
    $nameString =~ s/[žź]/z/g;
    $nameString =~ s/[ýÿỹ]/y/g;
    $nameString =~ s/đ/d/g;
    $nameString =~ s/æ/ae/g;
    $nameString =~ s/[čćç]/c/g;
    $nameString =~ s/[ñńň]/n/g;
    $nameString =~ s/œ/oe/g;
    $nameString =~ s/[śšş]/s/g;
    $nameString =~ s/ř/r/g;
    $nameString =~ s/ğ/g/g;
    $nameString =~ s/Ř/R/g;
    return $nameString;
}







sub score_word_species
{
    local $name = shift;
    $name = lc(utf8_to_ascii(trim($name)));
    $name =~ s/[^A-Za-z0-9]//g;
    if($name =~ /[0-9]/)
    {
        return 0;
    }
    $score3 = score_word_species_3($name);
    $score4 = score_word_species_4($name);
    $score = (($score3+$score4)/2);
    if($score>=24) { return 1; }
    else { return 0; }
}

sub score_word_species_3
{
    local $name = shift;
    $name = lc($name);
    $len = length($name);
    $prev = "";
    $sumSci = 0;
    $numSeq = 1;
    while($name =~ /^(.)(.)(.)(.*)/)
    {
        $part = $1.$2.$3;
        
        if($prev)
        {
            $sumSci += $after{'species3'}{$prev}{$part};
            $numSeq++;
        }
        
        $name = $2.$3.$4;
        $prev = $part;
    }
    $sumSci += $ends{'species3'}{$prev};
    return (($sumSci*100)/$numSeq);
}

sub score_word_species_4
{
    local $name = shift;
    $name = lc($name);
    $len = length($name);
    $prev = "";
    $sumSci = 0;
    $numSeq = 1;
    while($name =~ /^(.)(.)(.)(.)(.*)/)
    {
        $part = $1.$2.$3.$4;
        
        if($prev)
        {
            $sumSci += $after{'species4'}{$prev}{$part};
            $numSeq++;
        }
        
        $name = $2.$3.$4.$5;
        $prev = $part;
    }
    $sumSci += $ends{'species4'}{$prev};
    return (($sumSci*100)/$numSeq);
}










sub score_word_genus
{
    local $name = shift;
    $name = lc(utf8_to_ascii(trim($name)));
    $name =~ s/[^A-Za-z0-9]//g;
    if($name =~ /[0-9]/)
    {
        return 0;
    }
    $score3 = score_word_genera_3($name);
    $score4 = score_word_genera_4($name);
    $score = (($score3+$score4)/2);
    if($score>=24) { return 1; }
    else { return 0; }
}

sub score_word_genera_3
{
    local $name = shift;
    $name = lc($name);
    $len = length($name);
    $prev = "";
    $sumSci = 0;
    $numSeq = 1;
    while($name =~ /^(.)(.)(.)(.*)/)
    {
        $part = $1.$2.$3;
        
        if($prev)
        {
            $sumSci += $after{'genera3'}{$prev}{$part};
            $numSeq++;
        }
        
        $name = $2.$3.$4;
        $prev = $part;
    }
    $sumSci += $ends{'genera3'}{$prev};
    return (($sumSci*100)/$numSeq);
}

sub score_word_genera_4
{
    local $name = shift;
    $name = lc($name);
    $len = length($name);
    $prev = "";
    $sumSci = 0;
    $numSeq = 1;
    while($name =~ /^(.)(.)(.)(.)(.*)/)
    {
        $part = $1.$2.$3.$4;
        
        if($prev)
        {
            $sumSci += $after{'genera4'}{$prev}{$part};
            $numSeq++;
        }
        
        $name = $2.$3.$4.$5;
        $prev = $part;
    }
    $sumSci += $ends{'genera4'}{$prev};
    return (($sumSci*100)/$numSeq);
}









sub score_word_family
{
    local $name = shift;
    $name = lc(utf8_to_ascii(trim($name)));
    $name =~ s/[^A-Za-z0-9]//g;
    if($name =~ /[0-9]/)
    {
        return 0;
    }
    $score3 = score_word_family_3($name);
    $score4 = score_word_family_4($name);
    $score = (($score3+$score4)/2);
    if($score>=38) { return 1; }
    else { return 0; }
}

sub score_word_family_3
{
    local $name = shift;
    $name = lc($name);
    $len = length($name);
    $prev = "";
    $sumSci = 0;
    $numSeq = 2;
    while($name =~ /^(.)(.)(.)(.*)/)
    {
        $part = $1.$2.$3;
        
        if($prev)
        {
            $sumSci += $after{'family3'}{$prev}{$part};
            $numSeq++;
        }
        
        $name = $2.$3.$4;
        $prev = $part;
    }
    $sumSci += 2*$ends{'family3'}{$prev};
    return (($sumSci*100)/$numSeq);
}

sub score_word_family_4
{
    local $name = shift;
    $name = lc($name);
    $len = length($name);
    $prev = "";
    $sumSci = 0;
    $numSeq = 2;
    while($name =~ /^(.)(.)(.)(.)(.*)/)
    {
        $part = $1.$2.$3.$4;
        
        if($prev)
        {
            $sumSci += $after{'family4'}{$prev}{$part};
            $numSeq++;
        }
        
        $name = $2.$3.$4.$5;
        $prev = $part;
    }
    $sumSci += 2*$ends{'family4'}{$prev};
    return (($sumSci*100)/$numSeq);
}



exit;
