# ============================================================================
#  SKRIP BUKTI PENGERJAAN - MODE A (LDAP UY / Sesi Setelah Login LDAP)
#  Proyek: SAKTI
#
#  Cara pakai (dari root proyek):
#      powershell -ExecutionPolicy Bypass -File .\docs\bukti\ambil-bukti.ps1
#
#  Skrip ini MENCETAK kode asli + hasil test langsung dari repositori.
#  Screenshot setiap blok "BUKTI n" untuk dijadikan lampiran laporan.
# ============================================================================

$ErrorActionPreference = 'Stop'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $root

function Banner([string]$text) {
    Write-Host ""
    Write-Host ("=" * 78) -ForegroundColor Cyan
    Write-Host ("  " + $text) -ForegroundColor Cyan
    Write-Host ("=" * 78) -ForegroundColor Cyan
}

function Show-Code([string]$path, [int]$start, [int]$end, [string]$note = '') {
    Banner ("FILE: " + $path + "  (baris " + $start + "-" + $end + ")")
    if ($note -ne '') {
        Write-Host ("  " + $note) -ForegroundColor DarkGray
        Write-Host ""
    }
    $lines = Get-Content -LiteralPath $path
    for ($i = $start; $i -le $end -and $i -le $lines.Count; $i++) {
        $num = "{0,4}" -f $i
        Write-Host ("  " + $num + " | " + $lines[$i - 1])
    }
}

Write-Host ""
Write-Host "  BUKTI PENGERJAAN MODE A - DIAMBIL LANGSUNG DARI REPOSITORI" -ForegroundColor Yellow
Write-Host ("  Waktu: " + (Get-Date -Format "yyyy-MM-dd HH:mm:ss"))
Write-Host ("  Folder: " + $root)

# ---------------------------------------------------------------------------
Banner "BUKTI 1 - FILE YANG DIUBAH / DIBUAT (git status + commit)"
Write-Host "  Status working tree:" -ForegroundColor DarkGray
git status --short
Write-Host ""
Write-Host "  Riwayat commit terakhir:" -ForegroundColor DarkGray
git log --oneline -5
Write-Host ""
$feat = git log --format="%H|%s" -n 30 | Select-String -Pattern 'secure session management' | Select-Object -First 1
if ($feat) {
    $hash = ($feat.Line -split '\|')[0]
    Write-Host "  File pada commit fitur keamanan sesi ($hash):" -ForegroundColor DarkGray
    git show --stat --oneline $hash
}

# ---------------------------------------------------------------------------
Show-Code "app/Http/Controllers/AuthController.php" 106 120 `
    "Regenerasi ID sesi tepat setelah Auth::attempt() berhasil (anti session fixation)."

# ---------------------------------------------------------------------------
Show-Code "app/Http/Controllers/AuthController.php" 186 215 `
    "Hanya identitas minimal yang disimpan; kunci sensitif dibersihkan."

# ---------------------------------------------------------------------------
Show-Code "app/Http/Controllers/AuthController.php" 221 239 `
    "Logout: Auth::logout + invalidate + regenerateToken + cookie kedaluwarsa."

# ---------------------------------------------------------------------------
Show-Code "config/session.php" 30 50 `
    "Idle timeout 30 menit dan absolute timeout 480 menit (8 jam)."

# ---------------------------------------------------------------------------
Show-Code "config/session.php" 180 217 `
    "Atribut cookie: secure dari .env, http_only true, same_site lax."

# ---------------------------------------------------------------------------
Show-Code ".env.example" 30 46 `
    "Template environment tanpa kredensial asli (nilai dummy/aman)."

# ---------------------------------------------------------------------------
Show-Code "config/ldap.php" 55 62 `
    "Logging LDAP nonaktif secara default agar kredensial tidak tercetak."

# ---------------------------------------------------------------------------
Show-Code "app/Http/Middleware/EnforceSessionTimeout.php" 35 75 `
    "Middleware: bersihkan kunci sensitif, hitung idle & absolute expiry."

# ---------------------------------------------------------------------------
Show-Code "app/Http/Middleware/EnforceSessionTimeout.php" 76 120 `
    "Middleware: forced logout, hapus sesi, hapus cookie, redirect/401."

# ---------------------------------------------------------------------------
Show-Code "bootstrap/app.php" 15 30 `
    "Registrasi EnforceSessionTimeout pada middleware group web."

# ---------------------------------------------------------------------------
Banner "BUKTI 10 - DAFTAR AUTOMATED TEST (LDAP DI-MOCK)"
Select-String -Path "tests\Feature\SessionSecurityTest.php" -Pattern "public function test_" |
    ForEach-Object { Write-Host ("  baris {0,3} | {1}" -f $_.LineNumber, $_.Line.Trim()) }
Write-Host ""
Write-Host "  Mock LDAP:" -ForegroundColor DarkGray
Select-String -Path "tests\Feature\SessionSecurityTest.php" -Pattern "Mockery::mock|->bind\(" |
    ForEach-Object { Write-Host ("  baris {0,3} | {1}" -f $_.LineNumber, $_.Line.Trim()) }

# ---------------------------------------------------------------------------
Banner "BUKTI 11 - HASIL TEST KEAMANAN SESI (LDAP DI-MOCK)"
php artisan test tests/Feature/SessionSecurityTest.php

# ---------------------------------------------------------------------------
Banner "BUKTI 12 - NILAI KONFIGURASI AKTIF (dibaca Laravel)"
php artisan tinker --execute="dump(['idle_menit' => config('session.lifetime'), 'absolute_menit' => config('session.absolute_lifetime'), 'secure' => config('session.secure'), 'http_only' => config('session.http_only'), 'same_site' => config('session.same_site'), 'cookie' => config('session.cookie')]);"

# ---------------------------------------------------------------------------
Banner "BUKTI 13 - GIT BEBAS SECRET (hanya file yang di-track)"
$trackedEnv = git ls-files | Select-String -Pattern '(^|/)\.env'
Write-Host "  File env yang ter-track:"
$trackedEnv | ForEach-Object { Write-Host ("    " + $_.Line) }
Write-Host ""
Write-Host "  .env diabaikan oleh:"
git check-ignore -v .env | ForEach-Object { Write-Host ("    " + $_) }
Write-Host ""
Write-Host "  Scan pola secret pada tracked files (vendor dikecualikan):"
$hits = git grep -n -I -E 'APP_KEY=base64:[A-Za-z0-9+/]{20,}={0,2}|-----BEGIN [A-Z ]*PRIVATE KEY-----|AKIA[0-9A-Z]{16}|LDAP_PASSWORD=[^[:space:]]+|DB_PASSWORD=[^[:space:]]+' -- . ':!vendor' 2>$null
if ($hits) { $hits | ForEach-Object { Write-Host ("    " + $_) -ForegroundColor Red } }
else { Write-Host "    TIDAK ADA SECRET DITEMUKAN" -ForegroundColor Green }

# ---------------------------------------------------------------------------
Banner "SELESAI - SEMUA BUKTI BERHASIL DICETAK"
Write-Host "  Screenshot tiap blok BUKTI 1-13 untuk lampiran laporan." -ForegroundColor Green
Write-Host ""
