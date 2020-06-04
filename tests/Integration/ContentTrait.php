<?php

namespace WP_Rocket\Tests\Integration;

trait ContentTrait {

	protected function goToContentType( $config ) {

		switch ( $config['type'] ) {

			case 'front_page':
				return $this->go_to( home_url() );

			case 'home':
				return $this->setUpHome( $config['show_on_front'] );

			case 'is_category':
				$args = isset( $config['cat_args'] ) ? $config['cat_args'] : [];
				return $this->setUpCategory( $args );

			case 'is_tag':
				$args = isset( $config['tag_args'] ) ? $config['tag_args'] : [];
				return $this->setUpTag( $args );

			case 'is_tax':
				$config = array_merge(
					[
						'taxonomy' => 'wptests_tax',
						'object_type' => 'post',
						'args' => [],
					],
					$config
				);

				return $this->setUpTax( $config['taxonomy'], $config['object_type'], $config['args'] );

			case 'is_page':
			case 'is_post':
				$post_data = isset( $config['post_data'] ) ? $config['post_data'] : [];
				return $this->setUpPost( $post_data );
		}
	}

	protected function setUpHome( $show_on_front = 'post' ) {
		update_option( 'show_on_front', $show_on_front );

		set_current_screen( 'front' );
		$home = untrailingslashit( get_option( 'home' ) );
		$this->go_to( $home );

		$this->assertTrue( is_home() );
	}

	protected function setUpCategory( $args = [] ) {
		$category = $this->factory->category->create_and_get( $args );
		$this->go_to( "/?cat={$category->term_id}" );

		$this->assertTrue( is_category() );

		return $category;
	}

	protected function setUpTag( $args = [] ) {
		if ( empty( $args ) ) {
			$args = [
				'name'     => 'TagName',
				'taxonomy' => 'post_tag',
			];
		}

		$tag = $this->factory->term->create_and_get( $args );
		$this->go_to( "/?tag={$tag->slug}" );

		$this->assertTrue( is_tag() );

		return $tag;
	}

	protected function setUpTax( $taxonomy = 'wptests_tax', $object_type = 'post', array $args = [] ) {
		$args = [
			[
				'public' => true,
			],
			$args,
		];

		register_taxonomy( $taxonomy, $object_type, $args );

		$this->assertContains( $taxonomy, get_taxonomies( [ 'publicly_queryable' => true ] ) );

		$term = $this->factory->term->create_and_get(
			[
				'taxonomy' => $taxonomy,
			]
		);

		$post = $this->factory->post->create();
		wp_set_object_terms( $post, $term->slug, $taxonomy );

		$this->go_to( "/?{$taxonomy}={$term->slug}" );

		$this->assertTrue( is_tax() );

		return [
			'tax'  => $taxonomy,
			'term' => $term,
			'post' => $post,
		];
	}

	protected function setUpPost( $post_data ) {
		$post_data = $this->setUpPostData( $post_data );

		$post = $this->factory->post->create_and_get( $post_data );

		$this->go_to( get_permalink( $post ) );
		$this->assertTrue( is_singular() );

		return $post;
	}

	protected function setUpPostData( $post_data ) {
		if ( ! isset( $post_data['type'] ) ) {
			$post_data['type'] = 'post';
		}

		switch ( $post_data['type'] ) {
			case 'is_page':
				$post_data['type'] = 'page';
				break;
			case 'is_post':
				$post_data['type'] = 'post';
				break;
		}

		return array_merge(
			[
				'post_title'  => 'Test',
				'content'     => '',
				'post_status' => 'publish',
				'post_type'   => 'post',
			],
			$post_data
		);
	}
}
