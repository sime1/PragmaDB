<?php

require('../../Functions/mysql_fun.php');
require('../../Functions/page_builder.php');
require('../../Functions/urlLab.php');

session_start();

$absurl=urlbasesito();

date_default_timezone_set("Europe/Rome");

if(empty($_SESSION['user'])){
	header("Location: $absurl/error.php");
}
else{
	$id=$_GET["id"];
	if(isset($_REQUEST['submit'])){
		//Ho dei dati da inserire
		$nomef=$_POST["nome"]; //nome della classe ricevuta dal form
		$descf=$_POST["desc"]; //descrizione della classe
		$num_cluf=$_POST["num_clu"]; //Numero di Classi OUT
		$err_nome=false;
		$err_desc=false;
		$errori=0;
		if($nomef==null){
			$err_nome=true;
			$errori++;
		}
		if($descf==null){
			$err_desc=true;
			$errori++;
		}
		if($errori>0){
			$title="Errore";
			startpage_builder($title);
echo<<<END

			<div id="content" class="alerts">
				<h2>Errore nell'inserimento dei seguenti campi:</h2>
				<ul>
END;
			if($err_nome){
echo<<<END

					<li>Nome: NON INSERITO</li>
END;
			}
			if($err_desc){
echo<<<END

					<li>Descrizione: NON INSERITA</li>
END;
			}
		}
		else{
      $nomef=mysql_escape_string($nomef);
			$descf=mysql_escape_string($descf);
			$conn=sql_conn();
      $query="UPDATE Segnali SET Nome='$nomef', Descrizione='$descf' WHERE CodAuto=$id";
      mysql_query($query,$conn);
			$query="DELETE FROM SegnaliClassi WHERE Segnale=$id";
			mysql_query($query,$conn);
			for($i = 1; $i <= $num_cluf; $i++)
			{
				$cur=$_POST["clu$i"];
				$query = "INSERT INTO SegnaliClassi(Classe, Segnale) VALUES($cur,$id)";
				mysql_query($query,$conn);
				echo "inserito $i: $query";
			}
			$title="Segnale Modificato con successo";
			startpage_builder($title);
echo<<<END

			<div id="content" class="alerts">
				<h2>Operazione effettuata</h2>
				<p>Il segnale è stato modificato con successo.</p>
				<p><a class="link-color-pers" href="$absurl/Classi/Segnali/segnali.php">Torna a Segnali</a>.</p>
			</div>
END;
		}
	}
	else if(isset($id)){
		//Non ho ricevuto nessun dato in post
		//Mostro il form per l'inserimento
		$conn=sql_conn();
		$query="SELECT Nome, Descrizione FROM Segnali WHERE CodAuto=$id";
		$list=mysql_query($query,$conn);
		$row=mysql_fetch_row($list);
		$title="Modifica Segnale";
		startpage_builder($title);
echo<<<END
			<div id="content">
				<h2>Modifica Segnale</h2>
				<div id="form">
					<form action="$absurl/Classi/Segnali/modificasegnale.php?id=$id" method="post">
						<fieldset>
							<p>
								<label for="nome">Nome*:</label>
								<input type="text" id="nome" name="nome" maxlength="100" value="$row[0]" />
							</p>
							<p>
								<label for="desc">Descrizione*:</label>
								<textarea rows="2" cols="0" id="desc" name="desc" maxlength="10000">$row[1]</textarea>
							</p>
							<script type="text/javascript" src="$absurl/UseCase/script_uc.js"></script>
							<p id="clus">
							<label for="clu1">Classi che gestiscono questo segnale: </label>
END;
	$conn=sql_conn();
	$query = "SELECT s.Classe, c.Nome FROM SegnaliClassi s, Classe c WHERE s.Segnale=$id AND c.CodAuto = s.Classe";
	$list=mysql_query($query,$conn);
	$classi = array();
	while($row=mysql_fetch_row($list))
		$classi[$row[0]] = $row[1];
	$query = "SELECT CodAuto, Nome FROM Classe";
	$list=mysql_query($query,$conn);
	$classi_tutte = array();
	while($row=mysql_fetch_row($list))
		$classi_tutte[$row[0]] = $row[1];
	$i = 1;
	foreach($classi as $k => $classe)
	{
		echo<<<END
			<select id="clu$i" name="clu$i" onchange="multiple_sel(5,$i)">
			<option value="N/D">N/D</option>
			<option value="$k" selected="selected">$classe</option>
END;
		unset($classi_tutte[$k]);
		foreach($classi_tutte as $ind => $cur)
		{
		echo<<<END
			<option value="$ind">$cur</option>
END;
		}
	echo "</select>";
	$i++;
	}
echo<<<END
						<select id="clu$i" name="clu$i" onchange="multiple_sel(5,$i)">
								<option value="N/D">N/D</option>
END;
	foreach($classi_tutte as $ind => $cur)
	{
echo<<<END
				<option value="$ind">$cur</option>
END;
	}
echo<<<END
							</select></p>
							<input type="hidden" id="num_clu" name="num_clu" value="0" />
							<p>
								<input type="submit" id="submit" name="submit" value="Modifica" />
								<input type="reset" id="reset" name="reset" value="Cancella" />
							</p>
						</fieldset>
					</form>
				</div>
			</div>
END;
	}
	endpage_builder();
}
?>
