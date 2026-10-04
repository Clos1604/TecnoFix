<?php
/**
 * CONTROLADOR: OrdenesController
 * Procesa la creación, filtrado y cambio de estado de órdenes de servicio
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Orden.php';
require_once __DIR__ . '/../models/Usuario.php';

class OrdenesController {

    public static function index($filtroEstado = null, $busqueda = null) {
        return Orden::getAll($filtroEstado, $busqueda);
    }

    public static function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clienteNombre = trim($_POST['cliente_nombre'] ?? '');
            $clienteTelefono = trim($_POST['cliente_telefono'] ?? '');
            $tipoDispositivo = trim($_POST['tipo_dispositivo'] ?? '');
            $marca = trim($_POST['marca'] ?? '');
            $modelo = trim($_POST['modelo'] ?? '');
            $diagnostico = trim($_POST['diagnostico_inicial'] ?? '');
            $costo = floatval($_POST['costo_estimado'] ?? 0);

            // Validaciones estrictas
            if (empty($clienteNombre) || empty($clienteTelefono) || empty($tipoDispositivo) || empty($marca) || empty($modelo) || empty($diagnostico)) {
                return ['success' => false, 'message' => 'Por favor complete todos los campos obligatorios del formulario.'];
            }

            $data = [
                'cliente_nombre' => $clienteNombre,
                'cliente_telefono' => $clienteTelefono,
                'cliente_email' => trim($_POST['cliente_email'] ?? ''),
                'cliente_direccion' => trim($_POST['cliente_direccion'] ?? ''),
                'tipo_dispositivo' => $tipoDispositivo,
                'marca' => $marca,
                'modelo' => $modelo,
                'numero_serie' => trim($_POST['numero_serie'] ?? 'N/A'),
                'diagnostico_inicial' => $diagnostico,
                'costo_estimado' => $costo,
                'id_tecnico' => $_POST['id_tecnico'] ?? null,
                'fecha_estimada' => $_POST['fecha_estimada'] ?? date('Y-m-d', strtotime('+5 days'))
            ];

            $userId = $_SESSION['user_id'] ?? 1;
            $res = Orden::crearConTransaccion($data, $userId);

            if (!empty($res['exito'])) {
                return ['success' => true, 'codigo' => $res['codigo_orden'], 'message' => 'Orden de servicio creada con éxito en la base de datos.'];
            } else {
                return ['success' => false, 'message' => 'Error al registrar orden: ' . ($res['error'] ?? 'Error desconocido')];
            }
        }
        return ['success' => false, 'message' => 'Solicitud inválida.'];
    }

    public static function actualizarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idOrden = intval($_POST['id_orden'] ?? 0);
            $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');

            if ($idOrden <= 0 || empty($nuevoEstado)) {
                return ['success' => false, 'message' => 'Datos de actualización de orden inválidos.'];
            }

            $userId = $_SESSION['user_id'] ?? 1;
            $res = Orden::cambiarEstado($idOrden, $nuevoEstado, $observaciones, $userId);

            if (!empty($res['exito'])) {
                return ['success' => true, 'message' => 'Estado de la orden actualizado a ' . $nuevoEstado];
            } else {
                return ['success' => false, 'message' => 'Error al cambiar estado: ' . ($res['error'] ?? '')];
            }
        }
        return ['success' => false, 'message' => 'Petición no válida.'];
    }
}
