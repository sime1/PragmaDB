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
	$conn=sql_conn();
  $query="SELECT DISTINCT u.CodAuto,u.IdUC,u.Descrizione, r.CodAuto, r.IdRequisito, r.Descrizione
				FROM (Requisiti r RIGHT JOIN (UseCase u LEFT JOIN RequisitiUC ruc ON u.CodAuto=ruc.UC) ON ruc.CodReq=r.CodAuto)
				";
	$req=mysql_query($query,$conn) or fail("Query fallita: ".mysql_error($conn));
	$title="Requisiti";
	startpage_builder($title);
echo<<<END

			<div id="content">
				<h2>Confronto UseCase - Requisiti</h2>
				<table>
					<thead>
						<tr>
              <th>Id UseCase</th>
              <th>Descrizione UseCase</th>
							<th>Id Requisito</th>
              <th>Descrizione Requisito</th>
						</tr>
					</thead>
					<tbody>
END;
while($row=mysql_fetch_row($req)){
echo<<<END
            <tr>
              <td><a href="$absurl/UseCase/modificausecase.php?id=$row[0]">$row[1]</a></td>
              <td>$row[2]</td>
              <td><a href="$absurl/Requisiti/modificarequisito.php?id=$row[3]">$row[4]</a></td>
              <td>$row[5]</td>

END;
	}
echo<<<END

					</tbody>
				</table>
			</div>
END;
	endpage_builder();
}
?>
