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
    const SPECIAL_VALUE = -1;
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
    private $distance_between_axisx_labels;
    private $distance_between_axisy_labels;
    private $axisy_upper_limit;
    private $axisy_bottom_limit;
    private $axsiy_step;

    public function __construct($image_width, $image_height)
    {
        $this->initdatabase();

        $this->setdimensions($image_width, $image_height);

        $this->definesafespace();

        $this->createimage();

        $this->allocatecolors();

        $this->draw();
    }

    private function initdatabase()
    {
        $this->database = new GrapherDB();
    }

    private function setdimensions($image_width, $image_height)
    {
        $this->image_width = $image_width;
        $this->image_height = $image_height;
    }
    private function createimage()
    {
        $this->image = imagecreatetruecolor($this->image_width, $this->image_height);
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

    private function draw()
    {
        $this->drawbackground($this->colors["white"]);

        $this->drawaxes();

        $this->fetchgraphdata();

        $this->drawlabelswithgrid();

        $this->drawconnceteddatapoints();
    }

    private function drawbackground($background_color)
    {
        imagefill($this->image, 0, 0, $background_color);
    }

    private function drawaxes()
    {
        $this->drawaxisx();
        $this->drawaxisy();
    }

    private function drawaxisx()
    {
        $this->drawline(
            $this->safe_space["start_x"],
            $this->safe_space["start_y"],
            $this->safe_space["end_x"],
            $this->safe_space["start_y"],
            $this->colors["black"]
        );
    }

    private function drawaxisy()
    {
        $this->drawline(
            $this->safe_space["start_x"],
            $this->safe_space["end_y"],
            $this->safe_space["start_x"],
            $this->safe_space["start_y"],
            $this->colors["black"]
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

    private function drawlabelswithgrid()
    {
        imagesetthickness($this->image, 1);
        $this->drawxaxislabels();
        $this->drawyaxislabels();
    }

    private function drawxaxislabels()
    {
        extract($this->calculatexaxisproperties());

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

            $x += $this->distance_between_axisx_labels;
        }
    }

    private function calculatexaxisproperties()
    {
        $this->distance_between_axisx_labels = $this->safe_image_width / count($this->labels);

        return array(
            "x" => $this->safe_space["start_x"] + $this->distance_between_axisx_labels,
            "y" => $this->safe_space["start_y"] + 2 * $this::LABEL_FONT_SIZE
        );
    }

    private function drawyaxislabels()
    {
        extract($this->calculateaxisyproperties());

        for ($i = $this->axisy_bottom_limit; $i < $this->axisy_upper_limit + $this->axsiy_step; $i += $this->axsiy_step) {
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

            $y -= $this->distance_between_axisy_labels;
        }
    }

    private function calculateaxisyproperties()
    {
        $filtered_data = $this->filterdata();

        $this->axisy_upper_limit = $this->calculateupperlimit($filtered_data);
        $this->axisy_bottom_limit = $this->calculatebottomlimit($filtered_data);

        $this->axsiy_step = ($this->axisy_upper_limit - $this->axisy_bottom_limit) / ($this::Y_AXIS_LABEL_COUNT - 1);

        $this->distance_between_axisy_labels = $this->safe_image_height / $this::Y_AXIS_LABEL_COUNT;

        return array(
            "x" => $this->safe_space["start_x"] - 10 * $this::LABEL_FONT_SIZE,
            "y" => $this->safe_space["start_y"] - $this->distance_between_axisy_labels,
        );
    }

    private function filterdata()
    {
        return array_filter($this->data, function($value) {
            return count($value) > 0 && $value[0] != $this::SPECIAL_VALUE;
        });
    }

    private function calculateupperlimit($data)
    {
        return doubleval(ceil(max(array_map("max", $data))) / 10 * 10);
    }

    private function calculatebottomlimit($data)
    {
        return doubleval(ceil(min(array_map("min", $data))) / 10 * 10);
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

    private function drawconnceteddatapoints()
    {
        $previous_label_pos = null;
        $previous_value_pos = null;

        foreach ($this->labels as $label)
        {
            $value = $this->doesvalueexist($label) ? $this->data[$label][0] : null;

            $current_label_pos = $this->getlabelposition($label);
            $current_value_pos = $this->getvalueposition($value);

            $this->decidedatapointtype($label, $value);

            if ($this->arevaluesvalid($previous_value_pos, $current_value_pos))
            {
                $this->drawlinebetweenpoints(
                    $this->safe_space["start_x"] + $previous_label_pos,
                    $this->safe_space["start_y"] - $previous_value_pos,
                    $this->safe_space["start_x"] + $current_label_pos,
                    $this->safe_space["start_y"] - $current_value_pos
                );
            }

            $previous_label_pos = $current_label_pos;
            $previous_value_pos = $current_value_pos;
        }
    }

    private function doesvalueexist($label)
    {
        return isset($this->data[$label]) && count($this->data[$label]) > 0;
    }

    private function decidedatapointtype($label, $value)
    {
        switch ($value) {
            case $this::SPECIAL_VALUE:
                $this->drawspecialdatapoint($label, $this->safe_space["start_y"], $this->colors["red"]);
                break;
            case null:
                $this->drawspecialdatapoint($label, $this->safe_space["start_y"], $this->colors["grey"]);
                break;
            default:
                $this->drawdatapoint($label, $value);
                break;
        }
    }

    private function arevaluesvalid($prev_y, $curr_y)
    {
        return isset($prev_y) && isset($curr_y) &&
            $prev_y != $this->safe_space["start_y"] && $curr_y != $this->safe_space["start_y"];
    }

    private function drawdatapoint($label, $value)
    {
        imagefilledellipse(
            $this->image,
            $this->safe_space["start_x"] + $this->getlabelposition($label),
            $this->safe_space["start_y"] - $this->getvalueposition($value),
            2 * $this::DOT_SIZE,
            2 * $this::DOT_SIZE,
            $this->colors["blue"]
        );
    }

    private function drawspecialdatapoint($label, $pos_y, $color)
    {
        imagefilledellipse(
            $this->image,
            $this->safe_space["start_x"] + $this->getlabelposition($label),
            $pos_y,
            2 * $this::DOT_SIZE,
            2 * $this::DOT_SIZE,
            $color
        );
    }

    private function getlabelposition($label)
    {
        $label_position = array_search($label, $this->labels);
        return $this->distance_between_axisx_labels * ($label_position + 1);
    }

    private function getvalueposition($value)
    {
        if ($value == null || $value == $this::SPECIAL_VALUE) {
            return $this->safe_space["start_y"];
        }

        return $this->distance_between_axisy_labels * (abs($this->axisy_bottom_limit - $value) / $this->axsiy_step + 1);
    }

    private function drawlinebetweenpoints($x1, $y1, $x2, $y2)
    {
        $this->drawline($x1, $y1, $x2, $y2, $this->colors["blue"]);
    }

    public function getimage()
    {
        return $this->image;
    }
}