# Plan de Migración a Arquitectura DDD (Clean Architecture)

## Contexto y Objetivo
Estamos en un proceso de refactorización y migración gradual del código espagueti o arquitecturas MVC convencionales hacia una arquitectura orientada al dominio (Domain-Driven Design) bajo el patrón **Vertical Slices**. 

El objetivo es modularizar todas las características del departamento de sistemas en un dominio centralizado (`GestionSistemas`), para lograr una mejor mantenibilidad, escalabilidad y testabilidad del código.

## Principios Fundamentales de la Migración

1. **Desconexión Gradual de Legacy:**
   A medida que refactorizamos un módulo (ej. `pcEntregas` o `pcDevueltos`), los controladores y rutas antiguas en `App\Http\Controllers` deben ser **desconectados** e ignorados a favor del nuevo flujo.
   - Las nuevas rutas se alojan bajo el prefijo del módulo, ej: `/api/gestion-sistemas/actas-entrega`.
   - El código en React también se elimina del legacy (`src/modules/pcEntregas`) y se crea bajo `src/modules/GestionSistemas/ActasEntrega`.

2. **Estructura en Capas (DDD) en Laravel:**
   Toda nueva característica bajo `GestionSistemas` **debe** implementarse respetando la separación de capas:

   * **Domain (Dominio):**
     Contiene las reglas de negocio puras.
     *Ubicación:* `app/Modules/GestionSistemas/Domain/Entities/`
     *Regla:* Aquí van clases PHP nativas (Entidades) sin ninguna dependencia de Laravel o base de datos.
   
   * **Application (Aplicación):**
     Orquesta la lógica del negocio.
     *Ubicación:* `app/Modules/GestionSistemas/Application/`
     *Componentes:*
     - **DTOs:** Objetos para transferir datos de las peticiones a la capa de aplicación de forma limpia.
     - **Use Cases:** Los casos de uso (ej. `CrearActaDevolucionUseCase`). Aquí reside la lógica principal del requerimiento y es quien llama a los repositorios para interactuar con los datos.
   
   * **Infrastructure (Infraestructura):**
     Capa que implementa la persistencia real (Base de datos, APIS, etc).
     *Ubicación:* `app/Modules/GestionSistemas/Infrastructure/Repositories/`
     *Regla:* Aquí se utilizan los modelos de Eloquent (`App\Models\...`) para guardar o leer. La lógica de BD (transacciones, consultas) no debe filtrarse a los UseCases.

   * **Presentation (Presentación):**
     Capa que recibe y responde solicitudes HTTP.
     *Ubicación:* `app/Modules/GestionSistemas/Presentation/Controllers/` y `Routes/api.php`.
     *Regla:* Los Controladores sólo validan el Request (usando Rules o FormRequests), mapean los datos a un **DTO** e inyectan el **UseCase** correspondiente. No deben tener lógica de negocio.

## Ejemplo de Flujo Refactorizado: `ActasDevolucion`
En la transición de `PcDevueltoController` a `ActaDevolucionController`:
1. El Controller recibe la petición POST.
2. Extrae las variables y construye el `CrearActaDevolucionDTO`.
3. Inyecta `CrearActaDevolucionUseCase` y lo ejecuta pasándole el DTO.
4. El UseCase prepara los archivos, crea la entidad `ActaDevolucion` en memoria.
5. El UseCase inyecta `ActaDevolucionRepository` y llama al método `save()`.
6. El Repositorio realiza la transacción en base de datos mediante Eloquent y retorna la entidad con su nuevo ID.
7. El Controller responde con formato JSON o Resource.

**Recordatorio Permanente para el Agente:** Siempre que vayas a refactorizar o crear una nueva funcionalidad para `GestionSistemas`, **debes usar la estructura de capas arriba mencionada**. Nunca introduzcas lógica de guardado directo en Eloquent dentro de los nuevos controladores si el alcance requiere un patrón de dominio.
