require 'rubygems'                                                                                                             
# require 'spec'
require 'Nokogiri'
require 'ruby-debug'
require 'bj'
require File.join(File.dirname(__FILE__), '../lib', 'neti_taxon_finder_client.rb')


describe "TaxonFinder client" do
  arr = []
  # file_name = $*[1]
  # puts "file_name = "+file_name.to_s
  # before :each do
    # @client = TaxonFinderClient.new
    # arr << NetiTaxonFinderClient.new
    # @client1 = NetiTaxonFinderClient.new
    # @client2 = NetiTaxonFinderClient.new
    # @client3 = NetiTaxonFinderClient.new
  # end
  # for i <= 4
  #   @client.find(c)
  it "should run several clients simultaneously" do
    basedir = '/Users/anna/work/test_neti_app/18/'
    Dir.chdir(basedir)
    files = Dir.glob("*.txt")
    
    # arr = ['/Library/Webserver/Documents/reconciled.txt', '/Library/Webserver/Documents/pictorialgeo.txt']
    files.each do |file_name|
      client = NetiTaxonFinderClient.new
      response = open(file_name)
      puts "=" * 40
      puts "file_name = "+file_name
      # debugger
      # response = open(content)
      content1 = Nokogiri::HTML(response).content
      client.find(content1).include?("Astraea americana")
      # client.find(content1).should == "Astraea americana"
      # assert last_response.body.include?("Volutharpa ampullacea")
    end
  end
end

