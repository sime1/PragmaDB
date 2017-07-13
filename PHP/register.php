<?php

require('Functions/mysql_fun.php');
require('Functions/page_builder.php');
require('Functions/urlLab.php');

session_start();

$enable = false;

$absurl=urlbasesito();

if(!$enable)
  echo "Pagina non abilitata.";
else
{
if(isset($_REQUEST['submit'])){
	$user=$_POST["username"];
	$pwd=sha1($_POST["password"]);
	$firstname=$_POST["firstname"];
	$lastname=$_POST["lastname"];
	$conn = sql_conn();
	$query = "INSERT INTO Utenti(Username, Nome, Cognome, Password) VALUES('$user', '$firstname', '$lastname', '$pwd')";
	mysql_query($query, $conn);
	header("Location: $absurl/index.php");
}
else{
	if(empty($_SESSION['user'])){
		$title="Registration";
		startpage_builder($title);
echo<<<END

			<div id="immagine">
				<img src="$absurl/Immagini/logo_full.png" alt="Logo CoCode" />
			</div>
			<div id="form">
				<h1>Registrazione</h1>
				<form action="$absurl/register.php" method="post">
					<fieldset>
						<p>
							<label for="username">Username:</label>
							<input type="text" id="username" name="username" maxlength="10" />
						</p>
<p>
							<label for="firstname">Nome:</label>
							<input type="text" id="firstname" name="firstname" maxlength="20" />
						</p>
<p>
							<label for="l">Cognome:</label>
							<input type="text" id="lastname" name="lastname" maxlength="10" />
						</p>
						<p>
							<label for="password">Password:</label>
							<input type="password" id="password" name="password" maxlength="40" />
						</p>
						<p>
							<input type="submit" id="submit" name="submit" value="Accedi" />
							<input type="reset" id="reset" name="reset" value="Cancella" />
						</p>
					</fieldset>
				</form>
			</div>
END;
		endpage_builder();
	}
	else{
		header("Location: $absurl/Utente/home.php");
	}
}
}
?>
