<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $repo;

    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    public function listUsers($filters = [])
    {
        return $this->repo->getByOrg($filters);
    }

    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->repo->create($data);
    }

    public function updateUser($id, array $data)
    {
        $user = $this->repo->findById($id);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // κρατάμε τον παλιό
        }

        return $this->repo->update($user, $data);
    }

    public function deleteUser($id)
    {
        $user = $this->repo->findById($id);
        return $this->repo->delete($user);
    }
}
