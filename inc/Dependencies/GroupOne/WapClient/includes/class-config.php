<?php
/**
 * Per-copy settings: the library version and where its bundled assets and translations live.
 *
 * Held in a class, not in global constants, so that every copy of the library on a site (each under its own
 * namespace prefix) keeps its own values: a constant is defined once per request, and the first copy to
 * define it would decide for all of them.
 *
 * The defaults are the package's own location. A host that bundles the library elsewhere (a prefixer copies
 * only the PHP classes, not assets/ or i18n/) calls Config::configure() once, before the chat renders.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

final class Config
{
    /** The library version; also the `?ver=` cache-buster on the bundled assets. Bumped by scripts/bump-version.sh. */
    public const VERSION = '3.1.1';

    /** @var string|null */
    private static $url = null;

    /** @var string|null */
    private static $file = null;

    /** @var string|null */
    private static $version = null;

    /**
     * Override where this copy finds its assets and translations.
     *
     * @param array{url?: string, file?: string, version?: string} $args
     *     url:     public URL of the directory that holds assets/ (trailing slash added if missing).
     *     file:    path of a file in the directory that holds i18n/, as plugin_basename() expects.
     *     version: cache-buster for the assets, when the host ships its own build of them.
     */
    public static function configure(array $args): void
    {
        if (isset($args['url']) && '' !== $args['url']) {
            self::$url = rtrim((string) $args['url'], '/') . '/';
        }
        if (isset($args['file']) && '' !== $args['file']) {
            self::$file = (string) $args['file'];
        }
        if (isset($args['version']) && '' !== $args['version']) {
            self::$version = (string) $args['version'];
        }
    }

    public static function version(): string
    {
        return self::$version ?? self::VERSION;
    }

    /**
     * URL of the package root, with a trailing slash: assets live under `{url}assets/`.
     */
    public static function url(): string
    {
        return self::$url ?? plugin_dir_url(self::root() . '/wap-client.php');
    }

    /**
     * A file in the package root, for plugin_basename() when loading `i18n/`.
     */
    public static function file(): string
    {
        return self::$file ?? self::root() . '/wap-client.php';
    }

    /**
     * Forget every override.
     *
     * @internal For tests.
     */
    public static function reset(): void
    {
        self::$url     = null;
        self::$file    = null;
        self::$version = null;
    }

    /** The package root: one level above includes/. */
    private static function root(): string
    {
        return dirname(__DIR__);
    }
}
