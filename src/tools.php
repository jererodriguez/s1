<?php

    class Event 
    {
        private static $events = [];
    
        public static function listen($name, $callback) {
            self::$events[$name][] = $callback;
        }
    
        public static function trigger($name, $argument = null) {
            foreach (self::$events[$name] as $event => $callback) {
                if($argument && is_array($argument)) {
                    call_user_func_array($callback, $argument);
                }
                elseif ($argument && !is_array($argument)) {
                    call_user_func($callback, $argument);
                }
                else {
                    call_user_func($callback);
                }
            }
        }

        public static function configDefaultEvents()
        {
            // Event::listen('login', function(){
            //     echo 'Event user login fired! <br>';
            // });

            // Event::listen('logout', function($param){
            //     echo 'Event '. $param .' logout fired! <br>';
            // });

            // Event::listen('loginUser', function(){
               
            // });


            
        }
    }

    class SendMail
    {
        public $_mailer;
        public function __construct()
        {
            $opt['ssl']['verify_peer'] = FALSE;
            $opt['ssl']['verify_peer_name'] = FALSE;
            $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587,'TLS'))
	        ->setUsername('')
            ->setPassword('')
            ->setStreamOptions($opt);
			;
			// Create the Mailer using your created Transport
            $this->_mailer = new Swift_Mailer($transport);
            
            // $transport = (new Swift_SmtpTransport('mail.proinso.sa.com', 465,'SSL'))
            // ->setUsername('developer@proinso.sa.com')
            // ->setPassword('Tincho2020')
            // ;
            // // Create the Mailer using your created Transport
            // $this->_mailer = new Swift_Mailer($transport);
        }

        public function Send(string $title, array $from, array $to, string $body )
        {
            $message = (new Swift_Message($title))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body, 'text/html')
            ;
            // Send the message
            return $this->_mailer->send($message);
        }
    }

    class ServidorEmail
    {
        public static function MaqBloqueada($fecha, $codMaquina, $rucCliente, $sectorError)
        {
            $cuerpo =
            '<h1 style="font-family:Arial;font-size:36px;line-height:44px;padding-top:10px;padding-bottom:10px">Informacion</h1>
            <p><b>Categoria:</b> Bloqueo de puesto por validacion de datos.</p><br>
            <p><b>Fecha de Registro:</b> '.$fecha.'.</p><br>
            <p><b>Codigo Maquina:</b> '.$codMaquina.'.</p><br>
            <p><b>Cedula Identidad Usuario:</b> '.$rucCliente.'.</p><br>
            <p><b>Modulo Error:</b> '.$sectorError.'.</p><br>
            <p><b>Internal ID Error:</b> '.$sectorError.'.</p><br>';

            $mensaje = self::FormatoMensaje($cuerpo);

            $title = '📌 Puestos Comodin';
            $from = ['developer@proinso.sa.com' => 'Sistema'];
            $to = REPORTES_CORREOS;
            $body = $mensaje;
            $SendMail = new SendMail();
            $SendMail->Send($title,$from,$to,$body);
        }

        public static function UsuarioNuevo($fecha, $rucCliente)
        {
            $cuerpo =
            '<h1 style="font-family:Arial;font-size:36px;line-height:44px;padding-top:10px;padding-bottom:10px">Informacion</h1>
            <p><b>Categoria: </b>Registro Usuario.</p><br>
            <p><b>Fecha de Registro: </b>'.$fecha.'.</p><br>
            <p><b>Cedula Identidad Usuario: </b>'.$rucCliente.'.</p><br>';

            $mensaje = self::FormatoMensaje($cuerpo);
            $title = '📌 Interesados Comodin';
            $from = ['developer@proinso.sa.com' => 'Sistema'];
            $to = REPORTES_CORREOS;
            $body = $mensaje;
            $SendMail = new SendMail();
            $SendMail->Send($title,$from,$to,$body);

        }

        public static function ValidacionFotoUsuario($fecha, $rucCliente)
        {
            $cuerpo =
            '<h1 style="font-family:Arial;font-size:36px;line-height:44px;padding-top:10px;padding-bottom:10px">Informacion</h1>
            <p><b>Categoria: </b> Validacion Identidad Del Usuario.</p><br>
            <p><b>Fecha de Registro: </b>'.$fecha.'.</p><br>
            <p><b>Cedula Identidad Usuario: </b>'.$rucCliente.'.</p><br>';

            $mensaje = self::FormatoMensaje($cuerpo);

            $title = '📌 Interesados Comodin';
            $from = ['developer@proinso.sa.com' => 'Sistema'];
            $to = REPORTES_CORREOS;
            $body = $mensaje;
            $SendMail = new SendMail();
            $SendMail->Send($title,$from,$to,$body);

        }

        public static function AutoExclusionUsuario($fecha, $rucCliente, $fechaFinalAutoExclusion)
        {
            $cuerpo =
            '<h1 style="font-family:Arial;font-size:36px;line-height:44px;padding-top:10px;padding-bottom:10px">Informacion</h1>
            <p><b>Categoria: </b>Usuario AutoExcluido.</p><br>
            <p><b>Fecha de Registro: </b>'.$fecha.'.</p><br>
            <p><b>Cedula Identidad Usuario: </b>'.$rucCliente.'.</p><br>
            <p><b>Fecha fin AutoExclusion: </b>'.$fechaFinalAutoExclusion.'.</p><br>';

            $mensaje = self::FormatoMensaje($cuerpo);

            $title = '📌 Interesados Comodin';
            $from = ['developer@proinso.sa.com' => 'Sistema'];
            $to = REPORTES_CORREOS;
            $body = $mensaje;
            $SendMail = new SendMail();
            $SendMail->Send($title,$from,$to,$body);
        }

        public static function Soporte( $fecha, $rucCliente, $tema)
        {
            $cuerpo =
            '<h1 style="font-family:Arial;font-size:36px;line-height:44px;padding-top:10px;padding-bottom:10px">Informacion</h1>
            <p><b>Categoria: </b>Soporte.</p><br>
            <p><b>Fecha de Registro: </b> '.$fecha.'.</p><br>
            <p><b>Cedula Identidad Usuario: </b> '.$rucCliente.'.</p><br>
            <p><b>Tema: </b> '.$tema.'.</p><br>';

            $mensaje = self::FormatoMensaje($cuerpo);

            $title = '📌Soporte Comodin';
            $from = ['developer@proinso.sa.com' => 'Sistema'];
            $to = REPORTES_CORREOS;
            $body = $mensaje;
            $SendMail = new SendMail();
            $SendMail->Send($title,$from,$to,$body);

        }

        public static function AlertaTopeBanca($fecha,$netwin)
        {
            $cuerpo =
            '<h1 style="font-family:Arial;font-size:36px;line-height:44px;padding-top:10px;padding-bottom:10px">Informacion</h1>
            <p><b>Categoria: </b>Tope De Banca.</p><br>
            <p><b>Fecha de Registro: </b> '.$fecha.'.</p><br>
            <p><b>Netwin: </b> '.$netwin.'.</p><br>
            Ha alcanzado el limite de tope de banca<br>
            ';

            $mensaje = self::FormatoMensaje($cuerpo);

            $title = '📌Tope Banca';
            $from = ['developer@proinso.sa.com' => 'Sistema'];
            $to = REPORTES_CORREOS;
            $body = $mensaje;
            $SendMail = new SendMail();
            $SendMail->Send($title,$from,$to,$body);
        }

        public static function PeticionCargaExtraBilletera($fecha, $rucCliente, $tipoTransaccion, $empresa)
        {
            $cuerpo =
            '<h1 style="font-family:Arial;font-size:36px;line-height:44px;padding-top:10px;padding-bottom:10px">Informacion</h1>
            <p><b>Categoria: </b>Transaccion Billetera.</p><br>
            <p><b>Fecha de Registro: </b> '.$fecha.'.</p><br>
            <p><b>Cedula Identidad Usuario: </b> '.$rucCliente.'.</p><br>
            <p><b>Tipo Transaccion: </b> '.$tipoTransaccion.'.</p><br>
            <p><b>Empresa: </b> '.$empresa.'.</p><br>';

            $mensaje = self::FormatoMensaje($cuerpo);

            $title = '📌Transacciones Comodin';
            $from = ['developer@proinso.sa.com' => 'Sistema'];
            $to = REPORTES_CORREOS;
            $body = $mensaje;
            $SendMail = new SendMail();
            $SendMail->Send($title,$from,$to,$body);
        }

        public static function AlertaPdv($fecha, $rucCliente, $codPdv, $alerta)
        {
            $cuerpo =
            '<h1 style="font-family:Arial;font-size:36px;line-height:44px;padding-top:10px;padding-bottom:10px">Informacion</h1>
            <p><b>Categoria:</b> Bloqueo de puesto por validacion de datos.</p><br>
            <p><b>Fecha de Registro:</b> '.$fecha.'.</p><br>
            <p><b>Codigo Pdv:</b> '.$codPdv.'.</p><br>
            <p><b>Ultimo cliente en Transaccion:</b> '.$rucCliente.'.</p><br>
            <p><b>Alerta Descripcion:</b> '.$alerta.'.</p><br>';

            $mensaje = self::FormatoMensaje($cuerpo);

            $title = '📌Puestos Comodin';
            $from = ['developer@proinso.sa.com' => 'Sistema'];
            $to = REPORTES_CORREOS;
            $body = $mensaje;
            $SendMail = new SendMail();
            $SendMail->Send($title,$from,$to,$body);
        }

        public static function FormatoMensaje($cuerpo)
        {
            $formato = '<html>
                <style type="text/css">
                    @media all and (max-width: 599px) {
                        .container600 {
                            width: 100%;
                        }
                    }
                </style>
                </head>
                <body style="background-color:#F4F4F4;">


                <table width="100%" cellpadding="0" cellspacing="0" style="min-width:100%;">
                    <tr>
                    <td width="100%" style="min-width:100%;background-color:#F4F4F4;padding:10px;">
                        <center>
                        <table class="container600" cellpadding="0" cellspacing="0" width="600" style="margin:0 auto;">
                            <tr>
                            <td width="100%" style="text-align:left;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="min-width:100%;">
                                <tr>
                                    <td width="100%" style="min-width:100%;background-color:#000000;color:#000000;padding:30px;">
                                    <center>
                                        <img alt="" src="https://local.quattropy.com/flexible.tools2/server_webv4/public/comodin.png" width="210" style="display: block;" />
                                    </center>
                                    </td>
                                </tr>
                                </table>
                                <table width="100%" cellpadding="0" cellspacing="0" style="min-width:100%;">
                                <tr>
                                    <td width="100%" style="min-width:100%;background-color:#F8F7F0;color:#58585A;padding:30px;">
                                    '.$cuerpo.'
                                    </td>
                                </tr>
                                </table>
                                <table width="100%" cellpadding="0" cellspacing="0" style="min-width:100%;">
                                <tr>
                                    <td width="100%" style="min-width:100%;background-color:#58585A;color:#FFFFFF;padding:30px;">
                                    <p style="font-size:16px;line-height:20px;font-family:Georgia,Arial,sans-serif;text-align:center;">2019 @ COPYRIGHT - PROINSO S.A</p>
                                    </td>
                                </tr>
                                </table>
                            </td>
                            </tr>
                        </table>
                        </center>
                    </td>
                    </tr>
                </table>
                </body>';

            return $formato;
        }
    }

	class Util
    {
       /**
		* Metodo para sentencias sql
		*
		* @param PDO $db 'Coneccion de base de datos'
		* @param string $query 'sentencia sql'
		* @param array $data 'datos por arreglo de condiciones'
		* @param string $type 'traer una fila o todas fecht/fecthAll solo funciona para los select'
		* @param string $fetchStyle 'tipo FETCH_OBJ... ect. solo funciona para los select'
		* @return array 'retorna una arreglo'
		*/

		public static function executeQuery(PDO $db, string $query, array $data = [], string $type = "",  string $fetchStyle = "FETCH_OBJ") : array
        {
			$resultado['data'] = null;
			$númargs = func_num_args();
            try {
				if($númargs != 2){
					$params = array();
					preg_match_all('/:\w+/', $query, $matches);

					if(count($matches[0]) != count($data)){
						throw new PDOException('Incomplete Parameters: '.json_encode($data).' / '.json_encode($matches[0])  );
					}

					foreach($matches[0] as $param) {
						$paramName = substr($param, 1);
						if(!isset($data[$paramName])){
							throw new PDOException('Undefined index: '.json_encode($data) );
						}
						$params[$param] = $data[$paramName] ??  null;
					}
				}

				//Pepare the SQL statement
                if(($stmt =  $db->prepare($query)) !== false) {
					if($númargs != 2){
						//Bind all parameters
						foreach($params as $param => $value) {
							$stmt->bindValue($param, $value);
						}
					}
                    //Execute the statement
					$stmt->execute();
					$type = explode(" " , trim($query) );
					switch ($type[0]) {
						case "insert":
						case "INSERT":
							$resultado['data'] = $db->lastInsertId();
							break;
						case "SELECT":
						case "select":
							$result = "";
                            $fetchStyle;
							switch ($fetchStyle) {
								case 'FETCH_ASSOC':
									if($type == "ALL"){
										$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
									}else{
										$result = $stmt->fetch(PDO::FETCH_ASSOC);
									}
									break;
								case 'FETCH_OBJ':
									if($type == "ALL"){
										$result = $stmt->fetchAll(PDO::FETCH_OBJ);
									}else{
										$result = $stmt->fetch(PDO::FETCH_OBJ);
									}
									break;

								default:
									if($type == "ALL"){
										$result = $stmt->fetchAll(PDO::FETCH_OBJ);
									}else{
										$result = $stmt->fetch(PDO::FETCH_OBJ);
									}
									break;
							}
							if(!$result){
								$result = [];
							}
                            // print_r($result);
							$resultado['data'] =  $result;
							// $resultado['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
							break;
						default:
							$resultado['data'] =  $stmt->rowCount();
							break;
					}
                }
            }
            catch(PDOException $e){
                $resultado['error'] = $e->getMessage();
			}
            return $resultado;
        }
                
        /**
         * historialGanadores
         *
         * @param  mixed $db
         * @param  mixed $ciCliente
         * @param  mixed $tipo
         * @param  mixed $fechaHora
         * @param  mixed $idJuego
         * @param  mixed $codMaquina
         * @param  mixed $msj 
         * @param  mixed $lastId 
         * @return void
         */
        public static function historialGanadores( PDO $db,$id_cliente,$tipo,$fechaHora,$idJuego,$codMaquina,$msj,$lastId )
        {
            self::executeQuery( $db , "INSERT ".DB_MBO.".historial_ganadores(
                id_cliente,
                tipo_hg,
                fecha_hg,
                id_juego,
                cod_maquina,
                descripcion,
                referencia
            )
            VALUES
                (
                '$id_cliente',
                '$tipo',
                '$fechaHora',
                '$idJuego',
                '$codMaquina',
                '$msj',
                '$lastId'
                );
            " );
        }

    }
function Aud()
{
    $aud = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // $aud = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        // $aud = $_SERVER['REMOTE_ADDR'];
    }
    $aud .= @$_SERVER['HTTP_USER_AGENT'];
    $aud .= gethostname();
    return sha1($aud);
}

function generateRandomString($length = 5, $tipo = 4)
{
    switch ($tipo) {
        case '4':
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
            break;

        case '2':
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;

        case '1':
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;

        case '0':
            $characters = '0123456789';
            break;
    }
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Undocumented function
 * un arcchivos
 * @param [type] $name
 * @param [type] $target
 * @param [type] $targetContent
 * @param boolean $comprime
 * @param string $nameFile
 * @return void
 */
function uploadFile($name, $target, $targetContent, $comprime = false, $nameFile = "") // para formada
{
    try {

        $respuestas = [];
        $nombre_archivo = "";
        // COMPROBACIÓN INICIAL ANTES DE CONTINUAR CON EL PROCESO DE UPLOAD
        // **********************************************************************
        // Si no se ha llegado ha definir el array global $_FILES, cancelaremos el resto del proceso
        if (empty($_FILES[$name])) {
            // Devolvemos un array asociativo con la clave error en formato JSON como respuesta
            // Cancelamos el resto del script
            return ['error' => 'No hay ficheros para realizar upload.'];
        }

        // DEFINICIÓN DE LAS VARIABLES DE TRABAJO (CONSTANTES, ARRAYS Y VARIABLES)
        // ************************************************************************

        // Definimos la constante con el directorio de destino de las descargas

        $targetLg = __DIR__ . "/../public/img/$target/lg/$targetContent/";
        $targetMd = __DIR__ . "/../public/img/$target/md/$targetContent/";
        $targetSm = __DIR__ . "/../public/img/$target/sm/$targetContent/";
        // Obtenemos el array de ficheros enviados
        $ficheros = $_FILES[$name];
        // Establecemos el indicador de proceso correcto (simplemente no indicando nada)
        $estado_proceso = NULL;
        // Paths para almacenar
        $paths = array();
        // Obtenemos los nombres de los ficheros
        $nombres_ficheros = $ficheros['name'];
        $nuevos_nombres = [];

        // LÍNEAS ENCARGADAS DE REALIZAR EL PROCESO DE UPLOAD POR CADA FICHERO RECIBIDO
        // ****************************************************************************

        // Si no existe la carpeta de destino la creamos
        if (!file_exists($targetLg)) @mkdir($targetLg);
        // Sólo en el caso de que exista esta carpeta realizaremos el proceso
        if (file_exists($targetLg)) {
            // Recorremos el array de nombres para realizar proceso de upload
            for ($i = 0; $i < count($nombres_ficheros); $i++) {
                // Extraemos el nombre y la extensión del nombre completo del fichero
                $nombre_extension = explode('.', basename($ficheros['name'][$i]));
                // Obtenemos la extensión
                $extension = array_pop($nombre_extension);
                // Obtenemos el nombre
                $nombre = str_replace(" ", "-", array_pop($nombre_extension) . "_" . time());

                if ($nameFile != "") {
                    $nombre = $nameFile;
                }
                // Creamos la ruta de destino
                $archivo_destino = $targetLg . DIRECTORY_SEPARATOR . utf8_decode($nombre) . '.' . $extension;

                $nuevos_nombres[] = $nombre . '.' . $extension;

                $targetSm = __DIR__ . "/../public/img/$target/sm/$targetContent/$nombre.$extension";
                $targetMd = __DIR__ . "/../public/img/$target/md/$targetContent/$nombre.$extension";

                // Mover el archivo de la carpeta temporal a la nueva ubicación
                move_uploaded_file($ficheros['tmp_name'][$i], $archivo_destino);
                // if (move_uploaded_file($ficheros['tmp_name'][$i], $archivo_destino)) {
                //     /* read the source image */

                //     $src = $archivo_destino;
                //     $dest = $targetSm;
                //     $desired_width = 90;
                //     // if ($extension == 'png') {
                //     //     $source_image = @imagecreatefrompng($src);
                //     // } else {
                //     //     $source_image = @imagecreatefromjpeg($src);
                //     // }
                //     $width = imagesx($source_image);
                //     $height = imagesy($source_image);
                //     /* find the "desired height" of this thumbnail, relative to the desired width  */
                //     $desired_height = floor($height * ($desired_width / $width));
                //     /* create a new, "virtual" image */
                //     $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
                //     /* preserve transparency if png */
                //     if ($extension == 'png') {
                //         imagealphablending($virtual_image, FALSE);
                //         imagesavealpha($virtual_image, TRUE);
                //         /* copy source image at a resized size */
                //         imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
                //         /* create the physical thumbnail image to its destination */
                //         imagepng($virtual_image, $dest);
                //     } else {
                //         /* copy source image at a resized size */
                //         imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
                //         /* create the physical thumbnail image to its destination */
                //         imagejpeg($virtual_image, $dest);
                //     }

                //     // $nombre_archivo = "";
                //     // Activamos el indicador de proceso correcto
                //     $estado_proceso = true;
                //     // Almacenamos el nombre del archivo de destino
                //     $paths[] = $archivo_destino;

                //     $src = $archivo_destino;
                //     $dest = $targetMd;
                //     $desired_width = 350;
                //     if ($extension == 'png') {
                //         $source_image = @imagecreatefrompng($src);
                //     } else {
                //         $source_image = @imagecreatefromjpeg($src);
                //     }
                //     $width = imagesx($source_image);
                //     $height = imagesy($source_image);
                //     /* find the "desired height" of this thumbnail, relative to the desired width  */
                //     $desired_height = floor($height * ($desired_width / $width));
                //     /* create a new, "virtual" image */
                //     $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
                //     /* preserve transparency if png */
                //     if ($extension == 'png') {
                //         imagealphablending($virtual_image, FALSE);
                //         imagesavealpha($virtual_image, TRUE);
                //         /* copy source image at a resized size */
                //         imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
                //         /* create the physical thumbnail image to its destination */
                //         imagepng($virtual_image, $dest);
                //     } else {
                //         /* copy source image at a resized size */
                //         imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
                //         /* create the physical thumbnail image to its destination */
                //         imagejpeg($virtual_image, $dest);
                //     }

                //     // $nombre_archivo = "";
                //     // Activamos el indicador de proceso correcto
                //     $estado_proceso = true;
                //     // Almacenamos el nombre del archivo de destino
                //     $paths[] = $archivo_destino;
                // } else {
                //     // Activamos el indicador de proceso erroneo
                //     $estado_proceso = false;
                //     // Rompemos el bucle para que no continue procesando ficheros
                //     break;
                // }
            }
        } else {

            return ['error' => 'No existe la carpeta. ' . $targetLg];
        }
        // PREPARAR LAS RESPUESTAS SOBRE EL ESTADO DEL PROCESO REALIZADO
        // **********************************************************************

        // Comprobamos si el estado del proceso a finalizado de forma correcta
        if ($estado_proceso === true) {
            /* Podríamos almacenar información adicional en una base de datos
                con el resto de los datos enviados por el método POST */

            // Como mínimo tendremos que devolver una respuesta correcta por medio de un array vacio.
            $respuestas = ['dirupload' => basename($targetLg), 'name' => $nuevos_nombres, 'total' => count($paths)];
            /* Podemos devolver cualquier otra información adicional que necesitemos por medio de un array asociativo
                Por ejemplo, prodríamos devolver la lista de ficheros subidos de esta manera:
                    $respuestas = ['ficheros' => $paths];
                Posteriormente desde el evento fileuploaded del plugin iríamos mostrando el array de ficheros utilizando la propiedad response
                del parámetro data:
                respuesta = data.response;
                respuesta.ficheros.forEach(function(nombre) {alert(nombre); });
            */
        } elseif ($estado_proceso === false) {
            $respuestas = ['error' => 'Error al subir los archivos. Póngase en contacto con el administrador del sistema'];
            // Eliminamos todos los archivos subidos
            foreach ($paths as $fichero) {
                unlink($fichero);
            }
            // Si no se han llegado a procesar ficheros $estado_proceso seguirá siendo NULL
        } else {
            $respuestas = ['error' => 'No se ha procesado ficheros.'];
        }

        // RESPUESTA DEVUELTA POR EL SCRIPT EN FORMATO JSON
        // **********************************************************************

        // Devolvemos el array asociativo en formato JSON como respuesta
        return $respuestas;
        //code...
    } catch (\Throwable $th) {
        throw $th;
    }
}

function saveImage($base64Image, $imageName, $format = 'png') {
    // Separate base64 data from the header
    $imageData = explode(',', $base64Image)[1];

    // Decode image from base64
    $imgData = base64_decode($imageData);

    // Determine the image format based on the output file format
    $imageType = 'png';
    if ($format === 'jpg') {
        $imageType = 'jpeg';
    }

    // Save large image
    $lgImage = __DIR__ . '/../public/img/stock/lg/productos/' . $imageName . '.' . $imageType;
    file_put_contents($lgImage, $imgData);
    $lgImageSize = getimagesize($lgImage);
    if ($lgImageSize[0] > 1920 || $lgImageSize[1] > 1920) {
        // Resize image if necessary
        resizeImage($lgImage, 1920, 1920);
    }

    // Save medium image
    $mdImage = __DIR__ . '/../public/img/stock/md/productos/' . $imageName . '.' . $imageType;
    file_put_contents($mdImage, $imgData);
    $mdImageSize = getimagesize($mdImage);
    if ($mdImageSize[0] > 1000 || $mdImageSize[1] > 1000) {
        // Resize image if necessary
        resizeImage($mdImage, 1000, 1000);
    }

    // Save small image
    $smImage = __DIR__ . '/../public/img/stock/sm/productos/' . $imageName . '.' . $imageType;
    $im = imagecreatefromstring($imgData);
    $smIm = imagescale($im, 42, 42, IMG_BICUBIC_FIXED);
    imagedestroy($im);
    if ($format === 'jpg') {
        imagejpeg($smIm, $smImage, 90);
    } else {
        imagepng($smIm, $smImage, 9);
    }
    imagedestroy($smIm);

    return [$lgImage, $mdImage, $smImage];
}


function resizeImage($filename, $maxWidth, $maxHeight) {
    // Get the size of the original image
    $size = getimagesize($filename);
    $width = $size[0];
    $height = $size[1];

    // Check if image needs resizing
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return $filename;
    }

    // Calculate the new dimensions
    $aspectRatio = $width / $height;
    $newWidth = min($maxWidth, $width);
    $newHeight = min($maxHeight, $height);

    if ($newWidth / $newHeight > $aspectRatio) {
        $newWidth = round($newHeight * $aspectRatio);
    } else {
        $newHeight = round($newWidth / $aspectRatio);
    }

    // Create a new image with the new dimensions
    $image = imagecreatetruecolor($newWidth, $newHeight);

    // Load the original image
    $origImage = imagecreatefromjpeg($filename);

    // Resize the original image to the new dimensions
    imagecopyresampled($image, $origImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save the resized image as a new file with the same name as the original file
    $resizedFilename = $filename;
    imagejpeg($image, $resizedFilename, 90);

    // Clean up the images
    imagedestroy($origImage);
    imagedestroy($image);

    // Delete the original file
    unlink($filename);

    // Return the filename of the resized image
    return $resizedFilename;
}


 function uploadFiles($name, $target, $targetContent, $comprime = false, $nameFile = "") {
    $targetDirectory = __DIR__ . "/../public/img/$target/";
    $subdirectories = ["lg", "md", "sm"];

    if (!isset($_FILES[$name])) {
        return ['error' => 'No hay ficheros para realizar upload.'];
    }

    $files = $_FILES[$name];
    $uploadedFiles = [];
    for ($i = 0; $i < count($files['name']); $i++) {
        $nameExtension = explode('.', basename($files['name'][$i]));
        $extension = array_pop($nameExtension);
        $name = str_replace(" ", "-", array_pop($nameExtension)) . "_" . time();
        if ($nameFile != "") {
            $name = str_replace('.png', '', $nameFile) . '.png';
        }
        $newFileName = $name . '.' . $extension;
        $subdirectoriesPaths = [];
        foreach ($subdirectories as $subdirectory) {
            $subdirectoryPath = $targetDirectory . $subdirectory . "/" . $targetContent;
            if (!file_exists($subdirectoryPath)) {
                mkdir($subdirectoryPath, 0777, true);
            }
            $subdirectoriesPaths[$subdirectory] = $subdirectoryPath . "/" . $newFileName;
        }
        $uploadedFiles[] = $subdirectoriesPaths;

        $srcImage = ($extension == 'png') ? imagecreatefrompng($files['tmp_name'][$i]) : imagecreatefromjpeg($files['tmp_name'][$i]);
        $srcWidth = imagesx($srcImage);
        $srcHeight = imagesy($srcImage);
        $desiredWidth = 90;
        $desiredHeight = floor($srcHeight * ($desiredWidth / $srcWidth));

        foreach ($subdirectoriesPaths as $key => $path) {
            $virtualImage = imagecreatetruecolor($desiredWidth, $desiredHeight);
            if ($extension == 'png') {
                imagealphablending($virtualImage, false);
                imagesavealpha($virtualImage, true);
            }
            imagecopyresampled($virtualImage, $srcImage, 0, 0, 0, 0, $desiredWidth, $desiredHeight, $srcWidth, $srcHeight);
            if ($extension == 'png') {
                imagepng($virtualImage, $path, 5);
            } else {
                imagejpeg($virtualImage, $path, 50);
            }
            imagedestroy($virtualImage);
        }
        imagedestroy($srcImage);
    }

    return $uploadedFiles;
}


function validateEmail($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return true;
}

//--- MONEDA
function moneda_local($valor, $moneda = "")
{
    if ($moneda == "") {
        if ($valor > 0 || $valor < 0) {
            return number_format($valor, MONEDA[0], MONEDA[1], MONEDA[2]);
        } else {
            return 0;
        }
    } elseif ($moneda == "usd_") {
        if ($valor > 0 || $valor < 0) {
            return number_format($valor, 2, '.', ',');
        } else {
            return 0;
        }
    }
}


function compressImage($source, $destination, $quality) { 
    // Get image info 
    $imgInfo = getimagesize($source); 
    $mime = $imgInfo['mime']; 
     
    // Create a new image from file 
    switch($mime){ 
        case 'image/jpeg': 
            $image = imagecreatefromjpeg($source); 
            break; 
        case 'image/png': 
            $image = imagecreatefrompng($source); 
            break; 
        case 'image/gif': 
            $image = imagecreatefromgif($source); 
            break; 
        default: 
            $image = imagecreatefromjpeg($source); 
    } 
     
    // Save image 
    imagejpeg($image, $destination, $quality); 
     
    // Return compressed image 
    return $destination; 
} 