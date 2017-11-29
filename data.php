<?php

class MensaClass
{
	/*
		Filters food on category
		@param category - Category, to filter food ('Knoblauch', 'vegetarisch', 'Rind', 'Schwein', 'Geflügel', 'mensaVital')
		@return - Json with filtered food
	*/
	public function filter($date, $location, $category) {
		$dom = $this->getDiv($date, $location);
		$food = $this->getJson($dom);
		
		// filter by category
		$result = array();
		foreach($food as $entry) {
			// loop through each foods category
			//var_dump($entry);
			foreach($entry['category'] as $cat) {
				// match
				if ($cat == $category) {
					$result[] = $entry;
					echo $entry['title'] . '<br>';
					break;
				}
			}
		}
		// result
		//var_dump($result);
	}	

	/*
		Gets rendered div from maxmanager.de
		@param date - Date (yyyy-mm-dd)
		@param location - Name of Mensa ('fulda', 'mensa-thm-in-giessen', 'mensa-thm-in-friedberg', 'mensa-otto-behaghel-strasse', 'otto-eger-heim', 'cafe-kunstweg', 'campustor', 'care', 'cafeteria-ifz', 'juwi')
		@return - rendered div
	*/
	private function getDiv($date, $location) {
		// build POSTFIELDS
		$postfields = "func=make_spl&locId=" . $location . "&lang=de&date=" . $date;
		// cURL options
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.maxmanager.de/daten-extern/sw-giessen/html/speiseplan-render.php");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		// header (to get rendered div)
		$headers = array();
		$headers[] = "Pragma: no-cache";
		$headers[] = "Origin: http://www.maxmanager.de";
		$headers[] = "Accept-Encoding: *";
		$headers[] = "Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7";
		$headers[] = "User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.75 Mobile Safari/537.36";
		$headers[] = "Content-Type: application/x-www-form-urlencoded";
		$headers[] = "Accept: text/javascript, text/html, application/xml, text/xml, */*";
		$headers[] = "Cache-Control: no-cache";
		$headers[] = "X-Requested-With: XMLHttpRequest";
		$headers[] = "Connection: keep-alive";
		$headers[] = "Referer: http://www.maxmanager.de/daten-extern/sw-giessen/html/speiseplaene.php?einrichtung=fulda";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		// cURL Result
		$result = curl_exec($ch);
		$result = strip_tags($result, '<html><body><table><tbody><div><tr><td><span><img><p><sup>');
		return $result;
	}
	// end getDiv()
	
	/*
		Parses rendered div from @getDiv($date, $location)
		@param div - Div to render
		@return - Json object with parsed data
	 */
	 private function getJson($div) {
	 	// build DOM
	 	@$dom = new DOMDocument();
		@$dom->loadHTML($div);
		
		// List with all food entries
		$foodList = array();
		// Single food entry
		$foodSingle = array();

		// Get all trs (contains food)
		$tr = $dom->getElementsbyTagName('tr');
			foreach($tr as $trs) {
			// Get all tds (contains image, text, content)
				foreach($trs->childNodes as $tds) {
					// Make sure, to get only tds
					if($tds->nodeName=='td') {
						// Cell1 contains text
						if ($tds->hasAttribute('class') && strstr($tds->getAttribute('class'), 'cell1')) {
							$title = "";
							$ingredients = array();
							// Get divs
							foreach($tds->childNodes as $divs) {
								if ($divs->nodeName=='div') {
									// Get spans
									foreach($divs->childNodes as $spans) {
										if ($spans->nodeName=='span') {
											// Get title of food
											$title = $title . ' ' . $spans->nodeValue;
											// Get sups
											foreach($spans->childNodes as $sups) {
												// Filter ingredients
												if ($sups->tagName=='sup') {
													// Add all ingredients to array
													$ingredients[] = $sups->nodeValue;
												}
											}
										}
									}
									// Remove ingredients
									foreach($ingredients as $ingredient) {
										$title = str_replace($ingredient, "", $title);
									}
									// UTF-8 for Umlauts
									$title = utf8_decode($title);
									// Some cleaning
									$title = str_replace(" - ab 0,70 €", "", $title);
									$title = str_replace("aus ökol. Anbau DE-ÖKO-007", "aus ökologischem Anbau", $title);
									$title = str_replace("Textzusätze Speiseleitsystem (leere Rezeptur!) ", "", $title);
						
									// Mensa Vital fix (remove p.P.)
									$mensaVital = '.';
									$pos = strpos($title, $mensaVital);
									// only for Mensa Vital
									if ($pos != false) {
										$title = substr($title, 0, $pos-1);
									}
								}				
								// Add to List
								if ($title != "") {
									$foodSingle['title'] = $title;
								}
						}
					}
					
					// Cell2 contains images (with content as title-Attribute)
					if ($tds->hasAttribute('class') && strstr($tds->getAttribute('class'), 'cell2')) {
						// Get imgs
						$ingredients = array();
						foreach($tds->childNodes as $imgs) {
							if ($imgs->nodeName=='img') {
								//print_r($imgs->attributes);
								$title = $imgs->getAttribute('title');
								$title = utf8_decode($title);
								$ingredients[] = trim($title);
							}
						}
						$foodSingle['category'] = $ingredients;
					}
					
					// Cell3 contains prices
					if ($tds->hasAttribute('class') && strstr($tds->getAttribute('class'), 'cell3')) {
						// No nested objects
						$price = $tds->nodeValue;
						$prices = explode(" /", $price);
						$foodSingle['price'] = trim($prices[0]);
					}
        		}
    		}
    		// add to list when new food item
    		if ($foodSingle['title'] != "" && !in_array($foodSingle, $foodList)) {
    			array_push($foodList, $foodSingle);
    		}
		}
		// return Json
		return $foodList;
	}
	// end getJson()
		
}

?>