<?php
class WorkflowModel {
    private $file = 'data/db.json';

    public function getAllProcesses() {
        if (!file_exists($this->file)) {
            file_put_contents($this->file, json_encode([]));
        }
        $data = file_get_contents($this->file);
        return json_decode($data, true);
    }

    public function saveProcesses($processes) {
        file_put_contents($this->file, json_encode($processes, JSON_PRETTY_PRINT));
    }

    public function createProcess($nombre_estudiante, $tipo) {
        $processes = $this->getAllProcesses();
        
        // El estado inicial cambia según el tipo de trámite seleccionado
        $estado_inicial = ($tipo === 'RETIRO_ADICION') ? 'RETIRO_ADICION_SOLICITADO' : 'SOLICITUD_ENVIADA';

        $newProcess = [
            'id' => uniqid(),
            'estudiante' => $nombre_estudiante,
            'tipo' => $tipo, // 'INSCRIPCION' o 'RETIRO_ADICION'
            'estado' => $estado_inicial,
            'materias' => '',
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];
        $processes[] = $newProcess;
        $this->saveProcesses($processes);
    }

    public function updateProcessState($id, $newState, $extraData = []) {
        $processes = $this->getAllProcesses();
        foreach ($processes as &$process) {
            if ($process['id'] === $id) {
                $process['estado'] = $newState;
                foreach ($extraData as $key => $value) {
                    $process[$key] = $value;
                }
                break;
            }
        }
        $this->saveProcesses($processes);
    }
}
?>