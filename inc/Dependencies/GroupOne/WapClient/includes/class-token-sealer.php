<?php
/**
 * Token Sealer — seals the WordPress app credential to WAP's wrap key.
 *
 * Uses libsodium's anonymous sealed box (X25519 + XSalsa20-Poly1305, bundled
 * with PHP >= 7.2): anyone holding the public key can seal, only WAP's private
 * half can open. The brand backend that relays the ciphertext into the GRND
 * can never read the credential.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Sealed-box wrapper for the app credential.
 */
class TokenSealer
{
    /**
     * Seal a credential to a base64-encoded X25519 public key.
     *
     * @param string $credential     Plaintext to seal (never logged, never cached).
     * @param string $public_key_b64 WAP wrap key, base64-encoded (32 bytes raw).
     *
     * @return string|\WP_Error Base64-encoded ciphertext, or WP_Error on failure.
     */
    public static function seal(string $credential, string $public_key_b64)
    {
        if (!function_exists('sodium_crypto_box_seal')) {
            return new \WP_Error(
                'wap_seal_failed',
                __('The sodium extension is required to seal the app credential (bundled with PHP 7.2+).', 'wap-client')
            );
        }

        $public_key = base64_decode($public_key_b64, true);
        if ($public_key === false || strlen($public_key) !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES) {
            return new \WP_Error(
                'wap_seal_failed',
                __('The wrap key is not a valid X25519 public key.', 'wap-client')
            );
        }

        try {
            $ciphertext = sodium_crypto_box_seal($credential, $public_key);
        } catch (\SodiumException $e) {
            return new \WP_Error(
                'wap_seal_failed',
                sprintf(
                    /* translators: 1: underlying sodium error message */
                    __('Sealing the app credential failed: %s', 'wap-client'),
                    $e->getMessage()
                )
            );
        }

        return base64_encode($ciphertext);
    }
}
