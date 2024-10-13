<?php

namespace App\Models;
use App\Models\Faculty;  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty_Rates extends Model
{
    use HasFactory;
    protected $table = 'faculty_rates';
    protected $fillable =[
        'faculty_id',
        'rate_type',
        'rate_value', 
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
     
}
