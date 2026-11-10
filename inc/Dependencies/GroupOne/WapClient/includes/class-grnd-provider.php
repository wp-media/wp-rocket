<?php
/**
 * GRND providers — how the library obtains a group.one identity token.
 *
 * The GRND is a signed JWT proving the customer's entitlement. It is issued by
 * a backend the *product* trusts (its brand backend), never by WAP — WAP only
 * verifies the signature. The library defines the provider contract and ships
 * two implementations:
 *
 *  - LicenseGrndProvider: the standardized brand exchange. Every brand backend
 *    exposes the same endpoint contract (only the host differs):
 *        POST {issuer_url}
 *        { "license_key": "...", "site_url": "...", "product": "...",
 *          "wrapped_app_token": "<base64 sealed box>", "wrap_key_id": "..." }
 *          -> { "grnd": "<JWT>", "expires_at": <unix seconds> }
 *    The wrapped_app_token fields are supplied by the WordPress adapter (the
 *    app credential sealed to WAP's wrap key — the brand only relays
 *    ciphertext); SaaS-style hosts use the same provider without them.
 *    Most products integrate by passing issuer_url + license_key — zero code.
 *
 *  - CallableGrndProvider: escape hatch for brands whose backends cannot
 *    conform — the host plugin supplies a callable returning the raw GRND (or
 *    a WP_Error), and owns everything about how it is obtained.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Contract for obtaining a fresh GRND.
 *
 * Implementations fetch a NEW token on every call — caching and expiry are
 * TokenManager's job, not the provider's.
 */
interface GrndProviderInterface
{
    /**
     * Fetch a fresh GRND.
     *
     * @return array{grnd: string, expires_at: int}|\WP_Error Token payload on
     *         success (expires_at may be 0 when the issuer did not supply it),
     *         WP_Error when the customer is not entitled or the issuer failed.
     */
    public function fetch();
}

/**
 * Default provider — standardized license→GRND exchange against a brand host.
 */
class LicenseGrndProvider implements GrndProviderInterface
{
    /**
     * HTTP timeout for the exchange call, in seconds.
     *
     * @var int
     */
    private const TIMEOUT = 15;

    /**
     * Extra HTTP headers the library is willing to forward to the brand issuer.
     *
     * Anything outside this allow-list is silently dropped — integrators cannot
     * inject arbitrary headers (e.g. `Authorization`) into the issuer exchange.
     * Adding a header here is a deliberate, code-reviewed decision.
     *
     * @var array<int, string>
     */
    private const ALLOWED_EXTRA_HEADERS = [
        'X-TOTP',
        'X-Onecom-Client-Domain',
    ];

    /**
     * Brand exchange endpoint, e.g. 'https://api.wp-rocket.me/grnd/token'.
     *
     * @var string
     */
    private string $issuer_url;

    /**
     * Product license key known to the host plugin.
     *
     * @var string
     */
    private string $license_key;

    /**
     * Product slug, e.g. 'wp-rocket'.
     *
     * @var string
     */
    private string $product;

    /**
     * Optional credential factory, invoked once per exchange (i.e. only on a
     * GRND cache miss). Returns the sealed app-token fields to include in the
     * request — ['wrapped_app_token' => ..., 'wrap_key_id' => ...] — or a
     * WP_Error when minting/sealing failed. Null for hosts without a
     * platform credential (SaaS-style).
     *
     * @var callable|null
     */
    private $credentials;

    /**
     * Extra HTTP headers forwarded to the brand issuer on every exchange.
     * Restricted to {@see ALLOWED_EXTRA_HEADERS} by the constructor — anything
     * outside the allow-list is dropped before storage. Always merged with the
     * default Content-Type: application/json header.
     *
     * @var array<string, string>
     */
    private array $extra_headers;

    /**
     * Constructor.
     *
     * @param string               $issuer_url     Brand exchange endpoint URL.
     * @param string               $license_key    Product license key.
     * @param string               $product        Product slug.
     * @param callable|null        $credentials    Optional sealed-credential factory
     *                                            (see property docblock).
     * @param array<string,string> $extra_headers  Optional headers forwarded to the
     *                                            issuer (e.g. auth/identity headers
     *                                            from the surrounding request).
     */
    public function __construct(
        string $issuer_url,
        string $license_key,
        string $product,
        ?callable $credentials = null,
        array $extra_headers = []
    ) {
        $this->issuer_url  = $issuer_url;
        $this->license_key = $license_key;
        $this->product     = $product;
        $this->credentials = $credentials;
        // Restrict the forwarded headers to an explicit allow-list so an
        // integrator (or a misconfigured host) cannot smuggle arbitrary
        // headers (e.g. `Authorization`) into the brand issuer exchange.
        $this->extra_headers = array_intersect_key(
            $extra_headers,
            array_flip(self::ALLOWED_EXTRA_HEADERS)
        );
    }

    /**
     * Exchange the license for a GRND at the brand backend.
     *
     * On onecom hosting, TOTP + the X-Onecom-Client-Domain header are
     * sufficient — `license_key`, `site_url` and `product` are all optional.
     * They are included in the payload only when non-empty so the brand
     * backend never sees a blank `license_key`.
     *
     * @return array{grnd: string, expires_at: int}|\WP_Error
     */
    public function fetch()
    {
        if (!$this->issuer_url) {
            return new \WP_Error(
                'wap_grnd_not_configured',
                __('GRND issuer URL must be configured.', 'wap-client')
            );
        }

        // Refuse non-https issuer URLs unconditionally. The exchange carries
        // the customer's license_key; sending it over plaintext would expose
        // the credential on any untrusted network hop.
        $scheme = parse_url($this->issuer_url, PHP_URL_SCHEME);
        if (is_string($scheme) && strtolower($scheme) !== 'https') {
            return new \WP_Error(
                'wap_grnd_issuer_insecure',
                __('The GRND issuer URL must use https.', 'wap-client')
            );
        }

        // Optional host allow-list. Integrators can lock the issuer down to a
        // known set of brand hosts via:
        //   add_filter('wap_client_allowed_issuer_hosts',
        //              fn() => ['api.wp-rocket.me']);
        // An empty list (the default) keeps every existing install working;
        // a non-empty list enforces membership. Comparison is exact (no
        // subdomain wildcards) and case-insensitive on the host part only.
        $allowed_hosts = apply_filters('wap_client_allowed_issuer_hosts', [], $this->product);
        if (is_array($allowed_hosts) && !empty($allowed_hosts)) {
            $host = parse_url($this->issuer_url, PHP_URL_HOST);
            if (!is_string($host) || !in_array(strtolower($host), array_map('strtolower', array_map('strval', $allowed_hosts)), true)) {
                return new \WP_Error(
                    'wap_grnd_issuer_not_allowed',
                    sprintf(
                        /* translators: 1: issuer host that was rejected */
                        __('The GRND issuer host "%s" is not in the wap_client_allowed_issuer_hosts allow-list.', 'wap-client'),
                        is_string($host) ? $host : ''
                    )
                );
            }
        }

        // Only include fields that actually have a value. The brand
        // backend (wp-api) treats them all as optional; on onecom hosting
        // TOTP + the X-Onecom-Client-Domain header in extra_headers are
        // enough to prove entitlement via CRM.
        $payload = [];
        if ($this->license_key !== '') {
            $payload['license_key'] = $this->license_key;
        }
        // Site binding for the entitlement decision: a product license is
        // issued per install, so the brand backend needs the caller's own home
        // URL to tell whether this site is covered by $license_key.
        $site_url = (string) home_url();
        if ($site_url !== '') {
            $payload['site_url'] = $site_url;
        }
        if ($this->product !== '') {
            $payload['product'] = $this->product;
        }

        // Mint + seal the platform credential only now — on a cache miss —
        // so a cached GRND never triggers a needless App Password rotation.
        if ($this->credentials !== null) {
            $sealed = ($this->credentials)();
            if (is_wp_error($sealed)) {
                return $sealed;
            }
            if (!is_array($sealed) || empty($sealed['wrapped_app_token']) || empty($sealed['wrap_key_id'])) {
                return new \WP_Error(
                    'wap_seal_failed',
                    __('The credential factory did not return a sealed app token.', 'wap-client')
                );
            }
            $payload['wrapped_app_token'] = (string) $sealed['wrapped_app_token'];
            $payload['wrap_key_id']       = (string) $sealed['wrap_key_id'];
        }

        $response = wp_remote_post($this->issuer_url, [
            'headers' => array_merge(
                ['Content-Type' => 'application/json'],
                $this->extra_headers
            ),
            'body'    => wp_json_encode($payload),
            'timeout' => self::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status = wp_remote_retrieve_response_code($response);
        $body   = json_decode(wp_remote_retrieve_body($response), true);

        // 401/403 from the brand mean the customer is not entitled (bad or
        // expired license) — a distinct, actionable failure for integrators.
        if (401 === $status || 403 === $status) {
            return new \WP_Error(
                'wap_grnd_not_entitled',
                sprintf(
                    /* translators: 1: HTTP status code */
                    __('The brand backend rejected the license (HTTP %d) — the customer is not entitled to a GRND.', 'wap-client'),
                    $status
                ),
                ['status' => $status]
            );
        }

        // Unknown extra response fields are ignored on purpose: the brand
        // schema is draft v0 and additive changes must not break clients.
        if ($status < 200 || $status >= 300 || !is_array($body) || empty($body['grnd'])) {
            return new \WP_Error(
                'wap_grnd_exchange_failed',
                sprintf(
                    /* translators: 1: HTTP status code */
                    __('GRND exchange failed (HTTP %d) — is the product license valid?', 'wap-client'),
                    $status
                ),
                ['status' => $status]
            );
        }

        return [
            'grnd'       => (string) $body['grnd'],
            'expires_at' => (int) ($body['expires_at'] ?? 0),
        ];
    }
}

/**
 * Escape-hatch provider wrapping a host-supplied callable.
 *
 * The callable returns either the raw GRND string, an array with 'grnd' (and
 * optionally 'expires_at'), or a WP_Error.
 */
class CallableGrndProvider implements GrndProviderInterface
{
    /**
     * Host-supplied token source.
     *
     * @var callable
     */
    private $callback;

    /**
     * Constructor.
     *
     * @param callable $callback Returns string|array{grnd:string,expires_at?:int}|WP_Error.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Invoke the callable and normalise its return value.
     *
     * @return array{grnd: string, expires_at: int}|\WP_Error
     */
    public function fetch()
    {
        $result = ($this->callback)();

        if (is_wp_error($result)) {
            return $result;
        }
        if (is_string($result) && $result !== '') {
            return ['grnd' => $result, 'expires_at' => 0];
        }
        if (is_array($result) && !empty($result['grnd'])) {
            return [
                'grnd'       => (string) $result['grnd'],
                'expires_at' => (int) ($result['expires_at'] ?? 0),
            ];
        }

        return new \WP_Error(
            'wap_grnd_provider_invalid',
            __('The grnd_provider callable did not return a GRND.', 'wap-client')
        );
    }
}
