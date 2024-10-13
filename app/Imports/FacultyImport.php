<?php

namespace App\Imports;

use App\Models\ContactDetails;
use App\Models\Department;
use App\Models\Employment;
use App\Models\Faculty_Rates; 
use App\Models\Faculty;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Exception;
class FacultyImport implements ToModel
{
    protected $isFirstRow = true;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
{ 
    if ($this->isFirstRow) {
        $this->isFirstRow = false;
        return null;
    }
 
    $departmentName = strtoupper(trim($row[1]));  
    $firstName = $row[2];  
    $middleName = isset($row[3]) ? trim($row[3]) : null;  
    $lastName = $row[4]; 
    $facultyType = $row[5];  
    $employmentType = $row[6];  

    
    $startDate = $this->convertDate($row[7]); 
    $endDate = $this->convertDate($row[8]);

    $status = $row[9];  
    $note = $row[10];  
    $phoneNumber = $row[11];  
    $email = $row[12];  
    $rateType = $row[13];  
    $rateValue = $row[14];  
    $teachingUnits = $row[15];  

     
    \Log::info('Looking up department: ' . $departmentName);

     
    $department = Department::whereRaw('UPPER(TRIM(department_name)) = ?', [$departmentName])->first();
    if (!$department) {
        \Log::error('Department not found: ' . $departmentName);
        throw new \Exception("Department not found: " . $departmentName);
    }

     
    $faculty = Faculty::updateOrCreate(
        ['id' => $row[0]],    
        [
            'department_id' => $department->id,
            'first_name' => $firstName,
            'middle_name' => $middleName,  
            'last_name' => $lastName,
            'faculty_type' => $facultyType,
        ]
    );

     
    Employment::updateOrCreate(
        ['faculty_id' => $faculty->id],
        [
            'employment_type' => $employmentType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'note' => $note,
        ]
    );

   
    ContactDetails::updateOrCreate(
        ['faculty_id' => $faculty->id],
        [
            'phone_number' => $phoneNumber,
            'email' => $email,
        ]
    );

    
     

     
    Faculty_Rates::updateOrCreate(
        ['faculty_id' => $faculty->id],
        [
            'rate_type' => $rateType,
            'rate_value' => $rateValue,
        ]
    );

    
    Unit::updateOrCreate(
        ['faculty_id' => $faculty->id],
        [
            'teaching_units' => $teachingUnits,
        ]
    );

    return $faculty;
}


    /**
     * Convert Excel date format to PHP DateTime object.
     *
     * @param mixed $value
     * @return \DateTime|null
     */
    private function convertDate($value)
    {
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value);
        }

        return null; 
    }
}
