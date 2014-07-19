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
    private $marginSafety = 0.7;
    private $torelanceConstant = 0.2;
    private $yCoord;
    private $xCoord;
    private $grid;
    private $imageCore;
    private $maxX, $maxY;
    private $health;
    private $boolImage;
    private $blackMark = 0x80;    

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
        $this->health = array();
        Debugbar::startMeasure('greyscale', 'Greyscale');
        $this->greyscale();
        Debugbar::stopMeasure('greyscale');
        Debugbar::startMeasure('trim', 'Trim');
        $this->trimBlack();
        Debugbar::stopMeasure('trim');
        Debugbar::startMeasure('calculate', 'Calculate');
        $this->maxX = $this->image->width();
        $this->maxY = $this->image->height();
        $this->dpi = round((($this->maxX / 11.7) + ($this->maxY / 8.27 )) / 2);
        $this->maxX = $this->image->width();
        $this->maxY = $this->image->height();
        $this->dpi = round((($this->maxX / 11.7) + ($this->maxY / 8.27 )) / 2);
        $this->cellSize = ($this->dpi) / 10;
        $this->tolerance = round(
                pow($this->cellSize, 2) * $this->torelanceConstant
        );
        $this->xMargin = round($this->marginSafety * $this->dpi);
        $this->yMargin = round($this->marginSafety * $this->dpi);
        $this->imageCore = $this->image->getCore();
        Debugbar::stopMeasure('calculate');
        Debugbar::startMeasure('preRotationStripDetect', 'Detecting Strips Before Rotation');
        $this->xCoord = $this->detectGridPoints($this->yMargin, $this->width());
        Debugbar::info(count($this->xCoord) . 'XCoords detected', $this->xCoord);
        $this->yCoord = $this->detectGridPoints($this->xMargin, $this->height());
        Debugbar::info(count($this->yCoord) . ' Y Coords detected', $this->yCoord);
        Debugbar::stopMeasure('preRotationStripDetect');
        Debugbar::startMeasure('rotation', 'Calling Rotation');
        $this->correctRotation();
        Debugbar::stopMeasure('rotation');
        Debugbar::startMeasure('postRotationStripDetect', 'Detecting Strips After Rotation');
        $this->imageCore = $this->image->getCore();
        $this->xCoord = $this->detectGridPoints($this->yMargin, $this->width());
        Debugbar::info(count($this->xCoord) . ' X Coords detected', $this->xCoord);
        $this->yCoord = $this->detectGridPoints($this->xMargin, $this->height());
        Debugbar::info(count($this->yCoord) . ' Y Coords detected', $this->yCoord);
        Debugbar::stopMeasure('postRotationStripDetect');
        $this->parseGrid();
        Debugbar::info('Grid Parsed', $this->grid);
        return $this;
    }

    public function getGrid() {
        return $this->grid;
    }

    public function getGridString() {
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
            if ((imagecolorat($this->imageCore, (int) $x, (int) $y) & 0xFF) < $this->blackMark) {
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

        Debugbar::debug("slideDir : " . $sliderDir . " Margin : " . $margin . " Axis: " . $axisMaxValue);

        for ($i = 0; $i < $axisMaxValue; $i++) {
            if ($i > $margin && $this->stripBlackAverage($i, $margin, $sliderDir) > 3) {
                $blackBooleanAxis[$i] = 1;
            } else {
                $blackBooleanAxis[$i] = 0;
            }
        }

        Debugbar::debug("blackBooleanAxis : ", $blackBooleanAxis);

        //It uses a slider along Y-axis and calculate's the average blackness
        //e.g. a point in vertical middle of the strip would score the highest

        $cellAverageGrid = array();
        for ($i = 0; $i < $axisMaxValue; $i++) {
            if ($i < $margin) {
                $cellAverageGrid[$i] = 0;
            } else {
                $offset = $i - round($this->cellSize / 2);
                $length = $this->cellSize;
                $sum = array_sum(array_slice(
                                $blackBooleanAxis, $offset, $length
                ));
                if ($sum < round($this->cellSize / 2.5)) {
                    $sum = 0;
                }
                $cellAverageGrid[$i] = $sum;
            }
        }
        
        Debugbar::debug("cellAverageGrid : ", $cellAverageGrid);

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
                //Debugbar::debug("Starting peak at :".$i);
                $stripStart = $i;
                $stripStartfound = 'yes';
            }
            if ($cellAverageGrid[$i] == 0 && $stripStartfound == 'yes' && $stripEndFound == 'no') {
                //Debugbar::debug("Ending peak at :".$i);
                $stripEnd = $i;
                $stripEndFound = 'yes';
            }

            if ($stripStartfound == 'yes' && $stripEndFound == 'yes') {
                //Debugbar::debug("Peak at : ".round(($stripStart + $stripEnd)/2.0));
                $stripCoords[] = round(($stripStart + $stripEnd) / 2.0);
                $stripStartfound = 'no';
                $stripEndFound = 'no';
            }
        }

        Debugbar::debug("stripCoords : ", $stripCoords);

        return $stripCoords;
    }

    public function correctRotation($debug = TRUE) {

        Debugbar::debug("Rotation Started");

        if (count($this->yCoord) == 0 || count($this->xCoord) == 0) {
            return $this;
        }

        $top_y = $this->yCoord[0];
        $bottom_y = $this->yCoord[count($this->yCoord) - 1];

        $white_count = 0;

        for ($i = $this->xMargin; $i < $this->maxX; $i++) {
            if ($this->stripBlackAverage($i, $top_y - 2, 'y', 5) > 3) {
                $white_count = 0;
                if ($this->stripBlackAverage($i + 20, $top_y - 2, 'y', 5) > 3) {
                    $i+=20;
                }
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

        Debugbar::debug("Top Trace Completed");

        $white_count = 0;
        for ($i = $this->xMargin; $i < $this->maxX; $i++) {
            if ($this->stripBlackAverage($i, $bottom_y - 2, 'y', 5) > 3) {
                $white_count = 0;
                if ($this->stripBlackAverage($i + 20, $bottom_y - 2, 'y', 5) > 3) {
                    $i+=20;
                }
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

        Debugbar::debug("Bottom Trace Completed");
        $angle_radian = atan(($bottom_margin - $top_margin) / ($bottom_y - $top_y));
        $rotation = ($angle_radian / (2 * pi())) * 360 * 1.2;        
        Debugbar::info("Rotation : " . $rotation, array(
            "bottom_x" => $bottom_margin,
            "bottom_y" => $bottom_y,
            "top_x" => $top_margin,
            "top_y" => $top_y
        ));

        if ($debug) {
            $this->image->circle(20, $bottom_margin, $bottom_y, function ($draw) {
                $draw->background('#00f');
                $draw->border(1, '#f00');
            });

            $this->image->circle(20, $top_margin, $top_y, function ($draw) {
                $draw->background('#00f');
                $draw->border(1, '#f00');
            });
        } 

        $this->image->rotate(360 - $rotation, 0xFFFFFF);
        $this->maxY = $this->height();
        $this->maxX = $this->width();
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
        $minX = round($x - $this->cellSize / 2.5);
        $maxX = round($x + $this->cellSize / 2.5);
        $minY = round($y - $this->cellSize / 2.5);
        $maxY = round($y + $this->cellSize / 2.5);
        for ($i = $minX; $i < $maxX; $i++) {
            for ($j = $minY; $j < $maxY; $j++) {
                if ( (imagecolorat($this->imageCore, $i, $j) & 0xFF) < 0xB0 ) {
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

    private function health() {
        //To output the quality of detection.
    }

    private function trimBlack() {
        $realX = 0;
        $lastX = (int) ($this->image->width());
        $lastY = (int) $this->image->height();
        $black = $this->blackMark * 3;
        $im = $this->image->getCore();

        //Trimming the left edge
        for ($i = 0; $i < $lastX / 4; $i++) {
            for ($j = 1; $j < $lastY - 1; $j++) {
                if ((imagecolorat($im, $i, $j - 1) & 0xFF) + (imagecolorat($im, $i, $j) & 0xFF) + (imagecolorat($im, $i, $j + 1) & 0xFF) < $black)
                    break 2;
            }
        }

        $realX = $i;

        $newImg = imagecreatetruecolor($lastX - $realX, $lastY);
        imagecopy($newImg, $im, 0, 0, $realX, 0, $lastX - $realX, $lastY);
        $this->setCore($newImg);
    }

}
