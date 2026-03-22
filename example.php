<?php

//https://codeshack.io/event-calendar-php/

//https://dev.to/phuocng/create-an-event-calendar-20fg


include 'Calendar.php';
$calendar = new Calendar('2025-11-12');
$calendar->add_event('Birthday', '2025-11-03', 1, 'green');
$calendar->add_event('Doctors', '2025-11-04', 1, 'red');
$calendar->add_event('Holiday', '2025-11-16', 7);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Event Calendar</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link href="calendar.css" rel="stylesheet" type="text/css">
	</head>
	<body>
	    <nav class="navtop">
	    	<div>
	    		<h1>Event Calendar</h1>
	    	</div>
	    </nav>
		<div class="content home">
			<?=$calendar?>
		</div>
	</body>
</html>
