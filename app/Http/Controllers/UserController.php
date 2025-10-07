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
                return '
                    <button class="editUser bg-blue-500 text-white px-2 py-1 rounded" data-id="'.$row->id.'">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="deleteUser bg-red-600 text-white px-2 py-1 rounded"data-id="'.$row->id.'">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
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

        return response()->json(['success' => true, 'message' => 'Ο χρήστης προστέθηκε επιτυχώς!']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|confirmed|min:8',
        ]);

        $this->service->updateUser($id, $validated);

        return response()->json(['success' => true, 'message' => 'Ο χρήστης ενημερώθηκε επιτυχώς!']);
    }

    public function destroy($id)
    {
        $this->service->deleteUser($id);
        return response()->json(['success' => true, 'message' => 'Ο χρήστης διαγράφηκε επιτυχώς!']);
    }
}
