# Proyecto — documentación específica

> **Qué es este archivo.** Documenta **este proyecto en particular**: qué módulos tiene, qué decisiones propias tomó y cómo se despliega. Se completa/edita en cada fork.
> `AGENTS.md` contiene las convenciones **genéricas** del stack (iguales en todos los proyectos): no hace falta tocarlo acá.
> OpenCode carga este archivo automáticamente porque está listado en `opencode.jsonc` (`instructions`).
> Si una decisión de este proyecto contradice a `AGENTS.md`, gana este archivo (se carga después).

## Identidad

- **Nombre:** ms-matafuegos
- **Descripción:** backend PHP 7.4 + Slim 4 + DDD liviano para gestionar catálogo de matafuegos, categorías, proveedores, multimedia, clientes, presupuestos y facturas.
- **Estado:** API funcional y migrada; sitio público en Vue 3 + Tailwind y panel de gestión en Vue 3 + PrimeVue.
- **Origen:** fork de `php-ddd-base`, conservando el namespace `Gaby\` y el paquete `gaby/php-ddd-base` por ahora.
- **Repositorio:** local (sin URL pública definida).

## Stack

- **PHP:** objetivo 7.4 (`config.platform` en `composer.json`); probado localmente en PHP 8.5.
- **Base de datos:** SQLite (`storage/database.sqlite`), migrada con Phinx.
- **API:** Slim 4 + PHP-DI 6 + JWT HS256.
- **Frontends:** sitio público en `site/` (Vue 3 + Vue Router + Tailwind 4, compilado a `public/`) y panel en `admin/` (Vue 3 + Vite + PrimeVue 4, compilado a `public/admin/`).
- **Hosting / deploy:** `php -S localhost:9981 -t public public/router.php` para desarrollo; Apache con `public/.htaccess` para producción.

## Desarrollo

- **API:** `php -S localhost:9981 -t public public/router.php`. También funciona `php -S localhost:9981 -t public`: `public/index.php` detecta el servidor embebido y sirve la SPA para las rutas que no son `/api`.
- **Sitio público:** `cd site && npm run dev` (Vite en `http://localhost:5174`, con proxy a `/api` y `/media`). Build: `cd site && npm run build` (genera `public/index.html`, `public/favicon.svg`, `public/robots.txt` y `public/site-assets/`).
- **Panel:** `cd admin && npm run dev` (Vite en `http://localhost:5173`, base `/admin/`). Build: `cd admin && npm run build` (genera `public/admin/`).
- **Prueba del build completo:** `http://localhost:9981/` (sitio), `http://localhost:9981/admin/` (panel) y `http://localhost:9981/api/ping` (API).

## Módulos

| Módulo | Ruta base | Entidad principal | Estado |
| --- | --- | --- | --- |
| `Auth` | `POST /api/auth/login` | – | listo (JWT HS256) |
| `Admin` | – | `Admin` | listo (repositorio + seed) |
| `Page` | `/api/pages` (protegido) y `/api/public/pages/{slug}` | `Page` | heredado de la base (el panel ya no muestra la sección Páginas) |
| `Inquiry` | `POST /api/inquiries` y `GET /api/inquiries` (protegido) | `Inquiry` | listo (alta pública con WhatsApp + bandeja protegida) |
| `Category` | `/api/categories` (protegido) y `/api/public/categories` | `Category` | listo (CRUD + lectura pública) |
| `Provider` | `/api/providers` (protegido) | `Provider` | listo (CRUD) |
| `Product` | `/api/products` (protegido) y `/api/public/products` | `Product` | listo (CRUD, stock, precios públicos, destacados) |
| `Media` | `/api/products/{id}/media` | `Media` | listo (imágenes/videos polimórficos) |
| `Customer` | `/api/customers` (protegido) | `Customer` | listo (CRUD + protección por referencias) |
| `Quote` | `/api/quotes` (protegido) | `Quote` | listo (CRUD + estados + totales) |
| `Invoice` | `/api/invoices` (protegido) | `Invoice` | listo (CRUD + estados + stock + desde presupuesto) |
| `Maintenance` | `/api/maintenance` (protegido) | `MaintenanceReminder` | listo (agenda semanal/mensual + aviso por WhatsApp) |
| `Migration` | `/api/migrations` (protegido) | – | listo (estado y ejecución de Phinx desde el panel) |

Rutas de salud: `GET /api/ping`.

## Autenticación

- `POST /api/auth/login` recibe `{ email, password }` y devuelve `{ token, admin }`.
- Token JWT HS256 firmado con `JWT_SECRET`, con `sub` = id del admin y vencimiento `JWT_TTL`.
- `JwtAuthMiddleware` protege todas las rutas de gestión (categorías, proveedores, productos, clientes, presupuestos, facturas y multimedia de escritura).
- Las lecturas públicas no requieren token: `/api/public/products`, `/api/public/products/{slug}`, `/api/public/categories`, `/api/public/pages/{slug}` y `GET /api/products/{id}/media`.
- El panel (`admin/`) guarda el token en `localStorage` (`cms_token`) y lo manda como `Authorization: Bearer …`; también guarda el admin en `cms_admin`.
- Usuario semilla: `admin@msmatafuegos.com` / `Admin1234`.

## API

### Consultas

- `POST /api/inquiries` (público): `{ name, email, phone, message }`. `phone` es el WhatsApp y es obligatorio; se valida que tenga entre 8 y 15 dígitos.
- `GET /api/inquiries` (protegido): bandeja de consultas con `phone` y `createdAt`.

### Categorías

- `GET /api/public/categories`: categorías activas para el sitio público.
- `GET /api/categories` (protegido): listado completo.
- `GET /api/categories/{id}` (protegido).
- `POST /api/categories` (protegido): `{ name, slug?, description?, status? }`.
- `PUT /api/categories/{id}` (protegido).
- `DELETE /api/categories/{id}` (protegido): los productos que la referencian quedan con `categoryId = null`.

### Proveedores

- `GET /api/providers` (protegido), admite `?q=`.
- `GET /api/providers/{id}` (protegido).
- `POST /api/providers` (protegido): `{ name, taxId?, contactName?, email?, phone?, address?, notes?, status? }`.
- `PUT /api/providers/{id}` (protegido).
- `DELETE /api/providers/{id}` (protegido): los productos que lo referencian quedan con `providerId = null`.

### Productos

- `GET /api/public/products`: solo activos; admite `?featured=1`, `?categoryId=...`, `?q=...`. Si `showPrice = false`, `price` sale `null`.
- `GET /api/public/products/{slug}`: detalle público; incluye categoría y multimedia, oculta el precio cuando corresponde.
- `GET /api/products` (protegido): admite `?active=1`, `?featured=1`, `?categoryId=...`, `?q=...`.
- `GET /api/products/low-stock` (protegido): productos con `stock <= minStock`.
- `GET /api/products/{id}` (protegido): incluye categoría, proveedor, stock, `minStock`, `lowStock` y multimedia.
- `POST /api/products` (protegido): `{ name, slug?, description?, sku?, categoryId?, providerId?, price?, showPrice?, stock?, minStock?, featured?, status? }`.
- `PUT /api/products/{id}` (protegido).
- `PATCH /api/products/{id}/stock` (protegido): `{ delta: -2 }` ajusta el stock de forma atómica; si quedaría negativo responde `409`.
- `DELETE /api/products/{id}` (protegido): elimina el producto y limpia su multimedia. Las líneas de presupuestos/facturas conservan la descripción y quedan con `productId = null`.

### Multimedia

- `GET /api/products/{id}/media` (público): lista la multimedia del producto.
- `POST /api/products/{id}/media` (protegido): `multipart/form-data` con `file` y `position?`.
- `PUT /api/products/{id}/media/{mediaId}` (protegido): `{ position }`.
- `DELETE /api/products/{id}/media/{mediaId}` (protegido): borra la fila y el archivo.
- Formatos permitidos: JPEG, PNG, WebP, GIF, MP4, WebM y MOV. Límite: 10 MB. El tipo (`image`/`video`) se infiere del MIME real.
- Los archivos se guardan en `public/media/products/{productId}/{uuid}.{ext}`. Al borrar el producto se eliminan sus archivos y registros.

### Clientes

- `GET /api/customers` (protegido), admite `?q=`.
- `GET /api/customers/{id}` (protegido).
- `POST /api/customers` (protegido): `{ name, taxId?, email?, phone?, address?, city?, notes?, status? }`.
- `PUT /api/customers/{id}` (protegido).
- `DELETE /api/customers/{id}` (protegido): responde `409` si el cliente tiene presupuestos o facturas. La identificación fiscal (`taxId`) es única cuando se informa.

### Presupuestos

- `GET /api/quotes` (protegido): filtros `?status=`, `?customerId=`, `?from=`, `?to=`.
- `GET /api/quotes/{id}` (protegido).
- `POST /api/quotes` (protegido).
- `PUT /api/quotes/{id}` (protegido): no permite editar un presupuesto cancelado.
- `PATCH /api/quotes/{id}/status` (protegido): `{ status }`.
- `DELETE /api/quotes/{id}` (protegido).
- Estados: `draft`, `sent`, `accepted`, `rejected`, `cancelled`.
- Numeración secuencial: `PRE-000001`, `PRE-000002`, …
- Los presupuestos **no descuentan stock**.

Payload de alta/edición:

```json
{
  "customerId": "uuid-del-cliente",
  "issueDate": "2026-10-21",
  "validUntil": "2026-11-20",
  "notes": "Presupuesto de prueba",
  "status": "draft",
  "lines": [
    { "productId": "uuid-del-producto", "quantity": 2 },
    { "description": "Servicio de recarga", "quantity": 1, "unitPrice": 8000 }
  ],
  "adjustments": [
    { "description": "Costo de envío", "kind": "surcharge", "valueType": "fixed", "value": 3000 },
    { "description": "Descuento pago efectivo", "kind": "discount", "valueType": "percentage", "value": 5 }
  ]
}
```

- En una línea con `productId`, la descripción y el precio unitario se toman del producto si no se envían.
- En una línea libre (`sin productId`) `description` y `unitPrice` son obligatorios.
- En un ajuste `fixed`, `value` es un importe decimal; en `percentage`, `value` es un porcentaje de `0` a `100`.
- Los porcentajes se calculan sobre el subtotal de las líneas, sin composición entre ajustes.

### Facturas

- `GET /api/invoices` (protegido): filtros `?status=`, `?customerId=`, `?quoteId=`, `?from=`, `?to=`.
- `GET /api/invoices/{id}` (protegido).
- `POST /api/invoices` (protegido): crea una factura en `draft`.
- `PUT /api/invoices/{id}` (protegido): solo si está en `draft`.
- `PATCH /api/invoices/{id}/status` (protegido): `{ status }`.
- `DELETE /api/invoices/{id}` (protegido): solo si está en `draft`.
- `POST /api/invoices/from-quote/{quoteId}` (protegido): copia líneas y ajustes del presupuesto, crea la factura en `draft` y marca el presupuesto como `accepted`. Un presupuesto solo puede facturarse una vez.
- Estados: `draft → issued → paid`, y `draft|issued|paid → cancelled`.
- Numeración secuencial: `FAC-000001`, `FAC-000002`, …
- Al pasar a `issued` se descuenta el stock de los productos facturados; `paid` no vuelve a descontarlo. Al cancelar una factura emitida/paga se repone el stock. Si no alcanza el stock, la emisión responde `409` y no cambia nada.

Payload de alta directa: igual al de presupuesto, reemplazando `validUntil` por `dueDate` y agregando `paymentMethod?`:

```json
{
  "customerId": "uuid-del-cliente",
  "issueDate": "2026-10-21",
  "dueDate": "2026-11-20",
  "paymentMethod": "cash",
  "notes": "Factura de prueba",
  "lines": [
    { "productId": "uuid-del-producto", "quantity": 2 }
  ],
  "adjustments": []
}
```

### Mantenimientos

- Los avisos **no se guardan**: se derivan de la última factura **pagada** de cada combinación cliente + producto cuyo producto tenga `requiresMaintenance = true`. La fecha de aviso es `paid_at + maintenanceDays` (365 por defecto).
- `GET /api/maintenance/calendar?from=YYYY-MM-DD&to=YYYY-MM-DD` (protegido): devuelve `{ from, to, today, items, overdue }`. Sin parámetros usa la semana actual (lunes a domingo); el rango máximo es de 120 días. `items` incluye los avisos (también los ya avisados, con su estado) cuya fecha de aviso cae en el rango; `overdue` lista los pendientes con fecha anterior a hoy.
- `POST /api/maintenance/{id}/notify` (protegido): marca el aviso como avisado (`notified_at = hoy`) y responde `204`. Es idempotente.
- `POST /api/maintenance/{id}/snooze` (protegido): `{ days: 7 }` pospone el aviso desde hoy (o desde su fecha de aviso si es futura) y responde `204`.
- El `id` del aviso es el `invoice_line_id` de la última factura pagada; si el cliente vuelve a comprar el mismo producto, el aviso anterior queda reemplazado por el nuevo.
- Estados: `pending` (fecha de aviso futura o de hoy), `overdue` (fecha pasada sin avisar) y `notified` (ya avisado).
- El panel muestra la semana en el tablero y la agenda mensual en **Ventas > Mantenimientos**. El envío es manual: abre `wa.me` con una plantilla fija que recuerda la compra/service, la fecha y el producto; no hay envío automático por API.

### Migraciones

- `GET /api/migrations/status` (protegido): devuelve el entorno, la base, el total de migraciones, las aplicadas, las pendientes y el detalle de cada archivo (`version`, `name`, `status`, `appliedAt`).
- `POST /api/migrations/run` (protegido): ejecuta solo las migraciones pendientes con Phinx y devuelve `{ applied, count, output, status }`.
- El panel las muestra en `Admin > Migraciones` con un botón de confirmación. No hay endpoint de rollback: la API solo aplica migraciones hacia adelante.
- Un lock en `storage/migrations.lock` evita dos ejecuciones simultáneas; si está ocupado, responde `409`.
- Si una migración falla, responde `500` con `detail` y `output` para poder corregirla desde el panel.

## Base de datos y migraciones propias

- `categories`: `id` (UUID), `name`, `slug` único, `description`, `status`, timestamps.
- `providers`: `id` (UUID), `name`, `tax_id`, `contact_name`, `email`, `phone`, `address`, `notes`, `status`, timestamps.
- `products`: `id` (UUID), `name`, `slug` único, `description`, `sku` único, `category_id`, `provider_id`, `price_cents`, `show_price`, `stock`, `min_stock`, `featured`, `requires_maintenance`, `maintenance_days` (365 por defecto), `status`, timestamps.
- `media`: `id` (UUID), `owner_type`, `owner_id`, `type`, `path`, `original_filename`, `mime_type`, `position`, `created_at` (tabla polimórfica, sin FK).
- `inquiries`: `id` (UUID), `name`, `email`, `phone` (WhatsApp), `message`, `created_at`.
- `customers`: `id` (UUID), `name`, `tax_id` único, `email`, `phone`, `address`, `city`, `notes`, `status`, timestamps.
- `quotes`: `id` (UUID), `sequence` único, `number` único, `customer_id`, `issue_date`, `valid_until`, `status`, `notes`, `subtotal_cents`, `adjustments_total_cents`, `total_cents`, timestamps.
- `quote_lines`: `id`, `quote_id`, `product_id`, `description`, `quantity`, `unit_price_cents`, `line_total_cents`, `position`.
- `quote_adjustments`: `id`, `quote_id`, `description`, `kind`, `value_type`, `value`, `amount_cents`, `position`.
- `invoices`: `id`, `sequence` único, `number` único, `customer_id`, `quote_id`, `issue_date`, `due_date`, `paid_at`, `status`, `payment_method`, `notes`, `subtotal_cents`, `adjustments_total_cents`, `total_cents`, timestamps.
- `invoice_lines` e `invoice_adjustments`: misma estructura que sus equivalentes de presupuesto.
- `maintenance_notices`: `invoice_line_id` (PK, FK a `invoice_lines` con `CASCADE`), `notified_at`, `snoozed_until`, `notes`, timestamps. Guarda sólo el acuse de cada aviso; el aviso en sí se deriva de las facturas pagadas.
- FKs: clientes con `RESTRICT`; categorías/proveedores/productos con `SET_NULL`; líneas hijas y acuses con `CASCADE`.
- `phinxlog`: historial de migraciones aplicadas que usa Phinx.
- Semillas heredadas: un admin de desarrollo y las páginas `home`, `about`, `contact`.
- Comandos: `vendor/bin/phinx migrate`, `vendor/bin/phinx rollback`, `vendor/bin/phinx status`.
- En el hosting sin terminal, las pendientes se ejecutan desde `Admin > Migraciones`; el botón usa la misma configuración de `phinx.php` y la base de `storage/database.sqlite`.

## Decisiones y patrones propios

- **Dinero en centavos:** la persistencia y los cálculos usan enteros (`price_cents`, `*_cents`). La API expone decimales con dos posiciones y acepta formatos como `1234.56`, `1234,56` o `$ 1.234,56`.
- **Ajustes:** `kind` es `surcharge` o `discount`; `valueType` es `fixed` o `percentage`. Los porcentajes se guardan en puntos básicos (`5% = 500`) y se calculan sobre el subtotal de las líneas, sin composición.
- **Stock:** los presupuestos no lo tocan. Las facturas lo descuentan al emitirse y lo reponen al cancelarse. `adjustStock()` del repositorio es atómico y rechaza stock negativo.
- **Estados de factura:** `draft` es editable y borrable; `issued`/`paid` ya aplicaron stock; `cancelled` es final.
- **Una factura por presupuesto:** `POST /api/invoices/from-quote/{quoteId}` rechaza un presupuesto ya facturado.
- **Clientes referenciados:** no se puede borrar un cliente con presupuestos o facturas; la FK es `RESTRICT` y el handler devuelve `409`.
- **Precio público opcional:** `showPrice = false` oculta `price` en las respuestas públicas, pero el panel sigue viendo el importe.
- **Stock mínimo:** `stock <= minStock` marca `lowStock`; `GET /api/products/low-stock` devuelve los productos a reponer.
- **Multimedia polimórfica:** `owner_type` + `owner_id` sin FK; el borrado del dueño dispara `DeleteOwnerMediaHandler`.
- **Slug:** se normaliza con `Gaby\Shared\Domain\Slug` (`strtolower`, espacios → `-`, solo `[a-z0-9-]`).
- **Namespace pendiente:** se mantiene `Gaby\` y `gaby/php-ddd-base`; el rename a `MsMatafuegos\` queda para una tarea aparte.
- **Sin IVA:** no se calcula IVA ni impuestos; los totales son subtotal + ajustes. Es una decisión explícita de esta etapa.
- **Migraciones desde el panel:** el módulo `Migration` envuelve Phinx (`Config` + `Manager`) y solo ejecuta hacia adelante. Los endpoints están protegidos con JWT, el panel pide confirmación y el lock de `storage/` evita ejecuciones simultáneas. Está pensado para hostings sin SSH; no reemplaza los comandos de Phinx en desarrollo.
- **Sitio público SPA:** vive en `site/` (Vue 3 + Vue Router + Tailwind 4) y se compila a `public/` con `assetsDir: site-assets` y `emptyOutDir: false` para no pisar `public/admin/` ni `public/media/`. Rutas: `/`, `/productos`, `/productos/:slug`, `/consultas` y `/contacto`; usa el API público real y no el módulo `Page`. `site/clean.mjs` borra solo el build anterior del sitio antes de compilar.
- **Vista previa al compartir el link:** `site/index.html` define `description`, `canonical`, Open Graph (`og:title`, `og:description`, `og:image` 1200×630, `og:url`) y Twitter Card `summary_large_image`; la imagen es `public/brand/og-image.png` (logo + "Recarga, venta y mantenimiento de matafuegos" + "Servicio a domicilio en San Miguel de Tucumán"). Se regenera con `python scripts/generate-og-image.py` (Pillow) a partir de `site/src/assets/ms-matafuegos-light.png` y Vite la copia desde `site/public/brand/`. Las URLs de `og:url`/`og:image` son absolutas y apuntan a `https://msmatafuegos.infy.click/`: si cambia el dominio hay que actualizarlas en `site/index.html`.
- **Panel de gestión SPA:** vive en `admin/` (Vue 3 + PrimeVue 4.5 + `@primeuix/themes`) con rutas hash (`#/dashboard`, `#/products`, …), `base: /admin/` y build a `public/admin/`. Usa el tema PrimeVue y la paleta negro/gris/azul; las acciones de tabla son sólo íconos y cada listado tiene búsqueda, alta/edición en modal, confirmación de borrado y toast de guardado.
- **Alta de cliente desde presupuestos/facturas:** el listado y el formulario de documentos tienen un botón **Nuevo cliente** que abre `CustomerCreateDialog.vue` sobre la pantalla; al guardar, el cliente queda seleccionado en el documento (y sumado a la lista de clientes del panel) sin salir del flujo.
- **PDF de presupuestos y facturas:** el botón de PDF de cada fila pide el detalle (`GET /api/quotes/{id}` o `/api/invoices/{id}`) y el cliente completo, y genera el archivo en el navegador con `jsPDF` (carga diferida, `admin/src/pdf.js`). No requiere backend ni vendor nuevo. El diseño usa el logo para fondos claros (`ms-matafuegos-print.png`), encabezado con número/fechas/forma de pago, datos del cliente, tabla de líneas y ajustes, totales y notas; pagina solo si hace falta. En facturas aclara "Comprobante no fiscal" porque no se discrimina IVA.
- **Sección Páginas quitada del panel:** el sitio público no usa el módulo `Page`, así que se sacó del menú, de los recursos y del resumen del tablero. La API (`/api/pages`) y la tabla `pages` siguen existiendo para no romper nada; el panel ya no las consume.
- **Logo de marca:** se usan los assets reales `site/src/assets/ms-matafuegos.png` (fondos claros) y `site/src/assets/ms-matafuegos-light.png` (fondos oscuros). El sitio los consume desde `BrandLogo.vue` y el panel desde el sidebar y el login; no se recrea el logo con SVG ni texto.
- **Un solo fallback para el sitio:** `public/.htaccess` manda `/api/*` a `index.php`, conserva `/admin/` y manda el resto de las rutas sin archivo a `index.html`. En desarrollo, `public/router.php` hace lo mismo para el servidor embebido y `public/index.php` además sirve la SPA si se arranca sin router.
- **Legacy Site eliminado:** se borraron `src/Apps/Site` y `public/assets/site.*`; el módulo `Page` sigue existiendo sólo para la API, no lo usa el sitio público ni el panel.
- **WhatsApp de consultas:** el campo `phone` de `inquiries` es obligatorio en el alta pública y se valida con 8 a 15 dígitos. El sitio arma el enlace `https://wa.me/5493815977614`; el panel muestra el WhatsApp y permite responder por ese medio.
- **Imágenes de demo del catálogo:** cada producto tiene 3 imágenes cargadas por la API de Media (21 en total). Son ilustrativas, provienen de Wikimedia Commons y WordPress Photo Directory y se normalizaron a JPEG. Varias usan licencias CC BY/CC BY-SA, así que antes de publicar hay que reemplazarlas por fotos propias o mostrar la atribución de `IMAGE_CREDITS.md`.
- **Mantenimientos programados:** al marcar una factura como `paid` se guarda `paid_at` (fecha del día). Un producto con `requiresMaintenance` define cada cuántos días avisar (`maintenanceDays`, 365 por defecto). El aviso se calcula al vuelo como la última factura pagada de cada cliente + producto; no hay tabla de avisos, sólo `maintenance_notices` para registrar `notified_at` / `snoozed_until`. Los presupuestos y las facturas no pagadas no generan avisos. El sistema arranca sin datos históricos: sólo cuentan las facturas pagadas después de esta versión. La agenda semanal se ve en el tablero y la mensual en **Ventas > Mantenimientos**; el mensaje de WhatsApp es una plantilla fija y el envío es manual.

## Tests

- Suite Pest en `src/tests/` (34 tests al cierre de esta etapa).
- Cubre: `Money`, precio público oculto, stock mínimo, ajuste de stock, totales y ajustes de presupuestos, estados de factura, `paid_at`, transición de stock al emitir/cancelar, rechazo por stock insuficiente, cálculo de vencimientos y estados de mantenimiento, agenda por rango, marcar avisado/posponer y handlers del módulo `Migration` con un runner en memoria.
- Correr: `vendor/bin/pest`.
- Los doubles viven en `Doubles/` dentro del módulo del test.

## Deploy

1. `cd site && npm ci && npm run build` y `cd admin && npm ci && npm run build` (generan `public/index.html`, `public/site-assets/` y `public/admin/`).
2. `composer install --no-dev --optimize-autoloader` (con `--ignore-platform-req=ext-sodium` si hiciera falta).
3. Apuntar el document root a `public/` (o usar el `.htaccess` raíz descripto en `AGENTS.md`).
4. Generar `.env` con `JWT_SECRET` propio y `APP_DEBUG=false`.
5. Subir `storage/database.sqlite` ya migrado (o correr `phinx migrate` en el server).
6. Verificar que `public/media/` y `storage/` tengan permisos de escritura: el primero para uploads y el segundo para la base SQLite, el lock de migraciones y los archivos temporales.

### InfinityFree (sin SSH)

- Generar el paquete con `powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\package-infinityfree.ps1` (o `.\scripts\package-infinityfree.cmd`). Sale en `deploy/ms-matafuegos-infinityfree.zip` (~6,9 MB).
- El ZIP incluye `public/` compilado, `src/`, `vendor/` sin dependencias de desarrollo, `database/migrations/`, `storage/database.sqlite`, `composer.json/lock`, `phinx.php`, `IMAGE_CREDITS.md`, un `.env` de producción con `JWT_SECRET` nuevo y `APP_DEBUG=false`, y un `.htaccess` raíz que manda `/api` directo a `public/index.php` y el resto a `public/`.
- Subir **el contenido** del ZIP directamente a `/htdocs` (sin una carpeta extra), conservar el `.htaccess` raíz y borrar el ZIP del hosting después de descomprimirlo.
- Para actualizar sólo datos (base + imágenes), usar `powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\package-data.ps1` (o `.\scripts\package-data.cmd`): genera `deploy/ms-matafuegos-datos.zip` (~4,1 MB) con `storage/database.sqlite` y `public/media/`. Antes de sobrescribir la base del hosting, descargar una copia: el ZIP reemplaza la base completa y pierde consultas/presupuestos/facturas cargados allá.
- La base ya viaja migrada. Si subís migraciones nuevas, ejecutalas desde `Admin > Migraciones` (no hace falta SSH); el botón requiere que `vendor/` incluya Phinx y que `storage/` sea escribible. Probar `/`, `/admin/` y `/api/ping` (debe devolver JSON, no el HTML del sitio).

## Gotchas

- `lcobucci/jwt 4.3`: `ValidAt` recibe un `Lcobucci\Clock\Clock`, no un `DateTimeImmutable`; usar `new ValidAt(SystemClock::fromUTC())`.
- En el PHP local de esta máquina falta `ext-zip`: Composer cae a clonar cada paquete (lento). Correr `php -d extension=zip C:\composer\composer.phar install` o habilitar `extension=zip` en el `php.ini`.
- `php -S` no lee `.htaccess`: usar `php -S localhost:9981 -t public public/router.php` (recomendado) o `php -S localhost:9981 -t public`. En el segundo caso `public/index.php` detecta `cli-server` y sirve la SPA; si se ve el JSON `{"error":"Not found."}` es el 404 de Slim, señal de que el servidor no está usando el fallback o se está golpeando una ruta `/api` inexistente.
- Al probar con `curl` en Windows, mandar los JSON con tildes desde un archivo UTF-8 (`--data-binary @archivo.json`); la consola puede romper el body y producir un `422` engañoso.
- Si se cambian `definitions.php` o `routes.php`, reiniciar el `php -S` (`taskkill //F //IM php.exe`) para ver los cambios.
- `APP_DEBUG=true` muestra el detalle de las excepciones: apagarlo en producción.
- **InfinityFree / docroot fijo:** si el `.htaccess` raíz manda todo a `public/` y la API devuelve el HTML del sitio (por ejemplo `/api/ping` con `Content-Type: text/html`), agregar antes de la regla general `RewriteRule ^api(/.*)?$ public/index.php [QSA,L]`. Algunos hosts dejan `REQUEST_URI` con `/public/` delante y el fallback SPA captura `/api` antes de que `public/.htaccess` lo derive a Slim. El generador de ZIP ya incluye esa regla.
- **InfinityFree / login OK pero rutas protegidas 401:** si `/api/auth/login` devuelve token pero `/api/products` responde `401 {"error":"Missing bearer token."}`, el hosting no está pasando el header `Authorization` a PHP. Agregar al `.htaccess` raíz o de `public/` `RewriteCond %{HTTP:Authorization} .` + `RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]`; `public/index.php` además normaliza `REDIRECT_HTTP_AUTHORIZATION`. No es un problema del JWT.
- **Migraciones desde el panel:** si `GET /api/migrations/status` devuelve `500` con `detail`, revisar que `phinx.php`, `database/migrations/` y `vendor/` estén en el hosting y que `storage/` permita escribir. El lock `storage/migrations.lock` puede quedar en el servidor; no es un archivo de datos y se puede borrar si quedó trabado, pero no mientras haya una migración corriendo.
- **Migración de mantenimiento en bases con documentos:** `20261023000001_add_maintenance_fields` usa `ALTER TABLE` nativo en vez de `addColumn()` de Phinx. En SQLite, Phinx reconstruye la tabla y, como las migraciones corren dentro de una transacción, su `PRAGMA foreign_keys = OFF` no tiene efecto: al soltar la tabla vieja se ejecutarían los `ON DELETE` de las hijas y se perderían las líneas de factura (`CASCADE`) o quedarían sin producto (`SET NULL`). El `down()` lanza `IrreversibleMigrationException` a propósito. Si se migra una base con datos, hacer copia de `storage/database.sqlite` antes.
- **InfinityFree cachea los estáticos 30 días:** `index.html` y `admin/index.html` salen con `Cache-Control: max-age=2592000, public`, así que después de un build el navegador puede seguir usando el HTML viejo, que apunta a `site-assets/*` con hashes que ya no existen (página en blanco, sin productos, o panel desactualizado). `public/.htaccess` fuerza `no-cache, no-store, must-revalidate` para los `.html`; si un navegador ya tiene el HTML viejo, un `Ctrl + F5` lo destraba. No cachear los `.html` no afecta a los assets, que llevan hash en el nombre.
- **La vista previa del link tarda en actualizarse:** WhatsApp, Facebook y Telegram cachean el preview por URL (no por meta tags). Después de subir el `index.html` con `og:image`, un link ya compartido puede seguir mostrando el preview viejo; para forzar la relectura, pasar la URL por el depurador de Facebook o compartirla con un parámetro (`?v=2`). El `og:image` tiene que ser una URL absoluta y accesible sin login: `https://msmatafuegos.infy.click/brand/og-image.png`.
- El build del sitio vive en `public/` (`index.html`, `site-assets/`, `favicon.svg`, `robots.txt`) y el del panel en `public/admin/`; no editar esos archivos a mano, se regeneran con `npm run build`.

## Pendientes

- [x] Desarrollar el sitio público (Vue + Tailwind) y el panel de gestión (Vue + PrimeVue) de ms-matafuegos.
- [ ] Reemplazar los datos de demo del catálogo por el contenido real y las 21 imágenes de demo (3 por producto) por fotos definitivas; ver `IMAGE_CREDITS.md`.
- [ ] Definir el dominio público y los enlaces de redes sociales del sitio.
- [ ] Agregar un test de dominio/aplicación para la validación del WhatsApp de las consultas.
- [ ] Renombrar el namespace `Gaby\` a `MsMatafuegos\` y el paquete Composer.
- [ ] Definir si se calcula IVA y cómo se discrimina en presupuestos/facturas.
- [ ] Módulo de notificaciones a clientes (a partir de presupuestos/facturas).
- [ ] Agregar tests de integración HTTP (hoy hay tests de dominio/aplicación con Pest).
- [x] Exportar presupuestos y facturas a PDF desde el panel (`admin/src/pdf.js`, jsPDF).
- [ ] Evaluar el envío de presupuestos/facturas por email con el PDF adjunto.
- [ ] Evaluar el envío automático de los avisos de mantenimiento por WhatsApp Cloud API (hoy es manual vía `wa.me`).
- [ ] Evaluar permitir registrar la fecha de pago real cuando se marca una factura como pagada (hoy `paid_at` toma el día en que se cambia el estado).
- [ ] Eliminar el módulo `Page` del backend (API y tabla `pages`) si se confirma que no se usa; el panel ya no lo muestra.
