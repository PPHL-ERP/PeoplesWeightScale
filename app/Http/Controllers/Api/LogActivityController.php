<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LogActivityResource;
use App\Models\LogActivity;
use Illuminate\Http\Request;

class LogActivityController extends Controller
{


    public function index(Request $request)
    {

      $user_id = $request->user_id ?? null;
      $user_name = $request->user_name ?? null;

      $query = LogActivity::query();

      // Filter by user_id
      if ($user_id) {
        $query->where('user_id', $user_id);
      }

       // Filter by user_name
       if ($user_name) {
        // $query->orWhere('user_name', 'LIKE', '%' . $user_name . '%');
      }


    $data = $query->latest()->get();


      // Check if any log activities found
      if ($data->isEmpty()) {
          return response()->json(['message' => 'No LogActivity found', 'data' => [] ], 200);
      }

      // Use the LogActivityResource to transform the data
      $transformedLogActivities = LogActivityResource::collection($data);

      // Return paginated log activities transformed with the resource
      return response()->json([
        'message' => 'Success!',
        'data' => $transformedLogActivities
      ], 200);
    }


    public function destroy(){
        $activity = LogActivity::all();
        if($activity->isEmpty()){
            return response()->json([
                'message'=>'data not found!'
            ],200);
        }
        foreach ($activity as $data){
            $data->delete();
        }
        return response()->json([
            'message' => 'All Log Activity Deleted Successfully!'
        ],200);
    }
}
