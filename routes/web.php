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

Route::post('/submitPatch/', function (Request $request) {
    $dictLib = resolve('App\Library\Services\Dict');
    $input = $request->all();
    $input = array_map(function ($value) { return ($value != null) ? $value : ""; }, $input);

    $dictLib->validateGroupIdAndDict($input['dict'], $input['groupId']);

    if ($input['approved'] && User::findOrFail(Auth::id())->role != "admin"){
        abort(400, "You have to be admin for this operation.");
    }

    $dictLib->submitPatch($input['dict'], $input['keywords'], Auth::id(),
                          $input['groupId'], $input['newEntry'],
                          $input['comment'], $input['flags'],
                          $input['approved'], $input['mergedIntoTei']);
})->middleware('auth');

Route::post('/submitPatchUpdate/', function (Request $request) {
    $dictLib = resolve('App\Library\Services\Dict');
    $input = $request->all();
    $input = array_map(function ($value) { return ($value != null) ? $value : ""; }, $input);

    $dictLib->validateDict($input['dict']);

    if (User::findOrFail(Auth::id())->role != "admin"){
        abort(400, "You have to be admin for this operation.");
    }

    $dictLib->submitPatchUpdate($input['dict'], $input['patchId'],
                                $input['approved'], $input['mergedIntoTei'], $input['flags']);
})->middleware('auth');
