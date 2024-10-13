<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DepartmentImport implements ToModel, WithHeadingRow
{
    private $skippedRowsCount = 0;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Check if faculty already exists
        $existingFaculty = Faculty::where([
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'designation' => $row['designation'],
            'status' => $row['status'],
        ])->first();

        if ($existingFaculty) {
            // Log a message
            Log::info('Data already exists for: ' . $row['first_name'] . ' ' . $row['last_name']);
            
             
            $this->skippedRowsCount++;

             
            return null;
        }

        
        $faculty = Faculty::create([
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'],
            'last_name' => $row['last_name'],
            'designation' => $row['designation'],
            'status' => $row['status'],
        ]);

         
        return new Department([
            'faculty_id' => $faculty->id,
            'department' => $row['department'],
            'department_head_name' => $row['department_head_name'],
            'head_contact_info' => $row['head_contact_info'],
        ]);
    }

    /**
     * Get the count of skipped rows.
     *
     * @return int
     */
    public function getSkippedRowsCount()
    {
        return $this->skippedRowsCount;
    }
}
