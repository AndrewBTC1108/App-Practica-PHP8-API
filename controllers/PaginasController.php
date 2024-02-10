<?php
namespace Controllers;
use Model\Usuario;

class PaginasController 
{
    public static function getUsuarios() 
    {
        $usuarios = Usuario::all('ASC');

        echo json_encode(['usuarios' => $usuarios]);
    }

    public static function create()
    {
        $alertas = [];
        $alertas = Usuario::getAlertas();
        $user = new Usuario;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $user->sincronizar($_POST);
            //validar info
            $alertas = $user->validarInfo();
            if(empty($alertas)){
                $user->guardar();
                echo json_encode(['data' => 'Usuario Creado']);
            }else {
                echo json_encode(['errors' => $alertas]);
            }
        }
    }

    public static function update()
    {
        $alertas = [];

        $alertas = Usuario::getAlertas();
        $user = new Usuario;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //sincronizar info con el modelo
            $user->sincronizar($_POST);
            //validar la info
            $alertas = $user->validarInfo();
            if(empty($alertas)){
                $user->guardar();
                echo json_encode(['data' => 'usuario modificado']);
            } else {
                echo json_encode(['errors' => $alertas]);
            }

        }
    }

    //borrar usuario
    public static function destroy()
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $id = $_POST['id'];
        
            $usuario = Usuario::find($id);
    
            $usuario->eliminar();
    
            echo json_encode(['data' => 'usuario Eliminado']);  
        }
    }
}