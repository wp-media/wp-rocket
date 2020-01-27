<?php
namespace WP_Rocket\Logger;

use Monolog\Formatter\HtmlFormatter;

defined( 'ABSPATH' ) || exit;

/**
 * Class used to format log records as HTML.
 *
 * @since  3.2
 * @author Grégory Viguier
 */
class HTML_Formatter extends HtmlFormatter {

	/**
	 * Formats a log record.
	 * Compared to the parent method, it removes the "channel" row.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $record A record to format.
	 * @return mixed         The formatted record.
	 */
	public function format( array $record ) {
		$output  = $this->addTitle( $record['level_name'], $record['level'] );
		$output .= '<table cellspacing="1" width="100%" class="monolog-output">';

		$output .= $this->addRow( 'Message', (string) $record['message'] );
		$output .= $this->addRow( 'Time', $record['datetime']->format( $this->dateFormat ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		if ( $record['context'] ) {
			$embedded_table = '<table cellspacing="1" width="100%">';

			foreach ( $record['context'] as $key => $value ) {
				$embedded_table .= $this->addRow( $key, $this->convertToString( $value ) );
			}

			$embedded_table .= '</table>';
			$output         .= $this->addRow( 'Context', $embedded_table, false );
		}

		if ( $record['extra'] ) {
			$embedded_table = '<table cellspacing="1" width="100%">';

			foreach ( $record['extra'] as $key => $value ) {
				$embedded_table .= $this->addRow( $key, $this->convertToString( $value ) );
			}

			$embedded_table .= '</table>';
			$output         .= $this->addRow( 'Extra', $embedded_table, false );
		}

		return $output . '</table>';
	}
}
