/* =========================================================
   TECNOFIX - APP.JS
   JavaScript del prototipo V2.

   IMPORTANTE:
   Esta versión todavía no utiliza PHP, MySQL ni una API.
   JavaScript solamente simula la navegación del sistema.
   ========================================================= */


/* =========================================================
   1. REFERENCIAS A LOS ELEMENTOS PRINCIPALES
   ========================================================= */

// Pantalla de inicio de sesión.
const loginScreen = document.getElementById("login-screen");

// Pantalla principal del sistema.
const appScreen = document.getElementById("app-screen");

// Formulario de inicio de sesión.
const loginForm = document.getElementById("login-form");

// Botón para cerrar sesión.
const logoutButton = document.getElementById("logout-btn");

// Botones del menú lateral.
const navigationButtons = document.querySelectorAll(".nav-item");

// Todas las secciones internas del sistema.
const pageSections = document.querySelectorAll(".page-section");


/* =========================================================
   2. INICIO DE SESIÓN SIMULADO
   ========================================================= */

/*
 * En esta V1 no se comprueba ningún usuario contra una base
 * de datos. El formulario solamente permite entrar al prototipo.
 *
 * En una futura versión:
 *   Usuario → PHP → Base de datos → Validación → Dashboard
 */
function showMessage(text) { alert(text); }

loginForm.addEventListener("submit", function (event) {

    // Evita que el navegador recargue la página.
    event.preventDefault();

    const user = loginForm.querySelector('input[type="text"], input[type="email"]').value.trim();
    const password = loginForm.querySelector('input[type="password"]').value;
    if (user !== "admin" || password !== "1234") { showMessage("Credenciales inválidas. Usa admin y 1234."); return; }
    // Ocultamos la pantalla de login.
    loginScreen.classList.remove("active");

    // Mostramos la aplicación.
    appScreen.classList.add("active");
});


/* =========================================================
   3. NAVEGACIÓN DEL MENÚ
   ========================================================= */

/*
 * Cada botón del menú posee un atributo data-section.
 *
 * Ejemplo:
 * data-section="clientes"
 *
 * Esto permite saber qué sección debe mostrarse.
 */
navigationButtons.forEach(function (button) {

    button.addEventListener("click", function () {

        // Obtenemos el nombre de la sección indicada por el botón.
        const targetSection = button.dataset.section;


        // Quitamos "active" de todos los botones.
        navigationButtons.forEach(function (item) {
            item.classList.remove("active");
        });


        // Marcamos como activo el botón seleccionado.
        button.classList.add("active");


        // Ocultamos todas las pantallas internas.
        pageSections.forEach(function (section) {
            section.classList.remove("active");
        });


        // Buscamos la sección seleccionada por su ID.
        const selectedSection = document.getElementById(targetSection);


        // Si existe, la mostramos.
        if (selectedSection) {
            selectedSection.classList.add("active");
        }
    });
});


/* =========================================================
   4. BOTONES QUE LLEVAN A OTRA SECCIÓN
   ========================================================= */

/*
 * Algunos botones dentro del contenido también pueden navegar.
 *
 * Ejemplo:
 * data-go="ordenes"
 *
 * Se utiliza en el botón "Ver todas" del Dashboard.
 */
document.querySelectorAll("[data-go]").forEach(function (button) {

    button.addEventListener("click", function () {

        // Obtenemos la sección destino.
        const targetSection = button.dataset.go;


        // Actualizamos el botón activo del menú.
        navigationButtons.forEach(function (item) {

            if (item.dataset.section === targetSection) {
                item.classList.add("active");
            } else {
                item.classList.remove("active");
            }
        });


        // Mostramos solamente la sección seleccionada.
        pageSections.forEach(function (section) {

            if (section.id === targetSection) {
                section.classList.add("active");
            } else {
                section.classList.remove("active");
            }
        });
    });
});


/* =========================================================
   5. CERRAR SESIÓN
   ========================================================= */

/*
 * En la versión funcional futura, aquí también se cerrará
 * la sesión del usuario en el servidor.
 *
 * Por ahora solamente regresamos a la pantalla de login.
 */
logoutButton.addEventListener("click", function () {

    // Ocultamos la aplicación.
    appScreen.classList.remove("active");

    // Mostramos nuevamente el login.
    loginScreen.classList.add("active");

    // Limpiamos usuario y contraseña.
    loginForm.reset();
});

document.querySelectorAll(".search-input").forEach(input => input.addEventListener("input", () => {
    const table = input.closest(".page-section").querySelector("table");
    if (table) table.querySelectorAll("tbody tr").forEach(row => row.style.display = row.textContent.toLowerCase().includes(input.value.toLowerCase()) ? "" : "none");
}));

document.querySelectorAll(".btn-primary, .btn-secondary, .link-btn").forEach(button => {
    if (button.type !== "submit" && !button.dataset.go) button.addEventListener("click", () => showMessage("Acción registrada en el prototipo V2."));
});
