<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */

Route::get('/', function() {
    return View::make('panel');
});

Route::get('/phpinfo', function() {
    phpinfo();
    return 1;
});

Route::get('/test', function() {
    return View::make('quickform');
});


Route::post('/test', function() {
    $validator = Validator::make(Input::file(), array(
                "image" => 'required|image|max:2048'
    ));

    if ($validator->fails()) {
        return Response::json(array('status' => $validator->messages()->all(':message')));
    } else {
        $omr = new OMRImage(Input::file('image'));
        return $omr->prepare()->debugImage()->response();
    }
});

Route::post('/parse', function() {

    if (Input::hasFile('files')) {
        $all_uploads = Input::file('files');

        // Make sure it really is an array
        if (!is_array($all_uploads)) {
            $all_uploads = array($all_uploads);
        }

        $error_messages = array();

        // Loop through all uploaded files
        foreach ($all_uploads as $upload) {
            // Ignore array member if it's not an UploadedFile object, just to be extra safe
            if (!is_a($upload, 'Symfony\Component\HttpFoundation\File\UploadedFile')) {
                continue;
            }

            $validator = Validator::make(
                            array('file' => $upload), array('file' => 'required|image|max:2048')
            );

            if ($validator->passes()) {
                $omr = new OMRImage($upload);
                return Response::json(array("files" => array($upload->getClientOriginalName()), "grid" => $omr->prepare()->getGrid()));
            } else {
                // Collect error messages
                $error_messages[] = 'File "' . $upload->getClientOriginalName() . '":' . $validator->messages()->first('file');
            }
        }

        // Redirect, return JSON, whatever...
        return Response::json(array('errors' => $error_messages));
    } else {
        return Response::json(array('errors' => array('No Files Uploaded')));
        // No files have been uploaded
    }
});

