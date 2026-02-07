<?php
session_start();
require_once __DIR__ . '/../config/Auth.php';

// Usa a classe Auth para fazer logout
Auth::logout();

// Redireciona para a home
header("Location: /");
exit();