<?php

require_once 'GrapherDB.php';

class Grapher
{
    const PADDING_X = 70;
    const PADDING_Y = 30;
    const LABEL_FONT_SIZE = 5;
    const VALUE_INDICATOR_SIZE = 5;
    const DOT_SIZE = 5;
    const Y_AXIS_LABEL_COUNT = 6;
    private $image_width;
    private $image_height;
    private $image;
    private $colors = array();
    private $safe_space = array();
    private $safe_image_width;
    private $safe_image_height;
    private $database = null;
    private $labels = array();
    private $data = array();

    public function __construct($image_width, $image_height)
    {
        $this->database = new GrapherDB();

        $this->image_width = $image_width;
        $this->image_height = $image_height;

        $this->definesafespace();

        $this->image = imagecreatetruecolor($image_width, $image_height);

        $this->allocatecolors();
        imagefill($this->image, 0, 0, $this->colors['white']);
        $this->drawaxes();

        $this->fetchgraphdata();

        $this->drawlabels();

        $this->drawdatapoints();
    }

    private function definesafespace()
    {
        $this->safe_space = array(
            "start_x" => $this::PADDING_X,
            "end_x" => $this->image_width - $this::PADDING_X,
            "start_y" => $this->image_height - $this::PADDING_Y,
            "end_y" => $this::PADDING_Y
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

    private function fetchgraphdata()
    {
        $this->labels = $this->database->getlabels();
        $this->data = $this->database->getdataassociatedwithlabels();
    }

    private function drawlabels()
    {
        $this->drawxaxislabels();
        $this->drawyaxislabels();
    }

    private function drawxaxislabels()
    {
        $distance_between_labels = $this->safe_image_width / count($this->labels);

        $x = $this->safe_space["start_x"] + $distance_between_labels;
        $y = $this->safe_space["start_y"] + 2 * $this::LABEL_FONT_SIZE;

        foreach ($this->labels as $value) {
            $this->drawlabel(
                $x - $this::LABEL_FONT_SIZE,
                $y,
                $x,
                $this->safe_space["start_y"] + $this::VALUE_INDICATOR_SIZE,
                $x,
                $this->safe_space["start_y"] - $this::VALUE_INDICATOR_SIZE,
                $value
            );
            $this->drawdottedline(
                $x,
                $this->safe_space["start_y"] - 2 * $this::DOT_SIZE,
                $x,
                $this->safe_space["end_y"],
                $this->colors["grey"],
                $this::DOT_SIZE
            );

            $x += $distance_between_labels;
        }
    }

    private function drawyaxislabels()
    {
        $upper_limit = doubleval(ceil(max(array_map("max", $this->data))) / 10 * 10);
        $bottom_limit = doubleval(ceil(min(array_map("min", $this->data))) / 10 * 10);

        $distance_between_labels = $this->safe_image_height / $this::Y_AXIS_LABEL_COUNT;

        $x = $this->safe_space["start_x"] - 10 * $this::LABEL_FONT_SIZE;
        $y = $this->safe_space["start_y"] - $distance_between_labels;

        $step = ($upper_limit - $bottom_limit) / ($this::Y_AXIS_LABEL_COUNT - 1);

        for ($i = $bottom_limit; $i < $upper_limit + $step; $i += $step) {
            $this->drawlabel(
                $x,
                $y - $this::LABEL_FONT_SIZE,
                $this->safe_space["start_x"] + $this::VALUE_INDICATOR_SIZE,
                $y,
                $this->safe_space["start_x"] - $this::VALUE_INDICATOR_SIZE,
                $y,
                $i
            );
            $this->drawdottedline(
                $this->safe_space["start_x"] + 2 * $this::DOT_SIZE,
                $y,
                $this->safe_space["end_x"],
                $y,
                $this->colors["grey"],
                $this::DOT_SIZE
            );

            $y -= $distance_between_labels;
        }
    }

    private function drawlabel($label_x, $label_y, $start_x, $start_y, $end_x, $end_y, $value)
    {
        imagestring(
            $this->image,
            $this::LABEL_FONT_SIZE,
            $label_x,
            $label_y,
            $value,
            $this->colors['black']
        );
        $this->drawline(
            $start_x,
            $start_y,
            $end_x,
            $end_y,
            $this->colors['black']
        );
    }

    private function drawdottedline($start_x, $start_y, $end_x, $end_y, $color, $dot_size)
    {
        $this->setdottedcolors($color, $dot_size);
        $this->drawline($start_x, $start_y, $end_x, $end_y, IMG_COLOR_STYLED);
    }

    private function setdottedcolors($color, $dot_size)
    {
        $line_colors = [$color, $this->colors["white"]];
        $line_style_colors = [];
        foreach ($line_colors as $line_color)
        {
            for ($j = 0; $j < $dot_size; $j++)
            {
                $line_style_colors[] = $line_color;
            }
        }
        imagesetstyle($this->image, $line_style_colors);
    }

    private function drawdatapoints()
    {
        foreach ($this->labels as $label)
        {
            $value = isset($this->data[$label]) ? $this->data[$label] : [];
            if (count($value) > 0)
            {
                $this->drawdatapoint($label, $value[0]);
            }
        }
    }

    private function drawdatapoint($label, $value)
    {
        imageellipse(
            $this->image,
            $this->safe_space["start_x"] + $this->getlabelposition($label),
            $this->safe_space["start_y"] - $this->getvalueposition($value),
            $this::DOT_SIZE,
            $this::DOT_SIZE,
            $this->colors["red"]
        );
    }

    private function getlabelposition($label)
    {
        $distance_between_labels = $this->safe_image_width / count($this->labels);
        $label_position = array_search($label, $this->labels);
        return $distance_between_labels * ($label_position + 1);
    }

    private function getvalueposition($value)
    {
        $upper_limit = doubleval(ceil(max(array_map("max", $this->data))) / 10 * 10);
        $bottom_limit = doubleval(ceil(min(array_map("min", $this->data))) / 10 * 10);
        $distance_between_labels = $this->safe_image_height / $this::Y_AXIS_LABEL_COUNT;
        $step = ($upper_limit - $bottom_limit) / ($this::Y_AXIS_LABEL_COUNT - 1);
        return $distance_between_labels * (abs($bottom_limit - $value) / $step + 1);
    }

    public function getimage()
    {
        return $this->image;
    }
}