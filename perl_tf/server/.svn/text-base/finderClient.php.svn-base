<?php


$thisURL = urldecode($_SERVER["REQUEST_URI"]);
if(preg_match("/url=(.*)$/",$thisURL,$arr))
{
    include_once("/data/www/taxonfinder/finder.php");
    include_once("/data/www/tools/function_new.php");
    
    $finder = new taxonFinder();
    
    $names = $finder->get_names(file_get_contents($arr[1]).". .","html");
    ksort($names);
    while(list($key,$val)=each($names))
    {
        echo "$key\t$val\n";
    }
}


?>
