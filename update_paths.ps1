# Script para actualizar rutas relativas a absolutas usando __DIR__
# Este script actualiza todos los archivos PHP del proyecto

$rootPath = "c:\xampp\htdocs\Campus_Latam"

# Patrones de búsqueda y reemplazo
$replacements = @(
    # Rutas de includes/config/models desde rol/admin, rol/cliente, rol/agente
    @{
        Pattern = "require_once '../../includes/auth.php';"
        Replacement = "require_once __DIR__ . '/../../includes/auth.php';"
    },
    @{
        Pattern = "require_once '../../config/db.php';"
        Replacement = "require_once __DIR__ . '/../../config/db.php';"
    },
    @{
        Pattern = "require_once '../../models/"
        Replacement = "require_once __DIR__ . '/../../models/"
    },
    # Vistas desde rol/*/
    @{
        Pattern = "require_once('view/"
        Replacement = "require_once __DIR__ . '/view/"
    },
    @{
        Pattern = "require_once ('view/"
        Replacement = "require_once __DIR__ . '/view/"
    },
    # Desde raíz
    @{
        Pattern = "require_once 'config/db.php';"
        Replacement = "require_once __DIR__ . '/config/db.php';"
    },
    @{
        Pattern = "require_once 'models/"
        Replacement = "require_once __DIR__ . '/models/"
    }
)

# Directorios a procesar
$directories = @(
    "$rootPath\rol\admin",
    "$rootPath\rol\cliente",
    "$rootPath\rol\agente",
    "$rootPath\rol\invitado",
    "$rootPath\test\controllers"
)

$filesUpdated = 0

foreach ($dir in $directories) {
    if (Test-Path $dir) {
        $phpFiles = Get-ChildItem -Path $dir -Filter "*.php" -File
        
        foreach ($file in $phpFiles) {
            $content = Get-Content $file.FullName -Raw -Encoding UTF8
            $originalContent = $content
            $fileModified = $false
            
            foreach ($replacement in $replacements) {
                if ($content -match [regex]::Escape($replacement.Pattern)) {
                    $content = $content -replace [regex]::Escape($replacement.Pattern), $replacement.Replacement
                    $fileModified = $true
                }
            }
            
            if ($fileModified) {
                Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
                Write-Host "✓ Actualizado: $($file.FullName)" -ForegroundColor Green
                $filesUpdated++
            }
        }
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Total de archivos actualizados: $filesUpdated" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
