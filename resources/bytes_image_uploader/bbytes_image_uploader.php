<?php

function bbytes_render_image_uploader( $name, $val, $max_images = 1 ) {
	if( !defined( 'BBYTES_RENDERED_IMAGE_UPLOAD_HANDLER' ) ) {
		wp_enqueue_media( array( "post" => $post->ID ) );
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var _custom_media = true,
				_orig_send_attachment = wp.media.editor.send.attachment;
				$('.bbytes_image_meta').on('click', '.bbytes_clear_media', function(e) {
					e.preventDefault();
					var $image_block   = $(this).closest('.bbytes_image_meta_block');
					var $image_wrapper = $image_block.closest('.bbytes_image_meta_blocks');
					var $add_link = $image_wrapper.closest('.bbytes_image_meta').find('.bbytes_upload_media');
					var max_images = parseInt($image_wrapper.data('max-images'));
					$image_block.remove();
					if( !max_images || $image_wrapper.find('.bbytes_image_meta_block').length < max_images ) {
						$add_link.show();
					}
				});
				$('.bbytes_image_meta').on('click', '.bbytes_upload_media', function(e) {
					e.preventDefault();
					var send_attachment_bkp = wp.media.editor.send.attachment;
					var $add_link = $(this);
					var $wrapper = $add_link.closest('.bbytes_image_meta');
					var $image_wrapper = $wrapper.find('.bbytes_image_meta_blocks');
					var name = $wrapper.data('image-name');
					var max_images = parseInt($image_wrapper.data('max-images'));
					if( !max_images || $image_wrapper.find('.bbytes_image_meta_block').length < max_images ) {
						_custom_media = true;
						wp.media.editor.send.attachment = function(props, attachment){
							if ( _custom_media ) {
								if(typeof attachment.sizes.thumbnail != "undefined" && typeof attachment.sizes.thumbnail.url != "undefined") {
									var src = attachment.sizes.thumbnail.url;
								} else {
									var src = attachment.url;
									alert('WARNING: This image is below the recommended dimensions of 500x500 pixels.');
								}
								var rand = Math.floor( Math.random() * 2147483648 );
								var id = name + rand;
								var $image_block = $("<div></div>").addClass('bbytes_image_meta_block').attr('id', id);
								var $image_link  = $("<a></a>").attr('href', '#').addClass('bbytes_edit_media').appendTo($image_block);
								var $input = $("<input/>").attr('type', "hidden").attr('name', name+'[]').val(attachment.id).appendTo($image_link);
								var $image = $("<img/>").attr('src', src).attr('alt', "preview").appendTo($image_link);
								$image_wrapper.append($image_block);
								var $remove_link  = $("<a></a>").attr('href', '#').addClass('bbytes_clear_media').html('<span class="dashicons dashicons-dismiss"></span>').appendTo($image_block);
								if( max_images && $image_wrapper.find('.bbytes_image_meta_block').length >= max_images ) {
									$add_link.hide();
								}
							} else {
								return _orig_send_attachment.apply( this, [props, attachment] );
							};
						}
						wp.media.editor.open($add_link);
					}
				});
				$('.bbytes_image_meta').on('click', '.bbytes_edit_media', function(e) {
					e.preventDefault();
					var send_attachment_bkp = wp.media.editor.send.attachment;
					var $image_block = $(this).closest('.bbytes_image_meta_block');
					var $image_wrapper = $image_block.closest('.bbytes_image_meta_blocks');
					var $wrapper = $image_wrapper.closest('.bbytes_image_meta');
					_custom_media = true;
					wp.media.editor.send.attachment = function(props, attachment) {
						if ( _custom_media ) {
							if(typeof attachment.sizes.thumbnail != "undefined" && typeof attachment.sizes.thumbnail.url != "undefined") {
								var src = attachment.sizes.thumbnail.url;
							} else {
								var src = attachment.url;
								alert('WARNING: This image is below the recommended dimensions of 500x500 pixels.');
							}

							var $input = $image_wrapper.find('input').val(attachment.id);
							var $image = $image_wrapper.find('img').attr('src', src);
						} else {
							return _orig_send_attachment.apply( this, [props, attachment] );
						};
					}
					wp.media.editor.open($(this));
				});
				$('body').on('click', '.add_media', function() {
					_custom_media = false;
				});
			});
		</script>
		<?php
		define( 'BBYTES_RENDERED_IMAGE_UPLOAD_HANDLER', true );
	}
	$field_name = $name . '[]';
	?>

	<div class="bbytes_image_meta" data-image-name="<?php echo esc_attr( $name ); ?>">
	<?php
	if( !$val ) {
		$val = array();
	}
	if( !is_array( $val ) ) {
		$val = array( $val );
	}
	?>
	<div class="bbytes_image_meta_blocks" data-max-images="<?php echo esc_attr( intval( $max_images ) ); ?>">
	<?php
	foreach( $val as $image_id ) {
		$src = "";
		if( $image_id ) {
			$data = wp_get_attachment_image_src( $image_id, "thumbnail" );
			$src = $data[0];
		}
		$id = $name . rand();
		?>
		<div class="bbytes_image_meta_block" id="<?php echo esc_attr($id); ?>">
			<a href="#" class="bbytes_edit_media">
				<input name="<?php echo $field_name; ?>" type="hidden" value="<?php echo $image_id ?>"/>
				<img src="<?php echo $src; ?>" alt="preview"/>
			</a>
			<a href="#" id="<?php echo $id; ?>_clear" class="bbytes_clear_media"><span class="dashicons dashicons-dismiss"></span></a>
		</div>
		<?php
	}
	?>
	</div><div class="bbytes_upload_media_wrapper">
		<a href="#" class="bbytes_upload_media"<?php echo ( $max_images && count( $val ) >= $max_images )?' style="display: none;"':''; ?>/>
			<span class="dashicons dashicons-plus"></span>
			<span>add image</span>
		</a>
	</div>
	</div>
	<?php
}
add_action( 'admin_head', 'bbytes_admin_render_image_uploader_styles' );
function bbytes_admin_render_image_uploader_styles() {
	?>
	<style type="text/css">
		.bbytes_image_meta {
			margin: 0 -15px;
		}
		.bbytes_image_meta, .bbytes_image_meta * {
			box-sizing: border-box;
			vertical-align: top;
		}
		.bbytes_image_meta .bbytes_upload_media_wrapper {
			display: inline-block;
			padding: 15px;
		}
		.bbytes_image_meta .bbytes_upload_media {
			display: inline-block;
			width: 100px;
			height: 100px;
			border: 2px dashed #ccc;
			color: #ccc;
			text-align: center;
			text-decoration: none;
			white-space: nowrap;
		}
		.bbytes_image_meta .bbytes_upload_media > span {
			display: block;
			width: 100%;
			padding: 5px;
		}
		.bbytes_image_meta .bbytes_upload_media > span.dashicons {
			font-size: 3em;
			height: 40px;
			margin-top: 20px;
		}
		.bbytes_image_meta .bbytes_image_meta_blocks {
			display: inline;
		}
		.bbytes_image_meta .bbytes_image_meta_blocks .bbytes_image_meta_block {
			position: relative;
			width: 130px;
			height: 130px;
			display:inline-block;
			padding: 15px;
		}
		.bbytes_image_meta .bbytes_image_meta_blocks .bbytes_image_meta_block > a {
			display: block;
		}
		.bbytes_image_meta .bbytes_image_meta_blocks .bbytes_image_meta_block img {
			width: 100%;
			height: 100%;
		}
		.bbytes_image_meta .bbytes_image_meta_blocks .bbytes_image_meta_block .bbytes_clear_media {
			position: absolute;
			top: 15px;
			right: 15px;
			text-decoration: none;
		}
	</style>
	<?php
}