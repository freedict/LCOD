<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Auth::routes();

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('get-all-dict-names', function (Request $request) {
    $dictLib = resolve('App\Library\Services\Dict');
    return $dictLib->getAllDictNames();
});

Route::get('suggestion/{dict}/{word}', function (Request $request) {
    $dictLib = resolve('App\Library\Services\Dict');
    $dictLib->validateDict($request->dict);
    return $dictLib->suggestion($request->word, $request->dict);
});

Route::get('lookup/{dict}/{word}', function (Request $request) {
    $dictLib = resolve('App\Library\Services\Dict');
    $dictLib->validateDict($request->dict);
    return $dictLib->lookup($request->word, $request->dict);
});

Route::get('lookupPatchGroup/{dict}/{groupId}', function (Request $request) {
    $dictLib = resolve('App\Library\Services\Dict');
    $dictLib->validateGroupIdAndDict($request->dict, $request->groupId);
    return $dictLib->lookupPatchGroup($request->dict, $request->groupId);
});
