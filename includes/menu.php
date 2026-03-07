<?php
$paginaActual = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-brand">
        <h2>SYSLOG</h2>
        <p>Panel administrativo</p>
    </div>

    <nav class="sidebar-menu">
        <ul>
            <?php if (canAccess("dashboard")): ?>
                <li>
                    <a href="dashboard.php" class="<?php echo $paginaActual == 'dashboard.php' ? 'active' : ''; ?>">
                        Inicio
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("empresas")): ?>
                <li>
                    <a href="empresas.php" class="<?php echo $paginaActual == 'empresas.php' ? 'active' : ''; ?>">
                        Empresas
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("ciudades")): ?>
                <li>
                    <a href="ciudades.php" class="<?php echo $paginaActual == 'ciudades.php' ? 'active' : ''; ?>">
                        Ciudades
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("datacenters")): ?>
                <li>
                    <a href="datacenters.php" class="<?php echo $paginaActual == 'datacenters.php' ? 'active' : ''; ?>">
                        Datacenters
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("nodos")): ?>
                <li>
                    <a href="nodos.php" class="<?php echo $paginaActual == 'nodos.php' ? 'active' : ''; ?>">
                        Nodos
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("equipos")): ?>
                <li>
                    <a href="equipos.php" class="<?php echo $paginaActual == 'equipos.php' ? 'active' : ''; ?>">
                        Equipos
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("interfaces")): ?>
                <li>
                    <a href="interfaces.php" class="<?php echo $paginaActual == 'interfaces.php' ? 'active' : ''; ?>">
                        Interfaces
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("alertas")): ?>
                <li>
                    <a href="alertas.php" class="<?php echo $paginaActual == 'alertas.php' ? 'active' : ''; ?>">
                        Alertas
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("reportes")): ?>
                <li>
                    <a href="reportes.php" class="<?php echo $paginaActual == 'reportes.php' ? 'active' : ''; ?>">
                        Reportes
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("usuarios")): ?>
                <li>
                    <a href="usuarios.php" class="<?php echo $paginaActual == 'usuarios.php' ? 'active' : ''; ?>">
                        Usuarios
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess("grupos")): ?>
                <li>
                    <a href="grupos.php" class="<?php echo $paginaActual == 'grupos.php' ? 'active' : ''; ?>">
                        Grupos
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="../actions/logout.php">
                    Cerrar sesión
                </a>
            </li>
        </ul>
    </nav>
</div>