# AGUA-API

API REST para la gestión de pedidos y distribución de agua.

## Descripción

API REST desarrollada en **Laravel 12** para digitalizar y optimizar el proceso de venta y distribución de agua. Permite la administración centralizada de clientes, registro de pedidos de suministro y seguimiento en tiempo real del estado de cada entrega.

## Tecnologías Utilizadas

| Tecnología | Versión | Descripción |
|------------|---------|-------------|
| PHP | 8.2+ | Lenguaje de programación |
| Laravel | 12.x | Framework PHP |
| MySQL | 8.0+ | Base de datos relacional |
| Laravel Sanctum | 4.x | Autenticación API |
| L5 Swagger | 10.x | Documentación OpenAPI |
| Cloudinary | - | Almacenamiento de comprobantes |

## Instalación

### Requisitos Previos
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ (para Vite)

### Pasos

1. **Clonar el repositorio**
```bash
git clone <repo-url>
cd agua-api
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
```

4. **Configurar el archivo `.env`**
```env
APP_NAME=AGUA-API
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agua_api
DB_USERNAME=root
DB_PASSWORD=

# Cloudinary (opcional)
CLOUDINARY_CLOUD_NAME=tu_cloud_name
CLOUDINARY_API_KEY=tu_api_key
CLOUDINARY_API_SECRET=tu_api_secret
CLOUDINARY_UPLOAD_FOLDER=comprobantes
```

5. **Generar clave de aplicación**
```bash
php artisan key:generate
```

6. **Ejecutar migraciones**
```bash
php artisan migrate
```

7. **Iniciar el servidor**
```bash
php artisan serve
```

## Arquitectura

El sistema sigue el patrón **MVC (Modelo-Vista-Controlador)** adaptado para una arquitectura de API REST:

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php       # Registro, login, logout
│   │   ├── ClienteController.php    # CRUD clientes
│   │   ├── PedidoController.php     # CRUD pedidos + funcionalidades
│   │   ├── DashboardController.php  # Estadísticas
│   │   └── CloudinaryController.php # Firmas para uploads
│   └── Requests/
│       ├── StorePedidoRequest.php
│       ├── UpdatePrioridadRequest.php
│       ├── UpdateNotaRequest.php
│       └── UpdateDireccionRequest.php
├── Models/
│   ├── User.php     # Usuarios del sistema
│   ├── Cliente.php  # Entidad cliente
│   └── Pedido.php   # Entidad pedido
routes/
└── api.php          # Definición de endpoints
```

## Modelos de Datos

### Cliente
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BigInt | Identificador único |
| nombre | String | Nombre completo |
| telefono | String (unique) | Teléfono - identificador de negocio |
| direccion | String | Dirección principal |
| deleted_at | Timestamp | Soft delete |

### Pedido
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BigInt | Identificador único |
| cliente_id | BigInt (FK) | Relación con cliente |
| cantidad_agua | Integer | Volumen solicitado |
| direccion_entrega | String | Lugar de entrega |
| prioridad | Integer (1-3) | Nivel de urgencia |
| estado | Enum | Pendiente, En proceso, Entregado, Cancelado |
| estado_pago | Enum | Pendiente, Pagado |
| fecha_pedido | Datetime | Fecha de creación |
| nota | Text | Instrucciones de entrega |
| comprobante_url | String | URL del comprobante (Cloudinary) |
| deleted_at | Timestamp | Soft delete |

### User
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BigInt | Identificador único |
| name | String | Nombre del usuario |
| email | String (unique) | Correo electrónico |
| password | String | Contraseña encriptada |

## Autenticación

La API utiliza **Laravel Sanctum** con tokens Bearer. Todos los endpoints (excepto register/login) requieren autenticación.

### Header Requerido
```
Authorization: Bearer <tu_token>
```

### Flujo de Autenticación
1. **Registro**: `POST /api/register` → Recibes token
2. **Login**: `POST /api/login` → Recibes token
3. **Uso**: Incluir token en header de cada request
4. **Logout**: `POST /api/logout` → Invalida token

## Endpoints

### Autenticación

#### Registro
```
POST /api/register
```
**Request:**
```json
{
    "name": "Juan Perez",
    "email": "juan@test.com",
    "password": "secret123"
}
```
**Response (201):**
```json
{
    "user": {
        "id": 1,
        "name": "Juan Perez",
        "email": "juan@test.com"
    },
    "token": "1|abc123..."
}
```

#### Login
```
POST /api/login
```
**Request:**
```json
{
    "email": "juan@test.com",
    "password": "secret123"
}
```
**Response (200):**
```json
{
    "user": { ... },
    "token": "1|abc123..."
}
```

#### Logout
```
POST /api/logout
```
**Headers:** `Authorization: Bearer <token>`
**Response (200):**
```json
{
    "mensaje": "Sesión cerrada correctamente"
}
```

---

### Clientes

#### Listar clientes
```
GET /api/clientes
```
**Response (200):**
```json
[
    {
        "id": 1,
        "nombre": "Empresa S.A",
        "telefono": "999888777",
        "direccion": "Av. La Paz 123"
    }
]
```

#### Crear cliente
```
POST /api/clientes
```
**Request:**
```json
{
    "nombre": "Empresa S.A",
    "telefono": "999888777",
    "direccion": "Av. La Paz 123"
}
```

#### Ver cliente
```
GET /api/clientes/{id}
```

#### Actualizar cliente
```
PUT /api/clientes/{id}
```
**Request:**
```json
{
    "nombre": "Nuevo Nombre",
    "direccion": "Nueva Dirección"
}
```

#### Eliminar cliente
```
DELETE /api/clientes/{id}
```

#### Buscar por teléfono
```
GET /api/clientes/telefono/{telefono}
```

#### Historial de pedidos
```
GET /api/clientes/{id}/pedidos
```

---

### Pedidos

#### Listar pedidos
```
GET /api/pedidos?desde=2026-01-01&hasta=2026-12-31
```

#### Crear pedido
```
POST /api/pedidos
```
**Request:**
```json
{
    "cliente_id": 1,
    "cantidad_agua": 20,
    "direccion_entrega": "Mz F Lt 1",
    "prioridad": 1,
    "nota": "Llamar al llegar"
}
```

#### Ver pedido
```
GET /api/pedidos/{id}
```

#### Actualizar pedido
```
PUT /api/pedidos/{id}
```
**Request:**
```json
{
    "estado": "En proceso"
}
```

#### Eliminar pedido (Soft Delete)
```
DELETE /api/pedidos/{id}
```

#### Crear cliente + pedido (atómico)
```
POST /api/pedido-completo
```
**Request:**
```json
{
    "telefono": "999888777",
    "nombre": "Empresa S.A.",
    "direccion": "Av. Siempre Viva 123",
    "cantidad_agua": 10,
    "direccion_entrega": "Local 2",
    "prioridad": 1,
    "nota": "Traer sencillo"
}
```

#### Filtrar por estado
```
GET /api/pedidos/estado/{estado}
```
Estados: `Pendiente`, `En proceso`, `Entregado`, `Cancelado`

#### Filtrar por prioridad
```
GET /api/pedidos/prioridad/{nivel}
```
Nivel: 1, 2 o 3

#### Pedidos pendientes
```
GET /api/pedidos/pendientes
```

#### Pedidos priorizados
```
GET /api/pedidos/priorizados
```

#### Pedidos de hoy
```
GET /api/pedidos/hoy
```

#### Pedidos con pago pendiente
```
GET /api/pedidos/pago-pendiente
```

#### Actualizar estado
```
PUT /api/pedidos/{id}/estado
```
**Request:**
```json
{
    "estado": "En proceso"
}
```

#### Actualizar prioridad
```
PUT /api/pedidos/{id}/prioridad
```
**Request:**
```json
{
    "prioridad": 2
}
```

#### Actualizar nota
```
PUT /api/pedidos/{id}/nota
```
**Request:**
```json
{
    "nota": "Dejar con el portero"
}
```

#### Actualizar dirección
```
PUT /api/pedidos/{id}/direccion
```
**Request:**
```json
{
    "direccion_entrega": "Av. Sol 555"
}
```

#### Cancelar pedido
```
PUT /api/pedidos/{id}/cancelar
```

#### Actualizar estado de pago
```
PUT /api/pedidos/{id}/estado-pago
```
**Request:**
```json
{
    "estado_pago": "Pagado"
}
```

#### Guardar comprobante
```
POST /api/pedidos/{id}/comprobante
```
**Request:**
```json
{
    "comprobante_url": "https://res.cloudinary.com/..."
}
```

---

### Dashboard

#### Estadísticas
```
GET /api/dashboard
```
**Response:**
```json
{
    "pedidos_pendientes": 5,
    "pedidos_proceso": 2,
    "pedidos_entregados": 10,
    "pagos_pendientes": 3,
    "total_pedidos": 20
}
```

---

### Cloudinary

#### Generar firma para upload
```
POST /api/cloudinary/firma
```
**Response:**
```json
{
    "timestamp": 1710260000,
    "signature": "abc123...",
    "api_key": "1234567890",
    "cloud_name": "mi-cloud",
    "folder": "comprobantes"
}
```

## Forma de Trabajar

### Estructura de Ramas
- `main` - Rama principal (producción)
- `develop` - Rama de desarrollo
- `feature/*` - Nuevas funcionalidades
- `fix/*` - Correcciones de bugs

### Convenciones

#### Commits
```
feat: nueva funcionalidad
fix: corrección de bug
docs: documentación
refactor: refactorización
```

#### Código
- Follows Laravel conventions
- PSR-4 autoloading
- Code style: Laravel Pint

### Comandos Útiles

```bash
# Desarrollo
composer run dev

# Tests
composer run test

# Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Ver rutas
php artisan route:list

# Generar documentación Swagger
php artisan l5-swagger:generate
```

## Licencia

MIT License - [LICENSE](LICENSE)

---

*API desarrollada para Transportes de Agua Herrera V.*
