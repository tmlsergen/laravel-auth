<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

readonly class MailService
{
    public static function sendPasswordReset(string $email, string $link): bool
    {
        try {
            Mail::to($email)->queue(new \App\Mail\PasswordReset($email, $link));

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending password reset email: ' . $e->getMessage());

            return false;
        }
    }
}
