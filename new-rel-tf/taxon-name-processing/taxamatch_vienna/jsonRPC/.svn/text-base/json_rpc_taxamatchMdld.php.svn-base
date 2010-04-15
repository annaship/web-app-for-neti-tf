<?php
/**
 * Biological Namestring parser and comparison tool based on
 * TAXAMATCH developed by Tony Rees, November 2008 (Tony.Rees@csiro.au)
 *
 * establishes a class with the public function "getMatches" -
 * subsequently calculates the distance based on the MDLD algorithm implemented
 * as an UDF in a MYSQL environment.
 *
 * Input Namestrings seperated by LF are compared against a defined reference Nameslist
 * and results are provided in an array of matches.
 *
 * A set of private functions is provided that parse and atomize the Namestrings into
 * single elements which are used for comparison against the reference list.
 *
 * @author Johannes Schachner <joschach@ap4net.at>
 * @since 21.09.2009
 */

/**
 * this is a json RPC server established using the jsonrpcphp project
 * (http://jsonrpcphp.org/)
 *
 * it returns an array with all search results and any occurred errors
 * format of the array is as follows:
 * array (
 * 'error' => ''                                       string containing all errors
 * 'result' => array (                                 array of all results
 *   0 => array (                                      first search
 *     'searchtext' => '',                             given taxon to search for
 *     'rowsChecked' => 0,                             how many rows were checked to find the results
 *     'searchresult' => array (                       here come the results
 *       0 => array (                                  first genus we've found
 *         'genus' => '',                              taxon of the genus
 *         'distance' => 0,                            distance of the genus
 *         'ratio' => 1,                               ratio of the genus (0...1)
 *         'text' => '',                               full taxon of the genus (incl. family)
 *         'genusID' => '',                            ID of the genus
 *         'species' => array (                        which species have we found
 *           0 => array (                              first found species
 *             'name' => '',                           name of the species
 *             'distance' => 0,                        distance of the species
 *             'ratio' => 1,                           ratio of the species (0...1)
 *             'taxon' => '',                          full taxon of the species
 *             'taxonID' => '',                        ID of the species
 *             'syn' => '',                            taxon of the synonym (if any)
 *             'synID' => 0,                           ID of the synonym (if any)
 *           ),
 *           1 => array (                              second species
 *           .
 *           .
 *           .
 *           ),
 *       ),
 *       1 => array (                                  second genus
 *       .
 *       .
 *       .
 *       ),
 *     ),
 *   ),
 *   1 => array (                                      second search
 *   .
 *   .
 *   .
 *   ),
 * ),
 * )
 */
require_once('inc/jsonRPCServer.php');
require_once('inc/variables.php');
/**
 * taxamatchMdld service class
 *
 * @package taxamatchMdldService
 * @subpackage classes
 */
class taxamatchMdldService
{

/*******************\
|                   |
|  public functions |
|                   |
\*******************/

/**
 * get all possible matches
 *
 * @param String $searchtext taxon string(s) to search for
 * @return array result of all searches
 */
public function getMatches($searchtext)
{
    global $options;

    // catch all output to the console
    ob_start();

    // base definition of the return array
    $matches = array('error'       => '',
                     'result'      => array());

    if (!@mysql_connect($options['dbhost'], $options['dbuser'], $options['dbpass']) || !@mysql_select_db($options['dbname'])) {
        $matches['error'] = 'no database connection';
        return $matches;
    }
    mysql_query("SET character set utf8");

    // split the input at newlines into several queries
    $searchItems = preg_split("[\n|\r]", $searchtext, -1, PREG_SPLIT_NO_EMPTY);

    foreach ($searchItems as $searchItem) {
        $searchresult = array();
        $fullHit = false;

        // parse the taxon string
        $parts = $this->_tokenizeTaxa($searchItem);

        // distribute the parsed string to different variables and calculate the (real) length
        $genus[0]    = ucfirst($parts['genus']);
        $lenGenus[0] = mb_strlen($parts['genus'], "UTF-8");
        $genus[1]    = ucfirst($parts['subgenus']);              // subgenus (if any)
        $lenGenus[1] = mb_strlen($parts['subgenus'], "UTF-8");   // real length of subgenus
        $epithet     = $parts['epithet'];
        $lenEpithet  = mb_strlen($parts['epithet'], "UTF-8");
        $rank        = $parts['rank'];
        $epithet2    = $parts['subepithet'];
        $lenEpithet2 = mb_strlen($parts['subepithet'], "UTF-8");

        /**
         * first do the search for the genus and subgenus
         * to speed things up we chekc first if there is a full hit
         * (it may not be very likely but the penalty is quite low)
         */
        $lev = array();
        $ctr = 0;  // how many checks did we do
        for ($i = 0; $i < 2; $i++) {
            // first let's see if there is a full hit of the searched genus or subgenus
            $res = mysql_query("SELECT g.genus, f.family, genID, a.author
                                FROM tbl_tax_genera g, tbl_tax_families f, tbl_tax_authors a
                                WHERE g.genus = '" . mysql_real_escape_string($genus[$i]) . "'
                                 AND g.familyID = f.familyID
                                 AND g.authorID = a.authorID");
            if (mysql_num_rows($res) > 0) {
                $row = mysql_fetch_array($res);
                $lev[] = array('genus'    => $row['genus'],
                               'distance' => 0,
                               'ratio'    => 1,
                               'taxon'    => $row['genus'] . ' ' . $row['author'] . ' (' . $row['family'] . ')',
                               'genusID'  => $row['genID']);
                $ctr++;
            } else {
                // no full hit, so do just the normal search
                $res = mysql_query("SELECT g.genus, f.family, genID, a.author,
                                     mdld('" . mysql_real_escape_string($genus[$i]) . "', g.genus, 2, 4) AS mdld
                                    FROM tbl_tax_genera g, tbl_tax_families f, tbl_tax_authors a
                                    WHERE g.familyID = f.familyID
                                     AND g.authorID = a.authorID");

                /**
                 * do the actual calculation of the distances
                 * and decide if the result should be kept
                 */
                while ($row = mysql_fetch_array($res)) {
                    $limit = min($lenGenus[$i], strlen($row['genus'])) / 2;     // 1st limit of the search
                    if ($row['mdld'] <= 3 && $row['mdld'] < $limit) {           // 2nd limit of the search
                        $lev[] = array('genus'    => $row['genus'],
                                       'distance' => $row['mdld'],
                                       'ratio'    => 1 - $row['mdld'] / max(mb_strlen($row['genus'], "UTF-8"), $lenGenus[$i]),
                                       'taxon'    => $row['genus'] . ' ' . $row['author'] . ' (' . $row['family'] . ')',
                                       'genusID'  => $row['genID']);
                    }
                    $ctr++;
                }
            }
            if (empty($genus[1])) break;    // no subgenus, we're finished here
        }

        // if there's more than one hit, sort them (faster here than within the db)
        if (count($lev) > 1) {
            foreach ($lev as $key => $row) {
                $sort1[$key] = $row['distance'];
                $sort2[$key] = $row['ratio'];
                $sort3[$key] = $row['genus'];
            }
            array_multisort($sort1, SORT_NUMERIC, $sort2, SORT_DESC, SORT_NUMERIC, $sort3, $lev);
        }


        /**
         * second do the search for the species and supspecies (if any)
         * if neither species nor subspecies are given, all species are returned
         * only genera which passed the first test will be used here
         */
        foreach ($lev as $key => $val) {
            $lev2 = array();
            $sql = "SELECT ts.synID, ts.taxonID,
                     te0.epithet epithet0, te1.epithet epithet1, te2.epithet epithet2,
                     te3.epithet epithet3, te4.epithet epithet4, te5.epithet epithet5,
                     ta0.author author0, ta1.author author1, ta2.author author2,
                     ta3.author author3, ta4.author author4, ta5.author author5";
            if ($epithet) {  // if an epithet was given, use it
                $sql .= ", mdld('" . mysql_real_escape_string($epithet) . "', te0.epithet, 4, 5)  as mdld";
                if ($epithet2 && $rank) {  // if a subepithet was given, use it
                    $sql .= ", mdld('" . mysql_real_escape_string($epithet2) . "', te{$rank}.epithet, 4, 5) as mdld2";
                }
            }
            $sql .= " FROM tbl_tax_species ts
                       LEFT JOIN tbl_tax_epithets te0 ON te0.epithetID = ts.speciesID
                       LEFT JOIN tbl_tax_epithets te1 ON te1.epithetID = ts.subspeciesID
                       LEFT JOIN tbl_tax_epithets te2 ON te2.epithetID = ts.varietyID
                       LEFT JOIN tbl_tax_epithets te3 ON te3.epithetID = ts.subvarietyID
                       LEFT JOIN tbl_tax_epithets te4 ON te4.epithetID = ts.formaID
                       LEFT JOIN tbl_tax_epithets te5 ON te5.epithetID = ts.subformaID
                       LEFT JOIN tbl_tax_authors ta0 ON ta0.authorID = ts.authorID
                       LEFT JOIN tbl_tax_authors ta1 ON ta1.authorID = ts.subspecies_authorID
                       LEFT JOIN tbl_tax_authors ta2 ON ta2.authorID = ts.variety_authorID
                       LEFT JOIN tbl_tax_authors ta3 ON ta3.authorID = ts.subvariety_authorID
                       LEFT JOIN tbl_tax_authors ta4 ON ta4.authorID = ts.forma_authorID
                       LEFT JOIN tbl_tax_authors ta5 ON ta5.authorID = ts.subforma_authorID
                      WHERE ts.genID = '" . $val['genusID'] . "'";
            $res = mysql_query($sql);
            while ($row = mysql_fetch_array($res)) {
                $name = trim($row['epithet0']);
                $found = false;
                if ($epithet) {
                    $distance = $row['mdld'];
                    $limit = min($lenEpithet, mb_strlen($row['epithet0'], "UTF-8")) / 2;                  // 1st limit of the search
                    if (($distance + $val['distance']) <= 4 && $distance <= 4 && $distance <= $limit) {   // 2nd limit of the search
                        if ($epithet2 && $rank) {
                            $limit2 = min($lenEpithet2, mb_strlen($row['epithet' . $rank], "UTF-8")) / 2; // 3rd limit of the search
                            if ($row['mdld2'] <= 4 && $row['mdld2'] <= $limit2) {                         // 4th limit of the search
                                $found = true;  // we've hit something
                                $ratio = 1
                                       - $distance / max(mb_strlen($row['epithet0'], "UTF-8"), $lenEpithet)
                                       - $row['mdld2'] / max(mb_strlen($row['epithet' . $rank], "UTF-8"), $lenEpithet2);
                                $distance += $row['mdld2'];
                            }
                        } else {
                            $found = true;  // we've hit something
                            $ratio = 1 - $distance / max(mb_strlen($row['epithet0'], "UTF-8"), $lenEpithet);
                        }
                    }
                } else {
                    $found = true;  // no epithet, so we've hit something anyway
                    $ratio = 1;
                    $distance = 0;
                }

                // if we've found anything valuable, look for the synonyms and put everything together
                if ($found) {
                    if ($row['synID']) {
                        $sql = "SELECT ts.taxonID, tg.genus,
                                 te0.epithet epithet0, te1.epithet epithet1, te2.epithet epithet2,
                                 te3.epithet epithet3, te4.epithet epithet4, te5.epithet epithet5,
                                 ta0.author author0, ta1.author author1, ta2.author author2,
                                 ta3.author author3, ta4.author author4, ta5.author author5
                                FROM tbl_tax_species ts
                                 LEFT JOIN tbl_tax_authors ta0 ON ta0.authorID = ts.authorID
                                 LEFT JOIN tbl_tax_authors ta1 ON ta1.authorID = ts.subspecies_authorID
                                 LEFT JOIN tbl_tax_authors ta2 ON ta2.authorID = ts.variety_authorID
                                 LEFT JOIN tbl_tax_authors ta3 ON ta3.authorID = ts.subvariety_authorID
                                 LEFT JOIN tbl_tax_authors ta4 ON ta4.authorID = ts.forma_authorID
                                 LEFT JOIN tbl_tax_authors ta5 ON ta5.authorID = ts.subforma_authorID
                                 LEFT JOIN tbl_tax_epithets te0 ON te0.epithetID = ts.speciesID
                                 LEFT JOIN tbl_tax_epithets te1 ON te1.epithetID = ts.subspeciesID
                                 LEFT JOIN tbl_tax_epithets te2 ON te2.epithetID = ts.varietyID
                                 LEFT JOIN tbl_tax_epithets te3 ON te3.epithetID = ts.subvarietyID
                                 LEFT JOIN tbl_tax_epithets te4 ON te4.epithetID = ts.formaID
                                 LEFT JOIN tbl_tax_epithets te5 ON te5.epithetID = ts.subformaID
                                 LEFT JOIN tbl_tax_genera tg ON tg.genID=ts.genID
                                WHERE ts.taxonID = '".mysql_escape_string($row['synID'])."'";
                        $result2 = mysql_query($sql);
                        $row2 = mysql_fetch_array($result2);
                        $syn = $row2['genus'];
                        if ($row2['epithet0']) $syn .= " " .          $row2['epithet0'] . " " . $row2['author0'];
                        if ($row2['epithet1']) $syn .= " subsp. "   . $row2['epithet1'] . " " . $row2['author1'];
                        if ($row2['epithet2']) $syn .= " var. "     . $row2['epithet2'] . " " . $row2['author2'];
                        if ($row2['epithet3']) $syn .= " subvar. "  . $row2['epithet3'] . " " . $row2['author3'];
                        if ($row2['epithet4']) $syn .= " forma "    . $row2['epithet4'] . " " . $row2['author4'];
                        if ($row2['epithet5']) $syn .= " subforma " . $row2['epithet5'] . " " . $row2['author5'];
                        $synID = $row2['taxonID'];
                    } else {
                        $syn = '';
                        $synID = 0;
                    }

                    // format the taxon-output
                    $taxon = $val['genus'];
                    if ($row['epithet0']) $taxon .= " "          . $row['epithet0'] . " " . $row['author0'];
                    if ($row['epithet1']) $taxon .= " subsp. "   . $row['epithet1'] . " " . $row['author1'];
                    if ($row['epithet2']) $taxon .= " var. "     . $row['epithet2'] . " " . $row['author2'];
                    if ($row['epithet3']) $taxon .= " subvar. "  . $row['epithet3'] . " " . $row['author3'];
                    if ($row['epithet4']) $taxon .= " forma "    . $row['epithet4'] . " " . $row['author4'];
                    if ($row['epithet5']) $taxon .= " subforma " . $row['epithet5'] . " " . $row['author5'];

                    // put everything into the output-array
                    $lev2[] = array('name'     => $name,
                                    'distance' => $distance + $val['distance'],
                                    'ratio'    => $ratio * $val['ratio'],
                                    'taxon'    => $taxon,
                                    'taxonID'  => $row['taxonID'],
                                    'syn'      => $syn,
                                    'synID'    => $synID);
                    if ($distance == 0 && $val['distance'] == 0) $fullHit = true;  // we've hit everything direct
                }
                $ctr++;
            }

            // if there's more than one hit, sort them (faster here than within the db)
            if (count($lev2) > 1) {
                $sort1 = array();
                $sort2 = array();
                $sort3 = array();
                foreach ($lev2 as $key2 => $row2) {
                    $sort1[$key2] = $row2['distance'];
                    $sort2[$key2] = $row2['ratio'];
                    $sort3[$key2] = $row2['name'];
                }
                array_multisort($sort1, SORT_NUMERIC, $sort2, SORT_DESC, SORT_NUMERIC, $sort3, $lev2);
            }

            // glue everything together
            if (count($lev2) > 0) {
                $lev[$key]['species'] = $lev2;
                $searchresult[] = $lev[$key];
            }
        }

        if ($fullHit) {
            // remove any entries with ratio < 100% if we have a full hit (ratio = 100%) anywhere
            foreach ($searchresult as $srKey => $srVal) {
                foreach ($srVal['species'] as $spKey => $spVal) {
                    if ($spVal['distance'] > 0) unset($searchresult[$srKey]['species'][$spKey]);
                }
            }
            foreach ($searchresult as $srKey => $srVal) {
                if (count($srVal['species']) == 0) unset($searchresult[$srKey]);
            }
        }

        $matches['result'][] = array('searchtext'  => $searchItem,
                                     'rowsChecked' => $ctr,
                                     'searchresult' => $searchresult);
    }
    $matches['error'] = ob_get_clean();

    return $matches;
}

/********************\
|                    |
|  private functions |
|                    |
\********************/

/**
 * parses and atomizes the Namestring
 *
 * @param string $taxon taxon string to parse
 * @return array parts of the parsed string
 */
private function _tokenizeTaxa($taxon)
{
    global $options;

    $result = array('genus'      => '',
                    'subgenus'   => '',
                    'epithet'    => '',
                    'author'     => '',
                    'rank'       => 0,
                    'subepithet' => '',
                    'subauthor'  => '');

    $taxon = ' ' . trim($taxon);
    $atoms = $this->_atomizeString($taxon, ' ');
    $maxatoms = count($atoms);
    $pos = 0;

    // check for any noise at the beginning of the taxon
    if ($this->_isEqual($atoms[$pos]['sub'], $options['taxonExclude']) !== false) $pos++;
    if ($pos >= $maxatoms) return $result;

    // get the genus
    $result['genus'] = $atoms[$pos++]['sub'];
    if ($pos >= $maxatoms) return $result;

    // check for any noise between genus and epithet
    if ($this->_isEqual($atoms[$pos]['sub'], $options['taxonExclude']) !== false) $pos++;
    if ($pos >= $maxatoms) return $result;

    // get the subgenus (if it exists)
    if (substr($atoms[$pos]['sub'], 0, 1) == '(' && substr($atoms[$pos]['sub'], -1, 1) == ')') {
        $result['subgenus'] = substr($atoms[$pos]['sub'], 1, strlen($atoms[$pos]['sub']) - 2);
        $pos++;
        if ($pos >= $maxatoms) return $result;
    }

    // get the epithet
    $result['epithet'] = $atoms[$pos++]['sub'];
    if ($pos >= $maxatoms) return $result;

    $sub = $this->_findInAtomizedArray($atoms, $options['taxonRankTokens']);
    if ($sub) {
        $result['rank'] = intval($sub['key']);
        $subpos  = $sub['pos'];
    } else {
        $result['rank'] = 0;
        $subpos = $maxatoms;
    }

    // author auslesen
    while ($pos < $subpos) {
        $result['author'] .= $atoms[$pos++]['sub'] . ' ';
    }
    $result['author'] = trim($result['author']);
    if ($pos >= $maxatoms) return $result;

    if ($result['rank']) {
        $pos = $subpos + 1;
        if ($pos >= $maxatoms) return $result;

        // get the subepithet
        $result['subepithet'] = $atoms[$pos++]['sub'];
        if ($pos >= $maxatoms) return $result;

        // subauthor auslesen
        while ($pos < $maxatoms) {
            $result['subauthor'] .= $atoms[$pos++]['sub'] . ' ';
        }
        $result['subauthor'] = trim($result['subauthor']);
    }

    return $result;
}


/**
 * localises a delimiter within a string and returns the positions
 *
 * Localises a delimiter within a string. Returns the positions of the
 * first character after the delimiter, the number of characters to the
 * next delimiter or to the end of the string and the substring. Skips
 * delimiters at the beginning (if desired) and at the end of the string.
 *
 * @param string $string string to atomize
 * @param string $delimiter delimiter to use
 * @param bool $trim skip delimiters at the beginning
 * @return array {'pos','len','sub'} of the atomized string
 */
private function _atomizeString($string, $delimiter, $trim = true)
{
    if (strlen($string) == 0) return array(array('pos' => 0, 'len' => 0, 'sub' => ''));

    $result = array();
    $pos1 = 0;
    $pos2 = strpos($string, $delimiter);
    if ($trim && $pos2 === 0) {
        do {
            $pos1 = $pos2 + strlen($delimiter);
            $pos2 = strpos($string, $delimiter, $pos1);
        } while ($pos1 == $pos2);
    }

    while ($pos2 !== false) {
        $result[] = array('pos' => $pos1, 'len' => $pos2 - $pos1, 'sub' => substr($string, $pos1, $pos2 - $pos1));
        do {
            $pos1 = $pos2 + strlen($delimiter);
            $pos2 = strpos($string, $delimiter, $pos1);
        } while ($pos1 == $pos2);
    }

    if ($pos1 < strlen($string)) {
        $result[] = array('pos' => $pos1, 'len' => strlen($string) - $pos1, 'sub' => substr($string, $pos1, strlen($string) - $pos1));
    }

    return $result;
}


/**
 * checks if a given text is equal with one item of an array
 *
 * Tests every array item with the text and returns the array-key
 * if they are euqal. If no match is found it returns "false".
 *
 * @param string $text to be compared with
 * @param array $needle items to compare
 * @return mixed|bool key of found match or false
 */
private function _isEqual($text, $needle)
{
    foreach ($needle as $key => $val) {
        if ($text == $val) return $key;
    }

    return false;
}


/**
 * compares a stack of needles with an array and returns the first match
 *
 * Compares each item of the needles array with each 'sub'-item of an atomized
 * string and returns the position of the first match ('pos') and the key
 * of the found needle or false if no match was found.
 *
 * @param array $haystack result of 'atomizeString'
 * @param array $needle stack of needles to search for
 * @return array|bool found match {'pos','key'} or false
 */
private function _findInAtomizedArray($haystack, $needle)
{
    foreach ($haystack as $hayKey => $hayVal) {
        foreach ($needle as $neeKey => $neeVal) {
            if ($neeVal == $hayVal['sub']) return array('pos' => $hayKey, 'key' => $neeKey);
        }
    }

    return false;
}

} // class taxamatchMdldService


/**
 * implementation of the json rpc functionality
 */
$service = new taxamatchMdldService();
jsonRPCServer::handle($service)
    or print 'no request';