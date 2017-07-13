<?php

require('../Functions/mysql_fun.php');
require('../Functions/page_builder.php');
require('../Functions/urlLab.php');

session_start();

$absurl=urlbasesito();
if(empty($_SESSION['user'])){
	header("Location: $absurl/error.php");
}
else{
	$id=$_GET['id'];
	$id=mysql_escape_string($id);
	$conn=sql_conn();
	$query="SELECT c.Nome, c.Descrizione
			FROM Classe c
			WHERE c.CodAuto='$id'"; //query che carica il metodo di id = $id
	$met=mysql_query($query,$conn) or fail("Query fallita: ".mysql_error($conn));
	$row=mysql_fetch_row($met);
  $title="Header Classe - $row[0]";
  startpage_builder($title);
  $desc = preg_replace("/\\\\file{([a-zA-Z _0-9]*)}/", "\${1}", $row[1]);
  $desc = preg_replace("/\\\\url{([a-zA-Z _:\\/\\-0-9]*)}/", '{@link ${1}}', $desc);
  $desc = preg_replace("/\\\\\\\\/", '', $desc);

echo<<<END

			<div id="content">
				<div class="widget">
					<h4 class="widget-title">Header</h4>
					<code>
						/**<br />
            * @desc $desc <br />
            */
END;
}
	endpage_builder();
?>