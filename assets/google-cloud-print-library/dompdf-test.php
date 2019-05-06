<?php

use Dompdf\Dompdf;

require_once("vendor/autoload.php");

$html =
  '<html><body>'.
  '<p>Put your html here, or generate it with your favourite '.
  'templating system.</p>'.
  '</body></html>';

$dompdf = new Dompdf();
$dompdf->load_html($html);
$dompdf->render();
//$dompdf->stream("sample.pdf");
file_put_contents('sample.pdf', $dompdf->output());
