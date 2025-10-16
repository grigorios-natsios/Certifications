<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;

class OrganizationController extends Controller
{
    public function show($id)
    {
        // Βρες το organization με τους users του
       $organization = Organization::with('users')
        ->where('id', Auth::user()->organization_id)
        ->first();

        return view('organization.show', ['organization'=>$organization]);
    }

}
