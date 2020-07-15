<?php

namespace WP_Rocket\Engine\DOM\Transformer;

trait Normalizer {

	/**
	 * Working copy of the HTML content.
	 *
	 * @var string
	 */
	private $normalizer_html;

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
	private $normalizer_html_start;

	/**
	 * The closing </head> tag and anything after it.
	 *
	 * @var string
	 */
	private $normalizer_html_end;

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
	 * HTML's character encoding.
	 *
	 * @var string
	 */
	protected $normalizer_encoding;

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
		$this->reset_normalizer();

		$this->normalizer_html = $html;

		$this->remove_doctype();
		$this->remove_opening_html_tag();
		$this->remove_ending_html_tag();

		// Detect the </head> and/or <body> tags.
		$this->head_closing_tag = $this->detect_tag( '/<\/head(?>\s+[^>]*)?>/is' );
		$this->body_opening_tag = $this->detect_tag( '/<body(?>\s+[^>]*)?>/is' );

		// If both the </head> and <body> tags are missing, we can't normalize.
		if ( false === $this->head_closing_tag && false === $this->body_opening_tag ) {
			$this->reset_normalizer();

			return false;
		}

		$this->add_missing_head_body_tags();

		$this->reinsert_html_tags();
		$this->reinsert_doctype();

		$html = $this->normalizer_html;

		$this->reset_normalizer();

		return $html;
	}

	/**
	 * Reset state.
	 *
	 * @since 3.2.6.1
	 */
	protected function reset_normalizer() {
		$this->normalizer_html       = '';
		$this->doctype               = '<!DOCTYPE html>';
		$this->normalizer_html_start = '<html>';
		$this->normalizer_html_end   = '</html>';
		$this->head_closing_tag      = false;
		$this->body_opening_tag      = false;
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

		if ( ! preg_match( $pattern, $this->normalizer_html, $matches ) ) {
			return;
		}

		if ( isset( $matches['doctype'] ) ) {
			$this->doctype = $matches['doctype'];
		}
		$this->normalizer_html = preg_replace( $pattern, '', $this->normalizer_html, 1 );
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

		if ( ! preg_match( $pattern, $this->normalizer_html, $matches ) ) {
			return;
		}

		if ( isset( $matches['html_start'] ) ) {
			$this->normalizer_html_start = $matches['html_start'];
		}
		$this->normalizer_html = preg_replace( $pattern, '', $this->normalizer_html, 1 );
	}

	/**
	 * Capture and removes the closing </html> tag, if exists.
	 *
	 * @since 3.6.2.1
	 */
	private function remove_ending_html_tag() {
		$pattern = '/(?<html_end><\/html(?>\s+[^>]*)?>.*)$/is';

		if ( ! preg_match( $pattern, $this->normalizer_html, $matches ) ) {
			return;
		}

		if ( isset( $matches['html_end'] ) ) {
			$this->normalizer_html_end = $matches['html_end'];
		}
		$this->normalizer_html = preg_replace( $pattern, '', $this->normalizer_html, 1 );
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
		if ( preg_match( '/^[^<]*(?><!--.*?-->\s*)*(?><head(?>\s+[^>]*)?>)/is', $this->normalizer_html, $matches ) ) {
			return;
		}

		$this->normalizer_html = "<head>{$this->normalizer_html}";
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
		if ( preg_match( '/(?><\/body(?>\s+[^>]*)?>.*)$/is', $this->normalizer_html, $matches ) ) {
			return;
		}

		$this->normalizer_html .= '</body>';
	}

	/**
	 * Reinserts the <html> and </html> tags.
	 *
	 * @since 3.6.2.1
	 */
	private function reinsert_html_tags() {
		$this->normalizer_html = "{$this->normalizer_html_start}{$this->normalizer_html}{$this->normalizer_html_end}";
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

		$this->normalizer_html = "{$this->doctype}{$this->normalizer_html}";
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
		if ( ! preg_match( $pattern, $this->normalizer_html, $matches ) ) {
			return false;
		}

		$tag               = $matches[0];
		$starting_position = mb_strpos( $this->normalizer_html, $tag, 0, $this->normalizer_encoding );

		return [
			'tag'               => $tag,
			'starting_position' => $starting_position,
			'ending_position'   => $starting_position + mb_strlen( $tag, $this->normalizer_encoding ),
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
		$before = mb_substr( $this->normalizer_html, 0, $insertion, $this->normalizer_encoding );
		$after  = mb_substr(
			$this->normalizer_html,
			$insertion,
			mb_strlen( $this->normalizer_html, $this->normalizer_encoding ),
			$this->normalizer_encoding
		);

		$this->normalizer_html = "{$before}{$tag}{$after}";
	}
}
