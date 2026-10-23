# Genera un ZIP listo para subir a InfinityFree.
#
# Uso:
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\package-infinityfree.ps1
#
# Salida por defecto:
#   deploy\ms-matafuegos-infinityfree.zip
#
# El ZIP incluye solo lo necesario para produccion:
#   public/ (sitio compilado, panel y multimedia), src/, vendor/ sin dev,
#   database/migrations/ (ejecutables desde Admin > Migraciones),
#   storage/database.sqlite, composer.json/lock, phinx.php,
#   .env de produccion con un JWT_SECRET nuevo y .htaccess raiz para /htdocs.

[CmdletBinding()]
param(
    [string]$OutputPath = ''
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
if ([string]::IsNullOrWhiteSpace($OutputPath)) {
    $OutputPath = Join-Path $root 'deploy\ms-matafuegos-infinityfree.zip'
} elseif (-not [System.IO.Path]::IsPathRooted($OutputPath)) {
    $OutputPath = Join-Path $root $OutputPath
}

$deployDir = Join-Path $root 'deploy'
$staging = Join-Path $deployDir 'staging'
$zipPath = [System.IO.Path]::GetFullPath($OutputPath)
$zipDir = Split-Path -Parent $zipPath

$required = @(
    'public\index.php',
    'public\.htaccess',
    'public\admin\index.html',
    'public\site-assets',
    'src',
    'vendor\autoload.php',
    'storage\database.sqlite',
    'composer.json',
    'composer.lock'
)

foreach ($relative in $required) {
    $path = Join-Path $root $relative
    if (-not (Test-Path -LiteralPath $path)) {
        throw "Falta un archivo o carpeta requerida: $relative"
    }
}

New-Item -ItemType Directory -Force -Path $deployDir | Out-Null
New-Item -ItemType Directory -Force -Path $zipDir | Out-Null

if (Test-Path -LiteralPath $staging) {
    Remove-Item -LiteralPath $staging -Recurse -Force
}
New-Item -ItemType Directory -Path $staging | Out-Null

Write-Host 'Copiando archivos de produccion...'
Copy-Item -LiteralPath (Join-Path $root 'public') -Destination (Join-Path $staging 'public') -Recurse -Force
Copy-Item -LiteralPath (Join-Path $root 'src') -Destination (Join-Path $staging 'src') -Recurse -Force
Copy-Item -LiteralPath (Join-Path $root 'vendor') -Destination (Join-Path $staging 'vendor') -Recurse -Force
Copy-Item -LiteralPath (Join-Path $root 'database') -Destination (Join-Path $staging 'database') -Recurse -Force
New-Item -ItemType Directory -Path (Join-Path $staging 'storage') | Out-Null
Copy-Item -LiteralPath (Join-Path $root 'storage\database.sqlite') -Destination (Join-Path $staging 'storage\database.sqlite') -Force
Copy-Item -LiteralPath (Join-Path $root 'composer.json') -Destination (Join-Path $staging 'composer.json') -Force
Copy-Item -LiteralPath (Join-Path $root 'composer.lock') -Destination (Join-Path $staging 'composer.lock') -Force
Copy-Item -LiteralPath (Join-Path $root 'phinx.php') -Destination (Join-Path $staging 'phinx.php') -Force
$creditsPath = Join-Path $root 'IMAGE_CREDITS.md'
if (Test-Path -LiteralPath $creditsPath) {
    Copy-Item -LiteralPath $creditsPath -Destination (Join-Path $staging 'IMAGE_CREDITS.md') -Force
}

# Los tests no hacen falta en el servidor.
$stagingTests = Join-Path $staging 'src\tests'
if (Test-Path -LiteralPath $stagingTests) {
    Remove-Item -LiteralPath $stagingTests -Recurse -Force
}

Write-Host 'Instalando dependencias de produccion (sin dev)...'
Push-Location $staging
try {
    & composer install --no-dev --optimize-autoloader --no-interaction --no-progress --ignore-platform-req=ext-sodium
    if ($LASTEXITCODE -ne 0) {
        throw "composer install fallo con codigo $LASTEXITCODE."
    }
} finally {
    Pop-Location
}

if (Test-Path -LiteralPath (Join-Path $staging 'vendor\pestphp')) {
    throw 'La carpeta vendor\pestphp sigue presente: el paquete no es apto para produccion.'
}

Write-Host 'Generando .env de produccion...'
$rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
$secretBytes = New-Object byte[] 32
$rng.GetBytes($secretBytes)
$jwtSecret = ($secretBytes | ForEach-Object { $_.ToString('x2') }) -join ''
$envContent = @"
DB_PATH=storage/database.sqlite
JWT_SECRET=$jwtSecret
JWT_TTL=3600
APP_TIMEZONE=America/Argentina/Buenos_Aires
APP_DEBUG=false
"@
Set-Content -LiteralPath (Join-Path $staging '.env') -Value $envContent -Encoding ASCII

Write-Host 'Generando .htaccess raiz para InfinityFree...'
$rootHtaccess = @'
# InfinityFree sirve /htdocs como raiz. Enviar todo a public/.
RewriteEngine On

# Hosting compartido (CGI/FastCGI): pasar el header Authorization a PHP.
# Sin esto, el login funciona pero las rutas protegidas responden
# "Missing bearer token." porque Apache no entrega el header.
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

# La API se resuelve directo en public/index.php. Si se manda primero a
# public/, algunos hosts dejan REQUEST_URI con /public/ delante y el fallback
# SPA termina devolviendo index.html en lugar del JSON.
RewriteRule ^api(/.*)?$ public/index.php [QSA,L]

RewriteCond %{REQUEST_URI} !^/public(/|$)
RewriteRule ^(.*)$ public/$1 [L]
'@
Set-Content -LiteralPath (Join-Path $staging '.htaccess') -Value $rootHtaccess -Encoding ASCII

$readme = @'
MS Matafuegos - paquete para InfinityFree
==========================================

Contenido
---------
- public/                  Sitio, panel y multimedia compilados.
- src/                     Codigo PHP (sin tests).
- vendor/                  Dependencias de produccion.
- database/migrations/     Migraciones (la base ya viene migrada).
- storage/database.sqlite  Base SQLite con el catalogo y las imagenes.
- .env                     Configuracion de produccion generada para este paquete.
- .htaccess                Redirige el docroot /htdocs hacia public/.

Instalacion
-----------
1. En InfinityFree, entrar al File Manager del dominio y abrir /htdocs.
2. Subir el contenido del ZIP (public, src, vendor, storage, etc.) directamente
   dentro de /htdocs. No crear una carpeta extra con el nombre del proyecto.
3. Conservar el .htaccess de la raiz y el .env incluidos.
4. No subir el ZIP dentro de /htdocs despues de descomprimirlo; borrarlo.
5. Probar:
   - https://TU-DOMINIO/            (sitio publico)
   - https://TU-DOMINIO/admin/      (panel)
   - https://TU-DOMINIO/api/ping    (API; debe devolver JSON, no HTML)
6. Entrar al panel con el usuario semilla y cambiar la contrasena antes de
   publicar el sitio. Usuario: admin@msmatafuegos.com / Admin1234.
7. Si hay migraciones nuevas, abrir Admin > Migraciones y usar el boton
   "Ejecutar migraciones". No hace falta una terminal.

Notas
-----
- La base ya esta migrada. Las migraciones pendientes se pueden ejecutar desde
  Admin > Migraciones; el boton necesita vendor/ con Phinx y storage/ escribible.
- El .htaccess raiz manda /api directo a public/index.php. Si /api/ping
  devuelve el HTML del sitio en vez de JSON, revisar que la regla de API este
  antes de la regla general que manda todo a public/.
- Si se reemplaza storage/database.sqlite, conservar el archivo con los datos
  reales y volver a subirlo.
- Si el panel no puede subir imagenes, revisar permisos de escritura de
  public/media/. Para migraciones y SQLite, revisar storage/.
- El .env contiene un JWT_SECRET nuevo. No compartir ese archivo ni volver a
  usar el secreto de desarrollo.
- Las imagenes del catalogo son de demostracion; antes de publicar conviene
  reemplazarlas por fotos propias o mostrar los creditos de IMAGE_CREDITS.md.
'@
Set-Content -LiteralPath (Join-Path $staging 'LEEME-INFINITYFREE.txt') -Value $readme -Encoding UTF8

if (Test-Path -LiteralPath $zipPath) {
    Remove-Item -LiteralPath $zipPath -Force
}

Write-Host 'Comprimiendo paquete...'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$zipStream = [System.IO.File]::Open($zipPath, [System.IO.FileMode]::Create)
$archive = New-Object System.IO.Compression.ZipArchive($zipStream, [System.IO.Compression.ZipArchiveMode]::Create, $false)
try {
    $files = Get-ChildItem -LiteralPath $staging -Recurse -File | Sort-Object FullName
    foreach ($file in $files) {
        $relative = $file.FullName.Substring($staging.Length + 1).Replace('\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
            $archive,
            $file.FullName,
            $relative,
            [System.IO.Compression.CompressionLevel]::Optimal
        ) | Out-Null
    }
} finally {
    $archive.Dispose()
    $zipStream.Dispose()
}

$zipInfo = Get-Item -LiteralPath $zipPath
$archive = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
$entryCount = $archive.Entries.Count
$archive.Dispose()

Remove-Item -LiteralPath $staging -Recurse -Force

Write-Host ''
Write-Host 'Paquete generado:' -ForegroundColor Green
Write-Host "  $zipPath"
Write-Host ("  {0:N2} MB / {1} archivos" -f ($zipInfo.Length / 1MB), $entryCount)
Write-Host ''
Write-Host 'Subi el contenido del ZIP directamente a /htdocs en InfinityFree.' -ForegroundColor Yellow
