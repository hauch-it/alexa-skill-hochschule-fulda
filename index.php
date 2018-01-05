<?php

// Import
require_once( 'alexa.php' );
require __DIR__ . '/vendor/autoload.php';
// Class
$alexa = new AlexaClass();

// Request (sent from Alexa)
$input = file_get_contents('php://input');
$echoArray = json_decode($input);

// Validation
$AMAZON_SKILL_ID = "amzn1.ask.skill.2eb3280a-9f73-453b-83eb-a0ec8c0f7d5e";
$validator = new \Humps\AlexaRequest\AlexaRequestValidator($AMAZON_SKILL_ID, file_get_contents('php://input'), $_SERVER['HTTP_SIGNATURECERTCHAINURL'], $_SERVER['HTTP_SIGNATURE']);

// Skill magic begins here
try 
{
	if($validator->validateRequest()) {
    	// Accept request, because it is validated
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
  	}
}
catch(\Humps\AlexaRequest\Exceptions\AlexaRequestException $e) 
{ 
	// Reject the request with a 400 error
	echo $e;
	echo http_response_code(400);
	die();
}

?>
