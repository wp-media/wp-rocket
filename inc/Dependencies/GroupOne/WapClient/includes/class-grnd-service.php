<?php
/**
 * GRND Service — the one-call GRND acquisition facade for WordPress hosts.
 *
 * Owns the full choreography that every consumer would otherwise reimplement:
 * App Password provisioning, wrap-key fetch, credential sealing, the brand
 * issuer exchange (or a custom provider), caching, and force-new invalidation.
 * Integrators call WP_Rocket_WapClient::get_grnd_token() and hand the result to the
 * browser widget as its Bearer credential — nothing else.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * One-call GRND acquisition for WordPress consumers.
 */
class GrndService
{
    /**
     * Rolling window for the forced re-auth brake, in seconds.
     *
     * @var int
     */
    private const REAUTH_WINDOW = 300;

    /**
     * Forced re-auths allowed per user/product within REAUTH_WINDOW.
     *
     * @var int
     */
    private const REAUTH_MAX_TRIES = 5;

    /**
     * Return a validated, cached GRND for a WordPress user.
     *
     * The WordPress credential is handled internally: on a GRND cache miss a
     * fresh Application Password is minted and sealed to WAP's wrap key
     * (X25519 sealed box); the plaintext never leaves this function's closure.
     * The returned GRND is structurally validated (EdDSA, wap designation
     * jti, positive exp) before it is cached or returned.
     *
     * @param array{
     *     product:         string,
     *     server_url:      string,
     *     issuer_url?:     string,
     *     license_key?:    string,
     *     grnd_provider?:  callable,
     *     user_id?:        int,
     *     user_login?:     string,
     *     force_new?:      bool,
     *     password_label?: string,
     *     extra_headers?:  array<string, string>,
     * } $args Arguments:
     *   - product         (required) Product slug registered with WAP.
     *   - server_url      (required) WAP backend base URL (wrap-key endpoint).
     *   - issuer_url      Brand GRND issuer endpoint (standardized contract).
     *                     Required unless grnd_provider is given.
     *   - license_key     License key sent to the issuer. Default ''.
     *   - grnd_provider   Custom callable returning the raw GRND (escape
     *                     hatch; the caller then owns credential sealing).
     *   - user_id / user_login  Defaults to the current WordPress user.
     *   - force_new       Revoke the stored App Password and drop the cached
     *                     GRND + wrap key before acquiring (the widget's
     *                     retry-after-401 flag). Default false.
     *   - password_label  Label for the minted App Password. Default product.
     *   - extra_headers   Optional associative array of HTTP headers to
     *                     forward verbatim to the brand issuer on every
     *                     exchange (e.g. ['X-TOTP' => '123456',
     *                     'X-Onecom-Client-Domain' => 'wpin10.1prod.one']).
     *                     Content-Type is always set to application/json.
     *
     * @return string|\WP_Error The GRND on success.
     */
    public static function get_grnd(array $args)
    {
        $product    = (string) ($args['product'] ?? '');
        $server_url = rtrim((string) ($args['server_url'] ?? ''), '/');
        $force_new  = ! empty($args['force_new']);

        // Default to the current WordPress user — the normal case for
        // admin-ajax consumers.
        $user_id    = (int) ($args['user_id'] ?? 0);
        $user_login = (string) ($args['user_login'] ?? '');
        if ($user_id <= 0 || '' === $user_login) {
            $current = wp_get_current_user();
            if (!$current || !$current->exists()) {
                return new \WP_Error('wap_grnd_config', 'No WordPress user: pass user_id + user_login or call as a logged-in user.');
            }
            $user_id    = $current->ID;
            $user_login = $current->user_login;
        }

        if ('' === $product || '' === $server_url) {
            return new \WP_Error('wap_grnd_config', 'product and server_url are required.');
        }

        $provider_callable = is_callable($args['grnd_provider'] ?? null) ? $args['grnd_provider'] : null;
        $issuer_url        = (string) ($args['issuer_url'] ?? '');
        if (null === $provider_callable && '' === $issuer_url) {
            return new \WP_Error('wap_grnd_config', 'Provide either issuer_url or grnd_provider.');
        }

        $token_manager   = new TokenManager();
        $wrap_key_client = new WrapKeyClient($server_url);

        // Credential factory for the standardized exchange: mint a fresh App
        // Password and seal "username:password" to WAP's wrap key. It runs
        // inside the provider — i.e. only on a GRND cache miss — so a valid
        // cached GRND never triggers a credential rotation. The plaintext
        // password lives only in this closure and never leaves the site.
        $label       = (string) ($args['password_label'] ?? $product);
        $credentials = static function () use ($user_id, $user_login, $product, $label, $wrap_key_client) {
            $app_password = (new AppPasswordManager())->provision($user_id, $product, $label);
            if (is_wp_error($app_password)) {
                return $app_password;
            }

            $wrap_key = $wrap_key_client->get_key();
            if (is_wp_error($wrap_key)) {
                return $wrap_key;
            }

            $sealed = TokenSealer::seal($user_login . ':' . $app_password, $wrap_key['public_key']);
            if (is_wp_error($sealed)) {
                return $sealed;
            }

            return ['wrapped_app_token' => $sealed, 'wrap_key_id' => $wrap_key['key_id']];
        };

        // Host-supplied callable wins (and owns any sealing itself); otherwise
        // the standardized license exchange with the sealed credential.
        // extra_headers are forwarded verbatim to the brand issuer on every
        // exchange (e.g. for X-TOTP / X-Onecom-Client-Domain forwarded from
        // the surrounding request). Empty array is fine.
        $extra_headers = isset($args['extra_headers']) && is_array($args['extra_headers'])
            ? array_map('strval', $args['extra_headers'])
            : [];

        $provider = null !== $provider_callable
            ? new CallableGrndProvider($provider_callable)
            : new LicenseGrndProvider(
                $issuer_url,
                (string) ($args['license_key'] ?? ''),
                $product,
                $credentials,
                $extra_headers
            );

        // One rotation per user at a time (AppPasswordLock). A second tab that
        // arrives while the first one mints waits for it, then finds its GRND
        // in the cache instead of revoking the password that GRND carries.
        // The cache is read under the lock only, cache hits included: read
        // before it, this request would keep its own copy of a cache miss.
        return AppPasswordLock::run(
            $user_id,
            static function () use ($user_id, $product, $force_new, $token_manager, $wrap_key_client, $provider) {
                // Forced re-auth (after a WAP 401): revoke the stored App Password and
                // drop the cached GRND and wrap key so everything — including the
                // sealed credential baked into the GRND — is re-provisioned fresh.
                //
                // Braked first. Every forced re-auth revokes and re-mints an
                // Application Password, re-fetches the wrap key and re-runs the issuer
                // exchange, so a client that 401-loops (a stale cached widget build, a
                // host frontend without the widget's retry cap) would churn credentials
                // indefinitely. Legitimate force_new is rare — New chat, GDPR erasure,
                // GRND expiry — so this ceiling never touches real use. Note the check
                // runs BEFORE any invalidation: a throttled call must not strip the
                // user of the credential it is declining to replace.
                if ($force_new) {
                    $throttled = self::throttle_reauth($user_id, $product);
                    if (is_wp_error($throttled)) {
                        return $throttled;
                    }

                    (new AppPasswordManager())->delete_stored_password($user_id, $product);
                    $token_manager->forget($user_id, $product);
                    $wrap_key_client->forget();
                }

                return $token_manager->get($user_id, $product, $provider, $force_new);
            }
        );
    }

    /**
     * Count this forced re-auth against the rolling window, refusing once the
     * ceiling is reached.
     *
     * There is deliberately no success reset: this side never learns whether
     * WAP accepted the GRND it minted, so the only honest signal is "how many
     * re-mints has this user asked for lately". The window simply expires.
     *
     * @param int    $user_id WordPress user ID.
     * @param string $product Product slug.
     *
     * @return true|\WP_Error True when the re-auth may proceed.
     */
    private static function throttle_reauth(int $user_id, string $product)
    {
        $key   = 'wap_grnd_rl_' . $user_id . '_' . sanitize_key($product);
        $now   = time();
        $state = get_transient($key);

        // A window whose start has aged out (or a malformed entry) starts over.
        if (!is_array($state)
            || !isset($state['count'], $state['started'])
            || ($now - (int) $state['started']) >= self::REAUTH_WINDOW
        ) {
            $state = ['count' => 0, 'started' => $now];
        }

        if ((int) $state['count'] >= self::REAUTH_MAX_TRIES) {
            // Integrator-facing, like every other WP_Error here: it reaches
            // debug.log via ChatWidget::log_auth_failure(), while the end user
            // gets the generic unavailable message.
            return new \WP_Error(
                'wap_grnd_reauth_throttled',
                sprintf(
                    'Too many forced GRND re-issues (%1$d within %2$d seconds) for user %3$d / product "%4$s". '
                    . "WAP is rejecting freshly minted tokens — check that the issuer's signing key is in WAP's "
                    . 'JWKS registry and that the issuer environment resolves correctly.',
                    self::REAUTH_MAX_TRIES,
                    self::REAUTH_WINDOW,
                    $user_id,
                    $product
                )
            );
        }

        $state['count'] = (int) $state['count'] + 1;

        // Expire with the window, not from now, so a burst can't extend it.
        $ttl = max(1, (int) $state['started'] + self::REAUTH_WINDOW - $now);
        set_transient($key, $state, $ttl);

        return true;
    }
}
