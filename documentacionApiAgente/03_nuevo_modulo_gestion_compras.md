# Módulo Gestión Compras (Arquitectura DDD)

## Contexto
Debido a la necesidad de realizar operaciones cruzadas (como buscar artículos de inventario al crear Actas de Entrega en `GestionSistemas`), y comprendiendo que *Inventario* pertenece lógicamente al departamento de Compras, se inicializa el nuevo módulo de dominio `GestionCompras` bajo la arquitectura **Vertical Slices / DDD**.

## Estructura Implementada

Se replicó la estructura de 4 capas para empezar la migración paulatina de la lógica de Compras e Inventarios.

1. **Domain (Dominio)**
   * `app/Modules/GestionCompras/Domain/Entities/InventarioBasico.php`: Entidad pura y anémica que abstrae únicamente los datos de respuesta necesarios para los selects del frontend (`id`, `codigo`, `nombre`, `marca`, `modelo`, `serial`), dejando atrás el modelo completo cargado de Eloquent si no es necesario.
   * `app/Modules/GestionCompras/Domain/Contracts/InventarioRepositoryInterface.php`: Interfaz que dicta el contrato de los repositorios de inventario.

2. **Application (Aplicación)**
   * `app/Modules/GestionCompras/Application/UseCases/BuscarInventarioUseCase.php`: Contiene la regla de negocio para buscar (no ejecuta si el string está vacío y mapea de entidades a arrays primitivos).

3. **Infrastructure (Infraestructura)**
   * `app/Modules/GestionCompras/Infrastructure/Repositories/InventarioRepository.php`: Repositorio que interactúa con el legacy model (`App\Models\Inventario`) realizando consultas `LIKE` sobre `codigo` y `nombre`, retornando arreglos de la entidad de dominio `InventarioBasico`.

4. **Presentation (Presentación)**
   * `app/Modules/GestionCompras/Presentation/Controllers/InventarioSearchController.php`: Controlador que expone el endpoint y cumple con OpenApi/Swagger.
   * `app/Modules/GestionCompras/Presentation/Routes/api.php`: Define las rutas correspondientes (`/api/gestion-compras/...`) y es requerido desde el `routes/api.php` principal del proyecto.

## Frontend Component
Para consumir este endpoint en NexaCore, se diseñó el componente:
* `InventarioSearchSelect.jsx`: Un *Dropdown Searchable* que invoca al endpoint a medida que el usuario tipea el código o nombre, evitando tener que memorizar y digitar manualmente el ID de la base de datos de los periféricos. 

## Regla Hacia el Futuro
A medida que el proyecto requiera refactorizar funciones de Compras o Inventario, **deben ubicarse dentro de este nuevo módulo `GestionCompras`** siguiendo exactamente las mismas reglas de separación de capas que usamos en `GestionSistemas`.
