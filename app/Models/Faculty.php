<?php

namespace App\Models;
use App\Models\Schedule; 
use App\Models\Department; 
use App\Models\Employment; 
use App\Models\Faculty_Rates; 
use App\Models\Unit; 
use App\Models\ContactDetails;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;
    protected $table = 'faculty';
    protected $fillable =[
        'department_id',
        'first_name',
        'middle_name',
        'last_name',
        'faculty_type', 
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function employment()
    {
        return $this->hasOne(Employment::class);    
    }
    public function faculty_rates()
    {
        return $this->hasMany(Faculty_Rates::class);
    }
    public function unit()
    {
        return $this->hasMany(Unit::class);
    }
    public function contact_details()
    {
        return $this->hasMany(ContactDetails::class);
    }
    public function schedule()
    {
        return $this->hasMany(Schedule::class);
    }
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
 
}
