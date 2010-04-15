<?

class taxonFinder
{
    private $capital;
    private $lower;
    private $all_names;
    private $socket;
    private $socket_ip;
    private $socket_port;
    private $currentString;
    private $currentStringState;
    private $wordListMatches;
    
    function __construct()
    {
        $this->capital = "A-ZÀÂÅÃÄÁÆCÇÉÈÊËÍÌÎÏÑÓÒÔØÕÖÚÙÛÜßKRŠSŽŒ";
        $this->lower = "a-zááàâåãäaæccçéèêëeíìîïiiñnóòôøõöoúùûüusšsrgžzýÿœ";
        $this->all_names = array();
        $this->socket_ip = "names.mbl.edu";
        $this->socket_port = "1234";
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->socket, $this->socket_ip, $this->socket_port);
    }    
    
    function get_names_from_url($url)
    {
        if(preg_match("/[\.|_]pdf\s*$/",$url))
    	{
	    	$OUT = fopen("/data/www/tools/tmp/tmp.pdf", "w+");
	    	fwrite($OUT,file_get_contents($url));
	    	fclose($OUT);
	    	$text = shell_exec("/data/www/tools/pdftotext -layout -htmlmeta -enc UTF-8 -q /data/www/tools/tmp/tmp.pdf - ");
	    	$type="pdf";
	    	$OUT = fopen("/data/www/tools/tmp/pdf.txt", "w+");
	    	fwrite($OUT,$text);
	    	fclose($OUT);
    	}else $text = file_get_contents($url);
        $this->get_names($text,"text");
        return $this->all_names;
    }
    
    function get_names($text,$type)
    {
        $this->all_names = array();
        
        $text = html_decode($text);		
        $text = html_entity_decode($text);		
        $text = html_entity_decode($text);		
		$text = preg_replace("/<\/?p ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?pb ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?div ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?head ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?table ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?tr ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?td ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?th ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?dl ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?dt ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?dd ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?li ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?em ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?area ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?a ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?br ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?body ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?b ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?img ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?i ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?strong ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?script ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?meta ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?hr ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?span ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?font ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?cell ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?row ?[^>]*>/i"," , ",$text);
		$text = preg_replace("/<\/?emph ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?em ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?big ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?taxonName ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?ref ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?ttl ?[^>]*>/i"," ",$text);
		$text = preg_replace("/<\/?hi ?[^>]*>/i"," ",$text);
		//$text = preg_replace("/<\/?[[:alpha:] ]*>/"," ",$text);
		$text = preg_replace("/<\/?[A-Za-z]*>/"," ",$text);
		$text = preg_replace("/&[A-Za-z0-9]{0,6};/"," ",$text);
		$text = preg_replace("/-\n/i","",$text);
		$text = preg_replace("/\n/i"," ",$text);
		$text = preg_replace("/-\r/i","",$text);
		$text = preg_replace("/\r/i"," ",$text);
		$text = preg_replace("/\.,?([^ \)])/",". \\1",$text);
		$text = preg_replace("/\. -/",". , -",$text);
		$text = preg_replace("/—/"," ",$text);
		$text = preg_replace("/[<>]/"," ",$text);
		$text = preg_replace("/=/"," ",$text);
		$text = str_replace("("," (",$text);
        $text = str_replace(")",") ",$text);
        $text = preg_replace("/\t/i"," , ",$text);
        $text = preg_replace("/&nbsp;/i"," ",$text);
		
		$text = str_replace(","," . ",$text);
		while(preg_match("/  /",$text)) $text = str_replace("  "," ",$text);
        $words_temp = explode(" ",$text);
        
        
        $currentString = "";
        $currentStringState = "";
        $wordListMatches = 0;
        while(list($key,$val)=each($words_temp))
        {
            $in = trim($val)."|$currentString|$currentStringState|$wordListMatches\n";
            //echo "$in<br>";
            
            socket_write($this->socket, $in, strlen($in));
            if($out = socket_read($this->socket, 2048))
            {
                list($currentString, $currentStringState, $wordListMatches, $returnString, $returnScore, $returnString2, $returnScore2) = explode("|",trim($out));
                //echo "&nbsp;&nbsp;&nbsp;$out<br>";
                //echo "&nbsp;&nbsp;&nbsp;$currentString,$currentStringState,$wordListMatches,$returnString,$returnScore,$returnString2,$returnScore2<br>";
                if($returnString) $this->all_names[$returnString]=$returnScore;
                if($returnString2) $this->all_names[$returnString2]=$returnScore2;
                
                //echo "IN: $in<br>OUT: $out<br>";
            }
        }
        
        return $this->all_names;
    }
}













class linkit
{
    private $capital;
    private $lower;
    private $mysqli;
    private $mysqli_uio;
    private $all_names;
    private $map;
    private $nologo;
    private $redirect;
    private $nb;
    private $link_type;
    private $collectionsID;
    private $classify;
    private $ip;
    private $synonyms;
    private $anchor_index;
    private $recognize;
    private $possible_names;
    private $save;
    private $saveFile;
    private $classIDs;
    private $synIDs;
    private $pages;
    private $abreviations;
    private $resolve;
    private $num_menus;
    private $socket;
    private $socket_ip;
    private $socket_port;    
    private $currentString;
    private $currentStringState;
    private $wordListMatches;
    private $returnString;
    private $returnScore;
    private $returnString2;
    private $returnScore2;
    
    function __construct($mapp,$logo,$rdirect,$nb_flag,$l_type,$class,$syn,$save,$resol)
    {
        $this->capital = "A-ZÀÂÅÃÄÁÆCÇÉÈÊËÍÌÎÏÑÓÒÔØÕÖÚÙÛÜßKRŠSŽŒ";
        $this->lower = "a-zááàâåãäaæccçéèêëeíìîïiiñnóòôøõöoúùûüusšsrgžzýÿœ";
        $this->socket_ip = "names.mbl.edu";
        $this->socket_port = "1234";
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->socket, $this->socket_ip, $this->socket_port);
        
        $this->ip = $_SERVER["REMOTE_ADDR"];
        $this->mysqli = new mysqli("128.128.250.148", "nbsec5", "uheqk73", "namebank");
        $this->mysqli_rss = new mysqli("128.128.250.148", "nbsec5", "uheqk73", "rss");
        $this->mysqli_uio = new mysqli("128.128.250.148", "nbsec5", "uheqk73", "tns_v2");
        $this->map=$mapp;
        $this->nologo=$logo;
        $this->redirect=$rdirect;
        $this->nb=$nb_flag;
        $this->link_type=$l_type;
        $this->classify=$class;
        $this->synonyms=$syn;
        $this->save=$save;
        $this->resolve=$resol;
        //$this->recognize = new recognize();
        $this->abreviations[1] = "itis";
        $this->abreviations[2] = "mam";
        $this->abreviations[3] = "algae";
        $this->abreviations[4] = "ildis";
        $this->abreviations[5] = "itisC";
        $this->abreviations[6] = "mb";
        $this->abreviations[7] = "ceph";
        $this->abreviations[8] = "ceph";
        $this->abreviations[9] = "fish";
        $this->abreviations[10] = "if";
        $this->abreviations[11] = "if";
        $this->abreviations[12] = "sp";
        $this->abreviations[13] = "sp";
        $this->abreviations[14] = "nz";
        $this->abreviations[15] = "ncbi";
        $this->abreviations[16] = "tree";
        $this->abreviations[17] = "mobot";
        $this->abreviations[18] = "citesPS";
        $this->abreviations[19] = "citesPG";
        $this->abreviations[20] = "citesAS";
        $this->abreviations[21] = "citesAG";
        $this->abreviations[24] = "micro";
        $this->abreviations[26] = "custar";
        $this->abreviations[27] = "grin";
        $this->abreviations[28] = "tol";
        $this->abreviations[29] = "pubmed";
        $this->abreviations[32] = "iucn";
        $this->abreviations[36] = "erms";
        $this->abreviations[37] = "morph";
        $this->abreviations[93] = "trop";
        $this->abreviations[108] = "fauna";
        $this->abreviations[110] = "lr";
        $this->num_menus=1;
        $this->anchor_index=0;
        $this->returnDoc = "";
    }
    
    function get_names_from_url($url)
    {
        if(!preg_match("/^http:.*((\.xml)|(\.exe)|(\.gif))$/i",$url))
        {
            $text = file_get_contents($url);
            if(preg_match("/[\.|_]pdf\s*$/",$url)) $type = "pdf";
            elseif(preg_match("/\.txt\s*$/",$url)) $type = "txt";
            else $type = "html";
        	if($text)
        	{
        	    $host = "";
                if(preg_match("/^(.*\/)[^\/]*$/",$url,$arr)) $prefix = $arr[1];
                if(preg_match("/^(http:\/\/[^\/]*)\//",$url,$arr)) $host = $arr[1];
                if(preg_match("/^(.*)\?/",$url,$arr)) $base_url = $arr[1];
                else $base_url = $prefix;
            	if(preg_match("/[\.|_]pdf\s*$/",$url) || preg_match("/^%PDF-/",$text))
            	{
        	    	$OUT = fopen("/data/www/tools/tmp/tmp.pdf", "w+");
        	    	fwrite($OUT,$text);
        	    	fclose($OUT);
        	    	$text = shell_exec("/data/www/tools/pdftotext -layout -htmlmeta -enc UTF-8 -q /data/www/tools/tmp/tmp.pdf - ");
        	    	$type="pdf";
        	    	$OUT = fopen("/data/www/tools/tmp/pdf.txt", "w+");
        	    	fwrite($OUT,$text);
        	    	fclose($OUT);
        	    	//unlink("/data/www/tools/tmp/tmp.pdf");
            	}
            	$this->get_names($text,$host,$prefix,$url,$base_url,$type);
        	}
        }
        return $this->returnDoc;
    }

    
    function get_names($text,$host,$prefix,$url,$base_url,$type)
    {
        $this->last_genus = array();
        $this->all_names = array();
        $this->possible_names = array();
        $this->classIDs = array();
        $this->synIDs = array();
        $this->pages = array();
        $this->returnDoc = "";
        $this->currentString = "";
        $this->currentStringState = "";
        $this->wordListMatches = 0;
        $this->returnString = "";
        $this->returnScore = 0;
        $this->returnString2 = "";
        $this->returnScore2 = 0;
        
        if($type=="txt") 
        {
            $this->output("<pre>");
        }
        
        $parameters="";
        $maps = explode("|",$this->map);
        while(list($key,$val)=each($maps))
        {
            $parameters.="map%5B%5D=$val&";
        }
        if($this->redirect) $parameters.="redirect=".$this->redirect."&";
        if($this->nologo) $parameters.="no_logo=".$this->nologo."&";
        if($this->nb) $parameters.="nb=".$this->nb."&";
        if($this->link_type) $parameters.="link_type=".$this->link_type."&";
        if($this->classify) $parameters.="classify=".$this->classify."&";
        $parameters = substr($parameters,0,-1);
        
        switch($this->map)
        {
            case "itis":
                $this->collectionsID=1;
                break;
            case "fungi":
                $this->collectionsID=11;
                break;
            case "fish":
                $this->collectionsID=9;
                break;
            case "algae":
                $this->collectionsID=3;
                break;
            case "sp2000":
                $this->collectionsID=12;
                break;
            case "nz":
                $this->collectionsID=14;
                break;
            case "ncbi":
                $this->collectionsID=15;
                break;
            case "tree":
                $this->collectionsID=16;
                break;
            case "mobot":
                $this->collectionsID=17;
                break;
            case "citesPS":
                $this->collectionsID=18;
                break;
            case "citesPG":
                $this->collectionsID=19;
                break;
            case "citesAS":
                $this->collectionsID=20;
                break;
            case "citesAG":
                $this->collectionsID=21;
                break;
            case "micro":
                $this->collectionsID=24;
                break;
            case "custar":
                $this->collectionsID=26;
                break;
            case "grin":
                $this->collectionsID=27;
                break;
            case "tol":
                $this->collectionsID=28;
                break;
            case "pubmed":
                $this->collectionsID=29;
                break;
            case "iucn":
                $this->collectionsID=32;
                break;
            case "erms":
                $this->collectionsID=36;
                break;
            case "morph":
                $this->collectionsID=37;
                break;
            case "trop":
                $this->collectionsID=93;
                break;
            case "fe":
                $this->collectionsID=108;
                break;
            case "lr":
                $this->collectionsID=110;
                break;
            default:
                if(preg_match("/|/",$this->map))
                {
                    $collectionsID="";
                    $maps = explode("|",$this->map);
                    while(list($key,$val)=each($maps))
                    {
                        switch($val)
                        {
                            case "itis":
                                $this->collectionsID.="1 OR mappings.collectionsID=";
                                break;
                            case "fungi":
                                $this->collectionsID.="11 OR mappings.collectionsID=";
                                break;
                            case "fish":
                                $this->collectionsID.="9 OR mappings.collectionsID=";
                                break;
                            case "algae":
                                $this->collectionsID.="3 OR mappings.collectionsID=";
                                break;
                            case "sp2000":
                                $this->collectionsID.="12 OR mappings.collectionsID=";
                                break;
                            case "nz":
                                $this->collectionsID.="14 OR mappings.collectionsID=";
                                break;
                            case "ncbi":
                                $this->collectionsID.="15 OR mappings.collectionsID=";
                                break;
                            case "tree":
                                $this->collectionsID.="16 OR mappings.collectionsID=";
                                break;
                            case "mobot":
                                $this->collectionsID.="17 OR mappings.collectionsID=";
                                break;
                            case "citesPS":
                                $this->collectionsID.="18 OR mappings.collectionsID=";
                                break;
                            case "citesPG":
                                $this->collectionsID.="19 OR mappings.collectionsID=";
                                break;
                            case "citesAS":
                                $this->collectionsID.="20 OR mappings.collectionsID=";
                                break;
                            case "citesAG":
                                $this->collectionsID.="21 OR mappings.collectionsID=";
                                break;
                            case "micro":
                                $this->collectionsID.="24 OR mappings.collectionsID=";
                                break;
                            case "custar":
                                $this->collectionsID.="26 OR mappings.collectionsID=";
                                break;
                            case "grin":
                                $this->collectionsID.="27 OR mappings.collectionsID=";
                                break;
                            case "tol":
                                $this->collectionsID.="28 OR mappings.collectionsID=";
                                break;
                            case "pubmed":
                                $this->collectionsID.="29 OR mappings.collectionsID=";
                                break;
                            case "iucn":
                                $this->collectionsID.="32 OR mappings.collectionsID=";
                                break;
                            case "erms":
                                $this->collectionsID.="36 OR mappings.collectionsID=";
                                break;
                            case "morph":
                                $this->collectionsID.="37 OR mappings.collectionsID=";
                                break;
                            case "trop":
                                $this->collectionsID.="93 OR mappings.collectionsID=";
                                break;
                            case "fe":
                                $this->collectionsID.="108 OR mappings.collectionsID=";
                                break;
                            case "lr":
                                $this->collectionsID.="110 OR mappings.collectionsID=";
                                break;
                            case "namebank":
                                $this->collectionsID.="9999 OR mappings.collectionsID=";
                                break;
                        }
                    }
                    $this->collectionsID = substr($this->collectionsID,0,-27);
                }
        }
        
        $in_name="";
        $in_vern=false;
        $begin=false;
        $between="";
        $did_header=false;
        $text_before="";
        $text_beforeNB="";
        $last_word_name=false;
        $in_alt=false;
        $in_tag=false;
        $between_tag=false;
        $in_style=false;
        $in_js=false;
        
        //while(preg_match("/href =/i",$text)) $text=preg_replace("/href =/i","href=",$text);
        //while(preg_match("/href= /i",$text)) $text=preg_replace("/href= /i","href=",$text);
        $words_temp = preg_split("/( |&nbsp;|<|>|\t|\n|\r|;|\.)/i",$text,-1,PREG_SPLIT_DELIM_CAPTURE);
        $num = count($words_temp);
        unset($words);
        $n=0;
        for($i=0 ; $i<$num ; $i++)
        {
            if($words_temp[$i]=="<")
            {
                $words[$n]=$words_temp[$i];
            }elseif($words_temp[$i]==">")
            {
                $words[$n-1].=$words_temp[$i];
            }elseif($words_temp[$i]==".")
            {
                $words[$n-1].=$words_temp[$i];
            }elseif($words_temp[$i]==";")
            {
                $words[$n-1].=$words_temp[$i];
            }elseif(preg_match("/^[[:space:]]*$/",$words_temp[$i]) && $n!=0)
            {
                $words[$n-1].=$words_temp[$i];
            }elseif(preg_match("/^([\S\s]*:)([\S\s]*)$/",$words_temp[$i],$arr))
            {
                @$words[$n].=$arr[1];
                $n++;
                @$words[$n].=$arr[2];
                $n++;
            }else
            {
                @$words[$n].=$words_temp[$i];
                $n++;
            }
        }
        $this->output("<base href='$prefix'>");
        $num = count($words);
        $OUTPUT = fopen("names.txt","w+");
        for($i=0 ; $i<$num ; $i++)
        {
            flush();
            $thisword = $words[$i];
            
            if((preg_match("/^(href=[\"']?)([\S\s]*)$/im",$thisword,$arr)||preg_match("/^(action=[\"']?)([\S\s]*)$/im",$thisword,$arr)))
            {
                if(preg_match("/^<base[^>]*$/im",$words[$i-1]))
                {
                    $prefix = $arr[2];
                }elseif($this->redirect && !preg_match("/\.css/",$thisword) && !$in_style)
                {
                    if(preg_match("/^http:/i",$arr[2]))
                    {
                        $thisword = $arr[1]."http://names.ubio.org/tools/linkit.php?$parameters&url=".$arr[2];
                    }elseif(preg_match("/^\//",$arr[2]))
                    {
                        $thisword = $arr[1]."http://names.ubio.org/tools/linkit.php?$parameters&url=".$host.$arr[2];
                    }elseif(preg_match("/^\?/",$arr[2]))
                    {
                        $thisword = $arr[1]."http://names.ubio.org/tools/linkit.php?$parameters&url=".$base_url.$arr[2];
                    }else $thisword = $arr[1]."http://names.ubio.org/tools/linkit.php?$parameters&url=".$prefix.$arr[2];
                }else
                {
                    if(!preg_match("/^http:/i",$arr[2]) && !preg_match("/^#/",$arr[2]))
                    {
                        if(preg_match("/^\//",$arr[2]))
                        {
                            $thisword = $arr[1].$host.$arr[2];
                        }elseif(preg_match("/^\?/",$arr[2]))
                        {
                            $thisword = $arr[1].$base_url.$arr[2];
                        }else $thisword = $arr[1].$prefix.$arr[2];
                    }
                }
            }elseif(preg_match("/^(\s*background=[\"']?)([\S\s]*)$/im",$thisword,$arr)||preg_match("/^(src=[\"']?)([\S\s]*)$/im",$thisword,$arr))
            {
                if(!preg_match("/^http:/i",$arr[2]) && !preg_match("/^#/",$arr[2]))
                {
                    if(preg_match("/^\//",$arr[2]))
                    {
                        $thisword = $arr[1].$host.$arr[2];
                    }else 
                    {
                        $thisword = $arr[1].$prefix.$arr[2];
                    }
                }
            }elseif(preg_match("/^(url\([\"']?)(.*)([\"']?\);([\S\s]*))$/im",$thisword,$arr))
            {
                if(!preg_match("/^http:/i",$arr[2]) && !preg_match("/^#/",$arr[2]))
                {
                    if(preg_match("/^\//",$arr[2]))
                    {
                        $thisword = $arr[1].$host.$arr[2].$arr[3];
                    }else 
                    {
                        $thisword = $arr[1].$prefix.$arr[2].$arr[3];
                    }
                }
            }elseif(preg_match("/^\s*@import\s*$/im",$thisword))
            {
                if(preg_match("/(\s*[\"'])(.*)([\"'];([\S\s]*))$/im",$words[$i+1],$arr))
                {
                    if(!preg_match("/^http:/i",$arr[2]) && !preg_match("/^#/",$arr[2]))
                    {
                        if(preg_match("/^\//",$arr[2]))
                        {
                            $words[$i+1] = $words[$i+1] = $arr[1].$host.$arr[2].$arr[3];
                        }else $words[$i+1] = $arr[1].$host."/".$arr[2].$arr[3];
                    }
                }
            }
            //continue;
            $words[$i] = $thisword;
            
            if($in_js)
            {
                if(preg_match("/script/",$words[$i],$arr)) 
                {
                    $in_js=false;
                }
                continue;
            }
            
            if($in_style)
            {
                if(preg_match("/>/",$words[$i],$arr)) $in_style=false;
                if($text_before) 
                {
                    $text_before.=$thisword;
                    $text_beforeNB.=$thisword;
                }
                else $this->output($words[$i]);
                continue;
            }
            
            if(preg_match("/^<link[^>]*$/im",$words[$i]))
            {
                $in_style=true;
                if($text_before) 
                {
                    $text_before.=$thisword;
                    $text_beforeNB.=$thisword;
                }
                else $this->output($words[$i]);
                continue;
            }
            
            if(!$did_header && (preg_match("/^<\/(head)>/i",$words[$i],$arr) || preg_match("/^<body/i",$words[$i],$arr)))
            {
                if(@$arr[1]=="body") $this->output("<head>");
                ?>
                    <style type="text/css">
                        #dropmenudiv{
                            position:absolute;
                            border:1px solid black;
                            border-bottom-width: 0;
                            font:normal 12px Verdana;
                            line-height:18px;
                            z-index:100;
                        }
                        
                        #dropmenudiv a{
                            width: 100%;
                            display: block;
                            text-indent: 3px;
                            border-bottom: 1px solid black;
                            padding: 1px 0;
                            text-decoration: none;
                            font-weight: bold;
                        }
                        
                        #dropmenudiv a:hover{ /*hover background color*/
                            background-color: white;
                        }
                    </style>
                    
                    <script language="JavaScript"><!--
                        
                        function popupWinTaxonFinder(theString,theFunc,theType)
                        {
                            newTargetTaxonFinder('http://names.mbl.edu/taxonfinder/popup.php?string='+theString+'&func='+theFunc+'&type='+theType, 'left=1500,top=30,width=500,height=300,resizable=0,scrollbars=0,status=0');
                        }
                        
                        function popupWinTaxonFinder2()
                        {
                            newTargetTaxonFinder('http://names.mbl.edu/taxonfinder/popup.php?func=addForm', 'left=1500,top=30,width=500,height=300,resizable=0,scrollbars=0,status=0');
                        }
                        
                        function newTargetTaxonFinder (page, features, windowName) 
                        {
                            newwin=window.open(page, windowName, features);
                            if (newwin.opener==null) newwin.opener=window;
                            newwin.opener.name = "opener";
                            newwin.focus();	
                        }

                        function popupWin_NAMES(theString,mouseX,mouseY)
                        {
                            //alert(''+mouseX+' - '+mouseY+'');
                            mouseX = mouseX-77;
                            mouseY = mouseY-180;
                            newTarget_NAMES('http://names.ubio.org/tools/recognize_popup.php?string='+theString, 'top='+mouseY+',left='+mouseX+',width=150,height=50,resizable=0,scrollbars=0,status=0');
                        }
                        
                        function newTarget_NAMES(page, features, windowName) 
                        {
                            newwin=window.open(page, windowName, features);
                            if (newwin.opener==null) newwin.opener=window;
                            newwin.opener.name = "opener";
                            newwin.focus();	
                        }                
                        
                        function togNode_NAMES(strNodeID)
                        {
                            var node  = document.getElementById(strNodeID);
                            var style = node.style.display;
                        
                            if (style == "none")
                            {
                                node.style.display = "block";
                            }
                            else
                            {
                                node.style.display = "none";
                            }
                        }
                        
                        function submitClassification_NAMES()
                        {
                            newwin=window.open("","classification_NAMES","width=700,height=400,top=200,left=200,toolbar=yes,scrollbars=yes,resizable=1");
                            if (newwin.opener==null) newwin.opener=window;
                            newwin.opener.name = "opener";
                            document.classification_NAMES.submit();
                        }
                        
                        /***********************************************
                        * AnyLink Drop Down Menu- © Dynamic Drive (www.dynamicdrive.com)
                        * This notice MUST stay intact for legal use
                        * Visit http://www.dynamicdrive.com/ for full source code
                        ***********************************************/
                        		
                        var menuwidth='165px' //default menu width
                        var menubgcolor='white'  //menu bgcolor
                        var disappeardelay=250  //menu disappear speed onMouseout (in miliseconds)
                        var hidemenu_onclick="yes" //hide menu when user clicks within menu?
                        
                        /////No further editting needed
                        
                        var ie4=document.all
                        var ns6=document.getElementById&&!document.all
                        
                        if (ie4||ns6)
                        document.write('<div id="dropmenudiv" style="visibility:hidden;width:'+menuwidth+';background-color:'+menubgcolor+'" onMouseover="clearhidemenu()" onMouseout="dynamichide(event)"></div>')
                        
                        function getposOffset(what, offsettype){
                        var totaloffset=(offsettype=="left")? what.offsetLeft : what.offsetTop;
                        var parentEl=what.offsetParent;
                        while (parentEl!=null){
                        totaloffset=(offsettype=="left")? totaloffset+parentEl.offsetLeft : totaloffset+parentEl.offsetTop;
                        parentEl=parentEl.offsetParent;
                        }
                        return totaloffset;
                        }
                        
                        
                        function showhide(obj, e, visible, hidden, menuwidth){
                        if (ie4||ns6)
                        dropmenuobj.style.left=dropmenuobj.style.top=-500
                        if (menuwidth!=""){
                        dropmenuobj.widthobj=dropmenuobj.style
                        dropmenuobj.widthobj.width=menuwidth
                        }
                        if (e.type=="click" && obj.visibility==hidden || e.type=="mouseover")
                        obj.visibility=visible
                        else if (e.type=="click")
                        obj.visibility=hidden
                        }
                        
                        function iecompattest(){
                        return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
                        }
                        
                        function clearbrowseredge(obj, whichedge){
                        var edgeoffset=0
                        if (whichedge=="rightedge"){
                        var windowedge=ie4 && !window.opera? iecompattest().scrollLeft+iecompattest().clientWidth-15 : window.pageXOffset+window.innerWidth-15
                        dropmenuobj.contentmeasure=dropmenuobj.offsetWidth
                        if (windowedge-dropmenuobj.x < dropmenuobj.contentmeasure)
                        edgeoffset=windowedge-dropmenuobj.x-dropmenuobj.contentmeasure-5
                        }
                        else{
                        var topedge=ie4 && !window.opera? iecompattest().scrollTop : window.pageYOffset
                        var windowedge=ie4 && !window.opera? iecompattest().scrollTop+iecompattest().clientHeight-15 : window.pageYOffset+window.innerHeight-18
                        dropmenuobj.contentmeasure=dropmenuobj.offsetHeight
                        if (windowedge-dropmenuobj.y < dropmenuobj.contentmeasure){ //move up?
                        edgeoffset=dropmenuobj.contentmeasure+obj.offsetHeight
                        if ((dropmenuobj.y-topedge)<dropmenuobj.contentmeasure) //up no good either?
                        edgeoffset=dropmenuobj.y+obj.offsetHeight-topedge
                        }
                        }
                        return edgeoffset
                        }
                        
                        function populatemenu(what){
                        if (ie4||ns6)
                        dropmenuobj.innerHTML=what.join("")
                        }
                        
                        
                        function dropdownmenu(obj, e, menucontents, menuwidth){
                        if (window.event) event.cancelBubble=true
                        else if (e.stopPropagation) e.stopPropagation()
                        clearhidemenu()
                        dropmenuobj=document.getElementById? document.getElementById("dropmenudiv") : dropmenudiv
                        populatemenu(menucontents)
                        
                        if (ie4||ns6){
                        showhide(dropmenuobj.style, e, "visible", "hidden", menuwidth)
                        dropmenuobj.x=getposOffset(obj, "left")
                        dropmenuobj.y=getposOffset(obj, "top")
                        dropmenuobj.style.left=dropmenuobj.x+clearbrowseredge(obj, "rightedge")+"px"
                        dropmenuobj.style.top=dropmenuobj.y-clearbrowseredge(obj, "bottomedge")+obj.offsetHeight+"px"
                        }
                        
                        return clickreturnvalue()
                        }
                        
                        function clickreturnvalue(){
                        if (ie4||ns6) return false
                        else return true
                        }
                        
                        function contains_ns6(a, b) {
                        while (b.parentNode)
                        if ((b = b.parentNode) == a)
                        return true;
                        return false;
                        }
                        
                        function dynamichide(e){
                        if (ie4&&!dropmenuobj.contains(e.toElement))
                        delayhidemenu()
                        else if (ns6&&e.currentTarget!= e.relatedTarget&& !contains_ns6(e.currentTarget, e.relatedTarget))
                        delayhidemenu()
                        }
                        
                        function hidemenu(e){
                        if (typeof dropmenuobj!="undefined"){
                        if (ie4||ns6)
                        dropmenuobj.style.visibility="hidden"
                        }
                        }
                        
                        function delayhidemenu(){
                        if (ie4||ns6)
                        delayhide=setTimeout("hidemenu()",disappeardelay)
                        }
                        
                        function clearhidemenu(){
                        if (typeof delayhide!="undefined")
                        clearTimeout(delayhide)
                        }
                        
                        if (hidemenu_onclick=="yes")
                        document.onclick=hidemenu
                    //--></script>
                <?php
                if(@$arr[1]=="body") $this->output("</head>");
                $did_header=true;
            }
            
            if(@!$begin && ($type=="txt" || $type=="pdf" || preg_match("/^<body/i",$thisword) || preg_match("/^\s*<\/head/i",$thisword) || preg_match("/^\s*<p>/i",$thisword))) $begin=true;
            
            if($begin)
            {
                //echo "|$thisword|\n<br>";
                $clean_word = $this->clean_text($thisword);
                //echo "|$clean_word|\n<br>";
                //continue;
                $good=false;
                
                if($in_alt)
                {
                    if(preg_match("/$in_alt/",$words[$i],$arr)) $in_alt=false;
                    if($text_before) 
                    {
                        $text_before.=$thisword;
                        $text_beforeNB.=$thisword;
                    }
                    else $this->output($words[$i]);
                    continue;
                }
                
                if($in_tag)
                {
                    if(preg_match("/>/",$words[$i])) $in_tag=false;
                    if($text_before) 
                    {
                        $text_before.=$thisword;
                        $text_beforeNB.=$thisword;
                    }
                    else $this->output($words[$i]);
                    continue;
                }
                
                if($between_tag)
                {
                    if(preg_match("/<\/$between_tag/",$words[$i])) $between_tag=false;
                    if($text_before) 
                    {
                        $text_before.=$thisword;
                        $text_beforeNB.=$thisword;
                    }
                    else $this->output($words[$i]);
                    continue;
                }
                
                if($text_before && (preg_match("/^<(.*)>[[:space:]]*$/m",$words[$i]) || preg_match("/class=\"?[A-Za-z]+\"?>[[:space:]]*$/m",$words[$i]) || preg_match("/^\.[[:space:]]*$/m",$words[$i]) || preg_match("/^<\/span>[[:space:]]*$/im",$words[$i]) || preg_match("/^<\/font>[[:space:]]*$/im",$words[$i])) && !preg_match("/^(p|div|br|tr|td|hr|li|h[1-4]|ol|th)$/i",$clean_word))
                {
                    $text_before.=$thisword;
                    $text_beforeNB.=$thisword;
                    continue;
                }
                
                if(preg_match("/^<a\s*$/im",$words[$i]) || preg_match("/^<span[^>]*$/im",$words[$i]) || preg_match("/^<font[^>]*$/im",$words[$i]) || preg_match("/^<li[^>]*$/im",$words[$i]))
                {
                    $in_tag=true;
                    if($text_before) 
                    {
                        $text_before.=$thisword;
                        $text_beforeNB.=$thisword;
                    }
                    else $this->output($words[$i]);
                    continue;
                }
                
                if(preg_match("/^<(title)[\s\S]*$/im",$words[$i],$arr) || preg_match("/^<((cc|dc):)[\s\S]*$/im",$words[$i],$arr))
                {
                    echo "$arr[1]<br>";
                    $between_tag=$arr[1];
                    if($text_before) 
                    {
                        $text_before.=$thisword;
                        $text_beforeNB.=$thisword;
                    }
                    else $this->output($words[$i]);
                    continue;
                }
                
                //if(preg_match("/^alt=([\"'])[^\\1]*/",$words[$i],$arr)||preg_match("/^title=([\"'])[^\\1]*/",$words[$i],$arr))
                if(preg_match("/^alt=(\")[^\"]*$/",$words[$i],$arr)||preg_match("/^alt=(')[^']*$/",$words[$i],$arr)||preg_match("/^title=(\")[^\"]*$/",$words[$i],$arr)||preg_match("/^title=(')[^']*$/",$words[$i],$arr))
                {
                    if($arr[1]!="'") $in_alt = "\\"."\"";
                    else $in_alt="'";
                    if($text_before) 
                    {
                        $text_before.=$thisword;
                        $text_beforeNB.=$thisword;
                    }
                    else $this->output($words[$i]);
                    continue;
                }
                
                if(preg_match("/^\s*&/",$thisword))
                {
                    if($text_before) 
                    {
                        $text_before.=$thisword;
                        $text_beforeNB.=$thisword;
                    }
                    else $this->output($words[$i]);
                    continue;
                }
                
                
                
//                $word_to_send = trim($thisword);
//                $word_to_send = str_replace("\n"," ",$thisword);
//                $word_to_send = str_replace("\r"," ",$thisword);
//                $word_to_send = preg_replace("/\s/"," ",$thisword);
//                //$in = trim(str_replace(","," ",$thisword))."|$this->currentString|$this->currentStringState|$this->wordListMatches\n";
//                //$in = "$thisword|$this->currentString|$this->currentStringState|$this->wordListMatches\015\012";
//                $in = trim($word_to_send)."|$this->currentString|$this->currentStringState|$this->wordListMatches\n";
//                socket_write($this->socket, $in, strlen($in));
//                if($out = socket_read($this->socket, 2048))
//                {
//                    list($cs, $css, $wlm, $rst, $rsc, $rst2, $rsc2) = explode("|",trim($out));
//                    $this->currentString = $cs;
//                    $this->currentStringState = $css;
//                    $this->wordListMatches = $wlm;
//                    $this->returnString = $rst;
//                    $this->returnScore = $rsc;
//                    $this->returnString2 = $rst2;
//                    $this->returnScore2 = $rsc2;
//                    if($this->returnString)
//                    {
//                        //echo "IN:$in<br>OUT:$out<br>TB:$text_before<br>INNAME: $in_name<br>";
//                        $this->all_names[$this->returnString] = $this->returnScore;
//                        fwrite($OUTPUT, $this->returnString."\t".$this->returnScore."\n");
//                        
//                        $name_words = split(" ",$this->returnString);
//                        $search_string="/(";
//                        while(list($key,$val)=each($name_words))
//                        {
//                            if($key==0 && preg_match("/^(.*)(\[.*\])$/",$val,$arr))
//                            {
//                                $val = preg_replace("/[\[\]]/","",$name_words[$key]);
//                                $name_words[$key] = $val;
//                                //echo "preg_replace(/^$arr[1]\./m,$val,$text_before)<br>";
//                                $text_before = preg_replace("/^".$arr[1]."\./m",$val,$text_before);
//                            }else
//                            {
//                                $val = str_replace(".","\.",$val);
//                                $val = str_replace("(","\(",$val);
//                                $val = str_replace(")","\)",$val);
//                                $name_words[$key] = $val;
//                            }
//                            $search_string.=$val."[\s\S]*";
//                        }
//                        $search_string = substr($search_string,0,-7).")/mi";
//                        
//                        if($in_name==$this->returnString) $toOutput = $this->get_link($this->returnString,$text_before,$search_string);
//                        else
//                        {
//                            //echo "okokok<br>";
//                            $toOutput = $this->get_link($this->returnString,$text_before.$thisword,$search_string);
//                        }
//                        $this->output($toOutput);                        
//                        
//                        $continue = 0;
//                        if($in_name != $this->returnString) $continue = 1;
//                        $in_name = "";
//                        $text_before = "";
//                        $text_beforeNB = "";
//                        if($continue) continue;
//                    }
//                    if($this->returnString2)
//                    {
//                        $this->all_names[$this->returnString2] = $this->returnScore2;
//                        fwrite($OUTPUT, $this->returnString2."\t".$this->returnScore2."\n");
//                        
//                        $name_words = split(" ",$this->returnString2);
//                        $search_string="/(";
//                        while(list($key,$val)=each($name_words))
//                        {
//                            if($key==0 && preg_match("/^(.*)(\[.*\])$/",$val,$arr))
//                            {
//                                $val = preg_replace("/[\[\]]/","",$name_words[$key]);
//                                $name_words[$key] = $val;
//                                $text_before = preg_replace("/^".$arr[1]."\./m",$val,$text_before);
//                            }else
//                            {
//                                $val = str_replace(".","\.",$val);
//                                $val = str_replace("(","\(",$val);
//                                $val = str_replace(")","\)",$val);
//                                $name_words[$key] = $val;
//                            }
//                            $search_string.=$val."[\s\S]*";
//                        }
//                        $search_string = substr($search_string,0,-7).")/mi";
//                        
//                        $toOutput = $this->get_link($this->returnString,$thisword,$search_string);
//                        $this->output($toOutput);
//                        
//                        $in_name = "";
//                        $text_before = "";
//                        $text_beforeNB = "";
//                        continue;
//                    }elseif($this->wordListMatches || $this->currentStringState=="genus")
//                    {
//                        if(strlen($this->wordListMatches)==1)
//                        {
//                            $this->output($text_before);
//                            $text_before = "";
//                        }
//                        $text_before .= $thisword;
//                        $text_beforeNB .= $thisword;
//                        $in_name = $this->currentString;
//                        //echo "IN NAME:$in_name<br>";
//                        continue;
//                    }elseif($text_before)
//                    {
//                        $this->output($text_before);
//                        $text_before = "";
//                        $text_beforeNB = "";
//                        $in_name = "";
//                    }
//                    
//                    //echo "IN:$in<br>OUT:$out<br>TB:$text_before<br><br>";
//                }
                
                
                $word_to_send = trim($thisword);
                $word_to_send = str_replace("\n"," ",$thisword);
                $word_to_send = str_replace("\r"," ",$thisword);
                $word_to_send = preg_replace("/\s/"," ",$thisword);
                //$in = trim(str_replace(","," ",$thisword))."|$this->currentString|$this->currentStringState|$this->wordListMatches\n";
                //$in = "$thisword|$this->currentString|$this->currentStringState|$this->wordListMatches\015\012";
                $in = trim($word_to_send)."|$this->currentString|$this->currentStringState|$this->wordListMatches\n";
                socket_write($this->socket, $in, strlen($in));
                if($out = socket_read($this->socket, 2048))
                {
                    list($cs, $css, $wlm, $rst, $rsc, $rst2, $rsc2) = explode("|",trim($out));
                    $this->currentString = $cs;
                    $this->currentStringState = $css;
                    $this->wordListMatches = $wlm;
                    $this->returnString = $rst;
                    $this->returnScore = $rsc;
                    $this->returnString2 = $rst2;
                    $this->returnScore2 = $rsc2;
                    if($this->returnString)
                    {
                        $this->all_names[$this->returnString] = $this->returnScore;
                        fwrite($OUTPUT, $this->returnString."\t".$this->returnScore."\n");
                        
                        $name_words = split(" ",$this->returnString);
                        $search_string="/(";
                        while(list($key,$val)=each($name_words))
                        {
                            if($key==0 && preg_match("/^(.*)(\[.*\])$/",$val,$arr))
                            {
                                $val = preg_replace("/[\[\]]/","",$name_words[$key]);
                                $name_words[$key] = $val;
                                //echo "preg_replace(/^$arr[1]\./m,$val,$text_before)<br>";
                                $text_before = preg_replace("/^".$arr[1]."\./m",$val,$text_before);
                            }else
                            {
                                $val = str_replace(".","\.",$val);
                                $val = str_replace("(","\(",$val);
                                $val = str_replace(")","\)",$val);
                                $name_words[$key] = $val;
                            }
                            $search_string.=$val."[\s\S]*";
                        }
                        $search_string = substr($search_string,0,-7).")/mi";
                        
                        //echo "preg_replace($search_string,<b>\\1</b>,$text_before);<br>\n";
                        if($in_name==$this->returnString) $toOutput = preg_replace($search_string,"<b>\\1</b>",$text_before);
                        else $toOutput = preg_replace($search_string,"<b>\\1</b>",$text_before.$thisword);
                        //echo "$toOutput<br>";
                        $name = "";
                        reset($name_words);
                        unset($used);
                        while(list($key,$val)=each($name_words))
                        {
                            if(@$used[strtolower($val)]) continue;
                            if(preg_match("/^[G]$/",$this->returnScore[$key],$arr)) $toOutput = preg_replace("/($val)/mi","\\1<a href='' title='Ambig' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','ambig','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/yellow.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a><a href='' title='Add' onClick=\"popupWinTaxonFinder2(); return false;\"><img src=http://www.ubio.org/images/tree/blue_dot.png border=0></a>",$toOutput);
                            elseif(preg_match("/^[F]$/",$this->returnScore[$key],$arr)) $toOutput = preg_replace("/($val)/mi","\\1<a href='' title='Ambig' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','ambig','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/yellow.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a><a href='' title='Add' onClick=\"popupWinTaxonFinder2(); return false;\"><img src=http://www.ubio.org/images/tree/green_dot.png border=0></a>",$toOutput);
                            elseif(preg_match("/^[S]$/",$this->returnScore[$key],$arr)) $toOutput = preg_replace("/($val)/mi","\\1<a href='' title='Ambig' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','ambig','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/yellow.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a><a href='' title='Add' onClick=\"popupWinTaxonFinder2(); return false;\"><img src=http://www.ubio.org/images/tree/blue_dot.png border=0></a>",$toOutput);
                            elseif(preg_match("/^[fgs]$/",$this->returnScore[$key],$arr)) $toOutput = preg_replace("/($val)/mi","<font color=blue>\\1</font><a href='' title='Verify' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','verify','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/green.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a>",$toOutput);
                            elseif(preg_match("/^[R0]$/",$this->returnScore[$key],$arr)) $toOutput = preg_replace("/($val)/mi","\\1",$toOutput);
                            else $toOutput = preg_replace("/($val)/mi","<font color=red>\\1</font><a href='' title='Add' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','add','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/green.gif border=0></a><a href='' title='Ambig' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','ambig','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/yellow.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a>",$toOutput);
                            $used[strtolower($val)] = 1;
                        }
                        unset($used);
                        $name = trim($name);
                        $this->output($toOutput);
                        
                        //$this->output($this->get_link($in_name,$text_before,$search_string));
                        
                        $continue = 0;
                        if($in_name != $this->returnString) $continue = 1;
                        $in_name = false;
                        $text_before = "";
                        $text_beforeNB = "";
                        //if($continue) continue;
                    }
                    if($this->returnString2)
                    {
                        $in_name = $this->returnString2;
                        fwrite($OUTPUT, $this->returnString2."\t".$this->returnScore2."\n");
                        $this->all_names[$in_name] = $this->returnScore2;
                        
                        $name_words = split(" ",$in_name);
                        $search_string="/(";
                        while(list($key,$val)=each($name_words))
                        {
                            if($key==0 && preg_match("/^(.*)(\[.*\])$/",$val,$arr))
                            {
                                $val = preg_replace("/[\[\]]/","",$name_words[$key]);
                                $name_words[$key] = $val;
                                $text_before = preg_replace("/^".$arr[1]."\./m",$val,$text_before);
                            }else
                            {
                                $val = str_replace(".","\.",$val);
                                $val = str_replace("(","\(",$val);
                                $val = str_replace(")","\)",$val);
                                $name_words[$key] = $val;
                            }
                            $search_string.=$val."[\s\S]*";
                        }
                        $search_string = substr($search_string,0,-7).")/mi";
                        
                        //echo "preg_replace($search_string,<b>\\1</b>,$thisword);<br>";
                        $toOutput = preg_replace($search_string,"<b>\\1</b>",$thisword);
                        $name = "";
                        reset($name_words);
                        unset($used);
                        while(list($key,$val)=each($name_words))
                        {
                            if(@$used[strtolower($val)]) continue;
                            if(preg_match("/^[G]$/",$this->returnScore2[$key],$arr)) $toOutput = preg_replace("/($val)/mi","\\1<a href='' title='Ambig' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','ambig','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/yellow.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a><a href='' title='Add' onClick=\"popupWinTaxonFinder2(); return false;\"><img src=http://www.ubio.org/images/tree/blue_dot.png border=0></a>",$toOutput);
                            elseif(preg_match("/^[F]$/",$this->returnScore2[$key],$arr)) $toOutput = preg_replace("/($val)/mi","\\1<a href='' title='Ambig' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','ambig','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/yellow.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a><a href='' title='Add' onClick=\"popupWinTaxonFinder2(); return false;\"><img src=http://www.ubio.org/images/tree/green_dot.png border=0></a>",$toOutput);
                            elseif(preg_match("/^[S]$/",$this->returnScore2[$key],$arr)) $toOutput = preg_replace("/($val)/mi","\\1<a href='' title='Ambig' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','ambig','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/yellow.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a><a href='' title='Add' onClick=\"popupWinTaxonFinder2(); return false;\"><img src=http://www.ubio.org/images/tree/blue_dot.png border=0></a>",$toOutput);
                            elseif(preg_match("/^[fgs]$/",$this->returnScore2[$key],$arr)) $toOutput = preg_replace("/($val)/mi","<font color=blue>\\1</font><a href='' title='Verify' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','verify','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/green.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a>",$toOutput);
                            elseif(preg_match("/^[R0]$/",$this->returnScore2[$key],$arr)) $toOutput = preg_replace("/($val)/mi","\\1",$toOutput);
                            else $toOutput = preg_replace("/($val)/mi","<font color=red>\\1</font><a href='' title='Add' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','add','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/green.gif border=0></a><a href='' title='Ambig' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','ambig','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/yellow.gif border=0></a><a href='' title='Remove' onClick=\"popupWinTaxonFinder('".base64_encode($val)."','remove','".$this->returnScore2[$key]."'); return false;\"><img src=http://names.ubio.org/tools/image/red.gif border=0></a>",$toOutput);
                            $used[strtolower($val)] = 1;
                        }
                        unset($used);
                        $name = trim($name);
                        $this->output($toOutput);
                        
                        //$this->output($this->get_link($in_name,$text_before,$search_string));
                        
                        $in_name = false;
                        $text_before = "";
                        $text_beforeNB = "";
                        //continue;
                    }elseif($this->wordListMatches || $this->currentStringState=="genus")
                    {
                        if(strlen($this->wordListMatches)==1)
                        {
                            $this->output($text_before);
                            $text_before = "";
                        }
                        $text_before .= $thisword;
                        $text_beforeNB .= $thisword;
                        $in_name = $this->currentString;
                        //continue;
                    }elseif($text_before)
                    {
                        $this->output($text_before);
                        $text_before = "";
                        $text_beforeNB = "";
                    }
                    
                    echo "IN:$in<br>OUT:$out<br>TB:$text_before<br><br>";
                }
                
                $in_name = false;
            }
            if(!$in_name && !$in_vern) 
            {
                $this->output($thisword);
            }
            flush();
        }
        fclose($OUTPUT);
        
        if($type=="txt") 
        {
            $this->output("</pre>");
        }
        return $this->returnDoc;
    }
    
    function output($text)
    {
        if($this->save) $this->returnDoc.=$text;
        else
        {
            //echo $text;
            flush();
        }
    }
    
    function clean_text($text)
    {
    	$thisword = trim($text);
    	if(preg_match("/^[^$this->capital$this->lower]*([$this->capital$this->lower]*)[^$this->capital$this->lower]*$/u",$thisword,$arr)) $thisword = $arr[1];
        return $thisword;
    }
    
    function get_abreviation($collectionsID)
    {
        if(@$this->abreviations[$collectionsID]) return $this->abreviations[$collectionsID];
        else return "????";
    }
    
    function all_names()
    {
        return $this->all_names;
    }
    
    function get_link($name,$text_before,$search_string)
    {               
        flush(); 
//        $name = str_replace("[","",$name);
//        $name = str_replace("]","",$name);
//        $text_before = str_replace("[","",$text_before);
//        $text_before = str_replace("]","",$text_before);
//        $search_string = str_replace("\[","",$search_string);
//        $search_string = str_replace("\]","",$search_string);
        
        
//        if(preg_match("/^([$this->capital])([$this->capital]+)$/",$name,$arr))
//        {
//            $name = $arr[1].strtolower($arr[2]);
//        }elseif(preg_match("/^([$this->capital])([$this->capital]+) ([$this->capital]+)$/",$name,$arr))
//        {
//            $name = $arr[1].strtolower($arr[2])." ".strtolower($arr[3]);
//        }
        
        $canon = trim(get_canonical($name));
        
        //if($this->ip=="128.128.169.144"||$this->ip=="128.128.172.241"||$this->ip=="69.168.81.169"||$this->ip=="128.128.169.62") $link="<a href='' onClick=\"popupWin_NAMES('".base64_encode($canon)."',event.screenX,event.screenY); return false;\"><img src=http://names.ubio.org/tools/image/yellow2.gif border=0></a>";
        //else $link="";
        $link="";
        
        
        $img="";
        if(!$this->nologo) $img = " <img src='http://names.ubio.org/tools/ubio_dots.gif' border=0>";
        
        $syn="";
        if($this->synonyms)
        {
            $result = $this->mysqli->query("SELECT namesB.nameString FROM names as namesA JOIN canonicalForms ON (namesA.canonicalFormID=canonicalForms.canonicalFormID) JOIN names as namesB ON (namesB.parentID=namesA.namebankID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' AND namesB.languageCode='".$this->synonyms."'");
            if($result && $row=$result->fetch_assoc())
            {
                $syn = " (<font color='orange'>".$row["nameString"]."</font>)";
            }
        }
        
        $anchor="";
        if($this->classify)
        {
            $inClass=false;
            $result = $this->mysqli->query("SELECT namebankID FROM names JOIN canonicalForms USING (canonicalFormID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."'");
            unset($namebankIDs);
            while($result && $row=$result->fetch_assoc())
            {
      		    $result2 = $this->mysqli_uio->query("SELECT * FROM HierarchiesNew WHERE childID=".$row["namebankID"]." AND _classificationsID=83");
  		        if($result2 && $row2=$result2->fetch_assoc())
  		        {
  		            $this->classIDs[$canon]=$row2["childID"];
  		            $theKey = $row2["ancestry"]."|".$row2["childID"];
  		            if($this->save || $this->map=="none") $this->pages[$theKey][]="<a href='' onClick=\"scrollThisWindow('".$this->anchor_index."_NAMES'); return false;\" class='classify_NAMES'>$canon</a>";
  		            else $this->pages[$theKey][]="<a href='' onClick=\"scrollMainWindow('".$this->anchor_index."_NAMES'); return false;\" class='classify_NAMES'>$canon</a>";
  		            break;
  		        }
  		    }
  		    if(@$this->classIDs[$canon]) 
  		    {
  		        $anchor="<a name='".$this->anchor_index."_NAMES'></a>";
  		        $this->anchor_index++;
  		    }else
  		    {
  		        $result = $this->mysqli->query("SELECT DISTINCT nB.namebankID,nB.nameString,nA.lexicalQualifier  FROM names AS nA JOIN canonicalForms ON (nA.canonicalFormID=canonicalForms.canonicalFormID) LEFT JOIN names AS nB ON (nA.lexicalUnit=nB.lexicalUnit) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' AND nB.lexicalUnit!=0");
                unset($namebankIDs);
                while($result && $row=$result->fetch_assoc())
                {
          		    $result2 = $this->mysqli_uio->query("SELECT * FROM HierarchiesNew WHERE childID=".$row["namebankID"]." AND _classificationsID=83");
      		        if($result2 && $row2=$result2->fetch_assoc())
      		        {
      		            $this->classIDs[$canon]=$row2["childID"];
      		            $theKey = $row2["ancestry"]."|".$row2["childID"];
      		            $lexEnding = "(".$row["lexicalQualifier"].")";
      		            if($this->save || $this->map=="none") $this->pages[$theKey][]="<a href='' onClick=\"scrollThisWindow('".$this->anchor_index."_NAMES'); return false;\" class='classify_NAMES'>$canon $lexEnding</a>";
      		            else $this->pages[$theKey][]="<a href='' onClick=\"scrollMainWindow('".$this->anchor_index."_NAMES'); return false;\" class='classify_NAMES'>$canon $lexEnding</a>";
      		            break;
      		        }
      		    }
      		    if(@$this->classIDs[$canon]) 
      		    {
      		        $anchor="<a name='".$this->anchor_index."_NAMES'></a>";
      		        $this->anchor_index++;
      		    }
  		    }
        }
        
        
        
        if(($this->save && $this->save!="return") || $this->map=="none") return $anchor.$text_before;


        
        if($this->map=="namebank")
        {
            //$result = $this->mysqli->query("SELECT namebankID,nameString FROM names WHERE nameString='".$this->mysqli->real_escape_string($canon)."' AND languageCode='sci'");
            $result = $this->mysqli->query("SELECT namebankID,nameString FROM names JOIN canonicalForms USING (canonicalFormID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' ORDER BY CHAR_LENGTH(nameString) DESC");
            if($result && $row=$result->fetch_assoc())
            {
                if($this->link_type==1) return $anchor.preg_replace($search_string,"<a href=http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]." target=new>\\1</a>".$syn.$link.$img,$text_before);
                elseif($this->link_type==2) 
                {
                    $row["nameString"] = str_replace("'","",$row["nameString"]);
                    $string = "<script type=\"text/JavaScript\"><!--\n
                        var menu".$this->num_menus."=new Array()\n
                        menu".$this->num_menus."[0]='<a href=\"http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]."\" target=new><img src=http://names.ubio.org/tools/image/ubio.gif border=0>".$row["nameString"]."</a>'\n
                        //--></script>\n
                        <b>\\1</b> <a href='' onClick=\"return clickreturnvalue()\" onMouseover=\"dropdownmenu(this, event, menu".$this->num_menus.", '".((strlen($row["nameString"])*9)+46)."')\" onMouseout=\"delayhidemenu()\"><img src=http://names.ubio.org/tools/ubio_dots.gif border=0></a>\n";
                    $this->num_menus++;
                    return $anchor.preg_replace($search_string,$string.$syn.$link,$text_before);
                }
                else return $anchor.preg_replace($search_string,"<b>\\1</b>".$syn."$link".$img."[<a href=http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]." target=new>nb</a>]",$text_before);
            }
        }elseif($this->map=="pub")
        {
            $numR=0;
            $maxLength=0;
            if($this->link_type==2)
            {
                $toReturn = "<script type=\"text/JavaScript\"><!--\n
                        var menu".$this->num_menus."=new Array()\n";
            }
            $result = $this->mysqli_rss->query("SELECT itemID FROM ItemNames JOIN Names USING (nameID) WHERE nameString='".$this->mysqli->real_escape_string($canon)."' ORDER BY itemID DESC LIMIT 15");
            $used = array();
            $i=0;
            while($result && $row=$result->fetch_assoc())
            {
                if($this->link_type==2) 
                {
                    $result2 = $this->mysqli_rss->query("SELECT date,imageurl,imagelink,title,itemTitle,itemLink FROM FeedItems JOIN RssFeeds USING (feedID) WHERE itemID=".$row["itemID"]);
                    $row2 = $result2->fetch_assoc();
                    $imageurl = $row2["imageurl"];
                    $imagelink = $row2["imagelink"];
                    $title = $row2["title"];
                    $date = substr($row2["date"],0,10);
                    if(preg_match("/^<!\[CDATA\[\[[^\]]*\](.*)\]\]>$/",$title,$arr)) $title = $arr[1];
                    if(preg_match("/^(.*): Table of Contents$/i",$title,$arr)) $title = $arr[1];
                    if(preg_match("/^(.*) - Latest articles$/i",$title,$arr)) $title = $arr[1];
                    if(preg_match("/^Blackwell Synergy: (.*)$/i",$title,$arr)) $title = $arr[1];
                    $title = str_replace("'","",$title);
                    $title = str_replace("\"","",$title);
                    $itemTitle = $row2["itemTitle"];
                    if(preg_match("/^<!\[CDATA\[\[[^\]]*\](.*)\]\]>$/",$itemTitle,$arr)) $itemTitle = $arr[1];
                    if(preg_match("/^<!\[CDATA/",$itemTitle,$arr))
                    {
                        $itemTitle = str_replace("<![CDATA[","",$itemTitle);
                        $itemTitle = str_replace("]]>","",$itemTitle);
                    }
                    $itemTitle = html_entity_decode($itemTitle);
                    $itemTitle = str_replace("'","",$itemTitle);
                    $itemTitle = str_replace("\"","",$itemTitle);
                    $itemTitle = preg_replace("/<span[^>]+>/","",$itemTitle);
                    $itemTitle = preg_replace("/<\/span>/","",$itemTitle);
                    if(@$used[$row2["itemLink"]]==1) continue;
                    if(@$used[$itemTitle]==1) continue;
                    $used[$row2["itemLink"]] = 1;
                    $used[$itemTitle] = 1;
                    $row2["itemLink"] = "http://names.ubio.org/tools/redirect.php?url=".base64_encode($row2["itemLink"]);
                    if(strlen($itemTitle)>75) $itemTitle = substr($itemTitle,0,75)."</em>...";
                    $toReturn.="menu".$this->num_menus."[".$numR."]='<a href=\"".$row2["itemLink"]."\" target=new class=uBioRSSfeed><font color=black><small>($date)</small>&nbsp;&nbsp;$title</font><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color=gray><i>$itemTitle</i></font>'\n";
                    $numR++;
                    if(strlen($itemTitle)>$maxLength) $maxLength = strlen($itemTitle);
                    if((strlen($title)+13)>$maxLength) $maxLength = (strlen($title)+13);
                    $i++;
                    if($i==10) break;
                }
            }
            if($this->link_type==2 && preg_match("/http/",$toReturn))
            {
                $toReturn.="\n//--></script>\n
                    <b>\\1</b> <a href='' onClick=\"return clickreturnvalue()\" onMouseover=\"dropdownmenu(this, event, menu".$this->num_menus.", '".(($maxLength*8.5)+166)."')\" onMouseout=\"delayhidemenu()\"><img src=http://names.ubio.org/tools/ubio_dots.gif border=0></a>\n";
                $this->num_menus++;
                //echo "preg_replace($search_string,$toReturn.$syn.$link,$text_before);<br>";
                return $anchor.preg_replace($search_string,$toReturn.$syn.$link,$text_before);
            }
        }elseif($this->map=="all")
        {
            $i=1;
            $numR=0;
            $maxLength=0;
            unset($used);
            if($this->link_type==2)
            {
                $toReturn = "<script type=\"text/JavaScript\"><!--\n
                        var menu".$this->num_menus."=new Array()\n";
            }
            else $toReturn = $img."[";
            
            
            $names = array();
            $count = array();
            $used = array();
            $returnItems = array();
            $namebankIDstring = "";
            $result = $this->mysqli->query("SELECT namebankID,nameString FROM names JOIN canonicalForms USING (canonicalFormID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' ORDER BY CHAR_LENGTH(nameString) DESC");
            while($result && $row=$result->fetch_assoc())
            {
                $names[$row["nameString"]] = $row["namebankID"];
                $namebankIDstring .= $row["namebankID"].",";
                $result2 = $this->mysqli->query("SELECT mappings.collectionsID,foreignKey,collectionsURL,linkit_logo FROM mappings JOIN weblinks USING (collectionsID) WHERE namebankID=".$row["namebankID"]." AND mappings.collectionsID!=10 AND mappings.collectionsID!=29 ORDER BY mappings.collectionsID ASC");
                while($result2 && $row2=$result2->fetch_assoc())
                {
                    @$count[$row2["collectionsID"]]++;
                    if($count[$row2["collectionsID"]]>=4) continue;
                    if(preg_match("/FOREIGNKEY/",$row2["collectionsURL"]))
                    {
                        $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("FOREIGNKEY",$row2["foreignKey"],$row2["collectionsURL"]));
                    }elseif(preg_match("/GENUS/",$row2["collectionsURL"]))
                    {
                        $words = explode(" ",$canon);
                        $url = $row2["collectionsURL"];
                        $url = str_replace("GENUS",$words[0],$url);
                        if(@$words[1]) $url = str_replace("SPECIES",$words[1],$url);
                        $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode($url);
                    }elseif(preg_match("/NAMESTRING/",$row2["collectionsURL"]))
                    {
                        $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("NAMESTRING",urlencode($canon),$row2["collectionsURL"]));
                    }elseif(preg_match("/CANONICAL/",$row2["collectionsURL"]))
                    {
                        $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("CANONICAL",urlencode($canon),$row2["collectionsURL"]));
                    }
                    if(@$used[$url]) continue;
                    $used[$url]=true;
                    $abrev = $this->get_abreviation($row2["collectionsID"]);
                    if($this->link_type==2)
                    {
                        $row["nameString"] = str_replace("'","",$row["nameString"]);
                        $returnItems[$row2["collectionsID"]][] = "'<a href=\"$url\" target=new><img src=http://names.ubio.org/tools/image/".$row2["linkit_logo"]." border=0> ".$row["nameString"]."</a>'\n";
                    }
                    else $returnItems[$row2["collectionsID"]][] = "<a href=$url target=new>$abrev</a>";
                    if(strlen($row["nameString"])>$maxLength) $maxLength = strlen($row["nameString"]);
                }
            }
            
            
            
            
            uksort($names,"sort_by_name_length");
            if(list($key,$val)=each($names))
            {
                if($this->link_type==2)
                {
                    $key = str_replace("'","",$key);
                    $toReturn.="menu".$this->num_menus."[".$numR."]='<a href=\"http://names.ubio.org/browser/details.php?namebankID=$val\" target=new><img src=http://names.ubio.org/tools/image/ubio.gif border=0> $key</a>'\n";
                    $numR++;
                }else $toReturn.="<a href=http://names.ubio.org/browser/details.php?namebankID=$val target=new>nb</a>|";
                if(strlen($key)>$maxLength) $maxLength = strlen($key);
            }
            
            
            ksort($returnItems);
            while(list($key,$val)=each($returnItems))
            {
                while(list($key2,$val2)=each($val))
                {
                    if($this->link_type==2)
                    {
                        $toReturn.="menu".$this->num_menus."[".$numR."]=$val2";
                        $numR++;
                    }
                    else $toReturn.=$val2;
                }
            }
            
            

//            $result = $this->mysqli->query("SELECT mappings.collectionsID,foreignKey,collectionsURL,nameString,linkit_logo FROM names JOIN canonicalForms USING (canonicalFormID) JOIN mappings ON names.namebankID=mappings.namebankID JOIN weblinks USING (collectionsID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' AND mappings.collectionsID!=10 AND mappings.collectionsID!=29 ORDER BY mappings.collectionsID ASC");
//            if($this->resolve=="lex" && (@!$result || $result->num_rows==0)) $result = $this->mysqli->query("SELECT mappings.collectionsID,foreignKey,collectionsURL,nB.nameString,linkit_logo FROM names as nA JOIN canonicalForms ON nA.canonicalFormID=canonicalForms.canonicalFormID LEFT JOIN names as nB ON nA.lexicalUnit=nB.lexicalUnit JOIN mappings ON nB.namebankID=mappings.namebankID JOIN weblinks USING (collectionsID) WHERE nA.lexicalUnit!=0 AND canonicalForm='".$this->mysqli->real_escape_string($canon)."' AND mappings.collectionsID!=10 AND mappings.collectionsID!=29 ORDER BY mappings.collectionsID ASC");
//            $count = array();
//            while($result && $row=$result->fetch_assoc())
//            {
//                @$count[$row["collectionsID"]]++;
//                if($count[$row["collectionsID"]]>=4) continue;
//                if(preg_match("/FOREIGNKEY/",$row["collectionsURL"]))
//                {
//                    $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("FOREIGNKEY",$row["foreignKey"],$row["collectionsURL"]));
//                }elseif(preg_match("/GENUS/",$row["collectionsURL"]))
//                {
//                    $words = explode(" ",$canon);
//                    $url = $row["collectionsURL"];
//                    $url = str_replace("GENUS",$words[0],$url);
//                    if(@$words[1]) $url = str_replace("SPECIES",$words[1],$url);
//                    $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode($url);
//                }elseif(preg_match("/NAMESTRING/",$row["collectionsURL"]))
//                {
//                    $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("NAMESTRING",urlencode($canon),$row["collectionsURL"]));
//                }elseif(preg_match("/CANONICAL/",$row["collectionsURL"]))
//                {
//                    $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("CANONICAL",urlencode($canon),$row["collectionsURL"]));
//                }
//                if(@$used[$url]) continue;
//                $used[$url]=true;
//                $abrev = $this->get_abreviation($i);
//                if($this->link_type==2)
//                {
//                    $row["nameString"] = str_replace("'","",$row["nameString"]);
//                    $toReturn.="menu".$this->num_menus."[".$numR."]='<a href=\"$url\" target=new><img src=http://names.ubio.org/tools/image/".$row["linkit_logo"]." border=0> ".$row["nameString"]."</a>'\n";
//                    $numR++;
//                }
//                else $toReturn.="<a href=$url target=new>$abrev</a>|";
//                if(strlen($row["nameString"])>$maxLength) $maxLength = strlen($row["nameString"]);
//            }
//                
//                
//            $result = $this->mysqli->query("SELECT namebankID,nameString FROM names JOIN canonicalForms USING (canonicalFormID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' ORDER BY CHAR_LENGTH(nameString) DESC");
//            if($result && $row=$result->fetch_assoc())
//            {
//                if($this->link_type==2)
//                {
//                    $row["nameString"] = str_replace("'","",$row["nameString"]);
//                    $toReturn.="menu".$this->num_menus."[".$numR."]='<a href=\"http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]."\" target=new><img src=http://names.ubio.org/tools/image/ubio.gif border=0> ".$row["nameString"]."</a>'\n";
//                    $numR++;
//                }else $toReturn.="<a href=http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]." target=new>nb</a>|";
//                if(strlen($row["nameString"])>$maxLength) $maxLength = strlen($row["nameString"]);
//            }
            if($this->link_type==2 && preg_match("/http/",$toReturn))
            {
                $toReturn.="\n//--></script>\n
                    <b>\\1</b> <a href='' onClick=\"return clickreturnvalue()\" onMouseover=\"dropdownmenu(this, event, menu".$this->num_menus.", '".(($maxLength*9)+46)."')\" onMouseout=\"delayhidemenu()\"><img src=http://names.ubio.org/tools/ubio_dots.gif border=0></a>\n";
                $this->num_menus++;
                return $anchor.preg_replace($search_string,$toReturn.$syn.$link,$text_before);
            }
            elseif($this->link_type!=2 && $toReturn != $img."[")
            {
                $toReturn = substr($toReturn,0,-1)."]";
                return $anchor.preg_replace($search_string,"<b>\\1</b>".$syn."$link".$toReturn,$text_before);
            }
        }elseif(preg_match("/ OR /",$this->collectionsID))
        {
            $numR=0;
            $maxLength=0;
            unset($used);
            if($this->link_type==2)
            {
                $toReturn = "<script type=\"text/JavaScript\"><!--\n
                        var menu".$this->num_menus."=new Array()\n";
            }
            else $toReturn = $img."[";
            $result = $this->mysqli->query("SELECT foreignKey,collectionsURL,mappings.collectionsID,linkit_logo,nameString FROM names JOIN canonicalForms USING (canonicalFormID) JOIN mappings ON names.namebankID=mappings.namebankID JOIN weblinks USING (collectionsID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' AND (mappings.collectionsID=$this->collectionsID) LIMIT 3");
            if($this->resolve=="lex" && (@!$result || $result->num_rows==0)) $result = $this->mysqli->query("SELECT foreignKey,collectionsURL,mappings.collectionsID,linkit_logo,nB.nameString FROM names as nA JOIN canonicalForms ON nA.canonicalFormID=canonicalForms.canonicalFormID LEFT JOIN names as nB ON nA.lexicalUnit=nB.lexicalUnit JOIN mappings ON nB.namebankID=mappings.namebankID JOIN weblinks USING (collectionsID) WHERE nA.lexicalUnit!=0 AND canonicalForm='".$this->mysqli->real_escape_string($canon)."' AND (mappings.collectionsID=$this->collectionsID) LIMIT 3");
            while($result && $row=$result->fetch_assoc())
            {
                if(preg_match("/FOREIGNKEY/",$row["collectionsURL"]))
                {
                    $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("FOREIGNKEY",$row["foreignKey"],$row["collectionsURL"]));
                }elseif(preg_match("/GENUS/i",$row["collectionsURL"]))
                {
                    $words = explode(" ",$canon);
                    $url = $row["collectionsURL"];
                    $url = str_replace("GENUS",$words[0],$url);
                    if(@$words[1]) $url = str_replace("SPECIES",$words[1],$url);
                    $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode($url);
                }elseif(preg_match("/NAMESTRING/",$row["collectionsURL"]))
                {
                    $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("NAMESTRING",urlencode($canon),$row["collectionsURL"]));
                }elseif(preg_match("/CANONICAL/",$row["collectionsURL"]))
                {
                    $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("CANONICAL",urlencode($canon),$row["collectionsURL"]));
                }
                if(@$used[$url]) continue;
                $used[$url]=true;
                $abrev = $this->get_abreviation($row["collectionsID"]);
                if($this->link_type==2)
                {
                    $row["nameString"] = str_replace("'","",$row["nameString"]);
                    $toReturn.="menu".$this->num_menus."[".$numR."]='<a href=\"$url\" target=new><img src=http://names.ubio.org/tools/image/".$row["linkit_logo"]." border=0> ".$row["nameString"]."</a>'\n";
                    $numR++;
                }
                else $toReturn.="<a href=$url target=new>$abrev</a>|";
                if(strlen($row["nameString"])>$maxLength) $maxLength = strlen($row["nameString"]);
            }
            if(preg_match("/9999/",$this->collectionsID))
            {
                $result = $this->mysqli->query("SELECT namebankID,nameString FROM names JOIN canonicalForms USING (canonicalFormID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' ORDER BY CHAR_LENGTH(nameString) DESC");
                if($result && $row=$result->fetch_assoc())
                {
                    if($this->link_type==2)
                    {
                        $row["nameString"] = str_replace("'","",$row["nameString"]);
                        $toReturn.="menu".$this->num_menus."[".$numR."]='<a href=\"http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]."\" target=new><img src=http://names.ubio.org/tools/image/ubio.gif border=0> ".$row["nameString"]."</a>'\n";
                        $numR++;
                    }else $toReturn.="<a href=http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]." target=new>nb</a>|";
                    if(strlen($row["nameString"])>$maxLength) $maxLength = strlen($row["nameString"]);
                }
            }
            if($this->link_type==2 && preg_match("/http/",$toReturn))
            {
                $url = str_replace("'","",$url);
                $toReturn.="\n//--></script>\n
                    <b>\\1</b> <a href='' onClick=\"return clickreturnvalue()\" onMouseover=\"dropdownmenu(this, event, menu".$this->num_menus.", '".(($maxLength*9)+46)."')\" onMouseout=\"delayhidemenu()\"><img src=http://names.ubio.org/tools/ubio_dots.gif border=0></a>\n";
                $this->num_menus++;
                return $anchor.preg_replace($search_string,$toReturn.$syn.$link,$text_before);
            }
            elseif($this->link_type!=2 && $toReturn != $img."[")
            {
                $toReturn = substr($toReturn,0,-1)."]";
                return $anchor.preg_replace($search_string,"<b>\\1</b>".$syn."$link".$toReturn,$text_before);
            }
        }else
        {
            $result = $this->mysqli->query("SELECT foreignKey,collectionsURL,linkit_logo,nameString FROM names JOIN canonicalForms USING (canonicalFormID) JOIN mappings ON names.namebankID=mappings.namebankID JOIN weblinks USING (collectionsID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' AND mappings.collectionsID=$this->collectionsID");
            if($this->resolve=="lex" && (@!$result || $result->num_rows==0)) $result = $this->mysqli->query("SELECT foreignKey,collectionsURL,mappings.collectionsID,linkit_logo,nB.nameString FROM names as nA JOIN canonicalForms ON nA.canonicalFormID=canonicalForms.canonicalFormID LEFT JOIN names as nB ON nA.lexicalUnit=nB.lexicalUnit JOIN mappings ON nB.namebankID=mappings.namebankID JOIN weblinks USING (collectionsID) WHERE nA.lexicalUnit!=0 AND canonicalForm='".$this->mysqli->real_escape_string($canon)."' AND mappings.collectionsID=$this->collectionsID");
            if((@!$result || $result->num_rows==0) && preg_match("/ /",$canon))
            {
                $array = explode(" ",$canon);
                $search_string = "/(".$array[0].")/";
                $canon = $array[0];
                $result = $this->mysqli->query("SELECT foreignKey,collectionsURL,linkit_logo,nameString FROM names JOIN canonicalForms USING (canonicalFormID) JOIN mappings ON names.namebankID=mappings.namebankID JOIN weblinks USING (collectionsID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' AND mappings.collectionsID=$this->collectionsID");
            }
            while($result && $row=$result->fetch_assoc())
            {
                if($this->link_type==1) return $anchor.preg_replace($search_string,"<a href=http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("FOREIGNKEY",$row["foreignKey"],$row["collectionsURL"]))." target=new>\\1</a>".$syn.$link.$img,$text_before);
                elseif($this->link_type==2) 
                {
                    $row["nameString"] = str_replace("'","",$row["nameString"]);
                    $string = "<script type=\"text/JavaScript\"><!--\n
                        var menu".$this->num_menus."=new Array()\n
                        menu".$this->num_menus."[0]='<a href=\"http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("FOREIGNKEY",$row["foreignKey"],$row["collectionsURL"]))."\" target=new><img src=http://names.ubio.org/tools/image/".$row["linkit_logo"]." border=0> ".$row["nameString"]."</a>'\n
                        //--></script>\n
                        <b>\\1</b> <a href='' onClick=\"return clickreturnvalue()\" onMouseover=\"dropdownmenu(this, event, menu".$this->num_menus.", '".((strlen($row["nameString"])*9)+46)."')\" onMouseout=\"delayhidemenu()\"><img src=http://names.ubio.org/tools/ubio_dots.gif border=0></a>\n";
                    $this->num_menus++;
                    return $anchor.preg_replace($search_string,$string.$syn.$link,$text_before);
                }
                else $url = "http://names.ubio.org/tools/redirect.php?url=".base64_encode(str_replace("FOREIGNKEY",$row["foreignKey"],$row["collectionsURL"]));
                $abrev = $this->get_abreviation($this->collectionsID);
                return $anchor.preg_replace($search_string,"<b>\\1</b>".$syn."$link".$img."[<a href=$url target=new>$abrev</a>]",$text_before);
            }
        }
        
        if($this->map!="all"&&!$this->nb)
        {
            $result = $this->mysqli->query("SELECT namebankID,nameString FROM names JOIN canonicalForms USING (canonicalFormID) WHERE canonicalForm='".$this->mysqli->real_escape_string($canon)."' ORDER BY CHAR_LENGTH(nameString) DESC");
            if($result && $row=$result->fetch_assoc())
            {
                if($this->link_type==1) return $anchor.preg_replace($search_string,"<a href=http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]." target=new>\\1</a>".$syn.$link.$img,$text_before);
                elseif($this->link_type==2) 
                {
                    $row["nameString"] = str_replace("'","",$row["nameString"]);
                    $string = "<script type=\"text/JavaScript\"><!--\n
                        var menu".$this->num_menus."=new Array()\n
                        menu".$this->num_menus."[0]='<a href=\"http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]."\" target=new><img src=http://names.ubio.org/tools/image/ubio.gif border=0>".$row["nameString"]."</a>'\n
                        //--></script>\n
                        <b>\\1</b> <a href='' onClick=\"return clickreturnvalue()\" onMouseover=\"dropdownmenu(this, event, menu".$this->num_menus.", '".((strlen($row["nameString"])*9)+46)."')\" onMouseout=\"delayhidemenu()\"><img src=http://names.ubio.org/tools/ubio_dots.gif border=0></a>\n";
                    $this->num_menus++;
                    return $anchor.preg_replace($search_string,$string.$syn.$link,$text_before);
                }
                else return $anchor.preg_replace($search_string,"<b>\\1</b>".$syn.$link.$img."[<a href=http://names.ubio.org/browser/details.php?namebankID=".$row["namebankID"]." target=new>nb</a>]",$text_before);
            }
        }
        if($this->link_type==1) return $anchor.$text_before.$syn;
        else return $anchor.preg_replace($search_string,"<b>\\1</b>".$syn.$link,$text_before);
    }
}




function sort_by_name_length($a, $b)
{
   if (strlen($a) == strlen($b)) return 0;
   return (strlen($a) < strlen($b)) ? -1 : 1;
}




?>
