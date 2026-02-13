@echo off
cd /d "%~dp0"

set PHP_EXE=php
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set PHP_EXE=C:\xampp\php\php.exe
    ) else (
        echo [ERREUR] PHP introuvable. Utilisez XAMPP ou ajoutez PHP au PATH.
        pause
        exit /b 1
    )
)

if not exist "composer.phar" (
    echo Telechargement de Composer (composer.phar)...
    "%PHP_EXE%" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    if errorlevel 1 (
        echo Erreur telechargement. Telechargez https://getcomposer.org/Composer-Setup.exe et installez Composer.
        pause
        exit /b 1
    )
    "%PHP_EXE%" composer-setup.php --quiet
    "%PHP_EXE%" -r "unlink('composer-setup.php');"
    if not exist "composer.phar" (
        echo Echec installation Composer.
        pause
        exit /b 1
    )
    echo Composer installe.
    echo.
)

echo Installation des dependances PHP (composer install)...
echo.
"%PHP_EXE%" composer.phar install

echo.
echo Termine. Vous pouvez lancer le projet avec run.bat
pause
