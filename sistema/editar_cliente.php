
<?php
session_start();

include "../conexion.php";

if (!empty($_POST)) {
    $alert = '';
    if (empty($_POST['nombre']) ) {
        $alert = '<p class="msg_error">Todos los campos son obligatorios.</p>';
    } else {
        include "../conexion.php";
        $idCliente = $_POST['id'];
        $nit = $_POST['nit'];
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];
        
        $result = 0;
        if (is_numeric($nit) and $nit !=0) 
        {
            $query = mysqli_query($conection, "SELECT * FROM cliente 
            WHERE (nit = '$nit' AND idcliente != $idCliente)");

            $result= mysqli_fetch_array($query);
            $result=count($result);
            # code...
        }


        if ($result > 0) {
            $alert = '<p class="msg_error">El DNI ya existe.</p>';
        } else {

            if($nit=='')
            {
                $nit=0;

            }    
                $sql_update = mysqli_query($conection,"UPDATE cliente 
                            SET nit = '$nit', nombre = '$nombre', telefono = $telefono, direccion = '$direccion' 
                            WHERE idcliente = $idCliente ");

            if ($sql_update) {
                $alert = '<p class="msg_save">Cliente actualizado correctamente.</p>';
            } else {
                $alert = '<p class="msg_error">Error al actualizar el cliente.</p>';
            }
        }
    }
   
}

//Mostrar datos
if (empty($_REQUEST["id"])) {
    header('location: lista_clientes.php');
    mysqli_close($conection);
}
$idcliente = $_REQUEST['id'];
$sql = mysqli_query($conection, "SELECT * FROM cliente WHERE idcliente=$idcliente AND estatus=1");
mysqli_close($conection);
$result_sql = mysqli_num_rows($sql);
if ($result_sql == 0) {
    header('location: lista_clientes.php');
} else {
    
    while ($data = mysqli_fetch_array($sql)) {
        $idcliente = $data['idcliente'];
        $nit = $data['nit'];
        $nombre = $data['nombre'];
        $telefono = $data['telefono'];
        $direccion = $data['direccion'];
   

       
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php include "includes/scripts.php";    ?>
    <title>Actualizar Cliente</title>
</head>

<body>
    <?php include "includes/header.php";    ?>

    <section id="container">

        <div class="form_register">
            <h1>Actualizar Cliente</h1>
            <hr>
            <div class="alert"><?php echo isset($alert)  ? $alert : ''; ?></div>

            <form action="" method="POST">
                <input type="hidden" name="id" value="<?php echo $idcliente;?>">
                <label for="nit">DNI</label>
                <input type="number" name="nit" id="nit" placeholder="Número de DNI" value="<?php echo $nit;?>">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" placeholder="Nombre completo" value="<?php echo $nombre;?> ">
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" placeholder="Teléfono" value="<?php echo $telefono;?>">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" placeholder="Dirección" value="<?php echo $direccion;?> ">
                
                <div class="save">
                    <input type="submit" value="Guardar Cliente" class="btn_save">
                </div>

            </form>
        </div>

    </section>

    <?php include "includes/footer.php";    ?>
</body>

</html>