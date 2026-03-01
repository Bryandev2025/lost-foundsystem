<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // Expected headings:
        // name,email,phone,role,password
        foreach ($rows as $row) {

            $data = [
                'name' => $row['name'] ?? null,
                'email' => $row['email'] ?? null,
                'phone' => $row['phone'] ?? null,
                'role' => $row['role'] ?? 'user',
                'password' => $row['password'] ?? 'Password123!',
            ];

            Validator::make($data, [
                'name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email', 'max:190', 'unique:users,email'],
                'phone' => ['nullable', 'string', 'max:20'],
                'role' => ['required', 'in:admin,staff,user'],
                'password' => ['required', 'string', 'min:8'],
            ])->validate();

            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
            ]);
        }
    }
}