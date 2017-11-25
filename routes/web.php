<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::get('/', 'HomeController@index')->name('home');

Route::get('/tests', 'HomeController@tests')->name('tests');

Route::get('/edit/{dict}/{groupId}', 'HomeController@edit')->name('edit');

Route::post('/submitPatch', function (Request $request) {
    $dictLib = resolve('App\Library\Services\Dict');
    $input = $request->all();
    $input = array_map(function ($value) { return ($value != null) ? $value : ""; }, $input);
    $approved = $input['approved'] ? true: false;
    $mergedIntoTei = $input['mergedIntoTei'] ? true: false;
    Log::Info($input);
    $dictLib->validateGroupIdAndDict($input['dict'], $input['groupId']);

    if ($input['approved'] && User::findOrFail(Auth::id())->role != "admin"){
        abort(400, "You have to be admin for this operation.");
    }

    $dictLib->submitPatch($input['dict'], Auth::id(), $input['groupId'],
                          array('comment' => $input['comment'], 'newFlags' => $input['newFlags'],
                                'approved'=> $approved, 'mergedIntoTei' => $mergedIntoTei,
                                'newEntry' => $input['newEntry']));
})->middleware('auth');

Route::post('/submitPatchUpdate', function (Request $request) {
    $dictLib = resolve('App\Library\Services\Dict');
    $input = $request->all();
    $input = array_map(function ($value) { return ($value != null) ? $value : ""; }, $input);
    $approved = $input['approved'] ? true: false;
    $mergedIntoTei = $input['mergedIntoTei'] ? true: false;

    $dictLib->validateDict($input['dict']);
    if (User::findOrFail(Auth::id())->role != "admin"){
        abort(400, "You have to be admin for this operation.");
    }

    $dictLib->submitPatchUpdate($input['dict'], $input['patchId'],
                                $approved, $mergedIntoTei);
})->middleware('auth');

Route::get('/makeMeAdmin', function (Request $request) {

    if (!Auth::check()) {
        return "You have to be logged in to become an admin!";
    }

    $user = Auth::user();
    if ($user->role == "admin") {
        return "You are already an admin!";
    }
    $user->role = "admin";
    $user->save();
    return "Now you are an admin!";
});
