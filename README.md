# Statkraft
Statkraft conecta con el futuro

La carpeta backend contiene el codigo PHP para los Endpoints y el CMS, se copia tal cual al servidor destino y se configura el webserver para que tenga como carpeta publica la carpeta "public". Se realizan las configuraciones en los archivos "config/\*.php" que sean necesarios. 
Los archivos sql contienen la estructura inicial de la base de datos. Se ejecuta primero el mas pequeño y luego el segundo, en caso de error por la linea de create database, ignorarlo, y revisar que se hayan llenado las tablas de RECYCLE_OPTIONS y UBIGEO con informacion.

La carpeta Frontend contiene la web publica. Para iniciar ejecutar
```
npm install
```
para instalar todas las dependencias, luego de eso se puede usar
```
npm run dev
```
para ejecutar la web y 
```
npm run production
```
para generar los archivos que deberan ser copiados en sus respectivas carpetas dentro del site y la carpeta "/public".
Para el despliegue en los servidores EPI, se deben copiar todo menos css y html. El css se copia en el editor de EPI tal cual y el HTML se deben reemplazar todas las rutas de imagenes y videos por su URL completa.