<?php
// Imports
require_once( 'data.php' );

class AlexaClass {

    
    /*
    	Check, if Request is LaunchRequest or not
    	@param alexaRequest - Request that is sent by Alexa to the SSL-Server
		@return LaunchRequest => true or false
		@author Nicolai
    */
    public function isLaunchRequest($alexaRequest) {
    	// Extract 'type' from alexa Request
    	$request = $alexaRequest->request->type;
    	// When type = LaunchRequest return true
    	if (strcmp ($request, 'LaunchRequest') == 0) {
    		return True;
    	}
    	// if type ist not LaunchRequest return false => so its an IntentRequest
    	else {
    		return false;
    	} 
    }
    
    /*
    	When LaunchRequest is true, then let Alexa describe the Skill
		@param plainText - string with welcome text
		@return welcome text as json => text for the alexa response
		@author Nicolai
    */
    public function handleLaunchRequest() {
    	$plainText = $this->welcome();
    	// Return encoded array
    	return $this->response($plainText);
    }
    
    /*
    	Welcome text, that describes the Skills functionality
		@param welcome - string with welcome text
		@return welcome - text as string
		@author Nicolai
    */
    private function welcome() {
    	$welcome = "Hallo, Ich bin ein Alexa Skill für die Mensa in Fulda. Um dir vorlesen zu lassen, 
    	welche Speisen angeboten werden, sage: Frage Mensa Fulda, was heute vegetarisch ist. 
    	Oder: Frage Mensa Fulda, was morgen vegan ist. Weitere Beispiele findest du in der 
    	Beschreibung. Ich hoffe, ich bin Dir nützlich!";
    	
    	return $welcome;
    }

    private function gag() {
        $gag ='Das weiß ich leider nicht. Ich versuche aber, ein gutes Wort bei Olli einzulegen.
        <amazon:effect name="whispered">Olli, rück die Credits raus.</amazon:effect>';

        return $gag;
    }
    
	/*
		IntentRequest
    	@param alexaRequest - Request that is sent by Alexa to the SSL-Server
		@param date - from alexa DateSlot => Intent value
		@param category - from alexa CategorySlot => Intent value
		@param data - array with food entries
		@param plain - string for alexa to read
		@return json string to send to alexa
		@author Dominic
    */
    public function handleIntentRequest($alexaRequest) {

        // OliverIntent
        $intent = $alexaRequest->request->intent->name;
        if (strcmp($intent, 'OliverIntent') == 0) {
            $text = $this->gag();
            // Send response back to Alexa
            $responseToAlexa = $this->response($text);
            return $responseToAlexa;
        }

        // FilterIntent

    	// Class, used to get data
    	$mensa = new MensaClass();
    
    	// Extract values from request slot
		$date = $alexaRequest->request->intent->slots->DateSlot->value;
		$category = $alexaRequest->request->intent->slots->CategorySlot->value;
		
		$data = NULL;

		// Check if date is empty, if yes then set today as date
		if (strlen ( $date ) == 0 ) {
			// When date is not set, then use current date
			$date = date('Y-m-d');
		}
		// Check if category is empty
		if (strlen ( $category ) == 0 ) {
			// No category filter => all entries
			$data = $mensa->food($date, 'fulda');
		}
		else {
			// Match some synonyms the user could say, so Alexa gets the correct value instead of the synonym
			$category = $mensa->match($category);
			
			// Filtered Array on Date, Location, Category
			$data = $mensa->filter($date, 'fulda', $category);
		}

		// Convert data array to string
		$plain = $mensa->toPlainText($data);

		// Send response back to Alexa
		$responseToAlexa = $this->response($plain);
		return $responseToAlexa;
    }
	
	 /*
     	Response, that is sent to Alexa (is read by her)
     	@param toRead - Text, that Alexa should read
		@return json
		@author Dominic
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
        $json = json_encode($responseArray);
        return $json;
    }

}