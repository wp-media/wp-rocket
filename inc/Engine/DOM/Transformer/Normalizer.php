<?php

namespace WP_Rocket\Engine\DOM\Transformer;

trait Normalizer {

	/**
	 * HTML content.
	 *
	 * @var string
	 */
	private $html;

	/**
	 * The <DOCTYPE>.
	 *
	 * @var string
	 */
	private $doctype;

	/**
	 * <html> opening tag and anything before it.
	 *
	 * @var string
	 */
	private $html_start;

	/**
	 * The closing </head> tag and anything after it.
	 *
	 * @var string
	 */
	private $html_end;

	/**
	 * Information about the </head> tag.
	 *
	 * @var array|bool
	 */
	private $head_closing_tag;

	/**
	 * Information about the <body> tag.
	 *
	 * @var array|bool
	 */
	private $body_opening_tag;

	/**
	 * Normalize the document structure.
	 *
	 * This makes sure the document adheres to the general structure:
	 *   ```
	 *   <!DOCTYPE html>
	 *   <html>
	 *     <head>
	 *     </head>
	 *     <body>
	 *     </body>
	 *   </html>
	 *   ```
	 *
	 * @param string $html HTML structure to normalize.
	 *
	 * @return string Normalized HTML.
	 */
	protected function normalize_structure( $html ) {
		$this->reset();

		$this->html = $html;

		$this->remove_doctype();
		$this->remove_opening_html_tag();
		$this->remove_ending_html_tag();

		// Detect the </head> and/or <body> tags.
		$this->head_closing_tag = $this->detect_tag( '/<\/head(?>\s+[^>]*)?>/is' );
		$this->body_opening_tag = $this->detect_tag( '/<body(?>\s+[^>]*)?>/is' );

		// If both the </head> and <body> tags are missing, we can't normalize.
		if ( false === $this->head_closing_tag && false === $this->body_opening_tag ) {
			$this->reset();

			// @todo Need a way to bail out of the DOM processing.
			return false;
		}

		$this->add_missing_head_body_tags();

		$this->reinsert_html_tags();
		$this->reinsert_doctype();

		$html = $this->html;

		$this->reset();

		return $html;
	}

	/**
	 * Reset the state.
	 *
	 * @since 3.2.6.1
	 */
	protected function reset() {
		$this->html             = '';
		$this->doctype          = '<!DOCTYPE html>';
		$this->html_start       = '<html>';
		$this->html_end         = '</html>';
		$this->head_closing_tag = false;
		$this->body_opening_tag = false;
	}

	/**
	 * Removes the original doctype, if exists.
	 *
	 * @since 3.6.2.1
	 *
	 * @return void
	 */
	private function remove_doctype() {
		$pattern = '/^(?<doctype>[^<]*(?>\s*<!--.*?-->\s*)*<!doctype(?>\s+[^>]+)?>)/is';

		if ( ! preg_match( $pattern, $this->html, $matches ) ) {
			return;
		}

		if ( isset( $matches['doctype'] ) ) {
			$this->doctype = $matches['doctype'];
		}
		$this->html = preg_replace( $pattern, '', $this->html, 1 );
	}

	/**
	 * Capture and removes the opening <html> tag, if exists.
	 *
	 * @since 3.6.2.1
	 *
	 * @return void
	 */
	private function remove_opening_html_tag() {
		$pattern = '/^(?<html_start>[^<]*(?>\s*<!--.*?-->\s*)*<html(?>\s+[^>]*)?>)/is';

		if ( ! preg_match( $pattern, $this->html, $matches ) ) {
			return;
		}

		if ( isset( $matches['html_start'] ) ) {
			$this->html_start = $matches['html_start'];
		}
		$this->html = preg_replace( $pattern, '', $this->html, 1 );
	}

	/**
	 * Capture and removes the closing </html> tag, if exists.
	 *
	 * @since 3.6.2.1
	 */
	private function remove_ending_html_tag() {
		$pattern = '/(?<html_end><\/html(?>\s+[^>]*)?>.*)$/is';

		if ( ! preg_match( $pattern, $this->html, $matches ) ) {
			return;
		}

		if ( isset( $matches['html_end'] ) ) {
			$this->html_end = $matches['html_end'];
		}
		$this->html = preg_replace( $pattern, '', $this->html, 1 );
	}

	/**
	 * Adds missing <head> and/or <body> tags.
	 *
	 * @since 3.6.2.1
	 */
	private function add_missing_head_body_tags() {
		if ( false === $this->head_closing_tag ) {
			$this->add_head_closing_tag();
		} elseif ( false === $this->body_opening_tag ) {
			$this->add_body_opening_tag();
		}

		$this->add_head_opening_tag();
		$this->add_body_closing_tag();
	}

	/**
	 * Adds the missing <head> opening tag.
	 *
	 * @since 3.6.2.1
	 *
	 * @return void
	 */
	private function add_head_opening_tag() {
		if ( preg_match( '/^[^<]*(?><!--.*?-->\s*)*(?><head(?>\s+[^>]*)?>)/is', $this->html, $matches ) ) {
			return;
		}

		$this->html = "<head>{$this->html}";
	}

	/**
	 * Adds the missing </head> closing tag.
	 *
	 * @since 3.6.2.1
	 */
	private function add_head_closing_tag() {
		$this->insert_tag( '</head>', $this->body_opening_tag['starting_position'] );
	}

	/**
	 * Adds the missing <body> opening tag.
	 *
	 * @since 3.6.2.1
	 */
	private function add_body_opening_tag() {
		$this->insert_tag( '<body>', $this->head_closing_tag['ending_position'] );
	}

	/**
	 * Adds the closing </body> tag, if missing.
	 *
	 * @since 3.6.2.1
	 *
	 * @return void
	 */
	private function add_body_closing_tag() {
		if ( preg_match( '/(?><\/body(?>\s+[^>]*)?>.*)$/is', $this->html, $matches ) ) {
			return;
		}

		$this->html .= '</body>';
	}

	/**
	 * Reinserts the <html> and </html> tags.
	 *
	 * @since 3.6.2.1
	 */
	private function reinsert_html_tags() {
		$this->html = "{$this->html_start}{$this->html}{$this->html_end}";
	}

	/**
	 * Reinserts a standard doctype (while preserving any potentially leading comments).
	 *
	 * @since 3.6.2.1
	 */
	private function reinsert_doctype() {
		$this->doctype = str_ireplace(
			' PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"',
			'',
			$this->doctype
		);

		$this->html = "{$this->doctype}{$this->html}";
	}

	/**
	 * Detect the tag. When found, populate array with information about the tag.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $pattern regex pattern for this tag.
	 *
	 * @return array|bool
	 */
	protected function detect_tag( $pattern ) {
		if ( ! preg_match( $pattern, $this->html, $matches ) ) {
			return false;
		}

		$tag               = $matches[0];
		$starting_position = mb_strpos( $this->html, $tag );

		return [
			'tag'               => $tag,
			'starting_position' => $starting_position,
			'ending_position'   => $starting_position + mb_strlen( $tag ),
		];
	}

	/**
	 * Inserts the given tag into the HTML at the given insertion point.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $tag       Tag to insert.
	 * @param int    $insertion Position to insert it.
	 */
	protected function insert_tag( $tag, $insertion ) {
		$this->html = mb_substr( $this->html, 0, $insertion ) . $tag . mb_substr( $this->html, $insertion );
	}
}
