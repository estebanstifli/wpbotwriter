@echo off

:: Define la nueva versión
set VERSION=1.3.0

:: Ruta al archivo readme.txt
set README_PATH=readme.txt

:: Reemplaza la versión en el archivo readme.txt
"C:\Program Files\Git\usr\bin\sed.exe" -i "s/^Stable tag: .*/Stable tag: %VERSION%/" %README_PATH%

:: Verifica que la versión ha sido actualizada
echo Nueva versión establecida en %VERSION% en el archivo readme.txt

:: Añade el cambio al staging de Git
git add %README_PATH%

:: Crear el commit
git commit -m "Actualización a la versión %VERSION%. Cambios reflejados en readme.txt."

:: Subir los cambios a la rama principal (master)
git push Github master

:: Crear y subir el tag
git tag -a %VERSION% -m "Versión %VERSION%"
git push Github %VERSION%

pause
