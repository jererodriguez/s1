<?php

namespace Clases\Stock;

use \PDO;
class Imprimirqr
{
    
    public static function getSerie(pdo $db, $idserie) : array
    {
        $sql = "SELECT qr_code FROM stock_qrcode_serie serie LEFT JOIN stock_qrcode qr ON serie.`id_serie` = qr.`idserie` WHERE qr.`estado_qrcode` = '0' AND serie.`id_serie` = '$idserie';";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

}
?>