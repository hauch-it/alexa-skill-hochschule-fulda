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

// Validate date
if (strlen ($date) == 0 ) {
	// When date is not set, then use current date
	$date = date( 'Y-m-d' );
}
// Validate category
if (strlen ($category) == 0 ) {
	// No filter, all entries
	$data = $mensa->food( $date, 'fulda' );
}
else {
	// Filtered JSON on Date, Location, Category
	$data = $mensa->filter( $date, 'fulda', $category );
}

// Convert to Alexa-friendly format
$plain = $mensa->toPlainText( $data );

// Send back data
$json = $alexa->response( $plain );

header ( 'Content-Type: application/json' );
echo $json;
?>
