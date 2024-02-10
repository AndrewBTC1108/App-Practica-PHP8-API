<?php
/*
/ La clase Router se utiliza para manejar y administrar rutas en una aplicación web, 
permitiendo asociar funciones o métodos específicos a diferentes URL de la aplicación.
*/

namespace MVC;

class Router
{
    //toma dos argumentos: $url y $fn. Este método almacena la función $fn asociada a la URL $url en el array $rutasGet
    public array $getRoutes = [];
    public array $postRoutes = [];

    public function get($url, $fn)
    {
        $this->getRoutes[$url] = $fn;
    }

    public function post($url, $fn)
    {
        $this->postRoutes[$url] = $fn;
    }

    public function comprobarRutas()
    {

        $currentUrl = strtok($_SERVER['REQUEST_URI'], '?') ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }


        if ( $fn ) {
            // Call user fn va a llamar una función cuando no sabemos cual sera
            call_user_func($fn, $this); // This es para pasar argumentos
        } else {
            //redireccionamos a una pagina 404
            header('Location: /404');
        }
    }
}