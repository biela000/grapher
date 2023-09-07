<?php

class Grapher
{
    private $image_width;
    private $image_height;
    private $image;
    private $colors = array();
    private $PADDING_X = 30;
    private $PADDING_Y = 30;
    private $safe_space = array();
    private $safe_image_width;
    private $safe_image_height;

    public function __construct($image_width, $image_height)
    {
        $this->image_width = $image_width;
        $this->image_height = $image_height;

        $this->definesafespace();

        $this->image = imagecreatetruecolor($image_width, $image_height);

        $this->allocatecolors();
        imagefill($this->image, 0, 0, $this->colors['white']);
        $this->drawaxes();
    }

    private function definesafespace()
    {
        $this->safe_space = array(
            "start_x" => $this->PADDING_X,
            "end_x" => $this->image_width - $this->PADDING_X,
            "start_y" => $this->image_height - $this->PADDING_Y,
            "end_y" => $this->PADDING_Y
        );
        $this->safe_image_width = $this->safe_space["end_x"] - $this->safe_space["start_x"];
        $this->safe_image_height = $this->safe_space["start_y"] - $this->safe_space["end_y"];
    }

    private function allocatecolors()
    {
        $this->colors = array(
            "white" => imagecolorallocate($this->image, 255, 255, 255),
            "black" => imagecolorallocate($this->image, 0, 0, 0),
            "red" => imagecolorallocate($this->image, 255, 0, 0),
            "blue" => imagecolorallocate($this->image, 0, 0, 255),
            "grey" => imagecolorallocate($this->image, 128, 128, 128)
        );
    }

    private function drawaxes()
    {
        // X axis
        $this->drawline(
            $this->safe_space["start_x"],
            $this->safe_space["start_y"],
            $this->safe_space["end_x"],
            $this->safe_space["start_y"],
            $this->colors['black']
        );

        // Y axis
        $this->drawline(
            $this->safe_space["start_x"],
            $this->safe_space["start_y"],
            $this->safe_space["start_x"],
            $this->safe_space["end_y"],
            $this->colors['black']
        );
    }

    private function drawline($x1, $y1, $x2, $y2, $color)
    {
        imageline($this->image, $x1, $y1, $x2, $y2, $color);
    }

    public function getimage()
    {
        return $this->image;
    }
}