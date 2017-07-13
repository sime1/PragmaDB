<?php

require('../../Functions/mysql_fun.php');
require('../../Functions/page_builder.php');
require('../../Functions/urlLab.php');

session_start();

$absurl=urlbasesito();

if(empty($_SESSION['user'])){
	header("Location: $absurl/error.php");
}
else{
	header('Content-type: application/x-tex');
	header('Content-Disposition: attachment; filename="sfin.tex"');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');
	echo<<<END
\\normalsize
\\begin{longtable}{|c|c|c|}
\hline Classe & SFIN & Esito \\\\
\hline 
END;
	$accettabili = 0;
	$ottimali = 0;
	$non_accettabili = 0;
	$esito = '';
	$conn=sql_conn();
	$query='SELECT count(*), r.A as fin FROM Relazione r GROUP BY r.A';
	$res = mysql_query($query, $conn);
	$classes = array();
	while($row = mysql_fetch_row($res))
	{
		$classes[] = $row;
	}

	$query = 'SELECT 0, c.CodAuto as fin FROM Classe c WHERE c.CodAuto NOT IN (SELECT A FROM Relazione)';
	$res = mysql_query($query, $conn);
	while($row = mysql_fetch_row($res))
	{
		$classes[] = $row;
	}

	foreach($classes as $class)
	{
		$query = "SELECT PrefixNome FROM Classe WHERE CodAuto=$class[1]";
		$res = mysql_query($query, $conn);
		$row = mysql_fetch_row($res);
		$class_name = str_replace('<', '$<$', $row[0]);
		$class_name = str_replace('>', '$>$', $class_name);
		if($class[0] > 3)
		{
			$esito = 'Ottimale';
			$ottimali++;
		}
		else
		{
			$esito = 'Accettabile';
			$accettabili++;
		}
		echo "$class_name & $class[0] & $esito \\\\\n\\hline ";
	}
	echo<<<END
\\end{longtable}
END;

	echo<<<END
\\begin{table}[h]
	\\centering
	\\begin{tabular}{l r}
		\\hline
		\\rule[-0.3cm]{0cm}{0.8cm}
		\\textbf{Esito} & \\textbf{Numero} \\\\
		\\hline
		\\rule[0cm]{0cm}{0.4cm}
		Accettabile & $accettabili \\\\
		\\rule[0cm]{0cm}{0.4cm}
		Ottimale & $ottimali \\\\
		\\hline
  \\end{tabular}
	\\caption{Esiti SFIN}
\\end{table}
END;
}
?>
