# Documentación: Subida Firmada a Cloudinary (Seguridad)

Esta implementación permite que el panel administrativo de React suba comprobantes de pago de forma segura a Cloudinary, utilizando firmas generadas por el backend de Laravel para proteger el `API_SECRET`.

## 1. Concepto de Seguridad
A diferencia de los "Upload Presets" sin firma (Unsigned), esta implementación utiliza **Signed Uploads**. 
- **Firma en Servidor:** El `API_SECRET` reside únicamente en el archivo `.env` del servidor.
- **Validación Temporal:** La firma generada incluye un `timestamp` para evitar que sea reutilizada indefinidamente.
- **Restricción de Carpeta:** El backend define mediante la firma a qué carpeta (`comprobantes/`) puede subir el cliente.

## 2. Configuración del Backend (Laravel)

### Variables de Entorno (.env)
Es obligatorio configurar las siguientes claves en el archivo `.env` del proyecto `agua-api`:

```env
CLOUDINARY_CLOUD_NAME=tu_cloud_name
CLOUDINARY_API_KEY=tu_api_key
CLOUDINARY_API_SECRET=tu_api_secret
CLOUDINARY_UPLOAD_FOLDER=comprobantes
```

### Endpoint de Firma
El controlador `CloudinaryController` expone el siguiente endpoint:

- **Ruta:** `POST /api/cloudinary/firma`
- **Protección:** Requiere autenticación vía **Sanctum** (Bearer Token).
- **Respuesta:**
  ```json
  {
      "timestamp": 1710260000,
      "signature": "abcdef123456...",
      "api_key": "1234567890",
      "cloud_name": "mi-cloud",
      "folder": "comprobantes"
  }
  ```

## 3. Integración con el Frontend (React)

El frontend sigue este flujo de 3 pasos:

1. **Petición de Firma:** Antes de subir la imagen, solicita los datos firmados al backend.
2. **Subida Directa:** Realiza un `POST` multipart a `https://api.cloudinary.com/v1_1/{cloud_name}/image/upload` enviando:
   - `file`: El archivo de imagen.
   - `api_key`: Entregada por el backend.
   - `timestamp`: Entregado por el backend.
   - `signature`: La firma generada por el backend.
   - `folder`: La carpeta destino autorizada.
3. **Registro en BD:** Tras recibir la URL segura de Cloudinary, el frontend invoca `POST /api/pedidos/{id}/comprobante` para guardar el enlace en la base de datos de la API.

## 4. Desarrollo Técnico
- **Algoritmo:** SHA-1 (Hash).
- **Controlador:** `app/Http/Controllers/CloudinaryController.php`
- **Rutas:** `routes/api.php`
- **Modelo:** Los cambios se reflejan en el campo `comprobante_url` de la tabla `pedidos`.

---
*Documentación generada el 12 de Marzo de 2026 para Transportes de Agua Herrera V.*
