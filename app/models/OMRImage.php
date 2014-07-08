<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OMRImage
 *
 * @author karan
 */
class OMRImage {

    private $image;
    private $cellSize;
    private $dpi;
    private $tolerance;
    private $xMargin;
    private $yMargin;
    private $marginSafety = 0.6;
    private $torelanceConstant = 0.2;
    private $yCoord;
    private $xCoord;
    private $grid;
    private $GDObj;
    private $maxX, $maxY;

    public function __call($method_name, $args) {
        if (method_exists($this->image, $method_name)) {
            return call_user_func_array(array($this->image, $method_name), $args);
        } else {
            return call_user_func_array(array($this->image, $method_name), $args);
        }
    }

    public function __construct($file) {
        $this->image = Image::make($file);
        return $this;
    }

    public function prepare() {
        $this->trim('top-left', null, 60);
        $this->greyscale();                
        $this->blur(10);        
        $this->maxX = $this->image->width();
        $this->maxY = $this->image->height();
        $this->dpi = round((($this->maxX / 11.7) + ($this->maxY / 8.27 )) / 2);                
        $this->maxX = $this->image->width();
        $this->maxY = $this->image->height();
        $this->dpi = round((($this->maxX / 11.7) + ($this->maxY / 8.27 )) / 2);
        $this->cellSize = ($this->dpi) / 10;
        $this->tolerance = round(
                pow($this->cellSize, 2) * $this->torelanceConstant / 4
        );
        $this->xMargin = round($this->marginSafety * $this->dpi);
        $this->yMargin = round($this->marginSafety * $this->dpi);
        $this->GDObj = $this->image->getCore();
        $this->xCoord = $this->detectGridPoints($this->yMargin, $this->width());
        Log::info(count($this->xCoord) . 'XCoords detected', $this->xCoord);
        $this->yCoord = $this->detectGridPoints($this->xMargin, $this->height());
        Log::info(count($this->yCoord) . ' Y Coords detected', $this->yCoord);
        $this->correctRotation();
        $this->GDObj = $this->image->getCore();
        $this->xCoord = $this->detectGridPoints($this->yMargin, $this->width());
        Log::info(count($this->xCoord) . ' X Coords detected', $this->xCoord);
        $this->yCoord = $this->detectGridPoints($this->xMargin, $this->height());
        Log::info(count($this->yCoord) . ' Y Coords detected', $this->yCoord);
        $this->parseGrid();
        Log::info('Grid Parsed', $this->grid);
        return $this;
    }
    
    public function getGrid()   {
        return $this->grid;
    }
    
    public function getGridString()   {
        /*
        $string = array();
        foreach($this->grid as $row)    {
            $string = implode('',$row);
        }
        return implode('',$string);
         * 
         */
        return "Done :)";
    }

    private function validateAspect() {

        $aspectRatio = $this->image->width() / $this->image->height();
        if ($aspectRatio > 0.704 && $aspectRatio < 0.732) {
            Log::info('Aspect ratio validates A4 compliance.');
            Log::info('Image DPI detected: ' .
                    ($this->width() / 8.27) .
                    'X' .
                    ($this->height() / 11.7));
        } else {
            Log::error('Aspect ratio out of bounds for A4 limits');
        }
    }

    private function stripBlackAverage($coord, $margin, $direction, $stripLength = 5) {

        $slider = array();
        if ($direction == 'x') {
            for ($i = 0; $i < $stripLength; $i++) {
                $slider[$i] = $this->isBlack($margin + $i, $coord);
            }
        } else {
            for ($i = 0; $i < $stripLength; $i++) {
                $slider[$i] = $this->isBlack($coord, $margin + $i);
            }
        }
        return array_sum($slider);
    }

    private function isBlack($x, $y) {

        if ($x < 0 || $y < 0 || $x >= $this->maxX || $y >= $this->maxY) {
            return 0;
        } else {
            if ((imagecolorat($this->GDObj, (int) $x, (int) $y) & 0xFF) < 150) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    private function detectGridPoints($margin, $axisMaxValue) {

        $blackBooleanAxis = array();

        //Loop to detect which pixels on a given margin are black
        //The position of this margin should idealy overlay on black strips

        $sliderDir = (round($this->image->height()) == round($axisMaxValue)) ? 'x' : 'y';

        Log::debug("slideDir : " . $sliderDir . " Margin : " . $margin . " Axis: " . $axisMaxValue);

        for ($i = 0; $i < $axisMaxValue; $i++) {
            if ($this->stripBlackAverage($i, $margin, $sliderDir) > 3) {
                $blackBooleanAxis[$i] = 1;
            } else {
                $blackBooleanAxis[$i] = 0;
            }
        }

        Log::debug("blackBooleanAxis : ", $blackBooleanAxis);

        //It uses a slider along Y-axis and calculate's the average blackness
        //e.g. a point in vertical middle of the strip would score the highest

        $cellAverageGrid = array();
        for ($i = 0; $i < $axisMaxValue; $i++) {
            $offset = max($i - round($this->cellSize / 2), 0);
            if ($i < round($this->cellSize / 2)) {
                $length = round($this->cellSize / 2 + $i);
            } else {
                $length = $this->cellSize;
            }
            $cellAverageGrid[$i] = array_sum(array_slice(
                            $blackBooleanAxis, $offset, $length
            ));
        }

        //Log::debug("cellAverageGrid : ",$cellAverageGrid);
        //Flattens out white points beyond a threshold

        for ($i = 0; $i < $axisMaxValue; $i++) {
            if ($cellAverageGrid[$i] < round($this->cellSize / 2.5)) {
                $cellAverageGrid[$i] = 0;
            }
        }

        Log::debug("cellAverageGrid : ", $cellAverageGrid);

        // Finding the co-rodinate of strips
        // the centre would have highest surrounding blackness
        // i.e. value of $cellAverageGrid[$i]

        $stripCoords = array();
        $stripStart = -1;
        $stripStartfound = 'no';
        $stripEnd = -1;
        $stripEndFound = 'no';

        for ($i = 0; $i < $axisMaxValue; $i++) {
            if ($cellAverageGrid[$i] > 0 && $stripStartfound == 'no' && $stripEndFound == 'no') {
                //Log::debug("Starting peak at :".$i);
                $stripStart = $i;
                $stripStartfound = 'yes';
            }
            if ($cellAverageGrid[$i] == 0 && $stripStartfound == 'yes' && $stripEndFound == 'no') {
                //Log::debug("Ending peak at :".$i);
                $stripEnd = $i;
                $stripEndFound = 'yes';
            }

            if ($stripStartfound == 'yes' && $stripEndFound == 'yes') {
                //Log::debug("Peak at : ".round(($stripStart + $stripEnd)/2.0));
                $stripCoords[] = round(($stripStart + $stripEnd) / 2.0);
                $stripStartfound = 'no';
                $stripEndFound = 'no';
            }
        }

        Log::debug("stripCoords : ", $stripCoords);

        return $stripCoords;
    }

    public function correctRotation() {

        $top_y = $this->yCoord[0];
        $bottom_y = $this->yCoord[count($this->yCoord) - 1];

        $white_count = 0;

        for ($i = $this->xMargin; $i < $this->image->width(); $i++) {
            if ($this->stripBlackAverage($i, $top_y - 2, 'y', 5) > 3) {
                $white_count = 0;
            } else {
                if ($this->stripBlackAverage($i, $top_y - 4, 'y', 5) > 3) {
                    $top_y-=2;
                    $white_count = 0;
                } elseif ($this->stripBlackAverage($i, $top_y, 'y', 5) > 3) {
                    $top_y+=2;
                    $white_count = 0;
                } else {
                    $white_count = $white_count + 1;
                }
            }
            if ($white_count == 5) {
                break;
            }
        }
        $top_margin = $i - 5;

        $white_count = 0;
        for ($i = $this->xMargin; $i < $this->image->width(); $i++) {
            if ($this->stripBlackAverage($i, $bottom_y - 2, 'y', 5) > 3) {
                $white_count = 0;
            } else {
                if ($this->stripBlackAverage($i, $bottom_y - 4, 'y', 5) > 3) {
                    $bottom_y-=2;
                    $white_count = 0;
                } elseif ($this->stripBlackAverage($i, $bottom_y, 'y', 5) > 3) {
                    $bottom_y+=2;
                    $white_count = 0;
                } else {
                    $white_count = $white_count + 1;
                }
            }
            if ($white_count == 5) {
                break;
            }
        }
        $bottom_margin = $i - 5;

        $hyp = sqrt(
                ($bottom_margin - $top_margin) * ($bottom_margin - $top_margin) + ($bottom_y - $top_y) * ($bottom_y - $top_y)
        );
        $angle_radian = asin(($bottom_y - $top_y) / $hyp);
        $angle_degree = ($angle_radian / (2 * pi())) * 360;
        $rotation = 90 - $angle_degree;
        Log::info("Rotation : " . $rotation, array(
            "bottom_x" => $bottom_margin,
            "bottom_y" => $bottom_y,
            "top_x" => $top_margin,
            "top_y" => $top_y
        ));

        $this->image->circle(20, $bottom_margin, $bottom_y, function ($draw) {
            $draw->background('#0000ff');
            $draw->border(1, '#f00');
        });

        $this->image->circle(20, $top_margin, $top_y, function ($draw) {
            $draw->background('#0000ff');
            $draw->border(1, '#f00');
        });

        $height = $this->height();
        $width = $this->width();

        $this->image->rotate($rotation, 0xFFFFFF)
                ->resizeCanvas(
                        $this->maxX, $this->maxY, 'center', false, 'ffffff');

        return $this;
    }

    private function parseGrid() {
        $this->grid = array();
        foreach ($this->xCoord as $row => $x) {
            array_push($this->grid, array());
            foreach ($this->yCoord as $column => $y) {
                array_push($this->grid[$row], $this->isBlackDot($x, $y));
            }
        }
    }

    private function isBlackDot($x, $y) {
        $counter = 0;
        $minX = round($x - $this->cellSize / 4);
        $maxX = round($x + $this->cellSize / 4);
        $minY = round($y - $this->cellSize / 4);
        $maxY = round($y + $this->cellSize / 4);
        for ($i = $minX; $i < $maxX; $i++) {
            for ($j = $minY; $j < $maxY; $j++) {
                if ($this->isBlack($i, $j)) {
                    $counter++;
                }
            }
        }
        if ($counter > $this->tolerance) {
            $this->image->rectangle(
                    $minX, $minY, $maxX, $maxY, function ($draw) {
                $draw->background('#0000ff');
                $draw->border(1, '#f00');
            });
            return 1;
        } else {
            return 0;
        }
    }

    public function debugImage() {
        $this->drawGuides();
        $this->drawMargins();
        return $this;
    }

    private function drawGuides() {
        foreach ($this->xCoord as $point) {
            $this->image->line($point, 0, $point, (int) $this->image->height(), function ($draw) {
                $draw->color('#555');
            });
        }
        foreach ($this->yCoord as $point) {
            $this->image->line(0, $point, (int) $this->image->width(), $point, function ($draw) {
                $draw->color('#555');
            });
        }
    }

    private function drawMargins() {
        $this->image->line($this->xMargin, 0, $this->xMargin, $this->image->height(), function ($draw) {
            $draw->color('#eee');
        });
        $this->image->line(0, $this->yMargin, $this->image->width(), $this->yMargin, function ($draw) {
            $draw->color('#eee');
        });
    }

}
