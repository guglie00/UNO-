<?php
	require "functions.php";
	//header('Content-Type: application/json');
	$filePath = MATCH_FILE_PATH;
	if (is_file($filePath)) {
		$file = fopen ($filePath, 'r');
		$MATCH_JSON = fread ($file, filesize($filePath));
		fclose ($file);
		$MATCH = json_decode($MATCH_JSON,true);
	}

	session_start ();
	if (!isset ($_SESSION['matchID']) ) {
		if (isset($MATCH ['player'][session_id()]) && !empty ($MATCH['matches'])) {
			$_SESSION['matchID'] = $MATCH ['player'][session_id()];
			echo "time() -".  $MATCH['matches'][$_SESSION['matchID']]['lastChange'];
			/*if ((time() - $MATCH['matches'][$_SESSION['matchID']]['lastChange']) > 500 ) {
				$matchFound = false;
			}*/
		} else {
			$matchFound = false;
			/*if ((time() - $MATCH['matches'][$_SESSION['matchID']]['lastChange']) < 500 ) {
				echo "time() -".  $MATCH['matches'][$_SESSION['matchID']]['lastChange'];
				$matchFound = false;
			}else {*/
				if (isset($MATCH['matches']) && !empty ($MATCH['matches'])) {
					foreach ($MATCH ['matches'] as $matchID => $match) {
						if (count ($match['playerList']) < $match ['maxPlayers']) {
							$MATCH ['players'][session_id()] = $_SESSION ['matchID'] = $matchID;
							$MATCH ['matches'][$matchID]['playerList'][] = [
								'id'=>session_id(),
								'nick'=> $_SESSION['user'],
							];
							$MATCH ['matches'][$matchID]['lastChangeAt'] = time();
							$MATCH ['matches'][$matchID]['nextPlayer'] = $_SESSION['user'];
							$matchFound = true;
							break;
						}
					}
				}
			//}
			if (!$matchFound) {
				$_SESSION['matchID'] = 'match'.dechex(time());
				$MATCH['players'][session_id()] = $_SESSION['matchID'];
				$MATCH ['matches'][$_SESSION['matchID']] = [
					'itsOver' => false,
					'playerList' => [[
						'id'=>session_id(),
						'nick'=> $_SESSION['user']
					]],
					'activePlayer' => $_SESSION['user'],
					'nextPlayer' => null,
					'currentCard' => null,
					'maxPlayers' => 2,
					'lastChangeAt' => time(),
					'somma' => 0
				];
			}
		}
	}
	$t = json_encode($MATCH, JSON_PRETTY_PRINT);

	$f = fopen($filePath, 'w');
	fwrite($f, $t);
	fclose($f);
	//$_POST['nPlayers'] = count ($MATCH['players']);

	if (count ($MATCH['matches'][$_SESSION['matchID']]['playerList']) >= $MATCH['matches'][$_SESSION['matchID']]['maxPlayers']) {
		header ("Refresh:0; url=gioco.php");
	} else {
		header ("Refresh:1");
	}
	//echo  json_encode($MATCH);

?>
<html>
	<head>
		<meta charset=utf-8>
		<title>Attendi...</title>
		<link href="./Stylesheets/attendi.css" rel="stylesheet" type="text/css" title="stile1"/>
	</head>
	<body>
	<table><tr><td><img style="width:300px;margin-left:180px" src="Stylesheets/immagini/attendi.png"/></td>
	<td><img style="width:300px;margin:20px"src="Stylesheets/immagini/attendi2.png"/></td>
	<td><img style="width:250px"src="Stylesheets/immagini/attendi3.png"/></td></tr></table>

		<div class="loader">.</div>
	</body>
</html>
