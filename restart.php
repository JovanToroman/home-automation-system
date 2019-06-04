<?php
require 'phpMQTT.php';

$url = parse_url("broker.hivemq.com:1883");
$topic = "restart31071993";
$topic2 = "lightbulb31071993";
$client_id = "310719939";

$message = "restart";

$mqtt = new Bluerhinos\phpMQTT($url['host'], $url['port'], $client_id);
if ($mqtt->connect(true, NULL, "", "")) {
    $mqtt->publish($topic, $message, 0);
    $mqtt->publish($topic2, $message, 0);
    echo "Restart signal sent";
    $mqtt->close();
    header('Location: index.php?restartSuccessful=1');
    die();
}else{
    header('Location: index.php?restartSuccessful=0');
    die();
}
