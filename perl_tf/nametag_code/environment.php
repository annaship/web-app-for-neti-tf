<?php

/* Set your working local paths */
define("DOC_ROOT", "/Users/anthonygoddard/dev/TaxonFinder/nametag_code/");
define("WEB_ROOT","http://localhost/nametag_code/");


define("TAXONFINDER_SOCKET_SERVER", "127.0.0.1"); //load balancer
//if(rand(0,1)) define("TAXONFINDER_SOCKET_SERVER", "128.128.250.144"); //tf01
//else define("TAXONFINDER_SOCKET_SERVER", "128.128.250.145"); //tf02
define("TAXONFINDER_SOCKET_PORT",   "1234");
define("TAXONFINDER_STOP_KEYWORD",  "asdfib3r234");






function require_all_classes($dir)
{
    if($handle = opendir($dir))
    {
       while(false !== ($file = readdir($handle)))
       {
           if($file != "." && $file != "..")
           {
               if(preg_match("/\.php$/",trim($file))) require_once($dir.$file);
               elseif(!preg_match("/\./", $file) && $file != "modules") require_all_classes($dir.$file."/");
           }
       }
       closedir($handle);
    }
}

function require_module($module)
{
    $module_path = DOC_ROOT . "classes/modules/$module/module.php";
    require_once($module_path);
}

function get_remote_page($url)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,         // return web page
        CURLOPT_FOLLOWLOCATION => true,         // follow redirects
        CURLOPT_ENCODING       => "",           // handle all encodings
        CURLOPT_CONNECTTIMEOUT => 20,           // timeout on connect
        CURLOPT_TIMEOUT        => 20,           // timeout on response
        CURLOPT_MAXREDIRS      => 3,             // stop after 3 redirects
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1',
        CURLOPT_COOKIEJAR      => DOC_ROOT.'/tmp/namelink_cookiejar.txt',
        CURLOPT_COOKIEFILE     => DOC_ROOT.'/tmp/namelink_cookiefile.txt'
    );

    $ch      = curl_init($url);
    curl_setopt_array($ch,$options);
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch) ;
    $header  = curl_getinfo($ch);
    curl_close($ch);

  //  $header['errno']   = $err;
  //  $header['errmsg']  = $errmsg;
  //  $header['content'] = $content;
    return $content;
}

?>