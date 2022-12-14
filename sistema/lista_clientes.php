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
    <title>Listas de Clientes</title>
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <section id="container">

        <div class="lista_usuario">
            <div class="usuario">
                <h1><i class="fa-solid fa-users"></i> Lista de Clientes</h1>
                <a href="registro_cliente.php" class="btn_new"><i class="fa-solid fa-user-plus"></i> Crear cliente</a>

            </div>

            <div class="Buscar">
                <form action="buscar_cliente.php" method="get" class="form_search">
                    <input type="text" name="busqueda" id="busqueda" placeholder="Buscar">
                    <button type="submit" class="btn_search"><i class="fa fa-search"></i></button>
                </form>

            </div>


        </div>
        <div class="containerTable">
            <table>
                <tr>
                    <th>ID</th>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>

                <?php

                //paginator
                $sql_register    = mysqli_query($conection, "SELECT count(*) AS total_registro FROM cliente WHERE estatus= 1");
                $result_register = mysqli_fetch_array($sql_register);
                $total_registro  = $result_register['total_registro'];

                $por_pagina = 100;

                if (empty($_GET['pagina'])) {
                    $pagina = 1;
                    # code...
                } else {
                    $pagina = $_GET['pagina'];
                }
                $desde = ($pagina - 1) * $por_pagina;
                $total_paginas = ceil($total_registro / $por_pagina);

                $query = mysqli_query($conection, "SELECT * FROM cliente 
            WHERE estatus=1 ORDER BY idcliente ASC LIMIT $desde, $por_pagina
                ");

                mysqli_close($conection);

                $result = mysqli_num_rows($query);
                if ($result > 0) {
                    while ($data = mysqli_fetch_array($query)) {
                        if ($data["nit"] == 0) {
                            $nit = 'C/F';
                        } else {
                            $nit = $data["nit"];
                        }


                ?>
                        <tr>
                            <td><?php echo $data["idcliente"] ?></td>
                            <td><?php echo $nit; ?></td>
                            <td><?php echo $data["nombre"] ?></td>
                            <td><?php echo $data["telefono"] ?></td>
                            <td><?php echo $data["direccion"] ?></td>

                            <td>
                                <a class="link_edit" href="editar_cliente.php?id=<?php echo $data["idcliente"]; ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                                <?php
                                if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) { ?>
                                    |
                                    <a class="link_delete" href="eliminar_confirmar_cliente.php?id=<?php echo $data["idcliente"]; ?>"><i class="fa-solid fa-trash"></i> Eliminar</a>
                                <?php
                                }
                                ?>

                            </td>
                        </tr>

                <?php
                    }
                }
                ?>

            </table>
        </div>

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

                $margen = 3;
                $margen_izquierdo = $pagina-$margen>0? $pagina-$margen:1;
                $margen_derecha = $pagina+$margen<=$total_paginas? $pagina+$margen:$total_paginas;

                for ($i = $margen_izquierdo; $i <= $margen_derecha; $i++) {

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