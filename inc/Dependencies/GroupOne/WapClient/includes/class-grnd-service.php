<?php
/**
 * GRND Service — the one-call GRND acquisition facade for WordPress hosts.
 *
 * Owns the full choreography that every consumer would otherwise reimplement:
 * App Password provisioning, wrap-key fetch, credential sealing, the brand
 * issuer exchange (or a custom provider), caching, and force-new invalidation.
 * Integrators call WapClient::get_grnd_token() and hand the result to the
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
     *     send_user_ref?:  bool,
     *     password_label?: string,
     *     extra_headers?:  array<string, string>,
     *     permissions?:    array<string, string>|callable,
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
     *   - send_user_ref   Send the user's opaque per-product reference to the
     *                     issuer as `user_ref` (see UserRef). It is created on
     *                     first use, and the exchange is aborted with
     *                     `wap_user_ref_unavailable` when it cannot be read or
     *                     created. Default false: the request body is then
     *                     exactly what it was without this option.
     *   - password_label  Label for the minted App Password. Default product.
     *   - extra_headers   Optional associative array of HTTP headers to
     *                     forward verbatim to the brand issuer on every
     *                     exchange (e.g. ['X-TOTP' => '123456',
     *                     'X-Onecom-Client-Domain' => 'wpin10.1prod.one']).
     *                     Content-Type is always set to application/json.
     *   - permissions     The user's Permissions choices (`id => value`), or a
     *                     callable returning them, sent to the issuer by the
     *                     standardized exchange. Default: the product's
     *                     registered rows for this user
     *                     ({@see ChatWidget::permissions_for()}). Ignored with
     *                     grnd_provider, whose callable sends its own.
     *
     * @return string|\WP_Error The GRND on success.
     */
    public static function get_grnd(array $args)
    {
        $product    = (string) ($args['product'] ?? '');
        $server_url = rtrim((string) ($args['server_url'] ?? ''), '/');
        $force_new  = ! empty($args['force_new']);

        $send_user_ref = ! empty($args['send_user_ref']);

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

        // Extra request fields, composed from what the active options contribute.
        $contributors = [];
        if ($send_user_ref) {
            // A read, never a creation: the reference was resolved under the lock before the exchange.
            $contributors[] = static function () use ($user_id, $product) {
                $ref = UserRef::stored($user_id, $product);

                return null === $ref
                    ? new \WP_Error(UserRef::ERROR_UNAVAILABLE, 'The user reference is not available for the exchange.')
                    : ['user_ref' => $ref];
            };
        }

        // The user's Permissions choices, for the issuer to sign into the GRND. Read on a
        // GRND cache miss only, like the credential, so a choice the user just changed
        // (which drops the cached GRND) is what the issuer signs. A host's array or
        // callable is final (anything but an array sends nothing); without one, the
        // product's registered rows are sent. No choices, no field: the request is unchanged.
        $permissions_arg = $args['permissions'] ?? null;
        $contributors[]  = static function () use ($permissions_arg, $product, $user_id): array {
            if (is_callable($permissions_arg)) {
                $permissions = $permissions_arg();
            } elseif (is_array($permissions_arg)) {
                $permissions = $permissions_arg;
            } else {
                // Rows are registered by the chat surfaces; without them (a host
                // calling the facade with no surface) there is nothing to send.
                $permissions = class_exists(ChatWidget::class) ? ChatWidget::permissions_for($product, $user_id) : [];
            }

            $map = [];
            foreach (is_array($permissions) ? $permissions : [] as $id => $value) {
                if (is_scalar($value)) {
                    $map[(string) $id] = (string) $value;
                }
            }

            // An object even when every id is numeric, which would otherwise encode as a list.
            return [] === $map ? [] : ['permissions' => (object) $map];
        };

        $is_builtin = null === $provider_callable;
        $provider   = $is_builtin
            ? new LicenseGrndProvider(
                $issuer_url,
                (string) ($args['license_key'] ?? ''),
                $product,
                $credentials,
                $extra_headers,
                self::compose_extras($contributors)
            )
            : new CallableGrndProvider($provider_callable);

        // One rotation per user at a time (AppPasswordLock). A second tab that
        // arrives while the first one mints waits for it, then finds its GRND
        // in the cache instead of revoking the password that GRND carries.
        // The cache is read under the lock only, cache hits included: read
        // before it, this request would keep its own copy of a cache miss.
        return AppPasswordLock::run(
            $user_id,
            static function () use ($user_id, $product, $force_new, $token_manager, $wrap_key_client, $provider, $send_user_ref, $is_builtin) {
                // The reference comes first, before any cache read, so a reference failure
                // never revokes an App Password, burns the force_new brake or re-fetches the
                // wrap key. Without a stored reference, any cached GRND predates it: drop it
                // so this request mints one that carries the reference.
                if ($send_user_ref && null === UserRef::stored($user_id, $product)) {
                    // The built-in exchange refuses without consent, so create nothing before it.
                    if (!$is_builtin || ConsentGate::is_granted($user_id, $product)) {
                        $ref = UserRef::ensure($user_id, $product);
                        if (is_wp_error($ref)) {
                            return $ref;
                        }

                        $token_manager->forget($user_id, $product);
                    }
                }

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
     * Combine payload contributors into the one callable the provider takes.
     *
     * Each contributor returns an array of request fields or a WP_Error. They run
     * in order, the first WP_Error wins and stops the rest, and the arrays are merged.
     *
     * @param array<int, callable> $contributors Payload contributors.
     *
     * @return callable|null Null when there is nothing to add, so the request body stays unchanged.
     */
    private static function compose_extras(array $contributors): ?callable
    {
        if ([] === $contributors) {
            return null;
        }

        return static function () use ($contributors) {
            $merged = [];
            foreach ($contributors as $contributor) {
                $fields = $contributor();
                // A non-array is passed through for the provider's guard to reject.
                if (is_wp_error($fields) || !is_array($fields)) {
                    return $fields;
                }
                // Two contributors must not set one field: the later would silently replace the earlier.
                $clash = array_intersect_key($merged, $fields);
                if ([] !== $clash) {
                    return new \WP_Error(
                        'wap_grnd_payload_invalid',
                        'Two GRND payload contributors set the same field: ' . implode(', ', array_map('strval', array_keys($clash))) . '.'
                    );
                }
                $merged = array_merge($merged, $fields);
            }

            return $merged;
        };
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
