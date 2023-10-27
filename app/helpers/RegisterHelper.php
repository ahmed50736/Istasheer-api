<?php

namespace App\helpers;

use App\helpers\FutureSmsIntegration;
use App\Mail\RegistrationCredentailsMailer;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class RegisterHelper
{

    /**
     * User name generator
     * @param string $name
     * @return object $suggestions
     */
    public static function userNamesuggestions(string $name)
    {
        // Assuming the name is in a variable called $name
        $suggestions = [];

        // Remove spaces and convert to lowercase
        $username = strtolower(str_replace(' ', '', $name));

        // Check if the username already exists in the database
        if (!User::where('username', $username)->exists()) {
            $suggestions[] = $username;
        }

        // If the username already exists, add numbers to the end until a unique username is found
        for ($i = 1; User::where('username', $username)->exists(); $i++) {
            $suggestedUsername = $username . $i;
            if (!User::where('username', $suggestedUsername)->exists()) {
                $suggestions[] = $suggestedUsername;
            }
        }

        // Return the suggestions
        return $suggestions;
    }

    /**
     * username & password sender for admin & attorney On Registration
     * @param object $user
     * @param string $userName
     * @param string $password
     * @return void
     */
    public static function sendingUserNameAndPasswordToUser(object $user, string $userName, string $password): void
    {
        switch ($user->user_type) {
            case '1':
                $type = 'super Admin';
                break;
            case 2:
                $type = 'Attorney';
                break;
            case 3:
                $type = 'User';
                break;
            case 4:
                $type = 'Admin';
                break;
        }
        if (isset($user->email) && $user->email != null) {
            Mail::to($user->email)->send(new RegistrationCredentailsMailer($userName, $password, $type));
        } else {
            FutureSmsIntegration::sendingRegistrationCredentials($user->phone_no, $userName, $password, $type);
        }
    }
}
