<?php

namespace App\Http\Controllers;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
class DepartmentController extends Controller
{
    public function createDepartment(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
        ]);

        $existingDepartment = Department::where('department_name', $request->input('department_name'))->first();
        if ($existingDepartment) {
            return response()->json([
                'message' => 'This Department has already been created',
            ], 409);
        }

        $department = new Department();
        $department->department_name = $request->input('department_name');
        $department->save();

        return response()->json([
            'message' => 'Department Created Successfully!',
            'department' => $department,
        ], 201);

    }
    public function fetchDepartment()
    {
        $departments = Department::all();
        return response()->json($departments, 200);
    }
    public function getDepartments()
    {
        $departments = Department::all();
        return response()->json($departments);
    }
    public function update(Request $request, $id)
    {

        $request->validate([
            'department_name' => 'required|string|max:255',
        ]);

        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'message' => 'Department not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $existingDepartment = Department::where('department_name', $request->input('department_name'))
            ->where('id', '!=', $id)
            ->first();

        if ($existingDepartment) {
            return response()->json([
                'message' => 'This Department has already been created',
            ], 409);
        }

        $department->update([
            'department_name' => $request->input('department_name'),
        ]);

        return response()->json([
            'message' => 'Department updated successfully',
            'data' => $department
        ], Response::HTTP_OK);
    }
    public function destroy($id)
    {

        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'message' => 'Department not found',
            ], 404);
        }


        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully',
        ], 200);
    }
    public function fetchFacultyByDepartment()
    {
        $departments = DB::table('department')
            ->leftJoin('faculty as department_head', function ($join) {
                $join->on('department.id', '=', 'department_head.department_id')
                    ->where('department_head.faculty_type', '=', 'department_head');
            })
            ->leftJoin('faculty', 'department.id', '=', 'faculty.department_id')
            ->select(
                'department.id',
                'department.department_name',
                DB::raw("CONCAT_WS(' ', department_head.first_name, department_head.middle_name, department_head.last_name) as department_head"),
                DB::raw("COUNT(faculty.id) as total_faculty")
            )
            ->groupBy(
                'department.id',
                'department.department_name',
                'department_head.first_name',
                'department_head.middle_name',
                'department_head.last_name'
            )
            ->get();

        return response()->json($departments);
    }



    public function ViewFacultyByDepartment($departmentId)
    {

        $department = Department::with('faculty')
            ->where('id', $departmentId)
            ->first();

        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        return response()->json([
            'department_name' => $department->department_name,
            'faculty' => $department->faculty
        ]);
    }


}
