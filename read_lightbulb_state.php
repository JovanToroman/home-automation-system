<?php
require 'phpMQTT.php';
$topic2= "lightbulb_status31071993";
$client_id = "310719937";
$url = parse_url("broker.hivemq.com:1883");

$mysqli = new mysqli('localhost', 'jovan', 'password', 'home');
if($mysqli->connect_errno) {
	echo "Database connection failed";
}

function procmsg($topic, $msg){
    global $mysqli;
    
    $sql = "INSERT INTO `lightbulbstate`(`state`) VALUES($msg)";
    if(!$result = $mysqli->query($sql)) {
        echo "Query failed with message: " . $mysqli->error . " and 
        error code " . $mysqli->errno;
    }
}
    
$mqtt = new Bluerhinos\phpMQTT($url['host'], $url['port'], $client_id);
if ($mqtt->connect(true, NULL, "", "")) {
  $topics[$topic2] = array(
      "qos" => 0,
      "function" => "procmsg"
  );
  $mqtt->subscribe($topics,0);
  while($mqtt->proc()) {

  }
  $mqtt->close();
} else {
  exit(1);
}
