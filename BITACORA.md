# Bitacora tecnica

Fecha: 2026-02-04

Objetivo general: separar direccion en calle + altura, persistir correctamente provincia/ciudad/direccion, mejorar el flujo de guardado y la experiencia del predictivo de calles, y asegurar que el dashboard muestre datos aunque el edit este pendiente.

Resumen de cambios
- Se agregaron campos `address_street` y `address_number` al modelo `profiles` y se continua manteniendo `address` combinado por compatibilidad.
- Se corrigio el flujo de guardado para persistir ubicacion en `profiles` al enviar el formulario (ademas de seguir dejando el payload en `edits`).
- Se ajusto el dashboard del profesional para restaurar provincia/ciudad por nombre cuando faltan IDs y para mostrar datos aunque haya edicion pendiente.
- Se sumo precarga de calles por ciudad con spinner para acelerar el predictivo.
- Se agregaron endpoints y logica de cache para mejorar la performance de sugerencias.

Base de datos / migraciones
- Se agrego la migracion `database/migrations/2026_02_04_000000_add_address_parts_to_profiles_table.php` con columnas `address_street` (string 180, nullable) y `address_number` (string 50, nullable) en `profiles`.
- `app/Models/Profile.php` se actualizo para incluir `address_street` y `address_number` en `$fillable`.

Backend (controladores)
- `app/Http/Controllers/ProviderProfileController.php`
- Validacion: se incorporaron `address_street` y `address_number` en el `validate()` del form.
- Normalizacion de direccion: si hay `address_street`/`address_number` se compone `address`; si no, se intenta parsear `address` para separar calle/altura.
- Payload: se agrega `address_street`/`address_number` al payload enviado a `edits`.
- Persistencia inmediata: se guarda ubicacion directamente en `profiles` al enviar el formulario, para que quede reflejada en la base aunque el edit siga pendiente.
- Compatibilidad: se setean `state` y `city` desde `province_name` y `city_name` cuando hay datos.

- `app/Http/Controllers/AdminEditController.php`
- Se incluyen `address_street` y `address_number` en el set de actualizacion al aprobar edits.
- Se elimino el borrado automatico de ubicacion cuando el perfil no es presencial (para no perder datos de provincia/ciudad/direccion en modo remoto).

- `app/Http/Controllers/GeoRefController.php`
- Nuevo endpoint `streetPreload` para precalentar cache del bbox de la ciudad.
- Mantiene logica de cache para provincias/ciudades y sugerencias.

Rutas
- `routes/web.php` agrega ruta `GET /geo/street-preload` para precarga de bbox/calles.

Frontend (dashboard de profesional)
- `resources/views/dashboard/profile_edit.blade.php`
- Se separan inputs de direccion: `address_street` y `address_number`, manteniendo `address` hidden combinado.
- Se agrega spinner de precarga (`Cargando calles...`) y bloqueo temporal del input de calle durante la precarga.
- La cascada de provincia/ciudad:
- Restaura valores por ID y tambien por nombre (`province_name`/`city_name`) si el ID no esta disponible.
- Normaliza texto (sin tildes) para matching confiable.
- Carga selects incluso en modo bloqueado para que el usuario vea los valores guardados.
- Dispara precarga de calles al seleccionar ciudad.

Notas operativas
- Para verificar guardado en base (SQLite local), usar Tinker y consultar `profiles` con `address_street`, `address_number`, `province_name`, `city_name`.
- Si se requiere backfill historico, se puede crear un script/migracion adicional para parsear `address` y completar `address_street`/`address_number` en perfiles existentes.

Fecha: 2026-02-05

Objetivo: Integracion de herramientas de tracking y preparacion para produccion.

Resumen de cambios:
- Configuracion de variables de entorno `GTM_ID` y `FACEBOOK_PIXEL_ID` en `render.yaml`.
- Ajuste de seguridad en `render.yaml` estableciendo `APP_DEBUG` en `false`.
- Documentacion de la infraestructura de tracking iniciada.
