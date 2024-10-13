<?php

namespace App\Http\Controllers;

use App\Mail\SendWelcomeMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
class MailController extends Controller
{
    public function sendEmail(){
        try{
            $toEmailAddress = "mlbb8794@gmail.com";
            $welcomeMessage = "Hey Welcome to Programming Fields. this is email configuration";
            $response = Mail::to($toEmailAddress)->send(new SendWelcomeMail($welcomeMessage));
            dd($response);
        }
        catch (Exception $e) {
            \Log::error("Unable to send email" . $e->getMessage());
        }
    }
}
