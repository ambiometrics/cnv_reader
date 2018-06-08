<?php

include_once __DIR__ . '/../vendor/autoload.php';

use edwrodrig\cnv_reader\CnvReader;

$cnv = new CnvReader(__DIR__ . '/files/PIRA001.cnv');

echo "Date : ", $cnv->getHeaders()->getDateTime()->format('Y-m-d'), "\n";
echo "Lat  : ", $cnv->getHeaders()->getCoordinate()->getLat(), "\n";
echo "Lng  : ", $cnv->getHeaders()->getCoordinate()->getLng(), "\n";

foreach ( $cnv->getHeaders()->getMetrics() as $metric ) {
    echo $metric->getName() , "\t";
}
echo "\n";

foreach ( $cnv as $row ) {
    foreach ( $row as $column ) {
        echo $column , "\t";
    }
    echo "\n";
}