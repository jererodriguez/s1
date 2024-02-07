#!/bin/bash

# Definir las rutas de las carpetas lg, md y sm

lg_dir="/var/www/stock_comodin/s1/public/img/stock/lg/productos"
md_dir="/var/www/stock_comodin/s1/public/img/stock/md/productos"
sm_dir="/var/www/stock_comodin/s1/public/img/stock/sm/productos"

# Iterar a través de las imágenes en la carpeta lg
for img in "$lg_dir"/*.png; do
  # Obtener el nombre de archivo sin la extensión
  filename=$(basename "$img" .png)

  # Verificar si la imagen no está en md
  if [ ! -f "$md_dir/$filename.png" ]; then
    # Comprimir la imagen a 512px de ancho y guardarla en md
    convert "$img" -resize 512x "$md_dir/$filename.png"
    echo "Imagen guardada en $md_dir: $filename.png"
  fi

  # Verificar si la imagen no está en sm
  if [ ! -f "$sm_dir/$filename.png" ]; then
    # Comprimir la imagen a 42x42px y guardarla en sm
    convert "$img" -resize 42x42 "$sm_dir/$filename.png"
    echo "Imagen guardada en $sm_dir: $filename.png"
  fi
done

# Contar la cantidad de imágenes en md y sm y mostrar el resultado
md_count=$(ls "$md_dir"/*.png 2>/dev/null | wc -l)
sm_count=$(ls "$sm_dir"/*.png 2>/dev/null | wc -l)
echo "Cantidad de imágenes guardadas en $md_dir: $md_count"
echo "Cantidad de imágenes guardadas en $sm_dir: $sm_count"

