<?php
/**
 * Duplicate Content module.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager\Modules\DuplicateContent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Module {
	const ACTION       = 'wp_features_manager_duplicate_post';
	const NONCE_PREFIX = 'wp_features_manager_duplicate_post_';

	public static function register() {
		add_filter( 'post_row_actions', array( __CLASS__, 'add_row_action' ), 10, 2 );
		add_filter( 'page_row_actions', array( __CLASS__, 'add_row_action' ), 10, 2 );
		add_action( 'admin_action_' . self::ACTION, array( __CLASS__, 'duplicate_requested_post' ) );
	}

	public static function add_row_action( $actions, $post ) {
		if ( ! $post instanceof \WP_Post || ! self::can_duplicate( $post ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg( array( 'action' => self::ACTION, 'post' => $post->ID ), admin_url( 'admin.php' ) ),
			self::NONCE_PREFIX . $post->ID
		);
		$actions['wp_features_manager_duplicate'] = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $url ),
			/* translators: %s: content title. */
			esc_attr( sprintf( __( 'Duplicate %s', 'wp-features-manager' ), get_the_title( $post ) ) ),
			esc_html__( 'Duplicate', 'wp-features-manager' )
		);

		return $actions;
	}

	public static function duplicate_requested_post() {
		$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
		$post    = get_post( $post_id );

		if ( ! $post ) {
			self::stop_with_error( __( 'The content to duplicate could not be found.', 'wp-features-manager' ), 404 );
		}

		check_admin_referer( self::NONCE_PREFIX . $post_id );
		if ( ! self::can_duplicate( $post ) ) {
			self::stop_with_error( __( 'You are not allowed to duplicate this content.', 'wp-features-manager' ), 403 );
		}

		$duplicate_id = self::create_draft_copy( $post );
		if ( is_wp_error( $duplicate_id ) ) {
			self::stop_with_error( $duplicate_id->get_error_message(), 500 );
		}

		$redirect_url = wp_get_referer();
		if ( ! $redirect_url ) {
			$redirect_url = self::posts_list_url( $post->post_type );
		}
		wp_safe_redirect( $redirect_url );
		exit;
	}

	private static function create_draft_copy( \WP_Post $post ) {
		$duplicate_id = wp_insert_post( wp_slash( self::draft_data( $post ) ), true );
		if ( is_wp_error( $duplicate_id ) ) {
			return $duplicate_id;
		}

		$copy_error = self::copy_taxonomies( $post, $duplicate_id );
		if ( ! is_wp_error( $copy_error ) ) {
			$copy_error = self::copy_meta( $post->ID, $duplicate_id );
		}
		if ( is_wp_error( $copy_error ) ) {
			wp_delete_post( $duplicate_id, true );
			return $copy_error;
		}

		return $duplicate_id;
	}

	private static function draft_data( \WP_Post $post ) {
		return array(
			'post_author'           => get_current_user_id(),
			'post_content'          => $post->post_content,
			'post_content_filtered' => $post->post_content_filtered,
			'post_title'            => self::next_copy_title( $post ),
			'post_excerpt'          => $post->post_excerpt,
			'post_status'           => 'draft',
			'post_type'             => $post->post_type,
			'post_parent'           => $post->post_parent,
			'menu_order'            => $post->menu_order,
			'comment_status'        => $post->comment_status,
			'ping_status'           => $post->ping_status,
			'post_password'         => $post->post_password,
			'post_mime_type'        => $post->post_mime_type,
		);
	}

	private static function next_copy_title( \WP_Post $post ) {
		$copy_label = _x( 'Copy', 'duplicate content title suffix', 'wp-features-manager' );
		$base_title = $post->post_title;
		$copy_index = 1;
		$pattern    = '/^(.*) \(' . preg_quote( $copy_label, '/' ) . '(?: (\d+))?\)$/u';

		if ( preg_match( $pattern, $post->post_title, $title_parts ) ) {
			$base_title = $title_parts[1];
			$copy_index = empty( $title_parts[2] ) ? 2 : (int) $title_parts[2] + 1;
		}
		do {
			$candidate = self::copy_title_candidate( $base_title, $copy_label, $copy_index );
			++$copy_index;
		} while ( post_exists( $candidate, '', '', $post->post_type ) );

		return $candidate;
	}

	private static function copy_title_candidate( $base_title, $copy_label, $copy_index ) {
		if ( 1 === $copy_index ) {
			/* translators: 1: original title, 2: translated "Copy" label. */
			return sprintf( _x( '%1$s (%2$s)', 'first duplicate content title', 'wp-features-manager' ), $base_title, $copy_label );
		}

		/* translators: 1: original title, 2: translated "Copy" label, 3: copy number. */
		return sprintf( _x( '%1$s (%2$s %3$d)', 'numbered duplicate content title', 'wp-features-manager' ), $base_title, $copy_label, $copy_index );
	}

	private static function posts_list_url( $post_type ) {
		if ( 'post' === $post_type ) {
			return admin_url( 'edit.php' );
		}
		return add_query_arg( 'post_type', $post_type, admin_url( 'edit.php' ) );
	}

	private static function copy_taxonomies( \WP_Post $post, $duplicate_id ) {
		foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
			$term_ids = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
			if ( is_wp_error( $term_ids ) ) {
				return $term_ids;
			}
			$assigned_terms = wp_set_object_terms( $duplicate_id, $term_ids, $taxonomy );
			if ( is_wp_error( $assigned_terms ) ) {
				return $assigned_terms;
			}
		}

		return null;
	}

	private static function copy_meta( $post_id, $duplicate_id ) {
		$excluded_keys = array( '_edit_lock', '_edit_last', '_wp_old_slug' );
		foreach ( get_post_meta( $post_id ) as $meta_key => $meta_values ) {
			if ( in_array( $meta_key, $excluded_keys, true ) ) {
				continue;
			}
			foreach ( $meta_values as $meta_value ) {
				if ( false === add_post_meta( $duplicate_id, $meta_key, $meta_value ) ) {
					return new \WP_Error( 'wp_features_manager_meta_copy_failed', __( 'The duplicate could not copy all custom fields.', 'wp-features-manager' ) );
				}
			}
		}

		return null;
	}

	private static function can_duplicate( \WP_Post $post ) {
		$post_type = get_post_type_object( $post->post_type );
		return 'trash' !== $post->post_status
			&& 'attachment' !== $post->post_type
			&& $post_type
			&& $post_type->show_ui
			&& current_user_can( 'edit_post', $post->ID )
			&& current_user_can( $post_type->cap->create_posts );
	}

	private static function stop_with_error( $message, $response ) {
		wp_die(
			esc_html( $message ),
			esc_html__( 'Duplicate content', 'wp-features-manager' ),
			array( 'response' => $response, 'back_link' => true )
		);
	}
}

Module::register();
