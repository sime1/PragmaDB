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
	$conn=sql_conn();

	$query="SELECT s.CodAuto, s.Nome
			FROM Segnali s
			ORDER BY s.Nome";
	$cl=mysql_query($query,$conn) or fail("Query fallita: ".mysql_error($conn));
	$title="Segnali";
	startpage_builder($title);
/*							<li><a class="link-color-pers" href="$absurl/Package/LaTeX/getpackageclassistbe.php">Package/Classi ST (Back-End)</a></li>
							<li><a class="link-color-pers" href="$absurl/Package/LaTeX/getpackageclassistfe.php">Package/Classi ST (Front-End)</a></li>*/
echo<<<END

			<div id="content">
				<h2>Classi</h2>
				<div class="widget-area-left secondary" role="complementary">
					<aside id="export" class="widget">
						<h4 class="widget-title">Esporta in LaTeX (DP)</h4>
						<ul>

						</ul>
					</aside>
				</div>
				<div class="widget-area-right secondary" role="complementary">
					<aside id="operations" class="widget">
						<h4 class="widget-title">Operazioni</h4>
						<ul>
							<li><a class="link-color-pers" href="$absurl/Classi/Segnali/inseriscisegnale.php">Inserisci Segnale</a></li>
						</ul>
					</aside>
				</div>
				<table>
					<thead>
						<tr>
							<th>Nome</th>
              <th>Operazioni</th>
						</tr>
					</thead>
					<tbody>
END;
	while($row=mysql_fetch_row($cl)){
echo<<<END
      <tr>
        <td>$row[1]</td>
  			<td>
  				<ul>
  					<li><a class="link-color-pers" href="$absurl/Classi/Segnali/Attributi/attributi.php?sig=$row[0]">Attributi</a></li>
  					<li><a class="link-color-pers" href="$absurl/Classi/Segnali/modificasegnale.php?id=$row[0]">Modifica</a></li>
  					<li><a class="link-color-pers" href="$absurl/Classi/Segnali/eliminasegnale.php?id=$row[0]">Elimina</a></li>
  				</ul>
  			</td>
			</tr>
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
