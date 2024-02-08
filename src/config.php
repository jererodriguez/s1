<?php
date_default_timezone_set('America/Asuncion');
// BASES DE DATOS -------------------
define("DB_USER", "quattropy_valeriaystock");
define("DB_PSW", "Paraguay22.");
define("DB_BASE", "quattropy_valeriaystock");
    define("DB_CON", "");
	define('ESTADO_SERVIDOR', '1');   
	define('MENSAJE_SERVIDOR', 'En mantenimiento. Intentelo en unos minutos.');
//-----------------------------------

// COMUNES ---------------------------
	define("DOMAIN",@$_SERVER['HTTP_HOST']);
	define("PATH","//s1/public"); // Path to main index from host
	define("HOST", "https://".DOMAIN.(DOMAIN=='localhost' || DOMAIN == 'stock.quattropy.com' ?PATH:''));
	define("SUCURSAL_DEFECTO",1);
    define("URLS",[
          
    ]);
//------------------------------------


// PARTICULARES ---------------------------
	define("COMPANY", " Virtuales");
	define("MISSION","Sistema Integral de Gesti&oacute;n");
	define("TIMEZONE", "America/Asuncion");
	define("EMAIL","");
	define("PHONE","");
	define("FACEBOOK","");
	define("COPYRIGHT","2017");
	define("DEVELOPER","");
	define("DEVWEBPAGE","");
	// define("MONDEDA","Gs");
	define("DECIMALES","0");
	define("COD_GRP_PRO","3");
	//define("MONEDA_BASE","1000"); // UN MIL, UN DOLLAR , UN PESO  ### ESTO ESTA EN DEFAULT.PHP se usan para hacer los cierres ###
	define('MONEDA', array(
        0,',','.','Gs' // Paraguay
        // 2,',','.','$' // Argentina
        )
	);
	define("FECHA","d/m/Y");
	define("HORA","H:i:s");
//-----------------------------------------
//MIDDLEWARE
define("KEY", "123");
define(
    "MIDDLEWARE" , 
    [	
        "path" =>[
            "/api",
            
        ], /* or ["/api", "/admin"] */
        "ignore" => [
            "/api/stock",
            "/api/stock/getSerie",
        ], 
    ]
);
//-----------------------------------------
//RUTAS 
define("RUTAS",[
    '/logica/stock/'
]);
//-----------------------------------------


