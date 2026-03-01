<?php

namespace App\Http\Controllers\API\Imports;

use App\Http\Controllers\Controller;
use App\Imports\ItemsImport;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportsController extends Controller
{
    // Staff/Admin: bulk import items (e.g., legacy list)
    public function importItems(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ]);

        Excel::import(new ItemsImport($request->user()->id), $request->file('file'));

        return response()->json(['message' => 'Items imported successfully.']);
    }

    // Admin: bulk import users
    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ]);

        Excel::import(new UsersImport(), $request->file('file'));

        return response()->json(['message' => 'Users imported successfully.']);
    }
}