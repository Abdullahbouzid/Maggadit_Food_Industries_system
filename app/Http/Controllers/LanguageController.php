<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        $validLocales = ['ar', 'en'];
        if (!in_array($locale, $validLocales)) {
            $locale = 'en';
        }
        session(['locale' => $locale]);
        return redirect()->back();
    }
}
