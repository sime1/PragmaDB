<?php

require('../Functions/mysql_fun.php');
require('../Functions/urlLab.php');

session_start();

$zip = new ZipArchive();
$zname = tempnam('.', 'tmp');

if(empty($_SESSION['user']) || ($zip->open($zname, ZipArchive::CREATE)!==TRUE))
{
	header("Location: $absurl/error.php");
}
else
{
	header('Content-type: application/octet-stream');
	header('Content-Disposition: attachment; filename="classesUML.zip"');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');
  $conn=sql_conn();
	//													0										1											2					          3									4											5								 6						   7                       8
  $query="SELECT Nome, CodAuto FROM Classe";
	$list=mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
	$file = null;
	//exec("mkdir ./uml");
	
	$classes = array();
	while($row=mysql_fetch_row($list))
	{
		$classes[] = $row;
	}
	foreach($classes as $class)
	{
		$str = '';
		$filename = "uml/Class{$class['0']}.uml";
		//$file = fopen($filename, 'wb');
		$str .= "@startuml\nscale 1000*1000\nskinparam classAttributeIconSize 0\nclass $class[0]{\n";
		//fwrite($file, "@startuml\nscale 1000*1000\nskinparam classAttributeIconSize 0\nclass $class[0]{\n");
		//proprietà
		$query = "SELECT AccessMod, Nome, Tipo FROM Attributo WHERE Classe = $class[1]";
		$list = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
		while($row=mysql_fetch_row($list))
			$str .= "$row[0] $row[1] : $row[2]\n";
			//fwrite($file, "$row[0] $row[1] : $row[2]\n");
		$str .= "__\n";
		//fwrite($file, "__\n");
		//metodi
		$query = "SELECT CodAuto, Nome, AccessMod, ReturnType FROM Metodo WHERE Classe = $class[1]";
		$list = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
		$methods = array();
		while($row = mysql_fetch_row($list))
		{
			$methods[] = $row;
		}
		//parametri
		foreach($methods as $met)
		{
			$query = "SELECT Nome, Tipo FROM Parametro WHERE Metodo = $met[0]";
			$list = mysql_query($query, $conn) or fail("Query fallita: ".mysql_error($conn));
			$str .= "$met[2] $met[1](";
			//fwrite($file, "$met[2] $met[1](");
			if($row = mysql_fetch_row($list))
				$str .= "$row[0] : $row[1]";
				//fwrite($file, "$row[0] : $row[1]");
			while($row = mysql_fetch_row($list))
				$str .= ",$row[0] : $row[1]";
				//fwrite($file, ",$row[0] : $row[1]");
			$str .= ") : $met[3]\n";
			//fwrite($file, ") : $met[3]\n");
		}
		$str .= "__\n";
		//fwrite($file, "__\n");
		//segnali
		$query = "SELECT s.Nome FROM Segnali s, SegnaliClassi sc WHERE s.CodAuto = sc.Segnale AND sc.Classe=$class[1]";
		$list = mysql_query($query, $conn) or fail("query fallita");
		while($row = mysql_fetch_row($list))
			$str .= "<<signal>> $row[0]\n";
			//fwrite($file, "<<signal>> $row[0]\n");
		$str .= "\n}\nhide circle\n@enduml";
		//fwrite($file, "\n}\nhide circle\n@enduml");
		$zip->addFromString($filename, $str);
		//fclose($file);
	}
	//fclose($file);
	//exec("rm ./ClassesUML.zip");
	//exec("plantuml -charset UTF-8 -o \"./images\" \"./uml/*.uml\" ");
	//exec("zip -r ./ClassesUML.zip ./uml");
	//exec("rm -rf ./images");
	//exec("rm -rf ./uml");
	$zip->close();
	$file = fopen($zname, "rb");
	fpassthru($file);
	fclose($file);
	unlink($zname);
}
