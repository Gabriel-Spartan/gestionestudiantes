<?php

class AuthController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function login($username, $password)
    {
        // Lógica de login
    }

    public function logout()
    {
        // Lógica de logout
    }

    public function isAuthenticated()
    {
        // Verificar autenticación
    }
}
