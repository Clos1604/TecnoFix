/* =========================================================
   TECNOFIX - APP.JS V3
   Lógica del cliente, interacción AJAX y dinamismo
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
    const searchOrdersInput = document.getElementById("search-orders");
    const filterStatusSelect = document.getElementById("filter-status");

    // Modales
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

    /* =========================================================
       1. AUTENTICACIÓN (LOGIN & LOGOUT) - RF-01
       ========================================================= */
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value;

            loginAlert.style.display = "none";

            if (!username || !password) {
                showLoginAlert("Por favor ingrese su usuario y contraseña.");
                return;
            }

            // Intento de envío a backend PHP mediante Fetch API
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
                    // Login exitoso
                    if (data.user) {
                        document.getElementById("logged-user-name").textContent = data.user.nombre;
                        document.getElementById("logged-user-rol").textContent = data.user.rol;
                    }
                    loginScreen.classList.remove("active");
                    appScreen.classList.add("active");
                    loadOrders();
                } else {
                    showLoginAlert(data.message || "Credenciales inválidas. Compruebe usuario/contraseña.");
                }
            })
            .catch(err => {
                // Fallback simulación cliente si no hay PHP servido por HTTP directo
                console.log("Servidor PHP API no detectado HTTP directo, ejecutando validación cliente demo:", err);
                if ((username === "admin" || username === "admin@tecnofix.com") && (password === "1234" || password === "admin123")) {
                    loginScreen.classList.remove("active");
                    appScreen.classList.add("active");
                    loadOrders();
                } else {
                    showLoginAlert("Credenciales inválidas. Para la demo use: admin@tecnofix.com / 1234");
                }
            });
        });
    }

    function showLoginAlert(msg) {
        loginAlert.textContent = msg;
        loginAlert.style.display = "block";
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
       2. NAVEGACIÓN Y MENÚ RESPONSIVO
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
        menuToggleBtn.addEventListener("click", () => {
            sidebar.classList.add("mobile-open");
        });
    }

    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener("click", () => {
            sidebar.classList.remove("mobile-open");
        });
    }

    /* =========================================================
       3. CARGA DE ÓRDENES Y FILTROS (AJAX / MEMORIA)
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
        .catch(err => {
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

        // Dashboard preview table
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

    // Filtros dinámicos al escribir o seleccionar
    if (searchOrdersInput) searchOrdersInput.addEventListener("input", loadOrders);
    if (filterStatusSelect) filterStatusSelect.addEventListener("change", loadOrders);

    /* =========================================================
       4. MODAL CREAR NUEVA ÓRDEN DE SERVICIO
       ========================================================= */
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
            .catch(err => {
                // Fallback cliente si no hay backend activo
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

    /* =========================================================
       5. MODAL CAMBIAR ESTADO
       ========================================================= */
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

    // Inicializar órdenes al cargar
    loadOrders();
});
