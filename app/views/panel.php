<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">        
        <title></title>       
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="<?= asset('css/bootstrap.min.css') ?>" rel="stylesheet">
        <link href="<?= asset('css/jquery.fileupload.css') ?>" rel="stylesheet">
        <link href="<?= asset('css/jquery.fileupload-ui.css') ?>" rel="stylesheet">
        <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
        <noscript><link rel="stylesheet" href="<?= asset("css/jquery.fileupload-noscript.css") ?>"></noscript>
        <noscript><link rel="stylesheet" href="<?= asset("css/jquery.fileupload-ui-noscript.css") ?>"></noscript>
    </head>
    <body><div class="navbar navbar-default navbar-fixed-top">
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
                        <p>Result Table</p>
                    </blockquote>                    
                </div>
                <div class="tab-pane" id="answers">
                    <blockquote>
                        <p>Grid for Entering Correct Answers</p>
                    </blockquote>                    
                </div>                
                <div class="tab-pane" id="links">
                    <blockquote>
                        <p>Links to answer sheet pdf and other documents</p>
                    </blockquote>                    
                </div>                
            </div>

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
        </div>
        <script src="js/jquery.min.js"></script>        
        <script src="js/vendor/jquery.ui.widget.js"></script>        
        <script src="js/jquery.iframe-transport.js"></script>        
        <script src="js/jquery.fileupload.js"></script>        
        <script src="js/bootstrap.min.js"></script>
        <script>
            /*jslint unparam: true */
            /*global window, $ */
            $(function() {
                'use strict';

                $('#fileupload').fileupload({
                    url: 'parse',
                    dataType: 'json',
                    sequentialUploads: true,
                    done: function(e, data) {
                        $.each(data.files, function(index, file) {
                            $("#statusText").text('File ' + data.files[0].name + " interpreted Successfully")
                        });
                    },
                    start: function(e, data) {
                        if (data.loaded = data.total) {
                            $("#statusText").text("Uploading " + data.files[0].name);
                        }
                    },
                    progress: function(e, data) {
                        if (data.loaded = data.total) {
                            $("#statusText").text("Interpreting " + data.files[0].name);
                        }

                    },
                    progressall: function(e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#progress .progress-bar').css(
                                'width',
                                progress + '%'
                                );
                    }
                }).prop('disabled', !$.support.fileInput)
                        .parent().addClass($.support.fileInput ? undefined : 'disabled');
            });
        </script>
    </body>    
</html>
