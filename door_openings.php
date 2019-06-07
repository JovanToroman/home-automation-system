<?php
if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}
$no_of_records_per_page = 10;
$offset = ($pageno-1) * $no_of_records_per_page;
$mysqli = new mysqli('localhost', 'jovan', 'password', 'home');
if($_GET['dateFrom'] == NULL && $_GET['dateTo'] == NULL){
    $init_vals = "SELECT * FROM dooropening WHERE DATE(`timestamp`) = CURDATE() LIMIT $offset, $no_of_records_per_page";
    $total_pages_sql = "SELECT COUNT(*) FROM dooropening WHERE DATE(`timestamp`) = CURDATE()";
} elseif ($_GET['dateFrom'] != NULL && $_GET['dateTo'] == NULL){
    $init_vals = "SELECT * FROM dooropening WHERE DATE(`timestamp`) >= DATE('" . $_GET['dateFrom'] . "') LIMIT $offset, $no_of_records_per_page";
    $total_pages_sql = "SELECT COUNT(*) FROM dooropening WHERE DATE(`timestamp`) >= DATE('" . $_GET['dateFrom'] . "')";
} elseif ($_GET['dateFrom'] == NULL && $_GET['dateTo'] != NULL){
    $init_vals = "SELECT * FROM dooropening WHERE DATE(`timestamp`) <= DATE('" . $_GET['dateTo'] . "') LIMIT $offset, $no_of_records_per_page";
    $total_pages_sql = "SELECT COUNT(*)  FROM dooropening WHERE DATE(`timestamp`) <= DATE('" . $_GET['dateTo'] . "')";
} else {
    $init_vals = "SELECT * FROM dooropening WHERE DATE(`timestamp`) <= DATE('" . $_GET['dateTo'] 
    . "') AND DATE(`timestamp`) >= DATE('" . $_GET['dateFrom'] . "') LIMIT $offset, $no_of_records_per_page";
    $total_pages_sql = "SELECT COUNT(*) FROM dooropening WHERE DATE(`timestamp`) <= DATE('" . $_GET['dateTo'] 
    . "') AND DATE(`timestamp`) >= DATE('" . $_GET['dateFrom'] . "')";
}

$conn=mysqli_connect('localhost', 'jovan', 'password', 'home');
// Check connection
if (mysqli_connect_errno()){
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die();
}

//$total_pages_sql = "SELECT COUNT(*) FROM table";
$result = mysqli_query($conn,$total_pages_sql);
$total_rows = mysqli_fetch_array($result)[0];
$total_pages = ceil($total_rows / $no_of_records_per_page);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<header>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</header>
<body>
    <div class="container col-md-2" style="margin-top:100px;">
        <nav>
        <ul class="nav flex-column">
            <li class="nav-item">
            <a class="nav-link" href="/index.php">Home page</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="/temperature.php">Temperature</a>
            </li>
            <li class="nav-item">
            <a class="nav-link active" href="#">Door openings</a>
            </li>
        </ul>
        </nav>
    </div>
<div class="container col-md-3">
<h3>Door openings</h3>
<h4>Filter data</h4>
    <form action="/door_openings.php">
        <div class="form-group">
            <label for="dateFrom">Date from:</label>
            <input type="date" class="form-control" id="dateFrom" name="dateFrom">
        </div>
        <div class="form-group">
            <label for="dateTo">Date to:</label>
            <input type="date" class="form-control" id="dateTo" name="dateTo">
        </div>
        <input class="btn btn-default btn-block" style="margin-top: 10px;" type="submit" value="Apply">
    </form>
<table class="table">
<thead>
<tr>
  <th scope="col">#</th>
  <th scope="col">Event</th>
  <th scope="col">Time</th>
</tr>
</thead>
<tbody>
<?php
$count = ($pageno - 1) * $no_of_records_per_page;
if(!$result = $mysqli->query($init_vals)) {
    echo "Query failed with message: " . $mysqli->error . " and 
    error code " . $mysqli->errno;
}
if ($result->num_rows === 0) {
    echo "No door events today";
}
while ($row = $result->fetch_assoc()) {
$count += 1;
?>
  <tr>
    <th scope="row"><?php echo $count; ?></th>
    <td><?php
    print $row['event']; ?></td>
    <td><?php print $row['timestamp']; ?></td>
  </tr>
<?php } ?>
</tbody>
<ul class="pagination">
    <li><a href="?pageno=1&dateFrom=<?php echo $_GET['dateFrom'] . "&dateTo=" . $_GET['dateTo']; ?>">First</a></li>
    <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
        <a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1) . "&dateFrom=" . $_GET['dateFrom'] . "&dateTo=" . $_GET['dateTo']; } ?>">Prev</a>
    </li>
    <li class="<?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
        <a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1) . "&dateFrom=" . $_GET['dateFrom'] . "&dateTo=" . $_GET['dateTo']; } ?>">Next</a>
    </li>
    <li><a href="?pageno=<?php 
    if ($total_pages != 0) {
        echo $total_pages;
    } else {
        echo 1;
    }
    echo "&dateFrom=" . $_GET['dateFrom'] . "&dateTo=" . $_GET['dateTo'];
     ?>">Last</a></li>
</ul>
</table>
</div>
</body>
</html>
