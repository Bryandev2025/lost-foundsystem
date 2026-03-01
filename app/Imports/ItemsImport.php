<?php

namespace App\Imports;

use App\Models\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsImport implements ToCollection, WithHeadingRow
{
    public function __construct(private int $reportedByUserId)
    {
    }

    public function collection(Collection $rows)
    {
        // Expected headings:
        // type,title,description,category,brand,color,location,date_incident
        foreach ($rows as $row) {

            $data = [
                'type' => $row['type'] ?? null,
                'title' => $row['title'] ?? null,
                'description' => $row['description'] ?? null,
                'category' => $row['category'] ?? null,
                'brand' => $row['brand'] ?? null,
                'color' => $row['color'] ?? null,
                'location' => $row['location'] ?? null,
                'date_incident' => $row['date_incident'] ?? null,
            ];

            Validator::make($data, [
                'type' => ['required', 'in:lost,found'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'category' => ['nullable', 'string', 'max:100'],
                'brand' => ['nullable', 'string', 'max:100'],
                'color' => ['nullable', 'string', 'max:50'],
                'location' => ['nullable', 'string', 'max:255'],
                'date_incident' => ['nullable', 'date'],
            ])->validate();

            Item::create([
                ...$data,
                'status' => 'pending',
                'reported_by' => $this->reportedByUserId,
                'found_by' => ($data['type'] === 'found') ? $this->reportedByUserId : null,
            ]);
        }
    }
}