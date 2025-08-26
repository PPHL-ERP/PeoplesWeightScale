<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\SalesEmployeeFlat;

class SalesEmployeeSyncService
{
    public function sync()
    {
        $apiUrl = 'https://hrm.peoplesitsolution.com/api/get-sales-employee-list';
        $response = Http::get($apiUrl);

        if (!$response->ok()) {
            return ['status' => false, 'message' => 'API অনুরোধ ব্যর্থ হয়েছে'];
        }

        $employees = $response->json('data');
        $newCount = 0;
        $updatedCount = 0;

        foreach ($employees as $item) {
            $record = SalesEmployeeFlat::where('employeeName', $item['employeeName'])
                ->where('companyName', $item['companyName'])
                ->whereDate('jDate', $item['jDate'])
                ->first();

            $data = [
                'phone_number'     => $item['employeephone'] ?? null,
                'sectorName'       => $item['sectorName'],
                'departmentName'   => $item['departmentName'],
                'designationName'  => $item['designationName'],
                'status'           => $item['status'],
                'sGross'           => $item['sGross'],
                'tLeave'           => $item['tLeave'],
            ];

            if (!$record) {
                // নতুন রেকর্ড ইনসার্ট
                SalesEmployeeFlat::create(array_merge([
                    'employeeId' => $item['employeeId'],

                    'employeeName' => $item['employeeName'],
                    'companyName'  => $item['companyName'],
                    'jDate'        => $item['jDate'],
                ], $data));
                $newCount++;
            } else {
                // বিদ্যমান রেকর্ড আপডেট যদি কিছু পরিবর্তন হয়
                $changed = false;
                foreach ($data as $key => $value) {
                    if ($record->{$key} != $value) {
                        $record->{$key} = $value;
                        $changed = true;
                    }
                }
                if ($changed) {
                    $record->save();
                    $updatedCount++;
                }
            }
        }

        return [
            'status' => true,
            'new' => $newCount,
            'updated' => $updatedCount,
            'message' => "সিঙ্ক সম্পন্ন হয়েছে। নতুন: {$newCount}, আপডেট হয়েছে: {$updatedCount}"
        ];
    }
}
