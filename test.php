<?php
require_once('data.php');

$mensa = new MensaClass();
$food = $mensa->filter('2017-12-04', 'fulda', 'vegetarisch');

?>