param(
  [string]$Message = "build: update frontend assets"
)
cd $PSScriptRoot
Write-Host "[1/4] Building frontend assets..." -ForegroundColor Cyan
npm run build
if ($LASTEXITCODE -ne 0) {
  Write-Host "Build failed!" -ForegroundColor Red
  exit $LASTEXITCODE
}
Write-Host "[2/4] Force-adding public/build..." -ForegroundColor Cyan
git add -f public/build
Write-Host "[3/4] Committing..." -ForegroundColor Cyan
git commit -m $Message
Write-Host "[4/4] Pushing to origin main..." -ForegroundColor Cyan
git push origin main
Write-Host "Done." -ForegroundColor Green
