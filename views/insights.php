<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Facebook Insights Library</title>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/jquery-ui.min.js"></script>
	<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/themes/base/jquery-ui.css" />
	<link href='http://fonts.googleapis.com/css?family=Oxygen' rel='stylesheet' type='text/css'>

	<script type="text/javascript">
		$(function() {
			var dates = $( "#datepicker_from, #datepicker_until" ).datepicker({
				changeMonth     : true,
				numberOfMonths  : 3,
				beforeShow: customRange,
				onSelect        : function( selectedDate ) {
					$( "#datepicker_until" ).datepicker( "option", "maxDate", "+1w" );
				}
			});

			function customRange(input) { 
				var min = new Date(2008, 11 - 1, 1), //Set this to your absolute minimum date
					dateMin = min,
					dateMax = null,
					dayRange = 6; // Set this to the range of days you want to restrict to

				if (input.id === "datepicker_from") {
					if ($("#datepicker_until").datepicker("getDate") != null) {
						dateMax = $("#datepicker_until").datepicker("getDate");
						dateMin = $("#datepicker_until").datepicker("getDate");
						dateMin.setDate(dateMin.getDate() - dayRange);
						if (dateMin < min) {
							dateMin = min;
						}
					}
					else {
						dateMax = new Date; //Set this to your absolute maximum date
					}                      
				}
				else if (input.id === "datepicker_until") {
					dateMax = new Date; //Set this to your absolute maximum date
					if ($("#datepicker_from").datepicker("getDate") != null) {
						dateMin = $("#datepicker_from").datepicker("getDate");
						var rangeMax = new Date(dateMin.getFullYear(), dateMin.getMonth(),dateMin.getDate() + dayRange);

						if(rangeMax < dateMax) {
							dateMax = rangeMax; 
						}
					}
				}
				return {
					minDate: dateMin, 
					maxDate: dateMax
				};     
			}
		});
	</script>

	<style type="text/css">
		body {
			background:#fff;
			margin:0;
			padding:1em;
			font-size:14px;
			color:#1f1f1f;
			font-family: 'Oxygen', sans-serif;
		}

		h1, h2, h3, h4, h5 {
			margin:0;
			padding: 6px 0 6px 10px;
			border-left: 1px solid #444;
			background: #333;
			-moz-border-radius: 2px;
			-webkit-border-radius: 2px;
			border-radius: 2px;
			text-shadow: 1px 1px #222;
			font-size: 18px;
			color: #fff;
			width:810px;
		}
		.range {
			background:#eee;
			padding:6px;
			border-left:1px solid #ddd;
			border-right:1px solid #ddd;
			border-bottom:1px solid #ddd;
			margin:0 5px 30px 5px;
			width:795px;
		}
		.results {
			margin:1em 5px;
			width:810px;
		}
		.results h3 {
			font-size:15px;
		}
		table {
			width:100%;
			padding:2px;
			margin:0 auto 50px auto;
			border:1px solid #ddd;
		}
		table th {
			background:#eee;
			padding:4px;
			border-bottom:1px solid #ddd;
			text-align:left;
		}
		table th:first-child {
			border-left:1px solid #ddd;
		}
		table th:last-child {
			border-left:1px solid #ddd;
		}
		table td {
			border-bottom:1px solid #eee;
			padding:4px;
		}
		table td.sub {
			margin:0;
			padding: 6px 0 6px 10px;
			background: #666;
			text-shadow: 1px 1px #222;
			font-size: 14px;
			color: #fff;
		}
		table td.subtable {
			padding:0;
			margin:0;
			border-bottom:0;
		}
		table td table {
			width:100%;
			padding:0;
			margin:0;
		}
		table td table th {
			font-size:12px;
		}
		table td table td {
			font-size:12px;
		}
		.form {
		
		}
		.form form {
			width:810px;
		}
		.form p {
			border-bottom: 1px solid #DDD;
			background: #FCFCFC;
			margin: 0;
		}
		.form label {
			float: left;
			width: 200px;
			display: inline-block;
			padding: 20px;
			vertical-align: top;
			text-align: left;
			font-weight: bold;
		}
		.form span.field {
			margin-left: 220px;
			display: block;
			background: white;
			padding: 20px;
			border-left: 1px solid #DDD;
		}
		.form span.field input {
			border: 1px solid #CCC;
			background: #FCFCFC;
			padding: 8px 5px;
			width: 300px;
			-moz-border-radius: 2px;
			-webkit-border-radius: 2px;
			border-radius: 2px;
			-moz-box-shadow: inset 1px 1px 2px #ddd;
			-webkit-box-shadow: inset 1px 1px 2px #ddd;
			box-shadow: inset 1px 1px 2px #ddd;
			color: #666;
		}
		.form input.submit {
			margin:10px 0 0 325px;
			border: 1px solid #333;
			background: #333;
			color: white;
			cursor: pointer;
			padding: 7px 10px;
			font-weight: bold;
			-moz-border-radius: 2px;
			-webkit-border-radius: 2px;
			border-radius: 2px;
		}
	</style>	
</head>
<body>
	<div id="content">
		<?= $this->load->view($view); ?>
	</div>
</body>
</html>