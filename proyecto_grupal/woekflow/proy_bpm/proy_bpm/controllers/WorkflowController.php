<?php
require_once 'models/WorkflowModel.php';

class WorkflowController {
    private $model;

    public function __construct() {
        $this->model = new WorkflowModel();
    }

    public function handleRequest($currentUser) {
        $action = $_GET['action'] ?? 'view_dashboard';
        $id = $_POST['id'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            switch ($action) {
                // Acciones del Estudiante
                case 'iniciar_solicitud':
                    $procesos = $this->model->getAllProcesses();
                    $tieneActivo = false;
                    foreach ($procesos as $p) {
                        // Esta activo si NO está en estado FIN ni RECHAZO_ARCHIVADO
                        if ($p['estudiante'] === $currentUser['nombre'] && !in_array($p['estado'], ['FIN', 'RECHAZO_ARCHIVADO'])) {
                            $tieneActivo = true; 
                            break;
                        }
                    }
                    if (!$tieneActivo && isset($_POST['tipo'])) {
                        $this->model->createProcess($currentUser['nombre'], $_POST['tipo']);
                    }
                    break;
                case 'realizar_pago':
                    $this->model->updateProcessState($id, 'PAGO_REALIZADO');
                    break;

                case 'mandar_inscripcion':
                    $materias_seleccionadas = isset($_POST['materias']) ? implode(', ', $_POST['materias']) : 'Ninguna';
                    $this->model->updateProcessState($id, 'INSCRIPCION_ENVIADA', ['materias' => $materias_seleccionadas]);
                    break;

                case 'finalizar':
                    $this->model->updateProcessState($id, 'FIN');
                    break;

                // Si el estudiante cancela su solicitud, elimina el registro de la DB
                case 'cancelar_solicitud':
                    $procesos = $this->model->getAllProcesses();
                    $procesosFiltrados = array_filter($procesos, function($p) use ($id) {
                        return $p['id'] !== $id;
                    });
                    $this->model->saveProcesses(array_values($procesosFiltrados));
                    break;

                case 'modificar_inscripcion':
                    $this->model->updateProcessState($id, 'MATERIAS_DISPONIBLES_ENVIADAS', ['materias' => '']);
                    break;

                case 'guardar_retiro_adicion':
                    $materias_actualizadas = isset($_POST['materias']) ? implode(', ', $_POST['materias']) : 'Ninguna';
                    $this->model->updateProcessState($id, 'FIN', ['materias' => $materias_actualizadas]);
                    break;

                // Acciones para limpiar las notificaciones de rechazo en el panel estudiante
                case 'cerrar_rechazo_inscripcion':
                case 'cerrar_rechazo_retiro':
                    // Cambiamos a un estado interno 'ARCHIVADO_RECHAZO' o simplemente lo eliminamos para liberar el panel. 
                    // En este caso lo actualizaremos a un estado terminal archivado para que quede en el historial de Kardex pero libere al estudiante.
                    $this->model->updateProcessState($id, 'RECHAZO_ARCHIVADO');
                    break;

                // Acciones de Kardex (Inscripción ordinaria)
                case 'solicitar_pago':
                    $this->model->updateProcessState($id, 'PAGO_SOLICITADO');
                    break;

                // si el Kardex rechaza inscripción necesita un motivo
                case 'kardex_rechazar_inscripcion':
                    $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : 'No especificado';
                    $this->model->updateProcessState($id, 'INSCRIPCION_RECHAZADA', ['motivo_rechazo' => $motivo]);
                    break;

                case 'verificar_pago_ok':
                    $this->model->updateProcessState($id, 'MATERIAS_DISPONIBLES_ENVIADAS');
                    break;
                case 'verificar_pago_error':
                    $this->model->updateProcessState($id, 'PAGO_SOLICITADO');
                    break;
                case 'aprobar_carga_ok':
                    $this->model->updateProcessState($id, 'COMPROBANTE_ENVIADO');
                    break;
                case 'aprobar_carga_error':
                    $this->model->updateProcessState($id, 'MATERIAS_DISPONIBLES_ENVIADAS');
                    break;

                // si el Kardex rechaza Retiro/Adición necesita un motivo
                case 'kardex_aceptar_retiro':
                    $this->model->updateProcessState($id, 'RETIRO_ADICION_ACEPTADO');
                    break;
                case 'kardex_rechazar_retiro':
                    $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : 'No especificado';
                    $this->model->updateProcessState($id, 'RETIRO_ADICION_RECHAZADO', ['motivo_rechazo' => $motivo]);
                    break;
            }
            header("Location: index.php");
            exit;
        }

        // Cargar Vistas
        $procesos = $this->model->getAllProcesses();
        if ($currentUser['role'] === 'kardex') {
            require 'views/kardex.php';
        } else {
            $misProcesos = array_filter($procesos, function($p) use ($currentUser) {
                return $p['estudiante'] === $currentUser['nombre'];
            });
            
            require_once 'models/MateriaModel.php';
            $materiaModel = new MateriaModel();
            $materias_disponibles = $materiaModel->getAllMaterias();
            
            $procesos = $misProcesos;
            require 'views/estudiante.php';
        }
    }
}
?>