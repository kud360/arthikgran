<?php

class QuickController extends BaseController {
    /*
      |--------------------------------------------------------------------------
      | Quick Controller
      |--------------------------------------------------------------------------
      |
      | You may wish to use controllers instead of, or in addition to, Closure
      | based routes. That's great! Here is an example controller method to
      | get you started. To route to this controller, just add the route:
      |
      |	Route::get('/', 'HomeController@showWelcome');
      |
     */

    public function single() {
        return View::make('quickform');
    }

    public function parseSingle() {

        $validator = Validator::make(Input::all(), array(
            "image" => 'required|image'
        ));

        if ($validator->fails()) {
            return Redirect::to('quick/single')->withErrors($validator);
        }   else    {
            $omr = OMRImage::make(Image::make(Input::file('image')));            
            return View::make('quickresult')>with('image',$omr->image());
        }                
    }

}
