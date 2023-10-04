<?php

require_once 'Grapher.php';

if (isset($_GET['debug']) && $_GET['debug'] == 'true') {
    ini_set('display_errors', 1);
}

$image_width = isset($_GET['width']) ? intval($_GET['width']) : 1000;
$image_height = isset($_GET['height']) ? intval($_GET['height']) : 300;

$grapher = new Grapher($image_width, $image_height);

$img = $grapher->getimage();

if (isset($_GET['image']) && $_GET['image'] == 'true') {
    header('Content-Type: image/png');
    imagepng($img);
} else {
    header('Content-Type: application/json');
    echo json_encode($grapher->getdatapoints());
}