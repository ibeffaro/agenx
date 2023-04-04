<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

class Pdf {

    public function generate($html, $filename='', $stream=TRUE, $paper = 'A4', $orientation = "portrait") {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        if(is_array($paper)) {
            $dompdf->setPaper($paper);
        } else {
            $dompdf->setPaper($paper, $orientation);
        }
        $dompdf->render();
        if ($stream) {
            $dompdf->stream($filename.".pdf", array("Attachment" => 0));
        } else {
            return $dompdf->output();
        }
    }

}
