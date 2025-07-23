<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesBookingRequest;
use App\Http\Resources\SalesBookingResource;
use App\Models\Product;
use App\Models\SalesBooking;
use App\Models\SalesBookingDetails;
use Illuminate\Http\Request;

class SalesBookingController extends Controller
{

    public function index1(Request $request)
    {

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $bookingId = $request->bookingId ?? null;
        $dealerId = $request->dealerId ?? null;
        $bookingPointId = $request->bookingPointId ?? null;
        $bookingPerson = $request->bookingPerson ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;


        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);

        $status = $request->status ?? null;

        $query = SalesBooking::query();

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

       // Filter by productId within salesBookingDetails
        if ($productId) {
            $query->whereHas('salesBookingDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
       // Filter by childCategoryId within salesBookingDetails' products
        if ($childCategoryId) {
            $query->whereHas('salesBookingDetails.product', function ($q) use ($childCategoryId) {
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

        // Fetch sales bookings with eager loading of related data

        //$sales_bookings = $query->latest()->get();
        $sales_bookings = $query->with(['dealer', 'bookingPoint', 'salesBookingDetails.product.childCategory'])->latest()->get();

        // Check if any sales bookings found
        if ($sales_bookings->isEmpty()) {
          return response()->json(['message' => 'No Sales Booking found', 'data' => []], 200);
        }

        // Use the SalesBookingResource to transform the data
        $transformedSalesBookings = SalesBookingResource::collection($sales_bookings);

        // Return DailyPrices transformed with the resource
        return response()->json([
          'message' => 'Success!',
          'data' => $transformedSalesBookings
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
      $productId       = $request->productId;
      $childCategoryId = $request->childCategoryId;
      $startDate      = $request->input('startDate', $oneYearAgo);
      $endDate        = $request->input('endDate', $today);
      $status         = $request->status;
      $limit          = $request->input('limit', 100); // Default 100

        $query = SalesBooking::query();

          // Filter by bookingId
        if ($bookingId) {
            $query->where('bookingId', 'LIKE', '%' . $bookingId . '%');
        }
        // Filter by dealerId
           if ($dealerId) {
          $query->where('dealerId', $dealerId);
        }
         // Filter by bookingPointId
       if ($bookingPointId) {
        $query->where('bookingPointId', $bookingPointId);
      }
        // Filter by bookingPerson
        if ($bookingPerson) {
            $query->where('bookingPerson', operator: $bookingPerson);
        }

       // Filter by productId within salesBookingDetails
        if ($productId) {
            $query->whereHas('salesBookingDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
       // Filter by childCategoryId within salesBookingDetails' products
        if ($childCategoryId) {
            $query->whereHas('salesBookingDetails.product', function ($q) use ($childCategoryId) {
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

        // Fetch sales bookings with eager loading of related data

        //$sales_bookings = $query->latest()->get();
        $sales_bookings = $query->with(['dealer', 'bookingPoint', 'salesBookingDetails.product.childCategory'])->latest()->paginate($limit);

       // Return paginated response
       return response()->json([
        'message' => 'Success!',
        'data' => SalesBookingResource::collection($sales_bookings),
        'meta' => [
            'current_page' => $sales_bookings->currentPage(),
            'last_page' => $sales_bookings->lastPage(),
            'per_page' => $sales_bookings->perPage(),
            'total' => $sales_bookings->total(),
        ]
    ], 200);
    }


    public function store(SalesBookingRequest $request)
    {
        try {

            $sales_booking = new SalesBooking();
            $sales_booking->bookingId = $request->bookingId;
            $sales_booking->dealerId = $request->dealerId;
            $sales_booking->saleCategoryId = $request->saleCategoryId;
            $sales_booking->bookingPointId = $request->bookingPointId;
            $sales_booking->bookingPerson = $request->bookingPerson;
            $sales_booking->bookingType = $request->bookingType;
            $sales_booking->isBookingMoney = $request->isBookingMoney;
            $sales_booking->discount = $request->discount;
            $sales_booking->discountType = $request->discountType;
            $sales_booking->advanceAmount = $request->advanceAmount;
            $sales_booking->totalAmount = $request->totalAmount;
            $sales_booking->bookingDate = $request->bookingDate;
            $sales_booking->invoiceDate = $request->invoiceDate;
            $sales_booking->note = $request->note;
            $sales_booking->crBy = auth()->id();
            $sales_booking->status = 'pending';

            $sales_booking->save();

            //dd($sales_booking);


            // Detail input START
            $bookingId = $sales_booking->id;


            foreach ($request->input('sales_bookings', []) as $detail) {
              $productId = $detail['productId'];
              $unitId = $detail['unitId'];
              $bookingPrice = $detail['bookingPrice'];
              $bookingQty = $detail['bookingQty'];
              //$noteDetails = $detail['noteDetails'];
              $noteDetails = $detail['noteDetails'] ?? null;


              $sbDetail = new SalesBookingDetails();
              $sbDetail->bookingId = $bookingId;
              $sbDetail->productId = $productId;
              $sbDetail->unitId = $unitId;
              $sbDetail->bookingPrice = $bookingPrice;
              $sbDetail->bookingQty = $bookingQty;
              $sbDetail->noteDetails = $noteDetails;
              $sbDetail->save();
            }

            return response()->json([
              'message' => 'Sales Booking created successfully',
              'data' => new SalesBookingResource($sales_booking),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
        }

    public function show($id)
    {
        $sales_booking = SalesBooking::find($id);
        if (!$sales_booking) {
          return response()->json(['message' => 'Sales Booking not found'], 404);
        }
        return new SalesBookingResource($sales_booking);
    }

    public function update(SalesBookingRequest $request, $id)
{
    try {
        $sales_booking = SalesBooking::find($id);

        if (!$sales_booking) {
            return response()->json(['message' => 'Sales Booking not found.'], 404);
        }

        // Update the main SalesBooking fields
        $sales_booking->dealerId = $request->dealerId;
        $sales_booking->saleCategoryId = $request->saleCategoryId;
        $sales_booking->bookingPointId = $request->bookingPointId;
        $sales_booking->bookingPerson = $request->bookingPerson;
        $sales_booking->bookingType = $request->bookingType;
        $sales_booking->isBookingMoney = $request->isBookingMoney;
        $sales_booking->discount = $request->discount;
        $sales_booking->discountType = $request->discountType;
        $sales_booking->advanceAmount = $request->advanceAmount;
        $sales_booking->totalAmount = $request->totalAmount;
        $sales_booking->bookingDate = $request->bookingDate;
        $sales_booking->invoiceDate = $request->invoiceDate;
        $sales_booking->note = $request->note;
        $sales_booking->status = 'pending';

        $sales_booking->update();

        // Update SalesBookingDetails
        $existingDetailIds = $sales_booking->salesBookingDetails()->pluck('id')->toArray();

        foreach ($request->input('sales_bookings', []) as $detail) {
            if (isset($detail['id']) && in_array($detail['id'], $existingDetailIds)) {
                // Update existing detail
                $sbDetail = SalesBookingDetails::find($detail['id']);
                $sbDetail->productId = $detail['productId'];
                $sbDetail->unitId = $detail['unitId'];
                $sbDetail->bookingPrice = $detail['bookingPrice'];
                $sbDetail->bookingQty = $detail['bookingQty'];
                $sbDetail->noteDetails = $detail['noteDetails'];
                $sbDetail->save();

                // Remove updated detail ID from the list of existing IDs
                $existingDetailIds = array_diff($existingDetailIds, [$sbDetail->id]);
            } else {
                // Create new detail if not exists
                $sbDetail = new SalesBookingDetails();
                $sbDetail->bookingId = $sales_booking->id;
                $sbDetail->productId = $detail['productId'];
                $sbDetail->unitId = $detail['unitId'];
                $sbDetail->bookingPrice = $detail['bookingPrice'];
                $sbDetail->bookingQty = $detail['bookingQty'];
                $sbDetail->noteDetails = $detail['noteDetails'];
                $sbDetail->save();
            }
        }

        // Delete removed details
        SalesBookingDetails::whereIn('id', $existingDetailIds)->delete();

        return response()->json([
            'message' => 'Sales Booking updated successfully',
            'data' => new SalesBookingResource($sales_booking),
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

    public function statusUpdate(Request $request, $id)
    {
      $sales_booking = SalesBooking::find($id);
      $sales_booking->status = $request->status;
      $sales_booking->appBy = auth()->id();

      $sales_booking->update();
      return response()->json([
        'message' => 'Sales Booking Status change successfully',
      ], 200);
    }

    public function destroy($id)
    {
        $sales_booking = SalesBooking::find($id);
        if (!$sales_booking) {
          return response()->json(['message' => 'Sales Booking not found'], 404);
        }
        $sales_booking->delete();
        return response()->json([
          'message' => 'Sales Booking deleted successfully',
        ], 200);
    }

    // public function getBookList()
    // {
    //   $bookList = SalesBooking::where('status', 'approved')
    //     ->select('id', 'bookingId',)
    //     ->get();
    //   return response()->json([
    //     'data' => $bookList
    //   ], 200);
    // }


public function getBookList()
{

    $bookList = SalesBooking::with(['dealer','bookingPoint'])
        ->where('status', 'approved')
        ->select('id', 'bookingId', 'dealerId','bookingPointId')
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

        ];
    });

    return response()->json([
        'data' => $bookList
    ], 200);
}

}