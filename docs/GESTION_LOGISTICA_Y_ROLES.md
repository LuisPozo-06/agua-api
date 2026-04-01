# Gestión Logística de Choferes y Control de Acceso (RBAC)

Este documento detalla las funcionalidades implementadas para la asignación de conductores a pedidos y el sistema de permisos de usuario.

## 1. Sistema de Roles y Permisos (RBAC)
Se implementó un control de acceso basado en roles para restringir secciones del sistema.

### Backend (Laravel)
*   **Librería**: `spatie/laravel-permission`.
*   **Seeder**: `RolesAndPermissionsSeeder` crea permisos para cada sección (`ver pedidos`, `crear usuarios`, etc.) y el rol `Administrador`.
*   **Auth**: El `AuthController` ahora retorna los roles y todos los permisos del usuario en el login para que el frontend pueda reaccionar.
*   **Gestión de Usuarios**: `UserController` permite crear usuarios y asignarles permisos específicos de forma dinámica.

### Frontend (React)
*   **AuthContext**: Incluye la función `hasPermission(permiso)` para validar si el usuario logueado tiene acceso.
*   **Sidebar**: Las opciones del menú se renderizan dinámicamente basándose en los permisos del usuario.
*   **Vista de Usuarios**: Nueva sección para que el Administrador gestione el personal y sus accesos.

---

## 2. Módulo de Gestión de Choferes
Permite administrar el personal encargado de las entregas.

### Backend (Laravel)
*   **Modelo/Migración**: Tabla `chofers` con campos:
    *   `nombres_completos` (string)
    *   `telefono` (string)
    *   `is_active` (boolean): Estado laboral (en temporada o fuera).
    *   `estado_asignacion` (enum: disponible, ocupado): Estado logístico real.
*   **Controlador**: `ChoferController` con CRUD completo. Los booleanos están validados y casteados para evitar errores de interpretación entre BD y Frontend.

### Frontend (React)
*   **Vista Choferes**: Interfaz para crear, editar y eliminar conductores.
*   **Filtros de Estado**: Se implementó una lista desplegable para activar/desactivar choferes (Estado Laboral) y gestionar su disponibilidad manual si fuera necesario.

---

## 3. Asignación Logística en Pedidos
Integración de los conductores con el flujo de entrega de agua.

### Backend (Laravel)
*   **Relación**: Se añadió la relación `chofer` a los pedidos.
*   **Eager Loading**: `PedidoRepository` ahora carga siempre al chofer asignado para evitar múltiples consultas (`with('chofer')`).
*   **Lógica de Negocio (`PedidoService`)**: 
    *   Al asignar un chofer, su estado pasa a `ocupado`.
    *   Al marcar un pedido como **Entregado**, el chofer asignado vuelve automáticamente a estado `disponible`.
*   **Endpoint**: `PUT /api/pedidos/{id}/asignar-chofer` (Recibe `chofer_id`).

### Frontend (React)
*   **Tabla de Pedidos**: Se añadió la columna **"Chofer Asignado"**. 
    *   Muestra un selector solo con choferes **Activos** y **Disponibles**.
    *   Si un chofer ya está asignado, su nombre se mantiene visible incluso si pasa a estado ocupado.
    *   Una vez entregado el pedido, el nombre del chofer queda como registro histórico pero ya no es editable.

---

## 4. Mejoras Técnicas y Correcciones
*   **Filtro "Pago Pendiente"**: Se corrigió el error de concatenación dinámica en `Pedidos.jsx` que impedía filtrar pedidos no pagados.
*   **Casts de Booleano**: Se aseguró que los estados activos/inactivos se comuniquen como tipos booleanos nativos entre PHP y JS para evitar errores con el valor `"0"`.
