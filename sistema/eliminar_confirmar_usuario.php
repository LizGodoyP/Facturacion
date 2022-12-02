<?php
session_start();
if($_SESSION['rol'] != 1) 
{
 header("location: ./");    
}

    include "../conexion.php";
    if(!empty($_POST))
    {
        if($_POST['idusuario']==1){
            header("location: lista_usuarios.php");
            mysqli_close($conection);
            exit;
        }
        
        $idusuario = $_POST['idusuario'];
        // $query_delete = mysqli_query($conection,"DELETE FROM usuario WHERE idusuario = $idusuario");
        $query_delete = mysqli_query($conection,"UPDATE usuario SET estatus = 0 WHERE idusuario=$idusuario");
        mysqli_close($conection);
        if ($query_delete) {
            header("location: lista_usuarios.php");
            # code...
        }else{
            echo "Error al eliminar";
        }
    }


    if (empty($_REQUEST['id']) || $_REQUEST['id'] == 1) {
        header("location: lista_usuarios.php");
        mysqli_close($conection);
    } else {
   
        $idusuario = $_REQUEST['id'];

        $query = mysqli_query($conection, "SELECT u.nombre,u.usuario,r.rol 
                                            FROM usuario u 
                                            INNER JOIN rol r 
                                            ON u.rol = r.idrol 
                                            WHERE u.idusuario=$idusuario AND estatus=1");
        mysqli_close($conection);

        $result = mysqli_num_rows($query);

        if ($result > 0) {
            while ($data = mysqli_fetch_array($query)) {
                $nombre  = $data['nombre'];
                $usuario = $data['usuario'];
                $rol     = $data['rol'];
            }
        } else {
            header("location: lista_usuarios.php");
        }
    }
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <?php
    include "includes/scripts.php";
    ?>
    <title>Eliminar Usuario</title>
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <section id="container">
        <div class="data_delete">
            <i class="fa-solid fa-user-xmark fa-7x" style="color: gray;"></i>
            <br>
            <br>
            <h2>¿Está seguro de eliminar el siguiente registro?</h2>
            <p>Nombre: <span><?php echo $nombre; ?></span></p>
            <p>Usuario: <span><?php echo $usuario; ?></span></p>
            <p>Tipo de usuario: <span><?php echo $rol; ?></span></p>

            <form method="POST" action="">
                <input type="hidden" name="idusuario" value="<?php echo $idusuario; ?>">
                <a href="lista_usuarios.php" class="btn_cancel"><i class="fa-solid fa-ban"></i> Cancelar</a>
                <button type="submit" class="btn_ok"><i class="fa-regular fa-trash-can"></i> Eliminar</button>
            </form>
        </div>
    </section>

    <?php
    include "includes/footer.php";
    ?>
</body>

</html>