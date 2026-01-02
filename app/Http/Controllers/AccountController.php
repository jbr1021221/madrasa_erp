<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Redirect to payments index (Accounts/Earnings page)
     */
    public function index()
    {
        return redirect()->route('payments.index');
    }
}
