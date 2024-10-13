<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        

        User::create([
            'id' => "1",
            'email' => 'payroll@gmail.com',
            'user_type' => 'payroll',
            'password' => 'password123',
        ]);
        User::create([
            'id' => "2",
            'email' => 'admin@gmail.com',
            'user_type' => 'admin',
            'password' => bcrypt('password123'),

        ]);

        Department::create([ 
            'id' => "1",
            'department_name' => 'test', 
        ]);
        Faculty::create([ 
            'department_id' => '1', 
            'first_name' => 'test', 
            'middle_name' => 'test', 
            'last_name' => 'test', 
            'faculty_type' => 'faculty', 
        ]); 
        Faculty::create([ 
            'department_id' => '1', 
            'first_name' => 'test', 
            'middle_name' => 'test', 
            'last_name' => 'test', 
            'faculty_type' => 'faculty', 
        ]); 
        Faculty::create([ 
            'department_id' => '1', 
            'first_name' => 'test', 
            'middle_name' => 'test', 
            'last_name' => 'test', 
            'faculty_type' => 'faculty', 
        ]);  

        
    }
}
