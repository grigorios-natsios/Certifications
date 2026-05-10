<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    public function __construct(private Google2FA $google2fa)
    {
        $this->google2fa->setWindow(1);
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if ($code === '' || ! ctype_digit($code) || strlen($code) !== 6) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $code);
    }

    public function otpAuthUrl(User $user, string $secret): string
    {
        $issuer  = config('app.name', 'Laravel');
        $account = $user->email;

        return $this->google2fa->getQRCodeUrl($issuer, $account, $secret);
    }
}
