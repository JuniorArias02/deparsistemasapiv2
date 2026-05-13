pusher
app_id = "2153923"
key = "ba83f0d074913a42e048"
secret = "351c70c13e3829c05d43"
cluster = "sa1"

Perfecto, aquí tienes el bloque de instrucciones técnicas listo para que se lo envíes a tu equipo. Está diseñado para que no pierdan tiempo y sepan exactamente qué configurar en el backend y qué credenciales necesitan de **Pusher**.

---

### **Instrucciones de Continuación: Implementación de WebSockets (Pusher)**

Para habilitar las funciones de chat y actualizaciones en tiempo real en el módulo de **Buzón de Sugerencias**, debemos integrar **Pusher** como servicio de broadcasting, dado que el entorno de producción (Hostinger) no permite servidores de sockets persistentes.

#### **1. Configuración del Entorno (Backend)**

Es necesario configurar el driver de broadcasting en Laravel. Actualizar el archivo `.env` con las siguientes credenciales de la cuenta de Pusher ya creada:

* `BROADCAST_DRIVER=pusher`
* `PUSHER_APP_ID=tu_app_id`
* `PUSHER_APP_KEY=tu_app_key`
* `PUSHER_APP_SECRET=tu_app_secret`
* `PUSHER_APP_CLUSTER=sa1` (South America - São Paulo)

#### **2. Eventos de Dominio y Broadcasting**

Dentro de `src/Modules/BuzonSugerencias/Domain/Events/`, crear los eventos que deben transmitir en tiempo real.

* **Requisito:** Los eventos deben implementar la interfaz `ShouldBroadcast`.
* **Eventos principales:**
* `NuevoComentarioPublicado`: Se dispara al guardar un mensaje en `sugerencia_comentarios`.
* `EstadoTicketActualizado`: Se dispara al cambiar el `estado_id`.



#### **3. Manejo de Archivos Multimedia (Imágenes)**

**Importante:** Por limitaciones de tamaño en los mensajes de Pusher (10KB), **no enviar la imagen en Base64 por el socket.**

* El flujo debe ser: `Subir imagen vía API POST` -> `Guardar en Storage` -> `Disparar evento con la URL pública de la imagen`.

#### **4. Integración en el Frontend (React)**

* Utilizar **Laravel Echo** y **Pusher-JS** para la suscripción a los canales.
* **Seguridad:** Implementar **Private Channels** para asegurar que solo el creador del ticket y el administrador asignado puedan escuchar los mensajes del `codigo_ticket` correspondiente. Reutilizar el middleware de JWT para la autenticación del canal (`broadcasting/auth`).

#### **5. Tareas Inmediatas para el Agente:**

1. Instalar el SDK de Pusher: `composer require pusher/pusher-php-server`.
2. Configurar el archivo `config/broadcasting.php` para asegurar que el cluster apunte a `sa1`.
3. Definir la lógica de autorización en `routes/channels.php` vinculada a la entidad `BuzonSugerencia`.

---

**Nota para el equipo:** El objetivo es que la interfaz de React se actualice automáticamente sin necesidad de recargar la página (F5) cada vez que el administrador responda o el usuario suba una nueva evidencia.