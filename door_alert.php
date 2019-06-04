<?php
$door_alert = include 'door_alert_active.txt';
$file = fopen('door_alert_active.txt', "w");
echo $door_alert;
//if($door_alert == 'no'){
        fwrite($file, 'sadasdsdassadsda');
//} elseif($door_alert == 'yes'){
//        file_put_contents('/var/www/html/door_alert_active.txt', "<?php return 'no';");
//}
//header('Location: index.php');

?>