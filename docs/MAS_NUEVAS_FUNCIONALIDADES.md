# Nuevas Funcionalidades Extendidas: AGUA-API

A continuación, se detalla la implementación del segundo bloque de funcionalidades solicitadas integradas a la API, manteniendo los estándares de seguridad y buenas prácticas de Laravel.

## 1. Actualizar Nota de un Pedido
Permite al administrador modificar o añadir instrucciones de entrega en el campo `nota` de un pedido existente.

* **Endpoint**: `PUT /api/pedidos/{id}/nota`
* **Controlador**: `PedidoController@actualizarNota`
* **Validación**: `UpdateNotaRequest` (nullable, string, max:500)

**Ejemplo Request:**
```json
// PUT /api/pedidos/3/nota
{
    "nota": "Dejar en la puerta, casa azul"
}
```

## 2. Filtrar Pedidos por Prioridad
Busca y lista aquellos pedidos que coincidan con un nivel específico de prioridad (1, 2 o 3), ordenados ascendentemente por su fecha de creación.

* **Endpoint**: `GET /api/pedidos/prioridad/{nivel}`
* **Controlador**: `PedidoController@pedidosPorPrioridad`

**Ejemplo Request:**
`GET /api/pedidos/prioridad/1`

## 3. Buscar Cliente por Teléfono
Dado que el teléfono es el identificador único principal de los clientes en nuestro sistema, este endpoint recupera la data en base a este número de contacto.

* **Endpoint**: `GET /api/clientes/telefono/{telefono}`
* **Controlador**: `ClienteController@buscarPorTelefono`
* **Comportamiento**: Devuelve el cliente en JSON o arroja un error **404 Not Found** si el número no está registrado.

**Ejemplo Request:**
`GET /api/clientes/telefono/999888777`

## 4. Actualizar Dirección de Entrega de un Pedido
Útil cuando el cliente solicita un cambio de domicilio para la recepción de un pedido ya generado.

* **Endpoint**: `PUT /api/pedidos/{id}/direccion`
* **Controlador**: `PedidoController@actualizarDireccion`
* **Validación**: `UpdateDireccionRequest` (required, string, max:255)

**Ejemplo Request:**
```json
// PUT /api/pedidos/3/direccion
{
    "direccion_entrega": "Av. Principal 456, Mz A Lt 2"
}
```

## 5. Listar Pedidos Pendientes
Vista rápida que filtra todos los pedidos cuyo estado sea estrictamente "Pendiente", sin necesidad de usar el endpoint dinámico general.

* **Endpoint**: `GET /api/pedidos/pendientes`
* **Controlador**: `PedidoController@pendientes`

## 6. Filtrar Pedidos por Rango de Fechas
Se extendió el endpoint principal de visualización de pedidos para aceptar "Query Parameters" dinámicos. Esto permite a la gerencia auditar las operaciones comprendidas entre dos fechas específicas, retornado los datos ordenados del más reciente al más antiguo.

* **Endpoint**: `GET /api/pedidos`
* **Controlador**: Modificación sobre `PedidoController@index`
* **Filtros Soportados**: `?desde=YYYY-MM-DD` y `?hasta=YYYY-MM-DD` (Pueden usarse juntos o separados)

**Ejemplo Request:**
`GET /api/pedidos?desde=2026-03-01&hasta=2026-03-10`
