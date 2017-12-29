<?php

// Import
require_once( 'alexa.php' );
// Class
$alexa = new AlexaClass();

// Request (sent from Alexa)
$input = file_get_contents('php://input');
$echoArray = json_decode($input);

$data = NULL;
// Decide, which type of Request
$isLaunchRequest = $alexa->isLaunchRequest($echoArray);
if ($isLaunchRequest == True) {
	// LaunchRequest, welcome text
	$data = $alexa->handleLaunchRequest();
}
// IntentRequest, food text
else {
	$data = $alexa->handleIntentRequest($echoArray);
}

header ('Content-Type: application/json');
echo $data;
?>
