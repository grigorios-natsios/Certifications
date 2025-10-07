<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserRepository
{
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function getByOrg($filters = [])
    {
        $query = $this->model->where('organization_id', Auth::user()->organization_id);

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['searchEmail'])) {
            $query->where('email', 'like', '%' . $filters['searchEmail'] . '%');
        }

        return $query;
    }

    public function findById($id)
    {
        return $this->model
            ->where('id', $id)
            ->where('organization_id', Auth::user()->organization_id)
            ->firstOrFail();
    }

    public function create(array $data)
    {
        $data['organization_id'] = Auth::user()->organization_id;
        return $this->model->create($data);
    }

    public function update(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user)
    {
        return $user->delete();
    }
}
