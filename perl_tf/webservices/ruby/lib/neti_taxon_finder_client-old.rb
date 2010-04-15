require 'ostruct'
require 'socket'
require File.dirname(__FILE__) + '/name'


class Object
   def blank?
     respond_to?(:empty?) ? empty? : !self
   end
end

class NetiTaxonFinderClient
  
  def initialize(host = 'localhost', port = 1234)
    @host = host
    @port = port
  end
  
  def add_name(name)
    @names << name
  end

  def get(data)

    socket ||= TCPSocket.new @host, @port

    socket.puts data
    output = ""
    while !socket.eof? do
      output = output + socket.read(1024)
    end
    puts "output[0..100] = "+output[0..100].inspect.to_s
    # @names = ["This", "is", "a", "name"]
    socket.close 
    
    # @names = "Nothing!"
    @names = output.gsub("\t","\n") #if output
    
  end  
  
  alias_method :find, :get
  
  def taxon_find(word, current_position)
    input = "#{word}|#{@current_string}|#{@current_string_state}|#{@word_list_matches}|0"
    socket.puts input
    if output = socket.gets
      response = parse_socket_response(output)
      @current_string = response.current_string
      @current_string_state = response.current_string_state
      @word_list_matches = response.word_list_matches
      add_name Name.new(response.return_string, response.return_score, current_position) unless response.return_string.blank?
      add_name Name.new(response.return_string_2, response.return_score_2, current_position) unless response.return_string_2.blank?
    end
  end
  
  def parse_socket_response(response)
    current_string, current_string_state, word_list_matches, return_string, return_score, return_string_2, return_score_2 = response.strip.split '|'
    OpenStruct.new( { :current_string       => current_string,
                      :current_string_state => current_string_state,
                      :word_list_matches    => word_list_matches,
                      :return_string        => return_string,
                      :return_score         => return_score,
                      :return_string_2      => return_string_2,
                      :return_score_2       => return_score_2 })
  end
  
  def socket
    @socket ||= TCPSocket.open @host, @port
  end
end