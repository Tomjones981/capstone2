<?php
 namespace App\Imports;

 use App\Models\Faculty;
 use App\Models\User;
 use Illuminate\Support\Facades\Log;
 use Illuminate\Support\Facades\Hash;
 use Maatwebsite\Excel\Concerns\ToModel;
 use Maatwebsite\Excel\Concerns\WithHeadingRow;
 
 class UsersImport implements ToModel, WithHeadingRow
 {
     /**
      * @param array $row
      *
      * @return User|Faculty|null
      */
     public function model(array $row)
     {
         Log::info('Processing row: ' . json_encode($row));
 
         try {
           
             if (isset($row['id']) && isset($row['email']) && isset($row['password'])) {
              
                 $existingUser = User::find($row['id']);
                 if ($existingUser) {
                     Log::error('User ID already exists: ' . $row['id']);
                     return null;
                 }
 
          
                 $user = new User();
                 $user->id = $row['id'];
                 $user->email = $row['email'];
                 $user->password = Hash::make($row['password']);
                 $user->user_type = 1;
                 $user->save();
 
                 Log::info('User imported successfully: ' . $user->id);
 
                 
                 if ($user) {
                     $faculty = new Faculty();
                     $faculty->faculty_id = $user->id;
                     $faculty->first_name = $row['firstname'];
                     $faculty->middle_name = $row['middle_name'];
                     $faculty->last_name = $row['last_name'];
                     $faculty->designation = $row['designation'];
                     $faculty->status = isset($row['status']) ? $row['status'] : 'active';  
                     $faculty->save();
 
                     Log::info('Faculty imported successfully: ' . $faculty->faculty_id);
 
                     return $user;  
                 } else {
                     Log::error('Failed to create User: ' . json_encode($row));
                     return null;
                 }
             } else {
                 Log::error('Missing required User fields: ' . json_encode($row));
                 return null;
             }
         } catch (\Exception $e) {
             Log::error('Error during import: ' . $e->getMessage());
             return null;
         }
     }
 }
 
