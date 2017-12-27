<?php

// Imports
require_once( 'data.php' );
require_once( 'alexa.php' );

// Classes
$mensa = new MensaClass();
$alexa = new AlexaClass();

// Request (sent from Alexa)
$input = file_get_contents( 'php://input' );
$echoArray = json_decode( $input );

// Extract values from request slot
$date = $echoArray->request->intent->slots->DateSlot->value;
$category = $echoArray->request->intent->slots->CategorySlot->value;

// Filtered JSON on Date, Location, Category
$data = $mensa->filter( $date, 'fulda', $category );
$plain = $mensa->toPlainText( $data );

// Send back data
$json = $alexa->response( $plain );

header ( 'Content-Type: application/json' );
echo $json;
?>
