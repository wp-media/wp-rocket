<?php
/**
 * Screen targeting — resolves whether a widget surface belongs on the current
 * admin screen.
 *
 * Shared by ChatColumn and ChatEmbed: both let the host opt screens in with
 * `screens` (hook suffixes / WP_Screen ids) and/or a `should_render` callable,
 * and both are fail-closed when neither is given.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * A surface's screen opt-in rules.
 */
final class ScreenTarget
{
    /**
     * Screen ids / hook suffixes the surface was opted into.
     *
     * @var string[]
     */
    private array $screens;

    /**
     * Optional dynamic test: fn (WP_Screen|null $screen, string $hook): bool.
     *
     * @var callable|null
     */
    private $should_render;

    /**
     * Filter name applied to $screens, or '' to skip filtering.
     */
    private string $screens_filter;

    /**
     * @param mixed       $screens        Raw screens value from the caller.
     * @param mixed       $should_render  Raw should_render value from the caller.
     * @param string      $screens_filter Filter applied to the screen list.
     */
    public function __construct($screens, $should_render, string $screens_filter = '')
    {
        $this->screens        = self::sanitize_screens($screens);
        $this->should_render  = is_callable($should_render) ? $should_render : null;
        $this->screens_filter = $screens_filter;
    }

    /**
     * True when the host opted the current screen in. Fail-closed: a surface
     * with neither `screens` nor `should_render` matches nothing.
     *
     * @param string $hook       The current admin page hook suffix.
     * @param string $surface_id Surface id, passed to the screens filter.
     *
     * @return bool
     */
    public function matches(string $hook, string $surface_id): bool
    {
        $screen    = function_exists('get_current_screen') ? get_current_screen() : null;
        $screen_id = ($screen && isset($screen->id)) ? (string) $screen->id : '';

        $screens = $this->screens;
        if ('' !== $this->screens_filter) {
            /**
             * Filter the screens a surface is mounted on.
             *
             * @param string[] $screens Hook suffixes / WP_Screen ids.
             * @param string   $id      The surface id.
             */
            $screens = self::sanitize_screens(
                apply_filters($this->screens_filter, $screens, $surface_id)
            );
        }

        foreach ($screens as $candidate) {
            if ($candidate === $hook || ('' !== $screen_id && $candidate === $screen_id)) {
                return true;
            }
        }

        if ($this->should_render) {
            return (bool) call_user_func($this->should_render, $screen, $hook);
        }

        return false;
    }

    /**
     * Reduce a screen list to ids that could plausibly be matched.
     *
     * NOT sanitize_key(): it lowercases and strips dots, so real ids like
     * 'toplevel_page_MyPlugin' or 'edit.php' could never match — and with a
     * fail-closed default the surface would render nowhere, silently. The
     * filtered value in matches() goes through here too, so both paths agree.
     *
     * @param mixed $screens Raw screens value.
     *
     * @return string[] Screen ids, in order, without blanks.
     */
    private static function sanitize_screens($screens): array
    {
        if (!is_array($screens)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static function ($screen): string {
                $screen = trim((string) $screen);

                return preg_match('/^[A-Za-z0-9_.\-]+$/', $screen) ? $screen : '';
            },
            array_filter($screens, 'is_scalar')
        )));
    }
}
