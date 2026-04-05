<?php

namespace App\Services;

class TwoFactorAuthenticator
{
    public function generateSecret(): string
    {
        return strtoupper(bin2hex(random_bytes(20)));
    }

    public function currentCode(string $secret, ?int $timeSlice = null): string
    {
        $timeSlice ??= (int) floor(time() / 30);

        $binaryTime = pack('N*', 0).pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $binaryTime, $secret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $segment = substr($hash, $offset, 4);
        $value = unpack('N', $segment)[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $timeSlice = (int) floor(time() / 30);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->currentCode($secret, $timeSlice + $offset), $code)) {
                return true;
            }
        }

        return false;
    }
}