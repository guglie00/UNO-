<!doctype html>
<?php function head ($title="Benvenuto") { ?>

<html lang="it">
	<head>
		<meta charset="utf-8">
		<link href="Stylesheets/style.css" type="text/css" rel="stylesheet" title="style">
		<title><?php echo $title ?></title>
	</head>
	<body class="pagina">
	<ul>
		<li><a href="rules.php">Come si gioca?<a/></li>
		<li><a href="https://www.amazon.it/s/ref=nb_sb_noss_2?__mk_it_IT=%C3%85M%C3%85%C5%BD%C3%95%C3%91&url=search-alias%3Daps&field-keywords=carte+da+uno&rh=i%3Aaps%2Ck%3Acarte+da+uno">Shop<a/></li>
		<li><a href="score.html">Classifica<a/></li>
		<li><a href="contatti.html">Chi siamo?<a/></li>

	</ul>
	<div>
		<div>
<?php } ?>

<?php function printLoginForm () { ?>
		</div>
		<div class="contenitore">
		<img  class="logo"src="Stylesheets/immagini/logo.png"/>
		<div class="fermola">
		<div class="strobo1">Accedi per iniziare a giocare</div>
			<form method="post">
				<input class="stileform" type="text" placeholder="Username" name="username" required>
				<input class="stileform" type="password" placeholder="Password" name="password" required>
				<input class="stileform" type="submit" value="Sign In" name="signin">
			</form>

			<div class="strobo1"> Non hai un account? Iscriviti! </div>

			<form method="post" >
				<input class="stileform" type="text" placeholder="Username" name="username" required>
				<input class="stileform" type="password" placeholder="Password" name="password" required>
				<input class="stileform" type="password" placeholder="Confirm password" name="confirmedPassword" required>
				<input class="stileform" type="submit" value="Sign Up" name="signup">
			</form>
		</div>
		</div>

<?php } ?>

	<?php
		require "functions.php";

		session_start();

		if (!isset ($_REQUEST['signin']) && !isset($_REQUEST['signup'])) {
			//per evitare che rimanga in sessione in vecchio matchID
			session_unset();
		}

		$USERS = getUserArray ();

		/* ---------------- INIZIO ELABORAZIONE ---------------- */
		if (isset ($_REQUEST['signin'])) {
			$username = $_REQUEST['username'];
			$password = $_REQUEST['password'];
			if (array_key_exists($username, $USERS)) {
				if ($USERS [$username] ['password'] == hash('sha512',$password)) {
					$_SESSION['user'] = $username;
					header ("Refresh:0; url=attesa.php");
				} else {
					head("Error");
					echo"";
					printLoginForm();
				}
			} else {
				head("Error");
				printLoginForm();
			}
		} else if (isset ($_REQUEST['signup'])) {
			$username = $_REQUEST['username'];
			$password = $_REQUEST['password'];
			$Cpassword = $_REQUEST['confirmedPassword'];
			if ($password == $Cpassword) {
				if (!array_key_exists($username, $USERS)) {
					$USERS [$username] ['password'] = hash('sha512',$password);
					writeUserArray ($USERS);
					$_SESSION['user'] = $username;
					header ("Refresh:0; url=attesa.php");
				} else {
				?>
				<script>
				alert("Errore! This user already exist.");
				</script>
				<?php
					head("Error");
				}
			}
			else{
				?><script>
				alert("Errore! The password doesn't match");
				</script>
				<?php
				head("Error");
			}
			printLoginForm();
		} else {
			head("Log In");
			printLoginForm();
		}
	?>

	</body>
</html>
