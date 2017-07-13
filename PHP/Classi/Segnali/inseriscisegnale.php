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
      $query="INSERT INTO Segnali(Nome, Descrizione) VALUES('$nomef', '$descf')";
			mysql_query($query,$conn);
			for($i = 1; $i <= $num_cluf; $i++)
			{
				$cur=$_POST["clu$i"];
				$query = "INSERT INTO SegnaliClassi(Classe, Segnale) VALUES($cur,$id)";
				mysql_query($query,$conn);
			}
			$title="Segnale Inserito";
			startpage_builder($title);
echo<<<END

			<div id="content" class="alerts">
				<h2>Operazione effettuata</h2>
				<p>Il segnale è stato inserito con successo.</p>
				<p><a class="link-color-pers" href="$absurl/Classi/Segnali/segnali.php">Torna a Segnali</a>.</p>
			</div>
END;
		}
	}
	else{
		//Non ho ricevuto nessun dato in post
		//Mostro il form per l'inserimento
		$title="Inserisci Segnale";
		startpage_builder($title);
echo<<<END
			<div id="content">
				<h2>Inserisci Segnale</h2>
				<div id="form">
					<form action="$absurl/Classi/Segnali/inseriscisegnale.php" method="post">
						<fieldset>
							<p>
								<label for="nome">Nome*:</label>
								<input type="text" id="nome" name="nome" maxlength="100" />
							</p>
							<p>
								<label for="desc">Descrizione*:</label>
								<textarea rows="2" cols="0" id="desc" name="desc" maxlength="10000"></textarea>
							</p>
							<script type="text/javascript" src="$absurl/UseCase/script_uc.js"></script>
							<p id="clus">
							<label for="clu1">Classi che gestiscono questo segnale: </label>
							<select id="clu1" name="clu1" onchange="multiple_sel(5,1)">
								<option value="N/D">N/D</option>
END;
							$conn=sql_conn();
							$query="SELECT CodAuto, Nome FROM Classe";
							$list=mysql_query($query,$conn);
							while($row=mysql_fetch_row($list))
							{
								echo<<<END
									<option value="$row[0]">$row[1]</option>
END;
							}
echo<<<END
							</select></p>
							<input type="hidden" id="num_clu" name="num_clu" value="0" />
							<p>
								<input type="submit" id="submit" name="submit" value="Inserisci" />
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
