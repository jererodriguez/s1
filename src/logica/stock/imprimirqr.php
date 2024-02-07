<?php
date_default_timezone_set(TIMEZONE);
set_time_limit(240);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Imprimirqr;
use \Firebase\JWT\JWT;
use Spipu\Html2Pdf\Html2Pdf;


$app->group('/api/stock', function (\Slim\App $app) {

    $app->get('/getSerie', function (Request $request, Response $response, $args) {
        try {
            $idserie = $request->getParam('idserie');

            $talle = $request->getParam('talle'); // g grande o p pequeño

            if ($talle == 'p') {
                $qrxfila = 24 + 1;
                $ancho = 60;
                $anchoimg = $ancho - 3;
                $copias = 1;
                $orientacion = "L";
                $etiq = '9px';
            } else if ($talle == 'g') {
                $qrxfila = 5 + 1;
                $ancho = 90;
                $anchoimg = $ancho - 3;
                $copias = 2;
                $orientacion = "L";
                $etiq = '14px;';
            }

            $db = $this->db;
            $data = Imprimirqr::getSerie($db, $idserie);

            $html = "";
            if ($data) {
                $i = 1;
                $html = $html . "
                <style>
                table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}</style>
                <table border='1'>";
                foreach ($data as $row) {

                    if ($i == 1) {
                        $html = $html . "<tr>";
                    }
                    if ($i < $qrxfila) {
                        for ($x = 1; $x <= $copias; $x++) {
                                @$html = $html . "
                            <td> <div style='
                            width: " . $ancho . "px;
                            font-size: ".$etiq.";
                            text-align: center;  margin: 0px; padding-bottom:5px; '>
                            <img src='https://stock.quattropy.com/qr/phpqrcode/phpqr.php?qr=" . $row->qr_code . "' width='" . $anchoimg . "px;'  />
                            <span style='margin-top:-5px;'>" . $row->qr_code . "</span>
                            </div></td>";
                        }
                        $i++;
                    }
                    if ($i == $qrxfila) {
                        $html = $html . "</tr>";
                        $i = 1;
                        $cerrado = 1;
                    } else {
                        $cerrado = 0;
                    }
                }
                if ($cerrado == 0) {
                    $html = $html . "</tr>";
                }
                $html = $html . "</table>";
            }

            //echo  $html;

            $html2pdf = new Html2Pdf($orientacion, 'A3', 'es', true, 'UTF-8', array(15, 5, 15, 5));
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($html);
            $response =
                $this->response->withHeader('Content-Disposition', 'inline;filename=PDF_' . date("d-m-Y H:i:s"))
                ->withHeader('Content-type', 'application/pdf');

            $response->write($html2pdf->Output());

            return $response;
        } catch (PDOException $e) {
            throw $e;
        }
    });
});
