<?php
/**
 * MODELO: Orden (Órdenes de Servicio)
 * Maneja operaciones CRUD y Transacciones SQL para TecnoFix
 */

require_once __DIR__ . '/../config/conexion.php';

class Orden {
    
    /**
     * Obtener todas las órdenes de servicio con datos de cliente y equipo
     */
    public static function getAll($filtroEstado = null, $busqueda = null) {
        $db = getDBConnection();
        if ($db === null) {
            return [];
        }

        $sql = "SELECT o.*, 
                       c.nombre_completo AS cliente_nombre, c.telefono,
                       CONCAT(e.tipo_dispositivo, ' ', e.marca, ' ', e.modelo) AS equipo_info,
                       u.nombre AS tecnico_nombre
                FROM ordenes_servicio o
                INNER JOIN clientes c ON o.id_cliente = c.id_cliente
                INNER JOIN equipos e ON o.id_equipo = e.id_equipo
                LEFT JOIN usuarios u ON o.id_tecnico = u.id_usuario
                WHERE 1=1";
        
        $params = [];
        if ($filtroEstado && $filtroEstado !== 'Todos') {
            $sql .= " AND o.estado = :estado";
            $params[':estado'] = $filtroEstado;
        }

        if ($busqueda) {
            $sql .= " AND (o.codigo_orden LIKE :busq OR c.nombre_completo LIKE :busq OR e.modelo LIKE :busq)";
            $params[':busq'] = '%' . $busqueda . '%';
        }

        $sql .= " ORDER BY o.id_orden DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Crear una nueva orden de servicio usando TRANSACCIONES SQL
     * Garantiza integridad referencial atómica entre Cliente, Equipo, Orden e Historial.
     */
    public static function crearConTransaccion($data, $idUsuarioCreador) {
        $db = getDBConnection();
        if ($db === null) {
            return ['exito' => false, 'error' => 'No hay conexión disponible con la base de datos.'];
        }

        try {
            // INICIO DE TRANSACCIÓN SQL
            $db->beginTransaction();

            // 1. Insertar Cliente si no existe o seleccionar existente
            $stmtCliente = $db->prepare("INSERT INTO clientes (nombre_completo, telefono, email, direccion) 
                                         VALUES (:nombre, :telefono, :email, :direccion)");
            $stmtCliente->execute([
                ':nombre' => $data['cliente_nombre'],
                ':telefono' => $data['cliente_telefono'],
                ':email' => $data['cliente_email'] ?? null,
                ':direccion' => $data['cliente_direccion'] ?? null
            ]);
            $idCliente = $db->lastInsertId();

            // 2. Insertar Equipo
            $stmtEquipo = $db->prepare("INSERT INTO equipos (id_cliente, tipo_dispositivo, marca, modelo, numero_serie)
                                        VALUES (:id_cliente, :tipo, :marca, :modelo, :sn)");
            $stmtEquipo->execute([
                ':id_cliente' => $idCliente,
                ':tipo' => $data['tipo_dispositivo'],
                ':marca' => $data['marca'],
                ':modelo' => $data['modelo'],
                ':sn' => $data['numero_serie'] ?? 'N/A'
            ]);
            $idEquipo = $db->lastInsertId();

            // 3. Generar Código Único de Orden
            $codigoOrden = 'ORD-2026-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 4. Insertar Orden de Servicio
            $stmtOrden = $db->prepare("INSERT INTO ordenes_servicio 
                (codigo_orden, id_cliente, id_equipo, id_tecnico, diagnostico_inicial, estado, costo_estimado, fecha_estimada)
                VALUES (:codigo, :id_c, :id_e, :id_t, :diag, 'Pendiente', :costo, :fecha_est)");
            
            $stmtOrden->execute([
                ':codigo' => $codigoOrden,
                ':id_c' => $idCliente,
                ':id_e' => $idEquipo,
                ':id_t' => !empty($data['id_tecnico']) ? $data['id_tecnico'] : null,
                ':diag' => $data['diagnostico_inicial'],
                ':costo' => $data['costo_estimado'] ?? 0.00,
                ':fecha_est' => $data['fecha_estimada'] ?? date('Y-m-d', strtotime('+5 days'))
            ]);
            $idOrden = $db->lastInsertId();

            // 5. Registrar en Historial
            $stmtHist = $db->prepare("INSERT INTO historial_ordenes (id_orden, estado_anterior, estado_nuevo, observaciones, id_usuario)
                                      VALUES (:id_o, 'Nuevo', 'Pendiente', 'Creación de orden de servicio inicial.', :id_u)");
            $stmtHist->execute([
                ':id_o' => $idOrden,
                ':id_u' => $idUsuarioCreador
            ]);

            // CONFIRMAR TRANSACCIÓN
            $db->commit();

            return [
                'exito' => true,
                'codigo_orden' => $codigoOrden,
                'id_orden' => $idOrden,
                'mensaje' => 'Orden de servicio creada y registrada en base de datos mediante transacción limpia.'
            ];

        } catch (Throwable $exception) {
            // REVERTIR CAMBIOS SI HAY ERROR
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return [
                'exito' => false,
                'error' => 'No se pudo registrar la orden. Verifique los datos e inténtelo de nuevo.'
            ];
        }
    }

    /**
     * Actualizar Estado de Orden de Servicio
     */
    public static function cambiarEstado($idOrden, $nuevoEstado, $observaciones, $idUsuario) {
        $db = getDBConnection();
        if ($db === null) {
            return ['exito' => false, 'error' => 'No hay conexión disponible con la base de datos.'];
        }

        try {
            $db->beginTransaction();

            // Obtener estado actual
            $stmtAct = $db->prepare("SELECT estado FROM ordenes_servicio WHERE id_orden = :id");
            $stmtAct->execute([':id' => $idOrden]);
            $orden = $stmtAct->fetch();
            $estadoAnterior = $orden ? $orden['estado'] : 'Desconocido';

            // Actualizar orden
            $stmtUpd = $db->prepare("UPDATE ordenes_servicio SET estado = :est WHERE id_orden = :id");
            $stmtUpd->execute([':est' => $nuevoEstado, ':id' => $idOrden]);

            // Registrar historial
            $stmtHist = $db->prepare("INSERT INTO historial_ordenes (id_orden, estado_anterior, estado_nuevo, observaciones, id_usuario)
                                      VALUES (:id_o, :ant, :nue, :obs, :id_u)");
            $stmtHist->execute([
                ':id_o' => $idOrden,
                ':ant' => $estadoAnterior,
                ':nue' => $nuevoEstado,
                ':obs' => $observaciones,
                ':id_u' => $idUsuario
            ]);

            $db->commit();
            return ['exito' => true, 'mensaje' => 'Estado de orden actualizado exitosamente.'];
        } catch (Throwable $exception) {
            if ($db->inTransaction()) $db->rollBack();
            return ['exito' => false, 'error' => 'No se pudo actualizar la orden. Verifique los datos e inténtelo de nuevo.'];
        }
    }
}
