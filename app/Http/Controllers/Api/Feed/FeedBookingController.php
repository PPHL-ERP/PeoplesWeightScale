<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\FeedBookingRequest;
use App\Http\Resources\Feed\FeedBookingResource;
use App\Models\FeedBooking;
use App\Models\FeedBookingDetails;
use Illuminate\Http\Request;
use App\Traits\SectorFilter;

class FeedBookingController extends Controller
{
    use SectorFilter;

    public function indexOld(Request $request)
    {

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $bookingId = $request->bookingId ?? null;
        $dealerId = $request->dealerId ?? null;
        $bookingPointId = $request->bookingPointId ?? null;
        $bookingPerson = $request->bookingPerson ?? null;
        $commissionId = $request->commissionId ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;


        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);

        $status = $request->status ?? null;

        $query = FeedBooking::query();

          // Filter by bookingId
        if ($bookingId) {
            $query->where('bookingId', 'LIKE', '%' . $bookingId . '%');
        }
        // Filter by dealerId
           if ($dealerId) {
          $query->orWhere('dealerId', $dealerId);
        }
         // Filter by bookingPointId
       if ($bookingPointId) {
        $query->orWhere('bookingPointId', $bookingPointId);
      }
        // Filter by bookingPerson
        if ($bookingPerson) {
            $query->orWhere('bookingPerson', operator: $bookingPerson);
        }

        // Filter by commissionId
        if ($commissionId) {
            $query->orWhere('commissionId', operator: $commissionId);
        }

       // Filter by productId within feedBookingDetails
        if ($productId) {
            $query->whereHas('feedBookingDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
       // Filter by childCategoryId within feedBookingDetails' products
        if ($childCategoryId) {
            $query->whereHas('feedBookingDetails.product', function ($q) use ($childCategoryId) {
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
        $feed_bookings = $query->with(['dealer', 'bookingPoint', 'feedBookingDetails.product.childCategory'])->latest()->get();

        // Check if any feed bookings found
        if ($feed_bookings->isEmpty()) {
          return response()->json(['message' => 'No Feed Booking found', 'data' => []], 200);
        }

        // Use the SalesBookingResource to transform the data
        $transformedFeedBookings = FeedBookingResource::collection($feed_bookings);

        // Return DailyPrices transformed with the resource
        return response()->json([
          'message' => 'Success!',
          'data' => $transformedFeedBookings
        ], 200);
    }


    public function index(Request $request)
    {

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

         // Filters
      $bookingId       = $request->bookingId;
      $dealerId        = $request->dealerId;
      $bookingPointId  = $request->bookingPointId;
      $bookingPerson   = $request->bookingPerson;
      $commissionId   = $request->commissionId;
      $productId       = $request->productId;
      $childCategoryId = $request->childCategoryId;
      $startDate      = $request->input('startDate', $oneYearAgo);
      $endDate        = $request->input('endDate', $today);
      $status         = $request->status;
      $limit          = $request->input('limit', 100); // Default 100


        $query = FeedBooking::query();
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
          // Filter by bookingId
        if ($bookingId) {
            $query->where('bookingId', 'LIKE', '%' . $bookingId . '%');
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
        if ($productId) {
            $query->whereHas('feedBookingDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
       // Filter by childCategoryId within feedBookingDetails' products
        if ($childCategoryId) {
            $query->whereHas('feedBookingDetails.product', function ($q) use ($childCategoryId) {
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
        $feed_bookings = $query->with(['dealer', 'bookingPoint', 'feedBookingDetails.product.childCategory'])->latest()->paginate($limit);
        // Return paginated response
        return response()->json([
            'message' => 'Success!',
            'data' => FeedBookingResource::collection($feed_bookings),
            'meta' => [
                'current_page' => $feed_bookings->currentPage(),
                'last_page' => $feed_bookings->lastPage(),
                'per_page' => $feed_bookings->perPage(),
                'total' => $feed_bookings->total(),
            ]
        ], 200);

    }


    public function store(FeedBookingRequest $request)
    {
        try {

            $feed_booking = new FeedBooking();
            $feed_booking->bookingId = $request->bookingId;
            $feed_booking->dealerId = $request->dealerId;
            $feed_booking->saleCategoryId = $request->saleCategoryId;
            $feed_booking->subCategoryId = $request->subCategoryId;
            $feed_booking->childCategoryId = $request->childCategoryId;
            $feed_booking->bookingPointId = $request->bookingPointId;
            $feed_booking->bookingPerson = $request->bookingPerson;
            $feed_booking->commissionId = $request->commissionId;
            $feed_booking->bookingType = $request->bookingType;
            $feed_booking->isBookingMoney = $request->isBookingMoney;
            $feed_booking->discount = $request->discount;
            $feed_booking->discountType = $request->discountType;
            $feed_booking->advanceAmount = $request->advanceAmount;
            $feed_booking->totalAmount = $request->totalAmount;
            $feed_booking->bookingDate = $request->bookingDate;
            $feed_booking->invoiceDate = $request->invoiceDate;
            $feed_booking->note = $request->note;
            $feed_booking->crBy = auth()->id();
            $feed_booking->status = 'pending';

            $feed_booking->save();

            //dd($feed_booking);


            // Detail input START
            $bookingId = $feed_booking->id;


            foreach ($request->input('feed_bookings', []) as $detail) {
              $productId = $detail['productId'];
              $unitId = $detail['unitId'];
              $bookingPrice = $detail['bookingPrice'];
              $bookingQty = $detail['bookingQty'];
              $noteDetails = $detail['noteDetails'] ?? null;


              $fbDetail = new FeedBookingDetails();
              $fbDetail->bookingId = $bookingId;
              $fbDetail->productId = $productId;
              $fbDetail->unitId = $unitId;
              $fbDetail->bookingPrice = $bookingPrice;
              $fbDetail->bookingQty = $bookingQty;
              $fbDetail->noteDetails = $noteDetails;
              $fbDetail->save();
            }

            return response()->json([
              'message' => 'Feed Booking created successfully',
              'data' => new FeedBookingResource($feed_booking),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }


    public function show(string $id)
    {
        $feed_booking = FeedBooking::find($id);
        if (!$feed_booking) {
          return response()->json(['message' => 'Feed Booking not found'], 404);
        }
        return new FeedBookingResource($feed_booking);
    }


    public function update(FeedBookingRequest $request, string $id)
    {
        try {
            $feed_booking = FeedBooking::find($id);

            if (!$feed_booking) {
                return response()->json(['message' => 'Feed Booking not found.'], 404);
            }

            // Update the main FeedBooking fields
            $feed_booking->dealerId = $request->dealerId;
            $feed_booking->saleCategoryId = $request->saleCategoryId;
            $feed_booking->subCategoryId = $request->subCategoryId;
            $feed_booking->childCategoryId = $request->childCategoryId;
            $feed_booking->bookingPointId = $request->bookingPointId;
            $feed_booking->bookingPerson = $request->bookingPerson;
            $feed_booking->commissionId = $request->commissionId;
            $feed_booking->bookingType = $request->bookingType;
            $feed_booking->isBookingMoney = $request->isBookingMoney;
            $feed_booking->discount = $request->discount;
            $feed_booking->discountType = $request->discountType;
            $feed_booking->advanceAmount = $request->advanceAmount;
            $feed_booking->totalAmount = $request->totalAmount;
            $feed_booking->bookingDate = $request->bookingDate;
            $feed_booking->invoiceDate = $request->invoiceDate;
            $feed_booking->note = $request->note;
            $feed_booking->status = 'pending';

            $feed_booking->update();

            // Update FeedBookingDetails
            $existingDetailIds = $feed_booking->feedBookingDetails()->pluck('id')->toArray();

            foreach ($request->input('feed_bookings', []) as $detail) {
                if (isset($detail['id']) && in_array($detail['id'], $existingDetailIds)) {
                    // Update existing detail
                    $fbDetail = FeedBookingDetails::find($detail['id']);
                    $fbDetail->productId = $detail['productId'];
                    $fbDetail->unitId = $detail['unitId'];
                    $fbDetail->bookingPrice = $detail['bookingPrice'];
                    $fbDetail->bookingQty = $detail['bookingQty'];
                    $fbDetail->noteDetails = $detail['noteDetails'];
                    $fbDetail->save();

                    // Remove updated detail ID from the list of existing IDs
                    $existingDetailIds = array_diff($existingDetailIds, [$fbDetail->id]);
                } else {
                    // Create new detail if not exists
                    $fbDetail = new FeedBookingDetails();
                    $fbDetail->bookingId = $feed_booking->id;
                    $fbDetail->productId = $detail['productId'];
                    $fbDetail->unitId = $detail['unitId'];
                    $fbDetail->bookingPrice = $detail['bookingPrice'];
                    $fbDetail->bookingQty = $detail['bookingQty'];
                    $fbDetail->noteDetails = $detail['noteDetails'];
                    $fbDetail->save();
                }
            }

            // Delete removed details
            FeedBookingDetails::whereIn('id', $existingDetailIds)->delete();

            return response()->json([
                'message' => 'Feed Booking updated successfully',
                'data' => new FeedBookingResource($feed_booking),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    public function statusUpdate(Request $request, $id)
    {
      $feed_booking = FeedBooking::find($id);
      $feed_booking->status = $request->status;
      $feed_booking->appBy = auth()->id();

      $feed_booking->update();
      return response()->json([
        'message' => 'Feed Booking Status change successfully',
      ], 200);
    }
    public function destroy(string $id)
    {
        $feed_booking = FeedBooking::find($id);
        if (!$feed_booking) {
          return response()->json(['message' => 'Feed Booking not found'], 404);
        }
        $feed_booking->delete();
        return response()->json([
          'message' => 'Feed Booking deleted successfully',
        ], 200);
     }


     public function getFeedBookList()
{

    $bookList = FeedBooking::with(['dealer','bookingPoint'])
        ->where('status', 'approved')
        ->select('id', 'bookingId', 'dealerId','bookingPointId','invoiceDate')
        ->get();


    $bookList = $bookList->map(function($booking) {
        return [
            'id' => $booking->id,
            'bookingId' => $booking->bookingId,
            'dealer' => [
                'id' => $booking->dealer->id ?? null,
                'tradeName' => $booking->dealer->tradeName ?? null,
                'dealerCode' => $booking->dealer->dealerCode ?? null,
                // 'contactPerson' => $booking->dealer->contactPerson ?? null,
                // 'phone' => $booking->dealer->phone ?? null,
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
