<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">        
        <title>Bajaj Capital OASIS</title>       
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="<?= asset('css/bootstrap.min.css') ?>" rel="stylesheet">
        <link href="<?= asset('css/jquery.fileupload.css') ?>" rel="stylesheet">
        <link href="<?= asset('css/jquery.fileupload-ui.css') ?>" rel="stylesheet">
        <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
        <noscript><link rel="stylesheet" href="<?= asset("css/jquery.fileupload-noscript.css") ?>"></noscript>
        <noscript><link rel="stylesheet" href="<?= asset("css/jquery.fileupload-ui-noscript.css") ?>"></noscript>
    </head>
    <body>
        <div class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-fixed-top .navbar-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="https://bajajcapital.com">Bajaj Capital Ltd.</a>
                </div>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">

                    </ul>
                </div>
            </div>
        </div>
        <div class="container">
            <h1>OMR Answer Sheet Interpretation System (O.A.S.I.S.)</h1>
            <h2 class="lead">Version 1.0</h2>

            <div class="row">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <div class="btn btn-success fileinput-button col-lg-2" style="margin-top: 5px;">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Select files...</span>
                    <!-- The file input field used as target for the file upload widget -->
                    <input id="fileupload" type="file" name="files[]" multiple>
                </div>
                <div class="well well-sm col-lg-9 col-lg-offset-1">
                    <b>
                        Status:
                    </b>
                    <span id="statusText">
                        Waiting for file selection by user ...
                    </span>
                </div>
            </div>            

            <!-- The global progress bar -->
            <div class="row">
                <div id="progress" class="progress">
                    <div class="progress-bar progress-bar-info progress-bar-striped"></div>
                </div>
            </div>

            <!-- The container for the uploaded files -->
            <div id="files" class="files"></div>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li class="active"><a href="#instructions" role="tab" data-toggle="tab">Instructions</a></li>
                <li><a href="#result" role="tab" data-toggle="tab">Result</a></li>
                <li><a href="#answers" role="tab" data-toggle="tab">Correct Answers</a></li>
                <li><a href="#links" role="tab" data-toggle="tab">Links</a></li>                
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="instructions">
                    <blockquote>
                        <p>Some Instruction Text</p>
                    </blockquote>
                </div>
                <div class="tab-pane" id="result">
                    <blockquote>
                        <h3>Results<small>The result table in this section will be filled up as and when the answer sheets are corrected.</small></h3>
                        <p class="lead">Result Table</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="resultTable">
                                <tr>
                                    <th>
                                        Sr. No.
                                    </th>
                                    <th>
                                        Name
                                    </th>
                                    <th>
                                        Division
                                    </th>
                                    <th>
                                        Roll Number
                                    </th>
                                    <th>
                                        Marks
                                    </th>
                                    <th>
                                        Pass / Fail
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </blockquote>                    
                </div>
                <div class="tab-pane" id="answers">
                    <blockquote>
                        <h3>Results<small>This section becomes disabled once checking has begun.</small></h3>
                        <p>
                            Click the appropriate button representing the correct answer besides to the corresponding question number
                        </p>
                        <div class="hidden well-lg">
                            <div class="well well-sm col-lg-2">
                                <span class="badge">1</span>&nbsp;
                                <div class="btn-group" data-toggle="buttons">                            
                                    <label class="btn btn-default btn-sm">
                                        <input type="radio" name="options" value="A">A
                                    </label>
                                    <label class="btn btn-default btn-sm">
                                        <input type="radio" name="options" value="B">B
                                    </label>
                                    <label class="btn btn-default btn-sm">
                                        <input type="radio" name="options" value="C">C
                                    </label>
                                    <label class="btn btn-default btn-sm">
                                        <input type="radio" name="options" value="D">D
                                    </label>
                                </div>
                            </div>
                        </div>
                    </blockquote>                    
                </div>                
                <div class="tab-pane" id="links">
                    <blockquote>
                        <p>Links to answer sheet pdf and other documents</p>
                    </blockquote>                    
                </div>                
            </div>            
        </div>
        <script src="js/jquery.min.js"></script>        
        <script src="js/jquery.ui.widget.js"></script>        
        <script src="js/jquery.iframe-transport.js"></script>        
        <script src="js/jquery.fileupload.js"></script>        
        <script src="js/bootstrap.min.js"></script>
        <script>
            var defaultCorrect = [
                'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
                'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
                'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D', 'A'
            ];
            var correct = [];

            /*jslint unparam: true */
            /*global window, $ */
            $(function() {
                'use strict';

                $('#fileupload').fileupload({
                    url: 'parse',
                    dataType: 'json',
                    sequentialUploads: true,
                    done: function(e, data) {
                        console.log(data);
                        $("#statusText").text('File ' + data.files[0].name + " interpreted Successfully");
                        publishResult(data.result.grid);
                    },
                    start: function(e, data) {
                        $('#answers label.active').addClass('btn-success')
                        $('#answers label').addClass('disabled');
                        $('#answers label').each(function(index, elem) {
                            $(this).addClass('disabled');
                            if ($(this).hasClass('active')) {
                                $(this).addClass('btn-success')
                                correct.push($(this).text().trim());
                            }
                        });
                        if (data.loaded > 0) {
                            $("#statusText").text("Uploading " + data.files[0].name);
                        }
                    },
                    progress: function(e, data) {
                        if (data.loaded == data.total) {
                            $("#statusText").text("Interpreting " + data.files[0].name". This can take upto a minute ...");
                        }   else    {
                            $("#statusText").text("Uploading " + data.files[0].name);                            
                        }

                    },
                    progressall: function(e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);

                        $('#progress .progress-bar').css(
                                'width',
                                progress + '%'
                                );
                        $('#progress .progress-bar').text(progress + ' %');
                    }
                }).prop('disabled', !$.support.fileInput)
                        .parent().addClass($.support.fileInput ? undefined : 'disabled');

                $("#answers .hidden").each(function() {
                    var $entry = $('.well', this).clone();
                    $('.well', this).remove();
                    for (var i = 0; i < defaultCorrect.length; i++) {
                        if (i % 5 === 0) {
                            $(this).append('<div class="row">');
                        }
                        $(this).children('.row:last').append($entry.clone());
                        $('.row:last .well:last .badge', this).text(i + 1);
                        $('.row:last .well:last input[value=' + defaultCorrect[i] + ']', this).click();

                    }
                    $(this).removeClass('hidden');
                });
            });

            function getName(grid) {
                var name = "";
                var startRow = 2;
                var EndRow = startRow + 26
                var startColumn = 0;
                var EndColumn = grid.length - 1;
                var i, j

                for (j = startColumn; j < EndColumn; j++) {
                    for (i = startRow; i < EndRow; i++) {
                        if (grid[j][i]) {
                            name += String.fromCharCode(65 + i - startRow);
                            break;
                        }
                    }
                    if (i === EndRow) {
                        name += ' ';
                    }
                }

                return name.trim();
            }

            function getRollNo(grid) {
                var startRow = 32;
                var startColumn = 1;
                var digits = 3;
                var roll = 0;

                for (var i = 0; i < digits; i++) {

                    var faceValue = 0;

                    for (var j = 0; j < 5; j++) {
                        if (grid[ startColumn + i * 2 ][startRow + j]) {
                            faceValue = j;
                        } else if (grid[ startColumn + i * 2 + 1][startRow + j]) {
                            faceValue = j + 5;
                        }
                        if (faceValue) {
                            break;
                        }
                    }
                    roll = roll * 10 + faceValue;
                }
                return roll;
            }

            function getDiv(grid) {
                var startRow = 32;
                var startColumn = 10;
                for (i = 0; i < 5; i++) {
                    for (j = 0; j < 6; j++) {
                        var alpha = i + (j * 5)
                        if (j < 26) {
                            if (grid[startColumn + j][startRow + i]) {
                                return String.fromCharCode(65 + alpha)
                            }
                        }
                    }
                }
                return ' ';
            }

            function getMarks(grid) {

                var noOfAnswers = correct.length;
                var answersInARow = 5;
                var answersInAColumn = Math.ceil(noOfAnswers / answersInARow);
                var rowsPerAnswer = 1;
                var columnsPerAnswer = 6;
                var startColumn = 1;
                var startRow = 39;
                var marks = 0;
                for (var i = startRow; i < startRow + answersInAColumn * rowsPerAnswer; i += rowsPerAnswer) {

                    for (var j = startColumn; j < columnsPerAnswer * answersInARow; j += columnsPerAnswer) {

                        var questionNo = ((j - startColumn) / columnsPerAnswer) * answersInAColumn + ((i - startRow) / rowsPerAnswer);

                        if (questionNo <= noOfAnswers) {
                            var option = '';

                            option += (grid[j][i]) ? 'A' : '';
                            option += (grid[j + 1][i]) ? 'B' : '';
                            option += grid[j + 2][i] ? 'C' : '';
                            option += grid[j + 3][i] ? 'D' : '';

                            console.log(questionNo + ' ( ' + i + ',' + j + ' ) : ' + option);

                            if (option.length == 1) {
                                if (correct[questionNo] == option)
                                    marks += 1;
                            }
                        }
                    }
                }
                return marks;
            }

            function publishResult(grid) {
                var name = getName(grid);
                var marks = getMarks(grid);
                var roll = getRollNo(grid);
                var div = getDiv(grid);
                var srno = $("#resultTable tr").length;
                var pass = (marks > 19) ? "Pass" : "Fail";
                var $row = $('<tr></tr>');
                $row.append($('<td></td>').append(srno));
                $row.append($('<td></td>').append(name));
                $row.append($('<td></td>').append(div));
                $row.append($('<td></td>').append(roll));
                $row.append($('<td></td>').append(marks));
                $row.append($('<td></td>').append(pass));
                if (pass == "Pass") {
                    $row.addClass('success')
                } else {
                    $row.addClass('danger')
                }

                $("#resultTable").append($row);
            }
        </script>
    </body>    
</html>
