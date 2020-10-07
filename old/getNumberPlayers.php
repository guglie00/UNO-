<?php	
	require "functions.php";
	
	$MATCH = json_decode(openFile("Match.json"),true);
	echo count ($MATCH['players']);
?>