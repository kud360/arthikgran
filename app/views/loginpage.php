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
                    <a class="navbar-brand" href="https://bajajcapital.com">
                        <img style="height: 25px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALAAAABICAYAAABWdn8qAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OTdEQzAxQTVEMjcyMTFFMkJDMjlCNTlGRTExRTJCNDUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OTdEQzAxQTZEMjcyMTFFMkJDMjlCNTlGRTExRTJCNDUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo5N0RDMDFBM0QyNzIxMUUyQkMyOUI1OUZFMTFFMkI0NSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo5N0RDMDFBNEQyNzIxMUUyQkMyOUI1OUZFMTFFMkI0NSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PrEpL3UAACMlSURBVHja7F0LvBxl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"/>
                        &nbsp &nbsp Bajaj Capital Ltd.
                    </a>
                </div>                
            </div>
        </div>
        <div class="container">    
            <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
                <div class="panel panel-info" >
                    <div class="panel-heading">
                        <div class="panel-title">Sign In</div>                        
                    </div>     

                    <div style="padding-top:30px" class="panel-body" >

                        <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>

                        <form id="loginform" class="form-horizontal" role="form" action="login" method="post">
                            
                            <div id="signupalert" style="<?= (count($errors->all())) ? "" : "display:none" ?>" class="alert alert-danger">
                                <p>Error:</p>
                                <span>
                                    <?php
                                    if (count($errors->all())) {
                                        echo "Invalid Credentails. Please try again.";
                                    }
                                    ?>
                                </span>
                            </div>

                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input id="login-username" type="text" class="form-control" name="email" value="" placeholder="email">                                        
                            </div>

                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input id="login-password" type="password" class="form-control" name="password" placeholder="password" maxlength="60">
                            </div>
                            
                            <div style="margin-top:10px" class="form-group">
                                <!-- Button -->

                                <div class="col-sm-12 controls">
                                    <input type="submit" id="btn-login" class="btn btn-success" value="Login">
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="col-md-12 control">
                                    <div style="border-top: 1px solid#888; padding-top:15px; font-size:85%" >
                                        Don't have an account! 
                                        <a href="signup">
                                            Sign Up Here
                                        </a>
                                    </div>
                                </div>
                            </div>    
                        </form>     



                    </div>                     
                </div>  
            </div>
        </div>


        <script src="js/jquery.min.js"></script>        
        <script src="js/jquery.ui.widget.js"></script>        
        <script src="js/jquery.iframe-transport.js"></script>        
        <script src="js/jquery.fileupload.js"></script>        
        <script src="js/bootstrap.min.js"></script>
        <script src="js/main.js"></script>
        <script>
                                            document.querySelector("#number").addEventListener("keypress", function(evt) {
                                                if (evt.which < 48 || evt.which > 57)
                                                {
                                                    evt.preventDefault();
                                                }
                                            });
        </script>
    </body>    
</html>