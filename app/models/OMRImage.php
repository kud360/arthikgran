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
    private $marginSafety = 0.03;
    private $torelanceConstant = 0.7;
    private $yCoord;
    private $xCoord;
    private $optimizedImage;

    public static function make($image) {

        $newImage = new ORMImage();
        $newImage->originalImage = $image;
        $newImage->setVariables();
    }

    public function printDetails() {
        var_dump($this);
    }

    private function setVariables() {
        $this->originalHeight = $this->origninalImage->height();
        $this->originalWidth = $this->origninalImage->width();
        $this->validateAspect();
        $this->cellSize = $this->originalHeight;
        $this->tolerance = pow($this->cellSize, 2) * $this->torelanceConstant;
        $this->xMargin = $this->marginSafety * $this->originalWidth;
        $this->yMargin = $this->marginSafety * $this->originalHeight;
        $this->xCoord = $this->detectGridPoints($this->originalImage, $this->yMargin, $this->originalWidth);
        $this->yCoord = $this->detectGridPoints($this->originalImage, $this->xMargin, $this->originalHeight);
        $this->optimizedImage = $this->correctSkew($this->originalImage);
        $this->xCoord = $this->detectGridPoints($this->optimizedImage, $this->yMargin, $this->originalWidth);
        $this->yCoord = $this->detectGridPoints($this->optimizedImage, $this->xMargin, $this->originalHeight);
    }

    private function validateAspect() {

        $aspectRatio = $this->originalWidth / $this->originalHeight;
        if ($aspectRatio > 0.704 && $aspectRatio < 0.712) {
            Log::info('Aspect ratio validates A4 compliance.');
            Log::info('Image DPI detected: ' . ($this->originalWidth / 8.27) . 'X' . ($this->originalHeight / 11.7));
        } else {
            Log::error('Aspect ratio out of bounds for A4 limits');
            throw new Exception;
        }
    }

    private function stripBlackAverage($image, $coord, $margin, $stripLength = 5,$dir = FALSE) {

        $slider = array();
        if ( dir?(dir=='x'):($margin == $this->xMargin) ) {
            for ($i = 0; i < $stripLength; $i++) {
                $slider[$i] = $this->isBlack($image, $margin + $i, $coord);
            }
        } else {
            for ($i = 0; i < $stripLength; $i++) {
                $slider[$i] = $this->isBlack($image, $coord, $margin + $i);
            }
        }
        return array_sum($slider);
    }

    private function isBlack($image, $x, $y) {

        if ($x < 0 || $y < 0 || $x > $image->wigth() || $y > $image->height()) {
            return 0;
        } else {
            $rgb = $image->pickColor($x, $y, 'rgb');
            if ($rgb['red'] < 50 && $rgb['green'] < 50 && $rgb['blue'] < 50) {
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

        for ($i = 0; $i < $axisMaxValue; $i++) {
            if ($this->stripBlackAverage($image, $i, $margin) > 3) {
                $blackBooleanAxis[$i] = 1;
            } else {
                $blackBooleanAxis[$i] = 0;
            }
        }

        //It uses a slider along Y-axis and calculate's the average blackness
        //e.g. a point in vertical middle of the strip would score the highest

        $cellAverageGrid = array();
        for ($i = 0; $i < $axisMaxValue; $i++) {
            $offset = max($i - $this->cellSize / 2, 0);
            if ($i < $this->cellSize / 2) {
                $length = $this->cellSize / 2 + $i;
            } else {
                $length = $this->cellSize;
            }
            $cellAverageGrid[$i] = array_sum(array_slice(
                            $blackBooleanAxis, $offset, $length
            ));
        }

        //Flattens out white points beyond a threshold

        for ($i = 0; $i < $axisMaxValue; $i++) {
            if ($cellAverageGrid[$i] < $this->cellSize / 2) {
                $cellAverageGrid[$i] = 0;
            }
        }

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
                $stripStart = $i;
                $stripStartfound = 'yes';
            }
            if ($cellAverageGrid[$i] == 0 && $stripStartfound == 'yes' && $stripEndFound == 'no') {
                $stripEnd = $i;
                $stripEndFound = 'yes';
            }

            if ($cellAverageGrid == 'yes' && $stripEndFound == 'yes') {
                //$peaks[]=round(($first+$second)/2,0);
                $peaks[] = ($stripStart + $stripEnd) / 2;
                $stripStartfound = 'no';
                $stripEndFound = 'no';
            }
        }

        return $stripCoords;
    }

    public function correctSkew($image) {

        $top_y = $this->yCoord[0];
        $bottom_y = $this->yCoord[count($this->yCoord) - 1];

        $white_count = 0;
        
        for ($i = $this->xMargin; $i < $this->originalHeight; $i++) {
            if ($this->stripBlackAverage($image, $i, $top_y-2) > 3) {
                $white_count = 0;
            } else {
                $white_count = $white_count + 1;
            }
            if ($white_count == 5) {
                break;
            }
        }
        $top_margin = $i - 5;


        $white_count = 0;
        $b = array();
        for ($i = $this->xMargin; $i < $this->originalHeight; $i++) {
            if ($this->stripBlackAverage($image, $i, $bottom_y-2) > 3) {
                $white_count = 0;
            } else {
                $white_count = $white_count + 1;
            }
            if ($white_count == 5) {
                break;
            }
        }
        $bottom_margin = $i - 5;

        $hyp = sqrt(($bottom_margin - $top_margin) * ($bottom_margin - $top_margin) + ($bottom_y - $top_y) * ($bottom_y - $top_y));
        $angle_radian = asin(($bottom_y - $top_y) / $hyp);
        $angle_degree = ($angle_radian / (2 * pi())) * 360;
        $rotation = 90 - $angle_degree;
        $newImage = clone $image;
        $newImage->rotate(360 - $rotation, 0xFFFFFF)->resizeCanvas($this->originalWidth,$this->originalHeight, 'top-left', false, 'ffffff');
        return $newImage;
        
    }    
}