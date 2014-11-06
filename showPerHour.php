<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
		
<?php 
/**
* showPerHour.php
* Displays the data of today per hour
* @author Timea Turdean <timea.turdean@gmail.com>
* @version BETA 0.50
*/
require_once('db_lib.php');
$oDB = new db;
// here we store our results to be put into JavaScript variables
$outputPerDay = array();
$startDate = -1;
//-------- EDIT THIS with your own DB ------------------
// datetime field in the table
$datetimeField = 'created_at';
//table where the datetime field is part of
$table = 'tweets';

if(isset($_POST['selectToday'])){
	
	$startDate = date("Y-m-d")." 00:00:00";	
	$currentDate = date("Y-m-d H:i:s");
	
	$query = "SELECT ". $datetimeField ." FROM ". $table ." WHERE ". $datetimeField ." BETWEEN '" . $startDate ."' AND '". $currentDate."';";
	
	$result = $oDB->select($query);
	selectPerHour($result);
	$len = $result->num_rows;
	$result->close();
}

if(isset($_POST['yesterday'])){
	
	$currentDate = $_POST['currentDate'];	
	$date = new DateTime($currentDate);
	date_sub($date,date_interval_create_from_date_string("1 day"));
	$startDate = date_format($date,"Y-m-d")." 00:00:00";
	
	$query = "SELECT ". $datetimeField ." FROM ". $table ." WHERE ". $datetimeField ." BETWEEN '" . $startDate ."' AND '". $currentDate."';";
	
	$result = $oDB->select($query);
	selectPerHour($result);
	$len = $result->num_rows;
	$result->close();
}

function selectPerHour($result) {
	if ($row = mysqli_fetch_row($result)) {
		while ($row = mysqli_fetch_row($result)) {
			$data_day = date("Y-m-d H:i:s", strtotime($row[0]));
			$dd = date( "H", strtotime( $data_day));
			for ($x=0; $x<=9; $x++) {
				if ("0".$x == "".$dd) 
					$outputPerHour[$x] = $outputPerHour[$x]+1;
				else
					$outputPerHour[$x] = $outputPerHour[$x]+0;
			} 
			for ($x=10; $x<24; $x++) {
				if ("".$x == "".$dd) 
					$outputPerHour[$x] = $outputPerHour[$x]+1;
				else
					$outputPerHour[$x] = $outputPerHour[$x]+0;
			} 
		}
	}
?>	
<script type="text/javascript">
var startDate = "<?php echo $startDate; ?>";
var dataset = [ 
<?php
if (!empty($outputPerHour)) {
	for ($x=0; $x<24; $x++) {
		echo $outputPerHour[$x];
		echo ',';
	}
	echo $outputPerHour[$x];
}
?>
];
</script>
<?php
}
?>

	<div id="header">
			<div id="headerText">
				<h1 id="title">Tweets downloaded per hour - today</h1>
				<h2 id="subtitle">by Timea Turdean</h2>
		</div>
	</div>
	<div id="middle">
		<p id="subsubtitle">Data between: <?php echo $startDate ?> and <?php echo $currentDate ?>.</p>
		<br>
		<form id="form1" method="post" action="showPerHour.php">
		<input type="text" style="display:none;" id='curretDate' class='currentDate' value="" name="currentDate"></input>
		<a class='myButton' href="statistics.html">Go to statistics page</a>
		<input type="submit" id='yesterday' class='myButton' name="yesterday" value='Yesterday'></input>
		</form>
		<br>
		<br>
		<div id="chart"></div>
	</div>

	<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
	
	<script type="text/javascript">	

	  document.forms['form1'].elements['currentDate'].value =  startDate;
		
		var x = d3.scale.linear()
				.domain([0, d3.max(dataset)])
				.range([5, 600]);
						
		var items = d3.select("#chart")
			.selectAll("div")
				.data(dataset);
				
	  var wrappers = items.enter().append("div");
				
		wrappers.append("div")
        	.attr("class", "axisHour")
					.text(function(d,i){var s = i+":00-"+(i+1)+":00"; return s;});
						
		wrappers.append("div")
        	.attr("class", "bar")
					.style("width", 0)
					.text(function(d) { return d; })
					.on("click", function() {				
						d3.selectAll("div")
							d3.select(this).style("background-color", "orange");
					});
		
		items.select('div.bar')
			.transition()
			.style("width", function(d) { 
			var s = 0;
				if (x(d) > 40000)
					s = x(d)/10;
				     else
					s =  x(d);
			return s +"px";
			});
		
		items.exit().remove();
		
		if (dataset.length == 0)
			d3.select("#chart")
			.text("No values in this interval.");
						
	</script>
	
	</body>
</html>