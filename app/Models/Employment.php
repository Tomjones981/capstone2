<?php

namespace App\Models;
use App\Models\Faculty; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employment extends Model
{
    use HasFactory;
    protected $table = 'employment';
    protected $fillable =[
        'faculty_id',
        'employment_type',
        'start_date',
        'end_date',
        'status', 
        'note', 
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
