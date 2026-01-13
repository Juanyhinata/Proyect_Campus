# Script para corregir paréntesis extra en require_once
$ErrorActionPreference = "Stop"

$rootPath = "c:\xampp\htdocs\Campus_Latam"

function Fix-ExtraParenthesis {
    param([string]$FilePath)
    
    if (!(Test-Path $FilePath)) {
        return $false
    }
    
    $content = Get-Content $FilePath -Raw -Encoding UTF8
    $originalContent = $content
    
    # Corregir paréntesis extra en require_once
    $content = $content -replace "require_once __DIR__ \. '/view/([^']+)'\);", "require_once __DIR__ . '/view/`$1';"
    
    if ($content -ne $originalContent) {
        Set-Content -Path $FilePath -Value $content -Encoding UTF8 -NoNewline
        Write-Host "Corregido: $FilePath" -ForegroundColor Green
        return $true
    }
    
    return $false
}

Write-Host ""
Write-Host "=== Corrigiendo sintaxis de require_once ===" -ForegroundColor Cyan
$fixed = 0

$directories = @(
    "$rootPath\rol\admin",
    "$rootPath\rol\cliente",
    "$rootPath\rol\agente",
    "$rootPath\rol\invitado"
)

foreach ($dir in $directories) {
    if (Test-Path $dir) {
        $phpFiles = Get-ChildItem -Path $dir -Filter "*.php" -File
        foreach ($file in $phpFiles) {
            if (Fix-ExtraParenthesis $file.FullName) { $fixed++ }
        }
    }
}

Write-Host ""
Write-Host "Total de archivos corregidos: $fixed" -ForegroundColor Yellow
Write-Host ""
