<?php
session_start();

require_once 'controllers/WorkflowController.php';
require_once 'models/UserModel.php';
require_once 'models/MateriaModel.php';

$action = $_GET['action'] ?? 'home';
$userModel = new UserModel();
$workflowApp = new WorkflowController();
$materiaModel = new MateriaModel();

// 1. Lógica de Autenticación
if ($action === 'login_post') {
    $user = $userModel->getUserByUsername($_POST['username']);
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: index.php");
    } else {
        $error = "Credenciales incorrectas.";
        require 'views/login.php';
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// 2. Proteger rutas: Si no hay sesión, mostrar login
if (!isset($_SESSION['user'])) {
    require 'views/login.php';
    exit;
}

$currentUser = $_SESSION['user'];

// 3. Lógica de Administración (CRUD de usuarios)
if ($currentUser['role'] === 'admin') {
    // Usuarios
    if ($action === 'admin_create_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userModel->createUser($_POST['username'], $_POST['password'], $_POST['role'], $_POST['nombre']);
        header("Location: index.php"); exit;
    }
    if ($action === 'admin_delete_user' && isset($_GET['id'])) {
        $userModel->deleteUser($_GET['id']);
        header("Location: index.php"); exit;
    }
    
    // Materias
    if ($action === 'admin_create_materia' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $materiaModel->createMateria($_POST['codigo'], $_POST['nombre'], $_POST['prerequisito']);
        header("Location: index.php"); exit;
    }
    if ($action === 'admin_delete_materia' && isset($_GET['id'])) {
        $materiaModel->deleteMateria($_GET['id']);
        header("Location: index.php"); exit;
    }

    // Vistas del Admin
    $usuarios = $userModel->getAllUsers();
    $materias = $materiaModel->getAllMaterias();
    require 'views/admin.php';
    exit;
}
// 4. Lógica de Workflow (Estudiante y Kardex)
if ($currentUser['role'] === 'estudiante' || $currentUser['role'] === 'kardex') {
    $workflowApp->handleRequest($currentUser);
}
?>