<?php

namespace App\Models;
use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactDetails extends Model
{
    use HasFactory;
    protected $table = 'contact_details';
    protected $fillable =[
        'faculty_id',
        'phone_number', 
        'email',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
