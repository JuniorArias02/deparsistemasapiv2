# Actualización de Descripciones de Permisos

Ejecutar estos UPDATE en la base de datos de producción. Se usa `nombre` como WHERE ya que los IDs pueden variar.

```sql
-- =============================================
-- USUARIOS
-- =============================================
UPDATE permisos SET descripcion = 'Listar y ver todos los usuarios del sistema' WHERE nombre = 'usuario.listar';
UPDATE permisos SET descripcion = 'Crear nuevos usuarios en el sistema' WHERE nombre = 'usuario.crear';
UPDATE permisos SET descripcion = 'Actualizar información de usuarios existentes' WHERE nombre = 'usuario.actualizar';
UPDATE permisos SET descripcion = 'Eliminar usuarios del sistema' WHERE nombre = 'usuario.eliminar';

-- =============================================
-- PERSONAL
-- =============================================
UPDATE permisos SET descripcion = 'Listar y ver todo el personal registrado' WHERE nombre = 'personal.listar';
UPDATE permisos SET descripcion = 'Registrar nuevo personal en el sistema' WHERE nombre = 'personal.crear';
UPDATE permisos SET descripcion = 'Actualizar datos del personal existente' WHERE nombre = 'personal.actualizar';
UPDATE permisos SET descripcion = 'Eliminar registros de personal' WHERE nombre = 'personal.eliminar';

-- =============================================
-- ROLES
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevos roles de usuario' WHERE nombre = 'rol.crear';
UPDATE permisos SET descripcion = 'Actualizar roles de usuario existentes' WHERE nombre = 'rol.actualizar';
UPDATE permisos SET descripcion = 'Eliminar roles de usuario' WHERE nombre = 'rol.eliminar';

-- =============================================
-- PERMISOS
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevos permisos en el sistema' WHERE nombre = 'permiso.crear';
UPDATE permisos SET descripcion = 'Actualizar permisos existentes' WHERE nombre = 'permiso.actualizar';
UPDATE permisos SET descripcion = 'Eliminar permisos del sistema' WHERE nombre = 'permiso.eliminar';

-- =============================================
-- SEDES
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevas sedes' WHERE nombre = 'sede.crear';
UPDATE permisos SET descripcion = 'Actualizar información de sedes' WHERE nombre = 'sede.actualizar';
UPDATE permisos SET descripcion = 'Eliminar sedes del sistema' WHERE nombre = 'sede.eliminar';

-- =============================================
-- ÁREAS
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevas áreas organizacionales' WHERE nombre = 'area.crear';
UPDATE permisos SET descripcion = 'Actualizar áreas organizacionales' WHERE nombre = 'area.actualizar';
UPDATE permisos SET descripcion = 'Eliminar áreas organizacionales' WHERE nombre = 'area.eliminar';

-- =============================================
-- CARGOS
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevos cargos de personal' WHERE nombre = 'p_cargo.crear';
UPDATE permisos SET descripcion = 'Actualizar cargos de personal' WHERE nombre = 'p_cargo.actualizar';
UPDATE permisos SET descripcion = 'Eliminar cargos de personal' WHERE nombre = 'p_cargo.eliminar';

-- =============================================
-- DEPENDENCIAS SEDES
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevas dependencias de sedes' WHERE nombre = 'dependencia_sede.crear';
UPDATE permisos SET descripcion = 'Actualizar dependencias de sedes' WHERE nombre = 'dependencia_sede.actualizar';
UPDATE permisos SET descripcion = 'Eliminar dependencias de sedes' WHERE nombre = 'dependencia_sede.eliminar';

-- =============================================
-- DATOS EMPRESA
-- =============================================
UPDATE permisos SET descripcion = 'Crear datos de empresa' WHERE nombre = 'datos_empresa.crear';
UPDATE permisos SET descripcion = 'Actualizar datos de empresa' WHERE nombre = 'datos_empresa.actualizar';
UPDATE permisos SET descripcion = 'Eliminar datos de empresa' WHERE nombre = 'datos_empresa.eliminar';

-- =============================================
-- INVENTARIO
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevos artículos en el inventario' WHERE nombre = 'inventario.crear';
UPDATE permisos SET descripcion = 'Actualizar artículos del inventario' WHERE nombre = 'inventario.actualizar';
UPDATE permisos SET descripcion = 'Eliminar artículos del inventario' WHERE nombre = 'inventario.eliminar';

-- =============================================
-- EQUIPOS PC
-- =============================================
UPDATE permisos SET descripcion = 'Registrar nuevos equipos de cómputo' WHERE nombre = 'pc_equipo.crear';
UPDATE permisos SET descripcion = 'Actualizar información de equipos de cómputo' WHERE nombre = 'pc_equipo.actualizar';
UPDATE permisos SET descripcion = 'Eliminar equipos de cómputo del registro' WHERE nombre = 'pc_equipo.eliminar';

-- =============================================
-- CARACTERÍSTICAS TÉCNICAS PC
-- =============================================
UPDATE permisos SET descripcion = 'Registrar características técnicas de equipos' WHERE nombre = 'pc_caracteristicas_tecnicas.crear';
UPDATE permisos SET descripcion = 'Actualizar características técnicas de equipos' WHERE nombre = 'pc_caracteristicas_tecnicas.actualizar';
UPDATE permisos SET descripcion = 'Eliminar características técnicas de equipos' WHERE nombre = 'pc_caracteristicas_tecnicas.eliminar';

-- =============================================
-- LICENCIAS SOFTWARE PC
-- =============================================
UPDATE permisos SET descripcion = 'Registrar nuevas licencias de software' WHERE nombre = 'pc_licencia_software.crear';
UPDATE permisos SET descripcion = 'Actualizar licencias de software existentes' WHERE nombre = 'pc_licencia_software.actualizar';
UPDATE permisos SET descripcion = 'Eliminar licencias de software' WHERE nombre = 'pc_licencia_software.eliminar';

-- =============================================
-- MANTENIMIENTO PC
-- =============================================
UPDATE permisos SET descripcion = 'Registrar nuevos mantenimientos de equipos' WHERE nombre = 'pc_mantenimiento.crear';
UPDATE permisos SET descripcion = 'Actualizar registros de mantenimiento de equipos' WHERE nombre = 'pc_mantenimiento.actualizar';
UPDATE permisos SET descripcion = 'Eliminar registros de mantenimiento de equipos' WHERE nombre = 'pc_mantenimiento.eliminar';

-- =============================================
-- ENTREGAS PC
-- =============================================
UPDATE permisos SET descripcion = 'Registrar nuevas entregas de equipos de cómputo' WHERE nombre = 'pc_entrega.crear';
UPDATE permisos SET descripcion = 'Actualizar entregas de equipos de cómputo' WHERE nombre = 'pc_entrega.actualizar';
UPDATE permisos SET descripcion = 'Eliminar entregas de equipos de cómputo' WHERE nombre = 'pc_entrega.eliminar';

-- =============================================
-- PERIFÉRICOS ENTREGADOS PC
-- =============================================
UPDATE permisos SET descripcion = 'Registrar periféricos entregados con equipos' WHERE nombre = 'pc_periferico_entregado.crear';
UPDATE permisos SET descripcion = 'Actualizar periféricos entregados' WHERE nombre = 'pc_periferico_entregado.actualizar';
UPDATE permisos SET descripcion = 'Eliminar periféricos entregados' WHERE nombre = 'pc_periferico_entregado.eliminar';

-- =============================================
-- DEVOLUCIONES PC
-- =============================================
UPDATE permisos SET descripcion = 'Registrar nuevas devoluciones de equipos' WHERE nombre = 'pc_devuelto.crear';
UPDATE permisos SET descripcion = 'Actualizar devoluciones de equipos' WHERE nombre = 'pc_devuelto.actualizar';
UPDATE permisos SET descripcion = 'Eliminar devoluciones de equipos' WHERE nombre = 'pc_devuelto.eliminar';

-- =============================================
-- CONFIGURACIÓN CRONOGRAMA PC
-- =============================================
UPDATE permisos SET descripcion = 'Crear configuración de cronograma de mantenimiento' WHERE nombre = 'pc_config_cronograma.crear';
UPDATE permisos SET descripcion = 'Actualizar configuración de cronograma de mantenimiento' WHERE nombre = 'pc_config_cronograma.actualizar';
UPDATE permisos SET descripcion = 'Eliminar configuración de cronograma de mantenimiento' WHERE nombre = 'pc_config_cronograma.eliminar';

-- =============================================
-- MANTENIMIENTOS (GENERALES)
-- =============================================
UPDATE permisos SET descripcion = 'Listar todos los mantenimientos del sistema' WHERE nombre = 'mantenimiento.listar';
UPDATE permisos SET descripcion = 'Crear nuevas solicitudes de mantenimiento' WHERE nombre = 'mantenimiento.crear';
UPDATE permisos SET descripcion = 'Actualizar solicitudes de mantenimiento' WHERE nombre = 'mantenimiento.actualizar';
UPDATE permisos SET descripcion = 'Eliminar solicitudes de mantenimiento' WHERE nombre = 'mantenimiento.eliminar';
UPDATE permisos SET descripcion = 'Marcar mantenimientos como revisados' WHERE nombre = 'mantenimiento.marcar_revisado';
UPDATE permisos SET descripcion = 'Recibir y gestionar mantenimientos como receptor' WHERE nombre = 'mantenimiento.receptor';
UPDATE permisos SET descripcion = 'Ver y gestionar mantenimientos asignados' WHERE nombre = 'mantenimiento.asignado';

-- =============================================
-- AGENDA MANTENIMIENTO
-- =============================================
UPDATE permisos SET descripcion = 'Listar la agenda de mantenimientos programados' WHERE nombre = 'agenda_mantenimiento.listar';
UPDATE permisos SET descripcion = 'Crear eventos en la agenda de mantenimientos' WHERE nombre = 'agenda_mantenimiento.crear';
UPDATE permisos SET descripcion = 'Actualizar eventos de la agenda de mantenimientos' WHERE nombre = 'agenda_mantenimiento.actualizar';
UPDATE permisos SET descripcion = 'Eliminar eventos de la agenda de mantenimientos' WHERE nombre = 'agenda_mantenimiento.eliminar';

-- =============================================
-- PEDIDOS DE COMPRA
-- =============================================
UPDATE permisos SET descripcion = 'Listar todos los pedidos de compra' WHERE nombre = 'cp_pedido.listar';
UPDATE permisos SET descripcion = 'Listar pedidos de compra del área de compras' WHERE nombre = 'cp_pedido.listar.compras';
UPDATE permisos SET descripcion = 'Listar pedidos de compra como responsable' WHERE nombre = 'cp_pedido.listar.responsable';
UPDATE permisos SET descripcion = 'Crear nuevos pedidos de compra' WHERE nombre = 'cp_pedido.crear';
UPDATE permisos SET descripcion = 'Editar pedidos de compra existentes' WHERE nombre = 'cp_pedido.editar';
UPDATE permisos SET descripcion = 'Actualizar información de pedidos de compra' WHERE nombre = 'cp_pedido.actualizar';
UPDATE permisos SET descripcion = 'Actualizar ítems dentro de un pedido de compra' WHERE nombre = 'cp_pedido.actualizar_items';
UPDATE permisos SET descripcion = 'Ver detalle completo de un pedido de compra' WHERE nombre = 'cp_pedido.ver';
UPDATE permisos SET descripcion = 'Eliminar pedidos de compra' WHERE nombre = 'cp_pedido.eliminar';
UPDATE permisos SET descripcion = 'Aprobar pedidos de compra desde gerencia' WHERE nombre = 'cp_pedido.aprobar_gerencia';
UPDATE permisos SET descripcion = 'Rechazar pedidos de compra desde gerencia' WHERE nombre = 'cp_pedido.rechazar_gerencia';
UPDATE permisos SET descripcion = 'Aprobar pedidos de compra desde el área de compras' WHERE nombre = 'cp_pedido.aprobar_compras';
UPDATE permisos SET descripcion = 'Rechazar pedidos de compra desde el área de compras' WHERE nombre = 'cp_pedido.rechazar_compras';

-- =============================================
-- PRODUCTOS (COMPRAS)
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevos productos en el catálogo de compras' WHERE nombre = 'cp_producto.crear';
UPDATE permisos SET descripcion = 'Actualizar productos del catálogo de compras' WHERE nombre = 'cp_producto.actualizar';
UPDATE permisos SET descripcion = 'Eliminar productos del catálogo de compras' WHERE nombre = 'cp_producto.eliminar';

-- =============================================
-- PROVEEDORES
-- =============================================
UPDATE permisos SET descripcion = 'Registrar nuevos proveedores' WHERE nombre = 'cp_proveedor.crear';
UPDATE permisos SET descripcion = 'Actualizar información de proveedores' WHERE nombre = 'cp_proveedor.actualizar';
UPDATE permisos SET descripcion = 'Eliminar proveedores del sistema' WHERE nombre = 'cp_proveedor.eliminar';

-- =============================================
-- CENTRO DE COSTOS
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevos centros de costos' WHERE nombre = 'cp_centro_costo.crear';
UPDATE permisos SET descripcion = 'Actualizar centros de costos existentes' WHERE nombre = 'cp_centro_costo.actualizar';
UPDATE permisos SET descripcion = 'Eliminar centros de costos' WHERE nombre = 'cp_centro_costo.eliminar';

-- =============================================
-- DEPENDENCIAS (COMPRAS)
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevas dependencias de compras' WHERE nombre = 'cp_dependencia.crear';
UPDATE permisos SET descripcion = 'Actualizar dependencias de compras' WHERE nombre = 'cp_dependencia.actualizar';
UPDATE permisos SET descripcion = 'Eliminar dependencias de compras' WHERE nombre = 'cp_dependencia.eliminar';

-- =============================================
-- TIPOS DE SOLICITUD
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevos tipos de solicitud de compra' WHERE nombre = 'cp_tipo_solicitud.crear';
UPDATE permisos SET descripcion = 'Actualizar tipos de solicitud de compra' WHERE nombre = 'cp_tipo_solicitud.actualizar';
UPDATE permisos SET descripcion = 'Eliminar tipos de solicitud de compra' WHERE nombre = 'cp_tipo_solicitud.eliminar';

-- =============================================
-- PRODUCTOS/SERVICIOS
-- =============================================
UPDATE permisos SET descripcion = 'Crear nuevos productos o servicios' WHERE nombre = 'cp_producto_servicio.crear';
UPDATE permisos SET descripcion = 'Actualizar productos o servicios existentes' WHERE nombre = 'cp_producto_servicio.actualizar';
UPDATE permisos SET descripcion = 'Eliminar productos o servicios' WHERE nombre = 'cp_producto_servicio.eliminar';

-- =============================================
-- ENTREGA ACTIVOS FIJOS
-- =============================================
UPDATE permisos SET descripcion = 'Registrar nuevas entregas de activos fijos' WHERE nombre = 'cp_entrega_activos_fijos.crear';
UPDATE permisos SET descripcion = 'Actualizar entregas de activos fijos' WHERE nombre = 'cp_entrega_activos_fijos.actualizar';
UPDATE permisos SET descripcion = 'Eliminar entregas de activos fijos' WHERE nombre = 'cp_entrega_activos_fijos.eliminar';

-- =============================================
-- CONFIGURACIÓN DASHBOARDS
-- =============================================
UPDATE permisos SET descripcion = 'Acceso al dashboard de administración general' WHERE nombre = 'configuracion.dashboard_administrador';
UPDATE permisos SET descripcion = 'Acceso al dashboard de gestión de sistemas' WHERE nombre = 'configuracion.dashboard_sistemas';
UPDATE permisos SET descripcion = 'Acceso al dashboard de gestión de compras' WHERE nombre = 'configuracion.dashboard_compras';
UPDATE permisos SET descripcion = 'Acceso al dashboard de mantenimiento' WHERE nombre = 'configuracion.dashboard_mantenimiento';
```
