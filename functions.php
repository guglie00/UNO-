<?php
	define ("USERS_FILE_PATH","Files/users.json");
	define ("MATCH_FILE_PATH","Files/match.json");
	define ("CARDS_FILE_PATH","Files/cards.json");

	function getUserArray () {
		//Apertura o eventuale creazione del file contenente le credenziali di accesso degli utenti
		if (file_exists (USERS_FILE_PATH)) {
			$loginFile = fopen (USERS_FILE_PATH, 'r');
			$JSON_USERS = fread ($loginFile, filesize (USERS_FILE_PATH));
			$USERS = json_decode($JSON_USERS, true);
			fclose($loginFile);
		} else {
			$USERS = array();
			$USERS ['administrator'] ['password'] = hash('sha512',"admin");
			$JSON_USERS = json_encode($USERS, JSON_PRETTY_PRINT);
			$loginFile = fopen (USERS_FILE_PATH, 'w');
			fwrite ($loginFile,$JSON_USERS);
			fclose ($loginFile);
		}
		return $USERS;
	}

	function writeUserArray ($USERS) {
		$JSON_USERS = json_encode($USERS, JSON_PRETTY_PRINT);
		$loginFile = fopen (USERS_FILE_PATH, 'w');
		fwrite ($loginFile,$JSON_USERS);
		fclose ($loginFile);
	}

	function getMatchArray () {
		$filePath = "Match.json";
		if (file_exists ($filePath)) {
			$MatchFile = fopen ($filePath, 'r');
			$JSON_MATCH = fread ($MatchFile, filesize ($filePath));
			$MATCH = json_decode($JSON_MATCH, true);
			fclose($MatchFile);
		} else {
			$MATCH = array();
			$MATCH ['isOver']=false;
			$MATCH ['nPlayers']=0;
			$MATCH ['totalPlayers']=2;
			//$MATCH ['administrator'] ['password'] = hash('sha512',"admin");
			$JSON_MATCH = json_encode($MATCH, JSON_PRETTY_PRINT);
			$MatchFile = fopen ($filePath, 'w');
			fwrite ($MatchFile,$JSON_MATCH);
			fclose ($MatchFile);
		}
		return $MATCH;
	}

	function writeMatchArray ($MATCH) {
		$filePath = "Match.json";
		$JSON_MATCH = json_encode($MATCH, JSON_PRETTY_PRINT);
		$matchFile = fopen ($filePath, 'w');
		fwrite ($matchFile,$JSON_MATCH);
		fclose ($matchFile);
	}
	/*function openFile ($path) {
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

	function writeFile ($path, $content) {
		$file = fopen ($path, 'w');
		if ($file != false) {
			fwrite ($file, $content);
			fclose ($file);
		}
	}*/

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

	function writeFile ($path, $content) {
		$file = fopen ($path, 'w');
		if ($file != false) {
			fwrite ($file, $content);
			fclose ($file);
		}
	}

	function chargeCardsVector (&$CARDS) {
		$number = 0;
		$color = 'blue';
		for ($i=0; $i<104; $i++) {
			//Se il conatore è divisibile per due aumento il numero della carta, in questo modo produco due carte per numero
			if ($i%2 == 0 && $i != 0) {
				$number ++;
			}
			//Ogni 26 carte il colore cambia e i numeri cominciano nuovamente da 0
			if ($i == 26) {
				$number = 0;
				$color = 'red';
			} else {
				if ($i == 52) {
					$number = 0;
					$color = 'green';
				} else {
					if ($i == 78) {
						$number = 0;
						$color = 'yellow';
					}
				}
			}
			//Definizione degli attributi delle carte
			$CARDS[$i]['number'] = $number;
			$CARDS[$i]['color'] = $color;
			/*if ($number == 10 || $number == 11 || $number == 12)
				$CARDS[$i]['special'] = true;
			else*/
				$CARDS[$i]['special'] = false;
				$CARDS[$i]['available'] = true;
		}
		$number ++;
		for ($i; $i<112; $i++) {
			if ($i == 108) { $number ++; }
			$CARDS[$i]['number'] = $number;
			$CARDS[$i]['color'] = '';
			$CARDS[$i]['special'] = true;
			$CARDS[$i]['available'] = true;
		}
	}

	//Funzione che distibuisce 7 carte per ogni giocatore
	function cardsDistribution (&$PLAYER, &$CARDS) {
		for ($i=0; $i<7; $i++) {
			$PLAYER[$i] = getCard($CARDS);
		}
	}

	function addCard (&$CARDS, &$PLAYER) {
		$PLAYER[count($PLAYER)] = getCard($CARDS);
	}

	/* Funzione che ritorna l'indice per una carta pescata dal mazzo
	*/
	function getCard (&$CARDS) {
		do{
				$randI = rand (0,111);
		}while (!$CARDS[$randI]['available']);
		$CARDS [$randI]['available'] = false;
		return $randI;
	}

	/* Funzione che mette una carta in tavola
	 * @author Riccardo Gugliermini
	 */
	function putCard ($newCard, $actualCard) {
		//con global semplicemente aquisisco le variabili esterne alla funzione e modifico quelle senza dover passarmele per indirizzo nel caso dei vettori
		GLOBAL $MATCH,$CARDS;
		if (isset($MATCH['matches'][$_SESSION['matchID']]['colore+4'])&&$MATCH['matches'][$_SESSION['matchID']]['colore+4']==$CARDS[$_SESSION['matchID']][$newCard]['color']) {
			$CARDS[$_SESSION['matchID']][$actualCard]['available'] = true;
			unset($MATCH['matches'][$_SESSION['matchID']]['colore+4']);
			return true;
		}
		if ($CARDS[$_SESSION['matchID']][$newCard]['special'] || $CARDS[$_SESSION['matchID']][$newCard]['number'] == $CARDS[$_SESSION['matchID']][$actualCard]['number'] || $CARDS[$_SESSION['matchID']][$newCard]['color'] == $CARDS[$_SESSION['matchID']][$actualCard]['color']){
			$CARDS[$_SESSION['matchID']][$actualCard]['available'] = true;
			return true;
		} else {
			return false;
		}
	}

	function deleteCard ($el, &$MYCARDS) {
		for ($i=$el; $i<(count($MYCARDS)-1); $i++){
			$MYCARDS[$i]=$MYCARDS[$i+1];
		}
		//$MYCARDS[count($MYCARDS)] = null;
		unset($MYCARDS[count($MYCARDS)-1]);
	}

	function changePlayers (&$MATCH) {
		$activePlayer = $MATCH['matches'][$_SESSION['matchID']]['activePlayer'];
		$MATCH['matches'][$_SESSION['matchID']]['activePlayer'] = $MATCH['matches'][$_SESSION['matchID']]['nextPlayer'];
		$MATCH['matches'][$_SESSION['matchID']]['nextPlayer']= $activePlayer;
	}
?>
