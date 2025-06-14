<?php

class StudentController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function index()
    {
        // Listar estudiantes
    }

    public function create($data)
    {
        // Crear estudiante
    }

    public function update($id, $data)
    {
        // Actualizar estudiante
    }

    public function delete($id)
    {
        // Eliminar estudiante
    }

    public function view($id)
    {
        // Ver detalles de estudiante
    }
}
