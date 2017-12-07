<?php

class AlexaClass {
    //create Json from filtered Array(Category)
    public function alexaJsonOutTitleAfterFilter($result)
    {
        $responseArray =
        ['version' => '1.0',
            'response' => [
            'outputSpeech' => [
                'type' => 'ssml',
                'ssml' => '<speak> '.$result[0]["title"].'. </speak>'
            ],
            'shouldEndSession' => true
            ]
        ];

        $json = json_encode ( $responseArray );
        //Escape all HTML-Tags, so Alexa can read the <speak> tags in raw format
        $json = htmlentities($json, ENT_COMPAT, 'UTF-8');
        //some UTF-8 Problem fix
        $json = strtr($json, array(
            '\u00A0'    => ' ',
            '\u0026'    => '&',
            '\u003C'    => '<',
            '\u003E'    => '>',
            '\u00E4'    => 'ä',
            '\u00C4'    => 'Ä',
            '\u00F6'    => 'ö',
            '\u00D6'    => 'Ö',
            '\u00FC'    => 'ü',
            '\u00DC'    => 'Ü',
            '\u00DF'    => 'ß',
            '\u20AC'    => '€',
            '\u0024'    => '$',
            '\u00A3'    => '£',
            '\u00a0'    => ' ',
            '\u003c'    => '<',
            '\u003e'    => '>',
            '\u00e4'    => 'ä',
            '\u00c4'    => 'Ä',
            '\u00f6'    => 'ö',
            '\u00d6'    => 'Ö',
            '\u00fc'    => 'ü',
            '\u00dc'    => 'Ü',
            '\u00df'    => 'ß',
            '\u20ac'    => '€',
            '\u00a3'    => '£',
        ));
        //replace the from json_encode automatically set Escape-Sign from the <speak> closing Tag
        $json = str_replace("\\", "", $json);
        $json = str_replace('&quot;', '"', $json);
		$json = str_replace('&lt;', '<', $json);
		$json = str_replace('&gt;', '>', $json);
        //give Alexa something to speak
        return $json;
    }
}