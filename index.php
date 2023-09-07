<?php

require_once 'Grapher.php';

header('Content-Type: image/png');

$image_width = isset($_GET['width']) ? intval($_GET['width']) : 1000;
$image_height = isset($_GET['height']) ? intval($_GET['height']) : 300;

$grapher = new Grapher($image_width, $image_height);

$img = $grapher->getimage();

imagepng($img);