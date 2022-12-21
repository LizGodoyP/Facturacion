<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<?php
	include "includes/scripts.php";
	?>
	<title>Sistema de Ventas</title>
</head>

<body>
	<?php
	include "includes/header.php";
	include "../conexion.php";

	//Datos empresa
	$nit = '';
	$nombreEmpresa = '';
	$razonSocial = '';
	$telEmpresa = '';
	$emailEmpresa = '';
	$dirEmpresa = '';
	$iva = '';

	$query_empresa = mysqli_query($conection, "SELECT * FROM configuracion");
	$row_empresa = mysqli_num_rows($query_empresa);
	if ($row_empresa > 0) {
		while ($arrInfoEmpresa = mysqli_fetch_assoc($query_empresa)) {
			$nit = $arrInfoEmpresa['nit'];
			$nombreEmpresa = $arrInfoEmpresa['nombre'];
			$razonSocial = $arrInfoEmpresa['razon_social'];
			$telEmpresa = $arrInfoEmpresa['telefono'];
			$emailEmpresa = $arrInfoEmpresa['email'];
			$dirEmpresa = $arrInfoEmpresa['direccion'];
			$iva = $arrInfoEmpresa['iva'];
		}
	}




	$query_dash = mysqli_query($conection, "CALL dataDashboard();");
	$result_dash = mysqli_num_rows($query_dash);
	if ($result_dash > 0) {
		$data_dash = mysqli_fetch_assoc($query_dash);
		mysqli_close($conection);
	}
	?>
	<section id="container">
		<div class="divContainer">
			<div>
				<h1 class="titlePanelControl">
					Panel de Control
				</h1>
			</div>
			<div class="dashboard">

				<?php
				if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
				?>
					<a href="lista_usuarios.php">
						<i class="fas fa-users"></i>
						<p>
							<strong>Usuarios</strong><br>
							<span><?php echo $data_dash['usuarios']; ?></span>
						</p>
					</a>
				<?php }
				?>
				<a href="lista_clientes.php">
					<i class="fas fa-user"></i>
					<p>
						<strong>Clientes</strong><br>
						<span><?php echo $data_dash['clientes']; ?></span>
					</p>
				</a>
				<a href="lista_producto.php">
					<i class="fas fa-cubes"></i>
					<p>
						<strong>Productos</strong><br>
						<span><?php echo $data_dash['productos']; ?></span>
					</p>
				</a>
				<a href="ventas.php">
					<i class="fa-solid fa-file"></i>
					<p>
						<strong>Ventas</strong><br>
						<span><?php echo $data_dash['ventas']; ?></span>
					</p>
				</a>
			</div>
		</div>

		<div class="divInfoSistema">
			<div>
				<h1 class="titlePanelControl">
					Configuración
				</h1>
			</div>
			<div class="containerPerfil">
				<div class="containerDataUser">
					<div class="logoUser">
						<img src="img/logoUser.png" alt="">
					</div>
					<div class="divDataUser">
						<h4>Información personal</h4>


						<div>
							<label>Nombre:</label><span><?php echo $_SESSION['nombre']; ?> </span>
						</div>
						<div>
							<label>Correo: </label><span><?php echo $_SESSION['email']; ?></span>
						</div>
						<h4>Datos Usuario</h4>
						<div>
							<label>Rol: </label><span><?php echo $_SESSION['rol_name']; ?></span>
						</div>
						<div>
							<label>Usuario: </label><span><?php echo $_SESSION['user']; ?></span>
						</div>
						<h4>Cambiar contraseña</h4>
						<form action="" method="post" name="frmChangePass" id="frmChangePass">
							<div>
								<input type="password" name="txtPassUser" id="txtPassUser" placeholder="Contraseña actual" required>
							</div>
							<div>
								<input class="newPass" type="password" name="txtNewPassUser" id="txtNewPassUser" placeholder="Nueva contraseña " required>
							</div>
							<div>
								<input class="newPass" type="password" name="txtPassConfirm" id="txtPassConfirm" placeholder="Confirmar contraseña" required>
							</div>
							<div class="alertChangePass" style="display: none;">

							</div>
							<div class="save">
								<button type="submit" class="btn_save btnChangePass"><i class="fas fa-key"></i> Cambiar contraseña</button>
							</div>
						</form>

					</div>
				</div>
				<?php if ($_SESSION['rol'] == 1) { ?>
					<div class="containerDataEmpresa">
						<div class="logoEmpresa">
							<img src="img/logo_sistema.png" alt="">
						</div>
						<h4>Datos de la Empresa</h4>
						<form action="" method="post" name="frmEmpresa" id="frmEmpresa">
							<input type="hidden" name="action" value="updateDataEmpresa">
							<div>
								<label>DNI:</label><input type="text" name="txtNit" id="txtNit" placeholder="RUC de la Empresa" value="<?= $nit; ?>" required>
							</div>
							<div>
								<label>Nombre:</label><input type="text" name="txtNombre" id="txtNombre" placeholder="Nombre de la Empresa" value="<?= $nombreEmpresa; ?>" required>
							</div>
							<div>
								<label>Razón social:</label><input type="text" name="txtRSocial" id="txtRSocial" placeholder="Razón social" value="<?= $razonSocial; ?>" >
							</div>
							<div>
								<label>Teléfono:</label><input type="text" name="txtTelEmpresa" id="txtTelEmpresa" placeholder="Número de teléfono" value="<?= $telEmpresa; ?>" required>
							</div>
							<div>
								<label>Correo electrónico:</label><input type="email" name="txtEmailEmpresa" id="txtEmailEmpresa" placeholder="Correo electrónico" value="<?= $emailEmpresa; ?>" required>
							</div>
							<div>
								<label>Dirección:</label><input type="text" name="txtDirEmpresa" id="txtDirEmpresa" placeholder="Dirección de la empresa" value="<?= $dirEmpresa; ?>" required>
							</div>
							<div>
								<label>IGV (%):</label><input type="text" name="txtIva" id="txtIva" placeholder="Impuesto General de Ventas" value="<?= $iva; ?>" required>
							</div>
							<div class="alertFormEmpresa" style="display: none;"></div>
							<div class="save">
								<button type="submit" class="btn_save btnChangePass"><i class="far fa-save fa-lg"></i> Guardar datos</button>
							</div>

						</form>
					</div>
				<?php
				} ?>
			</div>
		</div>
	</section>

	<?php
	include "includes/footer.php";
	?>
</body>

</html>