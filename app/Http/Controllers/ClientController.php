<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);
    

        $file = $request->file('file')->getPathname();
        $organizationId = Auth::user()->organization_id;

        if (($handle = fopen($file, 'r')) !== false) {
            $rowIndex = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($rowIndex === 0) {
                    $rowIndex++;
                    continue; // skip header
                }

                // assume CSV columns: Name, Email
                $name = $data[0] ?? null;
                $email = $data[1] ?? null;

                if ($email) {
                    Client::updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => $name,
                            'organization_id' => $organizationId
                        ]
                    );
                }

                $rowIndex++;
            }
            fclose($handle);
        }

        return back()->with('message', 'Clients imported successfully!');
    }
}
