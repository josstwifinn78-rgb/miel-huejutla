param(
  [string]$TargetPath = 'C:\xampp\htdocs\miel_huejutla',
  [string]$PhpExe = 'C:\xampp\php\php.exe'
)
$ErrorActionPreference = 'Stop'
$ProjectRoot = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path $PhpExe)) { throw "No se encontró PHP en '$PhpExe'. Ajusta el parámetro -PhpExe." }
$phpFiles = Get-ChildItem -Path $ProjectRoot -Filter '*.php' -Recurse
foreach ($file in $phpFiles) {
  & $PhpExe -l $file.FullName
  if ($LASTEXITCODE -ne 0) { throw "La validación PHP falló en $($file.FullName)" }
}
New-Item -ItemType Directory -Force -Path $TargetPath | Out-Null
Get-ChildItem -Path $ProjectRoot -Force | Where-Object { $_.Name -notin @('.git','.vscode') } | ForEach-Object {
  Copy-Item -Path $_.FullName -Destination $TargetPath -Recurse -Force
}
Write-Host "Versión copiada a $TargetPath. Inicia Apache/MySQL y revisa /health.php."
