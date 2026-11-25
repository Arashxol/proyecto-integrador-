$base = "http://localhost:8000"
function CSRF([string]$html) {
    $m = [regex]::Match($html, 'name="_token" value="([^"]+)"')
    if ($m.Success) { return $m.Groups[1].Value }
    return ""
}

$r1 = Invoke-WebRequest -Uri "$base/login" -UseBasicParsing -SessionVariable sv 2>&1
Write-Host "GET /login: $($r1.StatusCode)"
$tok = CSRF $r1.Content

$body = "email=recepcion@dialab.com&password=password123&_token=$tok"
$r2 = Invoke-WebRequest -Uri "$base/login" -Method Post -Body $body -SessionVariable sv -UseBasicParsing -ErrorAction SilentlyContinue 2>&1
Write-Host "POST /login: $($r2.StatusCode)"

$r3 = Invoke-WebRequest -Uri "$base/qa" -SessionVariable sv -UseBasicParsing -ErrorAction SilentlyContinue 2>&1
Write-Host "GET /qa: $($r3.StatusCode)"
$tokQa = CSRF $r3.Content

$body4 = "email=qa_test@outlook.com&tipo=funcional&suite=smoke_funcional&_token=$tokQa"
$r4 = Invoke-WebRequest -Uri "$base/qa/ejecutar" -Method Post -Body $body4 -SessionVariable sv -UseBasicParsing -ErrorAction SilentlyContinue 2>&1
Write-Host "POST /qa/ejecutar: $($r4.StatusCode)"
if ($r4.StatusCode -eq 200) { Write-Host "JSON OK: $($r4.Content.Substring(0,100))" } else { Write-Host "BODY: $($r4.Content.Substring(0,200))" }
