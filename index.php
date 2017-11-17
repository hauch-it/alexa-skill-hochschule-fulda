
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alexa (Mensa Skill)</title>
  </head>
<div class="container">
<?php

/*
	locId = {
		'fulda',
		'mensa-thm-in-giessen',
		'mensa-thm-in-friedberg',
		'mensa-otto-behaghel-strasse',
		'otto-eger-heim',
		'cafe-kunstweg',
		'campustor',
		'care',
		'cafeteria-ifz',
		'juwi'
	}
*/

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "http://www.maxmanager.de/daten-extern/sw-giessen/html/speiseplan-render.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "func=make_spl&locId=fulda&lang=de&date=2017-11-21");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

$headers = array();
$headers[] = "Pragma: no-cache";
$headers[] = "Origin: http://www.maxmanager.de";
$headers[] = "Accept-Encoding: gzip, deflate";
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
$result = strip_tags($result, '<html><body><table><tbody><div><tr><td><span><img><p>');

@$dom = new DOMDocument();
@$dom->loadHTML($result);

// food
$foodList = array();
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
				// Get divs
				foreach($tds->childNodes as $divs) {
					if ($divs->nodeName=='div') {
						// Get spans
						foreach($divs->childNodes as $spans) {
							if ($spans->nodeName=='span') {
								// Remove ingredients
								$search = '/\d{1,2}[a-h]{0,1}/';
								$value = preg_replace($search, "", $divs->nodeValue);
								$value = str_replace(',', '', $value);
							}
						}
						//echo $value . '<br>';
						$val = utf8_decode($value);
						echo $val;
						$foodSingle['title'] = trim($value);
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
    // add to list (@ suppress warnings)
    if (@$foodSingle['title'] != "") {
    	$foodList[] = $foodSingle;
    }
}

//var_dump($foodList);
//echo json_encode($foodList, JSON_PRETTY_PRINT);

if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close ($ch);
?>
</div>
