<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaluwaganPageController extends Controller
{
    public function index()
    {
        return view('user.PaluwaganPage');
    }
}
