<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\CertificateCategory;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ClientCustomField;
use App\Models\ClientCustomValue;

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

        return back()->with('message', __('Clients imported successfully'));
    }

    public function index()
    {
       
        $user = Auth::user();
        $organization = $user->organization()->with('users')->first();
        $customFields = ClientCustomField::where('organization_id', Auth::user()->organization_id)->get();
        $categories = CertificateCategory::all();

        return view('dashboard', compact('categories', 'organization', 'customFields'));
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

        if (!empty($request->certificate_category_id)) {
            $query->whereHas('certificateCategories', function ($q) use ($request) {
                $q->where('certificate_categories.id', $request->certificate_category_id);
            });
        }

        if ($request->created_at) {
            $query->whereDate('created_at', $request->created_at);
        }

        $customFields = $request->input('custom_fields', []);

        if(!empty($customFields)) {
            foreach($customFields as $fieldId => $value){
                $query->whereHas('customValues', function($q) use ($fieldId, $value){
                    $q->where('custom_field_id', $fieldId)
                    ->where('value', 'like', "%{$value}%");
                });
            }
        }

        return DataTables::of($query)
            ->addColumn('category', function($client) {
                return $client->certificateCategories->pluck('name')->join(', ');
            })
            ->addColumn('certificate_categories', function($client) {
                return $client->certificateCategories->pluck('id')->toArray();
            })
            ->addColumn('custom_fields', function($client) {
                return $client->customValues->mapWithKeys(fn($v) => [$v->custom_field_id => $v->value])->toArray();
            })
            ->addColumn('actions', function ($client) {
                return '
                <div class="flex space-x-2 justify-center">
                    <button 
                        class="editClient w-8 h-8 flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white rounded-full transition" 
                        data-id="'.$client->id.'" 
                        title="'.__('Επεξεργασία').'">
                        <i class="fas fa-edit text-sm"></i>
                    </button>

                    <button 
                        class="deleteClient w-8 h-8 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-full transition" 
                        data-id="'.$client->id.'" 
                        title="'.__('Διαγραφή').'">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['custom_fields', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $organizations = Organization::all();
        $categories = CertificateCategory::all();
        $customFields = ClientCustomField::where('organization_id', Auth::user()->organization_id)->get();
        return view('clients.create', compact('organizations', 'categories', 'customFields'));
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
        if ($request->filled('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                ClientCustomValue::updateOrCreate(
                    ['client_id' => $client->id, 'custom_field_id' => $fieldId],
                    ['value' => $value]
                );
            }
        }

        if (!empty($validated['certificate_category_ids'])) {
            $client->certificateCategories()->sync($validated['certificate_category_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => __('Ο πελάτης δημιουργήθηκε επιτυχώς!')
        ]);
    }


    public function edit(Client $client)
    {
        $organizations = Organization::all();
        $categories = CertificateCategory::all();
        $customFields = ClientCustomField::where('organization_id', Auth::user()->organization_id)->get();
        return view('clients.edit', compact('client', 'organizations', 'categories', 'customFields'));
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

        if ($request->filled('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                ClientCustomValue::updateOrCreate(
                    [
                        'client_id' => $client->id,
                        'custom_field_id' => $fieldId
                    ],
                    ['value' => $value]
                );
            }
        }

        if (!empty($validated['certificate_category_ids'])) {
            $client->certificateCategories()->sync($validated['certificate_category_ids']);
        } else {
            $client->certificateCategories()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => __('Ο πελάτης ενημερώθηκε επιτυχώς!')
        ]);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json([
            'success' => true,
            'message' => __('Ο πελάτης διαγράφηκε επιτυχώς!')
        ]);
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

        return response()->json([
            'success' => true,
            'message' => __('Τα PDF certificates δημιουργήθηκαν επιτυχώς.')
        ]);
    }
    
}
