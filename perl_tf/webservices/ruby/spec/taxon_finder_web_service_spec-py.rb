require File.dirname(__FILE__) + '/spec_helper'
require 'uri'
require 'base64'
require 'ruby-debug'

TEST_TEXT = 'first we find Mus musculus and then we find Volutharpa ampullacea again and M. musculus also'
# TEST_URL  = 'http://www.bacterio.cict.fr/d/desulfotomaculum.html'
# TEST_URL  = 'http://www.gutenberg.org/files/2600/2600.txt' #Invalid argument

TEST_URL  = '/Users/anna/work/texts/aseashell/americanseashell.txt'
# LOCAL_URL = 'http://localhost/text_good1.txt'
# LOCAL_URL = 'http://localhost/xaa' #rm x?? && split -b 797k pictorialgeo.txt
# rm x?? && split -b 900k pictorialgeo.txt - 512
# LOCAL_URL = 'http://localhost/pictorialgeo.txt' #403 Forbidden
LOCAL_URL = 'http://localhost/Ifamericanseashell.txt'
# sometimes "Connection reset by peer"

describe "Taxon Finder Web Service" do
  include Rack::Test::Methods

  def app
    @app ||= Sinatra::Application
  end
  
  # it "should run several clients simultaneously" do
  # end

  # it "should return a name from text" do
  #   text = URI.escape TEST_TEXT
  #   get "/find?text=#{text}"
  #   assert last_response.body.include?("Mus musculus")
  #     # last_response.body.should == ""
  # end
  # 
  # it "should return all names from text" do
  #   text = URI.escape TEST_TEXT
  #   get "/find?text=#{text}"
  #   assert last_response.body.include?("Mus musculus")
  #   assert last_response.body.include?("Volutharpa ampullacea")
  # end
  
  it "should return all names from local URL" do
    get "/find?url=#{LOCAL_URL}"
    # last_response.body.should == ""
    assert last_response.body.include?('<verbatim>Mus musculus</verbatim>')
    assert last_response.body.include?('<verbatim>Volutharpa ampullacea</verbatim>')
  end  
#   
#   # it "should return all names from URL" do
#   #   get "/find?url=#{TEST_URL}"
#   #   # last_response.body.should == ""
#   #   assert last_response.body.include?('<verbatim>Vas ili</verbatim>')
#   #   assert last_response.body.include?('<dwc:scientificName>Vasilisa</dwc:scientificName>')
#   # end  
#   
#   
#   # it "should return a uppercase word for local URL" do
#   #   get "/find?url=#{LOCAL_URL}"
#   #   # last_response.body.should == ""
#   #   # pictorialgeo.txt
#   #   assert last_response.body.include?("TO CONTRADICT THE BLACK WARNING IN HIS EYES.")
#   #   # include("<name>ABRA ABRA\n</name>")
#   #   # assert last_response.body.include?('COUNTRIES')
#   #   # assert last_response.body.include?("<name>ABRA ABRA\n</name>")
#   # end
#   
#   # it "should return a uppercase word for URL" do
#   #   get "/find?url=#{TEST_URL}"
#   #   assert last_response.body.include?("Volutharpa ampullacea")
#   #   # assert last_response.body.include?("TO HEAR ABOUT NEW EBOOKS.") # for 2600
#   #   # last_response.body.should == ""
#   #   # last_response.body.should == "LIST OF PROKARYOTIC NAMES WITH STANDING IN NOMENCLATURE - GENUS DESULFOTOMACULUM" #include("<verbatim>Desulfosporosinus orientis</verbatim>")
#   # end
#   
#   it "should respond to /" do
#     get '/'
#     last_response.should be_ok
#   end
# 
#   it "should return 404 when page cannot be found" do
#     get '/404'
#     last_response.status.should == 404
#   end
#   
#   it "should return xml if the format isn't provided" do
#     get "/find?text=a"
#     last_response.body.should include("<?xml")
#   end
#   
#   it "should return xml if the format is unknown" do
#     get "/find?text=&format=nothing"
#     last_response.body.should include("<?xml")
#   end
#   
#   it "should return xml if xml is requested" do
#     get "/find?text=&format=xml"
#     last_response.body.should include("<?xml")
#   end
#   
#   it "should properly set the content headers for xml" do
#     get "/find?text=&format=xml"
#     last_response.headers['Content-Type'].should include("text/xml;charset=utf-8")
#   end  
#   
#   it "should return json if json is requested" do
#     get "/find?text=&format=json"
#     last_response.body.should include('{"response":{')
#   end
#   
#   it "should properly set the content headers for json" do
#     get "/find?text=&format=json"
#     last_response.headers['Content-Type'].should include("application/json;charset=utf-8")
#   end
#   
#   it "should return a verbatim name when a valid species name is identified in text" do
#     text = URI.escape TEST_TEXT
#     # debugger
#     get "/find?text=#{text}"
#     last_response.body.should include("<verbatim>Mus musculus</verbatim>")
#   end
#   # 
#   it "should return a verbatim name when a valid species name is identified in the supplied url" do
#     get "/find?url=#{TEST_URL}"
#     last_response.body.should include("<verbatim>Desulfosporosinus orientis</verbatim>")
#     last_response.body.should include("<verbatim>Desulfotomaculum alkaliphilum</verbatim>")
#   end 
#   
#   it "should display both sci. name and verbatim when an abbreviated species name is supplied" do 
#     text = URI.escape(TEST_TEXT)
#     get "/find?text=#{text}"
#     last_response.body.should include("<verbatim>Volutharpa ampullacea</verbatim>")
#     last_response.body.should include("<verbatim>M. musculus</verbatim>")
#     last_response.body.should include("<dwc:scientificName>Mus musculus</dwc:scientificName>")
#   end
#   
#   it "should accept encoded text" do
#     text = URI.escape(Base64::encode64(TEST_TEXT))
#     get "/find?encodedtext=#{text}"
#     last_response.should be_ok
#   end
#   
#   it "should accept an encoded URL" do
#     url = URI.escape(Base64::encode64(TEST_URL))
#     get "/find?encodedurl=#{url}"
#     last_response.should be_ok
#   end
#   
#   #this was removed from the contract.
#   # it "should return details if a species is found" do
#   #   text = URI.escape(TEST_TEXT)
#   #   get "/find?details=ubio&text=#{text}"
#   #   last_response.body.should include("<name>")
#   #   last_response.body.should include("<details>")
#   #   last_response.should be_ok
#   # end
#   
#   # TEST_TEXT = 'first we find 
#   # Mus musculus and then we find Volutharpa ampullacea again and M. musculus also'
#   # 
#   # 'Taxon Finder Web Service should return the proper offset' FAILED
#   # expected "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><names xmlns:dwc=\"http://rs.tdwg.org/dwc/terms/\"><name><verbatim>M. musculus</verbatim><dwc:scientificName>Mus musculus</dwc:scientificName><offsets><offset start=\"3\" end=\"13\"/></offsets></name><name><verbatim>Mus musculus</verbatim><dwc:scientificName>Mus musculus</dwc:scientificName><offsets><offset start=\"14\" end=\"25\"/></offsets></name><name><verbatim>Volutharpa ampullacea</verbatim><dwc:scientificName>Volutharpa ampullacea</dwc:scientificName><offsets><offset start=\"26\" end=\"46\"/></offsets></name></names></response>" to include "<offset start=\"44\" end=\"54\"/>"
#   
#   
#   it "should return the proper offset" do
#     text = URI.escape(TEST_TEXT)
#     get "/find?text=#{text}"
#     last_response.body.should include("<offset start=\"14\" end=\"25\"/>")
#     last_response.body.should include("<offset start=\"44\" end=\"54\"/>")
#   end
#   
# #   expected "
# # <?xml version=\"1.0\" encoding=\"UTF-8\"?>
# # <response>
# # <names xmlns:dwc=\"http://rs.tdwg.org/dwc/terms/\">
# # <name>
# # <verbatim>Canis
# # </verbatim>
# # <dwc:scientificName>Canis
# # </dwc:scientificName>
# # <offsets>
# # <offset start=\"-1\" end=\"3\"/>
# # </offsets>
# # </name>
# # </names>
# # </response>" to include "
# # <offset start=\"71\" end=\"92\"/>"
#   
#   it "should return another proper offset with weird whitespace" do
#     text = "dksjlf sldkjfl sdkljf slkdjf lksdj flksjd flksjdf          lskdjflksdj Canis lupus familiaris buhh"
#     text = URI.escape(text)
#     get "/find?text=#{text}"
#     last_response.body.should include("<offset start=\"71\" end=\"92\"/>")
#   end
#   
#   it "should return a proper offset even if the string begins with spaces" do
#     text = "       dksjlf sldkjfl sdkljf slkdjf lksdj flksjd flksjdf          lskdjflksdj Canis lupus familiaris buhh"
#     text = URI.escape text
#     get "/find?text=#{text}"
#     last_response.body.should include("<offset start=\"78\" end=\"99\"/>")
#   end
  
end
