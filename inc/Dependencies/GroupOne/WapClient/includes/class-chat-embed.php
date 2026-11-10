<?php
/**
 * Chat Embed — the chat widget rendered inline wherever the host wants it, with
 * no admin page and no panel chrome.
 *
 * Where register_chat_page() owns a whole admin page and register_chat_column()
 * docks a collapsible panel, an embed owns nothing: the library provides the
 * credentials, the assets and the localised config, and the host places the
 * mount point itself — inside a tab on a screen it already owns, a metabox, or
 * a React panel via WapChat.mount(el, {column: false}).
 *
 * See docs/integrating-a-product.md and the package README for the integration
 * contract.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Registers inline chat embeds and renders their mount points on demand.
 */
class ChatEmbed
{
    /**
     * Registered embeds, keyed by embed id.
     *
     * @var array<string, array>
     */
    private static array $embeds = [];

    /**
     * Ids whose screen test passed for the current request.
     *
     * @var array<string, bool>
     */
    private static array $active = [];

    /**
     * Ids whose mount point has already been emitted on this request.
     *
     * Only one mount point per surface may exist: the widget resolves it from a
     * single `cfg.root` selector, so a second element with the same id is dead
     * markup that sits on "Connecting…" forever — and duplicate ids are invalid
     * HTML besides. Because render() and get() are both public and documented as
     * safe to call unconditionally, the guard lives here rather than in the host.
     *
     * @var array<string, bool>
     */
    private static array $emitted = [];

    /**
     * Presentation defaults for an inline embed. The host's own surface (a tab
     * body, a metabox, an editor sidebar) already supplies the container and
     * usually its own card, so the widget fills the width it is given and draws
     * no second card. Caller-supplied layout keys always win.
     *
     * @var array<string, string>
     */
    private const EMBED_LAYOUT = [
        'width'  => 'fluid',
        'chrome' => 'flat',
    ];

    /**
     * Priority at which an embed activates on admin_enqueue_scripts.
     *
     * Ahead of ChatColumn's default 10 so that, when a host registers both on
     * one screen, the embed wins deterministically rather than depending on the
     * order the two were registered in.
     */
    private const ACTIVATE_PRIORITY = 5;

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    /**
     * Register an inline chat embed.
     *
     * Credential keys are identical to WP_Rocket_WapClient::register_chat_page(). Pass
     * 'screens' (admin hook suffixes or WP_Screen ids) and/or 'should_render'
     * (fn (WP_Screen|null $screen, string $hook): bool) to say which screens the
     * assets load on; with neither, the embed activates nowhere.
     *
     * CALL THIS ON `init` OR `admin_init`, NOT `admin_menu`: ChatWidget's AJAX
     * handlers resolve the surface from the live registry, and `admin_menu`
     * never fires on admin-ajax.php.
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
     *     layout?:         array<string, mixed>,
     * } $args Embed configuration.
     *
     * @return void
     */
    public static function register(array $args): void
    {
        $id = sanitize_key($args['id'] ?? '');
        if (!$id) {
            _doing_it_wrong(
                'WP_Rocket_WapClient::register_chat_embed',
                esc_html__('A non-empty id is required.', 'wap-client'),
                '2.2.0'
            );
            return;
        }

        $title = $args['title'] ?? __('Assistant', 'wap-client');

        // Sharing ChatWidget's registry is what makes ajax_auth() and
        // ajax_consent() work for an embed unchanged.
        $slug = ChatWidget::register_surface(
            array_merge($args, [
                'menu_slug'   => $id,
                'page_title'  => $title,
                'menu_title'  => $title,
                'root_id'     => self::root_id($id),
                // Forced, not merged. An embed is defined by having neither
                // panel chrome nor a page of its own, and a host migrating from
                // register_chat_column() will leave 'column' in place — which
                // would otherwise build a full docked panel inside their tab.
                // 'standalone' would likewise inject wap-standalone.css into a
                // screen the host owns.
                'column'      => [],
                'render_mode' => 'admin',
            ]),
            'WP_Rocket_WapClient::register_chat_embed',
            'embed',
            self::EMBED_LAYOUT
        );

        if (null === $slug) {
            return;
        }

        self::$embeds[$id] = [
            'id'     => $id,
            'target' => new ScreenTarget(
                $args['screens'] ?? [],
                $args['should_render'] ?? null,
                'wap_client_embed_screens'
            ),
        ];

        add_action('admin_enqueue_scripts', static function (string $hook) use ($id): void {
            self::maybe_activate($id, $hook);
        }, self::ACTIVATE_PRIORITY);
    }

    // -------------------------------------------------------------------------
    // Activation
    // -------------------------------------------------------------------------

    /**
     * Load the widget assets when the current screen was opted in.
     *
     * @param string $id   The registered embed id.
     * @param string $hook The current admin page hook suffix.
     *
     * @return void
     */
    private static function maybe_activate(string $id, string $hook): void
    {
        $embed = self::$embeds[$id] ?? null;
        if (!$embed || !$embed['target']->matches($hook, $id)) {
            return;
        }

        // A chat page on this screen is the more specific surface, and the two
        // cannot share one WapClientConfig global. Checked slug-side rather than
        // through the claim below so the outcome is the same whichever of the
        // two ran first.
        if (ChatWidget::hook_belongs_to_page($hook)) {
            return;
        }

        if (!ChatWidget::user_can_use_surface($id)) {
            return;
        }

        // Claimed only once the embed is certain to render, so a surface the
        // user may not see never blocks one they may.
        if (!ChatWidget::claim_screen($id)) {
            return;
        }

        self::$active[$id] = true;

        ChatWidget::enqueue_widget_assets($id);
    }

    /**
     * True when the embed's screen test passed on this request and the current
     * user may see it — i.e. when render()/get() will produce markup.
     *
     * Public so a host can skip building its own chrome (a tab, a panel header)
     * around a chatbox that is not going to appear.
     *
     * @param string $id The registered embed id.
     *
     * @return bool
     */
    public static function is_active(string $id): bool
    {
        $id = sanitize_key($id);

        if (empty(self::$active[$id])) {
            return false;
        }

        return ChatWidget::user_can_use_surface($id);
    }

    // -------------------------------------------------------------------------
    // Rendering
    // -------------------------------------------------------------------------

    /**
     * Echo the embed's mount point.
     *
     * A no-op unless the screen test already passed, so a host may call it
     * unconditionally from its own template.
     *
     * @param string $id The registered embed id.
     *
     * @return void
     */
    public static function render(string $id): void
    {
        echo self::get($id); // phpcs:ignore WordPress.Security.EscapeOutput -- get() returns markup already escaped by render_chat_root().
    }

    /**
     * The embed's mount point as an HTML string.
     *
     * For hosts that build markup rather than echo it (a tab renderer returning
     * a string, a REST/AJAX response, a template variable). Returns '' when the
     * embed is not active on this screen.
     *
     * @param string $id The registered embed id.
     *
     * @return string Escaped markup, or '' when there is nothing to render.
     */
    public static function get(string $id): string
    {
        $id = sanitize_key($id);

        if (!self::is_active($id)) {
            return '';
        }

        if (!empty(self::$emitted[$id])) {
            _doing_it_wrong(
                'WP_Rocket_WapClient::get_chatbox',
                esc_html(sprintf(
                    /* translators: %s: the embed id. */
                    __(
                        'The chatbox for "%s" was already placed on this screen. Only one mount point per '
                        . 'embed can work, so this call rendered nothing. Place it exactly once.',
                        'wap-client'
                    ),
                    $id
                )),
                '2.2.0'
            );
            return '';
        }

        self::$emitted[$id] = true;

        ob_start();
        ChatWidget::render_chat_root($id, self::root_id($id));

        return (string) ob_get_clean();
    }

    /**
     * DOM id of an embed's mount point. Namespaced by id, never the widget
     * default, so an embed cannot collide with a chat page's root on the same
     * document.
     *
     * @param string $id The registered embed id.
     *
     * @return string
     */
    private static function root_id(string $id): string
    {
        return 'wap-chat-embed-root-' . $id;
    }
}
