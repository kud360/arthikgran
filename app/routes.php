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

Route::get('/login', function() {
    Debugbar::disable();
    if (Auth::check()) {
        return Redirect::to('/');
    } else {
        return View::make('loginpage');
    }
});

Route::post('/login', function() {
    Debugbar::disable();
    $rules = array(
        'email' => 'required|email|unique:users',
        'password' => 'required'        
    );

    $validator = Validator::make(Input::all(), $rules);
    if ($validator->fails()) {
        return Redirect::to('/login')->withErrors($validator);
    } else {
        if (Auth::attempt(array('email' => Input::get('email'), 'password' => Input::get('password')))) {
            return Redirect::intended('/');
        } else {
            return Redirect::to('login');
        }
    }
});

Route::get('/signup', function() {
    Debugbar::disable();
    if (Auth::check()) {
        return Redirect::to('/');
    } else {
        return View::make('signuppage');
    }
});

Route::get('/logout', function() {
    Debugbar::disable();
    if (Auth::check()) {
        Auth::logout();
    }
    return Redirect::to('login');
});

Route::post('/signup', function() {
    Debugbar::disable();

    $rules = array(
        'email' => 'required|email|unique:users',
        'firstname' => 'required',
        'lastname' => 'required',
        'number' => 'required|size:10',
        'password' => 'required|min:6'
    );

    $messages = array(
        'firstname.required' => 'First Name cannot be Blank',
        'lastname.required' => 'Last Name cannot be Blank',
        'number.size' => 'Your contact number must be exactly 10 digits long. (Please use your mobile number)'
    );

    $validator = Validator::make(Input::all(), $rules, $messages);
    if ($validator->fails()) {
        return Redirect::to('/signup')->withErrors($validator);
    } else {
        $userArray = Input::only('firstname', 'lastname', 'email', 'number', 'password');
        $userArray['password'] = Hash::make($userArray['password']);
        $user = User::create($userArray);
        if (Auth::attempt(array('email' => $user->email, 'password' => $user->password))) {
            return Redirect::to('/');
        } else {
            return Redirect::to('/signup');
        }
    }
});

Route::group(array('before' => 'auth'), function() {
    Debugbar::disable();
    Route::get('/phpinfo', function() {
        Debugbar::disable();
        phpinfo();
        return 1;
    });

    Route::get('/', function() {
        return View::make('panel');
    });


    Route::get('/test', function() {
        Debugbar::disable();
        return View::make('quickform');
    });


    Route::post('/test', function() {
        Debugbar::disable();
        $validator = Validator::make(Input::file(), array(
                    "image" => 'required|image|max:524288'
        ));

        if ($validator->fails()) {
            return Response::json(array('status' => $validator->messages()->all(':message')));
        } else {
            $omr = new OMRImage(Input::file('image'));
            return $omr->prepare()->debugImage()->response();
        }
    });

    Route::post('/parse', function() {
        Debugbar::disable();
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
                                array('file' => $upload), array('file' => 'required|image|max:524288')
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
});
