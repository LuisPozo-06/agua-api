<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(version: "1.0", title: "AGUA-API Documentation", description: "Documentación de la API REST para gestionar pedidos de agua.")]
#[OA\SecurityScheme(securityScheme: "sanctum", type: "http", scheme: "bearer")]
abstract class Controller
{
    //
}
