#!/usr/bin/python

fn = open('all_names.txt', 'r')
out = open('all_names2.txt', 'w')
names = []
for line in fn:
  (offset,iname,name)=line.split("|")
  names.append((name,iname))
print "%i names listed in all_names.txt" % len(names)


# find correct offsets of names in order of all_names.txt
fd = open('document.txt', 'r')
text = fd.read()
start=0
for name,iname in names:
  name=name.strip()
  offset = text.find(name, start)
  if offset>=0:
    print "Name found: %s at offset %i" % (name,offset)
    start=offset+1
    out.write("%i|%s|%s\n" % (offset,iname,name) )
  else:
    print "cant find name: "+name
    
print "done. no more names"
out.close()
  