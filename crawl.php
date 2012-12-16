<?php
	
	include 'simple_html_dom.php';

	session_start();
	$_SESSION['COMPLETE_DATA'] = array();


	if(isset($_POST['URL'])){
		$data = parseURL($_POST['URL']);
		echo json_encode($data);
	}

	if(isset($_POST['parseURL'])){
		echo getXMLData($_POST['parseURL']);
	}

	if(isset($_POST['parseXML'])){
		echo scrapeData($_POST['parseXML']);
	}

	if(isset($_POST['export'])){
		exportData();
	}

	function parseURL($url){
		$html = file_get_html($url);
		$tmplink = array();
		foreach($html->find('table#all-products-table td:eq(4) a') as $e){
			$link = 'http://icecat.biz'.$e->href;
			if(!in_array($link,$tmplink)){			
				if(preg_match('/(\/p\/)+./',$link)){
					array_push($tmplink,$link);
				}				
			}
		}
		return $tmplink;
	}
	

	function getXMLData($url){
		$html = file_get_html($url);
		$tmplink = array();
		foreach($html->find('table#datasheet-embed input') as $e){
			$link = $e->value;
			if(!in_array($link,$tmplink)){
				if(preg_match('/.+(\.xml)/',$link)){
					array_push($tmplink,$link);
					return json_encode(array(
						'status'=>true,
						'data'=>$link
						)
					);		
				}	
			}
		}

		if(count($tmplink)==0){
			return json_encode(array(
				'status'=>false,
				'data'=>'Error: Could not find the data'
				)
			);
		}
		//return $tmplink;
	}


	function scrapeData($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		$status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($status==200){
			$x = new SimpleXMLElement($data,LIBXML_NOCDATA);
			
			// initiliaze to its corresponding variables
			$Kategorie        = $x->Product->Category->Name['Value']; /* Category */
			$Hersteller       = $x->Product->Supplier['Name']; /* Brand */
			$Artikelnummer    = $x->Product['Prod_id']; /* Product Code */
			$Bezeichnung      = $x->Product['Name']; /* Model */
			$EAN              = $x->Product->EANCode['EAN']; /* EAN / UPC */
			$UPC              = $EAN;
			$Beschreibung     = $x->Product->ProductDescription['LongDesc']; /* Product description */
			$Technische_Daten = $x->Product->SummaryDescription->LongSummaryDescription; /* Specs */
			$Sprache          = $x->Product->ProductFeature['59']['Value']; /* Language */		
			$URL              = $x->Product->ProductDescription['URL']; /* URL */
			$Marktrelease     = $x->Product['ReleaseDate']; /* Release date */		
			$Bild             = $x->Product['HighPic'];/* HiRes Image */

			$file_header = array(
				'Kategorie',
				'Hersteller',
				'Artikelnummer',
				'Bezeichnung',
				'EAN',
				'UPC',
				'Beschreibung',
				'Technische_Daten',
				'Sprache',
				'URL',
				'Marktrelease',
				'Bild');

			$file_content = array(
				$Kategorie,
				$Hersteller,
				$Artikelnummer,
				$Bezeichnung,
				$EAN,
				$UPC,
				$Beschreibung,
				$Technische_Daten,
				$Sprache,
				$URL,
				$Marktrelease,
				$Bild);			

			array_push($_SESSION['COMPLETE_DATA'],$file_content);	

			// INIT VARS	
			$DriveSave = false;
			$DBSave = true;
			$path = '.';

			if($DriveSave){
				/* enable $DriveSave = true if you want to save it on your drive */
				$fp = fopen($path."scrape_".time().".csv","w");
				fputcsv($fp, $file_header);
				fputcsv($fp, $file_content);		
				fclose($fp);				
			}	


			if($DBSave){
				/* enable $DBSave = true if you want to save this on your db */	

				/* Please set your database connection here 	 */		
				define('DB_HOST','localhost');
				define('DB_USER','User_0001');
				define('DB_PASS','testingit');
				define('DB_NAME','scrapeit');
				/* haggen added this: */
				define ('TABLE_NAME', 'scrape');

				$sql = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
				
				$stmt = $sql->prepare("INSERT INTO scrape(
					Kategorie,
					Hersteller,
					Artikelnummer,
					Bezeichnung,
					EAN,
					UPC,
					Beschreibung,
					Technische_Daten,
					Sprache,
					URL,
					Marktrelease,
					Bild) VALUES('?','?','?','?','?','?','?','?','?','?','?','?')");
				$stmt->bind_param('ssssssssssss',
					$Kategorie,
					$Hersteller,
					$Artikelnummer,
					$Bezeichnung,
					$EAN,
					$UPC,
					$Beschreibung,
					$Technische_Daten,
					$Sprache,
					$URL,
					$Marktrelease,
					$Bild);
				$stmt->execute();
				
			}
			return json_encode(array(
				'status'=>true,
				'data'=>'Completed. Data saved.'
				)
			);
		}
		else{
			return json_encode(array(
				'status'=>false,
				'data'=>'Error: HTTP return '.$status
				)
			);
		}
	}

	function exportData(){
		$file_header = array(
				'Kategorie',
				'Hersteller',
				'Artikelnummer',
				'Bezeichnung',
				'EAN',
				'UPC',
				'Beschreibung',
				'Technische_Daten',
				'Sprache',
				'URL',
				'Marktrelease',
				'Bild');

		if(count($_SESSION['COMPLETE_DATA'])>1){
			$fp = fopen($path."complete_scrape_".time().".csv","w");
			fputcsv($fp, $file_header);
			foreach($_SESSION['COMPLETE_DATA'] as $a){
				fputcsv($fp, $a);	
			}						
			fclose($fp);
		}
	}
?>
