<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides;

/**
 * Overrides how a single recommendation is presented, based on the state of the site.
 *
 * Implementations are composed by the container only: the recommendations list is intentionally not exposed to filters.
 */
interface Override_Interface {

	/**
	 * Gets the slug of the recommendation this override handles.
	 *
	 * @return string
	 */
	public function get_option_slug(): string;

	/**
	 * Applies the override to a formatted recommendation.
	 *
	 * May change 'title' and 'description', and set the button through 'cta_label' (label, defaults to "Activate"),
	 * 'cta_url' (replaces the section link; when empty a <button> is rendered), 'cta_class' (extra CSS classes)
	 * and 'cta_attributes' (extra HTML attributes, as attribute name => value).
	 *
	 * @param array $recommendation Formatted recommendation.
	 * @return array
	 */
	public function apply( array $recommendation ): array;
}
