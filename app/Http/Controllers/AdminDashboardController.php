<?php

namespace App\Http\Controllers;
use App\Models\Faculty;
use App\Models\Employment;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function getTotalFaculties()
    { 
        $totalFaculties = Faculty::count(); 
        return response()->json([
            'total_faculties' => $totalFaculties
        ]);
    }

    public function getTotalFullTimeFaculties()
    { 
        $totalFullTimeFaculties = Employment::where('employment_type', 'full_time')->count();
 
        return response()->json([
            'total_full_time_faculties' => $totalFullTimeFaculties
        ]);
    }

    public function getTotalPartTimeFaculties()
    { 
        $totalPartTimeFaculties = Employment::where('employment_type', 'part_time')->count();
 
        return response()->json([
            'total_part_time_faculties' => $totalPartTimeFaculties
        ]);
    }
 
    public function getTotalActiveFaculties()
    { 
        $totalActiveFaculties = Employment::where('status', 'active')->count();
 
        return response()->json([
            'total_active_faculties' => $totalActiveFaculties
        ]);
    }

    public function getTotalInActiveFaculties()
    { 
        $totalInActiveFaculties = Employment::where('status', 'inactive')->count();
 
        return response()->json([
            'total_inactive_faculties' => $totalInActiveFaculties
        ]);
    }
}
