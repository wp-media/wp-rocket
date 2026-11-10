<?php
/**
 * App Password Lock — serializes WAP's writes to a user's Application Passwords.
 *
 * WordPress core keeps all of a user's Application Passwords in one serialized
 * user meta value and rewrites the whole value on every create and delete,
 * without any locking. Two requests that rotate a user's WAP password at the
 * same time (two admin tabs loading past the rotation point) can therefore
 * lose each other's writes: a revoked password comes back, or a new one
 * vanishes. Everything that mints or revokes a WAP password runs inside this
 * lock, one request per user at a time.
 *
 * The lock is a row in the options table taken with INSERT IGNORE, which is
 * atomic thanks to the unique option_name index (the technique core's
 * WP_Upgrader::create_lock() uses), so it needs nothing WordPress itself does
 * not. It always lives in the first site's options table: Application
 * Passwords belong to the user, and users are shared by every site of a
 * network. It is re-entrant within a request, and it carries an expiry, so a
 * request that dies while holding it blocks the user for LEASE seconds at most.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Per-user mutex around Application Password rotation and revocation.
 */
final class AppPasswordLock
{
    /**
     * Seconds a lock stays valid once taken. Covers the slowest locked section:
     * the wrap-key fetch and the issuer exchange, 15-second timeouts each.
     *
     * @var int
     */
    private const LEASE = 60;

    /**
     * Seconds to wait for another request to release the lock before giving up.
     *
     * @var int
     */
    private const WAIT = 20;

    /**
     * Pause between two attempts, in microseconds.
     *
     * @var int
     */
    private const RETRY_INTERVAL = 100000;

    /**
     * Database errors in a row after which taking or releasing the lock gives up.
     *
     * @var int
     */
    private const MAX_DB_ERRORS = 10;

    /**
     * Locks this request holds: user ID => [lock value, nesting depth].
     *
     * @var array<int, array{0: string, 1: int}>
     */
    private static $held = [];

    /**
     * Whether the shutdown release is registered.
     *
     * @var bool
     */
    private static $shutdown_registered = false;

    /**
     * Run $callback while holding the user's lock.
     *
     * @param int      $wp_user_id WordPress user ID whose Application Passwords are about to change.
     * @param callable $callback   Work to do under the lock.
     *
     * @return mixed The callback's return value, or a WP_Error when the lock
     *               could not be taken: `wap_app_password_busy` when another
     *               request kept it for more than WAIT seconds,
     *               `wap_app_password_lock_failed` on a database error that persists.
     */
    public static function run(int $wp_user_id, callable $callback)
    {
        $acquired = self::acquire($wp_user_id);
        if (is_wp_error($acquired)) {
            return $acquired;
        }

        try {
            return $callback();
        } finally {
            self::release($wp_user_id);
        }
    }

    /**
     * Take the user's lock, waiting up to WAIT seconds for its current holder.
     *
     * @param int $wp_user_id WordPress user ID.
     *
     * @return true|\WP_Error True once the lock is held by this request.
     */
    private static function acquire(int $wp_user_id)
    {
        if (isset(self::$held[$wp_user_id])) {
            self::$held[$wp_user_id][1]++;

            return true;
        }

        global $wpdb;

        $table    = self::table();
        $name     = self::option_name($wp_user_id);
        $deadline = microtime(true) + self::WAIT;
        $errors   = 0;

        // Failed attempts are expected under contention (see below): keep them out of the response and the log.
        $suppressed = $wpdb->suppress_errors(true);

        try {
            do {
                // "{expiry}:{random}": the expiry lets a later request take over the
                // lock of one that died; the random part makes the value ours alone.
                $value = (time() + self::LEASE) . ':' . wp_generate_password(12, false);

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $inserted = $wpdb->query(
                    $wpdb->prepare(
                        "INSERT IGNORE INTO `{$table}` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, 'no')",
                        $name,
                        $value
                    )
                );
                if (1 === $inserted) {
                    return self::hold($wp_user_id, $value);
                }

                if (false === $inserted) {
                    // InnoDB can answer concurrent inserts of one key with a deadlock and
                    // roll the statement back: that is contention, so try again. An error
                    // that persists is not, and fails fast.
                    if (++$errors >= self::MAX_DB_ERRORS) {
                        return new \WP_Error(
                            'wap_app_password_lock_failed',
                            sprintf('Could not take the Application Password lock of user %d: %s', $wp_user_id, (string) $wpdb->last_error)
                        );
                    }

                    usleep(self::RETRY_INTERVAL);
                    continue;
                }
                $errors = 0;

                // Held by another request: take it over only once its lease ran out.
                // (No row: released in between, or the read failed; either way, try again after the pause.)
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $current = $wpdb->get_var(
                    $wpdb->prepare("SELECT `option_value` FROM `{$table}` WHERE `option_name` = %s", $name)
                );

                if (null !== $current && (int) $current < time()) {
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $taken = $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE `{$table}` SET `option_value` = %s WHERE `option_name` = %s AND `option_value` = %s",
                            $value,
                            $name,
                            (string) $current
                        )
                    );
                    if (1 === $taken) {
                        return self::hold($wp_user_id, $value);
                    }
                }

                usleep(self::RETRY_INTERVAL);
            } while (microtime(true) < $deadline);
        } finally {
            $wpdb->suppress_errors($suppressed);
        }

        return new \WP_Error(
            'wap_app_password_busy',
            sprintf(
                'Another request kept the Application Password lock of user %d for more than %d seconds.',
                $wp_user_id,
                self::WAIT
            )
        );
    }

    /**
     * Record the lock as held, and drop this request's cached copy of the
     * user's meta: it may have been read before the previous holder rewrote
     * the uuid on record and the passwords themselves (current_user_can()
     * alone loads every meta of the user), and everything under the lock
     * must see what that holder left.
     *
     * @param int    $wp_user_id WordPress user ID.
     * @param string $value      The lock row's value, ours alone.
     *
     * @return true
     */
    private static function hold(int $wp_user_id, string $value): bool
    {
        global $wpdb;

        self::$held[$wp_user_id] = [$value, 1];
        wp_cache_delete($wp_user_id, 'user_meta');

        // HyperDB / LudicrousDB: read the user's meta from the primary, not a replica that may lag behind it.
        if (is_object($wpdb) && method_exists($wpdb, 'send_reads_to_masters')) {
            $wpdb->send_reads_to_masters();
        }

        // A fatal error or an exit (wp_die(), wp_send_json()) skips run()'s finally; still release, so the
        // user is not locked out until the lease ends.
        if (!self::$shutdown_registered) {
            self::$shutdown_registered = true;
            register_shutdown_function(
                static function (): void {
                    foreach (array_keys(self::$held) as $held_user_id) {
                        self::$held[$held_user_id][1] = 1;
                        self::release($held_user_id);
                    }
                }
            );
        }

        return true;
    }

    /**
     * Release one level of the user's lock; the row goes once the outermost level ends.
     *
     * @param int $wp_user_id WordPress user ID.
     *
     * @return void
     */
    private static function release(int $wp_user_id): void
    {
        if (!isset(self::$held[$wp_user_id])) {
            return;
        }

        if (--self::$held[$wp_user_id][1] > 0) {
            return;
        }

        $value = self::$held[$wp_user_id][0];
        unset(self::$held[$wp_user_id]);

        global $wpdb;

        $table      = self::table();
        $suppressed = $wpdb->suppress_errors(true);

        // Matching on our own value: a lock that outlived its lease and was taken over is no longer ours
        // to delete. Retried like acquire(): a row left behind would block the user until its lease ends.
        for ($attempt = 1; $attempt <= self::MAX_DB_ERRORS; $attempt++) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $deleted = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM `{$table}` WHERE `option_name` = %s AND `option_value` = %s",
                    self::option_name($wp_user_id),
                    $value
                )
            );
            if (false !== $deleted) {
                break;
            }

            usleep(self::RETRY_INTERVAL);
        }

        $wpdb->suppress_errors($suppressed);
    }

    /**
     * The first site's options table: one table for every site of every
     * network, like the users whose passwords the lock protects.
     *
     * @return string
     */
    private static function table(): string
    {
        global $wpdb;

        return $wpdb->base_prefix . 'options';
    }

    /**
     * Option name of the user's lock row.
     *
     * @param int $wp_user_id WordPress user ID.
     *
     * @return string
     */
    private static function option_name(int $wp_user_id): string
    {
        return '_wap_client_app_password_lock_' . $wp_user_id;
    }
}
