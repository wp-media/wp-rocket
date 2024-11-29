<?php

$buffer = true;

/**
 * Filters the buffer content for performance hints.
 *
 * @since 3.17
 *
 * @param boolean $buffer Page HTML content.
 */
wpm_apply_filters_typed( 'string', 'rocket_performance_hints_buffer', $buffer );

