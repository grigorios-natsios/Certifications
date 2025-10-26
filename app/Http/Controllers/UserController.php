<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('users');
    }

    public function getUsers(Request $request)
    {
        $query = $this->service->listUsers($request->only(['role', 'searchEmail']));

        return DataTables::of($query)
            ->addColumn('actions', function ($row) {
            return'
                <div class="flex space-x-2 justify-center">
                    <button 
                        class="editUser w-8 h-8 flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white rounded-full transition" 
                        data-id="'.$row->id.'" 
                        title="'.__('Επεξεργασία').'">
                        <i class="fas fa-edit text-sm"></i>
                    </button>

                    <button 
                        class="deleteUser w-8 h-8 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-full transition" 
                        data-id="'.$row->id.'" 
                        title="'.__('Διαγραφή').'">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>
                </div>'
            ;})
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $this->service->createUser($validated);

        return response()->json([
            'success' => true,
            'message' => __('Ο χρήστης προστέθηκε επιτυχώς!')
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|confirmed|min:8',
        ]);

        $this->service->updateUser($id, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Ο χρήστης ενημερώθηκε επιτυχώς!')
        ]);
    }

    public function destroy($id)
    {
        $this->service->deleteUser($id);
        return response()->json([
            'success' => true,
            'message' => __('Ο χρήστης διαγράφηκε επιτυχώς!')
        ]);
    }
}
