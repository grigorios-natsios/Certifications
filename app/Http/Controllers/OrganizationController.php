<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;

class OrganizationController extends Controller
{
    public function show($id)
    {
        // Βρες το organization με τους users του
        $organization = Organization::with('users')->findOrFail($id);

        return view('organization.show', compact('organization'));
    }

}
