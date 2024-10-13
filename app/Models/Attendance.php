<?php

namespace App\Models;
use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $table = 'attendance';
    protected $fillable = [
        'faculty_id',
        'date',
        'schedule',
        'remarks',
        'absent',
        'time_in',
        'breaks',
        'time_out',
        'hours_worked',
        'late',
        'overbreak',
        'ot',
        'undertime',
        'night_differential',
        'nd_ot',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
