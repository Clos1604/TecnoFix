# 🔧 SEGUNDO PARCIAL - SEGUIMIENTO Y AVANCE DE IMPLEMENTACIÓN
## Proyecto Integrador: Sistema de Gestión de Servicios Técnicos "TecnoFix"

> **Asignatura:** Implementación y Validación de Software  
> **Institución:** Universidad UCN  
> **Estudiante:** Claudio Ramírez (Matrícula: 202110020069)  
> **Fecha de Entrega:** Octubre 2026  
> **Versión del Documento:** 2.0 (Línea Base Actualizada e Implementada)  

---

## 1. Portada y Ficha Técnica del Proyecto

* **Nombre del Sistema:** TecnoFix — Software de Gestión Operativa para Taller de Servicios Técnicos.
* **Lenguaje & Framework:** PHP 8.2 (Arquitectura en capas MVC), JavaScript (ES6+ AJAX Fetch API), HTML5, CSS3 Responsive.
* **Motor de Base de Datos:** MySQL 8.0 / MariaDB (XAMPP Server).
* **Control de Versiones:** Git & GitHub (Convención Conventional Commits, Estrategia GitFlow/Ramas de Características).
* **Entorno de Ejecución Objetivo:** Servidor Web Apache + MySQL (XAMPP local / Hosting Web).

---

## 2. Correcciones y Actualización de la Línea Base del Proyecto

Con el propósito de garantizar la evolución real del proyecto respecto al primer parcial, se procesaron de forma rigurosa las 6 observaciones emitidas en la retroalimentación docente.

### 2.1 Matriz Formal de Correcciones Aplicadas

| # | Observación Recibida | Elemento Afectado | Modificación Realizada | Estado Actual | Evidencia Verificable |
|---|---|---|---|---|---|
| **1** | Falta evidencia formal del repositorio Git y participación. | Repositorio / Versionamiento | Inicialización de repositorio Git, configuración de ramas (`main`, `feature/...`), mensajes de commit con convenciones y 2 Pull Requests integrados. | **CORREGIDO** | Repositorio activo, `git log` con 5 commits y `README.md` técnico. |
| **2** | Incorporar evidencia visual ANTES / DESPUÉS para cada mejora. | Prototipo / UI | Elaboración de matriz visual comparativa mostrando la evolución del diseño de V1 a V2/V3. | **CORREGIDO** | Capturas comparativas de interfaz en sección 2.2. |
| **3** | Corregir texto interno que aún indica "Prototipo V1" en V2. | Interfaz Login | Modificación del selector `.prototype-label` en `index.html` L63 a "Prototipo V2 / V3". | **CORREGIDO** | Etiqueta actualizada en plantilla HTML. |
| **4** | Ampliar criterios de aceptación a más requisitos. | Requisitos y Criterios | Redacción de criterios en sintaxis **Given-When-Then** para RF-01, RF-02 y RF-03. | **CORREGIDO** | Matriz de criterios ampliada en sección 3. |
| **5** | Profundizar evaluación de usabilidad con métricas observables. | Usabilidad / Pruebas | Medición de tiempos de tarea, tasa de error y pruebas de usabilidad sobre 5 hallazgos clave. | **CORREGIDO** | Informe de evaluación de usabilidad en sección 2.3. |
| **6** | Mantener estructura documental uniforme en IDs. | Trazabilidad Documental | Estandarización de IDs de Requisitos (`RF-01`, `RF-02`), Casos de Prueba (`PR-01`) y Tablas SQL. | **CORREGIDO** | Homologación en requisitos, DER, código PHP y pruebas. |

### 2.2 Matriz Visual ANTES / DESPUÉS de las Mejoras del Prototipo

* **ANTES (Prototipo V1):**
  - Texto inferior indicaba erróneamente "Prototipo V1".
  - Sin validación visual de errores de credenciales (usaba alerta JS emergente por defecto).
  - Sin diseño adaptativo para pantallas móviles.
  - Navegación estática sin menú desplegable en pantallas pequeñas.

* **DESPUÉS (Prototipo V2 / V3 Implementado):**
  - Texto inferior actualizado a "Prototipo V3 · Sistema Implementado TecnoFix (PHP & MySQL)".
  - Banner de error dinámico `#login-alert` con estilo visual en rojo y animación suave.
  - Menú lateral retráctil con botón de hamburguesa ☰ para dispositivos móviles.
  - Incorporación de barra de búsqueda en tiempo real `🔍` y filtro desplegable por estado.

### 2.3 Evaluación de Usabilidad con Métricas Observables

Se realizó una sesión de pruebas con 3 usuarios clave (1 Administrador y 2 Recepcionistas) evaluando los 5 problemas de usabilidad previamente detectados:

| Problema Identificado | Tarea Evaluada | Tiempo Promedio V1 | Tiempo Promedio V3 | Tasa de Éxito V3 | Observación |
|---|---|---|---|---|---|
| **1. Validación Credenciales** | Iniciar sesión con datos incorrectos | 12 seg (alert emergente) | 3 seg (banner en pantalla) | 100% | Retroalimentación visual inmediata sin bloquear el flujo. |
| **2. Botones sin Respuesta** | Hacer clic en "Ver todas las órdenes" | Fail (no navegaba) | 1.2 seg (redirección) | 100% | Se activó el listener `data-go="ordenes"`. |
| **3. Acciones de Órdenes** | Cambiar estado de orden en lista | 25 seg (modificación manual) | 5 seg (modal modal-cambiar-estado) | 100% | Modal simplificado con select de estado y observaciones. |
| **4. Adaptación Móvil** | Navegar sistema desde smartphone | Fail (desbordamiento) | 4 seg (menú hamburguesa) | 100% | Layout totalmente fluido con CSS Flexbox / Media Queries. |
| **5. Búsqueda y Filtros** | Buscar orden por nombre de cliente | 40 seg (búsqueda visual manual) | 2 seg (filtro instantáneo) | 100% | Filtrado en tiempo real por evento `input` en JS. |

---

## 3. Alcance y Requisitos Actualizados con Criterios de Aceptación

### 3.1 Cadena de Alineación del Proyecto
$$\text{Problema} \longrightarrow \text{Usuarios} \longrightarrow \text{Requisitos} \longrightarrow \text{Prototipo} \longrightarrow \text{Arquitectura} \longrightarrow \text{Implementación}$$

### 3.2 Matriz de Requisitos Funcionales y Criterios de Aceptación (Given-When-Then)

| ID Requisito | Requisito Funcional | Criterio de Aceptación (Given-When-Then) | Estado Implementación |
|---|---|---|---|
| **RF-01** | **Autenticación y Control de Acceso** | **Dado** que el usuario está en la pantalla de inicio de sesión,<br>**Cuando** ingresa un email registrado y su contraseña correcta,<br>**Entonces** el sistema valida el hash BCRYPT, inicia sesión en PHP y lo redirige al Dashboard según su rol. | **Implementado y Funcional** (PHP PDO + Sesiones) |
| **RF-01.1** | **Rechazo de Credenciales Inválidas** | **Dado** que el usuario ingresa una contraseña incorrecta o campos vacíos,<br>**Cuando** presiona "Iniciar Sesión",<br>**Entonces** el sistema muestra un mensaje de error claro en pantalla sin recargar la página. | **Implementado y Funcional** (AJAX + JS) |
| **RF-02** | **Registro de Orden de Servicio** | **Dado** que el usuario abre el modal "Nueva Orden",<br>**Cuando** completa los datos del cliente, equipo y diagnóstico y confirma el formulario,<br>**Entonces** el sistema ejecuta una **Transacción SQL** que inserta simultáneamente Cliente, Equipo, Orden e Historial. | **Implementado y Funcional** (Transacciones SQL) |
| **RF-02.1** | **Filtrado y Búsqueda de Órdenes** | **Dado** que el usuario está en el módulo de órdenes,<br>**Cuando** escribe en la barra de búsqueda o selecciona un estado en el filtro,<br>**Entonces** la tabla actualiza dinámicamente sus registros sin recargar la vista. | **Implementado y Funcional** (Eventos JS + Backend PDO) |
| **RF-03** | **Actualización de Estado de Orden** | **Dado** que un técnico o recepcionista selecciona "Cambiar Estado",<br>**Cuando** elige un nuevo estado y redacta observaciones,<br>**Entonces** el sistema actualiza la orden y guarda una auditoría en la tabla `historial_ordenes`. | **Implementado y Funcional** (PDO Update + Historial) |

---

## 4. Funcionalidades Implementadas (Código & Evidencia Ejecutable)

Se implementaron **dos funcionalidades principales totalmente operativas** conectadas a código PHP backend, base de datos MySQL y la interfaz de usuario HTML/CSS/JS:

### 4.1 Funcionalidad 1: Autenticación y Gestión de Sesiones (RF-01)
* **Controlador (`AuthController.php`):** Valida credenciales, sanitiza la entrada del usuario y mantiene variables de sesión seguras (`$_SESSION['usuario_logged']`).
* **Modelo (`Usuario.php`):** Realiza la consulta PDO `SELECT * FROM usuarios WHERE email = :email` y verifica el hash cifrado mediante `password_verify()`.
* **Interfaz:** Pantalla de login con alerta interactiva `#login-alert` que responde de forma inmediata ante errores sin parpadear.

```php
// Extracto de Código: Verificación de Contraseña Segura en Usuario.php
public static function login($email, $password) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE (email = :email OR nombre = :nombre) AND estado = 'Activo' LIMIT 1");
    $stmt->execute([':email' => $email, ':nombre' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']); // Seguridad: No almacenar hash en sesión
        return $user;
    }
    return false;
}
```

### 4.2 Funcionalidad 2: Registro y Gestión de Órdenes de Servicio (RF-02)
* **Controlador (`OrdenesController.php`):** Recibe solicitudes `POST`, procesa las reglas de validación de campos obligatorios y gestiona el flujo CRUD.
* **Modelo (`Orden.php`):** Implementa la creación atómica mediante **Transacciones SQL** (`beginTransaction`, `commit`, `rollBack`) para evitar registros huérfanos o parcialmente guardados.
* **Interfaz:** Modal responsivo `#modal-nueva-orden`, tabla dinámica con badges de colores según estado (`Pendiente`, `En Proceso`, `Completado`) y acciones rápidas por fila.

---

## 5. Arquitectura Actualizada del Sistema (Modelo MVC)

El sistema evoluciona formalmente hacia un esquema **MVC en 3 Capas Desacopladas**:

* **Capa 1: Presentación (Vista & UI):** `index.php`, `public/css/style.css`, `public/js/app.js`
* **Capa 2: Controladores y Lógica de Negocio (Backend PHP):** `AuthController.php`, `OrdenesController.php`, `api/login.php`, `api/ordenes.php`
* **Capa 3: Acceso a Datos y Persistencia (Modelos & MySQL):** `config/conexion.php`, `Usuario.php`, `Orden.php`, Base de datos MySQL `tecnofix_db`.

---

## 6. Implementación e Integración de la Base de Datos MySQL

### 6.1 Modelo de Datos Relacional (DER)
La base de datos `tecnofix_db` está estructurada para garantizar la **Integridad Referencial**, eliminando redundancias mediante claves primarias (`PK`), foráneas (`FK`), restricciones `UNIQUE` y tipos de datos estrictos.

* **Tablas:** `usuarios`, `clientes`, `equipos`, `ordenes_servicio`, `historial_ordenes`, `migracion_log`.

### 6.2 Evidencia de Transacciones SQL en Código PHP (Operación Atómica)
Para evitar datos inconsistentes al registrar una orden (donde deben insertarse cliente, equipo, orden e historial en conjunto), se utilizó el mecanismo de **Transacciones PDO**:

```php
// Implementación en Orden.php
public static function crearConTransaccion($data, $idUsuarioCreador) {
    $db = getDBConnection();
    try {
        $db->beginTransaction(); // INICIO DE TRANSACCIÓN ATÓMICA

        // 1. Insertar Cliente
        $stmtC = $db->prepare("INSERT INTO clientes (nombre_completo, telefono, email) VALUES (?, ?, ?)");
        $stmtC->execute([$data['cliente_nombre'], $data['cliente_telefono'], $data['cliente_email']]);
        $idCliente = $db->lastInsertId();

        // 2. Insertar Equipo
        $stmtE = $db->prepare("INSERT INTO equipos (id_cliente, tipo_dispositivo, marca, modelo) VALUES (?, ?, ?, ?)");
        $stmtE->execute([$idCliente, $data['tipo_dispositivo'], $data['marca'], $data['modelo']]);
        $idEquipo = $db->lastInsertId();

        // 3. Insertar Orden de Servicio
        $codigoOrden = 'ORD-2026-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmtO = $db->prepare("INSERT INTO ordenes_servicio (codigo_orden, id_cliente, id_equipo, diagnostico_inicial, costo_estimado) VALUES (?, ?, ?, ?, ?)");
        $stmtO->execute([$codigoOrden, $idCliente, $idEquipo, $data['diagnostico_inicial'], $data['costo_estimado']]);
        $idOrden = $db->lastInsertId();

        // 4. Registrar Historial Auditoría
        $stmtH = $db->prepare("INSERT INTO historial_ordenes (id_orden, estado_anterior, estado_nuevo, observaciones, id_usuario) VALUES (?, 'Nuevo', 'Pendiente', 'Creación de orden.', ?)");
        $stmtH->execute([$idOrden, $idUsuarioCreador]);

        $db->commit(); // CONFIRMAR CAMBIOS SI TODO SALIÓ BIEN
        return ['exito' => true, 'codigo_orden' => $codigoOrden];
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack(); // REVERTIR CAMBIOS SI OCURRIÓ UN ERROR
        return ['exito' => false, 'error' => $e->getMessage()];
    }
}
```

---

## 7. Matriz de Pruebas Básicas de Implementación

Conforme a la especificación, se ejecutaron y documentaron **3 pruebas exitosas** y **1 prueba fallida/negativa** (validación de errores):

| ID | Requisito | Acción Realizada | Resultado Esperado | Resultado Obtenido | Estado | Evidencia |
|---|---|---|---|---|---|---|
| **PR-01** | **RF-01** | Iniciar sesión con usuario `admin@tecnofix.com` y contraseña `1234`. | Acceso concedido, inicio de sesión PHP y despliegue del Dashboard. | Acceso permitido. Redirección correcta al panel. | **Aprobada** | Captura de acceso concedido y variable de sesión iniciada. |
| **PR-02** | **RF-01** | Iniciar sesión con contraseña incorrecta (`admin` / `erronea99`). | Denegar acceso y mostrar mensaje de error en el banner `#login-alert`. | Se muestra alerta roja: *"Credenciales inválidas. Compruebe su usuario o contraseña."* | **Aprobada (Negativa)** | Captura de mensaje de rechazo visual en el formulario de login. |
| **PR-03** | **RF-02** | Crear nueva orden de servicio completando todos los campos requeridos en el modal. | Inserción exitosa en MySQL mediante transacción y asignación de código `ORD-2026-XXXX`. | Transacción SQL ejecutada. Orden registrada e insertada en la tabla HTML. | **Aprobada** | Registro generado exitosamente en base de datos. |
| **PR-04** | **RF-02.1** | Escribir "iPhone" en la barra de búsqueda del módulo de órdenes. | Filtrar dinámicamente la tabla mostrando únicamente las órdenes asociadas a iPhone. | La tabla ocultó las órdenes no coincidentes y mostró la orden `ORD-2026-002`. | **Aprobada** | Filtrado en tiempo real sin recarga de página. |

---

## 8. Repositorio Git y Estrategia de Trabajo Colaborativo

### 8.1 Ficha del Repositorio
* **Plataforma:** GitHub / Git Local.
* **Rama Principal:** `main` (código estable y validado).
* **Ramas de Trabajo:** `feature/usabilidad-v2`, `feature/implementacion-php-mysql`.

### 8.2 Convención de Mensajes de Commit Aplicada
Se utilizó una norma estándar basada en **Conventional Commits**:
* `feat: ...` para desarrollo de nuevos módulos o backend.
* `fix: ...` para corrección de observaciones o usabilidad.
* `docs: ...` para documentación, diagramas y README.
* `test: ...` para casos de prueba.

### 8.3 Historial Verificable de Commits y Integraciones (Pull Requests)

```text
* 9e23be7 docs: agregar README.md completo con instrucciones de instalacion en XAMPP
*   0c62eeb Merge pull request #2 from feature/implementacion-php-mysql
|\  
| * 9501e36 feat: implementar arquitectura MVC, conexion PDO MySQL y modulos RF-01 y RF-02
|/  
*   7c313f0 Merge pull request #1 from feature/usabilidad-v2
|\  
| * cebde12 fix: corregir etiqueta de version V1 a V2 en pantalla de login y app.js
|/  
* ac0dee1 docs: linea base inicial del proyecto TecnoFix (Parcial 1)
```

---

## 9. Procedimiento de Migración de Datos (Protocolo ETL)

Aun cuando el sistema inicia con información nueva, se diseñó un procedimiento formal de migración ETL para trasvasar información desde planillas de Excel o sistemas legacy hacia la nueva base de datos MySQL de TecnoFix sin pérdida de integridad.

### 9.1 Flujo General del Proceso ETL
$$\text{Origen (Excel/CSV)} \longrightarrow \text{Respaldo copia DB} \longrightarrow \text{Extracción} \longrightarrow \text{Transformación (Limpieza)} \longrightarrow \text{Carga en Staging} \longrightarrow \text{Validación Conteos} \longrightarrow \text{Aceptación o Rollback}$$

### 9.2 Matriz de Componentes del Proceso de Migración

| Componente | Especificación Técnica |
|---|---|
| **Fuente de Origen** | Archivo plano `clientes_legacy.csv` proveniente del sistema anterior de registro manual en hojas de cálculo. |
| **Destino** | Tablas relacionales `clientes`, `equipos` y `ordenes_servicio` en MySQL. |
| **Respaldo Previas** | Ejecución de `mysqldump -u root tecnofix_db > respaldo_pre_migracion.sql` antes de la carga. |
| **Transformación** | 1. Estandarización de formato de teléfono a `809-XXX-XXXX`.<br>2. Formateo de Cédula/RNC eliminando guiones duplicados.<br>3. Mapeo de estados legacy ("En Espera" $\rightarrow$ `Pendiente`, "Terminado" $\rightarrow$ `Completado`). |
| **Entorno Seguro** | Instancia de base de datos de pruebas `tecnofix_staging_db`. |
| **Validación** | Verificación de conteo de registros antes vs. después (`SELECT COUNT(*)`), comprobación de claves foráneas no nulas y detección de duplicados por Cédula. |
| **Rollback** | En caso de error o inconsistencia mayor al 1%, restauración automática mediante script:<br>`DROP DATABASE tecnofix_db; CREATE DATABASE tecnofix_db; SOURCE respaldo_pre_migracion.sql;` |

---

## 10. Plan Básico de Capacitación para Usuarios Finales

### 10.1 Perfil del Usuario Objetivo
* **Rol:** Personal de Recepción y Servicio al Cliente / Técnicos de Taller.
* **Objetivo:** Capacitar al personal en el uso del sistema para registrar órdenes de servicio, realizar búsquedas rápidas y cambiar estados de reparación en menos de 3 minutos por cliente.

### 10.2 Estructura por Micro-Módulos (Duración Total: 35 Minutos)

1. **Micro-Módulo 1: Acceso Seguro y Navegación (5 min)**  
   * Demostración de inicio de sesión con credenciales asignadas.  
   * Explicación de la barra lateral, menú responsivo y cierre de sesión.
2. **Micro-Módulo 2: Recepción de Equipo y Registro de Orden (10 min)**  
   * Uso del botón `+ Crear Nueva Orden`.  
   * Captura adecuada del diagnóstico inicial del cliente y asignación de técnico.
3. **Micro-Módulo 3: Búsqueda y Filtrado Dinámico (10 min)**  
   * Localización de equipos mediante la barra de búsqueda `🔍` por cliente o código.  
   * Filtrado por órdenes pendientes o listas para entregar.
4. **Micro-Módulo 4: Actualización de Avances y Cierre de Servicio (10 min)**  
   * Uso del botón `Cambiar Estado`.  
   * Registro de observaciones técnicas para el cliente.

### 10.3 Ejercicio Práctico de Evaluación
* **Consigna para el participante:** *"Registrar un nuevo cliente llamado 'Carlos Mendoza', con equipo 'Tablet Samsung Tab S8', problema 'No carga', asignar costo estimado de DOP $3,500 y posteriormente cambiar el estado a 'En Proceso'."*
* **Criterio de Aprobación:** Completar la actividad sin cometer errores de validación en un tiempo máximo de 4 minutos.

---

## 11. Estrategia de Implementación y Entorno Objetivo

### 11.1 Enfoque de Despliegue Seleccionado: Enfoque Incremental / Piloto
Se selecciona una **Estrategia Incremental Piloto**. El sistema se desplegará inicialmente en la recepción principal de TecnoFix durante 2 semanas operando en paralelo con el registro manual para validar la estabilidad de la base de datos y la velocidad de respuesta, antes de realizar la migración completa y definitiva.

### 11.2 Matriz de Diferenciación de Entornos

| Entorno | Finalidad | Ubicación / Servidor | Configuración |
|---|---|---|---|
| **Desarrollo (Dev)** | Programación de nuevos módulos y ajuste de estilos UI. | Máquina local del desarrollador (VS Code). | PHP 8.2 Built-in Server / XAMPP. |
| **Pruebas (Staging)** | Integración de BD, ejecución de pruebas de software y validación con usuarios. | Servidor local de laboratorio UCN. | Apache + MySQL `tecnofix_db` con datos de prueba. |
| **Producción (Prod)** | Operación real y almacenamiento definitivo del taller TecnoFix. | Hosting Web / Servidor Dedicado Taller. | Apache / Nginx + MySQL con HTTPS (SSL) y backups diarios. |

### 11.3 Ficha Técnica del Target-Host (Infraestructura Objetivo)

```text
====================================================================
FICHA TÉCNICA DE INFRAESTRUCTURA (TARGET-HOST PROPIUESTO)
====================================================================
• Sistema Operativo: Linux Ubuntu Server 22.04 LTS (o Windows Server XAMPP)
• Servidor HTTP: Apache HTTP Server 2.4.58 con mod_rewrite activo.
• Entorno de Ejecución: PHP 8.2+ con extensiones pdo_mysql, mbstring, json.
• Motor de Base de Datos: MySQL 8.0 / MariaDB 10.4.
• Almacenamiento: Disco SSD NVMe con al menos 20 GB de espacio libre.
• Memoria RAM Mínima: 4 GB RAM.
• Seguridad: Certificado SSL TLS 1.3 (HTTPS) para cifrado de sesiones.
====================================================================
```

---

## 12. Conclusiones

1. Se logró transformar con éxito la propuesta inicial de diseño y arquitectura en una **solución funcional implementada en código PHP, HTML5/CSS3/JS y base de datos relacional MySQL**.
2. Todas las correcciones señaladas en la retroalimentación del Primer Parcial fueron **analizadas, aplicadas y documentadas con evidencia verificable**, incluyendo la estructura de repositorio Git con historial de commits y Pull Requests.
3. La implementación de **Transacciones SQL en PHP PDO** garantiza la integridad atómica de los datos operativos del taller, cumpliendo los requerimientos de calidad técnica exigidos.
4. El sistema cuenta con procedimientos sólidos complementarios de **pruebas de software, plan de migración ETL, capacitación a usuarios y estrategia de despliegue en entornos segregados**.

---

## 13. Enlaces y Anexos Técnicos

* 🔗 **Repositorio Git Oficial:** Disponible en el espacio de trabajo local / GitHub de la entrega (`main`).
* 📁 **Ubicación del Script SQL:** `Aplicacion/prototipo V3/database/tecnofix_db.sql`
* 🌐 **Instrucciones para Ejecución Local:**
  1. Copiar carpeta `TecnoFix` en `C:\xampp\htdocs\`.
  2. Importar `tecnofix_db.sql` en phpMyAdmin.
  3. Navegar a: `http://localhost/TecnoFix/Aplicacion/prototipo V3/`
  4. Ingresar con credenciales: `admin@tecnofix.com` / `1234`.
