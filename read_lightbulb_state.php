<?php
require 'phpMQTT.php';
$state = include 'lightbulb_state.txt'; 
$no_of_openings_to_store = 10;
$topic2= "lightbulb_status31071993";
$client_id = "310719937";
$url = parse_url("broker.hivemq.com:1883");


function procmsg($topic, $msg){
    global $state;
    $state = trim(substr($msg, 0, 3)) ;
    $result = print_r($state, true);
    file_put_contents('/var/www/html/lightbulb_state.txt', '<?php return ' . var_export($state, true) . ';');
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