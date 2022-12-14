<?php
session_start();
if ($_SESSION['rol'] != 1) {
    header("location: ./");
}

include "../conexion.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php
    include "includes/scripts.php";
    ?>
    <title>Listas de Usuarios</title>
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <section id="container">
        <?php
        //Cambié el REQUEST por SESSION
        $busqueda = strtolower($_REQUEST['busqueda']);

        if ($busqueda == '') {
            echo "<script> Swal.fire(
                'Calma!',
                'Escribe algo!',
                'error'
              ) </script>";
            // header("Location: lista_usuarios.php");
            // mysqli_close($conection);

        }



        ?>





        <div class="lista_usuario">
            <div class="usuario">
                <h1>Lista de Usuarios</h1>
                <a href="registro_usuario.php" class="btn_new">Crear usuario</a>

            </div>

            <div class="Buscar">
                <form action="buscar_usuario.php" method="get" class="form_search">
                    <input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="<?php echo $busqueda; ?>">
                    <input type="submit" value="Buscar" class="btn_search">
                </form>

            </div>


        </div>

        <div class="containerTable">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>


                <?php


                //paginator
                $rol = '';
                if ($busqueda == 'administrador') {
                    $rol = "OR rol LIKE '%1%' ";
                } else if ($busqueda == 'supervisor') {
                    $rol = "OR rol LIKE '%2%' ";
                } else if ($busqueda == 'vendedor') {
                    $rol = "OR rol LIKE '%3%' ";
                }




                $sql_register    = mysqli_query($conection, "SELECT count(*) AS total_registro FROM usuario WHERE ( idusuario LIKE '%$busqueda%' OR nombre LIKE '%$busqueda%' OR correo LIKE '%$busqueda%' OR usuario LIKE '%$busqueda%' $rol ) AND estatus=1");
                $result_register = mysqli_fetch_array($sql_register);
                $total_registro  = $result_register['total_registro'];

                $por_pagina = 10;

                if (empty($_GET['pagina'])) {
                    $pagina = 1;
                    # code...
                } else {
                    $pagina = $_GET['pagina'];
                }
                $desde = ($pagina - 1) * $por_pagina;
                $total_paginas = ceil($total_registro / $por_pagina);





                $query = mysqli_query($conection, "SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.rol FROM usuario u INNER JOIN rol r ON u.rol = r.idrol 
            WHERE 
            ( u.idusuario LIKE '%$busqueda%' OR 
            u.nombre LIKE '%$busqueda%' OR 
            u.correo LIKE '%$busqueda%' OR 
            u.usuario LIKE '%$busqueda%' OR
            r.rol LIKE '%$busqueda%' )
            AND
            
            estatus=1 ORDER BY u.idusuario ASC LIMIT $desde, $por_pagina
                ");
                mysqli_close($conection);

                $result = mysqli_num_rows($query);

                if ($result > 0) {
                    while ($data = mysqli_fetch_array($query)) {


                ?>
                        <tr>
                            <td><?php echo $data["idusuario"] ?></td>
                            <td><?php echo $data["nombre"] ?></td>
                            <td><?php echo $data["correo"] ?></td>
                            <td><?php echo $data["usuario"] ?></td>
                            <td><?php echo $data["rol"] ?></td>
                            <td>
                                <a class="link_edit" href="editar_usuario.php?id=<?php echo $data["idusuario"]; ?>">Editar</a>
                                <?php
                                if ($data["idusuario"] != 1) {
                                ?>


                                    |
                                    <a class="link_delete" href="eliminar_confirmar_usuario.php?id=<?php echo $data["idusuario"]; ?>">Eliminar</a>
                                <?php } ?>
                            </td>
                        </tr>

                <?php
                    }
                } else {

                    header("Location: lista_usuarios.php");
                    mysqli_close($conection);
                }
                ?>

            </table>
        </div>
        <?php
        if ($total_registro != 0) {
            # code...

        ?>


            <!-- paginator -->
            <div class="paginator">
                <ul>
                    <?php
                    if ($pagina != 1) {
                        # code...

                    ?>



                        <li><a href="?pagina=<?php echo 1; ?>&busqueda=<?php echo $busqueda ?>"> |< </a>
                        </li>
                        <li><a href="?pagina=<?php echo $pagina - 1; ?>&busqueda=<?php echo $busqueda ?>">
                                << </a>
                        </li>
                    <?php
                    }


                    for ($i = 1; $i <= $total_paginas; $i++) {

                        if ($i == $pagina) {
                            echo '<li class="pageSelected">' . $i . '</li>';
                        } else {
                            echo '<li><a href="?pagina=' . $i . '&busqueda=' . $busqueda . '">' . $i . '</a></li>';
                        }
                    }
                    if ($pagina != $total_paginas) {

                    ?>



                        <li><a href="?pagina=<?php echo $pagina + 1; ?>&busqueda=<?php echo $busqueda ?>"> >> </a></li>
                        <li><a href="?pagina=<?php echo $total_paginas; ?>&busqueda=<?php echo $busqueda ?>"> >| </a></li>
                    <?php
                    }
                    ?>
                </ul>
            </div>

        <?php
        }
        ?>
    </section>


    <?php
    include "includes/footer.php";
    ?>
    <!-- <script>
        function validarbusqueda($busqueda) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong!',
             
            })

        };
    </script> -->
</body>

</html>