<?php
require_once("../inc/xajax/xajax.inc.php");
require_once('../inc/jsonRPCClient.php');

/**
 * array of known functions
 * the key is the function name
 * the value is an array with first item beeing the number of neccessary parameters
 * and the second the number of possible parameters
 */
$knownFunctions = array('showMatchJsonRPC' => array(1, 1),
                        'dumpMatchJsonRPC' => array(1, 1));

$objResponse = new xajaxResponse();

function dispatcher()
{
    global $objResponse, $knownFunctions;

    if (func_num_args() > 0) {
        $funcName = func_get_arg(0);
        if (isset($knownFunctions[$funcName])) {
            $minParams = $knownFunctions[$funcName][0];
            $maxParams = $knownFunctions[$funcName][1];

            $params = array();
            for ($i = 0; $i < $maxParams && $i < func_num_args() - 1; $i++) {
                $params[] = func_get_arg($i + 1);
            }
            if (count($params) >= $minParams) {
                if (count($params) == 0) {
                    $funcName();
                } else {
                    call_user_func_array($funcName, $params);
                }
            }
        }
    }

    return $objResponse;
}


function showMatchJsonRPC($formData)
{
    global $objResponse;

    $start = microtime(true);

    $searchtext = ucfirst(trim($formData['searchtext']));
    if (substr($searchtext, 0, 3) == chr(0xef) . chr(0xbb) . chr(0xbf)) $searchtext = substr($searchtext, 3);

    $service = new jsonRPCClient('http://131.130.131.9/taxamatch/json_rpc_taxamatchMdld.php');
    try {
        $matches = $service->getMatches($searchtext);

        $stop = microtime(true);

        if ($matches['error']) {
            $out = $matches['error'];
        } else {
            $out = "";
            foreach ($matches['result'] as $result) {
                $out2 = '';
                $found = 0;
                $line = 0;
                foreach ($result['searchresult'] as $key => $row) {
                    foreach ($row['species'] as $key2 => $row2) {
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
                $out .= "<tr valign='baseline'>"
                      . "<td rowspan='$line'>"
                      . "&nbsp;&nbsp;<big><b>" . $result['searchtext'] . "</b></big>&nbsp;&nbsp;<br>\n"
                      . "&nbsp;&nbsp;$found match" . (($found > 1) ? 'es' : '') . " found&nbsp;&nbsp;<br>\n"
                      . "&nbsp;&nbsp;" . $result['rowsChecked'] . " rows checked&nbsp;&nbsp;"
                      . "</td>"
                      . $out2;
            }
            $out = "<big>" . number_format(($stop - $start), 2) . " seconds needed</big><br>\n"
                 . "<table rules='all' border='1'>\n"
                 . "<tr><th>&nbsp;search for&nbsp;</th><th>result</th><th>Dist.</th><th>Ratio</th></tr>\n"
                 . $out
                 . "</table>\n";
        }
    }
    catch (Exception $e) {
        $out =  "Fehler " . nl2br($e);
    }

    $objResponse->addAssign("ajaxTarget", "innerHTML", $out);
}

function dumpMatchJsonRPC($formData)
{
    global $objResponse;
    $searchtext = ucfirst(trim($formData['searchtext']));
    if (substr($searchtext, 0, 3) == chr(0xef) . chr(0xbb) . chr(0xbf)) $searchtext = substr($searchtext, 3);

    $service = new jsonRPCClient('http://131.130.131.9/taxamatch/json_rpc_taxamatchMdld.php');
    try {
        $matches = $service->getMatches($searchtext);

        $out = "<big><b>Dump or Results for search for '" . nl2br($searchtext) . "':</b></big><br>\n"
             . "<pre>" . var_export($matches, true) . "</pre>\n";
    }
    catch (Exception $e) {
        $out =  "Fehler " . nl2br($e);
    }

    $objResponse->addAssign("ajaxTarget", "innerHTML", $out);
}

/**
 * register all xajax-functions in this file
 */
$xajax = new xajax();
$xajax->registerFunction("dispatcher");
$xajax->processRequests();