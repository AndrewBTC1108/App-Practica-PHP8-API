<?php
// Permitir solicitudes CORS desde todos los orígenes
header("Access-Control-Allow-Origin: http://localhost:5173");
// Permitir los métodos HTTP permitidos
header("Access-Control-Allow-Methods: GET, POST");
// Permitir los encabezados personalizados que se envían con la solicitud
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// Permitir el uso de credenciales en solicitudes CORS
header("Access-Control-Allow-Credentials: true");

// Si la solicitud es OPTIONS, responder con un código de estado 200
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}