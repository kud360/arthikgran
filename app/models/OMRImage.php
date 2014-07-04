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

    private $originalHeight;
    private $originalWidth;
    private $originalImage;
    private $cellSize;
    private $tolerance;
    private $xMargin;
    private $yMargin;
    private $marginSafety = 0.06;
    private $torelanceConstant = 0.7;
    private $yCoord;
    private $xCoord;
    private $optimizedImage;

    public static function make($image) {

        $newImage = new OMRImage();
        $newImage->originalImage = $image;
        $newImage->setVariables();
        return $newImage;
    }

    public function printDetails() {
        var_dump($this);
    }

    public function image() {
        return $this->optimizedImage;
    }

    private function setVariables() {
        $this->originalHeight = round($this->originalImage->height());
        $this->originalWidth = round($this->originalImage->width());
        $this->validateAspect();
        $this->cellSize = round($this->originalHeight / 82.7);
        $this->tolerance = round(
                pow($this->cellSize, 2) * $this->torelanceConstant
        );
        $this->xMargin = round(
                $this->marginSafety * $this->originalWidth * 11.3 / 8.27
        );
        $this->yMargin = round(
                $this->marginSafety * $this->originalHeight
        );
        $this->xCoord = $this->detectGridPoints(
                $this->originalImage, $this->yMargin, $this->originalWidth
        );
        Log::info(count($this->xCoord) . 'XCoords detected', $this->xCoord);
        $this->yCoord = $this->detectGridPoints(
                $this->originalImage, $this->xMargin, $this->originalHeight
        );
        Log::info(count($this->yCoord) . ' Y Coords detected', $this->yCoord);
        $this->optimizedImage = $this->correctSkew($this->originalImage);
        $this->xCoord = $this->detectGridPoints(
                $this->optimizedImage, $this->yMargin, $this->originalWidth
        );
        Log::info(count($this->xCoord) . ' X Coords detected', $this->xCoord);
        $this->yCoord = $this->detectGridPoints(
                $this->optimizedImage, $this->xMargin, $this->originalHeight
        );
        Log::info(count($this->yCoord) . ' Y Coords detected', $this->yCoord);
    }

    private function validateAspect() {

        $aspectRatio = $this->originalWidth / $this->originalHeight;
        if ($aspectRatio > 0.704 && $aspectRatio < 0.712) {
            Log::info('Aspect ratio validates A4 compliance.');
            Log::info('Image DPI detected: ' .
                    ($this->originalWidth / 8.27) .
                    'X' .
                    ($this->originalHeight / 11.7));
        } else {
            Log::error('Aspect ratio out of bounds for A4 limits');
            throw new Exception;
        }
    }

    private function stripBlackAverage($image, $coord, $margin, $direction, $stripLength = 5) {

        $slider = array();
        if ($direction == 'x') {
            for ($i = 0; $i < $stripLength; $i++) {
                $slider[$i] = $this->isBlack($image, $margin + $i, $coord);
            }
        } else {
            for ($i = 0; $i < $stripLength; $i++) {
                $slider[$i] = $this->isBlack($image, $coord, $margin + $i);
            }
        }
        return array_sum($slider);
    }

    private function isBlack($image, $x, $y) {

        if ($x < 0 || $y < 0 || $x >= $image->width() || $y >= $image->height()) {
            return 0;
        } else {
            $rgb = $image->pickColor((int) $x, (int) $y);

            //Log::info('rgb value: ',array("rgb" => $rgb));

            if ($rgb[0] < 50 && $rgb[1] < 50 && $rgb[2] < 50) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    private function detectGridPoints($image, $margin, $axisMaxValue) {

        $blackBooleanAxis = array();

        //Loop to detect which pixels on a given margin are black
        //The position of this margin should idealy overlay on black strips

        $sliderDir = (round($image->height()) == round($axisMaxValue)) ? 'x' : 'y';

        Log::debug("slideDir : " . $sliderDir . " Margin : " . $margin . " Axis: " . $axisMaxValue);

        for ($i = 0; $i < $axisMaxValue; $i++) {
            if ($this->stripBlackAverage($image, $i, $margin, $sliderDir) > 3) {
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
            if ($cellAverageGrid[$i] < round($this->cellSize / 2)) {
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

    public function correctSkew($image) {

        $top_y = $this->yCoord[0];
        $bottom_y = $this->yCoord[count($this->yCoord) - 1];

        $white_count = 0;

        for ($i = $this->xMargin; $i < $image->width(); $i++) {
            if ($this->stripBlackAverage($image, $i, $top_y - 2, 'y', 5) > 3) {
                $white_count = 0;
            } else {
                if ($this->stripBlackAverage($image, $i, $top_y - 4, 'y', 5) > 3) {
                    $top_y-=2;
                    $white_count = 0;
                } elseif ($this->stripBlackAverage($image, $i, $top_y, 'y', 5) < 3) {
                    $top_y+=2;
                    $white_count = 0;
                } else {
                    $white_count = $white_count + 1;
                }
            }
            if ($white_count == 5) {
                if ($i)
                    break;
            }
        }
        $top_margin = $i - 5;
        
        $white_count = 0;        
        for ($i = $this->xMargin; $i < $image->width(); $i++) {
            if ($this->stripBlackAverage($image, $i, $bottom_y - 2, 'y', 5) > 3) {
                $white_count = 0;
            } else {
                if ($this->stripBlackAverage($image, $i, $bottom_y - 4, 'y', 5) > 3) {
                    $bottom_y-=2;
                    $white_count = 0;
                } elseif ($this->stripBlackAverage($image, $i, $bottom_y, 'y', 5) < 3) {
                    $bottom_y+=2;
                    $white_count = 0;
                } else {
                    $white_count = $white_count + 1;
                }
            }
            if ($white_count == 5) {
                if ($i)
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
        $newImage = clone $image;
        $newImage->rotate($rotation, 0xFFFFFF)
                ->resizeCanvas(
                        (int) $image->width(), (int) $image->height(), 'top-left', false, 'ffffff');

        $image->circle(10, $bottom_margin, $bottom_y, function ($draw) {
            $draw->background('#0000ff');
            $draw->border(1, '#f00');
        });

        $image->circle(10, $top_margin, $top_y, function ($draw) {
            $draw->background('#0000ff');
            $draw->border(1, '#f00');
        });
        return $newImage;
    }

    public function debugImage() {
        $debugImage = clone $this->originalImage;
        //$this->drawGuides($debugImage);
        $this->drawMargins($debugImage);
        return $debugImage;
    }

    private function drawGuides($image) {
        foreach ($this->xCoord as $point) {
            $image->line($point, 0, $point, (int) $image->height(), function ($draw) {
                $draw->color('#555');
            });
        }
        foreach ($this->yCoord as $point) {
            $image->line(0, $point, (int) $image->width(), $point, function ($draw) {
                $draw->color('#555');
            });
        }
    }

    private function drawMargins($image) {
        $image->line($this->xMargin, 0, $this->xMargin, $this->originalHeight, function ($draw) {
            $draw->color('#eee');
        });
        $image->line(0, $this->yMargin, $this->originalWidth, $this->yMargin, function ($draw) {
            $draw->color('#eee');
        });
    }

}
