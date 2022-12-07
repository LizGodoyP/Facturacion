<nav>
    <ul>
        <li><a href="./index.php"><i class="fa-solid fa-house"></i> Inicio</a></li>
        <li class="principal">
            <?php 
            if($_SESSION['rol'] == 1)
            {
            
            ?>
            <a href="#"><i class="fa-solid fa-users"></i> Usuarios <span class="arrow"><i class="fas fa-angle-down"></i></span></a>
            <ul>
                <li><a href="./registro_usuario.php"><i class="fa-solid fa-user-plus"></i> Nuevo Usuario</a></li>
                <li><a href="./lista_usuarios.php"><i class="fa-solid fa-users"></i> Lista de Usuarios</a></li>
            </ul>
        </li>
        <?php
        }
        ?>
        <li class="principal">
            <a href="#"><i class="fa-solid fa-users"></i> Clientes <span class="arrow"><i class="fas fa-angle-down"></i></span></a>
            <ul>
                <li><a href="./registro_cliente.php"><i class="fa-solid fa-user-plus"></i> Nuevo Cliente</a></li>
                <li><a href="./lista_clientes.php"><i class="fa-solid fa-users"></i> Lista de Clientes</a></li>
            </ul>
        </li>
        <!-- <li class="principal">
            <a href="#">Proveedores</a>
            <ul>
                <li><a href="#">Nuevo Proveedor</a></li>
                <li><a href="#">Lista de Proveedores</a></li>
            </ul>
        </li> -->
        <li class="principal">
            
            <a href="#"><i class="fa-sharp fa-solid fa-mug-saucer"></i> Productos <span class="arrow"><i class="fas fa-angle-down"></i></span></a>
            <ul>
            <?php 
            if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2)
            {
            
            ?>
                <li><a href="registro_producto.php"><i class="fa-solid fa-plus"></i> Nuevo Producto</a></li>
            <?php
            }
            ?>    
                <li><a href="lista_producto.php"><i class="fa-sharp fa-solid fa-cubes-stacked"></i> Lista de Productos</a></li>
            </ul>
        </li>
        <li class="principal">
            <a href="#"><i class="fa-solid fa-file"></i> Ventas <span class="arrow"><i class="fas fa-angle-down"></i></span></a>
            <ul>
                <li><a href="nueva_venta.php"><i class="fa-solid fa-file-pen"></i> Nuevo Venta</a></li>
                <li><a href="ventas.php"><i class="fa-solid fa-file"></i> Ventas</a></li>
            </ul>
        </li>
    </ul>
</nav>