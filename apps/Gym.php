<!DOCTYPE HTML>
<head>
<meta charset="utf-8">
<title>Jeff's Gym Schedule</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<!-- Le styles -->
<link href="/WebApps/bootstrap/css/bootstrap.css" rel="stylesheet">
<style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
	.commentClass { white-space: pre-line}
	
	
	.tt-hint {  height:50px; font-size: 1.8em; }
	.tt-hint { color:#b0b0b0}
	.tt-dropdown-menu 
	{ 
		margin-top:0px;
		border:2px solid #b0b0b0;
		border-radius:5px;	
		background-color:#f0f0f0;	
		min-width:200px;		
	}
	.tt-suggestion { 
		color:#404040;
		font-family:Helvetica;font-size:0.9em;
		background-color: #fafafa; 
		padding:5px;
		border:1px solid #c0c0c0;
		margin:5px;
	}
	.tt-suggestion p { margin-bottom:0 }
	.tt-is-under-cursor
	{
		background-color: #f5c0c0;
		border:1px solid #9f744e;
	}
		
		
  	.sidebar-nav {
        padding: 9px 0;
      }
      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
</style>
<link href="/WebApps/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->
<!-- Fav and touch icons -->
<!-- <link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">
<link rel="shortcut icon" href="../assets/ico/favicon.png"> -->
</head>
<body>
	<div class='container'>
		<div class='row-fluid'>
			<h1>Workout Routines</h1>
			<div style='float:left'>
				<h4>A</h4>
				<div class='span8'>
					<ul>
						<li>Barbell Squat 3x5</li>
						<li>Barbell Bench 3x5</li>
						<li>Barbell Romanian Deadlift 1x5</li>
					</ul>
				</div>
			</div>	
			<div style='float:left'>
				<h4>B</h4>
				<div class='span8'>
					<ul>
						<li>Barbell Squat 3x5</li>
						<li>Barbell Standing Overhead Press 3x5</li>
						<li>Barbell Row 3x5</li>
					</ul>
				</div>
			</div>
			<div style='clear:both'></div>
			<a href='http://www.youtube.com/watch?v=c8aJM24CCqI'>Deadlift</a><br />
			<a href='http://www.youtube.com/watch?v=sJZdo9p5x_E'>Barbell Squat</a><br />
			<a href='http://www.youtube.com/watch?v=gVpg3ChPzrs'>Squat Bar position</a><br />
			<a href='http://www.youtube.com/watch?v=muygfIwzDmw'>Overhead Press</a><br />
			<a href='http://www.menshealth.com/fitness/stretching-exercises/page/2'>Stretches</a>
		</div>
		<div class='row-fluid'>
		</div>
		<div class='row-fluid'>
			<h1>Add a workout</h1>
			<div class='span10'>
				<input id='txtActivity' type='text' placeholder='Activity' autocapitalize="off" />
				<!-- <select id='ddlActivity'>
				</select> -->
				<br />
				<input id='txtWeight' type='text' placeholder='Weight' pattern="\d*" />
				<!-- <input id='txtSets' type='text' placeholder='Sets' /> -->
				<br />
				<label style='display:inline'>Reps:</label>
				<select id='ddlReps'>
				</select>
				<div>
					<button id='btnAddEntry' class="btn btn-primary" type="button">Submit</button>
				</div>
			</div>	
		</div>
		<div class='row-fluid'>
			<h1>Past Workouts</h1>
			<div class='span10'>
				<table id='tblLog' class='table table-bordered'>

				</table>
			</div>	
		</div>
	</div>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
	<script src="/WebApps/bootstrap/js/bootstrap.min.js"></script>
	<script src='../js/typeahead.js/typeahead.js'></script>
	<script src='../js/typeahead.js/hogan.js'></script>
	<script type='text/javascript' src='../js/jefftron.js'></script>
	<script type='text/javascript'>
	<?php
		require_once "./_appServices/utils.php";
		$s = $_COOKIE['jefftronsessionid'];
		if (!isset($s) || !isValidSession($s)) {
			$s = getSessionByAppAndPassword('Gym Page', getSessionPw());
			echo "JEFFTRON.setCookie('jefftronsessionid', '$s', 90);";
		}

		echo "var jefftronsessionid = $s;";
		echo "\n";
	?>
	</script>
	
	<script type='text/javascript'>
	
		$(function() {
			loadWorkouts();
			$('#btnAddEntry').click(addEntry);
			configureActivityTypeahead();
			configureSetsAndReps();
//			configureActivityDropdown();

		});
		
		function configureSetsAndReps() {
			for (var i = 1; i <= 5; i++) {
				// $('#ddlSets').append('<option value="'+i+'">'+i+'</option>');
				var repsOffset = 1;
				var reps = 2*(i+repsOffset);
				$('#ddlReps').append('<option value="'+reps+'">'+reps+'</option>');
			}
		}
		
		function configureActivityDropdown() {
			$.getJSON("_appServices/DataService.php", {method:'GET', function:'gymactivitylist',sessionid:jefftronsessionid}, function( json ) {
				var ddl = $('#ddlActivity');
				ddl.html('');
				for(var i = 0; i < json.length; i++) {
					ddl.append('<option value="'+json[i].value+'">'+json[i].value+'</option>');
				}
			});
		}
		
		function configureActivityTypeahead() {
			$('#txtActivity').typeahead([
				{
			    name: 'x',
			    prefetch: {
					url: '/WebApps/_appServices/DataService.php?method=GET&function=gymactivitylist',
					ttl: 3000
				},
			    remote: '/WebApps/_appServices/DataService.php?method=GET&function=gymactivitylist',
			    template: '<p>{{value}}</p>',
	   		    engine: Hogan
			  }]);			
		}
		
		function loadWorkouts() {
			$.getJSON("_appServices/DataService.php", {method:'GET', function:'gymactivitylog',sessionid:jefftronsessionid}, function( json ) {
				
				$('#tblLog').html('');
				var headerRow = $('<tr></tr>');
				headerRow.append($('<td>Date</td>'));
				headerRow.append($('<td>Activity</td>'));
				// headerRow.append($('<td>Sets</td>'));
				headerRow.append($('<td>Reps</td>'));
				headerRow.append($('<td>Weight (lbs)</td>'));
				$('#tblLog').append(headerRow);
				
				for (var i = 0; i < json.length; i++) {
					var tr = $('<tr></tr>');
					
					tr.append($('<td>'+json[i].timestamp+'</td>'));
					tr.append($('<td>'+json[i].activity+'</td>'));
					// tr.append($('<td>'+json[i].sets+'</td>'));
					tr.append($('<td>'+json[i].reps+'</td>'));
					tr.append($('<td>'+json[i].weight+'</td>'));

					$('#tblLog').append(tr);	
				}
			});
		}
		
		function addEntry() {
			
			var repsVal = $('#ddlReps').val();
			var setsVal = 1; //$('#ddlSets').val();
			
			$.getJSON("_appServices/DataService.php", 
			{
				method:'SET', function:'gymactivitylog',
				sessionid:jefftronsessionid, 
				activity:$('#txtActivity').val(),
				weight:$('#txtWeight').val(),
				sets:setsVal,
				reps:repsVal
			}, function( json ) {
					//$('#txtReps,#txtActivity,#txtWeight,#txtSets').val('');
					loadWorkouts();		
			});
		}
		
	</script>
</body>