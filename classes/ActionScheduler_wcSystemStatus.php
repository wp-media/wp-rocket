<?php

/**
 * Class ActionScheduler_wcSystemStatus
 */
class ActionScheduler_wcSystemStatus {

	/**
	 * The active data stores
	 *
	 * @var ActionScheduler_Store
	 */
	protected $store;

	function __construct( $store ) {
		$this->store = $store;
	}

	public function print() {
		$counts = $this->store->action_counts();
		$labels = $this->store->get_status_labels();

		$oldest_and_newest = array();

		foreach ( array_keys( $labels ) as $status ) {
			$oldest_and_newest[ $status ] = array(
				'oldest' => '&ndash;',
				'newest' => '&ndash;',
			);

			if ( 'in-progress' === $status ) {
				continue;
			}

			$oldest = $this->store->query_actions( array(
				'claimed' => false,
				'status' => $status,
				'per_page' => 1,
			) );

			if ( !empty( $oldest ) ) {
				$date_object = $this->store->get_date_gmt( $oldest[0] );
				$oldest_and_newest[ $status ]['oldest'] = $date_object->format( 'Y-m-d H:i:s' );
			}

			if ( 2 > $counts[ $status ] ) {
				continue;
			}

			$newest = $this->store->query_actions( array(
				'claimed' => false,
				'status' => $status,
				'per_page' => 1,
				'order' => 'DESC',
			) );

			if ( !empty( $newest ) ) {
				$date_object = $this->store->get_date_gmt( $newest[0] );
				$oldest_and_newest[ $status ]['newest'] = $date_object->format( 'Y-m-d H:i:s' );
			}
		}
		?>

		<table class="wc_status_table widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="5" data-export-label="Action Scheduler"><h2><?php esc_html_e( 'Action Scheduler', 'action-scheduler' ); ?><?php echo wc_help_tip( esc_html__( 'This section shows scheduled action counts.', 'action-scheduler' ) ); ?></h2></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $counts as $status => $count ) {
					printf(
						'<tr><td>%s</td><td class="help">&nbsp;</td><td>%s</td><td>%s</td><td>%s</td></tr>',
						esc_html( $labels[ $status ] ),
						number_format_i18n( $count ),
						$oldest_and_newest[ $status ]['oldest'],
						$oldest_and_newest[ $status ]['newest']
					);
				}
				?>
			</tbody>
		</table>

		<?php
	}

}
