$ErrorActionPreference = "Stop"

$base = $PSScriptRoot
$chrome = "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"

php "$base\build.php"

$html = "file:///" + (($base + "\panduan.html") -replace '\\', '/')
$pdf = Join-Path $base "panduan-pendaftaran-login.pdf"
$profile = Join-Path $env:TEMP ("opencode\chrome-panduan-" + (Get-Random))

if (Test-Path -LiteralPath $pdf) {
  Remove-Item -LiteralPath $pdf -Force
}

& $chrome --headless=new --disable-gpu --no-sandbox --hide-scrollbars `
  --user-data-dir="$profile" --no-pdf-header-footer `
  --virtual-time-budget=10000 `
  --print-to-pdf="$pdf" $html

$deadline = (Get-Date).AddSeconds(30)
$lastSize = -1
while ((Get-Date) -lt $deadline) {
  if (Test-Path -LiteralPath $pdf) {
    $size = (Get-Item -LiteralPath $pdf).Length
    if ($size -gt 0 -and $size -eq $lastSize) { break }
    $lastSize = $size
  }
  Start-Sleep -Milliseconds 500
}

if (Test-Path -LiteralPath $pdf) {
  Write-Output "PDF dibuat: $pdf"
} else {
  throw "PDF gagal dibuat"
}
