<?php
class UserModel {
    private $file = 'data/users.json';

    public function __construct() {
        if (!file_exists($this->file)) {
            // Usuario administrador por defecto (Contraseña: admin)
            $defaultAdmin = [[
                'id' => uniqid(),
                'username' => 'admin',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'role' => 'admin',
                'nombre' => 'Administrador Principal'
            ]];
            file_put_contents($this->file, json_encode($defaultAdmin, JSON_PRETTY_PRINT));
        }
    }

    public function getAllUsers() {
        $data = file_get_contents($this->file);
        return json_decode($data, true);
    }

    public function saveUsers($users) {
        file_put_contents($this->file, json_encode($users, JSON_PRETTY_PRINT));
    }

    public function getUserByUsername($username) {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['username'] === $username) return $user;
        }
        return null;
    }

    public function createUser($username, $password, $role, $nombre) {
        $users = $this->getAllUsers();
        $users[] = [
            'id' => uniqid(),
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'nombre' => $nombre
        ];
        $this->saveUsers($users);
    }

    public function deleteUser($id) {
        $users = $this->getAllUsers();
        $users = array_filter($users, function($user) use ($id) {
            return $user['id'] !== $id;
        });
        $this->saveUsers(array_values($users)); // Reindexar
    }
}
?>