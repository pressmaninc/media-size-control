<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo __( 'Media Size Control setting', 'media-size-control' ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'media-size-control-settings-group' ); ?>
		<?php do_settings_sections( 'media-size-control-settings-group' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php echo sprintf( __( 'Limits the size of the file that can be uploaded. The default maximum upload size is %s', 'media-size-control' ), ini_get( 'upload_max_filesize' ) ); ?></th>
				<td>
					<textarea name="limit_mime_type" rows="20" cols="60"><?php
						$textarea_string = '';
						foreach ( get_option( 'limit_mime_type' ) as $mime_type => $limit_size_kb ) {
							$textarea_string .= $mime_type . ',' . $limit_size_kb . "\n";
						}
						echo wp_strip_all_tags( $textarea_string, false );
						?></textarea>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>

	</form>

	<table class="mime-type-list">
		<h3><?php echo __( 'MIME TYPE LIST', 'media-size-control' ) ?></h3>
		<tbody class="mime-type-list">
		<td class="mime-type-list" width="30%">
			<h3>FILE EXTENSION</h3>
		</td>
		<td class="mime-type-list">
			<h3>MIME TYPE</h3>
		</td>
		<?php
		$mime_types = wp_get_mime_types();
		foreach ( $mime_types as $extension => $mime_type ) {
			$extension = wp_strip_all_tags( $extension, false );
			$mime_type = wp_strip_all_tags( $mime_type, false );
			echo "<tr><td class='mime-type-list'>$extension</td><td>$mime_type</td></tr>";
		}
		?>
		</tbody>
	</table>
</div>