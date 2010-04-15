#!/usr/bin/env perl
$|++;

use IO::Socket;
my $sock = new IO::Socket::INET (
        PeerAddr => 'names.mbl.edu',
        PeerPort => '1234',
        Proto => 'tcp',
    );
die "Could not create socket: $!\n" unless $sock;


#print $sock "Hello there!\n";
#sleep(1);
#print $sock "Hello there!\n";
#close($sock);




# Send messages
$def_msg="Enter message to send to server : ";
print "\n",$def_msg;
while($msg=<STDIN>)
{
    chomp $msg;
    if($msg ne '')
    {
        print "\nSending message '".$msg."'.\n";
        print $sock "$msg\n";
        
        print "\nAwaiting response  ";
        if(defined ($line = <$sock>))
        {
            print $line;
        }
        
        print ".....<done>\n\n";
        print $def_msg;
        print $sock '';
    }else
    {
        print $sock '';
        close($sock);
        exit;
    }
}



#print $sock "Hello World!\n";
#
#while($line = <$sock>)
#{
#    print $line;
#}
#
#close($sock);


exit;
