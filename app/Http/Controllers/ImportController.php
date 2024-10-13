<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\FacultyImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Exceptions\InvalidFileException;
use Maatwebsite\Excel\Validators\ValidationException;
use Exception;

class ImportController extends Controller
{
    public function importFac(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new FacultyImport, $request->file('file'));
            return response()->json(['success' => true, 'message' => 'Faculty Imported Successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Faculty Import Failed: ' . $e->getMessage()], 500);
        }
    }

    public function importAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid file format. Please upload a valid Excel file.'
            ], 400);
        }

        $file = $request->file('file');

        try {
            Excel::import(new AttendanceImport, $file);
            return response()->json([
                'status' => 'success',
                'message' => 'Attendance uploaded and processed successfully.'
            ], 200);
        } catch (InvalidFileException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid file type or corrupted file: ' . $e->getMessage()
            ], 400);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'File validation error: ' . $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error during file import: ' . $e->getMessage()
            ], 500);
        }
    }
}
