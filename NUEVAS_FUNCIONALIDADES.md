# Nuevas Funcionalidades: AGUA-API

Este documento detalla las nuevas características implementadas en el backend de AGUA-API correspondientes a la gestión priorizada y mejorada de pedidos. A continuación, se explican sus reglas, rutas y ejemplos de uso.

## 1. Cambiar Prioridad de Pedido
Permite a un administrador nivelar la urgencia (del 1 al 3) para un pedido existente usando un Form Request.

* **Endpoint**: `PUT /api/pedidos/{id}/prioridad`
* **Controlador**: `PedidoController@actualizarPrioridad`
* **Validación**: `UpdatePrioridadRequest` (`prioridad` min:1, max:3)

**Ejemplo Request:**
```json
// PUT /api/pedidos/1/prioridad
{
    "prioridad": 1
}
```
**Ejemplo Response:**
```json
{
    "mensaje": "Prioridad actualizada",
    "pedido": {
        "id": 1,
        "prioridad": 1,
        "estado": "Pendiente",
        ...
    }
}
```

## 2. Listar Pedidos Ordenados por Prioridad
Retorna todos los pedidos ordenados de mayor prioridad (ASC) y fecha de pedido (ASC).

* **Endpoint**: `GET /api/pedidos/priorizados`
* **Controlador**: `PedidoController@priorizados`

**Ejemplo Response:**
```json
[
    {
        "id": 5,
        "prioridad": 1,
        "fecha_pedido": "2026-03-10T12:00:00",
        "cliente": { ... }
    },
    {
        "id": 2,
        "prioridad": 2,
        ...
    }
]
```

## 3. Cancelar un Pedido
Actualiza el estado de un pedido directamente a `Cancelado` sin usar Soft Delete, permitiendo auditar dicho registro cancelado si se consulta a futuro.

* **Endpoint**: `PUT /api/pedidos/{id}/cancelar`
* **Controlador**: `PedidoController@cancelar`

**Ejemplo Response:**
```json
{
    "mensaje": "Pedido cancelado",
    "pedido": {
        "id": 3,
        "estado": "Cancelado"
    }
}
```

## 4. Historial de Pedidos por Cliente
Devuelve una lista de todos los pedidos históricos pertenecientes a un ID de cliente específico.

* **Endpoint**: `GET /api/clientes/{id}/pedidos`
* **Controlador**: `ClienteController@pedidos`

**Ejemplo Response:**
```json
[
    {
        "id": 8,
        "cliente_id": 1,
        "fecha_pedido": "2026-03-10T15:20:00"
    },
    {
        "id": 3,
        "cliente_id": 1,
        "fecha_pedido": "2026-03-09T08:15:00"
    }
]
```

## 5. Pedidos del Día (Hoy)
Utiliza `Carbon::today()` para filtrar y listar aquellos pedidos cuya `fecha_pedido` coincida con el día en curso.

* **Endpoint**: `GET /api/pedidos/hoy`
* **Controlador**: `PedidoController@hoy`

## 6. Agregar Campo "Nota" a Pedidos
Se agregó exitosamente la columna `nota` (`TEXT`, nullable) a la tabla `pedidos` en la base de datos a través de una nueva migración. Esto permite guardar anotaciones sobre la entrega (p. ej. *"Tocar timbre 3 veces"* o *"Casa color rojo"*).

* **Modelo Actualizado**: Agregado a `$fillable` en `Pedido.php`, pudiendo pasarlo ahora en el request de creación común o completo.
