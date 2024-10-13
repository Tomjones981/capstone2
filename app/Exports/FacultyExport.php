<?php

namespace App\Exports;

use App\Models\Faculty;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FacultyExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Faculty::with(['department', 'employment', 'contact_details', 'faculty_rates', 'unit'])->get();
    }

    /**
     * @param mixed $faculty
     * @return array
     */
    public function map($faculty): array
    {
        return [
            $faculty->id,
            $faculty->department->department_name,
            $faculty->first_name,
            $faculty->middle_name,
            $faculty->last_name,
            $faculty->faculty_type,
            $faculty->employment->employment_type ?? '',
            $faculty->employment->start_date ?? '',
            $faculty->employment->end_date ?? '',
            $faculty->employment->status ?? '',
            $faculty->employment->note ?? '',
            $faculty->contact_details->first()->phone_number ?? '',
            $faculty->contact_details->first()->email ?? '', 
            $faculty->faculty_rates->first()->rate_type ?? '',  
            $faculty->faculty_rates->first()->rate_value ?? '',
            $faculty->unit->first()->teaching_units ?? '', 
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Faculty ID',
            'Department',
            'First Name',
            'Middle Name',
            'Last Name',
            'Faculty Type',
            'Employment Type',
            'Start Date',
            'End Date',
            'Status',
            'Note',
            'Phone Number',
            'Email',
            'Rate Type',
            'Rate Value',
            'Teaching Units',
        ];
    }
}
