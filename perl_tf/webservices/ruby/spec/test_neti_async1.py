#! /opt/local/bin/python

import os
import sys
import subprocess

files_dir = "/Users/anna/work/test_neti_app/18/"
# files = os.listdir("/Library/Webserver/Documents/")
files = os.listdir(files_dir)
# taxon_finder_client_spec2.rb

for f in files:
    #st = "python "+"Nclient.py "+"18/"+f+" >"+"results--"+f
    # print f
    f_name = files_dir+f
    # print f_name
    #p = subprocess.Popen("ruby "+"Nclient.py "+"18/"+f+" >"+"results--"+f,shell=True)
    # p = subprocess.Popen("spec "+"/Users/anna/work/web_app/perl_tf/webservices/ruby/spec/taxon_finder_web_service_spec-py1.rb "+f, shell=True)
    # p = subprocess.Popen("spec "+"/Users/anna/work/web_app/perl_tf/webservices/ruby/spec/taxon_finder_web_service_spec-py1.rb "+f, shell=True)
    p = subprocess.Popen("ruby "+"/Users/anna/work/web_app/perl_tf/webservices/ruby/spec/call_client.rb "+f_name, shell=True)
    # p = subprocess.Popen("spec "+files_dir+f, shell=True)

		# process = subprocess.Popen(['ls',], stdout=subprocess.PIPE)
		# print process.communicate()[0]

