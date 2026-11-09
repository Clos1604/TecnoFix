# TecnoFix

Sistema web para registrar clientes y equipos, administrar órdenes de reparación y consultar su historial. La versión funcional es `Aplicacion/prototipo V3`; V1 y V2 son prototipos previos.

## Tecnologías

- PHP 8.2, PDO y sesiones.
- MySQL 8 o MariaDB compatible con InnoDB.
- HTML, CSS y JavaScript sin dependencias de paquetes declaradas actualmente.
- GitHub Actions, SonarCloud, DeepSource y Snyk (configuración preparada; requiere conectar los servicios).

## Instalación local

1. Instale PHP con las extensiones `pdo` y `pdo_mysql`, MySQL/MariaDB y un servidor web como XAMPP.
2. Copie el repositorio en `C:\xampp\htdocs\TecnoFix` e inicie Apache y MySQL.
3. Configure en el entorno de PHP/Apache las variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` y `DB_PASS`. Use un usuario de base de datos de privilegios limitados; no use credenciales de producción en desarrollo.
4. En una base de datos de desarrollo vacía, importe `Aplicacion/prototipo V3/database/tecnofix_db.sql`. Este archivo solo crea el esquema y ya no elimina tablas existentes.
5. Configure cuatro contraseñas temporales distintas mediante `DEMO_ADMIN_PASSWORD`, `DEMO_SENIOR_TECH_PASSWORD`, `DEMO_RECEPTION_PASSWORD` y `DEMO_NEW_TECH_PASSWORD`. Use valores aleatorios de al menos 12 caracteres y no los guarde en Git.
6. Active explícitamente el inicializador y ejecútelo desde CLI:

   ```powershell
   $env:ALLOW_DEMO_USER_INIT = '1'
   php 'Aplicacion/prototipo V3/scripts/init_demo_users.php'
   ```

   El inicializador solo crea las cuentas que faltan, genera hashes con `password_hash(..., PASSWORD_BCRYPT)` y marca cada cuenta para cambio de contraseña. No restablece ni modifica cuentas existentes.

7. Importe `Aplicacion/prototipo V3/database/tecnofix_demo_data.sql` después de inicializar los usuarios. Conserva los clientes, equipos, órdenes e historial; resuelve los usuarios referenciados por correo y usa inserciones repetibles.
8. Abra `http://localhost/TecnoFix/Aplicacion/prototipo%20V3/`.

El inicializador requiere acceso a la base configurado con las variables `DB_*`. No lo ejecute contra producción. En instalaciones ya existentes, `CREATE TABLE IF NOT EXISTS` no migra estructuras y la carga demo puede omitir registros con claves ya utilizadas; respalde y revise la base antes de cualquier carga.

La credencial de MySQL que se encontraba en scripts auxiliares fue retirada, pero puede seguir presente en el historial de Git. Rótela en MySQL y en todos los entornos que la hayan usado; eliminar el texto de la revisión actual no invalida una contraseña expuesta en commits anteriores.

### Recuperar el acceso administrador

Use este procedimiento solo si la tabla `usuarios` quedó vacía y necesita recuperar el acceso en una instalación local. El script `Aplicacion/prototipo V3/scripts/recovery_admin.php` solo se ejecuta por CLI, exige `RECOVERY_ENVIRONMENT=local`, rechaza servidores de base de datos remotos y no continúa si ya hay cualquier usuario. No modifica otras tablas ni restablece datos. Inserta una única fila con rol `Administrador`, estado `Activo` y un hash generado con `password_hash(..., PASSWORD_DEFAULT)`.

1. Detenga el servidor web mientras realiza la recuperación. Compruebe que MySQL está en ejecución y configure las variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` y `DB_PASS` en la misma consola. `DB_HOST` debe ser `localhost`, `127.0.0.1` o `::1`.
2. Cree un directorio de autorización fuera de `htdocs` y de `Aplicacion\prototipo V3`; por ejemplo:

   ```powershell
   $recoveryDir = Join-Path $env:USERPROFILE 'TecnoFix-recovery'
   New-Item -ItemType Directory -Force $recoveryDir | Out-Null
   icacls $recoveryDir /inheritance:r /grant:r "$($env:USERNAME):(OI)(CI)F"
   ```

   El comando anterior quita permisos heredados y concede acceso a la carpeta solo a la cuenta local actual. Genere un token de al menos 32 bytes aleatorios y guárdelo, sin comillas ni espacios adicionales, en `recovery-token.txt` dentro de esa carpeta. Por ejemplo, con PHP instalado:

   ```powershell
   $token = php -r "echo bin2hex(random_bytes(32));"
   [System.IO.File]::WriteAllText((Join-Path $recoveryDir 'recovery-token.txt'), $token)
   ```

   Mantenga el token y el archivo fuera del repositorio y del directorio público. El archivo se elimina automáticamente después de crear el administrador.
3. Configure autorización y los datos de la cuenta solo en la consola actual. No los escriba en el código, en la línea de comandos ni en archivos del proyecto:

   ```powershell
   $env:RECOVERY_ENVIRONMENT = 'local'
   $env:RECOVERY_AUTH_FILE = Join-Path $recoveryDir 'recovery-token.txt'
   $env:RECOVERY_AUTH_TOKEN = Read-Host 'Token de recuperación'
   $env:RECOVERY_ADMIN_NAME = Read-Host 'Nombre del administrador'
   $env:RECOVERY_ADMIN_EMAIL = Read-Host 'Correo del administrador'
   $securePassword = Read-Host 'Contraseña nueva (mínimo 12 caracteres)' -AsSecureString
   $passwordPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
   try {
       $env:RECOVERY_ADMIN_PASSWORD = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($passwordPointer)
   } finally {
       [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($passwordPointer)
   }
   ```

   Use una contraseña nueva y robusta. PowerShell la oculta mientras se escribe; se conserva temporalmente en el entorno del proceso PHP. Ejecute una sola vez:

   ```powershell
   php 'Aplicacion/prototipo V3/scripts/recovery_admin.php'
   ```

   El script serializa las ejecuciones concurrentes, bloquea las inserciones concurrentes en `usuarios` y realiza la inserción dentro de una transacción. Si detecta usuarios existentes, errores o credenciales inválidas, no crea una cuenta. Si falla la eliminación del archivo al final, elimínelo manualmente inmediatamente; el alta ya habrá quedado confirmada.
4. Compruebe el mensaje de éxito y que `usuarios` contiene exactamente el nuevo usuario con `rol='Administrador'` y `estado='Activo'`. Pruebe el inicio de sesión en la aplicación con el correo y contraseña elegidos. El login existente valida `password_hash` con `password_verify()`, regenera el ID de sesión y guarda el rol que usan los controles de acceso; las operaciones administrativas continuarán exigiendo una sesión autenticada con ese rol.
5. Al terminar, incluso si el script informa un error, quite las variables temporales de la misma consola:

   ```powershell
   Remove-Item Env:RECOVERY_ENVIRONMENT, Env:RECOVERY_AUTH_FILE, Env:RECOVERY_AUTH_TOKEN
   Remove-Item Env:RECOVERY_ADMIN_NAME, Env:RECOVERY_ADMIN_EMAIL, Env:RECOVERY_ADMIN_PASSWORD
   ```

   Después de probar el acceso, elimine `Aplicacion/prototipo V3/scripts/recovery_admin.php` del servidor o de la copia de trabajo si ya no se necesita. Nunca coloque el token bajo el document root; no reutilice el token ni ejecute este mecanismo contra producción.

### Usuarios demo

| Rol | Correo |
|---|---|
| Administrador | `admin@tecnofix.com` |
| Técnico sénior | `carlos.tecnico@tecnofix.com` |
| Recepción | `maria.recepcion@tecnofix.com` |
| Técnico nuevo | `pedro.nuevo@tecnofix.com` |

Las contraseñas no se publican ni tienen valores predeterminados en el repositorio. Se suministran solo al inicializador y son temporales. La aplicación muestra una sola vez la contraseña aleatoria cuando un administrador crea una cuenta nueva.

## Configuración de análisis

### SonarCloud

El análisis se define en `sonar-project.properties` y en `.github/workflows/quality.yml`. En la configuración del repositorio de GitHub, agregue el secreto `SONAR_TOKEN` y las variables `SONAR_ORGANIZATION` y `SONAR_PROJECT_KEY`. El workflow omite explícitamente el análisis hasta que estén configurados.

Los hashes bcrypt fijos estaban escritos directamente en los `INSERT` de usuarios. Aunque bcrypt es un hash unidireccional, un valor reutilizable en el repositorio es material de autenticación detectable por reglas de credenciales/secrets y permite ataques offline si se conoce la contraseña original. Sin el issue key o el reporte de SonarCloud no se puede confirmar responsablemente el identificador exacto de la regla que generó el hallazgo.

### DeepSource

`.deepsource.toml` habilita los analizadores PHP y JavaScript y marca `tests/` como pruebas. Para obtener resultados, conecte el repositorio a DeepSource y habilite la integración de la rama `dev`; la configuración local por sí sola no ejecuta ni publica un análisis.

### Snyk

Agregue el secreto `SNYK_TOKEN` a GitHub; opcionalmente, configure la variable `SNYK_ORG`. El workflow ejecuta Snyk Code cuando hay token. El análisis Open Source de dependencias se ejecuta únicamente si existe un lockfile compatible. El repositorio no tiene actualmente `composer.lock` ni lockfile de JavaScript, por lo que no hay un inventario de dependencias de aplicación que afirmar como analizado.

## Validación y cambios

El workflow `.github/workflows/quality.yml` se ejecuta en pushes a `dev`, Pull Requests hacia `main` y ejecución manual. Prepara MySQL 8, comprueba sintaxis PHP y JavaScript, inicializa los cuatro usuarios y carga los datos de prueba dos veces, valida autenticación y `password_verify()`, exige cambio de contraseña y revisa las relaciones de los datos de prueba.

Correcciones realizadas en esta rama:

- Quitados los hashes fijos y los `DROP TABLE` del SQL de esquema; los datos demo se separaron y se cargan sin duplicar filas existentes.
- Quitadas las credenciales de base de datos del código y leídas desde variables de entorno.
- Añadido inicializador CLI con contraseñas únicas externas al repositorio, hash bcrypt y cambio inicial obligatorio.
- Retirados los accesos de respaldo embebidos y las escrituras simuladas del navegador.
- Añadidos controles de sesión, regeneración del identificador, cookies endurecidas, CSRF, autorización por rol y bloqueo de API para cuentas con cambio pendiente.
- Eliminada la exposición de contraseñas temporales en la tabla y quitados valores de base de datos de manejadores JavaScript inline.

| Evidencia | Antes | Después en el código |
|---|---|---|
| Usuarios demo | Cuatro hashes fijos en SQL y contraseñas de respaldo en PHP/JavaScript | Sin hashes en SQL; cuentas inicializadas con variables de entorno y `password_hash()` |
| Importación del esquema | `DROP TABLE` eliminaba tablas | Creación no destructiva con `IF NOT EXISTS`; semilla relacional separada |
| Conexión | Credencial de base de datos fija en PHP | Configuración `DB_*` del entorno |
| API | Altas con rol admin predeterminado sin sesión y lecturas/escrituras sin controles consistentes | Sesión obligatoria, roles, CSRF en escrituras y cambio inicial impuesto por servidor |
| Informes de analizadores | No se adjuntó reporte previo | No se han ejecutado ni recibido reportes en este entorno |

No se ejecutaron PHP, MySQL/MariaDB, SonarCloud, DeepSource ni Snyk localmente en esta sesión. Los comandos `php`, `mysql` y `mariadb` no están disponibles aquí. Por tanto, no se afirma que las pruebas o análisis estén aprobados; las primeras evidencias reales aparecerán al ejecutar el workflow después de conectar los servicios.

- Workflows: [GitHub Actions](https://github.com/Clos1604/TecnoFix/actions)
- Repositorio: [Clos1604/TecnoFix](https://github.com/Clos1604/TecnoFix)

## Pendiente en GitHub

1. Confirmar que `dev` es la rama de trabajo y crear un Pull Request de `dev` hacia `main`; no se hicieron commits ni pushes.
2. Configurar `SONAR_TOKEN`, `SONAR_ORGANIZATION` y `SONAR_PROJECT_KEY`, y comprobar el primer reporte y la regla concreta del hallazgo original.
3. Conectar el repositorio a DeepSource y revisar el análisis de `dev`.
4. Configurar `SNYK_TOKEN`; para análisis de dependencias, incorporar y mantener un manifiesto/lockfile si se agregan paquetes.
5. Revisar en CI que SQL, usuarios de prueba, login y relaciones pasen antes de abrir el Pull Request.
