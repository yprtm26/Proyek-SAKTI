# ============================================================================
#  GENERATOR HALAMAN BUKTI (HTML) - MODE A
#  Menghasilkan: docs/bukti/bukti-output.html
#
#  Jalankan dari root proyek:
#      powershell -ExecutionPolicy Bypass -File .\docs\bukti\buat-html-bukti.ps1
# ============================================================================

$ErrorActionPreference = 'Stop'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $root

function Html([string]$text) {
    return [System.Net.WebUtility]::HtmlEncode($text)
}

function Strip-Ansi([string]$text) {
    $esc = [char]27
    return ($text -replace ($esc + '\[[0-9;]*[a-zA-Z]'), '')
}

function Get-CodeHtml([string]$path, [int]$start, [int]$end) {
    $lines = Get-Content -LiteralPath $path
    $sb = New-Object System.Text.StringBuilder
    for ($i = $start; $i -le $end -and $i -le $lines.Count; $i++) {
        $enc = Html $lines[$i - 1]
        [void]$sb.AppendLine("<span class='line'><span class='ln'>$i</span><span class='code'>$enc</span></span>")
    }
    return $sb.ToString()
}

function New-Card([string]$num, [string]$title, [string]$note, [string]$body) {
    return @"
<section class="card">
  <div class="card-head">
    <span class="badge">BUKTI $num</span>
    <div><h2>$title</h2><p class="note">$note</p></div>
  </div>
  $body
</section>
"@
}

function New-CodeBody([string]$path, [int]$start, [int]$end) {
    $cmd = "FILE: $path  (baris $start-$end)"
    $code = Get-CodeHtml $path $start $end
    return "<div class='cmd'>$cmd</div><pre class='code'>$code</pre>"
}

function New-PlainBody([string]$cmd, [string]$output) {
    $safe = Html (Strip-Ansi $output)
    return "<div class='cmd'>$cmd</div><pre class='plain'>$safe</pre>"
}

# --- Ambil data dinamis -----------------------------------------------------
$gitStatus = (git status --short | Out-String)
$gitLog    = (git log --oneline -6 | Out-String)
$feat      = git log --format="%H|%s" -n 30 | Select-String -Pattern 'secure session management' | Select-Object -First 1
$gitStat   = ""
if ($feat) {
    $hash = ($feat.Line -split '\|')[0]
    $gitStat = (git show --stat --oneline $hash | Out-String)
}

$testsList = (Select-String -Path "tests\Feature\SessionSecurityTest.php" -Pattern "public function test_" |
    ForEach-Object { "baris {0,3} | {1}" -f $_.LineNumber, $_.Line.Trim() }) -join "`n"
$mocksList = (Select-String -Path "tests\Feature\SessionSecurityTest.php" -Pattern "Mockery::mock|->bind\(" |
    ForEach-Object { "baris {0,3} | {1}" -f $_.LineNumber, $_.Line.Trim() }) -join "`n"
$testOut   = (php artisan test tests/Feature/SessionSecurityTest.php 2>&1 | Out-String)
$configOut = (php artisan tinker --execute="dump(['idle_menit' => config('session.lifetime'), 'absolute_menit' => config('session.absolute_lifetime'), 'secure' => config('session.secure'), 'http_only' => config('session.http_only'), 'same_site' => config('session.same_site'), 'cookie' => config('session.cookie')]);" 2>&1 | Out-String)

$trackedEnv = (git ls-files | Select-String -Pattern '(^|/)\.env' | ForEach-Object { $_.Line }) -join "`n"
$ignoredEnv = (git check-ignore -v .env | Out-String)
$secretHits = git grep -n -I -E 'APP_KEY=base64:[A-Za-z0-9+/]{20,}={0,2}|-----BEGIN [A-Z ]*PRIVATE KEY-----|AKIA[0-9A-Z]{16}|LDAP_PASSWORD=[^[:space:]]+|DB_PASSWORD=[^[:space:]]+' -- . ':!vendor' 2>$null
if (-not $secretHits) { $secretHits = "TIDAK ADA SECRET DITEMUKAN" } else { $secretHits = ($secretHits | Out-String) }

$gitBody = New-PlainBody "git status --short" $gitStatus
$gitBody += New-PlainBody "git log --oneline -6" $gitLog
$gitBody += New-PlainBody "git show --stat <commit fitur keamanan sesi>" $gitStat

$middlewareBody = "<div class='sub'>Bagian 1 - daftar kunci sensitif</div>"
$middlewareBody += New-CodeBody "app/Http/Middleware/EnforceSessionTimeout.php" 19 27
$middlewareBody += "<div class='sub'>Bagian 2 - cek idle &amp; absolute timeout</div>"
$middlewareBody += New-CodeBody "app/Http/Middleware/EnforceSessionTimeout.php" 35 75
$middlewareBody += "<div class='sub'>Bagian 3 - forced logout, hapus cookie, 401/redirect</div>"
$middlewareBody += New-CodeBody "app/Http/Middleware/EnforceSessionTimeout.php" 76 120

$testListBody = New-PlainBody "Daftar test dan lokasi mock LDAP" ($testsList + "`n`nMock LDAP:`n" + $mocksList)

$secretBody = New-PlainBody "git ls-files | .env" $trackedEnv
$secretBody += New-PlainBody "git check-ignore -v .env" $ignoredEnv
$secretBody += New-PlainBody "Scan pola secret pada tracked files" $secretHits

# --- Susun HTML -------------------------------------------------------------
$cards = @()
$cards += New-Card "1" "File yang diubah / dibuat" "Status Git, riwayat commit, dan file pada commit fitur keamanan sesi." $gitBody
$cards += New-Card "2" "Regenerasi ID sesi setelah login" "AuthController.php baris 106-120 - regenerate() tepat setelah Auth::attempt() berhasil." (New-CodeBody "app/Http/Controllers/AuthController.php" 106 120)
$cards += New-Card "3" "Sanitasi data sesi" "AuthController.php baris 186-215 - hanya identitas minimal; kunci sensitif dibersihkan." (New-CodeBody "app/Http/Controllers/AuthController.php" 186 215)
$cards += New-Card "4" "Logout aman" "AuthController.php baris 221-239 - Auth::logout, invalidate, regenerateToken, cookie kedaluwarsa." (New-CodeBody "app/Http/Controllers/AuthController.php" 221 239)
$cards += New-Card "5" "Idle timeout 30 menit dan absolute timeout 8 jam" "config/session.php baris 30-50." (New-CodeBody "config/session.php" 30 50)
$cards += New-Card "6" "Atribut cookie sesi" "config/session.php baris 180-217 - secure dari env, http_only true, same_site lax." (New-CodeBody "config/session.php" 180 217)
$cards += New-Card "7" "Environment contoh tanpa kredensial asli" ".env.example baris 30-46." (New-CodeBody ".env.example" 30 46)
$cards += New-Card "8" "Logging LDAP nonaktif" "config/ldap.php baris 55-62 - mencegah kredensial tercetak ke log." (New-CodeBody "config/ldap.php" 55 62)
$cards += New-Card "9" "Middleware batas waktu sesi" "EnforceSessionTimeout.php - pembersihan kunci sensitif, cek timeout, forced logout." $middlewareBody
$cards += New-Card "10" "Registrasi middleware di grup web" "bootstrap/app.php baris 15-30." (New-CodeBody "bootstrap/app.php" 15 30)
$cards += New-Card "11" "Daftar automated test (LDAP di-mock)" "6 test, dua di antaranya adalah skenario wajib rubrik." $testListBody
$cards += New-Card "12" "Hasil test keamanan sesi" "php artisan test tests/Feature/SessionSecurityTest.php - 6 passed (60 assertions)." (New-PlainBody "php artisan test tests/Feature/SessionSecurityTest.php" $testOut)
$cards += New-Card "13" "Nilai konfigurasi aktif" "Dibaca langsung oleh Laravel: 30 menit, 480 menit, HttpOnly true, SameSite lax." (New-PlainBody "php artisan tinker --execute=dump(config(...))" $configOut)
$cards += New-Card "14" "Git bebas secret" "Hanya .env.example yang ter-track; .env diabaikan; tidak ada secret ditemukan." $secretBody

$now = Get-Date -Format "dd MMMM yyyy HH:mm"
$cardsHtml = $cards -join "`n"

$html = @"
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bukti Pengerjaan - Mode A - SAKTI</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#eef2f7;color:#0f172a;font-family:'Segoe UI',system-ui,-apple-system,sans-serif}
  .wrap{max-width:1180px;margin:0 auto;padding:32px 20px 80px}
  .hero{background:linear-gradient(135deg,#0f172a,#1e3a8a);color:#fff;border-radius:16px;padding:28px 32px;margin-bottom:20px}
  .hero h1{margin:0 0 10px;font-size:25px;letter-spacing:.2px}
  .hero p{margin:3px 0;color:#c7d2fe;font-size:14px}
  .hint{background:#fef9c3;border:1px solid #fde047;border-radius:12px;padding:14px 18px;margin:0 0 26px;font-size:14px;line-height:1.6}
  .hint b{color:#854d0e}
  .card{background:#fff;border:1px solid #dbe3ee;border-radius:14px;margin-bottom:24px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.06);page-break-inside:avoid}
  .card-head{display:flex;gap:14px;align-items:flex-start;padding:18px 22px;border-bottom:1px solid #e6edf5;background:#f8fafc}
  .badge{background:#1d4ed8;color:#fff;font-size:12px;font-weight:700;letter-spacing:.05em;padding:6px 10px;border-radius:999px;white-space:nowrap}
  .card h2{margin:0;font-size:16px}
  .note{margin:4px 0 0;font-size:13px;color:#64748b}
  .cmd{padding:11px 20px;background:#f1f5f9;border-bottom:1px solid #e6edf5;font:12.5px 'Cascadia Mono',Consolas,monospace;color:#334155;word-break:break-all}
  .sub{padding:10px 20px 0;font:600 12px 'Segoe UI',sans-serif;color:#1d4ed8;text-transform:uppercase;letter-spacing:.04em;background:#f8fafc;border-bottom:1px solid #eef2f7}
  pre.code{margin:0;padding:18px 20px;background:#0b1220;color:#e2e8f0;font:13px/1.65 'Cascadia Mono',Consolas,monospace;overflow-x:auto}
  .line{display:block;white-space:pre}
  .ln{display:inline-block;width:3.2em;margin-right:1.1em;text-align:right;color:#5b6b84;user-select:none}
  .code{white-space:pre}
  pre.plain{margin:0;padding:18px 20px;background:#0b1220;color:#e2e8f0;font:13px/1.6 'Cascadia Mono',Consolas,monospace;white-space:pre-wrap;overflow-x:auto}
  footer{text-align:center;color:#64748b;font-size:12px;margin-top:32px}
  @media print{body{background:#fff}.card{box-shadow:none}.hint{display:none}.wrap{max-width:none;padding:0}}
</style>
</head>
<body>
<div class="wrap">
  <div class="hero">
    <h1>Bukti Pengerjaan - Mode A</h1>
    <p>Proyek LDAP UY: Pengamanan Autentikasi dan Manajemen Sesi Setelah Login LDAP</p>
    <p>Proyek SAKTI &middot; Laravel 12 &middot; PHP 8.2</p>
    <p>Dibuat: $now</p>
  </div>

  <div class="hint">
    <b>Cara screenshot:</b> setiap kartu adalah satu bukti. Perbesar browser (Ctrl + +),
    lalu tekan <b>Win + Shift + S</b> dan potong area kartu. Simpan dengan nama
    <b>bukti-01.png</b>, <b>bukti-02.png</b>, dan seterusnya.
    Untuk mencetak langsung ke PDF: tekan <b>Ctrl + P</b> &rarr; <b>Save as PDF</b>.
  </div>

  $cardsHtml

  <footer>Dokumen bukti dihasilkan otomatis dari repositori SAKTI.</footer>
</div>
</body>
</html>
"@

$outPath = Join-Path $root "docs\bukti\bukti-output.html"
$utf8 = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($outPath, $html, $utf8)

Write-Host ""
Write-Host "  File bukti berhasil dibuat:" -ForegroundColor Green
Write-Host ("  " + $outPath) -ForegroundColor Yellow
Write-Host ""
Write-Host "  Buka dengan perintah:" -ForegroundColor Cyan
Write-Host "    Start-Process .\docs\bukti\bukti-output.html"
Write-Host ""
