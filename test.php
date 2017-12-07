<?php
require_once('data.php');
require_once('alexa.php');

$mensa = new MensaClass();
$alexa = new AlexaClass();

$jsonOut = $alexa->alexaJsonOutTitleAfterFilter($mensa->filter('2017-12-07', 'fulda', 'vegetarisch'));

header ( 'Content-Type: application/json' );
echo $jsonOut;
?>