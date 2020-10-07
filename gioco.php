<?php function displayHeader () { ?>
<!DOCTYPE HTML>
<html lang="it">
	<head>
		<title>UNO!</title>
		<meta charset="UTF-8">
		<link href="game.css" rel="stylesheet" type="text/css" title="stile1">
	</head>
	<body  onpageshow=" reloadChange(), timer()">
		<form method="POST">
			<input class="bottone" type="submit" name="end" value="Termina">
		</form>
<? } ?>
<?php function displayTurn ($MATCH) { ?>
		<h3>è il turno di <?php echo $MATCH['matches'][$_SESSION['matchID']]['activePlayer'] ?></h3>
<?php } ?>
<?php function displayColorChooser () { ?>
		<form method="POST">
			<select name="color">
				<option>blue</option>
				<option>red</option>
				<option>yellow</option>
				<option>green</option>
			</select>
			<input type="submit" name="cambia_colore"/>
		</form>
<?php } ?>
<?php function displayCommands() { ?>
		<form method="POST">
			<input class="bottone" type="submit" value="+" name="add" class="add">
			<input class="bottone" type="submit" value="Passa" name="pass">
			<input class="bottone" type="submit" value="UNO!" name="uno">
		</form>
<?php } ?>
<?php function actualCard ($CARDS, $el) { ?>
		<img class="actual" src="cards/<?php echo $CARDS[$el]['color']; echo $CARDS [$el]['number'];?>.png" class="card">
<?php } ?>
<?php
		function displayCards ($PLAYER, $CARDS) {
			foreach ($PLAYER as $index=>$el) {
?>
		<form method="POST">
			<input type="submit" value="" style="background-image: url(cards/<?php echo $CARDS[$el]['color']; echo $CARDS [$el]['number'];?>.png)" class="card">
			<input type="hidden" name="card" value="<?php echo $el ?>" >
			<input type="hidden" name="index" value="<?php echo $index ?>" >
			<input type="hidden" name="play">
		</form>
<?php
			}
		}
?>
<?php
require 'functions.php';
/* ------------------- INIZIO ELABORAZIONE ----------------- */
/* Il file CARDS_FILE_PATH contiene tutte le carte di ogni partita.
 * Le carte della partita corrente sono contenute in $CARDS[$_SESSION['matchID]].
 * Nel caso in cui non esista tale elemento del vettore significa che nessun giocatore ha effettuato il login, le carte devono essere quindi distribuite.
 */

//Apertura della sessione.
session_start();

//Se nella sessione non sono presenti gli elementi username e il numero di partita si viene reinidrizzati ad una pagina di errore.
/*if (!isset ($_SESSION['user']) || !isset ($_SESSION['matchID'])) {
	header ("location: error.php");
}*/

displayHeader ();

//Apertura o creazione del file contenente le carte delle partite.
$filePath = CARDS_FILE_PATH;
if (openFile ($filePath) != false) {
	$CARDS = json_decode(openFile ($filePath), true);
	$CARDS [$_SESSION['matchID']] = array ();
	chargeCardsVector($CARDS [$_SESSION['matchID']]);
	$file = fopen ($filePath, 'w');
	fwrite ($file, json_encode ($CARDS, JSON_PRETTY_PRINT));
	fclose ($file);
} else {
	$CARDS = array ();
	$CARDS [$_SESSION['matchID']] = array ();
	chargeCardsVector($CARDS [$_SESSION['matchID']]);
	$file = fopen ($filePath, 'w');
	fwrite ($file, json_encode ($CARDS, JSON_PRETTY_PRINT));
	fclose ($file);
}

if (!isset ($_SESSION['matchID'])){
	chargeCardsVector($CARDS[$_SESSION['matchID']]);
}
//Nel caso in cui le carte non siano ancora state distribuite si effettua la distribuzione
if (!isset ($_SESSION['myCards'])) {
	cardsDistribution ($_SESSION['myCards'],$CARDS[$_SESSION['matchID']]);
}

$MATCH = json_decode (openFile(MATCH_FILE_PATH,'r'),true);

if ($MATCH ['matches'][$_SESSION['matchID']]['itsOver'] ==  true) {
	header ('location:end.php');
}

if (count ($_SESSION['myCards']) <= 0 ){
	$MATCH ['matches'][$_SESSION['matchID']]['itsOver'] = true;
	writeFile(MATCH_FILE_PATH, json_encode($MATCH, JSON_PRETTY_PRINT));
	header ('location:end.php');
}

if (isset($_REQUEST['end'])) {
	$MATCH ['matches'][$_SESSION['matchID']]['itsOver'] = true;
	unset ($_SESSION['matchID']);
	unset ($_SESSION['myCards']);
	writeFile(MATCH_FILE_PATH, json_encode($MATCH, JSON_PRETTY_PRINT));
	header('location:./');
	goto fine;
}

//Se non è stata ancora comniciata la partita viene definita la prima carta
if ($MATCH['matches'][$_SESSION['matchID']]['currentCard'] == null) {
	$MATCH['matches'][$_SESSION['matchID']]['currentCard'] = getCard ($CARDS[$_SESSION['matchID']]);
	$_SESSION['currentCard'] = $MATCH['matches'][$_SESSION['matchID']]['currentCard'];
}

if (isset ($_REQUEST['add'])) {
	addCard($CARDS[$_SESSION['matchID']],$_SESSION['myCards']);
}

/*if (count ($_SESSION['myCards']) == 1 ) {
	if (! isset($_REQUEST['uno'])) {
		addCard($CARDS[$_SESSION['matchID']],$_SESSION['myCards']);
		addCard($CARDS[$_SESSION['matchID']],$_SESSION['myCards']);
	}
}*/

//Controllo se è stato richiesto il passo del turno oppure se è stata scelta una carta speciale
if (isset($_REQUEST['pass'])) {
	changePlayers ($MATCH);
	writeFile(CARDS_FILE_PATH,json_encode ($CARDS, JSON_PRETTY_PRINT));
	writeFile(MATCH_FILE_PATH, json_encode($MATCH, JSON_PRETTY_PRINT));
	header ('refresh:0');
	goto fine;
}

if (isset($_REQUEST['cambia_colore'])) {
	$color=$_REQUEST['color'];
	unset($_REQUEST['cambia_colore']);
	unset($_REQUEST['color']);
	$MATCH['matches'][$_SESSION['matchID']]['colore+4']=$color;
	changePlayers ($MATCH);
	writeFile(CARDS_FILE_PATH,json_encode ($CARDS, JSON_PRETTY_PRINT));
	writeFile(MATCH_FILE_PATH, json_encode($MATCH, JSON_PRETTY_PRINT));
	header ('refresh:0');
	goto fine;
}

//Accedi se il turno corrisponde al numero di sessione
if ($MATCH['matches'][$_SESSION['matchID']]['activePlayer'] == $_SESSION['user']) {
	//Mostra i comandi per la gestione: pulstante UNO - aggiungi carta
	displayCommands();

		//Verifico che il giocatore, avendo ricevuto un più due, abbia da rispondere con un altro più due
		if ($MATCH['matches'][$_SESSION['matchID']]['somma']>0) {
		$flag=false;
		foreach ($_SESSION['myCards'] as $key => $carta) {
			if ($CARDS[$_SESSION['matchID']][$carta]['number']==14){
				$flag=true;
			}
			if ($CARDS[$_SESSION['matchID']][$carta]['number']==10) {
				if (isset($MATCH['matches'][$_SESSION['matchID']]['colore+4'])) {
					if ($CARDS[$_SESSION['matchID']][$carta]['color']==$MATCH['matches'][$_SESSION['matchID']]['colore+4']) {
						$flag=true;
					}
				}else{
					$flag=true;
				}
			}
		}
		if (!$flag) {
			for ($i=0; $i < $MATCH['matches'][$_SESSION['matchID']]['somma']; $i++) {
				addCard($CARDS[$_SESSION['matchID']],$_SESSION['myCards']);
			}
			$MATCH['matches'][$_SESSION['matchID']]['somma']=0;
		}
	}

	//Se è stata premuta la carta tramite il form esegui le seguenti operazioni
	if (isset ($_REQUEST['play'])) {
		$card = $_REQUEST['card'];
		$index = $_REQUEST['index'];
		if (putCard($card,$MATCH['matches'][$_SESSION['matchID']]['currentCard'])) {
			$MATCH['matches'][$_SESSION['matchID']]['currentCard'] = $card;
			//Cambio dei turni
			if ($CARDS[$_SESSION['matchID']][$card]['number']==10) {
				$CARDS[$_SESSION['matchID']][$card]['available'] = false;
				$MATCH['matches'][$_SESSION['matchID']]['somma']=$MATCH['matches'][$_SESSION['matchID']]['somma']+2;
			}
			if ($CARDS[$_SESSION['matchID']][$card]['number']==12) {
				changePlayers($MATCH);
			}
			if ($CARDS[$_SESSION['matchID']][$card]['number']==13) {
				displayColorChooser ();
			}
			if ($CARDS[$_SESSION['matchID']][$card]['number']==14) {
				$CARDS[$_SESSION['matchID']][$card]['available'] = false;
				$MATCH['matches'][$_SESSION['matchID']]['somma']=$MATCH['matches'][$_SESSION['matchID']]['somma']+4;
				displayColorChooser ();
			}
			if ($CARDS[$_SESSION['matchID']][$card]['number'] != 10 && $CARDS[$_SESSION['matchID']][$card]['number'] != 14) {
				if ($MATCH['matches'][$_SESSION['matchID']]['somma']>0) {
					for ($i=0; $i < $MATCH['matches'][$_SESSION['matchID']]['somma']; $i++) {
						addCard($CARDS[$_SESSION['matchID']],$_SESSION['myCards']);
					}
					$MATCH['matches'][$_SESSION['matchID']]['somma']=0;
				}
			}
			//Elima la carta dal mazzo
			deleteCard($index, $_SESSION['myCards']);

			if ($CARDS[$_SESSION['matchID']][$card]['number'] != 14 && $CARDS[$_SESSION['matchID']][$card]['number'] != 13) {
				changePlayers($MATCH);
				writeFile(CARDS_FILE_PATH,json_encode ($CARDS, JSON_PRETTY_PRINT));
				writeFile(MATCH_FILE_PATH, json_encode($MATCH, JSON_PRETTY_PRINT));
				header('refresh:0');
				goto fine;
			}
		}
	}
}

writeFile(CARDS_FILE_PATH, json_encode($CARDS, JSON_PRETTY_PRINT));
writeFile(MATCH_FILE_PATH, json_encode($MATCH, JSON_PRETTY_PRINT));

//la riga seguente dice al goto di non eseguire il codice fino a qui da dove scrivo "goto fine;"
fine:

//Mostra la carta in tavola
displayTurn($MATCH);
actualCard($CARDS[$_SESSION['matchID']],$MATCH['matches'][$_SESSION['matchID']]['currentCard']);
displayCards($_SESSION['myCards'], $CARDS [$_SESSION['matchID']] );
?>
		<script type="text/javascript">
			function reloadOnChange () {
				var xmlhttp = new XMLHttpRequest ();
				xmlhttp.open("POST", "actualTurn.php",true);
				xmlhttp.send();
				xmlhttp.onreadystatechange = function () {
						if (this.readyState == 4 && this.status == 200) {
							var actualTurn = <?php echo '"'.$MATCH['matches'][$_SESSION['matchID']]['activePlayer'].'"' ?>;
							var nextTurn = <?php echo '"'.$MATCH['matches'][$_SESSION['matchID']]['nextPlayer'].'"' ?>;
							if (this.responseText == nextTurn ){
								window.location.href = window.location.href;
							} else {
								//setTimeout("reloadOnChange();",500);
								reloadOnChange();
							}
						}
				};
			}
		</script>
		<script>
			function timer () {
				setTimeout(function () {
					window.location.href = "error.php";
				},120000)
			}
		</script>
	</body>
</html>
