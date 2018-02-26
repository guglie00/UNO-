<!doctype html>
<html lang="it">
	<head>
		<title>UNO!</title>
		<meta charset="utf-8">
		<link href="style.css" rel="stylesheet" type="text/css" title="stile1">
	</head>
	
	<body>
	<?php function displayCards ($PLAYER, $CARDS) { ?>
		<form method="post">
			<?php foreach ($PLAYER as $el) { ?>
				<input type="submit" value="" style="background-image: url(cards/<?php echo $CARDS[$el]['color']; echo $CARDS [$el]['number'];?>.png)" class="card">
				<input type="hidden" name="play">
			<?php } ?>
			<input type="submit" value="+" name="add" class="add">
		</form>
	<?php } ?>
	
	<?php function actualCard ($CARDS, $el) { ?>
		<img src="cards/<?php echo $CARDS[$el]['color']; echo $CARDS [$el]['number'];?>.png" class="card">
	<?php } ?>
	
	<?php function playForm () { ?>
		<form method="post">
			<input type="submit" value="Play!" name="play">
		</form>
	<?php } ?>
	
	<?php 
		/* UNO
		 * Definizione delle carte:
		 * 0...9 -> numeri da 0 a 9 (2 carte per numero in 4 colori diversi, totale di 80)
		 * 10 -> +2 (2 carte per colore, totale di 8)
		 * 11 -> cambio giro (2 carte per colore, totale di 8)
		 * 12 -> stop (2 carte per colore, totale di 8)
		 * 13 -> cambio colore (4 carte totali, non hanno colore)
		 * 14 -> +4 (4 carte totali, non hanno colore)
		 * TOTALE : 112 CARTE
		 */
		
		/* Funzione che carica il vettore $CARDS con tutte le carte del gioco
		 * @author Riccardo Gugliermini
		 */
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
		 * @author Riccardo Gugliermini
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
		function putCard ($newCard, $actualCard, &$CARDS) {
			if ($CARD[$newCard]['special'] || $CARD[$newCard]['number'] == $CARD[$actualCard]['number'] || $CARD[$newCard]['color'] == $CARD[$actualCard]['color']){
				$CARD[$actualCard]['available'] = true;
				return true;
			} else {
				return false;
			}
		}
		
	
		
		
		
		/* ------------------- INIZIO ELABORAZIONE ----------------- */
		session_start();
		$filePath = "cards.json";
		
		if(isset($_REQUEST['add']))
			if ($_SESSION['player']==1)
				$_SESSION['player']=2;
			else
				$_SESSION['player']=1;

		
		if(!isset($_REQUEST['play'])) {
			playForm();
			$cardsFile = fopen ($filePath,'w');
			$CARDS = array ();
			chargeCardsVector($CARDS);
			$cardVector = json_encode ($CARDS,JSON_PRETTY_PRINT);
			fwrite ($cardsFile,$cardVector);
			fclose ($cardsFile);
			cardsDistribution($_SESSION['PLAYER1'], $CARDS);
			cardsDistribution($_SESSION['PLAYER2'], $CARDS);
			$_SESSION['actualCard'] = getCard ($CARDS);
		} else {
			$cardsFile = fopen ($filePath,'r');
			$cardsVector = fread ($cardsFile, filesize ($filePath));
			fclose ($cardsFile);
			$CARDS = json_decode($cardsVector, true);
			
			actualCard($CARDS,$_SESSION['actualCard']);
			
			if (!isset($_SESSION['player'])){
				echo "<h1>1</h1>";
			} else {
				echo "<h1>".$_SESSION['player']."</h1>";
			}
			
			if(isset($_REQUEST['add'])){
				if ( $_SESSION['player'] == 1) {
					addCard($CARDS,$_SESSION['PLAYER1']);
					$_SESSION['player'] = 1;
				} else {
					if ( $_SESSION['player'] == 2) {
					addCard($CARDS,$_SESSION['PLAYER2']);
					$_SESSION['player'] = 2;
					}
				}
			}
			
			//VISUALIZZAZIONE DELLE CARTE IN BASE AL GIOCATORE
			if ( !isset ($_SESSION['player']) || $_SESSION['player'] == 1) {
				displayCards($_SESSION['PLAYER1'],$CARDS);
				$_SESSION['player'] = 2;
			} else {
				displayCards($_SESSION['PLAYER2'],$CARDS);
				$_SESSION['player'] = 1;
			}
		}
		
	?>
	</body>
</html>
