import socket
import sys

HOST, PORT = "localhost", 1234
#data = open("/Users/Manohar/InformationExtraction/worldgeo.txt").read()
data = open(sys.argv[1]).read()

# Create a socket (SOCK_STREAM means a TCP socket)
sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

# Connect to server and send data
sock.connect((HOST, PORT))
sock.sendall(data)

received = sock.recv(1024)
#print len(received)
# Receive data from the server and shut down
while(len(received)%1024 ==0):
    print len(received)
    received = received+sock.recv(1024)
#received = received+sock.recv(1024)
sock.close()

#print "Sent:     %s" % data
print "Received: %s" % received
