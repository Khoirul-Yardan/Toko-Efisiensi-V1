<?php
require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

$generator = new BarcodeGeneratorPNG();
$barcode = $generator->getBarcode('12345678', $generator::TYPE_CODE_128);

header('Content-type: image/png');
echo $barcode;
