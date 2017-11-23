<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function tests()
    {
        return view('tests');
    }

    public function edit($dict, $groupId)
    {
        $dictLib = resolve('App\Library\Services\Dict');

        return view('edit', ['dict' => $dict, 'groupId' => $groupId]);
    }
}
