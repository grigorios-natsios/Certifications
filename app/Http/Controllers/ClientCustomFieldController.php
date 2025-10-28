<?php

namespace App\Http\Controllers;

use App\Models\ClientCustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ClientCustomFieldController extends Controller
{
    public function index()
    {
        return view('custom_fields');
    }

    public function datatable()
    {
        $fields = ClientCustomField::where('organization_id', Auth::user()->organization_id);

        return DataTables::of($fields)
            ->addColumn('actions', function ($field) {
                return '
                    <div class="flex space-x-2 justify-center">
                        <button class="editField w-8 h-8 flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white rounded-full transition" 
                            data-id="'.$field->id.'" 
                            data-name="'.$field->name.'" 
                            data-type="'.$field->type.'"
                            data-required="'.($field->is_required ? 1 : 0).'">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button class="deleteField w-8 h-8 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-full transition" 
                            data-id="'.$field->id.'">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:text,number,date,select,checkbox',
            'is_required' => 'boolean'
        ]);

        $validated['organization_id'] = Auth::user()->organization_id;

        ClientCustomField::create($validated);

        return response()->json(['success' => true, 'message' => 'Το πεδίο δημιουργήθηκε!']);
    }

    public function update(Request $request, ClientCustomField $custom_field)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:text,number,date,select,checkbox',
            'is_required' => 'boolean'
        ]);

        $custom_field->update($validated);

        return response()->json(['success' => true, 'message' => 'Το πεδίο ενημερώθηκε!']);
    }

    public function destroy(ClientCustomField $custom_field)
    {
        $custom_field->delete();
        return response()->json(['success' => true, 'message' => 'Το πεδίο διαγράφηκε!']);
    }
}
