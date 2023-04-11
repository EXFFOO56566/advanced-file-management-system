<html>
<head>
	<title>Colorbox</title>
	<link rel="stylesheet" href="colorbox.css" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="colorbox.js"></script>
		<script>
			$(document).ready(function(){
		
				$(".group1").colorbox({rel:false});
				$("#click").click(function(){ 
					$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
					return false;
				});
			});
		</script>
</head>
<body>
		<h1>Colorbox Demonstration</h1>
		<h2>Elastic Transition</h2>
		<a class="group1" href="content/ohoopee3.jpg" title="On the Ohoopee as an adult">Grouped Photo 3</a>
</body>
</html>