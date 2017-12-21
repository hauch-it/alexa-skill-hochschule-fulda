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
}