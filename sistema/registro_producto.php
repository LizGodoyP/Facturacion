<?php
session_start();
if ($_SESSION['rol'] != 1 and $_SESSION['rol'] != 2) {
    header("location: ./");
}


include "../conexion.php";

if (!empty($_POST)) 
{
    

    $alert = '';
    if (empty($_POST['producto']) || empty($_POST['categoria']) || empty($_POST['precio']) || $_POST['precio'] <=0 ||empty($_POST['cantidad']) ||$_POST['cantidad'] <=0 ) {
        $alert = '<p class="msg_error">Todos los campos son obligatorios.</p>';
    }else {


        $producto   = $_POST['producto'];
        $categoria  = $_POST['categoria'];
        $precio     = $_POST['precio'];
        $cantidad   = $_POST['cantidad'];
        $usuario_id = $_SESSION['idUser'];
        
        $foto = $_FILES['foto'];
        $nombre_foto = $foto['name'];
        $type = $foto['type'];
        $url_temp =$foto['tmp_name'];

        $imgProducto = 'img_producto.png';

        
        if($nombre_foto != '')
        {
            $destino       = 'img/uploads/';
            $img_nombre    = 'img_'.md5(date('d-m-Y H:m:s'));
            $imgProducto   = $img_nombre.'.jpg';
            $src           = $destino.$imgProducto;
        }

        $query = mysqli_query($conection, "SELECT * FROM producto WHERE descripcion = '$producto'");
        $result = mysqli_fetch_array($query);
        if ($result > 0) {
            $alert = '<p class="msg_error">El producto ya existe.</p>';
        } else{

        $query_insert = mysqli_query($conection, "INSERT INTO producto(descripcion, categoria, precio, existencia, usuario_id, foto)
                VALUES('$producto', '$categoria', '$precio', '$cantidad', '$usuario_id', '$imgProducto')");

            if ($query_insert) {
                if($nombre_foto != ''){
                    move_uploaded_file($url_temp,$src);
                }

                $alert = '<p class="msg_save">Producto guardado correctamente.</p>';
            } else {
                $alert = '<p class="msg_error">Error al guardar el producto.</p>';
            }
        }
        }
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php include "includes/scripts.php";    ?>
    <title>Registrar Producto</title>
</head>

<body>
    <?php include "includes/header.php";    ?>

    <section id="container">

        <div class="form_register">
            <h1><i class="fa-solid fa-plus"></i> Registro Producto</h1>
            <hr>
            <div class="alert"><?php echo isset($alert)  ? $alert : ''; ?></div>

            <form action="" method="POST" enctype="multipart/form-data">
                <label for="producto">Producto</label>
                <input type="text" name="producto" id="producto" placeholder="Nombre del producto">
                <label for="categoria">Tipo de Categoria</label>
                <?php

                $query_categoria = mysqli_query($conection, "SELECT * FROM categoria");
                mysqli_close($conection);
                $result_categoria = mysqli_num_rows($query_categoria);
                
                ?>

                <select name="categoria" id="categoria">
                    <?php
                    if ($result_categoria > 0) {
                        while ($categoria = mysqli_fetch_array($query_categoria)) { 
                    ?>
                            <option value="<?php echo $categoria["idcategoria"]; ?>"><?php echo $categoria["categoria"] ?></option>
                    <?php

                            # code...
                        }
                    }
                    ?>
                </select>

                <label for="precio">Precio</label>
                <input type="number" step="0.01" name="precio" id="precio" placeholder="Precio del producto">
                <label for="cantidad">Cantidad</label>
                <input type="text" name="cantidad" id="cantidad" placeholder="Cantidad del producto">

                <div class="photo">
                    <label for="foto">Foto</label>
                    <div class="prevPhoto">
                        <span class="delPhoto notBlock">X</span>
                        <label for="foto"></label>
                    </div>
                    <div class="upimg">
                    <input type="file" name="foto" id="foto" accept="image/png, .jpeg, .jpg, image/gif">
                    </div>
                    <div id="form_alert"></div>
                </div>


                <div class="save">
                    <button type="submit" class="btn_save"><i class="fa-regular fa-floppy-disk"></i> Guardar Producto </button>
                </div>

            </form>
        </div>

    </section>

    <?php include "includes/footer.php";    ?>
</body>

</html>