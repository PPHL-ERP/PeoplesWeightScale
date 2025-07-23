<?php

namespace App\Http\Controllers\Api\Chicks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chicks\ChicksBookingRequest;
use App\Http\Resources\Chicks\ChicksBookingResource;
use App\Models\ChicksBooking;
use App\Models\ChicksBookingDetail;
use Illuminate\Http\Request;
use App\Traits\SectorFilter;

class ChicksBookingController extends Controller
{
    use SectorFilter;
    public function index(Request $request)
    {

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

         // Filters
      $cBookingId       = $request->cBookingId;
      $dealerId        = $request->dealerId;
      $bookingPointId  = $request->bookingPointId;
      $bookingPerson   = $request->bookingPerson;
      $commissionId   = $request->commissionId;
      $pId       = $request->pId;
      $childCategoryId = $request->childCategoryId;
      $startDate      = $request->input('startDate', $oneYearAgo);
      $endDate        = $request->input('endDate', $today);
      $status         = $request->status;
      $limit          = $request->input('limit', 100); // Default 100


        $query = ChicksBooking::query();
        // ✅ Sector-based filter
        $userId = auth()->id();
        $canPass = $this->adminFilter($userId);

        if (!$canPass) {
            $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

            if (!empty($sectorIds)) {
                $query->where(function ($q) use ($sectorIds) {
                    $q->whereIn('bookingPointId', $sectorIds);
                });
            } else {
                return response()->json(['message' => 'No sector access assigned.'], 403);
            }
        }
          // Filter by cBookingId
        if ($cBookingId) {
            $query->where('cBookingId', 'LIKE', '%' . $cBookingId . '%');
        }
        // Filter by dealerId
           if ($dealerId) {
          $query->Where('dealerId', $dealerId);
        }
         // Filter by bookingPointId
       if ($bookingPointId) {
        $query->Where('bookingPointId', $bookingPointId);
      }
        // Filter by bookingPerson
        if ($bookingPerson) {
            $query->Where('bookingPerson', operator: $bookingPerson);
        }

        // Filter by commissionId
        if ($commissionId) {
            $query->Where('commissionId', operator: $commissionId);
        }

       // Filter by productId within feedBookingDetails
        if ($pId) {
            $query->whereHas('chicksBookingDetails', function ($q) use ($pId) {
                $q->where('productId', $pId);
            });
        }
       // Filter by childCategoryId within chicksBookingDetails' products
        if ($childCategoryId) {
            $query->whereHas('chicksBookingDetails.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
        }
         //Filter Date
         if ($startDate && $endDate) {
          $query->whereBetween('bookingDate', [$startDate, $endDate]);
      }

        // Filter by status
        if ($status) {
          $query->where('status', $status);
        }

        // Fetch feed bookings with eager loading of related data

        //$feed_bookings = $query->latest()->get();
        $feed_bookings = $query->with(['dealer', 'bookingPoint', 'chicksBookingDetails.product.childCategory'])->latest()->paginate($limit);
        // Return paginated response
        return response()->json([
            'message' => 'Success!',
            'data' => ChicksBookingResource::collection($feed_bookings),
            'meta' => [
                'current_page' => $feed_bookings->currentPage(),
                'last_page' => $feed_bookings->lastPage(),
                'per_page' => $feed_bookings->perPage(),
                'total' => $feed_bookings->total(),
            ]
        ], 200);

    }


    public function store(ChicksBookingRequest $request)
    {
        try {

            $chicks_booking = new ChicksBooking();
            $chicks_booking->cBookingId = $request->cBookingId;
            $chicks_booking->dealerId = $request->dealerId;
            $chicks_booking->categoryId = $request->categoryId;
            $chicks_booking->subCategoryId = $request->subCategoryId;
            $chicks_booking->childCategoryId = $request->childCategoryId;
            $chicks_booking->commissionId = $request->commissionId;
            $chicks_booking->bookingPointId = $request->bookingPointId;
            $chicks_booking->chicksPriceId = $request->chicksPriceId;
            $chicks_booking->bookingPerson = $request->bookingPerson;
            $chicks_booking->bookingType = $request->bookingType;
            $chicks_booking->isBookingMoney = $request->isBookingMoney;
            $chicks_booking->isMultiDelivery = $request->isMultiDelivery;
            $chicks_booking->deliveryDetails = json_encode($request->deliveryDetails);
            $chicks_booking->discount = $request->discount;
            $chicks_booking->discountType = $request->discountType;
            $chicks_booking->advanceAmount = $request->advanceAmount;
            $chicks_booking->totalAmount = $request->totalAmount;
            $chicks_booking->bookingDate = $request->bookingDate;
            $chicks_booking->invoiceDate = $request->invoiceDate;
            $chicks_booking->note = $request->note;
            $chicks_booking->crBy = auth()->id();
            $chicks_booking->status = 'pending';

            $chicks_booking->save();

            //dd($chicks_booking);


            // Detail input START
            $cbId = $chicks_booking->id;


            foreach ($request->input('chicks_bookings', []) as $detail) {
              $pId = $detail['pId'];
              $cdPriceId = $detail['cdPriceId'];
              $unitId = $detail['unitId'];
              $bQty = $detail['bQty'];
              $salePrice = $detail['salePrice'];
              $mrp = $detail['mrp'];
              $note = $detail['note'] ?? null;
              $settingId = $detail['settingId'] ?? [];
              $flockId = $detail['flockId'] ?? [];



              $fbDetail = new ChicksBookingDetail();
              $fbDetail->cbId = $cbId;
              $fbDetail->pId = $pId;
              $fbDetail->cdPriceId = $cdPriceId;
              $fbDetail->unitId = $unitId;
              $fbDetail->bQty = $bQty;
              $fbDetail->salePrice = $salePrice;
              $fbDetail->mrp = $mrp;
              $fbDetail->note = $note;
              $fbDetail->settingId = json_encode($settingId);
              $fbDetail->flockId = json_encode($flockId);
              $fbDetail->save();
            }

            return response()->json([
              'message' => 'Chicks Booking created successfully',
              'data' => new ChicksBookingResource($chicks_booking),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }


    public function show($id)
    {
        $chicks_booking = ChicksBooking::find($id);
        if (!$chicks_booking) {
          return response()->json(['message' => 'Chicks Booking not found'], 404);
        }
        return new ChicksBookingResource($chicks_booking);
    }


    public function update(ChicksBookingRequest $request, string $id)
    {
        try {
            $chicks_booking = ChicksBooking::find($id);

            if (!$chicks_booking) {
                return response()->json(['message' => 'Chicks Booking not found.'], 404);
            }

            // Update the main ChicksBooking fields
            $chicks_booking->dealerId = $request->dealerId;
            $chicks_booking->categoryId = $request->categoryId;
            $chicks_booking->subCategoryId = $request->subCategoryId;
            $chicks_booking->childCategoryId = $request->childCategoryId;
            $chicks_booking->commissionId = $request->commissionId;
            $chicks_booking->bookingPointId = $request->bookingPointId;
            $chicks_booking->chicksPriceId = $request->chicksPriceId;
            $chicks_booking->bookingPerson = $request->bookingPerson;
            $chicks_booking->bookingType = $request->bookingType;
            $chicks_booking->isBookingMoney = $request->isBookingMoney;
            $chicks_booking->isMultiDelivery = $request->isMultiDelivery;
            $chicks_booking->deliveryDetails = json_encode($request->deliveryDetails);
            $chicks_booking->discount = $request->discount;
            $chicks_booking->discountType = $request->discountType;
            $chicks_booking->advanceAmount = $request->advanceAmount;
            $chicks_booking->totalAmount = $request->totalAmount;
            $chicks_booking->bookingDate = $request->bookingDate;
            $chicks_booking->invoiceDate = $request->invoiceDate;
            $chicks_booking->note = $request->note;
            $chicks_booking->status = 'pending';

            $chicks_booking->update();

            // Update FeedBookingDetails
            $existingDetailIds = $chicks_booking->chicksBookingDetails()->pluck('id')->toArray();

            foreach ($request->input('chicks_bookings', []) as $detail) {
                if (isset($detail['id']) && in_array($detail['id'], $existingDetailIds)) {
                    // Update existing detail
                    $fbDetail = ChicksBookingDetail::find($detail['id']);
                    $fbDetail->pId = $detail['pId'];
                    $fbDetail->cdPriceId = $detail['cdPriceId'];
                    $fbDetail->unitId = $detail['unitId'];
                    $fbDetail->bQty = $detail['bQty'];
                    $fbDetail->salePrice = $detail['salePrice'];
                    $fbDetail->mrp = $detail['mrp'];
                    $fbDetail->note = $detail['note'];
                    $fbDetail->settingId = json_encode($detail['settingId'] ?? []);
                    $fbDetail->flockId = json_encode($detail['flockId'] ?? []);

                    $fbDetail->save();

                    // Remove updated detail ID from the list of existing IDs
                    $existingDetailIds = array_diff($existingDetailIds, [$fbDetail->id]);
                } else {
                    // Create new detail if not exists
                    $fbDetail = new ChicksBookingDetail();
                    $fbDetail->cbId = $chicks_booking->id;
                    $fbDetail->pId = $detail['pId'];
                    $fbDetail->cdPriceId = $detail['cdPriceId'];
                    $fbDetail->unitId = $detail['unitId'];
                    $fbDetail->bQty = $detail['bQty'];
                    $fbDetail->salePrice = $detail['salePrice'];
                    $fbDetail->mrp = $detail['mrp'];
                    $fbDetail->note = $detail['note'];
                    $fbDetail->settingId = json_encode($detail['settingId'] ?? []);
                    $fbDetail->flockId = json_encode($detail['flockId'] ?? []);

                    $fbDetail->save();
                }
            }

            // Delete removed details
            ChicksBookingDetail::whereIn('id', $existingDetailIds)->delete();

            return response()->json([
                'message' => 'Chicks Booking updated successfully',
                'data' => new ChicksBookingResource($chicks_booking),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function statusUpdate(Request $request, $id)
    {
      $chicks_booking = ChicksBooking::find($id);
      $chicks_booking->status = $request->status;
      $chicks_booking->appBy = auth()->id();

      $chicks_booking->update();
      return response()->json([
        'message' => 'Chicks Booking Status change successfully',
      ], 200);
    }

    public function destroy($id)
    {
        $chicks_booking = ChicksBooking::find($id);
        if (!$chicks_booking) {
          return response()->json(['message' => 'Chicks Booking not found'], 404);
        }
        $chicks_booking->delete();
        return response()->json([
          'message' => 'Chicks Booking deleted successfully',
        ], 200);
     }


     public function getChicksBookList()
     {

         $bookList = ChicksBooking::with(['dealer','bookingPoint'])
             ->where('status', 'approved')
             ->select('id', 'cBookingId', 'dealerId','bookingPointId','invoiceDate')
             ->get();


         $bookList = $bookList->map(function($booking) {
             return [
                 'id' => $booking->id,
                 'cBookingId' => $booking->cBookingId,
                 'dealer' => [
                     'id' => $booking->dealer->id ?? null,
                     'tradeName' => $booking->dealer->tradeName ?? null,
                     'dealerCode' => $booking->dealer->dealerCode ?? null,
                     'zoneName' => $booking->dealer->zone->zoneName ?? null,
                 ],
                 'bookingPoint' => [
                     'id' => $booking->bookingPoint->id ?? null,
                     'name' => $booking->bookingPoint->name ?? null,
                 ],
                 'invoiceDate' => $booking->invoiceDate,

             ];
         });

         return response()->json([
             'data' => $bookList
         ], 200);
     }
}