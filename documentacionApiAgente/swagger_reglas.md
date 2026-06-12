# Reglas de Documentación para Swagger (OpenAPI) en el Proyecto

Esta carpeta contiene las pautas y estándares para documentar la API usando Swagger (OpenAPI 3.0) mediante Atributos de PHP 8.

## Ejemplo Base

El siguiente bloque sirve como referencia estándar para documentar cualquier endpoint:

```php
#[OA\Get(
    path: '/api/areas',
    tags: ['Areas'],
    summary: 'Listar areas',
    description: 'Obtiene la lista de areas. Puede filtrarse por sede_id. Requiere permiso area.read.',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'sede_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))
    ],
    responses: [
        new OA\Response(response: 200, description: 'Lista de areas', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
        new OA\Response(response: 403, description: 'Prohibido')
    ]
)]
```

## Reglas Obligatorias y Buenas Prácticas

1. **Uso de Atributos PHP 8 (`#[]`)**
   Toda la documentación debe escribirse usando Atributos nativos de PHP 8 (e.g., `#[OA\Get]`, `#[OA\Post]`, `#[OA\Put]`, `#[OA\Delete]`). Se debe evitar el uso de comentarios de bloque (PHPDoc) con anotaciones `@OA\`.

2. **Ruta del Endpoint (`path`)**
   La ruta debe estar completa, iniciando con `/api/` (ejemplo: `/api/entidad/{id}`).

3. **Agrupación (`tags`)**
   Siempre agrupar los endpoints bajo un tag representativo del módulo o controlador, en formato de arreglo (ej. `['Areas']`, `['Sistemas']`).

4. **Resumen y Descripción (`summary` y `description`)**
   - **`summary`**: Acción corta, precisa y en infinitivo (ej. "Listar areas", "Crear acta").
   - **`description`**: Debe detallar el propósito de la ruta, mencionar si requiere algún permiso específico (ej. `Requiere permiso area.read.`) y explicar comportamientos adicionales como posibles filtros.

5. **Seguridad (`security`)**
   Para rutas protegidas por token JWT, incluir siempre la autenticación Bearer:
   `security: [['bearerAuth' => []]]`

6. **Parámetros (`parameters`)**
   Para cualquier parámetro enviado por la URL (ya sea `in: 'query'` o `in: 'path'`), instanciar un nuevo `OA\Parameter`. Definir obligatoriamente:
   - `name`: Nombre de la variable.
   - `in`: Ubicación (`query` o `path`).
   - `required`: Booleano `true` o `false`.
   - `schema`: Tipo de dato esperado (ej. `new OA\Schema(type: 'integer')`).

7. **Cuerpo de la Petición (`requestBody`)** *(Si aplica)*
   En peticiones `POST`, `PUT`, `PATCH`, se debe usar `requestBody` detallando los campos enviados o referenciando al esquema correspondiente del DTO/Form Request.

8. **Respuestas (`responses`)**
   Documentar al menos los escenarios de éxito (200, 201) y los posibles errores comunes (400, 403, 404, 500).
   - Para la respuesta exitosa genérica de la API, apuntar al esquema estándar si existe: `content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')`.
   - Incluir una descripción corta para cada código HTTP devuelto.

## Generar la Documentación

Una vez que hayas añadido o actualizado las anotaciones de tus endpoints, debes regenerar la documentación para que los cambios se reflejen. Ejecuta el siguiente comando en la raíz del proyecto:

```bash
php artisan l5-swagger:generate
```
