<?php
session_name('herbarium_wu_taxamatch');
session_start();

include('inc/variables.php');
include('inc/connect.php');

if (empty($_SESSION['uid'])) die();

$result = db_query("SELECT * FROM tbljobs WHERE jobID = '" . intval($_GET['id']) . "' AND uid = '" . $_SESSION['uid'] . "'");
if (mysql_num_rows($result) == 0) die();
$row = mysql_fetch_array($result);
$jobID = $row['jobID'];

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
  <title>taxamatch - bulk upload</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" type="text/css" href="css/screen.css">
  <script type="text/javascript" language="JavaScript">
    function exportCsv(id, displayOnlyParts) {
      window.open("bulkexport.php?id=" + id + "&short=" + displayOnlyParts, "bulkexport", "width=100, height=100, top=10, left=10");
      return false;
    }
  </script>
</head>

<body>

<?php
echo "<h1>" . $row['filename'] . "</h1>\n"
   . "starttime: " . $row['start'] . "<br>\n"
   . "endtime: " . $row['finish'] . "\n"
   . "<p>\n";


$result = db_query("SELECT * FROM tblqueries WHERE jobID = '$jobID' ORDER BY lineNr");

$displayOnlyParts = (!empty($_POST['short']) || (empty($_POST['long']) && mysql_num_rows($result) > 50)) ? 1 : 0;

echo "<form Action='" . $_SERVER['PHP_SELF'] . "?id=$jobID' Method='POST' name='f'>\n";
if ($displayOnlyParts) {
    echo "<input type='submit' value='display everything' name='long'>"
       . "&nbsp;&nbsp;"
       . "<input type='submit' value='export csv' name='export' onclick='return exportCsv(\"$jobID\", \"$displayOnlyParts\")'>";
} else {
    echo "<input type='submit' value='display only < 100%' name='short'>"
       . "&nbsp;&nbsp;"
       . "<input type='submit' value='export csv' name='export' onclick='return exportCsv(\"$jobID\", \"$displayOnlyParts\")'>";
}
echo "</form><p>\n";

$out = "";
$correct = 0;
$nr = 1;
while ($row = mysql_fetch_array($result)) {
    $matches = unserialize($row['result']);
    if ($matches) {
        foreach ($matches['result'] as $match) {
            $out2 = '';
            $found = 0;
            $line = 0;
            $blocked = 0;
            foreach ($match['searchresult'] as $key => $row) {
                foreach ($row['species'] as $key2 => $row2) {
                    if ($displayOnlyParts && $row2['distance'] == 0) $blocked++;
                    if ($found > 0) {
                        $out2 .= "<tr valign='baseline'>";
                    }
                    $out2 .= '<td>&nbsp;&nbsp;<b>' . $row2['taxon'] . ' <' . $row2['taxonID'] . '></b></td>'
                           . '<td>&nbsp;' . $row2['distance'] . '&nbsp;</td>'
                           . '<td align="right">&nbsp;' . number_format($row2['ratio'] * 100, 1) . "%</td></tr>\n";
                    if ($row2['syn']) {
                        $out2 .= "<tr><td>&nbsp;&nbsp;&rarr;&nbsp;" . $row2['syn'] . " <" . $row2['synID'] . "></td><td colspan='2'></td></tr>\n";
                        $line++;
                    }
                    $found++;
                    $line++;
                }
            }
            if (!$found) {
                $out2 = "<td colspan='3'>nothing found</td></tr>\n";
                $line++;
            }
            if (!$found || $found != $blocked) {
                $out .= "<tr valign='baseline'>"
                      . "<td rowspan='$line' align='right'>" . $nr++ . "</td>"
                      . "<td rowspan='$line'>"
                      . "&nbsp;&nbsp;<big><b>" . $match['searchtext'] . "</b></big>&nbsp;&nbsp;<br>\n"
                      . "&nbsp;&nbsp;$found match" . (($found > 1) ? 'es' : '') . " found&nbsp;&nbsp;<br>\n"
                      . "&nbsp;&nbsp;" . $match['rowsChecked'] . " rows checked&nbsp;&nbsp;"
                      . "</td>"
                      . $out2;
            } else {
                $correct++;
            }
        }
    }
}
echo "<table rules='all' border='1'>\n"
   . "<tr><th></th><th>&nbsp;search for&nbsp;</th><th>result</th><th>Dist.</th><th>Ratio</th></tr>\n"
   . $out;
if ($correct > 0) echo "<tr><td colspan='5'>&nbsp;&nbsp;$correct queries had a full hit</td></tr>\n";
echo "</table>\n";
?>

</body>
</html>