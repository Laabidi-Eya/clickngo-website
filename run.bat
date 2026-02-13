@echo off
cd /d "%~dp0"

set PHP_EXE=php
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set PHP_EXE=C:\xampp\php\php.exe
        echo Utilisation de PHP XAMPP : C:\xampp\php\php.exe
    ) else (
        echo [ERREUR] PHP introuvable.
        echo Si vous utilisez XAMPP, verifiez que PHP est dans : C:\xampp\php\
        echo Sinon, ajoutez le dossier de php.exe au PATH Windows.
        pause
        exit /b 1
    )
)

echo ============================================
echo   ClickNGo - Demarrage du serveur PHP
echo ============================================
echo.
echo Serveur demarre sur : http://localhost:8000
echo.
echo Pages principales :
echo   - Activites (front) : http://localhost:8000/mvcact/view/front office/
echo   - Module Activite   : http://localhost:8000/mvcact/
echo   - Front Utilisateur : http://localhost:8000/mvcUtilisateur/View/FrontOffice/
echo.
echo Appuyez sur Ctrl+C pour arreter le serveur.
echo.

start "" "http://localhost:8000/mvcUtilisateur/View/FrontOffice/index.php"

"%PHP_EXE%" -S localhost:8000
