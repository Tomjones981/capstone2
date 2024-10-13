<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rate;
use App\Models\Faculty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class RateController extends Controller
{
    // public function getFacultyWithRateType()
    // {
    //     // Subquery to aggregate rate types and values
    //     $subQuery = DB::table('faculty_rates')
    //         ->select('faculty_id', 'rate_type_id', DB::raw('SUM(rate_value) as total_rate_value'))
    //         ->join('faculty_types', 'faculty_rates.rate_type_id', '=', 'faculty_types.id')
    //         ->groupBy('faculty_id', 'rate_type_id');

    //     $faculties = Faculty::select('id', 'first_name', 'middle_name', 'last_name')
    //         ->with([
    //             'faculty_rates' => function ($query) use ($subQuery) {
    //                 $query->joinSub($subQuery, 'rate_summary', function ($join) {
    //                     $join->on('faculty_rates.faculty_id', '=', 'rate_summary.faculty_id')
    //                         ->on('faculty_rates.rate_type_id', '=', 'rate_summary.rate_type_id');
    //                 })
    //                     ->join('faculty_types', 'faculty_rates.rate_type_id', '=', 'faculty_types.id')
    //                     ->select('faculty_rates.faculty_id', 'faculty_types.rate_type', 'rate_summary.total_rate_value')
    //                     ->groupBy('faculty_rates.faculty_id', 'faculty_types.rate_type', 'rate_summary.total_rate_value');
    //             }
    //         ])
    //         ->get()
    //         ->map(function ($faculty) {
    //             $rateTypes = $faculty->faculty_rates->groupBy('rate_type');
    //             return [
    //                 'id' => $faculty->id,
    //                 'full_name' => $faculty->first_name . ' ' . $faculty->middle_name . ' ' . $faculty->last_name,
    //                 'rate_types' => $rateTypes->map(function ($rates, $rateType) {
    //                     return [
    //                         'rate_type' => $rateType,
    //                         'rate_value' => $rates->first()->total_rate_value,
    //                         'total_faculty' => $rates->count()
    //                     ];
    //                 })
    //             ];
    //         });

    //     return response()->json($faculties);
    // }

    // public function getFacultyWithRateType()
    // { 
    //     $subquery = DB::table('faculty_rates as fr')
    //         ->join('faculty_types as ft', 'fr.rate_type_id', '=', 'ft.id')
    //         ->select('ft.rate_type as rate_type_value', 'fr.rate_value')
    //         ->distinct()
    //         ->get();
     
    //     $faculties = DB::table('faculty as f')
    //         ->join('faculty_rates as fr', 'f.id', '=', 'fr.faculty_id')
    //         ->join('faculty_types as ft', 'fr.rate_type_id', '=', 'ft.id')
    //         ->select(
    //             'ft.id as faculty_id',
    //             DB::raw("CONCAT(f.first_name, ' ', f.middle_name, ' ', f.last_name) as faculty_full_name"),
    //             'ft.rate_type as rate_type_value',
    //             'fr.rate_value',
    //             DB::raw('COUNT(*) OVER (PARTITION BY ft.rate_type) as total_faculty_in_rate_type')
    //         )
    //         ->distinct()
    //         ->get();
    
    //     return response()->json($faculties);
    // }
    public function getFacultyWithRateType()
{
    // Get the faculties with their rate types and values
    $faculties = DB::table('faculty as f')
        ->join('faculty_rates as fr', 'f.id', '=', 'fr.faculty_id')
        ->join('faculty_types as ft', 'fr.rate_type_id', '=', 'ft.id')
        ->select(
            'f.id as faculty_id',
            DB::raw("CONCAT(f.first_name, ' ', f.middle_name, ' ', f.last_name) as faculty_full_name"),
            'ft.rate_type as rate_type_value',
            'fr.rate_value',
            DB::raw('COUNT(*) OVER (PARTITION BY ft.rate_type) as total_faculty_in_rate_type')
        )
        ->get();

    return response()->json($faculties);
}

    


}
