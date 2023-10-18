<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Optimization\RegexTrait;

class Controller implements ContextInterface {
	use RegexTrait;

	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Queries instance
	 *
	 * @var ATFQuery
	 */
	private $query;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options instance.
	 * @param ATFQuery     $query Queries instance.
	 */
	public function __construct( Options_Data $options, ATFQuery $query ) {
		$this->options = $options;
		$this->query   = $query;
	}

	/**
	 * Determine if the action is allowed.
	 *
	 * @param array $data Data to pass to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		return apply_filters( 'rocket_above_the_fold_optimization', true );
	}

	/**
	 * Optimize the LCP image
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function lcp( $html ): string {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		return $html;
	}

	/**
	 * Add above the fold images to lazyload exclusions
	 *
	 * @param array $exclusions Array of excluded patterns.
	 *
	 * @return array
	 */
	public function add_exclusions( $exclusions ): array {
		if ( ! $this->is_allowed() ) {
			return $exclusions;
		}

		global $wp;

		$url = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );

		$row = $this->query->get_row( $url, $this->is_mobile() );

		return $exclusions;
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return boolean
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&& $this->options->get( 'do_caching_mobile_files', 0 )
			&& wp_is_mobile();
	}
}
