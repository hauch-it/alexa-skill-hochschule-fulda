<?php

// Import alexa.php
require_once( 'alexa.php' );
require __DIR__ . '/vendor/autoload.php';
// Class alexa
$alexa = new AlexaClass();

/*
	Request (sent from Alexa) after the user says "Starte Mensa Fulda" or "Frage Mensa Fulda, was es heute zu essen gibt..." or..
	@param input - json input from alexa
	@param echoarray - decode json to array
	@return Plain text
	@author Dominic
*/
$input = file_get_contents('php://input');
$echoArray = json_decode($input);

/*
	Validation magic for security purpose
	@param validator - Skill ID check, input validation, ssl signature check
	@author Nicolai
*/
$AMAZON_SKILL_ID = "amzn1.ask.skill.2eb3280a-9f73-453b-83eb-a0ec8c0f7d5e";
$validator = new \Humps\AlexaRequest\AlexaRequestValidator($AMAZON_SKILL_ID, file_get_contents('php://input'), $_SERVER['HTTP_SIGNATURECERTCHAINURL'], $_SERVER['HTTP_SIGNATURE']);

/*
	Choose the request logic
	@param isLaunchRequest - true or false
	@param data - json string for alexa to read => send to Alexa
	@author Nicolai
*/
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
	// Reject the request with a 400 error => Bad Request
	echo $e;
	echo http_response_code(400);
	die();
}

?>
