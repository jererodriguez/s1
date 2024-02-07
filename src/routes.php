<?php
// ruta para negocio fanatico
    $thefolder = __DIR__."/";
    if(count(RUTAS)){
        // print_r(RUTAS);
        foreach (RUTAS as $key => $value) {
            // var_dump($value);
            $ruta = __DIR__.$value;
            if ($handler = opendir($ruta)) {
                while (false !== ($file = readdir($handler))) {
                    // echo $file ."<br>";
                    if( $file != "" ){
                        @include_once( $ruta.$file );
                    }
                }
                closedir($handler);
            }
        }

    }
// fin