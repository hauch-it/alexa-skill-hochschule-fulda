<?php
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
                'ssml' => '<speak> '. $toRead .'. </speak>'
            ],
            'shouldEndSession' => true
            ]
        ];
        // Encode, so that Alexa can read
        $json = json_encode ( $responseArray );
        return $json;
    }
    
    /*
    	Welcome text, that describes the Skills functionality
    */
    public function welcome() {
    	$welcome = "Willkommen bei der Mensa in Fulda. Um dir vorlesen zu lassen, 
    	welche Speisen angeboten werden, sage: Frage Mensa Fulda, was heute vegetarisch ist. 
    	Oder: Frage Mensa Fulda, was morgen vegan ist. Weitere Beispiele findest du in der 
    	Beschreibung des Skills. Ich hoffe, ich bin Dir nützlich!";
    }
    
    /*
    	Check, if welcome text should be read or not
    	@param alexaRequest - Request that is sent by Alexa
    */
    public function isLaunchRequest($alexaRequest) {
    	// Extract from request
    	$request = $alexaRequest->request->type;
    	// When LaunchRequest then read welcome text
    	if ( strcmp ( $request, "LaunchRequest" ) == 0 ) {
    		return True;
    	}
    	// IntentRequest
    	else {
    		return false;
    	} 
    }
}