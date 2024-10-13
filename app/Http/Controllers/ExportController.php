<?php

namespace App\Http\Controllers;
use App\Exports\FacultyExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportFacultyData()
    {
        return Excel::download(new FacultyExport, 'faculty_data.xlsx');
    }
}
