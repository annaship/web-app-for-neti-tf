#!/usr/bin/ruby

require 'rubygems'                                                                                                             
# require 'spec'
require 'Nokogiri'
require 'ruby-debug'
require File.join(File.dirname(__FILE__), '../lib', 'neti_taxon_finder_client.rb')

if ARGV.size < 1
  puts "Provide file name please"
else

  # file_name = "text_good1.txt"
  file_name = $*[0]
  client = NetiTaxonFinderClient.new
  response = open(file_name)
  puts "=" * 80

  puts "file_name = "+file_name
  # debugger
  content1 = Nokogiri::HTML(response).content
  resp = client.find(content1)
  
  # out_file = open("/Users/anna/work/test_neti_app/res/out_file_"+file_name, "w")
  # out_file.print resp.inspect.to_s
  puts resp.inspect.to_s

  # .include?("Astraea americana")


# file_name = $*[1]
#   
# if ARGV.size < 2
#   puts "Provide two files to compare, bad and good ones, please"
# else
#   first_name = ARGV[0]
#   get_name_list(first_name).take_all_names
end

