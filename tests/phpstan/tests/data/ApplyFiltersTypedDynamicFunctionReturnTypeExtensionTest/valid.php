<?php
$buffer = 'Hello world';
/**
 * Filters the buffer content for performance hints.
 *
 * @since 3.17
 *
 * @param string $buffer Page HTML content.
 */
wpm_apply_filters_typed( 'string', 'rocket_performance_hints_buffer', $buffer );
