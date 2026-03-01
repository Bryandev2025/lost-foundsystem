<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private ?string $type = null,
        private ?string $status = null
    ) {
    }

    public function query()
    {
        $q = Item::query()->with('reporter:id,name');

        if ($this->type) {
            $q->where('type', $this->type);
        }
        if ($this->status) {
            $q->where('status', $this->status);
        }

        return $q->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Type',
            'Title',
            'Category',
            'Location',
            'Date Incident',
            'Status',
            'Reported By',
            'Created At',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->type,
            $item->title,
            $item->category,
            $item->location,
            optional($item->date_incident)->format('Y-m-d'),
            $item->status,
            optional($item->reporter)->name,
            $item->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}