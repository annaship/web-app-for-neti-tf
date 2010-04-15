require 'ostruct'
require 'socket'
require File.dirname(__FILE__) + '/name'

class Object
   def blank?
     respond_to?(:empty?) ? empty? : !self
   end
end

class TaxonFinderClient
  def initialize(host = 'localhost', port = 1234)
    @host = host
    @port = port
    socket
  end
  
  def add_name(name)
    @names << name
  end
  
  def get(str)
    @names = []
    @current_string = ''
    @current_string_state = ''
    @word_list_matches = 0
    words = str.split(/\s/)
  
    current_position = 0
    words.each do |word|
      unless word.empty?
        taxon_find(word, current_position)
        current_position += word.length
      end
      current_position += 1
    end
    @socket.close
    @names
  end
  
  # def get(str)
  #   @names = []
  #   # @current_string = ''
  #   # @current_string_state = ''
  #   # @word_list_matches = 0
  #   # words = str.split(/\s/)
  #   # current_position = 0
  #   # words.each do |word|
  #   #   unless word.empty?
  #   #     taxon_find(word, current_position)
  #   #     current_position += word.length
  #   #   end
  #   #   current_position += 1
  #   # end
  #   @names
  # end
  #   
  alias_method :find, :get
  
  def taxon_find(word, current_position)
    input = "#{word}|#{@current_string}|#{@current_string_state}|#{@word_list_matches}|0"
    @socket.puts input
    if output = @socket.gets
      response = parse_socket_response(output)
      return if not response
      
      add_name Name.new(response.return_string, response.return_score, current_position) unless response.return_string.blank?
      add_name Name.new(response.return_string_2, response.return_score_2, current_position) unless response.return_string_2.blank?
    end
  end
  
  def parse_socket_response(response)
    current_string, current_string_state, word_list_matches, return_string, return_score, return_string_2, return_score_2 = response.strip.split '|'
    @current_string = current_string
    @current_string_state = current_string_state
    @word_list_matches = word_list_matches
    if not return_string.blank? or not return_string_2.blank?
      OpenStruct.new( { :current_string       => current_string,
                      :current_string_state => current_string_state,
                      :word_list_matches    => word_list_matches,
                      :return_string        => return_string,
                      :return_score         => return_score,
                      :return_string_2      => return_string_2,
                      :return_score_2       => return_score_2 })
    else
      false
    end
  end
  
  def socket
    @socket ||= TCPSocket.open @host, @port
  end
  
end