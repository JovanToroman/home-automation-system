<?php
$openings = include 'openings.txt'; ?>
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
$count = 0;
foreach($openings as $open): 
$count += 1;
?>
  <tr>
    <th scope="row"><?php echo $count; ?></th>
    <td><?php 
    $vals = explode(":", $open);
    print $vals[0]; ?></td>
    <td><?php print $vals[1] . ":" . $vals[2] . ":" . $vals[3]; ?></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</body>
</html>