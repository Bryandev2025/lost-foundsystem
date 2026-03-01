<?php

namespace App\Http\Controllers\API\Reports;

use App\Exports\ClaimsExport;
use App\Exports\ItemsExport;
use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\Audit\ActivityLogger;

class ReportsController extends Controller
{
    // Excel: export items
    public function exportItemsExcel(Request $request)
    {
        $type = $request->query('type');
        $status = $request->query('status');
        ActivityLogger::log($request->user()->id, 'EXPORT_ITEMS_EXCEL', 'report', null, []);
        return Excel::download(new ItemsExport($type, $status), 'items.xlsx');
    }

    // Excel: export claims
    public function exportClaimsExcel(Request $request)
    {
        $status = $request->query('status');
        ActivityLogger::log($request->user()->id, 'EXPORT_CLAIMS_EXCEL', 'report', null, []);
        return Excel::download(new ClaimsExport($status), 'claims.xlsx');
    }

    // PDF: list report
    public function exportItemsPdf(Request $request)
    {
        $q = Item::query()->with('reporter:id,name');

        if ($request->filled('type'))
            $q->where('type', $request->query('type'));
        if ($request->filled('status'))
            $q->where('status', $request->query('status'));

        $items = $q->latest()->get();

        ActivityLogger::log($request->user()->id, 'EXPORT_ITEMS_PDF', 'report', null, []);
        $pdf = Pdf::loadView('pdf.items-list', compact('items'));
        return $pdf->download('items-report.pdf');
    }

    // PRINT: Item sheet (includes image + QR)
    public function printItem(Request $request, Item $item)
    {
        $item->load('reporter:id,name');

        // Ensure QR token exists
        if (!$item->qr_token) {
            $item->update([
                'qr_token' => (string) \Illuminate\Support\Str::uuid(),
                'qr_generated_at' => now(),
            ]);
        }

        // Generate QR PNG temporarily (for embedding into PDF)
        $png = QrCode::format('png')->size(220)->margin(1)->generate($item->qr_token);
        $tmpPath = 'tmp/qr_' . $item->id . '.png';
        Storage::disk('local')->put($tmpPath, $png);

        $qrPngPath = storage_path('app/' . $tmpPath);

        $pdf = Pdf::loadView('pdf.item-show', compact('item', 'qrPngPath'));

        // Cleanup is optional; you can keep it or delete it later via CRON
        // Storage::disk('local')->delete($tmpPath);

        ActivityLogger::log($request->user()->id, 'PRINT_ITEM', 'item', $item->id, []);
        return $pdf->stream('item-' . $item->id . '.pdf');
    }

    // PRINT: Claim slip
    public function printClaimSlip(Request $request, Claim $claim)
    {
        $claim->load(['item:id,title', 'claimer:id,name', 'reviewer:id,name']);

        $pdf = Pdf::loadView('pdf.claim-slip', compact('claim'));
        ActivityLogger::log($request->user()->id, 'PRINT_CLAIM_SLIP', 'claim', $claim->id, []);
        return $pdf->stream('claim-' . $claim->id . '-slip.pdf');
    }
}