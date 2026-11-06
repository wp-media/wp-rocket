<?php
declare(strict_types=1);

/**
 * Handles lazyloading of images
 *
 * @package WP_Rocket\Dependencies\RocketLazyload
 */

namespace WP_Rocket\Dependencies\RocketLazyload;

/**
 * A class to provide the methods needed to lazyload images in WP Rocket and Lazyload by WP Rocket
 */
class Image {

	/**
	 * Finds the images to be lazyloaded and call the callback method to replace them.
	 *
	 * @param string $html   Original HTML.
	 * @param string $buffer Content to parse.
	 * @param bool   $use_native Use native lazyload.
	 * @return string
	 */
	public function lazyloadImages( $html, $buffer, $use_native = true ) {
		if ( ! preg_match_all( '#<img(?<atts>\s.+)\s?/?>#iUs', $buffer, $images, PREG_SET_ORDER ) ) {
			return $html;
		}

		$images = array_unique( $images, SORT_REGULAR );

		foreach ( $images as $image ) {
			$original_image = $image;
			$image          = $this->canLazyload( $image );

			if ( ! $image ) {
				$image_no_lazy = preg_replace( '/loading=["\']lazy["\']/i', '', $original_image );

				if ( null === $image_no_lazy ) {
					continue;
				}

				$html = str_replace( $original_image, $image_no_lazy, $html );

				continue;
			}

			$image_lazyload = $this->replaceImage( $image, $use_native );

			if ( ! $use_native && $this->noscriptEnabled() ) {
				$image_lazyload .= $this->noscript( $image[0] );
			}

			$html = str_replace( $image[0], $image_lazyload, $html );

			unset( $image_lazyload );
		}

		return $html;
	}

	/**
	 * Applies lazyload on background images defined in style attributes
	 *
	 * @param string $html   Original HTML.
	 * @param string $buffer Content to parse.
	 * @return string
	 */
	public function lazyloadBackgroundImages( $html, $buffer ) {
		// Candidate opening tags for the allowed tag names. The quoted-value
		// alternatives let a `>` character inside an attribute's value (e.g. raw
		// markup stored in an attribute) be skipped over instead of
		// prematurely ending the tag match. As in browsers, a quote only opens
		// a value right after `=`: a stray quote anywhere else is a plain
		// character, so it can't pair with a quote in a later tag and swallow
		// everything in between. Quantifiers are possessive (`*+`) so a long,
		// quote-free value can't be re-walked by backtracking once matched.
		if ( ! preg_match_all( '#<(?<tag>div|figure|section|aside|span|li|a)\b(?:[^>=]++|=\s*+"[^"]*+"|=\s*+\'[^\']*+\'|=)*+>#is', $buffer, $elements, PREG_SET_ORDER ) ) {
			return $html;
		}

		foreach ( $elements as $element ) {
			$style = $this->findRealAttribute( $element[0], 'style' );

			if ( ! $style ) {
				continue;
			}

			// Only strip the outer quote characters themselves here, without
			// trimming whitespace: the value must stay byte-for-byte identical
			// to what is inside the original tag, so the background-image match
			// found against it can still be located and removed from that tag.
			$element['styles'] = $this->stripOuterQuoteChars( $style['value'] );

			$attrs_no_style = str_replace( $style['attribute'], '', $element[0] );

			if ( $this->isExcluded( $attrs_no_style, $this->getExcludedAttributes() ) ) {
				continue;
			}

			/**
			 * Regex to detect bg images inside CSS.
			 *
			 * @param string $regex regex to detect.
			 * @return string
			 */
			$regex = apply_filters( 'rocket_lazyload_bg_images_regex', 'background-image\s*:\s*(?<attr>\s*url\s*\((?<url>[^)]+)\))\s*;?' );

			if ( @preg_match( "#$regex#is", '' ) === false ) {// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$regex = 'background-image\s*:\s*(?<attr>\s*url\s*\((?<url>[^)]+)\))\s*;?';
			}

			if ( ! preg_match( "#$regex#is", $element['styles'], $url ) ) {
				continue;
			}

			if ( preg_match( '#data:image#is', $url['url'], $img ) ) {
				continue;
			}
			$url['url'] = esc_url(
				trim(
					wp_strip_all_tags(
						html_entity_decode(
							$url['url'],
							ENT_QUOTES | ENT_HTML5
						)
					),
					'\'" '
				)
			);

			if ( $this->isExcluded( $url['url'], $this->getExcludedSrc() ) ) {
				continue;
			}

			$lazy_bg = $this->addLazyClass( $element[0] );
			$lazy_bg = str_replace( $url[0], '', $lazy_bg );
			$lazy_bg = str_replace( '<' . $element['tag'], '<' . $element['tag'] . ' data-bg="' . esc_attr( $url['url'] ) . '"', $lazy_bg );

			$html = str_replace( $element[0], $lazy_bg, $html );
			unset( $lazy_bg );
		}

		return $html;
	}

	/**
	 * Add the identifier class to the element
	 *
	 * @param string $element Element to add the class to.
	 * @return string
	 */
	private function addLazyClass( $element ) {
		$class = $this->getClasses( $element );

		if ( ! $class ) {
			$result = preg_replace( '#<(img|div|figure|section|aside|li|span|a)([^>]*)>#is', '<\1 class="rocket-lazyload"\2>', $element );

			if ( ! $result ) {
				return $element;
			}

			return $result;
		}

		if ( empty( $class['attribute'] ) || empty( $class['classes'] ) ) {
			return str_replace( $class['attribute'], 'class="rocket-lazyload"', $element );
		}

		$quotes  = $this->getAttributeQuotes( $class['classes'] );
		$classes = $this->trimOuterQuotes( $class['classes'], $quotes );

		if ( empty( $classes ) ) {
			return str_replace( $class['attribute'], 'class="rocket-lazyload"', $element );
		}

		$classes .= ' rocket-lazyload';

		return str_replace(
			$class['attribute'],
			'class=' . $this->normalizeClasses( $classes, $quotes ),
			$element
		);
	}

	/**
	 * Gets the attribute value's outer quotation mark, if one exists, i.e. " or '.
	 *
	 * @param string $attribute_value The target attribute's value.
	 *
	 * @return false|string quotation character; else false when no quotation mark.
	 */
	private function getAttributeQuotes( $attribute_value ) {
		$attribute_value = trim( $attribute_value );
		$first_char      = $attribute_value[0];

		if ( '"' === $first_char || "'" === $first_char ) {
			return $first_char;
		}

		return false;
	}

	/**
	 * Removes a matching pair of leading/trailing quote characters from an
	 * attribute value, without trimming any whitespace.
	 *
	 * Unlike trimOuterQuotes(), this preserves the value byte-for-byte
	 * (aside from the two quote characters themselves), which matters when the
	 * result must still be located as a substring of the original, untouched
	 * tag text.
	 *
	 * @param string $value Attribute value, as returned by findRealAttribute().
	 *
	 * @return string
	 */
	private function stripOuterQuoteChars( $value ) {
		$length = strlen( $value );

		if ( $length < 2 ) {
			return $value;
		}

		$first = $value[0];
		$last  = $value[ $length - 1 ];

		if ( ( '"' === $first || "'" === $first ) && $first === $last ) {
			return substr( $value, 1, -1 );
		}

		return $value;
	}

	/**
	 * Gets the class attribute and values from the given element, if it exists.
	 *
	 * @param string $element Given HTML element to extract classes from.
	 *
	 * @return false|string[] {
	 *      @type string $attribute Class attribute and value, e.g. class="value"
	 *      @type string $classes   String of class attribute's value(s)
	 * }; else, false when no class attribute exists.
	 */
	private function getClasses( $element ) {
		$found = $this->findRealAttribute( $element, 'class' );

		if ( ! $found ) {
			return false;
		}

		return [
			'attribute' => $found['attribute'],
			'classes'   => $found['value'],
		];
	}

	/**
	 * Finds the first genuine, non-nested occurrence of the given attribute on an HTML tag string.
	 *
	 * Unlike a plain `name\s*=` search, this ignores any occurrence of that literal text found
	 * inside the still-open value of another attribute (e.g. a `class=` token nested inside a
	 * `title="..."` value), so only a real attribute on the tag itself is ever returned.
	 *
	 * The quote-state walk is carried across candidates via $pos/$open_quote instead of
	 * re-scanning the tag from byte 0 for every candidate: preg_match_all() returns matches
	 * in ascending offset order, so each byte of the tag only needs to be visited once in
	 * total, keeping this O(tag length) instead of O(candidates x tag length).
	 *
	 * @param string $tag  HTML tag string to search in, e.g. `<div class="a">`.
	 * @param string $name Attribute name to look for, e.g. `class` or `style`.
	 *
	 * @return false|array{attribute: string, value: string} The matched attribute text and its
	 *         (still-quoted, if applicable) value; false when no genuine attribute is found.
	 */
	private function findRealAttribute( $tag, $name ) {
		$pattern = '#(?<=\s)' . preg_quote( $name, '#' ) . '\s*=\s*(?<value>"[^"]*+"|\'[^\']*+\'|[^\s>]++)#is';

		if ( ! preg_match_all( $pattern, $tag, $matches, PREG_OFFSET_CAPTURE ) ) {
			return false;
		}

		$pos        = 0;
		$open_quote = null;

		foreach ( $matches[0] as $index => $match ) {
			$offset = $match[1];

			$this->advanceQuoteState( $tag, $pos, $offset, $open_quote );
			$pos = $offset;

			if ( null !== $open_quote ) {
				continue;
			}

			return [
				'attribute' => $match[0],
				'value'     => $matches['value'][ $index ][0],
			];
		}

		return false;
	}

	/**
	 * Advances a quote-state walk over $tag[$from..$end), updating $open_quote by reference.
	 *
	 * Tracks whichever of `"`/`'` is currently open (if any), the same way
	 * isOffsetInsideQuotedValue() used to from byte 0 on every call. Callers resume from
	 * their own cursor instead of restarting at 0, so a tag is only walked once in total.
	 *
	 * @param string      $tag        HTML tag string being walked.
	 * @param int         $from       Start offset to resume scanning from (inclusive).
	 * @param int         $end        End offset to scan up to (exclusive).
	 * @param string|null $open_quote Currently open quote character, if any; passed by
	 *                                reference and updated in place.
	 *
	 * @return void
	 */
	private function advanceQuoteState( $tag, $from, $end, &$open_quote ) {
		for ( $i = $from; $i < $end; $i++ ) {
			$char = $tag[ $i ];

			if ( null === $open_quote ) {
				if ( '"' === $char || "'" === $char ) {
					$open_quote = $char;
				}

				continue;
			}

			if ( $char === $open_quote ) {
				$open_quote = null;
			}
		}
	}

	/**
	 * Removes outer single or double quotations.
	 *
	 * @param string       $string String to strip quotes from.
	 * @param false|string $quotes The outer quotes to remove.
	 *
	 * @return string string without quotes.
	 */
	private function trimOuterQuotes( $string, $quotes ) {
		$string = trim( $string );

		if ( empty( $string ) ) {
			return '';
		}

		if ( empty( $quotes ) ) {
			return $string;
		}

		$string = ltrim( $string, $quotes );
		$string = rtrim( $string, $quotes );
		return trim( $string );
	}

	/**
	 * Normalizes the class attribute values to ensure well-formed.
	 *
	 * @param string      $classes String of class attribute value(s).
	 * @param string|bool $quotes  Optional. Quotation mark to wrap around the classes.
	 *
	 * @return string well-formed class attributes.
	 */
	private function normalizeClasses( $classes, $quotes = '"' ) {
		$array_of_classes = $this->stringToArray( $classes );
		$classes          = implode( ' ', $array_of_classes );

		if ( false === $quotes ) {
			$quotes = '"';
		}

		return $quotes . $classes . $quotes;
	}

	/**
	 * Converts the given string into an array of strings.
	 *
	 * Note:
	 *  1. Removes empties.
	 *  2. Trims each string.
	 *
	 * @param string           $string    The target string to convert.
	 * @param non-empty-string $delimiter Optional. Default: ' ' (one space).
	 *
	 * @return array<string> An array of trimmed strings.
	 */
	private function stringToArray( $string, $delimiter = ' ' ) {
		if ( empty( $string ) ) {
			return [];
		}

		$array = explode( $delimiter, $string );

		if ( ! $array ) {
			return [];
		}

		$array = array_map( 'trim', $array );

		// Remove empties.
		return array_filter( $array );
	}

	/**
	 * Applies lazyload on picture elements found in the HTML.
	 *
	 * @param string $html   Original HTML.
	 * @param string $buffer Content to parse.
	 * @return string
	 */
	public function lazyloadPictures( $html, $buffer ) {
		if ( ! preg_match_all( '#<picture(?:.*)?>(?<sources>.*)</picture>#iUs', $buffer, $pictures, PREG_SET_ORDER ) ) {
			return $html;
		}

		$pictures = array_unique( $pictures, SORT_REGULAR );
		$excluded = array_merge( $this->getExcludedAttributes(), $this->getExcludedSrc() );

		foreach ( $pictures as $picture ) {
			if ( $this->isExcluded( $picture[0], $excluded ) ) {
				if ( ! preg_match( '#<img(?<atts>\s.+)\s?/?>#iUs', $picture[0], $img ) ) {
					continue;
				}

				$img = $this->canLazyload( $img );

				if ( ! $img ) {
					continue;
				}

				$nolazy_picture = str_replace( '<img', '<img data-no-lazy=""', $picture[0] );
				$html           = str_replace( $picture[0], $nolazy_picture, $html );

				continue;
			}

			if ( preg_match_all( '#<source(?<atts>\s.+)>#iUs', $picture['sources'], $sources, PREG_SET_ORDER ) ) {
				$lazy_sources = 0;
				$sources      = array_unique( $sources, SORT_REGULAR );
				$lazy_picture = $picture[0];

				foreach ( $sources as $source ) {
					$lazyload_srcset = preg_replace( '/([\s"\'])srcset/i', '\1data-lazy-srcset', $source[0] );

					if ( ! $lazyload_srcset ) {
						continue;
					}

					$lazy_picture = str_replace( $source[0], $lazyload_srcset, $lazy_picture );

					unset( $lazyload_srcset );
					$lazy_sources++;
				}

				if ( 0 === $lazy_sources ) {
					continue;
				}

				$html = str_replace( $picture[0], $lazy_picture, $html );
			}

			if ( ! preg_match( '#<img(?<atts>\s.+)\s?/?>#iUs', $picture[0], $img ) ) {
				continue;
			}

			$img = $this->canLazyload( $img );

			if ( ! $img ) {
				continue;
			}

			$img_lazy = $this->replaceImage( $img, false );

			if ( $this->noscriptEnabled() ) {
				$img_lazy .= $this->noscript( $img[0] );
			}

			$safe_img = str_replace( '/', '\/', preg_quote( $img[0], '#' ) );

			$new_html = preg_replace( '#<noscript[^>]*>.*' . $safe_img . '.*<\/noscript>(*SKIP)(*FAIL)|' . $safe_img . '#i', $img_lazy, $html );

			if ( ! $new_html ) {
				continue;
			}

			$html = $new_html;

			unset( $img_lazy );
		}

		return $html;
	}

	/**
	 * Checks if the image can be lazyloaded
	 *
	 * @param array<string> $image Array of image data coming from Regex.
	 *
	 * @return false|array<string>
	 */
	private function canLazyload( $image ) {
		if ( $this->isExcluded( $image['atts'], $this->getExcludedAttributes() ) ) {
			return false;
		}

		// Given the previous regex pattern, $image['atts'] starts with a whitespace character.
		if ( ! preg_match( '@\ssrc\s*=\s*(\'|")(?<src>.*)\1@iUs', $image['atts'], $atts ) ) {
			return false;
		}

		$image['src'] = trim( $atts['src'] );

		if ( '' === $image['src'] ) {
			return false;
		}

		if ( $this->isExcluded( $image['src'], $this->getExcludedSrc() ) ) {
			return false;
		}

		return $image;
	}

	/**
	 * Checks if the provided string matches with the provided excluded patterns
	 *
	 * @param string        $string          String to check.
	 * @param array<string> $excluded_values Patterns to match against.
	 *
	 * @return bool
	 */
	public function isExcluded( $string, $excluded_values ) {
		if ( ! is_array( $excluded_values ) ) {
			$excluded_values = (array) $excluded_values;
		}

		$excluded_values = array_filter( $excluded_values );

		if ( empty( $excluded_values ) ) {
			return false;
		}

		foreach ( $excluded_values as $excluded_value ) {
			if ( strpos( $string, $excluded_value ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the list of excluded attributes
	 *
	 * @return array<string>
	 */
	public function getExcludedAttributes() {
		/**
		 * Filters the attributes used to prevent lazylad from being applied
		 *
		 * @since 1.0
		 *
		 * @param array $excluded_attributes An array of excluded attributes.
		 */
		return apply_filters(
			'rocket_lazyload_excluded_attributes',
			[
				'data-src=',
				'data-no-lazy=',
				'data-lazy-original=',
				'data-lazy-src=',
				'data-lazysrc=',
				'data-lazyload=',
				'data-bgposition=',
				'data-envira-src=',
				'fullurl=',
				'lazy-slider-img=',
				'data-srcset=',
				'class="ls-l',
				'class="ls-bg',
				'soliloquy-image',
				'loading="eager"',
				'swatch-img',
				'data-height-percentage',
				'data-large_image',
				'avia-bg-style-fixed',
				'data-skip-lazy',
				'skip-lazy',
				'image-compare__',
			]
		);
	}

	/**
	 * Returns the list of excluded src
	 *
	 * @return array<string>
	 */
	public function getExcludedSrc() {
		/**
		 * Filters the src used to prevent lazylad from being applied
		 *
		 * @since 1.0
		 *
		 * @param array $excluded_src An array of excluded src.
		 */
		return apply_filters(
			'rocket_lazyload_excluded_src',
			[
				'/wpcf7_captcha/',
				'timthumb.php?src',
				'woocommerce/assets/images/placeholder.png',
			]
		);
	}

	/**
	 * Replaces the original image by the lazyload one
	 *
	 * @param array<string> $image      Array of matches elements.
	 * @param bool          $use_native Use native lazyload.
	 *
	 * @return string
	 */
	private function replaceImage( $image, $use_native = true ) {
		if ( empty( $image ) ) {
			return '';
		}

		$native_pattern = '@\sloading\s*=\s*(\'|")(?:lazy|auto)\1@i';
		$image_lazyload = $image[0];

		if ( $use_native ) {
			if ( preg_match( $native_pattern, $image[0] ) ) {
				return $image[0];
			}

			$image_lazyload = str_replace( '<img', '<img loading="lazy"', $image_lazyload );
		} else {
			$width  = 0;
			$height = 0;

			if ( preg_match( '@[\s"\']width\s*=\s*(\'|")(?<width>.*)\1@iUs', $image['atts'], $atts ) ) {
				$width = absint( $atts['width'] );
			}

			if ( preg_match( '@[\s"\']height\s*=\s*(\'|")(?<height>.*)\1@iUs', $image['atts'], $atts ) ) {
				$height = absint( $atts['height'] );
			}

			// Only match src attributes with safe values (no spaces, quotes, or angle brackets).
			$placeholder_atts = preg_replace(
				'@\ssrc\s*=\s*(\'|")(?<src>[^\s"\'>]+)\1@iUs',
				' src="' . $this->getPlaceholder( $width, $height ) . '"',
				$image['atts']
			);

			$image_lazyload = str_replace(
				$image['atts'],
				$placeholder_atts . ' data-lazy-src="' . esc_url( $image['src'] ) . '"',
				$image_lazyload
			);

			if ( preg_match( $native_pattern, $image_lazyload ) ) {
				$result = preg_replace( $native_pattern, '', $image_lazyload );

				if ( is_string( $result ) ) {
					$image_lazyload = $result;
				}
			}
		}

		/**
		 * Filter the LazyLoad HTML output
		 *
		 * @since 1.0
		 *
		 * @param string $html Output that will be printed
		 */
		$image_lazyload = apply_filters( 'rocket_lazyload_html', $image_lazyload );

		return $image_lazyload;
	}

	/**
	 * Checks if the noscript tag is enabled
	 *
	 * @return mixed
	 */
	private function noscriptEnabled() {
		/**
		 * Filter to enable or disable noscript tag
		 *
		 * @param bool $enable_noscript Enable or disable noscript tag.
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_lazyload_noscript', true );
	}

	/**
	 * Returns the HTML tag wrapped inside noscript tags
	 *
	 * @param string $element Element to wrap.
	 * @return string
	 */
	private function noscript( $element ) {
		return '<noscript>' . $element . '</noscript>';
	}

	/**
	 * Applies lazyload on srcset and sizes attributes
	 *
	 * @param string $html HTML image tag.
	 * @return string
	 */
	public function lazyloadResponsiveAttributes( $html ) {
		$data_srcset = preg_replace( '/[\s|"|\'](srcset)\s*=\s*("|\')([^"|\']+)\2/i', ' data-lazy-$1=$2$3$2', $html );

		if ( ! $data_srcset ) {
			return $html;
		}

		$html = $data_srcset;

		$lazy_responsive = preg_replace( '/[\s|"|\'](sizes)\s*=\s*("|\')([^"|\']+)\2/i', ' data-lazy-$1=$2$3$2', $html );

		if ( ! $lazy_responsive ) {
			return $html;
		}

		return $lazy_responsive;
	}

	/**
	 * Finds patterns matching smiley and call the callback method to replace them with the image
	 *
	 * @param string $text Content to search in.
	 * @return string
	 */
	public function convertSmilies( $text ) {
		global $wp_smiliessearch;

		if ( empty( $text ) || ! is_string( $text ) ) {
			return $text;
		}

		if ( ! get_option( 'use_smilies' ) || empty( $wp_smiliessearch ) ) {
			return $text;
		}

		$output = '';
		// HTML loop taken from texturize function, could possible be consolidated.
		$textarr = preg_split( '/(<.*>)/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE ); // capture the tags as well as in between.

		if ( ! $textarr ) {
			return $text;
		}

		$stop = count( $textarr );// loop stuff.

		// Ignore processing of specific tags.
		$tags_to_ignore       = 'code|pre|style|script|textarea';
		$ignore_block_element = '';

		for ( $i = 0; $i < $stop; $i++ ) {
			$content = $textarr[ $i ];

			// If we're in an ignore block, wait until we find its closing tag.
			if ( '' === $ignore_block_element && preg_match( '/^<(' . $tags_to_ignore . ')>/', $content, $matches ) ) {
				$ignore_block_element = $matches[1];
			}

			// If it's not a tag and not in ignore block.
			if ( '' === $ignore_block_element && strlen( $content ) > 0 && '<' !== $content[0] ) {
				$content = preg_replace_callback( $wp_smiliessearch, [ $this, 'translateSmiley' ], $content );
			}

			// did we exit ignore block.
			if ( '' !== $ignore_block_element && '</' . $ignore_block_element . '>' === $content ) {
				$ignore_block_element = '';
			}

			$output .= $content;
		}

		return $output;
	}

	/**
	 * Replace matches by smiley image, lazyloaded
	 *
	 * @param array<string> $matches Array of matches.
	 *
	 * @return string
	 */
	private function translateSmiley( $matches ) {
		global $wpsmiliestrans;

		if ( count( $matches ) === 0 ) {
			return '';
		}

		$smiley = trim( reset( $matches ) );
		$img    = $wpsmiliestrans[ $smiley ];

		$matches    = [];
		$ext        = preg_match( '/\.([^.]+)$/', $img, $matches ) ? strtolower( $matches[1] ) : false;
		$image_exts = [ 'jpg', 'jpeg', 'jpe', 'gif', 'png' ];

		// Don't convert smilies that aren't images - they're probably emoji.
		if ( ! in_array( $ext, $image_exts, true ) ) {
			return $img;
		}

		/**
		 * Filter the Smiley image URL before it's used in the image element.
		 *
		 * @since 2.9.0
		 *
		 * @param string $smiley_url URL for the smiley image.
		 * @param string $img        Filename for the smiley image.
		 * @param string $site_url   Site URL, as returned by site_url().
		 */
		$src_url = apply_filters( 'smilies_src', includes_url( "images/smilies/$img" ), $img, site_url() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		// Don't LazyLoad if process is stopped for these reasons.
		if ( is_feed() || is_preview() ) {
			return sprintf( ' <img src="%s" alt="%s" class="wp-smiley" /> ', esc_url( $src_url ), esc_attr( $smiley ) );
		}

		return sprintf( ' <img src="%s" data-lazy-src="%s" alt="%s" class="wp-smiley" /> ', $this->getPlaceholder(), esc_url( $src_url ), esc_attr( $smiley ) );
	}

	/**
	 * Returns the placeholder for the src attribute
	 *
	 * @since 1.2
	 *
	 * @param int $width  Width of the placeholder image. Default 0.
	 * @param int $height Height of the placeholder image. Default 0.
	 * @return string
	 */
	public function getPlaceholder( $width = 0, $height = 0 ) {
		$width  = 0 === $width ? 0 : absint( $width );
		$height = 0 === $height ? 0 : absint( $height );

		$placeholder = str_replace( ' ', '%20', "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 $width $height'%3E%3C/svg%3E" );
		/**
		 * Filter the image lazyLoad placeholder on src attribute
		 *
		 * @since 1.1
		 *
		 * @param string $placeholder Placeholder that will be printed.
		 * @param int    $width Placeholder width.
		 * @param int    $height Placeholder height.
		 */
		return apply_filters( 'rocket_lazyload_placeholder', $placeholder, $width, $height );
	}
}
