<?php
    session_start();
  

include "../conexion.php";

if (!empty($_POST)) 
{
    $alert = '';
    //No pueden ir vacíos los sgtes campos
    if (empty($_POST['nombre'])) 
    {
        $alert = '<p class="msg_error">El campo nombre es obligatorio.</p>';
    } else {
      
        $nit        = $_POST['nit'];
        $nombre     = $_POST['nombre'];
        $telefono   = $_POST['telefono'];
        $direccion  = $_POST['direccion'];
        $usuario_id = $_SESSION['idUser'];

       

        if(is_numeric($nit) and $nit != 0 ){
            $query = mysqli_query($conection, "SELECT * FROM cliente WHERE nit = '$nit'");
            $result = mysqli_fetch_array($query);
        } 
        if ($result>0) {            
            $alert = '<p class="msg_error">El número de DNI ya existe.</p>';
            
        }else{
            $query_insert = mysqli_query($conection, "INSERT INTO cliente(nit, nombre, telefono, direccion, usuario_id) 
            VALUES('$nit', '$nombre', '$telefono', '$direccion', '$usuario_id' )");
           

            if ($query_insert) {
                $alert = '<p class="msg_save">Cliente guardado correctamente.</p>';
            } else {
                $alert = '<p class="msg_error">Error al guardar al cliente.</p>';
                
            }

        }        
    }
    mysqli_close($conection);
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php include "includes/scripts.php";    ?>
    <title>Registrar Cliente</title>
</head>

<body>
    <?php include "includes/header.php";    ?>

    <section id="container">

        <div class="form_register">
            <h1><i class="fa-solid fa-user-plus"></i> Registro Cliente</h1>
            <hr>
            <div class="alert"><?php echo isset($alert)  ? $alert : ''; ?></div>

            <form action="" method="POST">
                <label for="nit">DNI</label>
                <input type="number" name="nit" id="nit" placeholder="Número de DNI">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" placeholder="Nombre completo">
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" placeholder="Teléfono">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" placeholder="Dirección">
                
                <div class="save">
                    <input type="submit" value="Guardar Cliente" class="btn_save">
                </div>

            </form>
        </div>

    </section>

    <?php include "includes/footer.php";    ?>
</body>

</html>