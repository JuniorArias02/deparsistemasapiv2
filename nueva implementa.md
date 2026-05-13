# FEATURES  BUZON SUGERENCIA

migracion:

```
// Configuración de la Base de Datos para Buzón de Sugerencias

Table estados_ticket {
  id int [pk, increment]
  nombre varchar [note: 'Abierto, En Proceso, Resuelto, Cerrado']
}

Table buzon_sugerencia {
  id int [pk, increment]
  codigo_ticket varchar [unique, note: 'Código generado para seguimiento']
  asunto varchar
  observaciones text
  estado_id int [ref: > estados_ticket.id]
  prioridad varchar [note: 'Baja, Media, Alta']
  creado_por int [ref: > usuarios.id]
  asignado_a int [ref: > usuarios.id, note: 'Admin que atiende el caso']
  fecha_creacion timestamp [default: `now()`]
  fecha_cierre timestamp
}

Table sugerencia_adjuntos {
  id int [pk, increment]
  sugerencia_id int [ref: > buzon_sugerencia.id]
  url_imagen varchar
  fecha_subida timestamp [default: `now()`]
}

Table sugerencia_comentarios {
  id int [pk, increment]
  sugerencia_id int [ref: > buzon_sugerencia.id]
  usuario_id int [ref: > usuarios.id]
  mensaje text
  fecha_comentario timestamp [default: `now()`]
}
```

1.crear migraciones para la creacion de los nuevos campos, y crear  de una vaes datos para el estado

1. actualmente el poryecto backned tiene una arqutiectura distinta,  en estos momentos centremonos en crear una carpeta llamada Modules y crear un Dominio llamado BuzonSugerencias y de ahi, crear todas las carpetas y casos de uso correpsondientes.
    
    nota: debemos reutilizar clases de jwt creadas, y de paso  en las rutas, debemos crear un archvio para que quede en api.
    
2. casos de uso:

Para que tu documentación o diagrama de casos de uso sea profesional, es ideal agruparlos por el "actor" (quién realiza la acción). Aquí tienes la lista estructurada para cubrir todo el ciclo de vida del buzón, desde que se crea hasta que se cierra:

---

### 1. Actor: Usuario (Colaborador/Cliente)

Estos son los casos de uso para la persona que reporta la sugerencia.

- **CrearSugerencia:** El acto de registrar el ticket inicial con asunto y observaciones. y fotos
- **SubirAdjuntosSugerencia:** El proceso de cargar las múltiples fotos al momento de crear o después.
- **ConsultarEstadoSugerencia:** Permite al usuario ver si su caso está abierto, en proceso o cerrado (usando el `codigo_ticket`).
- **ListarMisSugerencias:** Ver el historial de todas las sugerencias enviadas por ese usuario.
- **EnviarComentarioSugerencia:** Participar en el "chat" o hilo de conversación una vez iniciado el caso.

### 2. Actor: Administrador (Soporte/Gestor)

Estos son para quien gestiona y responde a las solicitudes.

- **ListarBuzonPendiente:** Visualizar solo los tickets que aún no han sido atendidos.
- **AsignarResponsable:** El administrador se asigna a sí mismo o a otro el ticket (actualiza el campo `asignado_a`).
- **CambiarPrioridad:** Definir si la sugerencia es Baja, Media o Alta.
- **ResponderSugerencia:** El acto del administrador de escribir en el chat/comentarios.
- **VisualizarAdjuntos:** Abrir y revisar las imágenes enviadas por el usuario.
- **FinalizarSugerencia (o CerrarCaso):** Cambiar el estado a "Cerrado" o "Resuelto" y registrar la `fecha_cierre`.

### 3. Actor: Sistema (Automatismos)

Casos de uso que el sistema ejecuta por detrás.

- **GenerarCodigoTicket:** Algoritmo para crear el código único (ej: SUG-2026-001).
- **NotificarCambioEstado:** Enviar un aviso (email o notificación push) al usuario cuando su caso pase a "En Proceso" o "Cerrado".
- **ValidarCierreCaso:** Restringir nuevos comentarios o ediciones una vez que el estado es "Finalizado".

---

### Resumen para plasmar en el diagrama:

Si necesitas nombres técnicos más directos para un menú o API, puedes usar estos:

1. **RegistrarSugerencia**
2. **CargarEvidenciaVisual**
3. **SeguimientoTicket** (Engloba ver el chat)
4. **GestionarEstado** (Cambiar de Abierto -> Proceso -> Cerrado)
5. **HistorialConversacion**
6. **AsignacionAgente**
7. **CierreSugerencia**

**Tip:** Si vas a dibujar el diagrama, recuerda que **ResponderSugerencia** y **FinalizarSugerencia** suelen tener una relación de *Include* o *Extend* con el caso de uso principal de **GestionarBuzon**.