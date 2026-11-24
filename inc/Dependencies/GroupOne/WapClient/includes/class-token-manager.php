<?php
/**
 * Token Manager — caching, sanity checks and refresh for GRND tokens.
 *
 * Owns everything generic about GRND handling so providers stay dumb fetchers:
 * structural sanity checks on every freshly fetched token, per-user/product
 * caching in a transient, expiry from the JWT `exp` claim (bounded by the
 * issuer-supplied expires_at), and refresh-before-expiry. The cached token
 * lives server-side only (WordPress options table) and is never sent to the
 * browser — the widget authenticates through admin-ajax.
 *
 * The sanity check is structural only (no signature verification — that is
 * WAP's job): it exists to catch misconfigured brand backends early, with an
 * error naming the exact rule a malformed token violated.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * GRND cache/refresh around a GrndProviderInterface.
 */
class TokenManager
{
    /**
     * Refresh a token this many seconds before it actually expires, so a GRND
     * never dies mid-request between here and WAP's verification.
     *
     * @var int
     */
    private const EXPIRY_SLACK = 60;

    /**
     * The only signature algorithm the GRND spec allows (Ed25519).
     *
     * @var string
     */
    private const REQUIRED_ALG = 'EdDSA';

    /**
     * The `jti` claim must be a GRND profile GRN carrying this tag —
     * grn:2@int:grnd:{system.environment}:wap/{nonce} — per the GRN spec.
     *
     * The nonce is NOT replay-checked: WAP validates the `jti`'s shape and tag
     * but keeps no store of spent nonces. Under per-call GRND transport the
     * same token is the Bearer credential for every request, so single-use
     * enforcement would reject every call after the first.
     *
     * @var string
     */
    private const JTI_PATTERN = '#^grn:2@int:grnd:[a-z0-9._-]*:wap/.+$#';

    /**
     * Maximum length of a GRN per spec.
     *
     * @var int
     */
    private const GRN_MAX_LENGTH = 256;

    /**
     * Return a valid GRND for this user/product, fetching via the provider
     * when the cache is empty, stale, or a refresh is forced. A malformed
     * token is never cached. A cached GRND also requires the backing App
     * Password to still be fresh (AppPasswordGuard::should_rotate()), independent of the GRND's own exp.
     *
     * @param int                   $wp_user_id Current WordPress user ID.
     * @param string                $product    Product slug.
     * @param GrndProviderInterface $provider   Token source.
     * @param bool                  $force      Drop the cache and re-fetch (e.g. after WAP rejected the token).
     *
     * @return string|\WP_Error The raw GRND, or a WP_Error (provider failure
     *                          or sanity-check violation).
     */
    public function get(int $wp_user_id, string $product, GrndProviderInterface $provider, bool $force = false)
    {
        $key = $this->cache_key($wp_user_id, $product);

        if (!$force) {
            $cached = $this->cached($wp_user_id, $product);
            if (null !== $cached) {
                return $cached;
            }
        }

        $result = $provider->fetch();
        if (is_wp_error($result)) {
            return $result;
        }

        $grnd  = (string) $result['grnd'];
        $check = self::sanity_check($grnd);
        if (is_wp_error($check)) {
            return $check;
        }

        // The sanity check guarantees a positive `exp`; the issuer-supplied
        // expires_at can only shorten the lifetime, never extend it past exp.
        $expires_at = self::jwt_exp($grnd);
        $issuer_exp = (int) $result['expires_at'];
        if ($issuer_exp > 0 && $issuer_exp < $expires_at) {
            $expires_at = $issuer_exp;
        }

        // Let the transient die at the refresh point (exp minus slack) so a
        // stale token can never be served even if the read-side check changes.
        $ttl = max(1, $expires_at - self::EXPIRY_SLACK - time());

        set_transient($key, ['grnd' => $grnd, 'expires_at' => $expires_at], $ttl);

        return $grnd;
    }

    /**
     * The cached GRND for this user/product when it can be served as is: not
     * about to expire, and its App Password not due for rotation
     * (AppPasswordGuard::should_rotate()). Null otherwise.
     *
     * @param int    $wp_user_id Current WordPress user ID.
     * @param string $product    Product slug.
     *
     * @return string|null
     */
    public function cached(int $wp_user_id, string $product): ?string
    {
        $cached = get_transient($this->cache_key($wp_user_id, $product));
        if (!is_array($cached) || empty($cached['grnd'])) {
            return null;
        }

        $expires_at = (int) ($cached['expires_at'] ?? 0);
        $grnd_fresh = $expires_at - self::EXPIRY_SLACK > time();
        if (!$grnd_fresh || AppPasswordGuard::should_rotate($wp_user_id, $product)) {
            return null;
        }

        return (string) $cached['grnd'];
    }

    /**
     * Drop the cached GRND for this user/product.
     *
     * Called on forced refresh, when WAP rejects the token, and — via
     * AppPasswordManager — whenever the App Password rotates: the app token
     * is baked into the GRND, so a rotation invalidates any cached GRND.
     *
     * @param int    $wp_user_id Current WordPress user ID.
     * @param string $product    Product slug.
     *
     * @return void
     */
    public function forget(int $wp_user_id, string $product): void
    {
        delete_transient($this->cache_key($wp_user_id, $product));
    }

    /**
     * Structural sanity check for a GRND (no signature verification).
     *
     * Verifies what a misconfigured brand backend most commonly gets wrong:
     *  - three-part JWT with base64url-decodable JSON header and payload
     *  - header `alg` is EdDSA (the only algorithm the GRND spec allows)
     *  - `jti` parses as the GRND profile GRN with tag `wap`
     *  - `exp` present and positive
     *
     * Each failure returns a WP_Error naming the violated rule. These messages
     * are for integrators (they reach debug.log via the caller); end users get
     * a generic message from the ajax layer.
     *
     * @param string $grnd Raw token as returned by the issuer.
     *
     * @return true|\WP_Error True when the token is structurally sound.
     */
    public static function sanity_check(string $grnd)
    {
        $parts = explode('.', $grnd);
        if (count($parts) !== 3) {
            return new \WP_Error(
                'wap_grnd_malformed',
                __('GRND sanity check failed: not a three-part JWT.', 'wap-client')
            );
        }

        $header = self::decode_segment($parts[0]);
        if (!is_array($header)) {
            return new \WP_Error(
                'wap_grnd_malformed',
                __('GRND sanity check failed: JWT header is not base64url-encoded JSON.', 'wap-client')
            );
        }

        $payload = self::decode_segment($parts[1]);
        if (!is_array($payload)) {
            return new \WP_Error(
                'wap_grnd_malformed',
                __('GRND sanity check failed: JWT payload is not base64url-encoded JSON.', 'wap-client')
            );
        }

        // `typ` is OPTIONAL per RFC 7519 §5.1, and the GRN spec
        // (designations.md §Validation) does not require it — only `alg`
        // is mandated. spec-conformant issuers (e.g. wpapi's GRND minter)
        // omit it. Accept its absence; if present, it must be "JWT" to
        // catch a brand sneaking a non-JWT 3-part token past this check.
        $typ = (string) ($header['typ'] ?? '');
        if ($typ !== '' && $typ !== 'JWT') {
            return new \WP_Error(
                'wap_grnd_malformed',
                sprintf(
                    /* translators: 1: typ found in the token header */
                    __('GRND sanity check failed: header typ, when present, must be JWT, got "%s".', 'wap-client'),
                    $typ
                )
            );
        }

        $alg = (string) ($header['alg'] ?? '');
        if ($alg !== self::REQUIRED_ALG) {
            return new \WP_Error(
                'wap_grnd_bad_alg',
                sprintf(
                    /* translators: 1: algorithm found in the token header */
                    __('GRND sanity check failed: header alg must be EdDSA, got "%s".', 'wap-client'),
                    $alg
                )
            );
        }

        $jti = $payload['jti'] ?? null;
        if (!is_string($jti)
            || strlen($jti) > self::GRN_MAX_LENGTH
            || !preg_match(self::JTI_PATTERN, $jti)
        ) {
            return new \WP_Error(
                'wap_grnd_bad_jti',
                __('GRND sanity check failed: jti must be a GRN of the form grn:2@int:grnd::wap/{nonce} (tag "wap").', 'wap-client')
            );
        }

        if (!isset($payload['exp']) || !is_numeric($payload['exp']) || (int) $payload['exp'] <= 0) {
            return new \WP_Error(
                'wap_grnd_missing_exp',
                __('GRND sanity check failed: exp claim is missing or not a positive timestamp.', 'wap-client')
            );
        }

        return true;
    }

    /**
     * Read the `exp` claim from a JWT without verifying it.
     *
     * No signature check happens client-side (that is WAP's job) — the claim
     * is only used to schedule refreshes.
     *
     * @param string $grnd Raw JWT.
     *
     * @return int Unix expiry, or 0 when unreadable.
     */
    public static function jwt_exp(string $grnd): int
    {
        $parts = explode('.', $grnd);
        if (count($parts) !== 3) {
            return 0;
        }
        $payload = self::decode_segment($parts[1]);
        return is_array($payload) ? (int) ($payload['exp'] ?? 0) : 0;
    }

    /**
     * Decode one base64url JWT segment into an associative array.
     *
     * @param string $segment Base64url-encoded JSON.
     *
     * @return array|null Decoded object, or null when not decodable.
     */
    private static function decode_segment(string $segment)
    {
        $binary = base64_decode(strtr($segment, '-_', '+/'), true);
        if ($binary === false) {
            return null;
        }
        $decoded = json_decode($binary, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Transient name for a user/product pair.
     *
     * @param int    $wp_user_id WordPress user ID.
     * @param string $product    Product slug.
     *
     * @return string
     */
    private function cache_key(int $wp_user_id, string $product): string
    {
        return 'wap_grnd_' . $wp_user_id . '_' . sanitize_key($product);
    }
}
