<?php
session_start();
require_once ("inc/xajax/xajax.inc.php");

$xajax = new xajax("ajax/taxamatchMdldServer.php");
$xajax->registerFunction("dispatcher");

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
  <title>herbardb - taxamatch MDLD</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" type="text/css" href="css/screen.css">
  <?php $xajax->printJavascript('inc/xajax'); ?>
</head>

<body onload="document.f.searchtext.focus();">
<h1>Taxamatch MDLD</h1>
<p>
  <form Action="<?php echo $_SERVER['PHP_SELF']; ?>" Method="POST" name="f" id="f">
    <table>
      <tr>
        <td><textarea name="searchtext" style="width:50em; height:10em;"></textarea></td>
      </tr><tr>
        <td valign="top">
          <input type="submit" value="search" name="searchSpecies" onclick="xajax_dispatcher('showMatchJsonRPC', xajax.getFormValues('f')); return false;">
        </td>
      </tr>
    </table>
  </form>
</p>

<p>
<div id="ajaxTarget"></div>
</p>

<div style="color:lightgray; position:absolute; right:1px; bottom:1px;"><a href="#" onclick="xajax_dispatcher('dumpMatchJsonRPC', xajax.getFormValues('f')); return false;">&pi;</div>
</body>
</html>