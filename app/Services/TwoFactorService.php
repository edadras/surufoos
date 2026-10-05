<?php

namespace App\Services;

use App\Support\TextNormalizer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

/** RFC 6238 TOTP for admin two-factor authentication (Google Authenticator compatible). */
class TwoFactorService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $bytes = 20): string
    {
        $random = random_bytes($bytes);
        $bits = '';
        foreach (str_split($random) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $secret = '';
        foreach (str_split($bits, 5) as $chunk) {
            $secret .= self::ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $secret;
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D/', '', TextNormalizer::normalize($code));
        if (strlen($code) !== 6) {
            return false;
        }
        $slice = (int) floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->codeAt($secret, $slice + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    public function codeAt(string $secret, int $slice): string
    {
        $key = $this->base32Decode($secret);
        $hash = hash_hmac('sha1', pack('N*', 0, $slice), $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $value = ((ord($hash[$offset]) & 0x7F) << 24) | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8) | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public function otpauthUrl(string $secret, string $account): string
    {
        $issuer = rawurlencode(settings()->siteName('en'));

        return "otpauth://totp/{$issuer}:".rawurlencode($account)."?secret={$secret}&issuer={$issuer}&digits=6&period=30";
    }

    public function qrSvg(string $url): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(220, 1), new SvgImageBackEnd));

        return $writer->writeString($url);
    }

    /** @return array<int, string> */
    public function recoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))->all();
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $secret));
        $bits = '';
        foreach (str_split($secret) as $char) {
            $bits .= str_pad(decbin(strpos(self::ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $out .= chr(bindec($byte));
            }
        }

        return $out;
    }
}
