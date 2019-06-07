<?php
require 'phpMQTT.php';
$url = parse_url("broker.hivemq.com:1883");
$topic = "door31071993";
$client_id = "310719933";

$mysqli = new mysqli('localhost', 'jovan', 'password', 'home');

if($mysqli->connect_errno) {
	echo "Database connection failed";
}

$query_lightbulb_state = "SELECT * FROM lightbulbstate WHERE TIME(`timestamp`) = (SELECT MAX(TIME(`timestamp`)) FROM lightbulbstate)";
    
 
function send_lightbulb_toggle_signal() {
    global $url;
    $topic = "lightbulb31071993";
    $client_id = "3107199311";

    $message = "toggle31071993";

    $mqtt = new Bluerhinos\phpMQTT($url['host'], $url['port'], $client_id);
    if ($mqtt->connect(true, NULL, "", "")) {
        $mqtt->publish($topic, $message, 0);
        echo "Lightbulb toggle signal sent\n";
        $mqtt->close();
    } else{
        echo "Lightbulb toggle signal sending failed\n";
    }   
}
    
function get_lightbulb_state() {
    global $query_lightbulb_state;
    global $mysqli;
    $lightbulb_state = NULL;
    if(!$result = $mysqli->query($query_lightbulb_state)) {
        echo "Query failed with message: " . $mysqli->error . " and 
        error code " . $mysqli->errno . "\n";
    }elseif ($result->num_rows === 0) {
        echo "No information about light bulb state\n";
    } else {
        $lightbulb_state = $result->fetch_assoc()['state'];
    }
    return $lightbulb_state;
}

function toggle_lightbulb($msg) {
    $lightbulb_state = get_lightbulb_state();
    if (substr($msg, 0, 6) == "opened") {
        send_lightbulb_toggle_signal();
    }
}

function procmsg($topic, $msg){
    global $mysqli;
    
    toggle_lightbulb($msg);
    
    $sql = "INSERT INTO `dooropening`(`event`) VALUES('$msg')";
    if(!$result = $mysqli->query($sql)) {
        echo "Query failed with message: " . $mysqli->error . " and 
        error code " . $mysqli->errno;
    }
}
    
$mqtt = new Bluerhinos\phpMQTT($url['host'], $url['port'], $client_id);
if ($mqtt->connect(true, NULL, "", "")) {
  $topics[$topic] = array(
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
?>
