<?php

namespace App\Http\Controllers;

use App\Models\TermsOfService;

class TermsController extends Controller
{
    public function show()
    {
        $terms = TermsOfService::latest()->first();

        return view('terms.show', compact('terms'));
    }
}
