<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
		
<?php

$func = @$_GET["func"];
$string = @$_GET["string"];
$string2 = @$_GET["string2"];
$type = @$_GET["type"];
$ambig = @$_GET["ambig"];


if(($string || $string2) && $func)
{
    if($string2) $string = trim(strtolower($string2));
    else $string = strtolower(trim(base64_decode($string)));
    if($ambig) $func = "ambig";
    $string = str_replace("(","",$string);
    $string = str_replace(")","",$string);
    $string = str_replace("[","",$string);
    $string = str_replace("]","",$string);
    $string = str_replace(".","",$string);
    
    echo "$func - $string - $type - $ambig<br>";
    //exit;
    switch($func)
    {
        case "add":
            if(preg_match("/^[Gg4]$/",$type,$arr)) $dict = "/data/www/taxonfinder/wordLists/genera_new.txt";
            elseif(preg_match("/^[Ss3]$/",$type,$arr)) $dict = "/data/www/taxonfinder/wordLists/species_new.txt";
            elseif(preg_match("/^[Ff5]$/",$type,$arr)) $dict = "/data/www/taxonfinder/wordLists/family_new.txt";
            
            $dictionary = make_array_from_file($dict);
            if(!@$dictionary[$string])
            {
                $FILE = fopen($dict, "a");
                fwrite($FILE, "$string\n");
                fclose($FILE);
            }
            break;
        case "verify":
            $dictionary = make_array_from_file("/data/www/taxonfinder/wordLists/genera_new.txt");
            if(!@$dictionary[$string])
            {
                $FILE = fopen("/data/www/taxonfinder/wordLists/genera_new.txt", "a");
                fwrite($FILE, "$string\n");
                fclose($FILE);
            }
            
            $lines = file("/data/www/taxonfinder/wordLists/dict_ambig.txt");
            $FILE = fopen("/data/www/taxonfinder/wordLists/dict_ambig.txt", "w+");
        	while(list($key,$val)=each($lines))
        	{
        		$line = trim(strtolower($val));	
        		if($line!=$string) fwrite($FILE,"$line\n");
        	}
        	fclose($FILE);
        	unset($lines);
            break;
        case "genera_family":
            $dictionary = make_array_from_file("/data/www/taxonfinder/wordLists/genera_family.txt");
            if(!@$dictionary[$string])
            {
                $FILE = fopen("/data/www/taxonfinder/wordLists/genera_family.txt", "a");
                fwrite($FILE, "$string\n");
                fclose($FILE);
            }
            
            unset($dictionary);
            $dictionary = make_array_from_file("/data/www/taxonfinder/wordLists/family_new.txt");
            if(!@$dictionary[$string])
            {
                $FILE = fopen("/data/www/taxonfinder/wordLists/family_new.txt", "a");
                fwrite($FILE, "$string\n");
                fclose($FILE);
            }
            break;
        case "ambig":
            $dictionary = make_array_from_file("/data/www/taxonfinder/wordLists/dict_ambig.txt");
            if(!@$dictionary[$string])
            {
                $FILE = fopen("/data/www/taxonfinder/wordLists/dict_ambig.txt", "a");
                fwrite($FILE, "$string\n");
                fclose($FILE);
            }
            
            if(preg_match("/^[Gg4]$/",$type,$arr)) $dict = "/data/www/taxonfinder/wordLists/genera_new.txt";
            elseif(preg_match("/^[Ss3]$/",$type,$arr)) $dict = "/data/www/taxonfinder/wordLists/species_new.txt";
            elseif(preg_match("/^[Ff5]$/",$type,$arr)) $dict = "/data/www/taxonfinder/wordLists/family_new.txt";
            
            $dictionary = make_array_from_file($dict);
            if(!@$dictionary[$string])
            {
                $FILE = fopen($dict, "a");
                fwrite($FILE, "$string\n");
                fclose($FILE);
            }
            break;
        case "remove":
            $dictionary = make_array_from_file("/data/www/taxonfinder/wordLists/overlap_new.txt");
            if(!@$dictionary[$string])
            {
                $FILE = fopen("/data/www/taxonfinder/wordLists/overlap_new.txt", "a");
                fwrite($FILE, "$string\n");
                fclose($FILE);
            }
            
            if(preg_match("/^[Ss3]$/",$type,$arr))
            {
                unset($dictionary);
                $dictionary = make_array_from_file("/data/www/taxonfinder/wordLists/species_bad.txt");
                if(!@$dictionary[$string])
                {
                    $FILE = fopen("/data/www/taxonfinder/wordLists/species_bad.txt", "a");
                    fwrite($FILE, "$string\n");
                    fclose($FILE);
                }
            }
            break;
    }
    
    ?>
    <script language="JavaScript"><!--
        self.close();
    //--></script>
    <?php
    //self.close();
    exit;
}elseif($func)
{
    echo "<center><b>Add Name</b><hr size=1 width=75><form action=popup.php method=get>
            <input type=text name=string2 size=40><br><br>
            <select name=type size=1>
                <option value=G>Genus
                <option value=S>Species
                <option value=F>Family
            </select>  
            <input type=checkbox name=ambig value=1>Ambig
            <input type=hidden name=func value=add><br><br>
            <input type=Submit value=Submit>
        </form></center>";
}





function make_array_from_file($filepath)
{
    $lines = file($filepath);
	$array = array();
	$num = count($lines);
	for($i=0 ; $i<$num ; $i++)
	{
		$line = trim(strtolower($lines[$i]));	
		$array[$line] = 1;
		unset($lines[$i]);
	}
	unset($lines);
	return $array;
}


?>