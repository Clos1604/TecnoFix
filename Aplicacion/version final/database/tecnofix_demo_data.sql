USE `tecnofix_db`;

-- Demo data is intentionally separate from schema creation and user setup.
-- INSERT IGNORE makes reruns skip existing primary/unique keys.
INSERT IGNORE INTO `clientes` (`id_cliente`, `nombre_completo`, `cedula_rnc`, `telefono`, `email`, `direccion`) VALUES
(1, 'Juan Pérez', '001-1234567-8', '809-555-0101', 'juan.perez@email.com', 'Av. 27 de Febrero #45, SD'),
(2, 'Ana Gómez', '001-7654321-9', '809-555-0202', 'ana.gomez@email.com', 'Calle El Sol #12, Santiago'),
(3, 'Empresa Inversiones SRL', '130-998877-1', '809-555-0303', 'contacto@inversiones.com', 'Torre Empresarial Piso 5');

INSERT IGNORE INTO `equipos` (`id_equipo`, `id_cliente`, `tipo_dispositivo`, `marca`, `modelo`, `numero_serie`) VALUES
(1, 1, 'Laptop', 'Dell', 'XPS 15 9520', 'SN-DELL-99812'),
(2, 2, 'Smartphone', 'Apple', 'iPhone 13 Pro', 'SN-APPL-77123'),
(3, 3, 'Desktop PC', 'Custom', 'Core i7 12th Gen', 'SN-CUST-33411');

INSERT IGNORE INTO `ordenes_servicio` (`id_orden`, `codigo_orden`, `id_cliente`, `id_equipo`, `id_tecnico`, `diagnostico_inicial`, `solucion_propuesta`, `estado`, `costo_estimado`, `costo_final`, `fecha_ingreso`, `fecha_estimada`)
SELECT 1, 'ORD-2026-001', 1, 1,
       (SELECT `id_usuario` FROM `usuarios` WHERE `email` = 'carlos.tecnico@tecnofix.com'),
       'El equipo no enciende tras descarga eléctrica.', 'Reemplazo de chip de carga y tarjeta madre.',
       'En Proceso', 4500.00, 0.00, NOW(), '2026-10-10'
UNION ALL
SELECT 2, 'ORD-2026-002', 2, 2,
       (SELECT `id_usuario` FROM `usuarios` WHERE `email` = 'carlos.tecnico@tecnofix.com'),
       'Pantalla fisurada y falla en digitalizador táctil.', 'Cambio de pantalla completa OLED.',
       'Pendiente', 6800.00, 0.00, NOW(), '2026-10-08'
UNION ALL
SELECT 3, 'ORD-2026-003', 3, 3, NULL,
       'Mantenimiento preventivo y formateo de sistema.', 'Limpieza de componentes y reinstalación de SO.',
       'Completado', 2500.00, 2500.00, '2026-10-01', '2026-10-03';

INSERT IGNORE INTO `historial_ordenes` (`id_historial`, `id_orden`, `estado_anterior`, `estado_nuevo`, `observaciones`, `id_usuario`)
SELECT 1, 1, 'Pendiente', 'En Proceso', 'Se desmonta la tarjeta para diagnóstico avanzado.',
       (SELECT `id_usuario` FROM `usuarios` WHERE `email` = 'carlos.tecnico@tecnofix.com')
UNION ALL
SELECT 2, 3, 'En Proceso', 'Completado', 'Mantenimiento realizado y pruebas pasadas exitosamente.',
       (SELECT `id_usuario` FROM `usuarios` WHERE `email` = 'admin@tecnofix.com');