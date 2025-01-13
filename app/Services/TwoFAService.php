<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TwoFAServiceException;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Recovery\Recovery;

class TwoFAService
{
    public function __construct(
        private Google2FA $google2FA,
        private Recovery $recovery
    ) {
    }

    /**
     * @throws TwoFAServiceException
     */
    public function enable2fa(string $email, int $userId): array
    {
        try {
            $secretKey = $this->google2FA->generateSecretKey(32);

            $qrCode = $this->generateQrCode($this->google2FA->getQRCodeUrl(
                config('app.name'),
                $email,
                $secretKey
            ));

            $filePath = '2fa/qr/'.$userId.'.svg';

            $this->uploadStringFile($qrCode, $filePath);

            [$codes, $codeFilePath] = $this->generateBackupCodes($userId);

            return [
                $filePath,
                $secretKey,
                $codes,
                $codeFilePath,
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'message' => 'Failed to enable 2FA',
                'userId' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);

            throw new TwoFAServiceException('Failed to enable 2FA');
        }
    }

    private function generateQrCode(string $qrCodeUrl): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    public function generateBackupCodes(int $userId): array
    {
        $recovery = $this->recovery->setCount(8)->toArray();

        $filePath = '2fa/recovery/'.$userId.'_recovery.json';
        $this->uploadStringFile(json_encode($recovery), $filePath);

        return [$recovery, $filePath];
    }

    private function uploadStringFile(string $file, string $filePath): void
    {
        Storage::disk('s3')->put($filePath, $file);
    }

    /**
     * @throws TwoFAServiceException
     */
    public function verify2fa(string $code, string $secret): void
    {
        try {
            $status = $this->google2FA->verifyKey($secret, $code);
            if (! $status) {
                throw new TwoFAServiceException('Invalid 2FA code.');
            }
        } catch (TwoFAServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'message' => 'Failed to verify 2FA',
                'trace' => $e->getTraceAsString(),
            ]);

            throw new TwoFAServiceException('Failed to verify 2FA');
        }
    }

    /**
     * @throws TwoFAServiceException
     */
    public function getBackupCodes(string $filePath): string
    {
        if (Storage::disk('s3')->missing($filePath)) {
            throw new TwoFAServiceException('Backup codes not found.');
        }

        return Storage::disk('s3')->get($filePath);
    }
}
