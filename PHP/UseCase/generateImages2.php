<?php

require('../Functions/mysql_fun.php');
require('../Functions/urlLab.php');

session_start();

$zip = new ZipArchive();
$zname = tempnam('.', 'tmp');
$filename = '';
if(empty($_SESSION['user']) || ($zip->open($zname, ZipArchive::CREATE)!==TRUE))
{
	header("Location: $absurl/error.php");
}
else
{
	header('Content-type: application/octet-stream');
	header('Content-Disposition: attachment; filename="useCaseUML.zip"');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');
  $conn=sql_conn();
	//													0										1											2					          3									4											5								 6						   7                       8
  $query="SELECT p.nome as nomePadre, f.nome as nomeFiglio, a.nome as attore,  p.IdUC as idPadre, f.IdUC as idFiglio, f.Estensioni as ext, f.Inclusioni as inc, p.CodAuto as CodPadre, a.Secondario as secondario
		FROM UseCase f, UseCase p, Attori a, AttoriUC au
		WHERE p.CodAuto = f.Padre AND (a.CodAuto = au.Attore AND au.UC = f.CodAuto)
		UNION
		SELECT NULL as nomePadre, p.nome as nomeFiglio, a.nome as attore, NULL as idPadre, p.IdUC as idFiglio, p.Estensioni as ext, p.Inclusioni as inc, NULL as CodPadre, a.Secondario as secondario
		FROM UseCase p, Attori a, AttoriUC au
		WHERE p.padre IS NULL AND (a.CodAuto = au.Attore AND au.UC = p.CodAuto)
		ORDER BY nomePadre, IdFiglio";
	$list=mysql_query($query, $conn) or fail("Query falita: ".mysql_error($conn));
	$old_par = "nome che uno usecase non avrà mai";
	//$file = null;
	//exec("mkdir ./uml");
	while($row=mysql_fetch_row($list))
	{
		if($old_par != $row[3])//cambiato use case padre, cambio file
		{
			if(!empty($line))
			{
				$line .= "}\n@enduml";
				$zip->addFromString($filename, $line);
				$line = '';
				//fwrite($file, "}\n@enduml");
				//fclose($file);
			}
			$filename = "uml/UseCase" . str_replace('.', '', $row[3]) . ".uml";
			//$file = fopen($filename, "wb");
			$line = "@startuml\nscale 1000*1000\nleft to right direction\nskinparam packageStyle rect\n";
			$query="SELECT DISTINCT a.nome
			  FROM Attori a, AttoriUC au, UseCase f
				WHERE a.CodAuto = au.Attore AND au.UC = f.CodAuto AND f.Padre" . ($row[7] ? "='$row[7]'" : " IS NULL");
			//echo $query;
			$res = array();
			$result = mysql_query($query, $conn) or fail("Query falita: ".mysql_error($conn));
			while($res = mysql_fetch_row($result))
			{
				$line .= ":$res[0]:\n";
			}
			$line .= "rectangle $row[3]{\n";
			$query = "SELECT f.Inclusioni as inc, f.Estensioni as ext, f.IdUC as id
				FROM UseCase f LEFT JOIN UseCase p ON p.CodAuto = f.Padre
				WHERE p.IdUC" . ($row[3] ? "='$row[3]'" : " IS NULL");
			//inclusioni
			$result = mysql_query($query, $conn) or fail("Query falita: ".mysql_error($conn));
			while($res = mysql_fetch_row($result))
			{
				$incs = explode(', ', $res[0]);
				foreach($incs as $inc)
					if(!empty($inc))
						$line .= "($res[2]) ..> ($inc): <<include>>\n";
				//estensioni
				$exts = explode(', ', $res[1]);
				foreach($exts as $ext)
					if(!empty($ext))
						$line .= "($ext) <.. ($res[2]): <<extend>>\n";
			}
			//fwrite($file, $line);
		}
		$old_par = $row[3];

		$line .= "( $row[4] - $row[1] ) as ($row[4])\n";
		//linee comunicazione
		if(empty($row[5]) && empty($row[6]))
		{
			if(!$row[8])
				$line .= ":" . $row[2]. ":" . "--($row[4])\n";
			else
				$line .= "($row[4])--:$row[2]:\n";
		}

		//fwrite($file, $line);
	}
	$line .= "@enduml";
	//fwrite($file, "@enduml");
	//fclose($file);
	$zip->addFromString($filename, $line);	
	$zip->close();
	//exec("rm ./useCasesUML.zip");
	//exec("plantuml -charset UTF-8 -o \"./images\" \"./uml/*.uml\" ");
	//exec("zip -r ./useCasesUML.zip ./uml");
	//exec("rm -rf ./images");
	//exec("rm -rf ./uml");
	$file = fopen($zname, "rb");
	fpassthru($file);
	fclose($file);
	unlink($zname);
}
