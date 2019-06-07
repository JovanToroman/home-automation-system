<!DOCTYPE html>
<html>
    <header>
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script>
        function clearInfo() {
            document.getElementById("info").remove();
            document.getElementById("clearButt").remove();
        }
        function clearInfo2() {
            document.getElementById("info2").remove();
            document.getElementById("clearButt2").remove();
            }
        </script>
    </header>
    <body>
    <div class="container col-md-2" style="margin-top:100px;">
    <nav>
    <ul class="nav flex-column">
        <li class="nav-item">
        <a class="nav-link active" href="#">Home page</a>
        </li>
        <li class="nav-item">
        <a class="nav-link" href="/temperature.php">Temperature</a>
        </li>
        <li class="nav-item">
        <a class="nav-link" href="/door_openings.php">Door openings</a>
        </li>
    </ul>
    </nav>
    </div>
    <div class="container col-md-2">
    <ul class="nav flex-column">
    <li class="nav-item">
    <div>
    <h3>Homepage</h3>
	<form action="/toggle_lightbulb.php">
		<input class="btn btn-default btn-block" style="margin-top: 10px;" type="submit" value="Toggle lightbulb">
	</form>
    <?php
    sleep(2);
    $mysqli = new mysqli('localhost', 'jovan', 'password', 'home');
    $query_state = "SELECT * FROM lightbulbstate WHERE TIME(`timestamp`) = (SELECT MAX(TIME(`timestamp`)) FROM lightbulbstate)";
    $lightbulb_state = "State not available";
    if(!$result = $mysqli->query($query_state)) {
    echo "Query failed with message: " . $mysqli->error . " and 
    error code " . $mysqli->errno;
    }elseif ($result->num_rows === 0) {
        echo "No information about light bulb state";
    } else {
        $lightbulb_state = $result->fetch_assoc()['state'];
    }
    

    if($lightbulb_state == 1){
            echo "<h5 style='color:green;'>Lightbulb is currently ON</h5>";
    } elseif($lightbulb_state == 0) {
            echo "<h5 style='color:red;'>Lightbulb is currently OFF</h5>";
    } else {
        echo "<h5>Lightbulb is not functioning properly.</h5>";
    }

    if($_GET['toggleSuccessful'] == 1){
            echo "<h5 id='info'>Info: Toggle lightbulb signal sucessfully sent</h5><button id='clearButt' class='btn' onclick='clearInfo();' type='button'>clear</button>";
    } elseif ($_GET['toggleSuccessful'] != NULL){
            echo "<h5 id='info'>Info: Toggle lightbulb signal sending failed</h5><button id='clearButt' class='btn' onclick='clearInfo();' type='button'>clear</button>";
    } ?>
    </div>
    </li>
    <li class="nav-item">
    <div>
	<form action="/temperature.php">
		<input class="btn btn-default btn-block" style="margin-top: 10px;" type="submit" value="Show temperature">
	</form>
    <?php
    $query_temp = "SELECT * FROM temperature WHERE TIME(`timestamp`) = (SELECT MAX(TIME(`timestamp`)) FROM temperature)";
    $temp = "Temperature not available";
    if(!$result = $mysqli->query($query_temp)) {
        echo "Query failed with message: " . $mysqli->error . " and 
        error code " . $mysqli->errno;
    } elseif ($result->num_rows === 0) {
        echo "No information about temperature";
    } else {
        $temp = $result->fetch_assoc()['value'];
    }
    echo "<h5>Last measured temperature was " . $temp . " C</h5>";?>
    </div>
    </li>
    <li class="nav-item">
    <div>
    <form  action="/door_openings.php">
		<input class="btn btn-default btn-block" style="margin-top: 10px;" type="submit" value="Show door openings">
	</form>
    <?php
    $query_opening = "SELECT * FROM dooropening WHERE TIME(`timestamp`) = (SELECT MAX(TIME(`timestamp`)) FROM dooropening)";
    $door_state = "Door opening info not available";
    if(!$result = $mysqli->query($query_opening)) {
        echo "Query failed with message: " . $mysqli->error . " and 
        error code " . $mysqli->errno;
    } elseif ($result->num_rows === 0) {
        echo "No information about door openings";
    } else {
        $door_state = $result->fetch_assoc()['event'];
    }
    if(trim($door_state) == 'closed'){
            echo "<h5 style='color:green;'>Doors are currently CLOSED</h5>";
    } elseif(trim($door_state) == 'opened') {
            echo "<h5 style='color:red;'>Doors are currently OPEN</h5>";
    } ?>
    </div>
    </li>
    <li class="nav-item">
    <div>
	<form action="/restart.php">
		<input class="btn btn-danger btn-block" style="margin-top: 10px;" type="submit" value="Restart system">
	</form>
    <?php
    if($_GET['restartSuccessful'] == 1){
            echo "<h5 id='info2'>Info: Restart signal sucessfully sent</h5><button id='clearButt2' class='btn' onclick='clearInfo2();' type='button'>clear</button>";
    } elseif ($_GET['restartSuccessful'] != NULL){
            echo "<h5 id='info2'>Info: Restart signal sending failed</h5><button id='clearButt2' class='btn' onclick='clearInfo2();' type='button'>clear</button>";
    } ?>
    </div>
    </li>
    </ul>
    </div>
    </body>
</html>
