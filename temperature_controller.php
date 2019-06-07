<?php
require 'phpMQTT.php';
$url = parse_url("broker.hivemq.com:1883");
$topic = "temperature31071993";
$client_id = "310719931";
$temperature_alert = false;
$alert_val = 30;
$user_email = "toromanj@gmail.com";

$mysqli = new mysqli('localhost', 'jovan', 'password', 'home');
if($mysqli->connect_errno) {
	echo "Database connection failed";
}

function procmsg($topic, $msg){
    global $alert_val;
    global $user_email;
    global $temperature_alert;
    
    handle_alert(intval($msg));

    global $mysqli;
    
    $sql = "INSERT INTO `temperature`(`value`) VALUES('$msg')";
    if(!$result = $mysqli->query($sql)) {
        echo "Query failed with message: " . $mysqli->error . " and 
        error code " . $mysqli->errno;
    }
}

function handle_alert($temp) {
    global $alert_val;
    global $user_email;
    global $temperature_alert;
    if ($temp >= $alert_val) {
            if ($temperature_alert == false) {
                    print("Sending email\n");
                    $headers = "From: home@home.com";
                    mail($user_email, "Temperature alert", "Temperature in your home has exceded set threshold of " . $alert_val, $headers);
                    $temperature_alert = true;
            }
    } else {
            if ($temperature_alert == true) {
                    print("Sending email\n");
                    $headers = "From: home@home.com";
                    mail($user_email, "Temperature alert over", "Temperature in your home is back to normal", $headers);
                    $temperature_alert = false;
            }
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
