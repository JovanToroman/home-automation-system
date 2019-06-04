<?php
require 'phpMQTT.php';

$url = parse_url("broker.hivemq.com:1883");
$topic = "lightbulb31071993";
$client_id = "310719932";

$message = "toggle31071993";

$mqtt = new Bluerhinos\phpMQTT($url['host'], $url['port'], $client_id);
if ($mqtt->connect(true, NULL, "", "")) {
    $mqtt->publish($topic, $message, 0);
    echo "Lightbulb toggle signal sent";
    $mqtt->close();
    header('Location: index.php?toggleSuccessful=1');
    die();
}else{
    header('Location: index.php?toggleSuccessful=0');
    die();
}
