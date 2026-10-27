<?php
namespace App\Http\Controllers;

use App\Models\WeightTransaction;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    public function printInvoice($id)
    {
        // Eager load the sector so the print payload can show `Name (code)`.
        $transaction = WeightTransaction::with('sector:id,name,sectorCode')->findOrFail($id);
        $pdf = Pdf::loadView('print.invoice', compact('transaction'));
        return $pdf->stream('Invoice_' . $transaction->transaction_id . '.pdf');
    }

    public function printPOS($id)
    {
        $transaction = WeightTransaction::with('sector:id,name,sectorCode')->findOrFail($id);
        return view('print.pos', compact('transaction'));
    }
}
