<?php

namespace WP_Rocket\Engine\DOM\Transformer;

use DOMElement;
use DOMNode;
use DOMText;
use DOMComment;
use WP_Rocket\Engine\DOM\Element;
use WP_Rocket\Engine\DOM\HTMLDocument;

/**
 * <head> transformer:
 *      1. Replaces each non-allowed content/node in <head> with a placeholder.
 *      2. Restores the original non-allowed content/nodes by replacing each placeholder.
 *
 * phpcs:disable Squiz.PHP.CommentedOutCode.Found -- Comments are by design.
 */
trait Head {

	/**
	 * Allowed HTML <head> tags definition.
	 *
	 * Internal constant implementation.
	 *
	 * @since 3.6.2.1
	 *
	 * @link  https://www.w3.org/TR/html5/document-metadata.html
	 * @link  https://developer.mozilla.org/en-US/docs/Web/HTML/Element/head
	 *
	 * @return string[]
	 */
	public static function ALLOWED_HEAD_TAGS() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		static $tags = null;

		if ( null === $tags ) {
			$tags = [
				'title',
				'base',
				'link',
				'style',
				'meta',
				'noscript',
				'script',
				'template',
			];
		}

		return $tags;
	}

	/**
	 * Nodes removed from the <head> to be restored after DOM save.
	 *
	 * @var string[]
	 */
	private $head_nodes;

	/**
	 * Increment number for <!--tag:N-->.
	 *
	 * @var integer
	 */
	private $head_nodes_increment = 0;

	/**
	 * HTML character encoding.
	 *
	 * @var string
	 */
	protected $head_encoding;

	/**
	 * Replaces nodes in the <head>.
	 *
	 * @since  3.6.2.1
	 *
	 * @param string $html HTML string to transform.
	 *
	 * @return string
	 */
	protected function replace_head_nodes( $html ) {
		$this->reset_head_nodes_state();

		$head_html = $this->get_head_content( $html );
		if ( empty( $head_html ) ) {
			return $html;
		}

		$new_head = $this->replace_nonallowed_head_nodes( $head_html );
		if ( empty( $new_head ) ) {
			return $html;
		}

		return str_replace( $head_html, $new_head, $html );
	}

	/**
	 * Restores the previously extracted nodes back into the <head>.
	 *
	 * @since  3.6.2.1
	 *
	 * @param string $html HTML string to restore.
	 *
	 * @return string HTML with nodes restored in the <head>, if applicable.
	 */
	protected function restore_head_nodes( $html ) {
		if ( empty( $this->head_nodes ) ) {
			return $html;
		}

		// Get the position of the end of the <head></head> element.
		$pos = mb_strpos( $html, '</head>', 0, $this->head_encoding ) + 7;
		if ( empty( $pos ) ) {
			return $html;
		}

		$head = str_replace(
			array_keys( $this->head_nodes ),
			$this->head_nodes,
			mb_substr( $html, 0, $pos, $this->head_encoding )
		);

		return $head . mb_substr( $html, $pos, mb_strlen( $html, $this->head_encoding ), $this->head_encoding );
	}

	/**
	 * Resets state.
	 *
	 * @since 3.6.2.1
	 */
	protected function reset_head_nodes_state() {
		$this->head_nodes           = null;
		$this->head_nodes_increment = 0;
	}

	/**
	 * Gets all of the content withing the <head> node.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html HTML to transform.
	 *
	 * @return string <head> content.
	 */
	private function get_head_content( $html ) {
		preg_match( '#<head[^>]*>(.*?)<\/head>#is', $html, $matches );
		if ( empty( $matches[1] ) ) {
			return '';
		}

		return $matches[1];
	}

	/**
	 * Replace each non-allowed head node with a placeholder.
	 *
	 * How does it work?
	 * The content in the <head> is moved into the <body>. Why? This technique does the following:
	 *      1. Preserves invalid nodes/content.
	 *      2. Preserve node order.
	 *
	 * @since 3.6.2.1
	 *
	 * @uses  SelfClosing::replace_self_closing()
	 *
	 * @param string $head_html <head> HTML to transform.
	 *
	 * @return string <head> HTML with only allowed nodes.
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	 */
	private function replace_nonallowed_head_nodes( $head_html ) {
		list( $dom, $body ) = $this->create_traversal_head_nodes( $head_html );
		if ( false === $dom || false === $body ) {
			return $head_html;
		}

		// Traverse to find any non-allowed nodes.
		$node = $body->firstChild;
		while ( $node ) {
			$next_sibling = $node->nextSibling;
			if ( ! $this->is_allowed_head_node( $node ) ) {
				$comment = $this->map_head_node_placeholder( $node, $dom );

				$body->insertBefore( $dom->createComment( $comment ), $node );

				$body->removeChild( $node );
			}
			$node = $next_sibling;
		}

		// No nodes replaced.
		if ( 0 === $this->head_nodes_increment ) {
			return;
		}

		$html = $dom->saveHTML( $body );

		unset( $dom, $body );

		return $this->get_transformed_head_html( $html );
	}

	/**
	 * Creates a DOM to traverse the <head> nodes.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $head_html <head> HTML to transform.
	 *
	 * @return array|bool
	 */
	private function create_traversal_head_nodes( $head_html ) {
		$html = '<head>' . Element::META_CHARSET . "</head><body>{$head_html}</body>";
		$dom  = HTMLDocument::from_html( $html, '1.0', $this->head_encoding, false );
		if ( false === $dom ) {
			return false;
		}

		$elements = $dom->getElementsByTagName( 'body' );
		if ( 0 === $elements->length ) {
			return false;
		}

		return [ $dom, $elements->item( 0 ) ];
	}

	/**
	 * Determine whether a node can be in the head.
	 *
	 * @since 3.6.2.1
	 *
	 * @param DOMNode $node Node.
	 *
	 * @return bool Whether valid head node.
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	 */
	private function is_allowed_head_node( DOMNode $node ) {
		if ( $node instanceof DOMComment ) {
			return true;
		}

		if ( $node instanceof DOMText ) {
			// Text nodes with empty spaces are okay.
			return (bool) preg_match( '/^\s*$/', $node->nodeValue );
		}

		if ( $node instanceof DOMElement ) {
			/**
			 * When Libxml library is < 2.8.0, replace <noscript> elements in the <head> with the placeholder.
			 *
			 * Why?
			 *
			 * The Libxml library < 2.8.0 has a bug in it where <noscript> elements cause the <head> to prematurely close.
			 * When that happens, the first <noscript> and the remaining <head> elements are moved into the <body> and the
			 * <body> element does not have all of the attributes.
			 */
			if ( 'noscript' === $node->nodeName ) {
				return version_compare( HTMLDocument::get_libxml_version(), '2.8', '>=' );
			}

			return in_array( $node->nodeName, self::ALLOWED_HEAD_TAGS(), true );
		}

		return false;
	}

	/**
	 * Maps placeholder => original HTML for the restorer and returns the comment to insert.
	 *
	 * @since 3.6.2.1
	 *
	 * @param DOMNode      $node instance of the node.
	 * @param HTMLDocument $dom  Instance of the DOM.
	 *
	 * @return string comment to insert in place of this node.
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	 */
	private function map_head_node_placeholder( $node, $dom ) {
		// Construct the placeholder: <!--tag:N-->.
		$comment = sprintf( '%s:%d', $node->nodeName, $this->head_nodes_increment );

		$placeholder                      = sprintf( '<!--%s-->', $comment );
		$this->head_nodes[ $placeholder ] = $node instanceof DOMText
			? trim( $node->nodeValue )
			: trim( $dom->saveHTML( $node ) );
		$this->head_nodes_increment++;

		return $comment;
	}

	/**
	 * Gets the transformed head content by stripping the body tags.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html HTML to transform.
	 *
	 * @return string
	 */
	private function get_transformed_head_html( $html ) {
		$html = str_replace( [ '<body>', '</body>' ], '', $html );

		return trim( $html );
	}
}
