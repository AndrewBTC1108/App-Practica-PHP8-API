<?php 

namespace Model;

class Usuario extends ActiveRecord
{
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'ciudad'];

    public $id;
    public $nombre;
    public $email;
    public $ciudad;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->ciudad = $args['ciudad'] ?? '';
    }

    // Validar el Login de Usuarios
    public function validarInfo()
    {
        if (!$this->nombre) {
            self::$alertas['nombre'][] = 'El nombre es requerido';
        }
        if (!$this->email) {
            self::$alertas['email'][] = 'El Email del Usuario es Obligatorio';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['email'][] = 'Email no valido';
        }
        if (!$this->ciudad) {
            self::$alertas['ciudad'][] = 'La Ciudad es requerida';
        }
        return self::$alertas;
    }
}