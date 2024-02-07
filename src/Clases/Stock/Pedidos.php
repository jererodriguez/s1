<?php
namespace Clases\Stock;
use \PDO;
// print_r($settings);
class Pedidos
{
    private $log;
    public function __construct($log = null)
    {
        $this->log = $log;
    }
    public static function recepcionPedidos(pdo $db, $id_destinatario): array
    {
        $sql = "
        SELECT
    cpedidos.id_pedido,
    cpedidos.pedido_comentario,
    cpedidos.id_usu_remitente as id_usu_re,
    cpedidos.id_usu_destinatario as id_usu_dest,
    u2.nombre_usu as nombre_dest,
    u2.apellido_usu as apellido_dest,
    cpedidos.pedido_fechahora,
    cpedidos.pedido_fechavencimiento,
    cpedidos.pedido_estado,
    CASE
        WHEN pedido_estado = '-1' THEN 'Anulado'
        WHEN pedido_estado = '0' THEN 'Lista de compras'
        WHEN pedido_estado = '1' THEN 'Pedido de compra'
        WHEN pedido_estado = '2' THEN 'Orden de compra'
        WHEN pedido_estado = '3' THEN 'Cuentas a pagar'
        ELSE '-'
    END as estadodelpedido
    FROM `stock_pedidos_cabecera` cpedidos
    left join stock_usuarios u2 on cpedidos.id_usu_remitente = u2.id_usu
    where id_usu_destinatario = '" . $id_destinatario . "' and cpedidos.pedido_estado = '0';
        ";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }
    public static function getPedidos(pdo $db, $form): array
    {
        $sql = "
        SELECT
    cpedidos.id_pedido,
    cpedidos.pedido_comentario,
    cpedidos.id_usu_remitente as id_usu_re,
    cpedidos.id_usu_destinatario as id_usu_dest,
    u2.nombre_usu as nombre_dest,
    u2.apellido_usu as apellido_dest,
    cpedidos.pedido_fechahora,
    cpedidos.pedido_fechavencimiento,
    cpedidos.pedido_estado,
    CASE
        WHEN pedido_estado = '-1' THEN 'Anulado'
        WHEN pedido_estado = '0' THEN 'Lista de compras'
        WHEN pedido_estado = '1' THEN 'Pedido de compra'
        WHEN pedido_estado = '2' THEN 'Orden de compra'
        WHEN pedido_estado = '3' THEN 'Cuentas a pagar'
        ELSE '-'
    END as estadodelpedido
    FROM `stock_pedidos_cabecera` cpedidos
    left join stock_usuarios u2 on cpedidos.id_usu_destinatario = u2.id_usu
    where id_usu_remitente = '" . $form['id_usuario'] . "' and cpedidos.pedido_estado = '".$form['pedido_estado']."';
        ";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }
    public static function crearNotadePedido(pdo $db, $form)
    {
        $fechahora = date('Y-m-d H:i:s');
        $insertar = "insert into `stock_pedidos_cabecera` (
            `id_usu_remitente`,
            `id_usu_destinatario`,
            `pedido_comentario`,
            `pedido_fechahora`,
            `pedido_fechavencimiento`,
            `pedido_estado`
          )
          values
            (
              '" . $form['id_usu_remitente'] . "',
              '" . $form['id_usu_destinatario'] . "',
              '" . $form['pedido_comentario'] . "',
              '" . $fechahora . "',
              '" . $form['fechadevencimiento'] . "',
              '0'
            );
          ";
        $stmt = $db->prepare($insertar);
        $stmt->execute();
        $id = $db->lastInsertId();
        return $id;
    }

    public static function getNotaDetalles(pdo $db, $idpedido): array
    {
        $sql = "SELECT cpedidos.id_pedido, cpedidos.id_usu_remitente, cpedidos.id_usu_destinatario, cpedidos.pedido_comentario, cpedidos.pedido_fechahora, cpedidos.pedido_fechavencimiento, cpedidos.pedido_observacion, cpedidos.pedido_costototal, cpedidos.pedido_estado, dpedidos.detalle_id, dpedidos.detalle_idpedido, dpedidos.detalle_txt, dpedidos.detalle_cantidad, dpedidos.detalle_estado, u.nombre_usu, u.apellido_usu, u.ci_usu FROM stock_pedidos_cabecera cpedidos inner join stock_pedidos_detalles as dpedidos on (cpedidos.id_pedido = dpedidos.detalle_idpedido) inner join stock_usuarios u on u.id_usu = cpedidos.id_usu_destinatario where cpedidos.id_pedido = '".$idpedido."'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public static function recepcionNotaDetalles(pdo $db, $idpedido): array
    {
        $sql = "SELECT cpedidos.id_pedido, cpedidos.id_usu_remitente, cpedidos.id_usu_destinatario, cpedidos.pedido_comentario, cpedidos.pedido_fechahora, cpedidos.pedido_fechavencimiento, cpedidos.pedido_estado, dpedidos.detalle_id, dpedidos.detalle_idpedido, dpedidos.detalle_txt, dpedidos.detalle_cantidad, dpedidos.detalle_estado, u.nombre_usu, u.apellido_usu, u.ci_usu FROM stock_pedidos_cabecera cpedidos inner join stock_pedidos_detalles as dpedidos on (cpedidos.id_pedido = dpedidos.detalle_idpedido) inner join stock_usuarios u on u.id_usu = cpedidos.id_usu_remitente where cpedidos.id_pedido = '".$idpedido."'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public static function nuevoItemPedido(pdo $db, $form)
    {
        //$fechahora = date('Y-m-d H:i:s');

        $insertar = "INSERT INTO stock_pedidos_detalles (`detalle_idpedido`, `detalle_txt`, `detalle_cantidad`, `detalle_estado`) VALUES ('".$form['idpedido']."', '".$form['detalletxt']."', '".$form['cantidad']."', '0')";

        $stmt = $db->prepare($insertar);
        $stmt->execute();
        $id = $db->lastInsertId();
        return $id;
    }


    public static function editNotaDetalle(pdo $db, $iddetalle, $detalle_txt, $cantidad)
    {
        $sql = "update
        stock_pedidos_detalles
      set
        `detalle_txt` = '$detalle_txt',
        `detalle_cantidad` = '$cantidad'
      where `detalle_id` = '" . $iddetalle . "';
      ";
        $stmt = $db->query($sql);

        return true;
    }

    public static function delnotaPedido(pdo $db, $idnota, $estado_pedido)
    {
        $sql = "SELECT * FROM stock_pedidos_cabecera where pedido_estado = '".$estado_pedido."' and id_pedido = ".$idnota."";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $sql = "DELETE FROM stock_pedidos_detalles WHERE detalle_idpedido = '$idnota'";
            $stmt = $db->query($sql);
            $sql = "DELETE FROM stock_pedidos_cabecera where pedido_estado = '$estado_pedido' and id_pedido = '$idnota'";
            $stmt = $db->query($sql);
            return true;
        } else {
            return false;
        }
    }

    public static function delDetallePedido(pdo $db, $iddetalle)
    {
        $sql = "DELETE FROM stock_pedidos_detalles WHERE detalle_id = '$iddetalle'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return true;
    }

    public static function gestionarPedido(pdo $db, $form)
    {
        $sql = "update
        stock_pedidos_cabecera
      set
        pedido_estado = '".$form['pedido_estado']."',
        pedido_observacion = '".$form['obs']."',
        pedido_fechavencimiento = '".$form['fechadevencimiento']."',

        pedido_costototal = '".$form['costototal']."'

      where `id_pedido` = '" . $form['idnota'] . "';
      ";


        $stmt = $db->query($sql);

        return true;
    }

    public static function solicitarCompra(pdo $db, $form)
    {
        $sql = "update
        stock_pedidos_cabecera
      set
        pedido_estado = '1',
        id_usu_remitente = '".$form['id_usu_remitente']."',
        id_usu_destinatario = '".$form['id_usu_destinatario']."'

      where `id_pedido` = '" . $form['idnota'] . "';
      ";
        $stmt = $db->query($sql);

        return true;
    }

    
}
