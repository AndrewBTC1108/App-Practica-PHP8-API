<?php

namespace Model;

class ActiveRecord
{

    // Base DE DATOS Protected unicamente se puede acceder en la clase y con ayuda de metodos
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    // Alertas y Mensajes
    protected static $alertas = [];

    // Definir la conexión a la BD - includes/database.php
    public static function setDB($database)
    {
        self::$db = $database;
    }

    // Setear un tipo de Alerta
    public static function setAlerta($tipo, $mensaje)
    {
        static::$alertas[$tipo][] = $mensaje;
    }

    // Obtener las alertas
    public static function getAlertas()
    {
        return static::$alertas;
    }

    // Validación que se hereda en modelos
    public function validar()
    {
        static::$alertas = [];
        return static::$alertas;
    }

    // Consulta SQL para crear un objeto en Memoria (Active Record)
    public static function consultarSQL($query)
    {
        // Consultar la base de datos
        $resultado = self::$db->query($query);

        // Iterar los resultados
        $array = [];
        //mientras que haya registros para procesar
        while ($registro = $resultado->fetch_assoc()) {
            //se van a ir guardando en el $array
            $array[] = static::crearObjeto($registro);
        }

        // liberar la memoria
        $resultado->free();

        // retornar los resultados
        return $array;
    }

    // Crea el objeto en memoria que es igual al de la BD
    protected static function crearObjeto($registro)
    {
        $objeto = new static;

        foreach ($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    // Identificar y unir los atributos de la BD
    public function atributos()
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === 'id') continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    // Sanitizar los datos antes de guardarlos en la BD
    public function sanitizarAtributos()
    {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach ($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->escape_string($value);
        }
        return $sanitizado;
    }

    // Sincroniza BD con Objetos en memoria
    public function sincronizar($args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    //Consulta Plana de SQL(Utilizar cuando los metodos del modelo no son suficientes)
    public static function SQL($query)
    {
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Registros - CRUD
    public function guardar()
    {
        //Variable resultado
        $resultado = '';
        if (!is_null($this->id)) {
            // actualizar
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro
            $resultado = $this->crear();
        }
        return $resultado;
    }

    // Obtener todos los Registros
    //por default es DESC
    public static function all($orden = 'DESC')
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id ${orden}";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Busca un registro por su id
    public static function find($id)
    {
        $query = "SELECT * FROM " . static::$tabla  . " WHERE id = ${id}";
        $resultado = self::consultarSQL($query);
        //array shift solo se va a traer un elemento, que seria el que requerimos nada mas
        return array_shift($resultado);
    }

    // Obtener Registros con cierta cantidad
    public static function get($limite)
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT ${limite} ";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }
    // Busqueda Where con una sola Columna 
    public static function where($columna, $valor)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} = '${valor}'";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }
    
    // Busca todos los registros que pertenecen a un ID 
    public static function belongsTo($columna, $valor)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} = '${valor}'";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    //Retornar los registros por un orden, va a tomar que columna queremos ordenar
    //y el tipode orden sea ascendente o descendente
    public static function ordenar($columna, $orden)
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY ${columna} ${orden} ";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    //Retornar por orden y con un limite
    public static function ordenarLimite($columna, $orden, $limite)
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY ${columna} ${orden} LIMIT ${limite} ";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Busqueda Where con Multiles opciones
    public static function whereArray($array = [])
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ";
        foreach ($array as $key => $value) {
            //validamos, busca el ultimo elemento del array
            if ($key == array_key_last($array)) {
                $query .= " ${key} = '${value}'";
            } else {
                //seguimos colocando 'AND'
                $query .= " ${key} = '${value}' AND ";
            }
        }
        // echo $query;
        $resultado = self::consultarSQL($query);
        return $resultado;
    }


    //Traer un total de registros
    //va a tomar una columna y un valor que por defecto estaran vacios ''
    //los parametros al estar por default quiere decir que son opcionales
    public static function total($columna = '', $valor = '')
    {
        $query = "SELECT count(*) FROM " . static::$tabla;
        //validamos que se haya escrito una columna
        if ($columna) {
            //le concatenamos el query
            $query .= " WHERE ${columna} = ${valor}";
        }
        //consultamos el query
        $resultado = self::$db->query($query);
        //genera un arreglo con los rrsultados
        $total = $resultado->fetch_array();
        //array shift va a sacar del arrelgo el total
        return array_shift($total);
    }

    //Total de registros con un array Where
    public static function totalArray($array = [])
    {
        $query = "SELECT count(*) FROM " . static::$tabla . " WHERE ";
        foreach ($array as $key => $value) {
            //validamos, busca el ultimo elemento del array
            if ($key == array_key_last($array)) {
                $query .= " ${key} = '${value}'";
            } else {
                //seguimos colocando 'AND'
                $query .= " ${key} = '${value}' AND ";
            }
        }
        //consultamos el query
        $resultado = self::$db->query($query);
        //genera un arreglo con los rrsultados
        $total = $resultado->fetch_array();
        //array shift va a sacar del arrelgo el total
        return array_shift($total);
    }
    // crea un nuevo registro
    public function crear()
    {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();
        // Crear consulta preparada con marcadores de posición
        $query = "INSERT INTO " . static::$tabla . " (" . implode(', ', array_keys($atributos)) . ") VALUES (" . rtrim(str_repeat('?, ', count($atributos)), ', ') . ")";
        // Preparar la consulta
        $stmt = self::$db->prepare($query);
        if (!$stmt) {
            // Manejar error si la consulta no se puede preparar
            return false;
        }
        // Obtener valores de atributos
        $valores = array_values($atributos);
        // Vincular parámetros
        $tipos = str_repeat('s', count($valores)); // asumimos que todos son cadenas (strings)
        $stmt->bind_param($tipos, ...$valores);
        // Ejecutar la consulta
        $resultado = $stmt->execute();
        // Obtener el ID del registro insertado
        $id = ($resultado) ? self::$db->insert_id : null;
        // Cerrar la consulta preparada
        $stmt->close();

        return [
            'resultado' => $resultado,
            'id' => $id
        ];
    }
    // Actualizar el registro
    public function actualizar()
    {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();
        // Crear consulta preparada con marcadores de posición
        $query = "UPDATE " . static::$tabla . " SET " . implode('=?, ', array_keys($atributos)) . "=? WHERE id = ? LIMIT 1";
        // Preparar la consulta
        $stmt = self::$db->prepare($query);
        if (!$stmt) {
            // Manejar error si la consulta no se puede preparar
            return false;
        }
        // Obtener valores de atributos y el ID
        $valores = array_values($atributos);
        $valores[] = $this->id;
        // Vincular parámetros
        $tipos = str_repeat('s', count($valores) - 1) . 'i'; // asumimos que todos son cadenas (strings) excepto el ID
        $stmt->bind_param($tipos, ...$valores);
        // Ejecutar la consulta
        $resultado = $stmt->execute();
        // Cerrar la consulta preparada
        $stmt->close();

        return $resultado;
    }

    // Eliminar un Registro por su ID
    public function eliminar()
    {
        // Crear consulta preparada
        $query = "DELETE FROM " . static::$tabla . " WHERE id = ? LIMIT 1";
        // Preparar la consulta
        $stmt = self::$db->prepare($query);
        if (!$stmt) {
            // Manejar error si la consulta no se puede preparar
            return false;
        }
        // Vincular el parámetro ID
        $stmt->bind_param("i", $this->id); // 'i' indica que el parámetro es un entero (ID)
        // Ejecutar la consulta
        $resultado = $stmt->execute();
        // Cerrar la consulta preparada
        $stmt->close();

        return $resultado;
    }
}
