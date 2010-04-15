<?php

$url = trim(@$_POST["url"]);
$url2 = trim(@$_POST["url2"]);
$map = @$_POST["map"];
$redirect = @$_POST["redirect"];
$no_logo = @$_POST["no_logo"];
$nb = @$_POST["nb"];
$link_type = @$_POST["link_type"];
$classify = @$_POST["classify"];
$synonyms = @$_POST["synonyms"];
$resolve = @$_POST["resolve"];


if(@!$url&&@!$url2)
{
    if(preg_match("/%255B/i",$_SERVER["REQUEST_URI"])) 
    {
        $theURL = urldecode($_SERVER["REQUEST_URI"]);
        $string = $theURL;
        $theMap=array();
        while(preg_match("/map%5B%5D=([^&]*)&(.*)$/i",$string,$arr))
        {
            $theMap[]=$arr[1];
            $string=$arr[2];
        }
    }
    else $theURL = $_SERVER["REQUEST_URI"];
    if(preg_match("/[&\?]url=(.*)$/",$theURL,$arr))
    {
        $url = $arr[1];
    }else $url = trim(@$_GET["url"]);
    if(@!$theMap) $map = @$_GET["map"];
    else $map=$theMap;
    $no_logo = @$_GET["no_logo"];
    $redirect = @$_GET["redirect"];
    $nb = @$_GET["nb"];
    $link_type = @$_GET["link_type"];
    $classify = @$_GET["classify"];
    $synonyms = @$_GET["synonyms"];
    if(@!$url&&@!$url2) include "http://www.ubio.org/templates/ubio_top.php";
}else
{
    if(@!$url&&@!$url2) include "http://www.ubio.org/templates/ubio_top.php";
    echo "<p align=center>http://names.ubio.org/tools/linkit.php?";
    if(!$map) echo "map%5B%5D=all&";
    else
    {
        while(list($key,$val)=each($map))
        {
            echo "map%5B%5D=$val&";
        }
    }
    if($redirect) echo "redirect=$redirect&";
    if($no_logo) echo "no_logo=$no_logo&";
    if($nb) echo "nb=$nb&";
    if($link_type) echo "link_type=$link_type&";
    if($classify) echo "classify=$classify&";
    if($synonyms) echo "synonyms=$synonyms&";
    if($resolve) echo "resolve=$resolve&";
    if($url) echo "url=$url<hr>";
    else echo "url=$url2<hr>";
}



include_once("/data/www/tools/function_new.php");
include_once("/data/www/taxonfinder/finder.php");

if(@!$url&&@!$url2)
{
    ?>
    <HTML>
    <HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <TITLE>uBio - LinkIT</TITLE>
    
    <link href="http://www.ubio.org/css_stylepages/ubio_2.css" rel="stylesheet" media="screen">
    
    </HEAD>
    
    
    <script language="JavaScript"><!--
        function popupWin(theString,theFunc)
        {
            newTarget('recognize_popup.php?string='+theString+'&func='+theFunc, 'top=50,left=300,width=500,height=300,resizable=0,scrollbars=0,status=0');
        }
        
        function newTarget (page, features, windowName) 
        {
            newwin=window.open(page, windowName, features);
            if (newwin.opener==null) newwin.opener=window;
            newwin.opener.name = "opener";
            newwin.focus();	
        }
    //--></script>
        
    <BODY>
    <?php
}






$ip = $_SERVER["REMOTE_ADDR"];

if(@!$url&&@!$url2)
{
	?>
		<table width="272" border="0" cellspacing="2" cellpadding="" align="center">
			<form action='linkit.php' METHOD='POST' ENCTYPE='multipart/form-data'>
			<tr>
				<td>
						<table width="468" height='350' border="0" cellspacing="2" cellpadding="0" align="center">
					<tr><td valign=center align=right>Enter URL:</td><td valign=center align=left><input type=text size=43 name=url></td></tr>
					<tr><td valign=center align=right>Example URLs:</td><td valign=center align=left>
						<select name=url2 size='1'>";
				<?php		
							$file = file("/data/www/tools/testpages_linkit.txt");
							sort($file);
							$num = count($file);
							echo "<option value='none'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- - Choose an example URL - -\n
								<option value='0'>\n";
							for($i=0 ; $i<$num ; $i++)
							{
								echo "<option value='".trim($file[$i])."'>".substr($file[$i],0,50)."\n";
							}
				?>		
	    				</select>
					</td></tr>
					<tr valign=center><td align=right>Map to:</td><td><select name=map[] size=5 multiple=1>
					    <option value=all>All
					    <option value=namebank>Namebank
					    <option value=nz>Nomenclator Zoologicus
					    <option value=micro>Micro*scope
					    <option value=custar>CU*STAR
					    <option value=ncbi>NCBI
					    <option value=pubmed>PubMed
					    <option value=itis>ITIS
					    <option value=sp2000>Species 2000
					    <option value=fungi>Index Fungorum
					    <option value=fish>Fishbase
					    <option value=algae>AlgaeBase
					    <option value=tree>TreeBase
					    <option value=tol>Tree of Life
					    <option value=trop>Tropicos
					    <option value=iucn>IUCN
					    <option value=erms>ERMS
					    <option value=morph>Morphbank
					    <option value=fe>Fauna Europaea
					    <option value=lr>Landcare Research
					    <option value=pub>uBio RSS
					    <option value=a>None
					</select> <input type=checkbox name=nb value=1>only</td></tr>
					<tr><td colspan=2><hr></td></tr>
					<tr><td align=right>Resolve Variants</td><td><input type=radio name=resolve value=0 checked>none <input type=radio name=resolve value=lex>lexical</td></tr>
					<tr height=25 valign=top><td></td><td>&nbsp;&nbsp;&nbsp;<i><font color=gray>lexical or nomenclatural variants will also be linked</font></i></td></tr>
					<tr><td align=right>uBio Logo</td><td><input type=radio name=no_logo value=0 checked>yes <input type=radio name=no_logo value=1>no</td></tr>
					<tr height=25 valign=top><td></td><td>&nbsp;&nbsp;&nbsp;<i><font color=gray>should our logo appear after each linked name</font></i></td></tr>
					<tr><td align=right>Link Type</td><td><input type=radio name=link_type value=2 checked>drop down menu <input type=radio name=link_type value=0>after name <input type=radio name=link_type value=1>over name</td></tr>
					<tr height=25 valign=top><td></td><td>&nbsp;&nbsp;&nbsp;<i><font color=gray>where the LinkIT hyperlinks will be placed</font></i></td></tr>
					<tr><td align=right>Replace all Links</td><td><input type=radio name=redirect value=0 checked>no <input type=radio name=redirect value=1>yes</td></tr>
					<tr height=25 valign=top><td></td><td>&nbsp;&nbsp;&nbsp;<i><font color=gray>selecting 'yes' will redirect all links back to the LinkIT tool</font></i></td></tr>
					<tr><td align=right>Classify Results</td><td><input type=radio name=classify value=0 checked>no <input type=radio name=classify value=1>yes</td></tr>
					<tr height=25 valign=top><td></td><td>&nbsp;&nbsp;&nbsp;<i><font color=gray>according to the latest <a href='http://annual.sp2000.org/2005/info_about_col.php' target='new'>Catalog of Life</a> classification</font></i></td></tr>
					<tr><td align=right>Add Vernaculars</td><td><select name=synonyms size=1>
					    <option value=0>&nbsp;&nbsp;- - Choose a Language - -&nbsp;
					    <option value=0>
					    <option value='eng'>English
					    <option value='fre'>French
					    <option value='spa'>Spanish
					    <option value='ger'>German
					    <option value='jpn'>Japanese
					    <option value='por'>Portuguese
					    <option value='rus'>Russian
					</select></td></tr>
					<tr valign=center align=center><td colspan=2><input type=submit value='Submit'></td></tr>
				</table>
				</td>
				</form>
			</tr>
		</table>
		<?php
}

if(@$url||@$url2)
{	
    if(!$map) $map="all";
    else
    {
        @reset($map);
        $newmap="";
        while(list($key,$val)=@each($map))
        {
            if($val=="all") 
            {
                $newmap="all|";
                break;
            }
            if($val=="pub") 
            {
                $newmap="pub|";
                break;
            }
            $newmap.="$val|";
        }
        $map = substr($newmap,0,-1);
    }
    
    if($map=="all" && $link_type==1) $link_type=0;
    if($map=="pub" && $link_type==1) $link_type=0;
    if($map=="pub") $nb=1;
	if($url || ($url2&&$url2!="none"))
    {
        if($url) 
        {
            //echo "<p align='center'><b>Reading <a href=$url target='new'>$url</a></b></p>";
            //flush();
            $function = new linkit($map,$no_logo,$redirect,$nb,$link_type,$classify,$synonyms,0,$resolve);
            $function->get_names_from_url($url);
        }else 
        {
            //echo "<p align='center'><b>Reading <a href=$url2 target='new'>$url2</a></b></p>";
            //flush();
            $function = new linkit($map,$no_logo,$redirect,$nb,$link_type,$classify,$synonyms,0,$resolve);
            $function->get_names_from_url($url2);
        }
	}else
    {
    	echo "<h3 align=center>No text to search</h3>";
    }
    
    exit;
}


?>


</BODY>
</HTML>

<? if(@!$url&&@!$url2) include "http://www.ubio.org/templates/ubio_bottom.php"; ?>