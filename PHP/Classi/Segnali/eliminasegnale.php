<?php

require('../../Functions/mysql_fun.php');
require('../../Functions/page_builder.php');
require('../../Functions/urlLab.php');

session_start();

date_default_timezone_set("Europe/Rome");

$absurl=urlbasesito();

if(empty($_SESSION['user'])){
	header("Location: $absurl/error.php");
}
else{
  $id=$_GET['id'];
  if(isset($id)){
	$conn=sql_conn();
	$query="DELETE FROM Segnali WHERE CodAuto='$id'"; //Chiama la SP per la rimozione
	$query=mysql_query($query,$conn) or fail("Query fallita: ".mysql_error($conn));
	$title="Segnale Eliminato";
	startpage_builder($title);
echo<<<END
			<div id="content" class="alerts">
				<h2>Operazione effettuata</h2>
				<p>Il segnale è stato eliminato con successo.</p>
				<p><a class="link-color-pers" href="$absurl/Classi/Segnali/segnali.php">Torna a Segnali</a>.</p>
			</div>
END;
  endpage_builder();
	}
	else{
    $title="Errore";
  	startpage_builder($title);
echo<<<END
    <div id="content" class="alerts">
      <h2>Errore</h2>
      <p>Si è verificato un errore</p>
      <p><a class="link-color-pers" href="$absurl/Classi/Segnali/segnali.php">Torna a Segnali</a>.</p>
    </div>
END;
  endpage_builder();
  }
}
?>
