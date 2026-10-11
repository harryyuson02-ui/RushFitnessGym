$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$cloudflaredPath = Join-Path $projectRoot '.tools\cloudflared.exe'
$cloudflaredUrl = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe'
$hotFile = Join-Path $projectRoot 'public\hot'
$serverProcess = $null

Set-Location $projectRoot

if (-not (Test-Path -LiteralPath $cloudflaredPath)) {
    New-Item -ItemType Directory -Force -Path (Split-Path -Parent $cloudflaredPath) | Out-Null
    Write-Host 'Downloading the secure mobile tunnel...' -ForegroundColor Cyan
    Invoke-WebRequest -Uri $cloudflaredUrl -OutFile $cloudflaredPath
}

if (Test-Path -LiteralPath $hotFile) {
    Remove-Item -LiteralPath $hotFile -Force
}

function Test-LaravelServer {
    try {
        $response = Invoke-WebRequest -Uri 'http://127.0.0.1:8000/up' -UseBasicParsing -TimeoutSec 2

        return $response.StatusCode -eq 200
    } catch {
        return $false
    }
}

if (-not (Test-LaravelServer)) {
    Write-Host 'Starting Rush Fitness Gym...' -ForegroundColor Cyan
    $serverProcess = Start-Process -FilePath 'php' `
        -ArgumentList @('artisan', 'serve', '--host=127.0.0.1', '--port=8000') `
        -WorkingDirectory $projectRoot `
        -WindowStyle Hidden `
        -PassThru

    $ready = $false
    for ($attempt = 0; $attempt -lt 20; $attempt++) {
        Start-Sleep -Milliseconds 250
        if (Test-LaravelServer) {
            $ready = $true
            break
        }
    }

    if (-not $ready) {
        Stop-Process -Id $serverProcess.Id -Force -ErrorAction SilentlyContinue
        throw 'Laravel did not start on http://127.0.0.1:8000.'
    }
}

Write-Host ''
Write-Host 'Your secure mobile URL will appear below as https://...trycloudflare.com' -ForegroundColor Green
Write-Host 'Open that HTTPS URL on the phone, sign in, and allow camera access.' -ForegroundColor Green
Write-Host 'Keep this terminal open. Press Ctrl+C when mobile testing is finished.' -ForegroundColor Yellow
Write-Host ''

try {
    & $cloudflaredPath tunnel --url 'http://127.0.0.1:8000' --no-autoupdate
} finally {
    if ($null -ne $serverProcess -and -not $serverProcess.HasExited) {
        Stop-Process -Id $serverProcess.Id -Force -ErrorAction SilentlyContinue
    }
}
