<?php

	$keyCode = "634ac1f2d88d585be786aad9c2eed3ca190df7ae";
	$first_name_only = false;

	function decode( $obj ) {
		
		$obj['nameString'] = base64_decode($obj['nameString']);
		$obj['fullNameString'] = base64_decode($obj['fullNameString']);

		#Synonyms Fixes
		if (count($obj['homotypicSynonyms']['value'])) {
		foreach( $obj['homotypicSynonyms']['value'] as &$tmpObj ) {
			$tmpObj['nameString'] = base64_decode($tmpObj['nameString']);
			$tmpObj['fullNameString'] = base64_decode($tmpObj['fullNameString']);
		}
		}

		#Vernacular Fixes
		if (count($obj['vernacularNames']['value'])) {
		foreach( $obj['vernacularNames']['value'] as &$tmpObj ) {
			$tmpObj['nameString'] = base64_decode($tmpObj['nameString']);
		}
		}

		#Citation Fixes
		if (count($obj['citations']['value'])) {
		foreach( $obj['citations']['value'] as &$tmpObj ) {
			$tmpObj['ArticleTitle'] = base64_decode($tmpObj['ArticleTitle']);
			$tmpObj['Author'] = base64_decode($tmpObj['Author']);
			$tmpObj['PublicationTitle'] = base64_decode($tmpObj['PublicationTitle']);
		}
		}

		
		return( $obj );
	}
	
	function getNamebankID( $nameString, $keyCode, $first_name_only ) {		
		$url = sprintf("http://www.ubio.org/webservices/service.php?function=namebank_search&searchName=%s&sci=1&vern=1&keyCode=%s", urlencode($nameString), $keyCode);
		$xmlStringContents = file_get_contents( $url );
		$ubio = json_decode( xml2json::transformXmlStringToJson($xmlStringContents), true );
		
		if (count($ubio['results']['scientificNames']['value'])) {
		foreach( $ubio['results']['scientificNames']['value'] as $rec ) {
			$namebankID[] = $rec['namebankID'];
			if ($first_name_only) {
				break;
			}
		}
		}
		return( $namebankID );
	}
	
	function getClassificationList( $namebankID, $keyCode ) {
		$url = sprintf("http://www.ubio.org/webservices/service.php?function=classificationbank_search&namebankID=%s&keyCode=%s", $namebankID, $keyCode);
		$xmlStringContents = file_get_contents( $url );
		$ubio = json_decode( xml2json::transformXmlStringToJson($xmlStringContents), true );

		if (count($ubio['results']['seniorNames']['value'])) {
		foreach( $ubio['results']['seniorNames']['value'] as $item ) {
//			if (in_array( $item['classificationTitleID'], array(100, 106)) ) {
			if (in_array( $item['classificationTitleID'], array(106)) ) {
				$list[ $item['classificationBankID'] ] = ( $item['classificationTitleID'] );
//print_r($item);			
//print base64_decode( $item['classificationTitle'] ) . "<br>";
			}
		}		
		}
		return( $list );
	}
	
	function getHigherTaxa( $classificationBankID, $keyCode ) {
		$url = sprintf("http://www.ubio.org/webservices/service.php?function=classificationbank_object&hierarchiesID=%s&ancestryFlag=1&keyCode=%s", $classificationBankID, $keyCode );
		$xmlStringContents = file_get_contents( $url );
		$ubioHT = json_decode( xml2json::transformXmlStringToJson($xmlStringContents), true );
		$taxaJoin = '';
		$taxa = array();
		$genus = '';
		$family = '';
		$class = '';
		$order = '';
		$phylum = '';
		$kingdom = '';
//print_r($ubioHT);		
		if (count($ubioHT['results']['ancestry']['value'])) {
		foreach ($ubioHT['results']['ancestry']['value'] as $rank) {
			if (strtolower($rank['rankName']) != "no rank") {
				$rank['nameString'] = base64_decode($rank['nameString']);
				$taxa[] = $rank;
				$taxaJoin .= $rank['nameString'] . " - ";
				
				switch( $rank['rankName'] ) {
					case 'genus':
						$genus = $rank['nameString'];
						break;
					case 'family':
						$family = $rank['nameString'];
						break;
					case 'class':
						$class = $rank['nameString'];
						break;
					case 'order':
						$order = $rank['nameString'];
						break;
					case 'phylum':
						$phylum = $rank['nameString'];
						break;
					case 'kingdom':
						$kingdom = $rank['nameString'];
						break;
				}
			}
		}
		}
		$taxaJoin = substr($taxaJoin, 0, -3);
		return( array( "genus" => $genus, "family" => $family, "class" => $class, "order" => $order, "phylum" => $phylum, "kingdom" => $kingdom ) );
	}

	switch( $_REQUEST['cmd'] ) {
		
		case 'taxagrid':
//print "<pre>";		
			$source = 104;
			require_once('xml2json.php');
			$list = json_decode( stripslashes($_REQUEST['list']), true );
			if (count($list)) {
			foreach( $list as $str ) {
				$namebankIDList = getNamebankID( $str['name'], $keyCode, $first_name_only );
//print_r( $namebankIDList );
				$classList = array();
				$found = false;
				if (count($namebankIDList)) {
				foreach( $namebankIDList as $namebankID ) {
					$tmpList = getClassificationList( $namebankID, $keyCode );
					if (count($tmpList)) {
						foreach( $tmpList as $name => $item ) {
							if ( !in_array( $name, $classList ) ) {
								$classList[$name] = $item;
							}
						}
						}
					}
					if (count($classList)) {
					foreach( $classList as $id => $source ) {
						$ht = getHigherTaxa( $id, $keyCode );
						$ht['scientificname'] = $str['name'];
						$ht['source'] = $source;
						$ranks[$source][] = $ht;
						$found = true;
					}
					}
				}

				if (!$found) {
					# Set everything to blank except scientificname
					$ranks["0"] = array( "scientificname" => $str['name'], "genus" => "", "family" => "", "class" => "", "order" => "", "phylum" => "", "kingdom" => "" );
				}
				
$i++;
if ($i > 5) {
//print_r($ranks);				
//	exit();			
}
			}
			}
//			print json_encode( $ranks );
			print json_encode( $ranks["106"] );
			break;
			
		case 'highertaxa':
			require_once('xml2json.php');
			$namebankID = 0;
//print "<pre>";
			$namebankID = getNamebankID( $_REQUEST['ScientificName'], $keyCode, true );
			$namebankID = $namebankID[0];
			
			if (count($namebankID)) {
				#Get Higher Taxa
				$higherTaxa = array();
				$url = sprintf("http://www.ubio.org/webservices/service.php?function=classificationbank_search&namebankID=%s&keyCode=%s", $namebankID, $keyCode);
				$xmlStringContents = file_get_contents( $url );
				$ubio = json_decode( xml2json::transformXmlStringToJson($xmlStringContents), true );

//				$higherTaxa = array();
				if (count($ubio['results']['seniorNames']['value'])) {
				foreach ($ubio['results']['seniorNames']['value'] as $source ) {
					$url = sprintf("http://www.ubio.org/webservices/service.php?function=classificationbank_object&hierarchiesID=%s&ancestryFlag=1&keyCode=%s", $source['classificationBankID'], $keyCode );
					$xmlStringContents = file_get_contents( $url );
					$ubioHT = json_decode( xml2json::transformXmlStringToJson($xmlStringContents), true );
					
					$taxaJoin = '';
					$taxa = array();
					if (count($ubioHT['results']['ancestry']['value'])) {
					foreach ($ubioHT['results']['ancestry']['value'] as $rank) {
						if (strtolower($rank['rankName']) != "no rank") {
							$rank['nameString'] = base64_decode($rank['nameString']);
							$taxa[] = $rank;
							$taxaJoin .= $rank['nameString'] . " - ";
						}
					}
					}
					$taxaJoin = substr($taxaJoin, 0, -3);
					$higherTaxa[] = array( source => base64_decode($source['classificationTitle']), taxa => $taxa, taxaJoin => $taxaJoin );
				}
				}

				#Get Synonyms, Vernacular & Citations
				$url = sprintf("http://www.ubio.org/webservices/service.php?function=namebank_object&namebankID=%s&keyCode=%s", $namebankID, $keyCode);
				$xmlStringContents = file_get_contents( $url );
				$ubio = json_decode( xml2json::transformXmlStringToJson($xmlStringContents), true );
				$ubio = decode( $ubio['results'] );

				$citations = array();
				$synonyms = array();
				$vernacular = array();
				
				if (count($ubio['citation']['value'])) {
					$citations = $ubio['citation']['values'];
				}
				if (count($ubio['vernacularNames']['value'])) {
					$vernacular = $ubio['vernacularNames']['value'];
				}
				if (count($ubio['homotypicSynonyms']['value'])) {
					$synonyms = $ubio['homotypicSynonyms']['value'];
				}
				
				$data = array( namebankID => $ubio['namebankID'], nameString => $ubio['nameString'], extinctFlag => $ubio['extinctFlag'], highertaxa => $higherTaxa, synonyms => $synonyms, vernacular => $vernacular, citations => $citations );
			}
			
			print json_encode( array( success => true, data => $data ) );
			break;
			
		default:
			$url = "http://services.eol.org/namelink/doc/nametag_service.php?open_tag=%3Cname%20offset=%27\$OFFSET%27%20type=%27taxonfinder%27%20class=%27scientific_name%27%20short=%27\$COMPLETE_NAME%27%20full=%27\$COMPLETE_NAME%27%3E&close_tag=%3C/name%3E&url=" . $_REQUEST['url'];
			print file_get_contents( $url );
			break;

	}

?>