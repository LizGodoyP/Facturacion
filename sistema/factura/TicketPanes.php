<?php
//print_r($configuracion); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Ticket</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<div id="page_pdf">
		<div class="ticket">
			<div>
				<p><label>N° de Pedido: </label> <?php echo $factura['nofactura']; ?></p>
				<p><label>Fecha: </label> <?php echo $factura['fecha']; ?></p>
			</div>
			<div>
				<p><label>Hora: </label> <?php echo $factura['hora']; ?></p>
				<p><label>Nombre Cliente: </label> <?php echo $factura['nombre']; ?></p>
			</div>

		</div>
		<table id="factura_detalle">
			<thead>
				<tr>
					<th class="textcenter" width="10px">Cant.</th>
					<th class="textcenter" width="40px">Descripción</th>
					<th class="textcenter" width="40px">Precio Unitario.</th>
					<th class="textcenter" width="40px">Categoria</th>

				</tr>
			</thead>
			<tbody>

				<?php

				if ($result_detalle > 0) {
					while ($row = mysqli_fetch_assoc($query_productos)) {
						if ($row['categoria'] == 'Bebidas') {
				?>
							<tr>
								<td class="textcenter"><?php echo $row['cantidad']; ?></td>
								<td class="textcenter"><?php echo $row['descripcion']; ?></td>
								<td class="textcenter"><?php echo $row['precio_venta']; ?></td>
								<td class="textcenter"><?php echo $row['categoria']; ?></td>
							</tr>
				<?php
						}
					}
				}
				?>
			</tbody>
		</table>
	</div>
</body>

</html>