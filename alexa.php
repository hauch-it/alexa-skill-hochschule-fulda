<?php
// Imports
require_once( 'data.php' );

class AlexaClass {

    /*
     	Response, that is sent to Alexa (is read by her)
     	@param toRead - Text, that Alexa should read
     */
    public function response($toRead)
    {
        $responseArray =
        ['version' => '1.0',
            'response' => [
            'outputSpeech' => [
                'type' => 'SSML',
                'ssml' => '<speak> ' . $toRead . ' </speak>'
            ],
            'shouldEndSession' => true
            ]
        ];
        // Encode, so that Alexa can read
        $json = json_encode ($responseArray);
        return $json;
    }
    
    /*
    	Check, if welcome text should be read or not
    	@param alexaRequest - Request that is sent by Alexa to the SSL-Server
    */
    public function isLaunchRequest($alexaRequest) {
    	// Extract from request
    	$request = $alexaRequest->request->type;
    	// When LaunchRequest then read welcome text
    	if (strcmp ($request, 'LaunchRequest') == 0) {
    		return True;
    	}
    	// IntentRequest
    	else {
    		return false;
    	} 
    }
    
    /*
    	When LaunchRequest, then let Alexa describe the Skill
    */
    public function handleLaunchRequest() {
    	$plainText = $this->welcome();
    	// Return encoded array
    	return $this->response($plainText);	
    }
    
    /*
    	Welcome text, that describes the Skills functionality
    */
    private function welcome() {
    	$welcome = "Hallo, I bims, 1 Alexa Skill für die Mensa ing Fulda. Ums dir vorlesen zu lassen, 
    	welche Speisen angeboten werden, sage: Frage Mensa Fulda, was heute vegetarisch ist. 
    	Oder: Frage Mensa Fulda, was morgen vegan ist. Weitere Beispiele findest du in der 
    	Beschreibung. Ich hoffe, I bims Dir nüdsli!";
    	
    	return $welcome;
    }
    
    public function handleIntentRequest($alexaRequest) {
    	// Class, used to get data
    	$mensa = new MensaClass();
    
    	// Extract values from request slot
		$date = $alexaRequest->request->intent->slots->DateSlot->value;
		$category = $alexaRequest->request->intent->slots->CategorySlot->value;
		
		$data = NULL;

		// Validate date
		if (strlen ( $date ) == 0 ) {
			// When date is not set, then use current date
			$date = date('Y-m-d');
		}
		// Validate category
		if (strlen ( $category ) == 0 ) {
			// No filter, all entries
			$data = $mensa->food($date, 'fulda');
		}
		else {
			// fetch and switch some synonyms from the user to the readable values which alexa need
			$category = $mensa->synonyms($category);
			
			// Filtered JSON on Date, Location, Category
			$data = $mensa->filter($date, 'fulda', $category);
		}

		// Convert to Alexa-friendly format
		$plain = $mensa->toPlainText($data);

		// Send response back to Alexa
		$responseToAlexa = $this->response($plain);
		return $responseToAlexa;
    }

}