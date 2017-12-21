<?php

// Imports
require_once('data.php');
require_once('alexa.php');

// Classes
$mensa = new MensaClass();
$alexa = new AlexaClass();

// Request (sent from Alexa)
$input = file_get_contents('php://input');
$echoArray = json_decode($input);

// Extract date from request
$date = $echoArray->request->intent->slots->DateSlot->value;

// Filtered JSON on Date, Location, Category
$data = $mensa->filter($date, 'fulda', 'vegetarisch');
$plain = $mensa->toPlainText($data);

$json = $alexa->response($plain);

header ( 'Content-Type: application/json' );
echo $json;
?>
