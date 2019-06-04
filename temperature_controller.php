<?php
require 'phpMQTT.php';
$temperatures = include 'temperatures.txt';
$no_of_temps_to_store = 10;
$url = parse_url("broker.hivemq.com:1883");
$topic = "temperature31071993";
$client_id = "310719931";
$temperature_alert = false;
$alert_val = 30;
$user_email = "toromanj@gmail.com";

function procmsg($topic, $msg){
    global $temperatures;
    global $no_of_temps_to_store;
    global $alert_val;
    global $user_email;
    global $temperature_alert;
    $temp_int = intval($msg);
    if ($temp_int >= $alert_val) {
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
    if (sizeof($temperatures) > $no_of_temps_to_store) {
            $temperatures = array_slice($temperatures, -$no_of_temps_to_store);
    }
    array_push($temperatures, ($msg . ":" . date("d, M, y - H:i:s", time())));
    $result = print_r($temperatures, true);
    file_put_contents('/var/www/html/temperatures.txt', '<?php return ' . var_export($temperatures, true) . ';');
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