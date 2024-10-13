<?php

namespace App\Models;
use App\Models\Faculty; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $table = 'unit';
    protected $fillable =[
        'faculty_id',
        'teaching_units', 
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
