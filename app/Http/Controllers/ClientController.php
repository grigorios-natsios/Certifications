<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\CertificateCategory;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function index()
    {
       
        $user = Auth::user();
        $organization = $user->organization()->with('users')->first();
        $categories = CertificateCategory::all();

        return view('dashboard', compact('categories', 'organization'));
    }

    public function datatable(Request $request)
    {
        $query = Client::with('certificateCategories')
            ->where('organization_id', Auth::user()->organization_id);

        if ($request->id) {
            $query->where('id', $request->id);
        }

        if ($request->name) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->email) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        if ($request->certificate_category_id) {
            $query->whereHas('certificateCategories', function ($q) use ($request) {
                $q->where('certificate_categories.id', $request->certificate_category_id);
            });
        }

        if ($request->created_at) {
            $query->whereDate('created_at', $request->created_at);
        }

        return DataTables::of($query)
            ->addColumn('category', function($client) {
                return $client->certificateCategories->pluck('name')->join(', ');
            })
            ->addColumn('certificate_categories', function($client) {
                return $client->certificateCategories->pluck('id')->toArray();
            })
            ->addColumn('actions', function ($client) {
                $edit = '<button class="editClient bg-blue-500 text-white px-2 py-1 rounded" data-id="'.$client->id.'"> <i class="fas fa-edit"></i></button>';
                $delete = '<button class="deleteClient  bg-red-600 text-white px-2 py-1 rounded" data-id="'.$client->id.'"> <i class="fas fa-trash-alt"></i></button>';
                return $edit.' '.$delete;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        $organizations = Organization::all();
        $categories = CertificateCategory::all();
        return view('clients.create', compact('organizations', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'certificate_category_ids' => 'array',
            'certificate_category_ids.*' => 'exists:certificate_categories,id',
        ]);

        // Ο οργανισμός πάντα από τον logged-in user
        $validated['organization_id'] = Auth::user()->organization_id;

        $client = Client::create($validated);

        if (!empty($validated['certificate_category_ids'])) {
            $client->certificateCategories()->sync($validated['certificate_category_ids']);
        }

        return response()->json(['message' => 'Client created successfully']);
    }


    public function edit(Client $client)
    {
        $organizations = Organization::all();
        $categories = CertificateCategory::all();
        return view('clients.edit', compact('client', 'organizations', 'categories'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'certificate_category_ids' => 'array',
            'certificate_category_ids.*' => 'exists:certificate_categories,id',
        ]);

        $validated['organization_id'] = Auth::user()->organization_id;

        $client->update($validated);

        if (!empty($validated['certificate_category_ids'])) {
            $client->certificateCategories()->sync($validated['certificate_category_ids']);
        } else {
            $client->certificateCategories()->detach();
        }

        return response()->json(['message' => 'Client updated successfully']);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully');
    }

    public function generateForClients(Request $request)
    {
        $clientIds = $request->input('clients', []);

        if (empty($clientIds)) {
            return response()->json(['message' => 'Δεν επιλέχθηκαν πελάτες.'], 400);
        }

        $clients = Client::with('certificateCategory')->whereIn('id', $clientIds)->get();

        foreach ($clients as $client) {
            $category = $client->certificateCategory;
            if (!$category || !$category->html_template) {
                continue; // skip clients χωρίς template
            }
            
            // Παίρνουμε το SVG template
            $html = $category->html_template;

            $replacements = [
                '{{name}}' => $client->name,
                '{{category}}' => $category->name,
                '{{date}}' => date('d/m/Y'),
            ];

            $htmlModified = str_replace(array_keys($replacements), array_values($replacements), $html);

            $pdf = Pdf::loadHTML($htmlModified)
          ->setPaper([0, 0, 841.89, 595.28], 'landscape'); // A4 landscape σε points


            $pdfPath = storage_path('app/public/pdfs/'.$client->id.'.pdf');

            if (!file_exists(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0777, true);
            }

            $pdf->save($pdfPath);
        }

        return response()->json(['message' => 'Τα PDF certificates δημιουργήθηκαν επιτυχώς.']);
    }
    
}
