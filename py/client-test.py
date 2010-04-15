import socket
import sys

HOST, PORT = '127.0.0.1', 1234
#L 128.128.171.137
# uploaded file
data = open('/Users/anna/work/ruby/text_good.txt').read()
# " ".join(sys.argv[1:])

# Create a socket (SOCK_STREAM means a TCP socket)
sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

# Connect to server and send data
sock.connect((HOST, PORT))
sock.send(data + "\n")

# Receive data from the server and shut down
received = sock.recv(1024)
sock.close()

print "Sent:     %s" % data
print "Received: %s" % received
