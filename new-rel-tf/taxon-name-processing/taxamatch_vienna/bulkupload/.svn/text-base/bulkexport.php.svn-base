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

function formatCell($value) {

  if(!isset($value) || $value === '')
    $value = "\t";
  else {
    $value = str_replace('"', '""', $value); // escape quotes
    $value = '"'.$value.'"'."\t";
  }
  return $value;
}

$header = "\"search for\"\t\"result\"\t\"Dist.\"\t\"Ratio\"";

$result = db_query("SELECT * FROM tblqueries WHERE jobID = '$jobID' ORDER BY lineNr");
$displayOnlyParts = (!empty($_GET['short']) || mysql_num_rows($result) > 50) ? 1 : 0;

$out = "";
$correct = 0;
while ($row = mysql_fetch_array($result)) {
    $matches = unserialize($row['result']);
    if ($matches) {
        foreach ($matches['result'] as $match) {
            $out2 = '';
            $found = 0;
            $blocked = 0;
            foreach ($match['searchresult'] as $key => $row) {
                foreach ($row['species'] as $key2 => $row2) {
                    if ($displayOnlyParts && $row2['distance'] == 0) $blocked++;
                    if ($found > 0) {
                        $out2 .= formatCell('');
                    }
                    $out2 .= formatCell($row2['taxon'] . ' <' . $row2['taxonID'] . '>')
                           . formatCell($row2['distance'])
                           . formatCell(number_format($row2['ratio'] * 100, 1) . "%")
                           . "\n";
                    if ($row2['syn']) {
                        $out2 .= formatCell('')
                               . formatCell("  -> " . $row2['syn'] . " <" . $row2['synID'] . ">")
                               . "\n";
                    }
                    $found++;
                }
            }
            if (!$found) {
                $out2 = formatCell("nothing found") . formatCell('') . formatCell('') . "\n";
            }
            if (!$found || $found != $blocked) {
                $out .= formatCell($match['searchtext'])
                      . $out2;
            } else {
                $correct++;
            }
        }
    }
}
if ($correct > 0)  {
    $out .= formatCell("$correct queries had a full hit") . formatCell('') . formatCell('') . formatCell('') . "\n";
}

$out = str_replace("\r", "", $out); // embedded returns have "\r"

header("Content-type: application/octet-stream charset=utf-8");
header("Content-Disposition: attachment; filename=bulkexport.csv");
header("Pragma: no-cache");
header("Expires: 0");
echo chr(0xef) . chr(0xbb) . chr(0xbf) . $header . "\n" . $out;