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
function groups_admin_categories_edit( $cat_id ) {

	global $wpdb;

	$output = '';

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'category', $current_url );
	$current_url = remove_query_arg( 'paged', $current_url );

        $cat = get_term( $cat_id, 'laboratory_category' );
        $parents = get_terms( 'laboratory_category', array( 'hide_empty' => 0 ) );

        // Header for the form
	$output .= '<form id="edit-page" action="' . esc_url( $current_url ) . '" method="post">';
            $output .= '<div class="group-page edit">';
                $output .= '<h1 class="group-page-edit__title">Edit Laboratory Category</h1>';

        // Core of the form
                // Org Title
                $output .= '<div class="left">';
                    $output .= '<div class="field">';
                        $output .= '<input type="text" name="title" id="title" value="' . $cat->name . '">';
                        $output .= '<p class="permalink-box"><strong>Permalink:</strong><span class="the-permalink"> ' . get_term_link( intval($cat_id), 'laboratory_category' ) . '</span><a class="button button-small" href="' . get_term_link( intval($cat_id), 'laboratory_category' ) . '">View Laboratory Category</a></p>';
                    $output .= '</div>';

                    // Org Description
                    $output .= '<div class="field">';

                        ob_start();
                        wp_editor( $cat->description, 'description', array() );
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
                                $output .= wp_nonce_field( 'groups-categories-edit', GROUPS_ADMIN_CATEGORIES_NONCE_1, true, false );
                                $output .= '<input class="button button-primary button-large" type="submit" value="' . __( 'Update', GROUPS_PLUGIN_DOMAIN ) . '"/>';
                                $output .= '<input type="hidden" name="cat_id" value="' . $cat_id . '">';
                                $output .= '<input type="hidden" value="edit" name="action" />';
                            $output .= '</div>';
                        $output .= '</div>';
                    $output .= '</div>';

                    // Category Parent
                    $output .= '<div class="postbox">';
                        $output .= '<h3 class="hndle">Category Parent</h3>';

                        $output .= '<div class="inside">';
                            $output .= sprintf(
                                '<select class="select parent" name="parent_id"  placeholder="%s" id="parent_id">',
                                __( 'Select category parent &hellip;', GROUPS_PLUGIN_DOMAIN )
                            );
                            
                                $output .= '<option value="">No parent category</option>';
                                foreach ( $parents as $parent ) {
                                    $parent->term_id == $cat->parent ? $selected = 'selected="true"' : $selected = '';
                                    $output .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $parent->term_id ), $selected, wp_filter_nohtml_kses( $parent->name ) );
                                }

                            $output .= '</select>';
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
function groups_admin_categories_edit_submit() {
	global $wpdb;


	if ( !wp_verify_nonce( $_POST[GROUPS_ADMIN_CATEGORIES_NONCE_1],  'groups-categories-edit' ) ) {
		wp_die( __( 'Access denied.', GROUPS_PLUGIN_DOMAIN ) );
	}

        if ($_POST) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $cat_id = $_POST['cat_id'];
            $updates = array();
            $parent_id = $_POST['parent_id'];

            $cat = get_term( $cat_id, 'laboratory_category' );

            if ( $cat->name != $title ) {
                $updates['name'] = $title;
            }
            if ( $cat->description != $description ) {
                $updates['description'] = $description;
            }
            if ( $cat->parent != $parent_id ) {
                $updates['parent'] = $parent_id;
            }

            if ( !empty( $updates ) ) {
                $result = wp_update_term( $cat->term_id, 'laboratory_category', $updates );

                if ( !is_wp_error( $result ) ) {
                    return $cat->term_id;
                } else {
                    return false;
                }
            } else {
                return $cat->term_id;
            }
        }


} // function groups_admin_groups_edit_submit
