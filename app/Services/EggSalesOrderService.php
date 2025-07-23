<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class EggSalesOrderService
{
    /**
     * 
     *
     * @param string $voucherNo
     * @param string $voucherType
     * @return array|null
     */
    public function getSalesOrderDetailsWithProducts(string $voucherNo, string $voucherType): ?array
    {
        if ($voucherType === 'PaymentRecieve') {
            return null;
        }

        $salesOrder = DB::table('sales_orders as so')
            ->leftJoin('sales_order_details as sod', 'so.id', '=', 'sod.saleId')
            ->leftJoin('products as p', 'sod.productId', '=', 'p.id')
            ->select(
                'so.id as salesOrderId',
                'so.saleId',
                'so.saleType',
                'so.totalAmount',
                'so.dueAmount',
                'so.salesPointId as sectorId',
                'sod.productId',
                'p.productName',
                'sod.qty',
                'sod.tradePrice',
                'sod.salePrice',
                'p.batchNo'
            )
            ->where('so.saleId', $voucherNo)
            // ->where('so.saleType', $voucherType)
            ->get();

        return $salesOrder->isEmpty() ? null : $salesOrder->toArray();
    }
}
