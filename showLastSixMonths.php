<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
		
<?php 
/**
* showLastSixMonths.php
* Displays the data of the last 6 months
* @author Timea Turdean <timea.turdean@gmail.com>
* @version BETA 0.50
*/
require_once('db_lib.php');
$oDB = new db;
// here we store our results to be put into JavaScript variables
$outputPerMonth = array();
//-------- EDIT THIS with your own DB ------------------
// datetime field in the table
$datetimeField = 'created_at';
//table where the datetime field is part of
$table = 'tweets';


if(isset($_POST['selectLastMonths'])){
	
	$currentDate = date("Y-m-d H:i:s");
	$date = new DateTime("now");
	date_sub($date,date_interval_create_from_date_string("156 days"));
	$startDate = date_format($date,"Y-m")."-01 00:00:00";
			
	for ($x=0; $x<6; $x++) {
		$date = new DateTime("now");
		date_sub($date,date_interval_create_from_date_string((156-$x*31)." days"));
		$firstDateStart = date_format($date,"Y-m")."-01 00:00:00";
		$firstDateEnd = date_format($date,"Y-m")."-31 23:59:59";
		
		$query = "SELECT count(*) FROM ". $table ." WHERE ". $datetimeField ." BETWEEN '" . $firstDateStart ."' AND '". $firstDateEnd."';";

		$result = $oDB->select($query);
		if ($row = mysqli_fetch_row($result)) 
			$outputPerMonth[$x] = $row[0];
		else 
			$outputPerMonth[$x] = 0;
	}
}
?>
<script type="text/javascript">
var dataset = [ 
<?php
if (!empty($outputPerMonth)) {
	for ($x=0; $x<6; $x++) {
		echo $outputPerMonth[$x];
		echo ',';
	}
	echo $outputPerMonth[$x];
}
?>
];
</script>

	<div id="header">
			<div id="headerText">
				<h1 id="title">Tweets downloaded the last months</h1>
				<h2 id="subtitle">by Timea Turdean</h2>
		</div>
	</div>
	<div id="middle">
	<p id="subsubtitle">Data between: <?php echo $startDate ?> and <?php echo $currentDate ?>.</p>
	<br><a class='myButton' href="statistics.html">Go to statistics page</a>
	<br>
	<br>
	<div id="chart"></div>
	</div>

	<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
	
	<script type="text/javascript">
		
		var x = d3.scale.linear()
				.domain([0, d3.max(dataset)])
				.range([5, 600]);
				
		var items = d3.select("#chart")
			.selectAll("div")
				.data(dataset);
				
		var wrappers = items.enter().append("div");
				
		wrappers.append("div")
        	.attr("class", "axisMonth")
					.text(function(d,i){
					var s = i+6; 
					if (s>12) 
						s = s-12;
					return s+" month";});
						
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
			if (x(d) < 1000 & x(d) != 0) 
				return x(d)*2 + "px";
			else
				return x(d) + "px";});
		
		items.exit().remove();
		
		if (dataset.length == 0)
			d3.select("#chart")
			.text("No values in this interval.");	
		
	</script>
	
	</body>
</html>