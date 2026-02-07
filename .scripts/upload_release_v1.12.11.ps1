$token = $env:GH_TOKEN
$repo = 'easyerpneeko/fagotto-updates'
$tag = 'v1.12.11'
$bodyObj = @{ 
  tag_name = $tag
  name = $tag
  body = 'Release v1.12.11 - fixes'
  draft = $false
  prerelease = $false
}
$body = $bodyObj | ConvertTo-Json
$headers = @{ Authorization = "token $token"; 'User-Agent' = 'fagotto-deployer' }
try {
  $create = Invoke-RestMethod -Method Post -Uri "https://api.github.com/repos/$repo/releases" -Headers $headers -Body $body -ErrorAction Stop
  $upload_url = $create.upload_url -replace '\{\?name,label\}',''
  $file = 'build\\fagotto-erd-aplication Setup 1.12.11.exe'
  if (-not (Test-Path $file)) { Write-Host "ERROR: instalador no encontrado: $file"; exit 2 }
  $filename = [IO.Path]::GetFileName($file)
  Invoke-WebRequest -Uri "$upload_url?name=$filename" -Headers @{ Authorization = "token $token"; 'User-Agent' = 'fagotto-deployer'; 'Content-Type' = 'application/octet-stream' } -InFile $file -Method Post -ErrorAction Stop
  Write-Host 'OK: Release creado y asset subido'
} catch {
  Write-Host 'ERROR:' $_.Exception.Message
  exit 1
}
