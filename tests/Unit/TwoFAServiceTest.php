<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\TwoFAServiceException;
use App\Services\TwoFAService;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Recovery\Recovery;
use Tests\TestCase;

final class TwoFAServiceTest extends TestCase
{
    private TwoFAService $twoFAService;
    private Google2FA $google2FA;
    private Recovery $recovery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->google2FA = Mockery::mock(Google2FA::class);
        $this->recovery = Mockery::mock(Recovery::class);
        $this->twoFAService = new TwoFAService($this->google2FA, $this->recovery);

        Storage::fake('s3');
    }

    public function test_enable_2fa_successful(): void
    {
        // Arrange
        $email = 'test@example.com';
        $userId = 1;
        $secretKey = 'test-secret-key';
        $qrCodeUrl = 'https://chart.googleapis.com/chart?test';
        $backupCodes = ['code1', 'code2'];

        $this->google2FA
            ->shouldReceive('generateSecretKey')
            ->with(32)
            ->once()
            ->andReturn($secretKey);

        $this->google2FA
            ->shouldReceive('getQRCodeUrl')
            ->with(config('app.name'), $email, $secretKey)
            ->once()
            ->andReturn($qrCodeUrl);

        $this->recovery
            ->shouldReceive('setCount')
            ->with(8)
            ->once()
            ->andReturnSelf();

        $this->recovery
            ->shouldReceive('toArray')
            ->once()
            ->andReturn($backupCodes);

        // Act
        [$qrFilePath, $returnedSecretKey, $returnedCodes, $codeFilePath] = $this->twoFAService->enable2fa($email, $userId);

        // Assert
        $this->assertEquals('2fa/qr/'.$userId.'.svg', $qrFilePath);
        $this->assertEquals($secretKey, $returnedSecretKey);
        $this->assertEquals($backupCodes, $returnedCodes);
        $this->assertEquals('2fa/recovery/'.$userId.'_recovery.json', $codeFilePath);

        Storage::disk('s3')->assertExists($qrFilePath);
        Storage::disk('s3')->assertExists($codeFilePath);
    }

    public function test_verify_2fa_successful(): void
    {
        // Arrange
        $code = '123456';
        $secret = 'secret-key';

        $this->google2FA
            ->shouldReceive('verifyKey')
            ->with($secret, $code)
            ->once()
            ->andReturn(true);

        // Act
        $this->twoFAService->verify2fa($code, $secret);

        // Assert
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_verify_2fa_with_invalid_code(): void
    {
        // Arrange
        $code = '123456';
        $secret = 'secret-key';

        $this->google2FA
            ->shouldReceive('verifyKey')
            ->with($secret, $code)
            ->once()
            ->andReturn(false);

        // Assert
        $this->expectException(TwoFAServiceException::class);
        $this->expectExceptionMessage('Invalid 2FA code.');

        // Act
        $this->twoFAService->verify2fa($code, $secret);
    }

    public function test_get_backup_codes_successful(): void
    {
        // Arrange
        $filePath = '2fa/recovery/1_recovery.json';
        $codes = json_encode(['code1', 'code2']);

        Storage::disk('s3')->put($filePath, $codes);

        // Act
        $result = $this->twoFAService->getBackupCodes($filePath);

        // Assert
        $this->assertEquals($codes, $result);
    }

    public function test_get_backup_codes_file_not_found(): void
    {
        // Arrange
        $filePath = '2fa/recovery/1_recovery.json';

        // Assert
        $this->expectException(TwoFAServiceException::class);
        $this->expectExceptionMessage('Backup codes not found.');

        // Act
        $this->twoFAService->getBackupCodes($filePath);
    }

    public function test_generate_backup_codes(): void
    {
        // Arrange
        $userId = 1;
        $backupCodes = ['code1', 'code2'];
        $expectedFilePath = '2fa/recovery/'.$userId.'_recovery.json';

        $this->recovery
            ->shouldReceive('setCount')
            ->with(8)
            ->once()
            ->andReturnSelf();

        $this->recovery
            ->shouldReceive('toArray')
            ->once()
            ->andReturn($backupCodes);

        // Act
        [$codes, $filePath] = $this->twoFAService->generateBackupCodes($userId);

        // Assert
        $this->assertEquals($backupCodes, $codes);
        $this->assertEquals($expectedFilePath, $filePath);
        Storage::disk('s3')->assertExists($filePath);
        $this->assertEquals(json_encode($backupCodes), Storage::disk('s3')->get($filePath));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
