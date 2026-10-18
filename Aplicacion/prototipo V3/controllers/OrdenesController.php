<?php
/**
 * CONTROLADOR: OrdenesController
 * Procesa la creación, filtrado y cambio de estado de órdenes de servicio
 */

require_once __DIR__ . '/../config/session.php';
startSecureSession();
require_once __DIR__ . '/../models/Orden.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/AuthController.php';

class OrdenesController {

    public static function index($filtroEstado = null, $busqueda = null) {
        return Orden::getAll($filtroEstado, $busqueda);
    }

    public static function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!AuthController::isAuthenticated() || AuthController::isPasswordChangePending() || !AuthController::isCsrfValid()) {
                return ['success' => false, 'message' => 'Sesión no válida para registrar órdenes.'];
            }
            if (!in_array($_SESSION['user_rol'] ?? '', ['Administrador', 'Recepcion'], true)) {
                return ['success' => false, 'message' => 'No tiene permisos para registrar órdenes.'];
            }

            $clienteNombre = trim(self::postString('cliente_nombre'));
            $clienteTelefono = trim(self::postString('cliente_telefono'));
            $tipoDispositivo = trim(self::postString('tipo_dispositivo'));
            $marca = trim(self::postString('marca'));
            $modelo = trim(self::postString('modelo'));
            $diagnostico = trim(self::postString('diagnostico_inicial'));
            $costoValue = self::postString('costo_estimado', '0');
            $costo = filter_var($costoValue, FILTER_VALIDATE_FLOAT);

            // Validaciones estrictas
            if (empty($clienteNombre) || empty($clienteTelefono) || empty($tipoDispositivo) || empty($marca) || empty($modelo) || empty($diagnostico)) {
                return ['success' => false, 'message' => 'Por favor complete todos los campos obligatorios del formulario.'];
            }
            if ($costo === false || $costo < 0 || strlen($diagnostico) > 10000) {
                return ['success' => false, 'message' => 'El costo o el diagnóstico no son válidos.'];
            }

            $email = trim(self::postString('cliente_email'));
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'El correo del cliente no es válido.'];
            }
            $technicianValue = self::postString('id_tecnico');
            $technicianId = $technicianValue === '' ? null : filter_var($technicianValue, FILTER_VALIDATE_INT);
            if ($technicianId === false) {
                return ['success' => false, 'message' => 'El técnico seleccionado no es válido.'];
            }

            $data = [
                'cliente_nombre' => $clienteNombre,
                'cliente_telefono' => $clienteTelefono,
                'cliente_email' => $email,
                'cliente_direccion' => trim(self::postString('cliente_direccion')),
                'tipo_dispositivo' => $tipoDispositivo,
                'marca' => $marca,
                'modelo' => $modelo,
                'numero_serie' => trim(self::postString('numero_serie', 'N/A')),
                'diagnostico_inicial' => $diagnostico,
                'costo_estimado' => $costo,
                'id_tecnico' => $technicianId,
                'fecha_estimada' => self::postString('fecha_estimada', date('Y-m-d', strtotime('+5 days')))
            ];

            $userId = intval($_SESSION['user_id'] ?? 0);
            if ($userId <= 0) {
                return ['success' => false, 'message' => 'Sesión no válida para registrar órdenes.'];
            }
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
            if (!AuthController::isAuthenticated() || AuthController::isPasswordChangePending() || !AuthController::isCsrfValid()) {
                return ['success' => false, 'message' => 'Sesión no válida para actualizar órdenes.'];
            }
            if (!in_array($_SESSION['user_rol'] ?? '', ['Administrador', 'Tecnico'], true)) {
                return ['success' => false, 'message' => 'No tiene permisos para cambiar estados.'];
            }

            $idOrden = filter_var(self::postString('id_orden'), FILTER_VALIDATE_INT);
            $nuevoEstado = trim(self::postString('nuevo_estado'));
            $observaciones = trim(self::postString('observaciones'));

            $validStates = ['Pendiente', 'En Proceso', 'Esperando Repuesto', 'Completado', 'Entregado', 'Cancelado'];
            if ($idOrden === false || $idOrden <= 0 || !in_array($nuevoEstado, $validStates, true)) {
                return ['success' => false, 'message' => 'Datos de actualización de orden inválidos.'];
            }

            $userId = intval($_SESSION['user_id'] ?? 0);
            if ($userId <= 0) {
                return ['success' => false, 'message' => 'Sesión no válida para actualizar órdenes.'];
            }
            $res = Orden::cambiarEstado($idOrden, $nuevoEstado, $observaciones, $userId);

            if (!empty($res['exito'])) {
                return ['success' => true, 'message' => 'Estado de la orden actualizado a ' . $nuevoEstado];
            } else {
                return ['success' => false, 'message' => 'Error al cambiar estado: ' . ($res['error'] ?? '')];
            }
        }
        return ['success' => false, 'message' => 'Petición no válida.'];
    }

    private static function postString($key, $default = '') {
        $value = $_POST[$key] ?? $default;
        return is_string($value) ? $value : $default;
    }
}
