/* =========================================================
   TECNOFIX - APP.JS V3
   Lógica del cliente, interacción AJAX, Creación de Usuarios (Admin)
   y Cambio de Contraseña Obligatorio (rol+123)
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    // Referencias DOM principales
    const loginScreen = document.getElementById("login-screen");
    const appScreen = document.getElementById("app-screen");
    const loginForm = document.getElementById("login-form");
    const loginAlert = document.getElementById("login-alert");
    const logoutBtn = document.getElementById("logout-btn");
    
    // Navegación
    const navButtons = document.querySelectorAll(".nav-item");
    const pageSections = document.querySelectorAll(".page-section");
    const sidebar = document.getElementById("sidebar");
    const menuToggleBtn = document.getElementById("menu-toggle-btn");
    const closeSidebarBtn = document.getElementById("close-sidebar-btn");

    // Tablas y Filtros
    const ordersTableBody = document.getElementById("orders-table-body");
    const dashboardOrdersBody = document.getElementById("dashboard-orders-body");
    const usersTableBody = document.getElementById("users-table-body");
    const searchOrdersInput = document.getElementById("search-orders");
    const filterStatusSelect = document.getElementById("filter-status");

    // Modales Órdenes
    const modalNuevaOrden = document.getElementById("modal-nueva-orden");
    const btnNuevaOrden = document.getElementById("btn-nueva-orden");
    const btnNuevaOrdenDash = document.getElementById("btn-nueva-orden-dash");
    const closeModalOrden = document.getElementById("close-modal-orden");
    const btnCancelarOrden = document.getElementById("btn-cancelar-orden");
    const formNuevaOrden = document.getElementById("form-nueva-orden");

    const modalCambiarEstado = document.getElementById("modal-cambiar-estado");
    const closeModalEstado = document.getElementById("close-modal-estado");
    const btnCancelarEstado = document.getElementById("btn-cancelar-estado");
    const formCambiarEstado = document.getElementById("form-cambiar-estado");

    // Modales Usuarios y Cambio de Password
    const modalCrearUsuario = document.getElementById("modal-crear-usuario");
    const btnCrearUsuario = document.getElementById("btn-crear-usuario");
    const closeModalUsuario = document.getElementById("close-modal-usuario");
    const btnCancelarUsuario = document.getElementById("btn-cancelar-crear-usuario");
    const formCrearUsuario = document.getElementById("form-crear-usuario");

    const modalCambiarPassOblig = document.getElementById("modal-cambiar-password-obligatorio");
    const formCambiarPassOblig = document.getElementById("form-cambiar-password-obligatorio");
    const passChangeAlert = document.getElementById("pass-change-alert");

    // Estado Local de Órdenes (con fallback mock)
    let localOrders = [
        {
            id_orden: 1,
            codigo_orden: 'ORD-2026-001',
            cliente_nombre: 'Juan Pérez',
            telefono: '809-555-0101',
            equipo_info: 'Laptop Dell XPS 15',
            diagnostico_inicial: 'El equipo no enciende tras descarga eléctrica.',
            estado: 'En Proceso',
            costo_estimado: 4500.00,
            tecnico_nombre: 'Carlos Ruiz'
        },
        {
            id_orden: 2,
            codigo_orden: 'ORD-2026-002',
            cliente_nombre: 'Ana Gómez',
            telefono: '809-555-0202',
            equipo_info: 'Smartphone Apple iPhone 13',
            diagnostico_inicial: 'Pantalla fisurada y falla en digitalizador.',
            estado: 'Pendiente',
            costo_estimado: 6800.00,
            tecnico_nombre: 'Carlos Ruiz'
        },
        {
            id_orden: 3,
            codigo_orden: 'ORD-2026-003',
            cliente_nombre: 'Empresa Inversiones SRL',
            telefono: '809-555-0303',
            equipo_info: 'Desktop PC Custom Core i7',
            diagnostico_inicial: 'Mantenimiento preventivo y formateo.',
            estado: 'Completado',
            costo_estimado: 2500.00,
            tecnico_nombre: 'Sin asignar'
        }
    ];

    let localUsers = [
        { id_usuario: 1, nombre: 'Administrador TecnoFix', email: 'admin@tecnofix.com', rol: 'Administrador', debe_cambiar_pass: 0 },
        { id_usuario: 2, nombre: 'Carlos Ruiz', email: 'carlos.tecnico@tecnofix.com', rol: 'Tecnico', debe_cambiar_pass: 0 },
        { id_usuario: 3, nombre: 'María López', email: 'maria.recepcion@tecnofix.com', rol: 'Recepcion', debe_cambiar_pass: 0 },
        { id_usuario: 4, nombre: 'Pedro Ramírez', email: 'pedro.nuevo@tecnofix.com', rol: 'Tecnico', debe_cambiar_pass: 1 }
    ];

    /* =========================================================
       1. AUTENTICACIÓN (LOGIN & LOGOUT) - RF-01
       ========================================================= */
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value;

            if (loginAlert) loginAlert.style.display = "none";

            if (!username || !password) {
                showLoginAlert("Por favor ingrese su usuario y contraseña.");
                return;
            }

            const formData = new FormData();
            formData.append("username", username);
            formData.append("password", password);

            fetch("api/login.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.user) {
                        document.getElementById("logged-user-name").textContent = data.user.nombre;
                        document.getElementById("logged-user-rol").textContent = data.user.rol;
                    }
                    loginScreen.classList.remove("active");
                    appScreen.classList.add("active");

                    // Verificar si debe cambiar contraseña obligatoriamente
                    if (data.debe_cambiar_pass === 1 || (data.user && data.user.debe_cambiar_pass === 1)) {
                        openCambiarPassModal();
                    }

                    loadOrders();
                    loadUsers();
                } else {
                    showLoginAlert(data.message || "Credenciales inválidas. Compruebe usuario/contraseña.");
                }
            })
            .catch(err => {
                // Fallback simulación cliente
                const passEsperada = (username.includes("tecnico") ? "tecnico123" : (username.includes("recepcion") ? "recepcion123" : "1234"));
                if ((username === "admin" || username === "admin@tecnofix.com") && (password === "1234" || password === "admin123")) {
                    loginScreen.classList.remove("active");
                    appScreen.classList.add("active");
                    loadOrders();
                    loadUsers();
                } else if (username === "pedro.nuevo@tecnofix.com" && password === "tecnico123") {
                    loginScreen.classList.remove("active");
                    appScreen.classList.add("active");
                    openCambiarPassModal();
                    loadOrders();
                    loadUsers();
                } else {
                    showLoginAlert("Credenciales inválidas. Para la demo use: admin@tecnofix.com / 1234 o pedro.nuevo@tecnofix.com / tecnico123");
                }
            });
        });
    }

    function showLoginAlert(msg) {
        if (loginAlert) {
            loginAlert.textContent = msg;
            loginAlert.style.display = "block";
        }
    }

    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            fetch("api/logout.php")
            .then(() => {
                appScreen.classList.remove("active");
                loginScreen.classList.add("active");
                if (loginForm) loginForm.reset();
            })
            .catch(() => {
                appScreen.classList.remove("active");
                loginScreen.classList.add("active");
            });
        });
    }

    /* =========================================================
       2. CREACIÓN DE USUARIOS POR ADMINISTRADOR Y CAMBIO DE CLAVE
       ========================================================= */
    function openCambiarPassModal() {
        if (modalCambiarPassOblig) modalCambiarPassOblig.classList.add("active");
    }

    function closeCambiarPassModal() {
        if (modalCambiarPassOblig) modalCambiarPassOblig.classList.remove("active");
    }

    if (formCambiarPassOblig) {
        formCambiarPassOblig.addEventListener("submit", function (e) {
            e.preventDefault();
            const nPass = document.getElementById("nueva_password").value;
            const cPass = document.getElementById("confirmar_password").value;

            if (passChangeAlert) passChangeAlert.style.display = "none";

            if (nPass.length < 6) {
                showPassChangeAlert("La nueva contraseña debe tener al menos 6 caracteres.");
                return;
            }

            if (nPass !== cPass) {
                showPassChangeAlert("Las contraseñas ingresadas no coinciden.");
                return;
            }

            const formData = new FormData(formCambiarPassOblig);
            formData.append("action", "cambiar_password");

            fetch("api/usuarios.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("¡Contraseña actualizada exitosamente! Bienvenido al sistema.");
                    closeCambiarPassModal();
                    formCambiarPassOblig.reset();
                } else {
                    showPassChangeAlert(data.message || "Error al actualizar contraseña.");
                }
            })
            .catch(() => {
                alert("¡Contraseña actualizada con éxito! (Simulación cliente)");
                closeCambiarPassModal();
                formCambiarPassOblig.reset();
            });
        });
    }

    function showPassChangeAlert(msg) {
        if (passChangeAlert) {
            passChangeAlert.textContent = msg;
            passChangeAlert.style.display = "block";
        }
    }

    // Modal Crear Usuario (Admin)
    function openModalUsuario() { if (modalCrearUsuario) modalCrearUsuario.classList.add("active"); }
    function closeModalUsuarioFunc() { if (modalCrearUsuario) modalCrearUsuario.classList.remove("active"); }

    if (btnCrearUsuario) btnCrearUsuario.addEventListener("click", openModalUsuario);
    if (closeModalUsuario) closeModalUsuario.addEventListener("click", closeModalUsuarioFunc);
    if (btnCancelarUsuario) btnCancelarUsuario.addEventListener("click", closeModalUsuarioFunc);

    if (formCrearUsuario) {
        formCrearUsuario.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(formCrearUsuario);
            formData.append("action", "crear");

            fetch("api/usuarios.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("¡Usuario registrado exitosamente!\n" + data.message + "\n\n💡 La contraseña temporal asignada es: " + data.default_password);
                    closeModalUsuarioFunc();
                    formCrearUsuario.reset();
                    loadUsers();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => {
                const nombre = formData.get("nombre");
                const email = formData.get("email");
                const rol = formData.get("rol");
                const defPass = rol.toLowerCase() + "123";

                localUsers.unshift({
                    id_usuario: localUsers.length + 1,
                    nombre: nombre,
                    email: email,
                    rol: rol,
                    debe_cambiar_pass: 1
                });

                alert("¡Usuario registrado exitosamente!\n\nUsuario: " + email + "\nRol: " + rol + "\nContraseña por defecto asignada: " + defPass + "\n\n(El usuario deberá cambiar la clave al iniciar sesión por primera vez)");
                closeModalUsuarioFunc();
                formCrearUsuario.reset();
                loadUsers();
            });
        });
    }

    function loadUsers() {
        fetch("api/usuarios.php")
        .then(res => res.json())
        .then(resData => {
            if (resData.success && Array.isArray(resData.data)) {
                renderUsersTable(resData.data);
            } else {
                renderUsersTable(localUsers);
            }
        })
        .catch(() => {
            renderUsersTable(localUsers);
        });
    }

    function renderUsersTable(users) {
        if (!usersTableBody) return;
        usersTableBody.innerHTML = "";

        users.forEach(u => {
            const tr = document.createElement("tr");
            const passDefecto = u.rol.toLowerCase() + "123";
            const estadoPassBadge = u.debe_cambiar_pass == 1 
                ? `<span class="badge badge-warning">🔒 Primer Inicio (Pendiente Cambio)</span>` 
                : `<span class="badge badge-success">✓ Clave Personalizada</span>`;

            tr.innerHTML = `
                <td>USR-00${u.id_usuario}</td>
                <td><strong>${escapeHtml(u.nombre)}</strong></td>
                <td>${escapeHtml(u.email)}</td>
                <td><span class="badge badge-info">${escapeHtml(u.rol)}</span></td>
                <td><code>${escapeHtml(passDefecto)}</code></td>
                <td>${estadoPassBadge}</td>
            `;
            usersTableBody.appendChild(tr);
        });
    }

    /* =========================================================
       3. NAVEGACIÓN Y MENÚ RESPONSIVO
       ========================================================= */
    navButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            const targetSection = btn.dataset.section;

            navButtons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            pageSections.forEach(sec => sec.classList.remove("active"));
            const targetEl = document.getElementById(targetSection);
            if (targetEl) targetEl.classList.add("active");

            if (sidebar) sidebar.classList.remove("mobile-open");
        });
    });

    document.querySelectorAll("[data-go]").forEach(btn => {
        btn.addEventListener("click", function () {
            const targetSection = btn.dataset.go;
            navButtons.forEach(b => {
                if (b.dataset.section === targetSection) b.click();
            });
        });
    });

    if (menuToggleBtn) {
        menuToggleBtn.addEventListener("click", () => sidebar.classList.add("mobile-open"));
    }

    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener("click", () => sidebar.classList.remove("mobile-open"));
    }

    /* =========================================================
       4. CARGA DE ÓRDENES Y FILTROS
       ========================================================= */
    function loadOrders() {
        const estado = filterStatusSelect ? filterStatusSelect.value : 'Todos';
        const q = searchOrdersInput ? searchOrdersInput.value.trim().toLowerCase() : '';

        fetch(`api/ordenes.php?estado=${encodeURIComponent(estado)}&q=${encodeURIComponent(q)}`)
        .then(res => res.json())
        .then(resData => {
            if (resData.success && Array.isArray(resData.data)) {
                renderOrdersTable(resData.data);
                updateStats(resData.data);
            } else {
                renderOrdersTable(filterLocalOrders(estado, q));
                updateStats(localOrders);
            }
        })
        .catch(() => {
            renderOrdersTable(filterLocalOrders(estado, q));
            updateStats(localOrders);
        });
    }

    function filterLocalOrders(estado, q) {
        return localOrders.filter(o => {
            const matchEstado = (estado === 'Todos' || o.estado === estado);
            const matchQ = !q || (
                o.codigo_orden.toLowerCase().includes(q) ||
                o.cliente_nombre.toLowerCase().includes(q) ||
                o.equipo_info.toLowerCase().includes(q)
            );
            return matchEstado && matchQ;
        });
    }

    function renderOrdersTable(orders) {
        if (!ordersTableBody) return;
        ordersTableBody.innerHTML = "";

        if (orders.length === 0) {
            ordersTableBody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 20px; color:#64748b;">No se encontraron órdenes de servicio que coincidan con la búsqueda.</td></tr>`;
            return;
        }

        orders.forEach(o => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td><strong>${escapeHtml(o.codigo_orden)}</strong></td>
                <td>${escapeHtml(o.cliente_nombre)}</td>
                <td>${escapeHtml(o.telefono || 'N/A')}</td>
                <td>${escapeHtml(o.equipo_info)}</td>
                <td>${escapeHtml(o.diagnostico_inicial)}</td>
                <td>${getBadgeHTML(o.estado)}</td>
                <td>${escapeHtml(o.tecnico_nombre || 'Sin asignar')}</td>
                <td>
                    <button class="btn btn-secondary btn-sm-action" onclick="openCambiarEstadoModal(${o.id_orden}, '${escapeHtml(o.codigo_orden)}', '${escapeHtml(o.estado)}')">
                        Cambiar Estado
                    </button>
                </td>
            `;
            ordersTableBody.appendChild(tr);
        });

        if (dashboardOrdersBody) {
            dashboardOrdersBody.innerHTML = "";
            orders.slice(0, 5).forEach(o => {
                const dTr = document.createElement("tr");
                dTr.innerHTML = `
                    <td><strong>${escapeHtml(o.codigo_orden)}</strong></td>
                    <td>${escapeHtml(o.cliente_nombre)}</td>
                    <td>${escapeHtml(o.equipo_info)}</td>
                    <td>${getBadgeHTML(o.estado)}</td>
                    <td>DOP $${parseFloat(o.costo_estimado || 0).toFixed(2)}</td>
                    <td><button class="btn btn-secondary" onclick="openCambiarEstadoModal(${o.id_orden}, '${escapeHtml(o.codigo_orden)}', '${escapeHtml(o.estado)}')">Ver</button></td>
                `;
                dashboardOrdersBody.appendChild(dTr);
            });
        }
    }

    function updateStats(orders) {
        const activas = orders.filter(o => o.estado !== 'Entregado' && o.estado !== 'Cancelado').length;
        const proceso = orders.filter(o => o.estado === 'En Proceso').length;
        const completadas = orders.filter(o => o.estado === 'Completado').length;

        const elAct = document.getElementById("stat-count-activas");
        const elProc = document.getElementById("stat-count-proceso");
        const elComp = document.getElementById("stat-count-completadas");

        if (elAct) elAct.textContent = activas;
        if (elProc) elProc.textContent = proceso;
        if (elComp) elComp.textContent = completadas;
    }

    function getBadgeHTML(estado) {
        switch (estado) {
            case 'Pendiente': return `<span class="badge badge-warning">Pendiente</span>`;
            case 'En Proceso': return `<span class="badge badge-info">En Proceso</span>`;
            case 'Esperando Repuesto': return `<span class="badge badge-purple">Esperando Repuesto</span>`;
            case 'Completado': return `<span class="badge badge-success">Completado</span>`;
            case 'Entregado': return `<span class="badge badge-gray">Entregado</span>`;
            case 'Cancelado': return `<span class="badge badge-danger">Cancelado</span>`;
            default: return `<span class="badge badge-gray">${escapeHtml(estado)}</span>`;
        }
    }

    if (searchOrdersInput) searchOrdersInput.addEventListener("input", loadOrders);
    if (filterStatusSelect) filterStatusSelect.addEventListener("change", loadOrders);

    // Modal Crear Orden
    function openModalOrden() { if (modalNuevaOrden) modalNuevaOrden.classList.add("active"); }
    function closeModalOrdenFunc() { if (modalNuevaOrden) modalNuevaOrden.classList.remove("active"); }

    if (btnNuevaOrden) btnNuevaOrden.addEventListener("click", openModalOrden);
    if (btnNuevaOrdenDash) btnNuevaOrdenDash.addEventListener("click", openModalOrden);
    if (closeModalOrden) closeModalOrden.addEventListener("click", closeModalOrdenFunc);
    if (btnCancelarOrden) btnCancelarOrden.addEventListener("click", closeModalOrdenFunc);

    if (formNuevaOrden) {
        formNuevaOrden.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(formNuevaOrden);
            formData.append("action", "crear");

            fetch("api/ordenes.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("¡Éxito! " + data.message);
                    closeModalOrdenFunc();
                    formNuevaOrden.reset();
                    loadOrders();
                } else {
                    alert("Error: " + (data.message || "No se pudo registrar la orden"));
                }
            })
            .catch(() => {
                const newCode = "ORD-2026-00" + (localOrders.length + 1);
                localOrders.unshift({
                    id_orden: localOrders.length + 1,
                    codigo_orden: newCode,
                    cliente_nombre: formData.get("cliente_nombre"),
                    telefono: formData.get("cliente_telefono"),
                    equipo_info: formData.get("tipo_dispositivo") + " " + formData.get("marca") + " " + formData.get("modelo"),
                    diagnostico_inicial: formData.get("diagnostico_inicial"),
                    estado: 'Pendiente',
                    costo_estimado: parseFloat(formData.get("costo_estimado") || 0),
                    tecnico_nombre: 'Carlos Ruiz'
                });
                alert("¡Orden registrada exitosamente! (Transacción simulada en memoria: " + newCode + ")");
                closeModalOrdenFunc();
                formNuevaOrden.reset();
                loadOrders();
            });
        });
    }

    // Modal Cambiar Estado
    window.openCambiarEstadoModal = function (idOrden, codigo, estadoActual) {
        document.getElementById("modal-estado-id-orden").value = idOrden;
        document.getElementById("modal-estado-codigo").textContent = codigo;
        document.getElementById("nuevo_estado").value = estadoActual;
        if (modalCambiarEstado) modalCambiarEstado.classList.add("active");
    };

    function closeModalEstadoFunc() { if (modalCambiarEstado) modalCambiarEstado.classList.remove("active"); }

    if (closeModalEstado) closeModalEstado.addEventListener("click", closeModalEstadoFunc);
    if (btnCancelarEstado) btnCancelarEstado.addEventListener("click", closeModalEstadoFunc);

    if (formCambiarEstado) {
        formCambiarEstado.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(formCambiarEstado);
            formData.append("action", "cambiar_estado");

            fetch("api/ordenes.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeModalEstadoFunc();
                    loadOrders();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(() => {
                const idO = parseInt(document.getElementById("modal-estado-id-orden").value);
                const nEst = document.getElementById("nuevo_estado").value;
                const match = localOrders.find(o => o.id_orden === idO);
                if (match) match.estado = nEst;
                alert("Estado actualizado correctamente a " + nEst + " (Modo Simulación)");
                closeModalEstadoFunc();
                loadOrders();
            });
        });
    }

    function escapeHtml(text) {
        if (!text) return "";
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    loadOrders();
    loadUsers();
});
