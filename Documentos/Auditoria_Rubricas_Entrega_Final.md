# Auditoría de rúbricas de la entrega final de TecnoFix

**Fecha de revisión:** 7 de noviembre de 2026  
**Base revisada:** archivos presentes en el repositorio y guía de entrega del III parcial.  
**Propósito:** identificar evidencia disponible y brechas antes de preparar el expediente final.

## Conclusión ejecutiva

TecnoFix cuenta con una aplicación PHP/MySQL funcional en la rama de trabajo: autenticación, administración de usuarios, clientes y órdenes; validaciones del servidor; control de sesión y permisos; y persistencia relacional. También existe una prueba automatizada de autenticación y relaciones de datos configurada en GitHub Actions. Sin embargo, el repositorio, por sí solo, **no acredita todavía el cumplimiento integral de la rúbrica final**. El alcance escrito excede lo implementado; los casos de prueba anteriores no tienen evidencia fechada de reejecución; los diagramas y matrices requieren cierre contra el código vigente; y faltan el paquete final PDF, un release etiquetado y el video de defensa.

Esta revisión es estática. No se ejecutó la aplicación ni la suite en esta revisión porque no se encontró PHP disponible en el entorno local; tampoco se tuvo acceso a una instancia de base de datos. Los estados “demostrado en código” describen capacidad visible en los archivos, no una ejecución observada.

## Resultado por dimensión puntuable

| Dimensión | Máximo | Estado con la evidencia disponible | Brecha principal |
|---|---:|---|---|
| Calidad y funcionamiento del producto | 15 | Parcial | Hay flujos implementados, pero no hay evidencia actual de un recorrido end-to-end ejecutado con confirmación y persistencia. El alcance declarado incluye funciones ausentes. |
| Diseño, arquitectura e implementación | 10 | Parcial | La separación MVC/API está en el código. Los diagramas editables se identifican como iniciales y no se acreditó un ER/despliegue actualizado al estado real. |
| Verificación, validación, pruebas y trazabilidad | 12.5 | Insuficiente | Hay prueba automatizada de autenticación/datos en CI y casos históricos en el avance, pero no un informe final de ejecución con fechas, evidencias, defectos y regresión. |
| Documentación y reproducibilidad | 7.5 | Parcial | README y scripts de instalación/datos demo existen; falta un expediente final consolidado, manual con pantallas reales y release reproducible identificado. |
| Defensa grabada, demo y dominio individual | 5 | Pendiente de evidencia | No se encontró archivo ni enlace del video. La participación, rostro, contribuciones y demostración deben aportarse por el equipo. |

La guía no establece una fórmula para convertir estos estados de auditoría en puntos. No se estima una calificación.

## Revisión de cada criterio de cierre

| Criterio de la guía | Estado | Evidencia encontrada y trabajo necesario |
|---|---|---|
| 5.1 Producto candidato y demo funcional | Parcial | V3 incluye login, usuarios, clientes y órdenes en `Aplicacion/prototipo V3`. El esquema y pantallas no incluyen inventario, pagos ni solicitudes de repuestos. Preparar lista terminada/parcial/excluida acorde al producto real y grabar capturas/video de flujos ejecutados. |
| 5.2 Diseño, arquitectura y código final | Parcial | Modelos, controladores, API, vistas y esquema SQL están separados. El diagrama de arquitectura se titula “inicial”; actualizarlo junto con el ER, diccionario y despliegue para reflejar V3, no la propuesta. |
| 5.3 Suite final de V&V | Insuficiente | `tests/authentication_test.php` cubre inicio de sesión, contraseñas incorrectas, banderas demo y relaciones de datos; `.github/workflows/quality.yml` prepara MySQL y ejecuta esa prueba. Falta evidencia de ejecución final fechada y casos integrales de creación/actualización de orden, valores límite, permisos y persistencia, con entradas, esperado/obtenido, ejecutor y evidencia. |
| 5.4 Bitácora de defectos y regresión | Pendiente | No se localizó una bitácora con identificadores, severidad, reproducción, corrección/commit y regresión. Registrar defectos reales encontrados durante la reejecución; no inventar hallazgos ni estados. |
| 5.5 Expediente documental | Parcial | Hay README, informe del segundo parcial, matriz de trazabilidad, SQL y diagramas. Consolidar PDF final, guía de usuario basada en la interfaz vigente, administración, informe de pruebas, matriz cerrada y respaldo/recuperación en un paquete coherente. |
| 5.6 Release y trazabilidad cerrada | Insuficiente | No hay etiquetas Git (`git tag --list` no devolvió versiones). Crear un release solo después de cerrar y verificar los cambios; relacionar cada requisito con caso, resultado, evidencia y versión. No se observó evidencia de permisos/accesibilidad del repositorio público. |
| 5.7 Informe técnico de calidad | Parcial | README explica tecnologías, instalación, límites del análisis estático y advertencia sobre credenciales antiguas. Añadir conclusiones basadas en pruebas ejecutadas y separar claramente implementado, parcial, excluido y riesgo residual. |
| Video de defensa | Pendiente de evidencia | No se encontró MP4 ni enlace en los archivos revisados. Lo debe grabar el estudiante/equipo con voz propia y demostración real, según la guía. |

## Evidencias anteriores que requieren actualización

El informe del segundo parcial describe una propuesta que incluye inventario, compras, pagos y reportes financieros, mientras el esquema y la interfaz actual muestran un producto más acotado. También incluye una tabla de pruebas como “aprobadas” y capturas/registros como evidencia, pero no se encontraron archivos de captura asociados en el repositorio. Esos resultados deben tratarse como históricos hasta que se reejecuten y se adjunte evidencia verificable; no deben copiarse como resultados del candidato final.

La guía exige que los requisitos aprobados se conserven y que los cambios de alcance se expliquen. Por ello, documentar cada requisito amplio como implementado, parcial, diferido o excluido con justificación y vínculo a pantalla/componente/tabla/caso de prueba. No marcar como terminado lo que solo figura en el plan.

## Funcionalidad observada en el código

| Área | Estado observado |
|---|---|
| Autenticación | Login por correo/nombre, `password_verify`, regeneración de sesión y cambio de contraseña inicial. |
| Usuarios | Alta administrativa, roles Administrador/Técnico/Recepción, usuarios demo seguros por inicializador y consulta administrativa. |
| Clientes y equipos | Se crean al registrar una orden; no se observó un flujo CRUD independiente de clientes. |
| Órdenes | Alta transaccional de cliente/equipo/orden/historial, listado/búsqueda y cambio de estado con historial. |
| Seguridad de solicitudes | Token CSRF, sesión con cookie HttpOnly/SameSite y controles de rol en operaciones sensibles. |
| Inventario, compras, pagos | No se observaron módulos/tablas operativos en la versión V3 actual. |
| Reportes | Existe sección de interfaz, pero su contenido debe verificarse contra datos y cálculos reales antes de declararlo terminado. |

## Acciones necesarias para poder cerrar la entrega

1. **Definir el alcance final aprobado.** Alinear requisitos, criterios de aceptación, interfaz, código, base de datos y README. Explicar las bajas/cambios respecto de inventario, compras, pagos y reportes si se conservan fuera de V1.
2. **Actualizar diagramas y trazabilidad.** Entregar diagrama MVC/API/despliegue y ER/diccionario actuales; cerrar requisito → pantalla → componente → tabla → prueba → resultado → evidencia.
3. **Ejecutar la suite desde cero.** Seguir README en un entorno limpio; guardar logs/capturas con fecha y ejecutor. Añadir los casos faltantes y registrar resumen de aprobadas, fallidas y bloqueadas. Repetir regresión tras corregir defectos.
4. **Completar defectos y manuales.** Crear bitácora solo con defectos reproducidos. Añadir guía de usuario/administrador con capturas reales y pasos reproducibles de respaldo/restauración.
5. **Preparar el candidato y release.** Corregir brechas, verificar que el repositorio no incluya secretos ni datos personales reales, crear una etiqueta/release identificable y comprobar que otro equipo pueda instalarlo desde cero.
6. **Grabar la defensa.** Asegurar que cada integrante aparezca y explique una contribución propia y una decisión técnica, y muestre una funcionalidad ejecutándose; comprobar acceso al video antes de entregar.
7. **Exportar el expediente consolidado a PDF.** Incluir portada e integrantes, índice, matriz de correcciones I/II, evidencias actualizadas, documentación, rúbrica de auto-verificación, identificador del release y enlace accesible al video.

## Referencias del repositorio

- Código de aplicación: `Aplicacion/prototipo V3/`.
- Esquema y datos demo: `Aplicacion/prototipo V3/database/`.
- Prueba automatizada actual: `tests/authentication_test.php`.
- Flujo de CI: `.github/workflows/quality.yml`.
- Guía de ejecución: `README.md`.
- Avance anterior: `Documentos/SegundoParcial_AvanceProyecto_TecnoFix.md`.
- Matriz y diagramas entregados: carpeta `Documentos/`.
