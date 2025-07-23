<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserActivityResource;
use App\Models\UserActivity;
// use function PHPUnit\Framework\isEmpty;
// use App\Exports\UserActivityExport;
// use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{

    public function index(Request $request)
    {

      $user_id = $request->user_id ?? null;
      $user_name = $request->user_name ?? null;
      $module_name = $request->module_name ?? null;

      $query = UserActivity::query();

      // Filter by user_id
      if ($user_id) {
        $query->where('user_id', $user_id);
      }

       // Filter by user_name
       if ($user_name) {
        // $query->orWhere('user_name', 'LIKE', '%' . $user_name . '%');
      }

       // Filter by module_name
       if ($module_name) {
        $query->orWhere('module_name', 'LIKE', '%' . $module_name . '%');
      }

      // Fetch user activities with eager loading of related data
    //   if($query){
    //       $data = $query->latest()->paginate(10);
    //   }else{
    //       $data =  UserActivity::latest()->paginate(10);
    //   }

    $data = $query->latest()->get();

      // Check if any user activities found
      if ($data->isEmpty()) {
          return response()->json(['message' => 'No UserActivity found', 'data' => [] ], 200);
      }

      // Use the UserActivityResource to transform the data
      $transformedUserActivities = UserActivityResource::collection($data);

      // Return paginated user activities transformed with the resource
      return response()->json([
        'message' => 'Success!',
        'data' => $transformedUserActivities
      ], 200);
    }

    public function useracti_list()
    {
        // $shifts = UserActivity::latest()->get();
        $shifts = UserActivity::latest()->take(12)->get();

        if($shifts->isEmpty()){
            return response()->json(['message' => 'No UserActivity found'], 200);
        }
        return UserActivityResource::collection($shifts);
    }

//     public function exportExcel()
//   {
//     return Excel::download(new UserActivityExport, 'user_activity.csv');
//   }

  public function destroy(){
    $user_activity = UserActivity::all();
    if($user_activity->isEmpty()){
        return response()->json([
            'message'=>'data not found!'
        ],200);
    }
    foreach ($user_activity as $data){
        $data->delete();
    }
    return response()->json([
        'message' => 'All User Activity Deleted Successfully!'
    ],200);
 }

}