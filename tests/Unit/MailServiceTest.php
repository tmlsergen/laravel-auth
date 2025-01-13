<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\PasswordReset;
use App\Services\MailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class MailServiceTest extends TestCase
{
    public function test_send_password_reset_email_successful(): void
    {
        // Arrange
        $email = 'test@example.com';
        $link = 'https://example.com/reset-password?token=123';

        Mail::fake();

        // Act
        $result = MailService::sendPasswordReset($email, $link);

        // Assert
        $this->assertTrue($result);
        Mail::assertQueued(PasswordReset::class, function (PasswordReset $mail) use ($email, $link) {
            return $mail->email === $email &&
                   $mail->link === $link &&
                   $mail->hasTo($email);
        });
    }

    public function test_send_password_reset_email_failure(): void
    {
        // Arrange
        $email = 'test@example.com';
        $link = 'https://example.com/reset-password?token=123';

        Mail::shouldReceive('to')
            ->once()
            ->with($email)
            ->andThrow(new \Exception('Failed to send email'));

        Log::shouldReceive('error')
            ->once()
            ->with('Error sending password reset email: Failed to send email');

        // Act
        $result = MailService::sendPasswordReset($email, $link);

        // Assert
        $this->assertFalse($result);
    }
}
