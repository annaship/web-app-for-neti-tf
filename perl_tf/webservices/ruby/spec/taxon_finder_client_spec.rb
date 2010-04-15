require 'rubygems'                                                                                                             
# require 'spec'
require 'Nokogiri'
require 'ruby-debug'
require File.join(File.dirname(__FILE__), '../lib', 'neti_taxon_finder_client.rb')


describe "TaxonFinder client" do
  arr = []
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
    # /Users/anna/work/archive/web-app/test/18/     
    arr = ['/Library/Webserver/Documents/reconciled.txt', '/Library/Webserver/Documents/pictorialgeo.txt']
    arr.each do |file_name|
      client = NetiTaxonFinderClient.new
      response = open(file_name)
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

# get '/find' do
#   # @@client = TaxonFinderClient.new 'localhost' NetiTaxonFinderClient
#   @@client = NetiTaxonFinderClient.new 'localhost' 
#   format = @@valid_formats.include?(params[:format]) ? params[:format] : "xml"
#   begin
#     content = params[:text] || params[:url] || params[:encodedtext] || params[:encodedurl]
#   rescue
#     status 400
#   end
#   content = URI.unescape content
#   # decode if it's encoded
#   content = Base64::decode64 content if params[:encodedtext] || params[:encodedurl]
#   # scrape if it's a url
#   if params[:encodedurl] || params[:url]
#     begin
#       response = open(content)
#     rescue
#       status 400
#     end
#     content = Nokogiri::HTML(response).content
#   end
#   names = @@client.find(content)