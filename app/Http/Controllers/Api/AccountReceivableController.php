<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountReceivableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountReceivableController extends Controller
{
    protected $arService;

    public function __construct(AccountReceivableService $arService)
    {
        $this->arService = $arService;
    }

    /**
     * Display a listing of the accounts receivable (optional).
     */
    public function index(Request $request)
    {
        $data = $this->arService->getAll($request);
        return response()->json([
            'message' => 'Accounts receivables fetched successfully!',
            'data' => $data
        ], 200);
    }

    /**
     * Store a newly created accounts receivable and details (pending approval).
     * Expected payload structure (example):
     * {
     *   "companyId": 1,
     *   "receiverType": "Receive From Dealer",
     *   "receiverId": 10,
     *   "voucher": "PRXXXXXX",
     *   "voucherDate": "2024-12-07",
     *   "invoiceType": "Invoice With Voucher",
     *   "paymentFor": "2",
     *   "paymentMode": "Online Banking",
     *   "paymentTypeId": 11,
     *   "transactionId": "122334",
     *   "checkNo": "",
     *   "checkDate": null,
     *   "ref": "dder",
     *   "totalAmount": 32900,
     *   "note": "haha",
     *   "invoiceData": [
     *     {
     *       "id": 6,
     *       "saleId": "FSO24110006",
     *       "totalAmount": "26000"
     *     },
     *     {
     *       "id": 7,
     *       "saleId": "FSO24110007",
     *       "totalAmount": "6900"
     *     }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        try {
            $paymentEntries = $request->input('payments');
            
            // Wrap single object as array if necessary
            if (!is_array($paymentEntries)) {
                $paymentEntries = [$paymentEntries];
            }
    
            foreach ($paymentEntries as $entry) {
                $this->arService->createWithDetails($entry);
            }
    
            return response()->json([
                'message' => 'Payment Receive Info created successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
        
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $record = $this->arService->getById($id);

        if (!$record) {
            return response()->json([
                'message' => 'Accounts Receivable not found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Accounts Receivable retrieved successfully',
            'data' => $record
        ], 200);
    }

    /**
     * Update the specified resource in storage (e.g. to approve).
     * You can handle approval by updating status to 'Approved'.
     */
    public function update(Request $request, $id)
    {
        // For example, handle approval
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Approved,Rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updated = $this->arService->update($id, $request->all());

            if (!$updated) {
                return response()->json(['message' => 'Resource not found or not updated'], 404);
            }

            return response()->json([
                'message' => 'Accounts Receivable updated successfully',
                'data' => $updated
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error updating AR: '.$e->getMessage());
            return response()->json([
                'message' => 'Failed to update Accounts Receivable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (optional).
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->arService->delete($id);

            if (!$deleted) {
                return response()->json(['message' => 'Accounts Receivable not found'], 404);
            }

            return response()->json(['message' => 'Accounts Receivable deleted successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting AR: '.$e->getMessage());
            return response()->json([
                'message' => 'Failed to delete Accounts Receivable',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
