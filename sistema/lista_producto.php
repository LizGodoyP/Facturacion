<?php
session_start();
include "../conexion.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php
    include "includes/scripts.php";
    ?>
    <title>Listas de Productos</title>
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <section id="container">

        <div class="lista_usuario">
            <div class="usuario">
                <h1>Lista de Productos</h1>
                <a href="registro_producto.php" class="btn_new"><i class="fa-solid fa-plus"></i> Crear producto</a>

            </div>

            <div class="Buscar">
                <form action="buscar_productos.php" method="get" class="form_search">
                    <input type="text" name="busqueda" id="busqueda" placeholder="Buscar">
                    <input type="submit" value="Buscar" class="btn_search">
                </form>

            </div>


        </div>

        <table>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Existencia</th>
                <th>Foto</th>
                <th>Acciones</th>
            </tr>

            <?php

            //paginator
            $sql_register    = mysqli_query($conection, "SELECT count(*) AS total_registro FROM producto WHERE estatus= 1");
            $result_register = mysqli_fetch_array($sql_register);
            $total_registro  = $result_register['total_registro'];

            $por_pagina = 3;

            if (empty($_GET['pagina'])) {
                $pagina = 1;
                # code...
            } else {
                $pagina = $_GET['pagina'];
            }
            $desde = ($pagina - 1) * $por_pagina;
            $total_paginas = ceil($total_registro / $por_pagina);

            $query = mysqli_query($conection, "SELECT * FROM producto 
            WHERE estatus=1 ORDER BY codproducto ASC LIMIT $desde, $por_pagina
                ");

            mysqli_close($conection);

            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_array($query)) {
                    if ($data['foto'] != 'img_producto.png') {
                        $foto = 'img/uploads/' . $data['foto'];
                        # code...
                    } else {
                        $foto = 'img/' . $data['foto'];
                    }



            ?>
                    <tr class="row<?php echo $data["codproducto"]; ?>">
                        <td><?php echo $data["codproducto"]; ?></td>
                        <td><?php echo $data["descripcion"]; ?></td>
                        <td class="celPrecio"><?php echo $data["precio"]; ?></td>
                        <td class="celExistencia"><?php echo $data["existencia"]; ?></td>
                        <td class="img_producto"><img src="<?php echo $foto; ?>" alt="<?php echo $data["descripcion"]; ?>"> </td>
                        <?php
                        if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) { ?>
                            <td>

                                <a class="link_add add_product" product="<?php echo $data["codproducto"]; ?>" href="#"><i class="fa-solid fa-plus"></i> Agregar</a>
                                |
                                <a class="link_edit" href="editar_producto.php?id=<?php echo $data["codproducto"]; ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                                |
                                <a class="link_delete del_product" href="#" product="<?php echo $data["codproducto"]; ?>"><i class="fa-solid fa-trash"></i> Eliminar</a>


                            </td>
                        <?php
                        }
                        ?>
                    </tr>

            <?php
                }
            }
            ?>

        </table>

        <!-- paginator -->
        <div class="paginator">
            <ul>
                <?php
                if ($pagina != 1) {
                    # code...

                ?>

                    <li><a href="?pagina=<?php echo 1; ?>"><i class="fa-solid fa-backward-step"></i></a>
                    </li>
                    <li><a href="?pagina=<?php echo $pagina - 1; ?>"><i class="fa-solid fa-caret-left fa-lg"></i></a>
                    </li>
                <?php
                }


                for ($i = 1; $i <= $total_paginas; $i++) {

                    if ($i == $pagina) {
                        echo '<li class="pageSelected">' . $i . '</li>';
                    } else {
                        echo '<li><a href="?pagina=' . $i . '">' . $i . '</a></li>';
                    }
                }
                if ($pagina != $total_paginas) {

                ?>



                    <li><a href="?pagina=<?php echo $pagina + 1; ?>"><i class="fa-solid fa-caret-right fa-lg"></i></a></li>
                    <li><a href="?pagina=<?php echo $total_paginas; ?>"><i class="fa-solid fa-forward-step"></i></a></li>
                <?php
                }
                ?>
            </ul>
        </div>
    </section>

    <?php
    include "includes/footer.php";
    ?>
</body>

</html>