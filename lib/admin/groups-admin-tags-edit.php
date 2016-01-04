<?php
/**
 * groups-admin-pages-edit.php
 * 
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 * 
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 * 
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * This header and all notices must be kept intact.
 * 
 * @author Karim Rahimpur
 * @package groups
 * @since groups 1.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Show edit page form.
 * @param int $page_id post id for the group page
 */
function groups_admin_tags_edit( $tag_id ) {

	global $wpdb;

	$output = '';

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'tag', $current_url );
	$current_url = remove_query_arg( 'paged', $current_url );

        $tag = get_term( $tag_id, 'laboratory_tag' );

        // Header for the form
	$output .= '<form id="edit-page" action="' . esc_url( $current_url ) . '" method="post">';
            $output .= '<div class="group-page edit">';
                $output .= '<h1 class="group-page-edit__title">Edit Laboratory Tag</h1>';

        // Core of the form
                // Org Title
                $output .= '<div class="left">';
                    $output .= '<div class="field">';
                        $output .= '<input type="text" name="title" id="title" value="' . $tag->name . '">';
                        $output .= '<p class="permalink-box"><strong>Permalink:</strong><span class="the-permalink"> ' . get_term_link( intval($tag_id), 'laboratory_tag' ) . '</span><a class="button button-small" href="' . get_term_link( intval($tag_id), 'laboratory_tag' ) . '">View Laboratory Tag</a></p>';
                    $output .= '</div>';

                    // Org Description
                    $output .= '<div class="field">';

                        ob_start();
                        wp_editor( $tag->description, 'description', array() );
                        $output .= ob_get_clean();

                    $output .= '</div>';
                $output .= '</div>';

                // Org Logo
                $output .= '<div class="right">';

                    // Save/Submit
                    $output .= '<div class="postbox">';
                        $output .= '<h3 class="hndle">Save</h3>';

                        $output .= '<div id="major-publishing-actions">';
                            $output .= '<div id="publishing-action">';
                                $output .= wp_nonce_field( 'groups-tags-edit', GROUPS_ADMIN_TAGS_NONCE_1, true, false );
                                $output .= '<input class="button button-primary button-large" type="submit" value="' . __( 'Update', GROUPS_PLUGIN_DOMAIN ) . '"/>';
                                $output .= '<input type="hidden" name="tag_id" value="' . $tag_id . '">';
                                $output .= '<input type="hidden" value="edit" name="action" />';
                            $output .= '</div>';
                        $output .= '</div>';
                    $output .= '</div>';

                $output .= '</div>';


        // Form Footer
            $output .= '</div>'; // .group.edit
	$output .= '</form>';

	echo $output;
} // function groups_admin_groups_edit

/**
 * Handle edit form submission.
 */
function groups_admin_tags_edit_submit() {
	global $wpdb;


	if ( !wp_verify_nonce( $_POST[GROUPS_ADMIN_TAGS_NONCE_1],  'groups-tags-edit' ) ) {
		wp_die( __( 'Access denied.', GROUPS_PLUGIN_DOMAIN ) );
	}

        if ($_POST) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $tag_id = $_POST['tag_id'];
            $updates = array();

            $tag = get_term( $tag_id, 'laboratory_tag' );

            if ( $tag->name != $title ) {
                $updates['name'] = $title;
            }
            if ( $tag->description != $description ) {
                $updates['description'] = $description;
            }

            if ( !empty( $updates ) ) {
                $result = wp_update_term( $tag->term_id, 'laboratory_tag', $updates );

                if ( !is_wp_error( $result ) ) {
                    return $tag->term_id;
                } else {
                    return false;
                }
            } else {
                return $tag->term_id;
            }
        }


} // function groups_admin_groups_edit_submit
