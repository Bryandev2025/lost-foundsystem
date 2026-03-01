<?php

namespace App\Exports;

use App\Models\Claim;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClaimsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private ?string $status = null)
    {
    }

    public function query()
    {
        $q = Claim::query()->with(['item:id,title', 'claimer:id,name', 'reviewer:id,name']);

        if ($this->status) {
            $q->where('status', $this->status);
        }

        return $q->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Item',
            'Claimer',
            'Status',
            'Reviewed By',
            'Reviewed At',
            'Released At',
            'Created At',
        ];
    }

    public function map($claim): array
    {
        return [
            $claim->id,
            optional($claim->item)->title,
            optional($claim->claimer)->name,
            $claim->status,
            optional($claim->reviewer)->name,
            $claim->reviewed_at?->format('Y-m-d H:i:s'),
            $claim->released_at?->format('Y-m-d H:i:s'),
            $claim->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}