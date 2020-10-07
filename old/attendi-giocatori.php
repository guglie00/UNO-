<html>

	<head>
		<meta charset="UTF-8">
		<title>Attendi...</title>
		<script>
				function waitPlayers () {
					var xmlhttp = new XMLHttpRequest ();
					xmlhttp.open("POST", "getNumberPlayers.php",true);
					xmlhttp.send();
					xmlhttp.onreadystatechange = function () {
							if (this.readyState == 4 && this.status == 200) {
								if (this.responseText == "2"){
									//location.reload("gioco.php"); 
									window.location.href = "gioco.php";
								} else {
									waitPlayers();
								}
							}
					};
				}
				
				function reloadChange () {
					reloadOnChange();
				}
		</script>
	</head>
	<body onpageshow="waitPlayers()">
<?php	
	require "functions.php";
	//header('Content-Type: application/json');
	$filePath = "Match.json";
	if (is_file($filePath)) {
		$file = fopen ($filePath, 'r');
		$MATCH_JSON = fread ($file, filesize($filePath));
		fclose ($file);
		$MATCH = json_decode($MATCH_JSON,true);
	}

	session_start ();
	if (!isset ($_SESSION['matchID'])) {
		if (isset($MATCH ['player'][session_id()]) && !empty ($MATCH['matches'])) {
			$_SESSION['matchID'] = $MATCH ['player'][session_id()];
		} else {
			$matchFound = false;
			if (isset($MATCH['matches']) && !empty ($MATCH['matches'])) {
				foreach ($MATCH ['matches'] as $matchID => $match) {
					if (count ($match['playerList']) < $match ['maxPlayers']) {
						$MATCH ['players'][session_id()] = $_SESSION ['matchID'] = $matchID;
						$MATCH ['matches'][$matchID]['playerList'][] = session_id();
						$MATCH ['matches'][$matchID]['lastChangeAt'] = time();
						$MATCH ['matches'][$matchID]['nextPlayer'] = session_id();
						$matchFound = true;
						break;
					}
				}
			}
			if (!$matchFound) {
				$_SESSION['matchID'] = 'match'.dechex(time());
				$MATCH['players'][session_id()] = $_SESSION['matchID'];
				$MATCH ['matches'][$_SESSION['matchID']] = [
					'itsOver' => false,
					'playerList' => [session_id()],
					'activePlayer' => session_id(),
					'nextPlayer' => null,
					'currentCard' => null,
					'maxPlayers' => 2,
					'lastChangeAt' => time(),
				];
			}
		}
	}
	$t = json_encode($MATCH, JSON_PRETTY_PRINT);

	$f = fopen($filePath, 'w');
	fwrite($f, $t);
	fclose($f);
?>
		<div>Attendi che tutti i giocatori si colleghino...</div>
		<div></div>
		<div id="risp"></div>
	</body>
</html>
		