<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<?php 
/**
* showPerDay.php
* Displays the data of this month per day
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

if(isset($_POST['selectThisMonth'])){

	$startDate = date("Y-m")."-01 00:00:00";	
	$currentDate = date("Y-m-d H:i:s");
	
	
	$query = "SELECT ". $datetimeField ." FROM ". $table ." WHERE ". $datetimeField ." BETWEEN '" . $startDate ."' AND '". $currentDate."';";
	
	$result = $oDB->select($query);
	selectPerDay($result);
	$len = $result->num_rows;
	$result->close();
}

if(isset($_POST['lastMonth'])){
	
	$currentDate = $_POST['currentDate'];	
	$date = new DateTime($currentDate);
	date_sub($date,date_interval_create_from_date_string("30 days"));
	$startDate = date_format($date,"Y-m")."-01 00:00:00";
	
	$date = new DateTime($currentDate);
	date_sub($date,date_interval_create_from_date_string("1 day"));
	$currentDate = date_format($date,"Y-m-d")." 00:00:00";
			
	$query = "SELECT ". $datetimeField ." FROM ". $table ." WHERE ". $datetimeField ." BETWEEN '" . $startDate ."' AND '". $currentDate."';";
	
	$result = $oDB->select($query);
	selectPerDay($result);
	$len = $result->num_rows;
	$result->close();
}

function selectPerDay($result) {
	if ($row = mysqli_fetch_row($result)) {
		while ($row = mysqli_fetch_row($result)) {
			$data_day = date("Y-m-d H:i:s", strtotime($row[0]));
			$dd = date( "d", strtotime( $data_day));
			for ($x=0; $x<=30; $x++) {
				if ("".$x == "".$dd) 
					$outputPerDay[$x] = $outputPerDay[$x]+1;
				else
					$outputPerDay[$x] = $outputPerDay[$x]+0;
			} 
		}
	}
}
?>

<script type="text/javascript">
var startDate = "<?php echo $startDate; ?>";
var dataset = [ 
<?php
if (!empty($outputPerDay)) {
	for ($x=0; $x<30; $x++) {
		echo $outputPerDay[$x];
		echo ',';
	}
	echo $outputPerDay[$x];
}
?>
];
</script>

	<div id="header">
			<div id="headerText">
				<h1 id="title">Tweets downloaded this month per day</h1>
				<h2 id="subtitle">by Timea Turdean</h2>
		</div>
	</div>
	<div id="middle">
		<p id="subsubtitle">Data between: <?php echo $startDate ?> and <?php echo $currentDate ?>.</p>
		<br>
		<form id="form1" method="post" action="showPerDay.php">
		<input type="text" style="display:none;" id='curretDate' class='currentDate' value="" name="currentDate"></input>
		<a class='myButton' href="statistics.html">Go to statistics page</a>
		<input type="submit" id='lastMonth' class='myButton' name="lastMonth" value='Last month'></input>
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
        	.attr("class", "axis")
					.text(function(d,i){return (i+1)+" day";});
						
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
					s = x(d)/2;
				     else
					s =  x(d);
			return s+"px";
			});
		
		items.exit().remove();
		
		if (dataset.length == 0)
			d3.select("#chart")
			.text("No values in this interval.");		
						
	</script>
	
	</body>
</html>