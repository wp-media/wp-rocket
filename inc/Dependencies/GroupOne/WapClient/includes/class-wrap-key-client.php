<?php
/**
 * Wrap Key Client — fetches and caches WAP's public wrap key.
 *
 * WAP publishes a rotating X25519 public key. The library seals the WordPress
 * Application Password to it (sealed box) before handing the ciphertext to the
 * brand backend for embedding into the GRND — the brand only ever handles
 * ciphertext; only WAP (holder of the ephemeral private half) can unwrap.
 *
 * Draft endpoint contract (see docs/wap-backend-grnd-requirements.md):
 *
 *     GET {server_url}/api/v1/auth/wrap-key
 *     -> 200 {
 *          "key_id":     "2026-07-a",
 *          "public_key": "<base64, 32 bytes>",
 *          "algorithm":  "x25519-sealed-box",
 *          "expires_at": 1751900000
 *        }
 *
 * Unknown extra fields are ignored (draft-schema tolerance).
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Fetch + cache client for the WAP wrap key, with key-id awareness.
 */
class WrapKeyClient
{
    /**
     * HTTP timeout for the wrap-key fetch, in seconds.
     *
     * @var int
     */
    private const TIMEOUT = 15;

    /**
     * Stop serving a cached key this many seconds before it expires, so a
     * seal never happens against a key WAP has already discarded.
     *
     * @var int
     */
    private const EXPIRY_SLACK = 300;

    /**
     * Cache lifetime when the endpoint supplies no expires_at. Short by
     * design: rotation is picked up on the next fetch cycle.
     *
     * @var int
     */
    private const DEFAULT_TTL = 900;

    /**
     * X25519 public key length in bytes.
     *
     * @var int
     */
    private const KEY_BYTES = 32;

    /**
     * WAP backend base URL (without trailing slash).
     *
     * @var string
     */
    private string $server_url;

    /**
     * Constructor.
     *
     * @param string $server_url WAP backend URL.
     */
    public function __construct(string $server_url)
    {
        $this->server_url = rtrim($server_url, '/');
    }

    /**
     * Return the current wrap key, from cache or freshly fetched.
     *
     * @return array{key_id: string, public_key: string}|\WP_Error Key id and
     *         base64-encoded public key, or WP_Error when unavailable.
     */
    public function get_key()
    {
        $cache_key = $this->cache_key();
        $cached    = get_transient($cache_key);
        if (is_array($cached) && !empty($cached['key_id']) && !empty($cached['public_key'])) {
            return ['key_id' => (string) $cached['key_id'], 'public_key' => (string) $cached['public_key']];
        }

        $response = wp_remote_get($this->server_url . '/api/v1/auth/wrap-key', [
            'headers' => ['Accept' => 'application/json'],
            'timeout' => self::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            return new \WP_Error(
                'wap_wrap_key_unavailable',
                sprintf(
                    /* translators: 1: underlying error message */
                    __('WAP wrap key could not be fetched: %s', 'wap-client'),
                    $response->get_error_message()
                )
            );
        }

        $status = wp_remote_retrieve_response_code($response);
        $body   = json_decode(wp_remote_retrieve_body($response), true);

        if ($status < 200 || $status >= 300 || !is_array($body)
            || empty($body['key_id']) || !is_string($body['key_id'])
            || empty($body['public_key']) || !is_string($body['public_key'])
        ) {
            return new \WP_Error(
                'wap_wrap_key_unavailable',
                sprintf(
                    /* translators: 1: HTTP status code */
                    __('WAP wrap key endpoint returned an unusable response (HTTP %d).', 'wap-client'),
                    $status
                )
            );
        }

        // The key must be a valid base64 X25519 public key — reject anything
        // else before it can reach the sealing step.
        $raw = base64_decode($body['public_key'], true);
        if ($raw === false || strlen($raw) !== self::KEY_BYTES) {
            return new \WP_Error(
                'wap_wrap_key_unavailable',
                __('WAP wrap key is not a valid base64-encoded X25519 public key.', 'wap-client')
            );
        }

        $key = ['key_id' => $body['key_id'], 'public_key' => $body['public_key']];

        $expires_at = (int) ($body['expires_at'] ?? 0);
        $ttl        = $expires_at > 0
            ? max(60, $expires_at - self::EXPIRY_SLACK - time())
            : self::DEFAULT_TTL;

        set_transient($cache_key, $key, $ttl);

        return $key;
    }

    /**
     * Drop the cached wrap key so the next seal fetches a fresh one.
     *
     * Called on forced re-auth: if the exchange failed because WAP rotated
     * the key, the retry must not re-seal against the stale one.
     *
     * @return void
     */
    public function forget(): void
    {
        delete_transient($this->cache_key());
    }

    /**
     * Transient name, scoped to the backend URL (multi-backend safe).
     *
     * @return string
     */
    private function cache_key(): string
    {
        return 'wap_wrap_key_' . md5($this->server_url);
    }
}
