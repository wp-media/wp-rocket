<?php
/**
 * Chat Column — a collapsible chat panel docked onto host-owned admin screens.
 *
 * See docs/integrating-a-product.md ("dock the chat as a collapsible column")
 * and the package README for the integration contract, framing options,
 * persistence model and accessibility behaviour.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Registers docked chat columns and persists their per-user state.
 */
class ChatColumn
{
    /**
     * Registered columns, keyed by column id.
     *
     * @var array<string, array>
     */
    private static array $columns = [];

    /**
     * Ids whose screen test passed for the current request.
     *
     * @var array<string, bool>
     */
    private static array $active = [];

    private const META_PREFIX = 'wap_client_column_';

    /**
     * Presentation defaults for a docked column: it owns a full-height panel of
     * its own, so it fills that panel and draws no card inside it.
     * Caller-supplied layout keys always win over these.
     *
     * @var array<string, string>
     */
    private const COLUMN_LAYOUT = [
        'width'        => 'fluid',
        'height'       => 'fill',
        'chrome'       => 'flat',
        'expandToggle' => 'off',
    ];

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    /**
     * Register a docked chat column.
     *
     * Credential keys are identical to WP_Rocket_WapClient::register_chat_page(). Pass
     * 'screens' (admin hook suffixes or WP_Screen ids) and/or 'should_render'
     * (fn (WP_Screen|null $screen, string $hook): bool) to opt screens in; with
     * neither, the column renders nowhere.
     *
     * CALL THIS ON `init` OR `admin_init`, NOT `admin_menu`: ajax_state() only
     * accepts ids present in this registry on the current request, and
     * `admin_menu` never fires on admin-ajax.php.
     *
     * @param array{
     *     id:              string,
     *     product:         string,
     *     server_url:      string,
     *     capability?:     string,
     *     screens?:        string[],
     *     should_render?:  callable,
     *     grnd?:           array{issuer_url?: string, license_key?: string},
     *     grnd_provider?:  callable,
     *     mode?:           string,
     *     mcp_endpoint?:   string,
     *     page_context?:   string,
     *     terms_url?:      string,
     *     privacy_url?:    string,
     *     site_access_url?: string,
     *     custom_css_url?: string,
     *     title?:          string,
     *     column?:         array{side?: string, width?: string, mode?: string,
     *                            defaultState?: string, breakpoint?: string,
     *                            showLauncher?: bool, persist?: bool},
     *     layout?:         array<string, mixed>,
     * } $args Column configuration.
     *
     * @return void
     */
    public static function register(array $args): void
    {
        $id = sanitize_key($args['id'] ?? '');
        if (!$id) {
            _doing_it_wrong(
                'WP_Rocket_WapClient::register_chat_column',
                esc_html__('A non-empty id is required.', 'wap-client'),
                '2.2.0'
            );
            return;
        }

        $column = is_array($args['column'] ?? null) ? $args['column'] : [];
        $column['enabled'] = true;

        $title = $args['title'] ?? __('Assistant', 'wap-client');

        // Sharing ChatWidget's registry is what makes ajax_auth() and
        // ajax_consent() work for a column unchanged.
        $slug = ChatWidget::register_surface(
            array_merge($args, [
                'menu_slug'  => $id,
                'page_title' => $title,
                'menu_title' => $title,
                'column'     => $column,
            ]),
            'WP_Rocket_WapClient::register_chat_column',
            'column',
            self::COLUMN_LAYOUT
        );

        if (null === $slug) {
            return;
        }

        self::$columns[$id] = [
            'id'     => $id,
            'target' => new ScreenTarget(
                $args['screens'] ?? [],
                $args['should_render'] ?? null,
                'wap_client_column_screens'
            ),
        ];

        add_action('admin_enqueue_scripts', static function (string $hook) use ($id): void {
            self::maybe_activate($id, $hook);
        });

        // admin_footer fires before admin_print_footer_scripts, so the mount
        // point exists before the footer-enqueued widget script boots.
        add_action('admin_footer', static function () use ($id): void {
            self::render($id);
        }, 5);
    }

    // -------------------------------------------------------------------------
    // Screen matching
    // -------------------------------------------------------------------------

    /**
     * Load the widget assets when the current screen was opted in.
     *
     * @param string $id   The registered column id.
     * @param string $hook The current admin page hook suffix.
     *
     * @return void
     */
    private static function maybe_activate(string $id, string $hook): void
    {
        $column = self::$columns[$id] ?? null;
        if (!$column || !$column['target']->matches($hook, $id)) {
            return;
        }

        // A chat page on this screen is the more specific surface, and the two
        // cannot share one WapClientConfig global.
        if (ChatWidget::hook_belongs_to_page($hook)) {
            return;
        }

        if (!ChatWidget::user_can_use_surface($id)) {
            return;
        }

        // An inline embed on this screen also wins — it activates at an earlier
        // hook priority, so this stands down regardless of registration order.
        // Claimed only once the column is certain to render, so a surface the
        // user may not see never blocks one they may.
        if (!ChatWidget::claim_screen($id)) {
            return;
        }

        self::$active[$id] = true;

        ChatWidget::enqueue_widget_assets($id);

        wp_localize_script(
            'wap-client-chat-' . $id,
            'WapClientColumn_' . str_replace('-', '_', $id),
            [
                'id'           => $id,
                'rootId'       => self::root_id($id),
                'initialState' => self::state($id),
                'stateNonce'   => wp_create_nonce('wp_rocket_wap_client_column_state'),
            ]
        );
    }

    // -------------------------------------------------------------------------
    // Rendering
    // -------------------------------------------------------------------------

    /**
     * Emit the column mount point.
     *
     * Public so a host can place the markup itself. A no-op unless the screen
     * test already passed, and it renders at most once per request.
     *
     * @param string $id The registered column id.
     *
     * @return void
     */
    public static function render(string $id): void
    {
        if (empty(self::$active[$id])) {
            return;
        }

        self::$active[$id] = false;

        if (!ChatWidget::user_can_use_surface($id)) {
            return;
        }

        $state = self::state($id);

        ?>
        <div
            class="wap-chat-column"
            data-wap-chat-column="<?php echo esc_attr($id); ?>"
            data-wap-column-state="<?php echo esc_attr($state); ?>"
        >
            <?php ChatWidget::render_chat_root($id, self::root_id($id)); ?>
        </div>
        <?php
    }

    /**
     * DOM id of a column's widget mount point. Never the widget default, so a
     * chat page and a column can coexist on one screen.
     *
     * @param string $id The registered column id.
     *
     * @return string
     */
    private static function root_id(string $id): string
    {
        return 'wap-chat-column-root-' . $id;
    }

    // -------------------------------------------------------------------------
    // Per-user state
    // -------------------------------------------------------------------------

    /**
     * The current user's collapsed/expanded preference for a column.
     *
     * @param string $id The registered column id.
     *
     * @return string 'expanded' or 'collapsed'.
     */
    private static function state(string $id): string
    {
        $config  = ChatWidget::surface_column_config($id);
        $default = in_array($config['defaultState'] ?? '', ['expanded', 'collapsed'], true)
            ? $config['defaultState']
            : 'collapsed';

        if (isset($config['persist']) && !$config['persist']) {
            return $default;
        }

        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return $default;
        }

        $stored = get_user_meta($user->ID, self::META_PREFIX . $id, true);

        return in_array($stored, ['expanded', 'collapsed'], true) ? $stored : $default;
    }

    /**
     * AJAX handler: read or record the current user's column state.
     *
     * POST params: op = 'get' | 'set', id = column id,
     * state = 'expanded' | 'collapsed'.
     *
     * The id allowlist is the live registry, so the column must be registered on
     * a hook that also runs for admin-ajax.php — see register().
     *
     * @return void (sends JSON response and exits)
     */
    public static function ajax_state(): void
    {
        check_ajax_referer('wp_rocket_wap_client_column_state');

        $op = sanitize_key(wp_unslash($_POST['op'] ?? 'get'));
        $id = sanitize_key(wp_unslash($_POST['id'] ?? ''));

        // Gated by the capability of the column the request names.
        if (isset(self::$columns[$id]) && !ChatWidget::user_can_use_surface($id)) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wap-client')], 403);
        }

        $user = wp_get_current_user();
        if (!$user->exists()) {
            wp_send_json_error(['message' => __('User not authenticated.', 'wap-client')], 401);
        }

        // Registered ids only — arbitrary meta keys must not be writable here.
        if (!in_array($op, ['get', 'set'], true) || !isset(self::$columns[$id])) {
            wp_send_json_error(['message' => __('Invalid request parameters.', 'wap-client')], 400);
        }

        if ('set' === $op) {
            $state = sanitize_key(wp_unslash($_POST['state'] ?? ''));
            if (!in_array($state, ['expanded', 'collapsed'], true)) {
                wp_send_json_error(['message' => __('Invalid request parameters.', 'wap-client')], 400);
            }
            update_user_meta($user->ID, self::META_PREFIX . $id, $state);
            wp_send_json_success(['state' => $state]);
        }

        wp_send_json_success(['state' => self::state($id)]);
    }
}
