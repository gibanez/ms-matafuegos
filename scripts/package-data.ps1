# Genera un ZIP solo con los datos: base SQLite + imagenes de productos.
#
# Uso:
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\package-data.ps1
#
# Salida por defecto:
#   deploy\ms-matafuegos-datos.zip

[CmdletBinding()]
param(
    [string]$OutputPath = ''
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
if ([string]::IsNullOrWhiteSpace($OutputPath)) {
    $OutputPath = Join-Path $root 'deploy\ms-matafuegos-datos.zip'
} elseif (-not [System.IO.Path]::IsPathRooted($OutputPath)) {
    $OutputPath = Join-Path $root $OutputPath
}

$deployDir = Join-Path $root 'deploy'
$staging = Join-Path $deployDir 'data-staging'
$zipPath = [System.IO.Path]::GetFullPath($OutputPath)
$zipDir = Split-Path -Parent $zipPath

$dbPath = Join-Path $root 'storage\database.sqlite'
$mediaPath = Join-Path $root 'public\media'

if (-not (Test-Path -LiteralPath $dbPath)) {
    throw 'Falta storage\database.sqlite.'
}

if (-not (Test-Path -LiteralPath $mediaPath)) {
    throw 'Falta public\media.'
}

New-Item -ItemType Directory -Force -Path $deployDir | Out-Null
New-Item -ItemType Directory -Force -Path $zipDir | Out-Null

if (Test-Path -LiteralPath $staging) {
    Remove-Item -LiteralPath $staging -Recurse -Force
}
New-Item -ItemType Directory -Path $staging | Out-Null

Write-Host 'Copiando base de datos...'
New-Item -ItemType Directory -Path (Join-Path $staging 'storage') | Out-Null
Copy-Item -LiteralPath $dbPath -Destination (Join-Path $staging 'storage\database.sqlite') -Force

Write-Host 'Copiando imagenes...'
Copy-Item -LiteralPath $mediaPath -Destination (Join-Path $staging 'public\media') -Recurse -Force

$creditsPath = Join-Path $root 'IMAGE_CREDITS.md'
if (Test-Path -LiteralPath $creditsPath) {
    Copy-Item -LiteralPath $creditsPath -Destination (Join-Path $staging 'IMAGE_CREDITS.md') -Force
}

$readme = @'
MS Matafuegos - datos para InfinityFree
=======================================

Contenido
---------
- storage/database.sqlite  Base con categorias, productos, multimedia y admin.
- public/media/            Imagenes de los productos.
- IMAGE_CREDITS.md         Creditos de las imagenes de demostracion.

Instalacion
-----------
1. Antes de reemplazar nada, descargar una copia del storage/database.sqlite
   que ya esta en el hosting, por si tiene datos nuevos.
2. Subir el contenido de este ZIP a /htdocs, sobrescribiendo:
   - storage/database.sqlite
   - public/media/ (se mezclan las carpetas de cada producto)
3. No tocar .env ni .htaccess.
4. Probar /api/public/products y /admin/.

Importante
----------
- Este ZIP reemplaza la base completa. Si el hosting ya tiene consultas,
  presupuestos o facturas cargados, se pierden. Hacer backup primero.
- El admin vuelve a quedar con el usuario semilla admin@msmatafuegos.com /
  Admin1234. Cambiar la clave antes de publicar.
'@
Set-Content -LiteralPath (Join-Path $staging 'LEEME-DATOS.txt') -Value $readme -Encoding UTF8

if (Test-Path -LiteralPath $zipPath) {
    Remove-Item -LiteralPath $zipPath -Force
}

Write-Host 'Comprimiendo datos...'
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
Write-Host 'Paquete de datos generado:' -ForegroundColor Green
Write-Host "  $zipPath"
Write-Host ("  {0:N2} MB / {1} archivos" -f ($zipInfo.Length / 1MB), $entryCount)
Write-Host ''
Write-Host 'Subi el contenido a /htdocs, sobrescribiendo storage/ y public/media/.' -ForegroundColor Yellow
