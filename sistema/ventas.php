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
    <title>Listas de Ventas</title>
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <section id="container">

        <div class="lista_usuario">
            <div class="usuario">
                <h1><i class="far fa-newspaper"></i> Lista de Ventas</h1>
                <a href="nueva_venta.php" class="btn_new"><i class="fas fa-plus"></i> Nueva Venta</a>

            </div>

            <div class="Buscar">
                <form action="buscar_venta.php" method="get" class="form_search">
                    <input type="text" name="busqueda" id="busqueda" placeholder="N° Factura">
                    <button type="submit" class="btn_search"><i class="fa fa-search"></i></button>
                </form>
            </div>
        </div>

        <div>
            <h5>Buscar por Fecha</h5>
            <form action="buscar_venta.php" method="get" class="form_search_date">
                <label for="">De: </label>
                <input type="date" name="decha_de" id="fecha_de" required>
                <label for="">A </label>
                <input type="date" name="fehca_a" id="fecha_a" required>
                <button type="submit" class="btn_view"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <table>
            <tr>
                <th>N°</th>
                <th>Fecha / Hora</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Estado</th>
                <th class="textright">Total Factura</th>
                <th class="textright">Acciones</th>
            </tr>

            <?php

            //paginator
            $sql_register    = mysqli_query($conection, "SELECT count(*) AS total_registro FROM factura WHERE estatus != 10 ");
            $result_register = mysqli_fetch_array($sql_register);
            $total_registro  = $result_register['total_registro'];

            $por_pagina = 5;

            if (empty($_GET['pagina'])) {
                $pagina = 1;
                # code...
            } else {
                $pagina = $_GET['pagina'];
            }
            $desde = ($pagina - 1) * $por_pagina;
            $total_paginas = ceil($total_registro / $por_pagina);

            $query = mysqli_query($conection, "SELECT f.nofactura, f.fecha, f.totalfactura, f.codcliente, 
                                                f.estatus,u.nombre as vendedor, 
                                                cl.nombre as cliente
                                                FROM factura f 
                                                INNER JOIN usuario u
                                                ON f.usuario = u.idusuario
                                                INNER JOIN cliente cl
                                                ON f.codcliente = cl.idcliente
                                                WHERE f.estatus != 10
                                                ORDER BY f.fecha DESC LIMIT $desde, $por_pagina");

            mysqli_close($conection);

            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_array($query)) {


                    if ($data["estatus"] == 1) {
                        $estado = '<span class="pagada"> Pagada</span>';
                    } else {
                        $estado = '<span class="anulada"> Anulada</span>';
                    }

            ?>
                    <tr id="row_<?php echo $data["nofactura"]; ?>">
                        <td><?php echo $data["nofactura"]; ?></td>
                        <td><?php echo $data["fecha"]; ?></td>
                        <td><?php echo $data["cliente"]; ?></td>
                        <td><?php echo $data["vendedor"]; ?></td>
                        <td><?php echo $estado; ?></td>
                        <td class="textright totalfactura"><span>S/.</span><?php echo $data["totalfactura"]; ?></td>


                        <td>
                            <div class="div_acciones">
                                <div>
                                    <button class="btn_view view_factura" type="button" cl="<?php echo $data["codcliente"]; ?>" f="<?php echo $data['nofactura']; ?>"><i class="fas fa-eye"></i></button>
                                </div>
                            

                            <?php
                            if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
                                if ($data["estatus"] == 1) {

                            ?>
                                    <div class="div_factura">
                                    <button class="btn_anular anular_factura" fac="<?php echo $data["nofactura"]; ?>"><i class="fas fa-ban"></i></button>

                                    </div>
                                <?php
                                } else {
                                ?>
                                    <div class="div_factura">
                                        <button type="button" class="btn_anular inactive" ><i class="fas fa-ban"></i></button>
                                    </div>

                            <?php
                                }
                            }
                            ?>
                            </div>


                        </td>
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