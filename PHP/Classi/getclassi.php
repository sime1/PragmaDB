<?php

require('../Functions/mysql_fun.php');
require('../Functions/urlLab.php');

session_start();

$absurl=urlbasesito();

if(empty($_SESSION['user'])){
	header("Location: $absurl/error.php");
}
else{
	header('Content-type: application/x-tex');
	header('Content-Disposition: attachment; filename="classi.tex"');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');
	
	echo "\documentclass[PdQ.tex]{subfiles}\n";
	echo "\usepackage{mdwlist}\n";
	echo "\begin{document}\n";
	
	$conn=sql_conn();
	
	//Selezioni i pacchetti
	
	$query = "SELECT CodAuto, Nome, Descrizione FROM Package";
	
	$rs_package = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
	
	while($package = mysql_fetch_row($rs_package)) {
	
		echo "\subsection{{$package[1]}}\n";
		
		echo "{$package[2]}\n";
		
		echo "\subsubsection{Classi}\n";
		
		// Classi del pacchetto 
		
		$query = "SELECT CodAuto, PrefixNome, Nome, Utilizzo, Descrizione, Abstract FROM Classe WHERE ContenutaIn = {$package[0]}";
		
		$rs_classi = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
			
		while($classe = mysql_fetch_row($rs_classi)){
			
			//Controllo se classe o interfaccia
			if(strstr($classe[1], "<<interface>>") || strstr($classe[1], "<<Interface>>")) {
				$tipo = "Interface";
				$classe[1] = preg_replace("/<<interface>>/i", "", $classe[1]);
				$classe[2] = preg_replace("/<<interface>>/i", "", $classe[2]);
			}
			else {
				if($classe[5] == 0)
					$tipo = "Class";
				else
					$tipo = "Abstract Class";
			}
			
			echo "\hypertarget{{$classe[2]}_label}{\paragraph{{$classe[2]}}}\n";
			
			//Immagine
			echo "\begin{figure}[h]\n\t\centering\n\t\includegraphics[width=\\textwidth,height=\\textheight,keepaspectratio]{images/Class{$classe[2]}.png}\n\t\caption{{$classe[1]}}\n\\end{figure}\n";
			
			echo "\begin{itemize}\n";
				echo "\t\item \\textbf{Nome}: \\file{{$classe[2]}};\n";
				echo "\t\item \\textbf{Tipo}: \\file{{$tipo}};\n";
				
				//Tolgo a capo e il punto finale da utilizzo e descrizione per rispettare NdP
				$classe[3] = NdP($classe[3]);
				$classe[4] = NdP($classe[4]);
				$classe[3] = lcfirst($classe[3]);
				$classe[4] = lcfirst($classe[4]);
				
				echo "\t\item \\textbf{Descrizione}: {$classe[4]};\n";
				echo "\t\item \\textbf{Utilizzo}: {$classe[3]};\n";
				
				//Controllo eventuali padri
				$query = "SELECT Padre.Nome
				FROM EreditaDa, Classe as Padre, Classe as Figlio
				WHERE EreditaDa.Padre = Padre.CodAuto
				AND EreditaDa.Figlio = Figlio.CodAuto
				AND EreditaDa.Figlio = {$classe[0]}";
				
				$rs_padre = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
				
				$virgola = 0;
				if(mysql_num_rows($rs_padre) > 0) {
					echo "\t\item \\textbf{Padre}: ";
					while($padre = mysql_fetch_row($rs_padre)) {
						if($virgola == 0) {
							echo "\\file{{$padre[0]}}";
							$virgola = 1;
						}
						else {
							echo ", {$padre[0]}";
						}
					}
					echo ";\n";
				}
				
				//Fine padre
				
				//Controllo eventuali figli
				$query = "SELECT Figlio.Nome
				FROM EreditaDa, Classe as Padre, Classe as Figlio
				WHERE EreditaDa.Padre = Padre.CodAuto
				AND EreditaDa.Figlio = Figlio.CodAuto
				AND EreditaDa.Padre = {$classe[0]}";

				$rs_figlio = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
				
				$virgola = 0;
				if(mysql_num_rows($rs_figlio) > 0) {
					if(mysql_num_rows($rs_figlio) == 1) {
						echo "\t\item \\textbf{Figlio}: ";
					}
					else {
						echo "\t\item \\textbf{Figli}: ";
					}
					while($figlio = mysql_fetch_row($rs_figlio)) {
						if($virgola == 0) {
							echo "\\file{{$figlio[0]}}";
							$virgola = 1;
						}
						else {
							echo ", \\file{{$figlio[0]}}";
						}
					}
					echo ";\n";
				}
				
				// Attributi
				
				$query = "SELECT AccessMod, Attributo.Nome, Tipo, Attributo.Descrizione
				FROM Attributo, Classe
				WHERE Attributo.Classe = Classe.CodAuto
				AND Classe.CodAuto = {$classe[0]}";
			
				$rs_attributi = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
				
				if(mysql_num_rows($rs_attributi) > 0) {
					echo "\t\item \\textbf{Attributi}: \n\t\begin{itemize}\n";
					while($attributi = mysql_fetch_row($rs_attributi)) {
						$attributi[1] = LaTeXUnderscore($attributi[1]);
						$attributi[3] = NdP($attributi[3]);
						echo "\t\t\item[] \\file{{$attributi[0]} {$attributi[1]}: {$attributi[2]}} \\\\\n\t\t{$attributi[3]};\n";
					}
					echo "\t";
					echo '\end{itemize}';
					echo "\n";
				}
				
				//Metodi
				
				$query = "SELECT AccessMod, Metodo.Nome, ReturnType, Metodo.Descrizione, Metodo.CodAuto
				FROM Metodo, Classe
				WHERE Metodo.Classe = Classe.CodAuto
				AND Classe.CodAuto = {$classe[0]}";
			
				$rs_metodi = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
				
				if(mysql_num_rows($rs_metodi) > 0) {
					echo "\t\item \\textbf{Metodi}: \n\t\begin{itemize}\n";
					while($metodi = mysql_fetch_row($rs_metodi)) {
						$metodi[1] = LaTeXUnderscore($metodi[1]);
						$metodi[3] = NdP($metodi[3]);
						
						echo "\t\t\item[] \\file{{$metodi[0]} {$metodi[1]}(";
						
						//Ottengo i parametri del metodo
						
						$query = "SELECT Parametro.Nome, Parametro.Tipo, Parametro.Descrizione
						FROM Parametro, Metodo
						WHERE Parametro.Metodo = Metodo.CodAuto
						AND Metodo.CodAuto = {$metodi[4]}";
						
						$rs_parametri = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
						
						//Stampo i parametri tra le parentesi del metodo
						
						$numeroParametri = mysql_num_rows($rs_parametri);
						while($parametro = mysql_fetch_row($rs_parametri)) {
							$parametro[0] = LaTeXUnderscore($parametro[0]);
							echo "{$parametro[0]}: {$parametro[1]}";
							$numeroParametri--;
							if($numeroParametri > 0)
								echo ", ";
						}
						
						echo "): {$metodi[2]}} \\\\\n\t\t{$metodi[3]};\\\\\n";
											
						//Itemize dei parametri con rispettiva descrizione
					
						$rs_parametri = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
						
						if(mysql_num_rows($rs_parametri) > 0) {
							echo "\t\tParametri: \n\t\t\begin{itemize}\n";
							while($parametro = mysql_fetch_row($rs_parametri)) {
								$parametro[2] = NdP($parametro[2]);
								$parametro[0] = LaTeXUnderscore($parametro[0]);
								$parametro[2] = LaTeXUnderscore($parametro[2]); 
								echo "\t\t\t\item \\file{{$parametro[0]}: {$parametro[1]}} \\\\\n\t\t\t{$parametro[2]};\n";
							}
							echo "\t\t";
							echo '\end{itemize}';
							echo "\n";
						}
					}
					echo "\t";
					echo '\end{itemize}';
					echo "\n";
				}
				
				// Relazione con le altri classi
				
				// IN
				
				$query = "SELECT Nome
				FROM Relazione, Classe
				WHERE Da = Classe.CodAuto
				AND A = {$classe[0]}";
				
				$rs_in = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));

				// OUT
				
				$query = "SELECT Nome
				FROM Relazione, Classe
				WHERE A = Classe.CodAuto
				AND Da = {$classe[0]}";
				
				$rs_out = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
				
				if(mysql_num_rows($rs_in) + mysql_num_rows($rs_out) > 0) {
					echo "\t\item \\textbf{Relazioni con le altre classi}: \n\t\begin{itemize}\n";
					while($in = mysql_fetch_row($rs_in)) {
						echo "\t\t\item IN \hyperlink{{$in[0]}_label}{\\file{{$in[0]}}}\n";
					}
					while($out = mysql_fetch_row($rs_out)) {
						echo "\t\t\item OUT \hyperlink{{$out[0]}_label}{\\file{{$out[0]}}}\n";
					}
					echo "\t\\end{itemize}\n";
				}
				
				// Eventi gestiti dalla classe
				
				$query = "SELECT Segnali.Nome, Segnali.Descrizione
				FROM Segnali, SegnaliClassi
				WHERE SegnaliClassi.Segnale = Segnali.CodAuto
				AND SegnaliClassi.Classe = {$classe[0]}";
							
				$rs_segnali = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
				
				if(mysql_num_rows($rs_segnali) > 0) {
					echo "\t\item \\textbf{Eventi gestiti}: \n\t\begin{itemize}\n";
					$numeroSegnali = mysql_num_rows($rs_segnali);
					while($segnali = mysql_fetch_row($rs_segnali)) {
						$segnali[1] = NdP($segnali[1]);
						echo "\item \\file{{$segnali[0]}} \\\\ {$segnali[1]}";
						$numeroSegnali--;
						if($numeroSegnali > 0)
							echo ";";
						else
							echo ".";
					}
					echo "\t\\end{itemize}\n";
				}
				
			echo '\end{itemize}';
			echo "\n\n";
		}
	}
	
	echo "\\end{document}\n";
}


//Tolgo a capo e rimuovo il punto finale per rispettare NdP.
function NdP($stringa) {
	$stringa = trim(preg_replace('/\s\s+/', ' ', $stringa));
	$stringa = rtrim($stringa, ".");
	return $stringa;
}

//Altirmenti LaTeX non compila
function LaTeXUnderscore ($stringa) {
	return str_replace('_','\_',$stringa);
}

?>