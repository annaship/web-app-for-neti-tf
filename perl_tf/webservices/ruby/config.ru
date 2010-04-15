require 'rubygems'
require 'sinatra'

Sinatra::Default.set(:run => false, :enf => :production)

require 'lib/taxon_finder_client.rb'
require 'taxon_finder_web_service.rb'
run Sinatra::Application
