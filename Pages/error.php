<!DOCTYPE HTML>
<html lang="it">
  <head>
    <title>Errore - UNO!</title>
    <meta charset="utf-8">
  </head>
  <body>
    <h1>Oops! Qualcosa è andato storto!</h1>
    <h3>Il tuo avversario ha abbandonato la partita</h3>
  </body>
</html>

<?php
require 'functions.php';
unset ($_SESSION['matchID']);
unset ($_SESSION['myCards']);
header('refresh:5; url=./');
?>
