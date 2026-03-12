<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CloudinaryController extends Controller
{
    #[OA\Post(
        path: "/api/cloudinary/firma",
        summary: "Genera una firma para subida segura a Cloudinary",
        description: "El backend firma la solicitud con el API Secret de Cloudinary (que nunca se expone al frontend). El cliente usa estos datos para subir directamente a Cloudinary.",
        security: [["sanctum" => []]],
        tags: ["Cloudinary"]
    )]
    #[OA\Response(
        response: 200,
        description: "Parámetros firmados para la subida",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "timestamp",  type: "integer", example: 1710000000),
                new OA\Property(property: "signature",  type: "string",  example: "abc123..."),
                new OA\Property(property: "api_key",    type: "string",  example: "123456789012345"),
                new OA\Property(property: "cloud_name", type: "string",  example: "mi-cloud"),
                new OA\Property(property: "folder",     type: "string",  example: "comprobantes"),
            ]
        )
    )]
    public function generarFirma(Request $request)
    {
        $timestamp = time();
        $folder    = config('services.cloudinary.folder', 'comprobantes');

        // Los parámetros que se firman deben coincidir EXACTAMENTE con
        // los que el frontend enviará en el POST a Cloudinary.
        // Cloudinary exige orden alfabético de las claves.
        $paramsToSign = [
            'folder'    => $folder,
            'timestamp' => $timestamp,
        ];
        ksort($paramsToSign);

        // Construir el string a firmar: "clave=valor&clave=valor" + API_SECRET
        $queryString = collect($paramsToSign)
            ->map(fn ($v, $k) => "{$k}={$v}")
            ->implode('&');

        $signature = sha1($queryString . config('services.cloudinary.secret'));

        return response()->json([
            'timestamp'  => $timestamp,
            'signature'  => $signature,
            'api_key'    => config('services.cloudinary.api_key'),
            'cloud_name' => config('services.cloudinary.cloud_name'),
            'folder'     => $folder,
        ]);
    }
}
