<?php
	session_start();
	function openFile ($path) {
		if (file_exists($path)) {
			$file = fopen ($path, 'r');
			if ($file != false) {
				$content = fread ($file, filesize($path));
				fclose ($file);
				return $content;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	$MATCH = json_decode(openFile ("Match.json"),true);
	echo $MATCH['matches'][$_SESSION['matchID']]['currentCard'];

?>