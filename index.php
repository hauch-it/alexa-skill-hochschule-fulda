<?php

// Imports
require_once('data.php');
require_once('alexa.php');

// Classes
$mensa = new MensaClass();
$alexa = new AlexaClass();

// Filtered JSON on Date, Location, Category
$data = $mensa->filter('2017-12-21', 'fulda', 'vegetarisch');
$plain = $mensa->toPlainText($data);

$json = $alexa->response($plain);

header ( 'Content-Type: application/json' );
echo $json;
?>
