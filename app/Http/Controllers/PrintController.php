<?php
namespace App\Http\Controllers;

use App\Models\WeightTransaction;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    public function printInvoice($id)
    {
        $transaction = WeightTransaction::findOrFail($id);
        $pdf = Pdf::loadView('print.invoice', compact('transaction'));
        return $pdf->stream('Invoice_' . $transaction->transaction_id . '.pdf');
    }

    public function printPOS($id)
    {
        $transaction = WeightTransaction::findOrFail($id);
        return view('print.pos', compact('transaction'));
    }
}
