<?php

namespace App\Models;
use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $table = 'department';
    protected $fillable =[
        'department_name',
    ];

    public function faculty()
    {
        return $this->hasMany(Faculty::class);
    }
 
}
