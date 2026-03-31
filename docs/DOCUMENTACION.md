# Documentación Técnica: API REST para Gestión de Pedidos de Agua

## 1. Título del Proyecto
**Sistema de Gestión de Pedidos de Agua (AGUA-API)**

## 2. Descripción del Proyecto
Este proyecto es una API REST desarrollada en **Laravel** diseñada para digitalizar y optimizar el proceso de venta y distribución de agua. Permite la administración centralizada de una cartera de clientes, el registro de pedidos de suministro y el seguimiento en tiempo real del estado de cada entrega.

## 3. Objetivo del Sistema
El objetivo principal es proporcionar una interfaz robusta y escalable que permita a los administradores:
- Registrar clientes de forma única (basado en número telefónico).
- Gestionar el ciclo de vida de un pedido desde su creación hasta su entrega.
- Obtener una visión analítica del negocio mediante estadísticas del Dashboard.
- Mantener la integridad de los datos mediante el uso de borrado lógico (Soft Deletes).

## 4. Tecnologías Utilizadas
- **Lenguaje**: PHP 8.2+
- **Framework**: Laravel 11
- **Base de Datos**: MySQL
- **ORM**: Eloquent
- **Arquitectura**: RESTful API
- **Herramientas de Testing**: Postman / ThunderClient

## 5. Arquitectura del Sistema
El sistema sigue el patrón de diseño **MVC (Modelo-Vista-Controlador)** adaptado para una arquitectura de API:
- **Modelos**: Representan las entidades de negocio y gestionan la lógica de base de datos a través de Eloquent.
- **Controladores**: Gestionan las peticiones HTTP, aplican validaciones y coordinan la respuesta.
- **Rutas**: Definen los puntos de entrada (endpoints) de la API siguiendo convenciones REST.
- **Migrations**: Gestión automatizada del esquema de base de datos con soporte para control de versiones.

## 6. Estructura del Proyecto
- `app/Models/`: Definición de modelos (`Cliente`, `Pedido`) y sus relaciones.
- `app/Http/Controllers/`: Lógica de negocio de la API.
- `app/Http/Requests/`: Validaciones personalizadas de Laravel.
- `database/migrations/`: Archivos de configuración de la estructura de tablas.
- `routes/api.php`: Definición de todos los endpoints disponibles.

## 7. Funcionalidades Desarrolladas
- [x] **Gestión de Clientes (CRUD)**: Creación, lectura, actualización y borrado lógico de clientes.
- [x] **Gestión de Pedidos (CRUD)**: Administración completa de pedidos vinculados a clientes.
- [x] **Operación Atómica (Pedido Completo)**: Endpoint que permite crear un pedido y un cliente en un solo paso, evitando duplicidad mediante el número telefónico.
- [x] **Filtros por Estado**: Capacidad de listar pedidos según si están Pendientes, En Proceso o Entregados.
- [x] **Dashboard de Estadísticas**: Resumen cuantitativo de la operación por estados.
- [x] **Soft Deletes**: Implementación de borrado lógico para evitar pérdida accidental de información histórica.

## 8. Endpoints del Backend

### Clientes
- `GET /api/clientes`: Lista todos los clientes activos.
- `GET /api/clientes/{id}`: Muestra el detalle de un cliente específico.
- `POST /api/clientes`: Registra un nuevo cliente (requiere validación).
- `PUT /api/clientes/{id}`: Actualiza los datos de un cliente.
- `DELETE /api/clientes/{id}`: Elimina lógicamente a un cliente.

### Pedidos
- `GET /api/pedidos`: Lista todos los pedidos con su respectiva información de cliente.
- `GET /api/pedidos/{id}`: Detalle de un pedido específico.
- `POST /api/pedidos`: Registra un pedido vinculado a un `cliente_id` existente.
- `POST /api/pedido-completo`: Crea cliente (si no existe) y pedido simultáneamente.
- `PUT /api/pedidos/{id}/estado`: Actualiza el estado del pedido.
- `DELETE /api/pedidos/{id}`: Elimina lógicamente un pedido.
- `GET /api/pedidos/estado/{estado}`: Filtra pedidos por el nombre del estado.

### General
- `GET /api/dashboard`: Retorna el conteo de pedidos pendientes, en proceso, entregados y el total global.

## 9. Modelo de Datos o Entidades

### Entidad: Cliente
| Campo | Tipo | Descripción |
|---|---|---|
| id | BigInt (PK) | Identificador único |
| nombre | String | Nombre completo del cliente |
| telefono | String (Unique) | Número de contacto (Identificador de negocio) |
| direccion | String | Dirección principal del cliente |
| deleted_at | Timestamp | Marca de tiempo para Soft Delete |

### Entidad: Pedido
| Campo | Tipo | Descripción |
|---|---|---|
| id | BigInt (PK) | Identificador único |
| cliente_id | BigInt (FK) | Relación con el cliente |
| cantidad_agua | Integer | Volumen o cantidad solicitada |
| direccion_entrega | String | Lugar de destino del pedido |
| prioridad | Integer | Nivel de urgencia |
| estado | Enum | [Pendiente, En proceso, Entregado, Cancelado] |
| fecha_pedido | Timestamp | Fecha en que se solicitó |
| deleted_at | Timestamp | Marca de tiempo para Soft Delete |

## 10. Flujo Básico del Sistema
1. **Petición**: Un cliente solicita un pedido enviando sus datos o su teléfono.
2. **Validación**: El sistema verifica si el cliente ya existe mediante el teléfono (`firstOrCreate`).
3. **Persistencia**: Se genera el pedido vinculado al cliente con el estado inicial "Pendiente".
4. **Seguimiento**: El estado del pedido se actualiza a medida que avanza el proceso de entrega.
5. **Cierre**: Una vez entregado, el pedido se marca como tal y se refleja en las estadísticas globales.

## 11. Estado Actual del Desarrollo
El sistema se encuentra en una fase **funcional y estable para operaciones base**. Se han integrado todas las reglas de negocio críticas como la no duplicidad de clientes y la persistencia de datos históricos mediante borrado lógico. Además, la **Autenticación con Laravel Sanctum** ha sido implementada exitosamente para asegurar todos los endpoints de acceso a datos.

## 12. Próximos Pasos
1. **Reportes**: Generación de reportes PDF/Excel de pedidos por rango de fechas.
2. **Notificaciones**: Sistema de alertas vía correo o SMS para cambios en el estado del pedido.
3. **Logs de Auditoría**: Registro de quién realizó cambios en los estados de los pedidos.
