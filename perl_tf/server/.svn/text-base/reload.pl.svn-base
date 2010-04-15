#!/usr/bin/perl
$|++;

use IO::Socket;
my $sock = new IO::Socket::INET (
        PeerAddr => 'animalia.mbl.edu',
        PeerPort => '1234',
        Proto => 'tcp',
    );
die "Could not create socket: $!\n" unless $sock;



print $sock "ykF7RYlQFHKxetrZCUgVB0WtgzIU - reload dictionaries\n";
close($sock);
exit;
