<?php

namespace App\Models;
use App\Models\Faculty; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    protected $table = 'schedule';
    protected $fillable =[
        'faculty_id',
        'date_from',
        'date_to', 
        'time_start', 
        'time_end', 
        'loading', 
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
