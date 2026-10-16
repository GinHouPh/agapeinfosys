@echo off
cd /d "%~dp0"
echo [1/4] Building frontend assets...
call npm run build
if %ERRORLEVEL% NEQ 0 (
  echo Build failed!
  exit /b %ERRORLEVEL%
)
echo [2/4] Force-adding public/build...
git add -f public/build
echo [3/4] Committing...
git commit -m "build: update frontend assets"
echo [4/4] Pushing to origin main...
git push origin main
echo Done.
