<?php
session_start();
if ($_SESSION['rol'] != 1 and $_SESSION['rol'] != 2) {
    header("location: ./");
}


include "../conexion.php";

if (!empty($_POST)) 

{
    

    $alert = '';
    if (empty($_POST['producto']) || empty($_POST['categoria']) || empty($_POST['precio']) || $_POST['precio'] <=0 || empty($_POST['id']) || empty($_POST['foto_actual']) || empty($_POST['foto_remove']) ) {
        $alert = '<p class="msg_error">Todos los campos son obligatorios.</p>';
    } else {
        $option = '';
        $codproducto   = $_POST['id'];
        $producto      = $_POST['producto'];
        $idcategoria = $_POST['idcategoria'];
        $categoria     = $_POST['categoria'];

        $precio        = $_POST['precio'];
        $imgProducto   = $_POST['foto_actual'];
        $imgRemove     = $_SESSION['foto_remove'];
        
        $foto        = $_FILES['foto'];
        $nombre_foto = $foto['name'];
        $type        = $foto['type'];
        $url_temp    =$foto['tmp_name'];

        $upd = '';
        
        

        if($nombre_foto != '')
        {
            $destino       = 'img/uploads/';
            $img_nombre    = 'img_'.md5(date('d-m-Y H:m:s'));
            $imgProducto   = $img_nombre.'.jpg';
            $src           = $destino.$imgProducto;
        }else{
            if ($_POST['foto_actual'] != $_POST['foto_remove']) {
                $imgProducto = 'img_producto.png';
            }
        }

        $query_update = mysqli_query($conection, "UPDATE producto
                                                    SET descripcion = '$producto',
                                                    precio = $precio,
                                                    categoria = '$categoria',
                                                    foto = '$imgProducto'
                                                    WHERE codproducto = $codproducto ");
                

            if ($query_update) {


                if (($nombre_foto != '' && ($_POST['foto_actual'] != 'img_producto.png')) || ($_POST['foto_actual'] !=  $_POST['foto_remove'])) {
                    unlink('img/uploads/'.$_POST['foto_actual']);
                }
                if($nombre_foto != '')
                {
                    move_uploaded_file($url_temp,$src);
                }
                
                $alert = '<p class="msg_save">Producto actualizado correctamente.</p>';
            } else {
                $alert = '<p class="msg_error">Error al actualizar el producto.</p>';
            }
        }
    }






//VALIDAR PRODUCTO
if (empty($_REQUEST['id'])) {
    header("location: lista_producto.php");
}else{
    $id_producto = $_REQUEST['id'];
    if(!is_numeric($id_producto)){
        header("location: lista_producto.php");
    }

    $query_producto = mysqli_query($conection, "SELECT p.codproducto, p.descripcion, p.precio, p.foto, c.idcategoria, c.categoria 
                                                FROM producto p
                                                INNER JOIN categoria c
                                                ON p.categoria = c.idcategoria
                                                WHERE p.codproducto = $id_producto AND p.estatus=1");
    $result_producto = mysqli_num_rows($query_producto);

    $foto='';
    $claseRemove = 'notBlock';



    if ($result_producto > 0) {
        $data_producto = mysqli_fetch_assoc($query_producto);

        if ($data_producto['foto'] != 'img_producto.png') {
            $claseRemove = '';
            $foto = '<img id="img" src="img/uploads/'.$data_producto['foto'].'" alt="Producto">';
            
        }


        // print_r($data_producto);
    }else{
        header("location: lista_producto.php");
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php include "includes/scripts.php";    ?>
    <title>Actualizar Producto</title>
</head>

<body>
    <?php include "includes/header.php";    ?>

    <section id="container">

        <div class="form_register">
            <h1><i class="fa-solid fa-plus"></i> Actualizar Producto</h1>
            <hr>
            <div class="alert"><?php echo isset($alert)  ? $alert : ''; ?></div>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $data_producto['codproducto']; ?>">
                <input type="hidden" id="foto_actual" name="foto_actual" value="<?php echo $data_producto['foto']; ?>">
                <input type="hidden" id="foto_remove" name="foto_remove" value="<?php echo $data_producto['foto']; ?>">



                <label for="producto">Producto</label>
                <input type="text" name="producto" id="producto" placeholder="Nombre del producto" value="<?php echo $data_producto['descripcion'];?>">
                <label for="categoria" >Tipo de Categoria</label>
                <?php

                $query_categoria = mysqli_query($conection, "SELECT * FROM categoria");
                mysqli_close($conection);
                $result_categoria = mysqli_num_rows($query_categoria);
                
                ?>

                <select name="categoria" id="categoria">
                    <option value=""></option>
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
                <input type="number" step="0.01" name="precio" id="precio" placeholder="Precio del producto" value="<?php echo $data_producto['precio'];?>">
               
                <div class="photo">
                    <label for="foto">Foto</label>
                    <div class="prevPhoto">
                        <span class="delPhoto <?php echo $claseRemove; ?>">X</span>
                        <label for="foto"></label>
                        <?php
                        echo $foto;
                        ?>
                    </div>
                    <div class="upimg">
                    <input type="file" name="foto" id="foto" accept="image/png, .jpeg, .jpg, image/gif">
                    </div>
                    <div id="form_alert"></div>
                </div>


                <div class="save">
                    <button type="submit" class="btn_save"><i class="fa-regular fa-floppy-disk"></i> Actualizar Producto </button>
                </div>

            </form>
        </div>

    </section>

    <?php include "includes/footer.php";    ?>
</body>

</html>