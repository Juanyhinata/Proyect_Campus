# Script mejorado para actualizar rutas a producción
$ErrorActionPreference = "Stop"

$rootPath = "c:\xampp\htdocs\Campus_Latam"

# Lista de archivos a procesar (admin)
$adminFiles = @(
    "gestion_cursos.php", "gestion_cursos_crear.php", "gestion_cursos_editar.php", "gestion_cursos_eliminar.php",
    "gestion_modulos.php", "gestion_modulos_crear.php", "gestion_modulos_editar.php", "gestion_modulos_eliminar.php",
    "gestion_temas.php", "gestion_temas_crear.php", "gestion_temas_editar.php", "gestion_temas_eliminar.php",
    "gestion_evaluaciones.php", "ordenar.php"
)

function Update-PhpFile {
    param([string]$FilePath)
    
    if (!(Test-Path $FilePath)) {
        Write-Host "Archivo no encontrado: $FilePath" -ForegroundColor Yellow
        return $false
    }
    
    $content = Get-Content $FilePath -Raw -Encoding UTF8
    $originalContent = $content
    
    # Reemplazos específicos
    $content = $content -replace "require_once '../../includes/auth\.php';", "require_once __DIR__ . '/../../includes/auth.php';"
    $content = $content -replace "require_once '../../config/db\.php';", "require_once __DIR__ . '/../../config/db.php';"
    $content = $content -replace "require_once '../../models/", "require_once __DIR__ . '/../../models/"
    $content = $content -replace "require_once\('view/", "require_once __DIR__ . '/view/"
    $content = $content -replace "require_once \('view/", "require_once __DIR__ . '/view/"
    
    if ($content -ne $originalContent) {
        Set-Content -Path $FilePath -Value $content -Encoding UTF8 -NoNewline
        Write-Host "OK: $FilePath" -ForegroundColor Green
        return $true
    }
    
    return $false
}

Write-Host ""
Write-Host "=== Actualizando archivos del modulo ADMIN ===" -ForegroundColor Cyan
$updated = 0
foreach ($file in $adminFiles) {
    $path = Join-Path "$rootPath\rol\admin" $file
    if (Update-PhpFile $path) { $updated++ }
}

Write-Host ""
Write-Host "=== Actualizando archivos del modulo CLIENTE ===" -ForegroundColor Cyan
$clienteFiles = Get-ChildItem "$rootPath\rol\cliente" -Filter "*.php" -File
foreach ($file in $clienteFiles) {
    if (Update-PhpFile $file.FullName) { $updated++ }
}

Write-Host ""
Write-Host "=== Actualizando archivos del modulo AGENTE ===" -ForegroundColor Cyan
$agenteFiles = Get-ChildItem "$rootPath\rol\agente" -Filter "*.php" -File
foreach ($file in $agenteFiles) {
    if (Update-PhpFile $file.FullName) { $updated++ }
}

Write-Host ""
Write-Host "=== Actualizando archivos del modulo INVITADO ===" -ForegroundColor Cyan
$invitadoFiles = Get-ChildItem "$rootPath\rol\invitado" -Filter "*.php" -File
foreach ($file in $invitadoFiles) {
    if (Update-PhpFile $file.FullName) { $updated++ }
}

Write-Host ""
Write-Host "=== Actualizando archivos de TEST ===" -ForegroundColor Cyan
if (Test-Path "$rootPath\test\controllers") {
    $testFiles = Get-ChildItem "$rootPath\test\controllers" -Filter "*.php" -File
    foreach ($file in $testFiles) {
        if (Update-PhpFile $file.FullName) { $updated++ }
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Total de archivos actualizados: $updated" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
