<<<<<<< HEAD
<?php
// Daten aus dem Request holen
$entityBody = file_get_contents ( 'php://input' );
 
// Datei namens echo.log öffen 
$datei_handle = fopen ( "echo.log", "a+" );
 
// etwas validierung      
if (is_string($entityBody ) && json_decode ( $entityBody ) != null) {
// Request schoen formatieren
   fputs ( $datei_handle, "\n" );
   fputs ( $datei_handle, json_encode ( json_decode ( $entityBody ), JSON_PRETTY_PRINT ) );
   fputs ( $datei_handle, "\n" );
}
// alles speichern und beenden      
fclose ( $datei_handle );
?>
=======
<?php
require_once('data.php');
require_once('alexa.php');

$mensa = new MensaClass();
$alexa = new AlexaClass();

$jsonOut = $alexa->alexaJsonOutTitleAfterFilter($mensa->filter('2017-12-07', 'fulda', 'vegetarisch'));

header ( 'Content-Type: application/json' );
echo $jsonOut;
?>
>>>>>>> f1fe42de0b7830cc4ade99d3b024134a96e8f0e2
