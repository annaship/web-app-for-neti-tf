<?php
/**
 * database access
 */
$options['dbhost'] = "localhost";     // hostname
$options['dbname'] = "herbarinput";   // database name
$options['dbuser'] = "webuser";       // username
$options['dbpass'] = "";              // password

/**
 * strings that should be ignored by parsing/atomizing functions
 * suffice genus or connect genus and epithet
 */
$options['taxonExclude'] = array('aff', 'aff.', 'cf', 'cf.', 'cv', 'cv.', 'agg', 'agg.', 'sect', 'sect.', 'ser', 'ser.', 'grex');

/**
 * strings of rank are recognized as seperators between species- and infraspecific-epithet
 */
$options['taxonRankTokens'] = array('1a' => 'subsp',  '1b' => 'subsp.',
                                    '2a' => 'var',    '2b' => 'var.',
                                    '3a' => 'subvar', '3b' => 'subvar.',
                                    '4a' => 'forma',
                                    '5a' => 'subf',   '5b' => 'subf.',   '5c' => 'subforma');  // forma my be f.