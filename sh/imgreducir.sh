#!/bin/bash

# Definir la ruta de la carpeta lg
lg_dir="/var/www/stock_comodin/s1/public/img/stock/lg/productos/"

# Iterar a través de las imágenes en la carpeta lg
for img in "$lg_dir"/*.png; do
  # Obtener el tamaño actual de la imagen en bytes
  size=$(stat -c%s "$img")
  
  # Verificar si la imagen ya pesa menos de 512kb
  if [ $size -le 524288 ]; then
    echo "Imagen ya es menor a 512kb: $img"
    continue
  fi
  
  # Reducir la calidad de la imagen hasta que pese menos de 512kb
  while [ $size -gt 524288 ]; do
    convert "$img" -resize '50%' -strip -quality 80 "$img"
    size=$(stat -c%s "$img")
  done
  
  new_size=$(stat -c%s "$img")
  echo "Imagen reducida y guardada: $img (Nuevo tamaño: $new_size bytes)"
done