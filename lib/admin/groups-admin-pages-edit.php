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
function groups_admin_pages_edit( $page_id ) {

	global $wpdb;

        $page = get_post( $page_id );
        $upload_link = esc_url( get_upload_iframe_src( 'image', $page->ID ) );
        $org_logo_id = get_post_meta( $page->ID, 'organization_logo', true );
        $org_logo_src = wp_get_attachment_image_src( $org_logo_id, 'full' );
        $has_logo = is_array( $org_logo_src );

	$output = '';

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'laboratory', $current_url );

        // Header for the form
	$output .= '<form id="edit-page" action="' . esc_url( $current_url ) . '" method="post">';
            $output .= '<div class="group-page edit">';
                $output .= '<h2 class="group-page-edit__title">Edit Laboratory</h2>';

        // Core of the form
                // Org Title
                $output .= '<div class="field">';
                    $output .= '<label for="title">Laboratory Title</label>';
                    $output .= '<input type="text" name="title" id="title" value="' . $page->post_title . '">';
                $output .= '</div>';

                // Org Description
                $output .= '<div class="field">';
                    $output .= '<label for="description">Laboratory Description</label>';
                    $output .= '<textarea name="description" id="description">';
                        $output .= $page->post_content;
                    $output .= '</textarea>';
                $output .= '</div>';

                // Org Logo
                $output .= '<div class="field">';
                    $output .= '<label>Laboratory Logo</label>';
                    $output .= '<div class="custom-img-container">';
                        if ( $has_logo ) {
                            $output .= '<img src="' . $org_logo_src[0] . '">';
                        }
                    $output .= '</div>';
                    $output .= '<a class="upload-custom-img" href="' . $upload_link . '">Upload Laboratory Logo</a>';
                    $output .= '<a class="delete-custom-img" href="#">Remove Logo</a>';
                    $output .= '<input class="custom-img-id" name="custom-img-id" type="hidden" value="' . esc_attr( $org_logo_id ) . '">';
                $output .= '</div>';

                // Org Link
                $output .= '<div class="field">';
                    $output .= '<label for="organization_link">Laboratory External Link</label>';
                    $output .= '<input type="text" name="organization_link" id="organization_link" value="' . get_post_meta( $page->ID, 'organization_link', true ) . '">';
                $output .= '</div>';

                // Save/Submig
                $output .= '<div class="field">';
                    $output .= wp_nonce_field( 'groups-pages-edit', GROUPS_ADMIN_PAGES_NONCE_1, true, false );
                    $output .= '<input class="button button-primary" type="submit" value="' . __( 'Save', GROUPS_PLUGIN_DOMAIN ) . '"/>';
                    $output .= '<input type="hidden" name="page_id" value="' . $page->ID . '">';
                    $output .= '<input type="hidden" value="edit" name="action"/>';
                    $output .= '<a class="cancel button" href="' . esc_url( $current_url ) . '">' . __( 'Cancel', GROUPS_PLUGIN_DOMAIN ) . '</a>';
                $output .= '</div>';

        // Form Footer
            $output .= '</div>'; // .group.edit
	$output .= '</form>';

	echo $output;

	Groups_Help::footer();
} // function groups_admin_groups_edit

/**
 * Handle edit form submission.
 */
function groups_admin_pages_edit_submit() {
	global $wpdb;

	if ( !wp_verify_nonce( $_POST[GROUPS_ADMIN_PAGES_NONCE_1],  'groups-pages-edit' ) ) {
		wp_die( __( 'Access denied.', GROUPS_PLUGIN_DOMAIN ) );
	}

        $title = $_POST['title'];
        $description = $_POST['description'];
        $logo = $_POST['custom-img-id'];
        $link = $_POST['organization_link'];
        $page_id = $_POST['page_id'];
        $updates = array();

        $page = get_post( $page_id );

        if ( $page->post_title != $title ) {
            $updates['post_title'] = $title;
        }
        if ( $page->post_content != $description ) {
            $updates['post_content'] = $description;
        }
        if ( get_post_meta( $page->ID, 'organization_logo', true ) != $logo ) {
            update_post_meta( $page->ID, 'organization_logo', $logo );
        }
        if ( get_post_meta( $page->ID, 'organization_link', true ) != $link ) {
            update_post_meta( $page->ID, 'organization_link', $link );
        }

        if ( !empty( $updates ) ) {
            $updates['ID'] = $page->ID;
            $result = wp_update_post( $updates );

            if ( !is_wp_error( $result ) ) {
                return $page->ID;
            } else {
                return false;
            }
        } else {
            return $page->ID;
        }


} // function groups_admin_groups_edit_submit
