<?php
require 'phpMQTT.php';
$openings = include 'openings.txt'; 
$no_of_openings_to_store = 10;
$url = parse_url("broker.hivemq.com:1883");
$topic = "door31071993";
$client_id = "310719933";
function procmsg($topic, $msg){
    global $openings;
    global $no_of_openings_to_store;
    if (sizeof($openings) > $no_of_openings_to_store) {
        $openings = array_slice($openings, -$no_of_openings_to_store);
    }
    array_push($openings, ($msg . ":" . date("d, M, y - H:i:s", time())));
    $result = print_r($openings, true);
    file_put_contents('/var/www/html/openings.txt', '<?php return ' . var_export($openings, true) . ';');
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