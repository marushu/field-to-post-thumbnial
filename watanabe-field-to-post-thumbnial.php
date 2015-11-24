<?php
/*
Plugin Name: Watanabe-field-to-post-thumbnial
Version: 0.1-alpha
Description: カスタムフィールドのimgタグをアイキャッチ画像へ登録するプラグインです。使用はDBインポート後1回がお勧めです。
Author: Hibou
Author URI: http://private.hibou-web.com
Plugin URI: http://private.hibou-web.com
Text Domain: watanabe-field-to-post-thumbnial
Domain Path: /languages
*/

function watanabe_field_to_post_thumbnial( $post_type = 'post', $delete = false, $key = '' ) {
	$args = array(
		'post_type' => $post_type,
		'posts_per_page' => -1,
	);
	$all_posts = get_posts( $args );
	$all_posts_num = count( $all_posts );

	foreach( $all_posts as $post ) {
		setup_postdata( $post );
		$field_image = get_post_meta( $post->ID, $key, true );

		preg_match('/(src=["\'](.*?)["\'])/', $field_image, $match);
		$split = preg_split('/["\']/', $match[0]);
		$src = $split[1];

		// change old thumbnail key!
		$image_url = $src;

		// no thumbnail? on to the next
		if( empty( $image_url ) ) continue;

		// find mime type for later
		$filetype = wp_check_filetype( $image_url );

		// Set up an array of args new attachment
		$args = array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => esc_attr( $post->post_title ), // you may want something different here
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		// Insert the attachment!
		$thumb_id = wp_insert_attachment( $args, $image_url,  $post->ID );

		// gotta set up some meta data (height, width, etc)
		// must include this path
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$metadata = wp_generate_attachment_metadata( $thumb_id, $image_url );
		wp_update_attachment_metadata( $thumb_id, $metadata );

		// set post thumbnail :)
		update_post_meta( $post->ID, '_thumbnail_id', $thumb_id );

		// delete fields image
		//if( true == $delete )
			//delete_post_meta( $p->ID, $key, $image_url );

	}
	wp_reset_postdata();
}

