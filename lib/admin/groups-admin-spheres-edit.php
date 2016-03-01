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
function groups_admin_spheres_edit( $sphere_id ) {

	global $wpdb;

	$output = '';

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'sphere', $current_url );
	$current_url = remove_query_arg( 'paged', $current_url );

        $sphere = get_term( $sphere_id, 'laboratory_sphere_of_science' );

        $s_id = $sphere->term_id;
        $sphere_meta = get_option( "taxonomy_$s_id" );
        $upload_link = esc_url( get_upload_iframe_src( 'image' ) );
        $sphere_icon_id = $sphere_meta['sphere_icon'];
        $sphere_icon_src = wp_get_attachment_image_src( $sphere_icon_id, 'full' );
        $has_icon = is_array( $sphere_icon_src );

        // Header for the form
	$output .= '<form id="edit-page" action="' . esc_url( $current_url ) . '" method="post">';
            $output .= '<div class="group-page edit">';
                $output .= '<h1 class="group-page-edit__title">Edit Laboratory Sphere of Science</h1>';

        // Core of the form
                // Org Title
                $output .= '<div class="left">';
                    $output .= '<div class="field">';
                        $output .= '<input type="text" name="title" id="title" value="' . $sphere->name . '">';
                        $output .= '<p class="permalink-box"><strong>Permalink:</strong><span class="the-permalink"> ' . get_term_link( intval($sphere_id), 'laboratory_sphere_of_science' ) . '</span><a class="button button-small" href="' . get_term_link( intval($sphere_id), 'laboratory_sphere_of_science' ) . '">View Laboratory Sphere of Science</a></p>';
                    $output .= '</div>';

                    // Org Description
                    $output .= '<div class="field">';

                        ob_start();
                        wp_editor( $sphere->description, 'description', array() );
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
                                $output .= wp_nonce_field( 'groups-spheres-edit', GROUPS_ADMIN_SPHERES_NONCE_1, true, false );
                                $output .= '<input class="button button-primary button-large" type="submit" value="' . __( 'Update', GROUPS_PLUGIN_DOMAIN ) . '"/>';
                                $output .= '<input type="hidden" name="sphere_id" value="' . $sphere_id . '">';
                                $output .= '<input type="hidden" value="edit" name="action" />';
                            $output .= '</div>';
                        $output .= '</div>';
                    $output .= '</div>';

                    $output .= '<div class="postbox">';
                        $output .= '<h3 class="hndle">Sphere Icon</h3>';

                        $output .= '<div class="form-field">';
                            $output .= '<div class="custom-img-container">';
                            if ( $has_icon ) {
                                $output .= '<img src="' . $sphere_icon_src[0] . '">';
                            }
                            $output .= '</div>';
                            if ( $has_icon ) {
                                $output .= '<a class="upload-custom-img hidden" href="' . $upload_link . '">Upload Sphere Icon</a>';
                                $output .= '<a class="delete-custom-img" href="#">Remove Icon</a>';
                            } else {
                                $output .= '<a class="upload-custom-img" href="' . $upload_link . '">Upload Sphere Icon</a>';
                                $output .= '<a class="delete-custom-img hidden" href="#">Remove Icon</a>';
                            }
                            $output .= '<input class="custom-img-id" name="custom-img-id" type="hidden" value="">';
                            $output .= '<p class="description">Upload a sphere of science icon</p>';
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
function groups_admin_spheres_edit_submit() {
	global $wpdb;


	if ( !wp_verify_nonce( $_POST[GROUPS_ADMIN_SPHERES_NONCE_1],  'groups-spheres-edit' ) ) {
		wp_die( __( 'Access denied.', GROUPS_PLUGIN_DOMAIN ) );
	}

        if ($_POST) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $sphere_id = $_POST['sphere_id'];
            $updates = array();

            $sphere = get_term( $sphere_id, 'laboratory_sphere_of_science' );

            if ( $sphere->name != $title ) {
                $updates['name'] = $title;
            }
            if ( $sphere->description != $description ) {
                $updates['description'] = $description;
            }

            if ( isset( $_POST['custom-img-id'] ) ) {
                $icon = $_POST['custom-img-id'];
                $sphere_meta = get_option( "taxonomy_$sphere_id" );
                if ( $sphere_meta['sphere_icon'] != $icon ) {
                    $sphere_meta['sphere_icon'] = $icon;
                }
                update_option( "taxonomy_$sphere_id", $sphere_meta );
            }

            if ( !empty( $updates ) ) {
                $result = wp_update_term( $sphere->term_id, 'laboratory_sphere_of_science', $updates );

                if ( !is_wp_error( $result ) ) {
                    return $sphere->term_id;
                } else {
                    return false;
                }
            } else {
                return $sphere->term_id;
            }
        }


} // function groups_admin_groups_edit_submit
