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
	header('Content-Disposition: attachment; filename="useCase.tex"');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');

	$conn=sql_conn();
	//$query_ord="CALL sortForest('UseCase')";
	//									0				1				2	        3            4							5								6								7									8		            9            10                11
	$query="SELECT u.CodAuto,u.IdUC,u.Nome,u.Diagramma,u.Descrizione,u.Precondizioni,u.Postcondizioni,u.ScenarioPrincipale,u.Inclusioni,u.Estensioni,u.ScenariAlternativi, u.Padre
			FROM _MapUseCase h JOIN UseCase u ON h.CodAuto=u.CodAuto
			ORDER BY h.Position";
	//$ord=mysql_query($query_ord,$conn) or fail("Query fallita: ".mysql_error($conn));
	$uc=mysql_query($query,$conn) or fail("Query fallita: ".mysql_error($conn));
	$noChildQuery = "SELECT DISTINCT IdUC FROM UseCase
		    WHERE CodAuto in (SELECT u.CodAuto FROM UseCase u, UseCase f WHERE u.CodAuto = f.Padre)";
	$noChild = mysql_query($noChildQuery, $conn);
	$ncArr = array();
	//echo "\\usepackage{color}\n\\usepackage{colortabl}";
	while($row=mysql_fetch_row($noChild)){
		$ncArr[] = $row[0];
	}
	while($row=mysql_fetch_row($uc)){
$sec = empty($row[11]) ? 'subsection' : 'subsubsection';
foreach($ncArr as $id)
{
	if($id == $row[1])
	{
		echo "\\newpage";
	}
}
echo<<<END
\\$sec{{$row[1]}: {$row[2]}}
\\label{{$row[1]}}
END;
foreach($ncArr as $id)
{
if($id == $row[1])
{
	$name=str_replace('.', '', $row[1]);
echo<<<END

\\begin{figure}[h]
\\centering
\\includegraphics[width=\\textwidth,height=\\textheight,keepaspectratio]{images/UseCase$name.png}
\\caption{{$row[1]}: {$row[2]}}
\\end{figure}
END;
}
}
		$query="SELECT a.Nome
				FROM AttoriUC auc JOIN Attori a ON auc.Attore=a.CodAuto
				WHERE auc.UC='$row[0]'
				ORDER BY a.Nome";
		$attori=mysql_query($query,$conn) or fail("Query fallita: ".mysql_error($conn));
		$row_attore=mysql_fetch_row($attori);
echo<<<END

\\begin{longtable}{l|p{10cm}}
\\rowcolor[gray]{0.8} \\multicolumn{2}{c}{} \\\\
\\rowcolor[gray]{0.8} \\multicolumn{2}{c}{\\textbf{{$row[1]} - {$row[2]}}} \\\\
\\rowcolor[gray]{0.8} \\multicolumn{2}{c}{} \\\\
\\hline
&\\\\
\\textbf{Attori} & $row_attore[0]
END;
		while($row_attore=mysql_fetch_row($attori)){
echo<<<END
, $row_attore[0]
END;
		}
echo<<<END
.\\\\[7pt]
\\textbf{Descrizione} & $row[4]\\\\[7pt]
\\textbf{Precondizione} & $row[5]\\\\[7pt]
\\textbf{Postcondizione} & $row[6]\\\\[7pt]
\\textbf{Scenario principale} &
END;
$lines = preg_split("/\\r\\n|\\r|\\n/", $row[7]);
if(count($lines) > 1)
{
	echo "\\begin{enumerate}\n";
	foreach($lines as $line)
	{
		$line = preg_replace("/^[0-9]+[.]/", "", $line);
		if(!empty($line))
			echo "\\item $line\n";
	}
	echo "\\end{enumerate}\n";
}
else
	echo $lines[0];
echo<<<END
\\\\[7pt]
END;
		if($row[8]!=null){
echo<<<END

\\textbf{Inclusioni} & $row[8]\\\\[7pt]
END;
		}
		if($row[10]!=null){
echo<<<END

\\textbf{Scenari alternativi} & $row[10]\\\\[7pt]
END;
		}
echo<<<END
\\hline
\\end{longtable}


END;
	}
}
?>
