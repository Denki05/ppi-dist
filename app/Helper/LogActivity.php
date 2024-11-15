<?php

namespace App\Helper;

use Request;
use App\Entities\Account\LogActivitys;

class LogActivity
{
    public static function addToLog($subject)
    {
        $log = [
            'subject' => $subject,
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'ip' => Request::ip(),
            'agent' => Request::header('user-agent'),
            'user_id' => auth()->check() ? auth()->user()->id : 1,
        ];

        // Handle any potential exceptions
        try {
            LogActivitys::create($log); // Assuming LogActivitys is a typo and should be LogActivity
        } catch (\Exception $e) {
            // Log or handle the exception appropriately
            \Log::error('Error saving log: ' . $e->getMessage());
        }
    }

    public static function logActivityLists()
    {
        return LogActivitys::latest()->get(); // Assuming LogActivitys is a typo and should be LogActivity
    }
}