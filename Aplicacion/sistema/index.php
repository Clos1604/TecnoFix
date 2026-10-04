<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLogged = !empty($_SESSION['usuario_logged']) && $_SESSION['usuario_logged'] === true;
$userName = $_SESSION['user_nombre'] ?? 'Administrador TecnoFix';
$userRol = $_SESSION['user_rol'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoFix | Sistema de Gestión de Servicios Técnicos</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

    <!-- =========================================================
         1. PANTALLA DE INICIO DE SESIÓN (RF-01)
         ========================================================= -->
    <section id="login-screen" class="screen <?php echo !$isLogged ? 'active' : ''; ?>">
        <div class="login-wrapper">

            <div class="login-brand">
                <div class="brand-logo-icon">🔧</div>
                <h2>TecnoFix</h2>
                <p class="login-subtitle">Sistema de Gestión de Servicios Técnicos</p>
            </div>

            <div class="login-card">
                <h1>Iniciar sesión</h1>
                <p class="login-description">Ingresa tus datos para acceder al sistema.</p>

                <!-- Alerta de Error Dinámica -->
                <div id="login-alert" class="alert alert-danger" style="display: none;"></div>

                <form id="login-form" method="POST">
                    <div class="form-group">
                        <label for="username">Usuario / Correo electrónico</label>
                        <input type="text" id="username" name="username" placeholder="Ej. admin@tecnofix.com" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" id="btn-login-submit">
                        Iniciar sesión
                    </button>
                </form>

                <p class="prototype-label">
                    Prototipo V3 · Sistema Implementado TecnoFix (PHP & MySQL)
                </p>
            </div>
        </div>
    </section>

    <!-- =========================================================
         2. APLICACIÓN PRINCIPAL (PANEL DE CONTROL & MÓDULOS)
         ========================================================= -->
    <section id="app-screen" class="screen <?php echo $isLogged ? 'active' : ''; ?>">

        <!-- Menú Lateral -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-logo-icon">🔧</div>
                <span>TecnoFix</span>
                <button class="mobile-close-btn" id="close-sidebar-btn">×</button>
            </div>

            <div class="user-mini">
                <div class="user-avatar"><?php echo strtoupper(substr($userName, 0, 2)); ?></div>
                <div class="user-info">
                    <strong id="logged-user-name"><?php echo htmlspecialchars($userName); ?></strong>
                    <span id="logged-user-rol"><?php echo htmlspecialchars($userRol); ?></span>
                </div>
            </div>

            <nav class="main-nav">
                <button class="nav-item active" data-section="dashboard">
                    <span class="nav-icon">⌂</span> Dashboard
                </button>
                <button class="nav-item" data-section="ordenes">
                    <span class="nav-icon">▤</span> Órdenes de servicio
                </button>
                <button class="nav-item" data-section="clientes">
                    <span class="nav-icon">◉</span> Clientes
                </button>
                <button class="nav-item" data-section="tecnicos">
                    <span class="nav-icon">◌</span> Técnicos
                </button>
                <button class="nav-item" data-section="reportes">
                    <span class="nav-icon">📊</span> Reportes
                </button>
            </nav>

            <button id="logout-btn" class="logout-btn">
                Cerrar sesión
            </button>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">

            <!-- Navbar Móvil Header -->
            <header class="top-header">
                <button class="menu-toggle" id="menu-toggle-btn">☰</button>
                <h1 class="header-title">TecnoFix Software</h1>
                <div class="header-status"><span class="status-dot green"></span> Sistema Conectado</div>
            </header>

            <!-- -----------------------------------------------------
                 SECCIÓN: DASHBOARD
                 ----------------------------------------------------- -->
            <section id="dashboard" class="page-section active">
                <div class="page-header">
                    <div>
                        <span class="section-label">PANEL PRINCIPAL</span>
                        <h2>Resumen del Negocio</h2>
                        <p>Estado operativo en tiempo real de TecnoFix.</p>
                    </div>
                    <span class="date-badge" id="current-date"><?php echo date('d \d\e F \d\e Y'); ?></span>
                </div>

                <div class="stats-grid">
                    <article class="stat-card">
                        <div class="stat-top">
                            <span>Órdenes Activas</span>
                            <span class="stat-icon blue" id="stat-activas">3</span>
                        </div>
                        <strong id="stat-count-activas">3</strong>
                        <small>En proceso / Pendientes</small>
                    </article>

                    <article class="stat-card">
                        <div class="stat-top">
                            <span>En Reparación</span>
                            <span class="stat-icon orange" id="stat-proceso">1</span>
                        </div>
                        <strong id="stat-count-proceso">1</strong>
                        <small>Trabajos de taller</small>
                    </article>

                    <article class="stat-card">
                        <div class="stat-top">
                            <span>Completadas</span>
                            <span class="stat-icon green" id="stat-completadas">1</span>
                        </div>
                        <strong id="stat-count-completadas">1</strong>
                        <small>Listas para entrega</small>
                    </article>
                </div>

                <div class="dashboard-actions">
                    <button class="btn btn-primary" id="btn-nueva-orden-dash">+ Nueva Orden de Servicio</button>
                    <button class="btn btn-secondary" data-go="ordenes">Ver Todas las Órdenes</button>
                </div>

                <div class="table-card">
                    <div class="table-header">
                        <h3>Últimas Órdenes Ingresadas</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Equipo</th>
                                    <th>Estado</th>
                                    <th>Costo Est.</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-orders-body">
                                <!-- Cargado dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- -----------------------------------------------------
                 SECCIÓN: ÓRDENES DE SERVICIO (RF-02)
                 ----------------------------------------------------- -->
            <section id="ordenes" class="page-section">
                <div class="page-header">
                    <div>
                        <span class="section-label">GESTIÓN OPERATIVA</span>
                        <h2>Órdenes de Servicio</h2>
                        <p>Registro, control de flujo de reparación y seguimiento de equipos.</p>
                    </div>
                    <button class="btn btn-primary" id="btn-nueva-orden">+ Crear Nueva Orden</button>
                </div>

                <!-- Barra de Búsqueda y Filtro (Mejora de Usabilidad V2/V3) -->
                <div class="filter-bar">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="search-orders" placeholder="Buscar por código, cliente o equipo...">
                    </div>

                    <div class="filter-group">
                        <label for="filter-status">Estado:</label>
                        <select id="filter-status">
                            <option value="Todos">Todos los estados</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Proceso">En Proceso</option>
                            <option value="Esperando Repuesto">Esperando Repuesto</option>
                            <option value="Completado">Completado</option>
                            <option value="Entregado">Entregado</option>
                        </select>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Equipo</th>
                                    <th>Diagnóstico Inicial</th>
                                    <th>Estado</th>
                                    <th>Técnico</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="orders-table-body">
                                <!-- Contenido dinámico mediante JS AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- -----------------------------------------------------
                 SECCIÓN: CLIENTES (RF-03)
                 ----------------------------------------------------- -->
            <section id="clientes" class="page-section">
                <div class="page-header">
                    <div>
                        <span class="section-label">DIRECTORIO</span>
                        <h2>Gestión de Clientes</h2>
                        <p>Base de datos unificada de clientes registrados.</p>
                    </div>
                </div>
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Cédula / RNC</th>
                                    <th>Teléfono</th>
                                    <th>Correo Electrónico</th>
                                    <th>Dirección</th>
                                </tr>
                            </thead>
                            <tbody id="clients-table-body">
                                <tr>
                                    <td>CLI-001</td>
                                    <td>Juan Pérez</td>
                                    <td>001-1234567-8</td>
                                    <td>809-555-0101</td>
                                    <td>juan.perez@email.com</td>
                                    <td>Av. 27 de Febrero #45, SD</td>
                                </tr>
                                <tr>
                                    <td>CLI-002</td>
                                    <td>Ana Gómez</td>
                                    <td>001-7654321-9</td>
                                    <td>809-555-0202</td>
                                    <td>ana.gomez@email.com</td>
                                    <td>Calle El Sol #12, Santiago</td>
                                </tr>
                                <tr>
                                    <td>CLI-003</td>
                                    <td>Empresa Inversiones SRL</td>
                                    <td>130-998877-1</td>
                                    <td>809-555-0303</td>
                                    <td>contacto@inversiones.com</td>
                                    <td>Torre Empresarial Piso 5</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- -----------------------------------------------------
                 SECCIÓN: TÉCNICOS
                 ----------------------------------------------------- -->
            <section id="tecnicos" class="page-section">
                <div class="page-header">
                    <div>
                        <span class="section-label">PERSONAL</span>
                        <h2>Técnicos del Taller</h2>
                        <p>Asignación de personal técnico y especialidades.</p>
                    </div>
                </div>
                <div class="stats-grid">
                    <article class="stat-card">
                        <h3>Carlos Ruiz</h3>
                        <p><strong>Especialidad:</strong> Micro-soldadura y Laptops</p>
                        <span class="badge badge-success">Disponible</span>
                    </article>
                    <article class="stat-card">
                        <h3>María López</h3>
                        <p><strong>Especialidad:</strong> Recepción y Diagnóstico Rápido</p>
                        <span class="badge badge-info">En Atención</span>
                    </article>
                </div>
            </section>

            <!-- -----------------------------------------------------
                 SECCIÓN: REPORTES
                 ----------------------------------------------------- -->
            <section id="reportes" class="page-section">
                <div class="page-header">
                    <div>
                        <span class="section-label">ANALÍTICA</span>
                        <h2>Reportes del Sistema</h2>
                        <p>Resumen de rendimiento y métricas de servicio.</p>
                    </div>
                </div>
                <div class="table-card">
                    <p>Órdenes atendidas este mes: <strong>14</strong></p>
                    <p>Tiempo promedio de reparación: <strong>2.4 días</strong></p>
                    <p>Satisfacción del cliente: <strong>96%</strong></p>
                </div>
            </section>

        </main>
    </section>

    <!-- =========================================================
         MODAL: CREAR NUEVA ÓRDEN DE SERVICIO
         ========================================================= -->
    <div id="modal-nueva-orden" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nueva Orden de Servicio</h3>
                <button class="modal-close" id="close-modal-orden">&times;</button>
            </div>
            <form id="form-nueva-orden">
                <div class="modal-body">
                    <h4 class="form-section-title">Información del Cliente</h4>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="cliente_nombre">Nombre Completo *</label>
                            <input type="text" id="cliente_nombre" name="cliente_nombre" required placeholder="Ej: Pedro Martínez">
                        </div>
                        <div class="form-group col-6">
                            <label for="cliente_telefono">Teléfono *</label>
                            <input type="text" id="cliente_telefono" name="cliente_telefono" required placeholder="Ej: 809-555-9988">
                        </div>
                    </div>

                    <h4 class="form-section-title">Información del Equipo</h4>
                    <div class="form-row">
                        <div class="form-group col-4">
                            <label for="tipo_dispositivo">Tipo Dispositivo *</label>
                            <select id="tipo_dispositivo" name="tipo_dispositivo" required>
                                <option value="Laptop">Laptop</option>
                                <option value="Smartphone">Smartphone</option>
                                <option value="Desktop PC">Desktop PC</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label for="marca">Marca *</label>
                            <input type="text" id="marca" name="marca" required placeholder="Ej: HP, Samsung">
                        </div>
                        <div class="form-group col-4">
                            <label for="modelo">Modelo *</label>
                            <input type="text" id="modelo" name="modelo" required placeholder="Ej: Pavilion 15">
                        </div>
                    </div>

                    <h4 class="form-section-title">Diagnóstico y Costo</h4>
                    <div class="form-group">
                        <label for="diagnostico_inicial">Diagnóstico Inicial / Falla Reportada *</label>
                        <textarea id="diagnostico_inicial" name="diagnostico_inicial" rows="3" required placeholder="Describa el problema reportado por el cliente..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="costo_estimado">Costo Estimado (DOP $)</label>
                            <input type="number" step="0.01" id="costo_estimado" name="costo_estimado" placeholder="0.00">
                        </div>
                        <div class="form-group col-6">
                            <label for="id_tecnico">Asignar Técnico</label>
                            <select id="id_tecnico" name="id_tecnico">
                                <option value="">Sin Asignar</option>
                                <option value="2">Carlos Ruiz (Técnico Senior)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btn-cancelar-orden">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Orden (Transacción SQL)</button>
                </div>
            </form>
        </div>
    </div>

    <!-- =========================================================
         MODAL: CAMBIAR ESTADO DE ÓRDEN
         ========================================================= -->
    <div id="modal-cambiar-estado" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Actualizar Estado de Orden</h3>
                <button class="modal-close" id="close-modal-estado">&times;</button>
            </div>
            <form id="form-cambiar-estado">
                <input type="hidden" id="modal-estado-id-orden" name="id_orden">
                <div class="modal-body">
                    <p>Cambiando estado para la orden: <strong id="modal-estado-codigo">ORD-2026-000</strong></p>
                    <div class="form-group">
                        <label for="nuevo_estado">Nuevo Estado *</label>
                        <select id="nuevo_estado" name="nuevo_estado" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Proceso">En Proceso</option>
                            <option value="Esperando Repuesto">Esperando Repuesto</option>
                            <option value="Completado">Completado</option>
                            <option value="Entregado">Entregado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="observaciones">Observaciones / Avances</label>
                        <textarea id="observaciones" name="observaciones" rows="3" placeholder="Detalle el trabajo realizado o motivo de cambio de estado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btn-cancelar-estado">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                </div>
            </form>
        </div>
    </div>

    <script src="public/js/app.js"></script>
</body>
</html>
