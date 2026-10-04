# 🔧 TecnoFix - Sistema de Gestión de Servicios Técnicos

![TecnoFix Header](Aplicacion/recursos/logo.png)

Sistema Web integral para la recepción, diagnóstico, asignación técnica y seguimiento de órdenes de servicio en taller de reparación de laptops, smartphones y equipos informáticos.

---

## 📌 Información de la Entrega

* **Asignatura:** Implementación y Validación de Software
* **Proyecto:** TecnoFix Integrador - Segundo Parcial
* **Estudiante / Autor:** Claudio Ramírez
* **Matrícula:** 202110020069
* **Tecnologías:** PHP 8.2 (MVC), MySQL 8.0 / MariaDB (XAMPP), HTML5, CSS3 Responsive, JavaScript (ES6 / Async Fetch API).

---

## 🛠️ Arquitectura e Implementación

El proyecto sigue una arquitectura en 3 capas basada en el patrón **Modelo-Vista-Controlador (MVC)**:

* **Presentación (`views/` & `public/`):** Interfaz fluida con diseño adaptativo móvil, modales interactivos y alertas dinámicas de validación.
* **Controladores (`controllers/` & `api/`):** Procesamiento de reglas de negocio, sanitización de datos y respuestas en JSON para interacción asíncrona AJAX.
* **Acceso a Datos (`models/` & `config/`):** Capa PDO con preparación estricta de sentencias (`Prepared Statements`), contraseñas hasheadas (`BCRYPT`) y **Transacciones SQL atómicas (`beginTransaction`, `commit`, `rollBack`)** para la creación de órdenes e inserción en tablas relacionales (`clientes`, `equipos`, `ordenes_servicio`, `historial_ordenes`).

---

## 🗄️ Estructura del Repositorio

```text
TecnoFix/
├── Aplicacion/
│   ├── prototipo V1/          # Primera versión estática presentada
│   ├── prototipo V2/          # Segunda versión con mejoras visuales y de usabilidad
│   ├── prototipo V3/          # TERCER PROTOTIPO FUNCIONAL IMPLEMENTADO (PHP + MYSQL)
│   │   ├── api/               # Endpoints JSON (login.php, logout.php, ordenes.php)
│   │   ├── config/            # Conexión PDO (conexion.php)
│   │   ├── controllers/       # AuthController.php, OrdenesController.php
│   │   ├── database/          # Script SQL oficial (tecnofix_db.sql)
│   │   ├── models/            # Usuario.php, Orden.php, Cliente.php
│   │   ├── public/            # Hojas de estilo CSS y JS app.js
│   │   └── index.php          # Vista principal enrutada
│   └── recursos/              # Assets visuales (logo.png)
├── Documentos/                # Diagramas .drawio, .vsdx, .pdf y documentación
├── README.md                  # Este manual técnico
└── .gitignore                 # Exclusiones de control de versiones
```

---

## 🚀 Guía de Instalación y Puesta en Marcha (Entorno XAMPP)

### Requisitos Previos
* XAMPP con **PHP >= 8.0** y **MySQL / MariaDB**.
* Navegador Web moderno (Chrome, Edge, Firefox).

### Pasos de Instalación:

1. **Clonar o Copiar el Repositorio:**
   Copiar la carpeta `TecnoFix` dentro de la ruta `C:\xampp\htdocs\`:
   ```bash
   C:\xampp\htdocs\TecnoFix\
   ```

2. **Iniciar Servicios en XAMPP Control Panel:**
   * Iniciar **Apache**
   * Iniciar **MySQL**

3. **Importar la Base de Datos en MySQL:**
   * Abrir [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
   * Crear o seleccionar la base de datos `tecnofix_db`.
   * Importar el archivo SQL ubicado en:
     `Aplicacion/prototipo V3/database/tecnofix_db.sql`

4. **Acceder a la Aplicación en el Navegador:**
   Navegar a la siguiente URL local:
   ```text
   http://localhost/TecnoFix/Aplicacion/prototipo V3/
   ```

---

## 🔑 Credenciales de Acceso para Pruebas (Seed Data)

| Rol | Usuario / Email | Contraseña | Permisos |
|---|---|---|---|
| **Administrador** | `admin@tecnofix.com` (o `admin`) | `1234` / `admin123` | Acceso Total + Gestión |
| **Técnico** | `carlos.tecnico@tecnofix.com` | `tecnico123` | Diagnósticos y Cambio de Estado |
| **Recepción** | `maria.recepcion@tecnofix.com` | `recepcion123` | Registro de Órdenes y Clientes |

---

## 🌿 Convención de Commits y Estrategia de Ramas Git

El repositorio aplica las siguientes convenciones de mensajería:

* `feat:` Nuevas funcionalidades desarrolladas en código.
* `fix:` Corrección de errores, estilo o detalles de usabilidad.
* `docs:` Actualizaciones de documentación, SQL o markdown.
* `test:` Matriz y ejecución de casos de prueba.

### Historial de Ramas y Integraciones (Pull Requests):
* `main`: Rama principal de producción integrada.
* `feature/usabilidad-v2`: Integración de correcciones del primer parcial (PR #1).
* `feature/implementacion-php-mysql`: Integración del backend PHP PDO y base de datos relacional MySQL (PR #2).

---

## 📄 Licencia y Derechos

Desarrollado para la materia de **Implementación y Validación de Software** - Universidad UCN 2026.
