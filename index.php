<?php
include("./profile.php");

function getClientHeaders(){
	$headers = array();
	foreach ($_SERVER as $k => $v){
		if (substr($k, 0, 5) == "HTTP_"){
			// $k = str_replace('_', ' ', substr($k, 5));
			// $k = str_replace(' ', '-', ucwords(strtolower($k)));
			$headers[$k] = $v;
		}
	}
	return $headers;
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>TestPage</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0;">
	<script type="text/javascript" src="profile.js"></script>
	<style type="text/css">
		table {
			width: 100%;
			border: 0;
		}
		thead td, tfoot td {
			font-weight: bold;
			background: #ddd;
			border-bottom: 2px solid #ccc;
		}
		td { width: 33%; padding: .25em }
		tr { background: #eee;}
		dt {
			float:left;
			padding: .5em .25em;
			color:#999;
			
		}
		dd {
			padding: .5em;
			font-weight: bold;
		}
	</style>
</head>
<body>
	<!-- 
    ** begin header from client **
    <?php print_r(getClientHeaders()) ?>
	** end header **
    
    ** begin cookie from client **
    <?php echo urldecode($_SERVER['HTTP_COOKIE'])."\n" ?>
    ** end cookie **
	-->
	<h1>Device Profile</h1>
    <p><a href="http://github.com/bryanrieger/profile">Source available on Github</a></p>
	<?php
		echo "<h2>UA String</h2>";
		echo "<dl>";
		echo "<dt>ua string</dt><dd>".$_SERVER['HTTP_USER_AGENT']."</dd>";
		echo "<dt>matches</dt><dd>";
		if ($fragments) {
			$fragments = $GLOBALS['fragments'];
		
			foreach ($fragments as $f) {
				echo $f->match.", ";
			}
		}
		echo "</dd>";
		echo "</dl>";
	?>
	<h2>Features</h2>
    <table>
    <thead>
    	<tr><td>Feature</td><td>Server</td><td>Client</td></tr>
    </thead>
    <tbody id="features">
	<?php
    	foreach ($profile as $name => $value) {
			if (is_numeric($value)) {
				if($value == 1) {
         			$value = "true";
      			} elseif($value == 0) {
         			$value = "false";
   				}
			}
			echo "<tr id=".$name."><td>".$name."</td><td>".$value."</td></tr>";
		}
	?>
	</tbody>
    <tfoot>
    	<tr>
        	<td><a href="#" onclick="window.location.reload();">Reload</a></td>
        	<td></td>
        	<td><a href="#" onclick="clearProfile();">Clear profile</a></td>
        </tr>
    </tfoot>
    </table>
	<script type="text/javascript">
    	for (var property in profile) {
    		var pid = document.getElementById(property);
    		if (pid) {
    			var td = document.createElement('td');
    			td.innerHTML =  profile[property];
    			pid.appendChild(td);
    		} else {
    			var tr = document.createElement('tr');
    			var td1 = document.createElement('td')
    			var td2 = document.createElement('td')
    			var td3 = document.createElement('td')
    			td1.innerHTML = property;
    			tr.appendChild(td1);
    			tr.appendChild(td2);
    			td3.innerHTML = profile[property];
    			tr.appendChild(td3);
    			var tbl = document.getElementById('features').appendChild(tr);
    		}
		}
		function clearProfile() {
			window.profiler.clear('profile')
			var tbl = document.getElementById('features');
			tbl.parentNode.removeChild(tbl);
		} 
    </script>

	
	
</body>
</html>