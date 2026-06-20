<?php
class MateriaModel {
    private $file = 'data/materias.json';

    public function __construct() {
        if (!file_exists($this->file)) {
            file_put_contents($this->file, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function getAllMaterias() {
        $data = file_get_contents($this->file);
        return json_decode($data, true) ?? [];
    }

    public function saveMaterias($materias) {
        file_put_contents($this->file, json_encode($materias, JSON_PRETTY_PRINT));
    }

    public function createMateria($codigo, $nombre, $prerequisito) {
        $materias = $this->getAllMaterias();
        $materias[] = [
            'id_materia' => uniqid(),
            'codigo' => $codigo,
            'nombre' => $nombre,
            'prerequisito' => $prerequisito
        ];
        $this->saveMaterias($materias);
    }

    public function deleteMateria($id) {
        $materias = $this->getAllMaterias();
        $materias = array_filter($materias, function($m) use ($id) {
            return $m['id_materia'] !== $id;
        });
        $this->saveMaterias(array_values($materias));
    }
}
?>