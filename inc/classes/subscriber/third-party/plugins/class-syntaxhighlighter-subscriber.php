<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for SyntaxHighlighter plugin
 *
 * @since 3.3.1
 * @author Remy Perona
 */
class SyntaxHighlighter_Subscriber implements Subscriber_Interface {
    /**
     * @inheritDoc
     */
    public static function get_subscribed_events() {
        if ( ! class_exists( 'SyntaxHighlighter' ) ) {
            return [];
        }

        return [
            'rocket_exclude_defer_js' => 'exclude_defer_js_syntaxhighlighter_scripts',
            'rocket_exclude_js'       => 'exclude_minify_js_syntaxhighlighter_scripts',
        ];
    }

    /**
     * Adds SyntaxHighlighter scripts to defer JS exclusion
     *
     * @since 3.3.1
     * @author Remy Perona
     *
     * @param array $excluded_scripts Array of scripts to exclude
     * @return array
     */
    public function exclude_defer_js_syntaxhighlighter_scripts( $excluded_scripts ) {
        return array_merge( 
            $excluded_scripts,
            [
                'syntaxhighlighter/syntaxhighlighter3/scripts/(.*).js',
                'syntaxhighlighter/syntaxhighlighter2/scripts/(.*).js',
            ]
        );
    }

    /**
     * Adds SyntaxHighlighter scripts to minify/combine JS exclusion
     *
     * @since 3.3.1
     * @author Remy Perona
     *
     * @param array $excluded_scripts Array of scripts to exclude
     * @return array
     */
    public function exclude_minify_js_syntaxhighlighter_scripts( $excluded_scripts ) {
        return array_merge( 
            $excluded_scripts,
            [
                rocket_clean_exclude_file( plugins_url( 'syntaxhighlighter/syntaxhighlighter3/scripts/(.*).js' ) ),
                rocket_clean_exclude_file( plugins_url( 'syntaxhighlighter/syntaxhighlighter2/scripts/(.*).js' ) ),
            ]
        );
    }
}
