<?php

class MensaClass
{

	/*
		Converts a filtered food array to plain text, to be read by Alexa
		@param filteredArray - array, that is retrieved by @function filter
		@param number - count of array entries
		@param wish - string with final sentence
		@param plainText - string to be read by Alexa
		@return plainText
		@author Robin, Sabrina, Nicoleta
	*/
	public function toPlainText($filteredArray) {
		// Count entries of food
		$number = count($filteredArray);
		$plainText = "Es gibt " . $number . " Gerichte.";
		
		// No entry found
		if ($number == 0) {
			return '<say-as interpret-as="interjection">Der Computer sagt Nein.</say-as>';
		}
		// Skip intro when there is only one entry
		else if ($number == 1) {
			return "Es gibt: " . $filteredArray[0]['title'];
		}
		else {
			// loop through entries
			$rnumber = 1;
			for ($i = 0; $i < $number; $i++) {
				$plainText .= " Das " . $rnumber . ". Gericht ist: " . $filteredArray[$i]['title'] . ".";
				$rnumber++;
			}
			// add wish
			$wish = $this->getWish();
			$plainText .= $wish;
			return $plainText;
		}
		
	}
	
	/*
		Wish as final sentence to be read by Alexa
		@param greetings - array with several final sentence with Alexa Markup-Language
		@return greetings - return random sentence
		@author Nicolai
	*/
	public function getWish() {
		$greetings = array(
			' <say-as interpret-as="interjection">Lass es dir schmecken</say-as>',
			' <say-as interpret-as="interjection">Mach\'s gut</say-as>',
			' <say-as interpret-as="interjection">Lecker</say-as>',
			' <say-as interpret-as="interjection">iiieh</say-as>',
			' <say-as interpret-as="interjection">Hals und Beinbruch</say-as>',
			' <say-as interpret-as="interjection">Guten Appetit</say-as>',
			' <say-as interpret-as="interjection">bon appetit</say-as>',
			' <say-as interpret-as="interjection">Arrivederci</say-as>',
			' <say-as interpret-as="interjection">Zum Wohl</say-as>'
		);
		
		// select random greeting
		$number = count($greetings);
		$rand = rand(0, $number);
		
		return $greetings[$rand];
	}

	/*
		Match some synonyms the user could say, so alexa.php gets the correct value instead of the synonym
		@param category - Category, to filter food ('Knoblauch', 'Vegetarisch', 'Rind', 'Schwein', 'Geflügel', 'mensaVital')
		@param array - Key Value pairs of synonyms and category values
		@return - cleaned category value
		@author Dominic
	*/
	
	public function match($category) {
		// Key-Value to match category
		$array = array(
			"schweinefleisch" => "schwein",
			"rindfleisch" => "rind",
			"huhn" => "geflügel",
			"hühnchen" => "geflügel",
			"hähnchen" => "geflügel",
			"geflügelfleisch" => "geflügel",
			"vegetarisches" => "vegetarisch",
			"ohne fleisch" => "vegetarisch",
			"fleischfrei" => "vegetarisch",
			"gemüse" => "vegetarisch",
			"veganes" => "vegan",
			"mensa vital" => "mensavital",
			"fleischgerichte" => "fleisch"
		);
	
		$category = strtolower($category);
		// Replace category using array
		$category = str_replace(array_keys($array), $array, $category);
		
		return $category;
	}
	
	
	/*
		Filters food on category
		@param dom - HTML Div from maxmanager.de
		@param food - cleaned div => array with food entries
		@param date - date to retrieve food for with date-format YYYY-MM-dd
		@param location - location to retrieve food for, e.g. fulda
		@param category - Category, to filter food ('Knoblauch', 'vegetarisch', 'Rind', 'Schwein', 'Geflügel', 'mensaVital')
		@return - Array with filtered food
		@author Dominic
	*/
	public function filter($date, $location, $category) {
		$dom = $this->getDiv($date, $location);
		$food = $this->getArray($dom);
		
		// filter by category
		$result = array();
		foreach($food as $entry) {
			// loop through each food category
			foreach($entry['category'] as $cat) {
				// compare the category from user with the category from food array and build result array
				if (strtolower($cat) == $category && $category != 'fleisch') {
					$result[] = $entry;
					break;
				}
				// filter by category is 'fleisch' or 'fleischgerichte' and combine all dishes with meat
				elseif ($category == 'fleisch' && (strtolower($cat) == 'rind' || strtolower($cat) == 'geflügel' || strtolower($cat) == 'schwein')) {
					$result[] = $entry;
					break;
				}
				// put vegan meals into vegetarian category. It is still possible to ask only for vegan meals.
				elseif ($category == 'vegetarisch' && (strtolower($cat) == 'vegan' || strtolower($cat) == 'vegetarisch')) {
					$result[] = $entry;
					break;
				}
			}
		}
		// result
    	return $result;
	}
	
	/*
		No category is given by user, so return all entries
		@param date - date to retrieve food for with date-format YYYY-MM-dd
		@param location - location to retrieve food for, e.g. fulda
		@return - Array with food
		@author Sabrina
	*/
	public function food($date, $location) {
		$dom = $this->getDiv($date, $location);
		$food = $this->getArray($dom);
		// result
		return $food;
	}

	/*
		Gets rendered div from maxmanager.de
		@param date - Date (yyyy-mm-dd)
		@param location - Name of Mensa ('fulda', 'mensa-thm-in-giessen', 'mensa-thm-in-friedberg', 'mensa-otto-behaghel-strasse', 'otto-eger-heim', 'cafe-kunstweg', 'campustor', 'care', 'cafeteria-ifz', 'juwi')
		@return - rendered div with stripped HTML Tags we dont need
		@author Dominic
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
		$headers[] = "Referer: http://www.maxmanager.de/daten-extern/sw-giessen/html/speiseplaene.php?einrichtung='.$location.'";
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
		@return - multiple Array with parsed data
		@author Nicolai & Dominic (Logic), Nicolai (Coding), Dominic (Coding, Testing => Fixes)
	*/
	 private function getArray($div) {
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
                                // Add 'Pasta' to String when the String starts with ' mit'
								if ($title != "" && substr($title, 0, 4) == ' mit') {
									$foodSingle['title'] = ' Pasta'.$title;
								} elseif($title != "" && substr($title, 0, 4) != ' mit') {
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
    		// Add to list when new food item
    		if ($foodSingle['title'] != "" && !in_array($foodSingle, $foodList)) {
    			array_push($foodList, $foodSingle);
    		}
		}
		// return multiple array which can now easily encode to Json-format
		return $foodList;
	}
	// end getArray()	
}
?>
