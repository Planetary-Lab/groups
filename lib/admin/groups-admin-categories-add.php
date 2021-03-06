<?php
/**
 * groups-admin-groups-add.php
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
 * Show add group form.
 */
function groups_admin_categories_add() {

	global $wpdb;

	$output = '';

	if ( !current_user_can( GROUPS_ADMINISTER_GROUPS ) ) {
		wp_die( __( 'Access denied.', GROUPS_PLUGIN_DOMAIN ) );
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'paged', $current_url );
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'category', $current_url );

        $parents = get_terms( 'laboratory_category', array( 'hide_empty' => 0 ) );

        // Header for the form
	$output .= '<form id="edit-page" action="' . esc_url( $current_url ) . '" method="post">';
            $output .= '<div class="group-page edit">';
                $output .= '<h1 class="group-page-edit__title">Add Laboratory Category</h1>';

        // Core of the form
                // Org Title
                $output .= '<div class="left">';
                    $output .= '<div class="field">';
                        $output .= '<input type="text" name="title" id="title">';
                    $output .= '</div>';

                    // Org Description
                    $output .= '<div class="field">';

                        ob_start();
                        wp_editor( '', 'description', array() );
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
                                $output .= wp_nonce_field( 'groups-categories-add', GROUPS_ADMIN_CATEGORIES_NONCE_1, true, false );
                                $output .= '<input class="button button-primary button-large" type="submit" value="' . __( 'Update', GROUPS_PLUGIN_DOMAIN ) . '"/>';
                                $output .= '<input type="hidden" value="add" name="action" />';
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
                                    $output .= sprintf( '<option value="%s">%s</option>', esc_attr( $parent->term_id ), wp_filter_nohtml_kses( $parent->name ) );
                                }

                            $output .= '</select>';
                        $output .= '</div>';
                    $output .= '</div>';

                $output .= '</div>';


        // Form Footer
            $output .= '</div>'; // .group.edit
	$output .= '</form>';

	echo $output;
} // function groups_admin_groups_add

/**
 * Handle add group form submission.
 * @return int new group's id or false if unsuccessful
 */
function groups_admin_categories_add_submit() {

	global $wpdb;

	if ( !current_user_can( GROUPS_ADMINISTER_GROUPS ) ) {
		wp_die( __( 'Access denied.', GROUPS_PLUGIN_DOMAIN ) );
	}

	if ( !wp_verify_nonce( $_POST[GROUPS_ADMIN_CATEGORIES_NONCE_1], 'groups-categories-add' ) ) {
		wp_die( __( 'Access denied.', GROUPS_PLUGIN_DOMAIN ) );
	}

	$parent_id   = isset( $_POST['parent_id'] ) ? $_POST['parent_id'] : 0;
	$description = isset( $_POST['description'] ) ? $_POST['description'] : '';
	$name        = isset( $_POST['title'] ) ? $_POST['title'] : null;
	$slug        = isset( $_POST['slug'] ) ? $_POST['slug'] : null;

        $cat = wp_insert_term(
            $name,
            'laboratory_category',
            array(
                'description'   => $description,
                'parent'        => $parent_id,
                'slug'          => $slug
            )
        );


        if ( is_wp_error( $cat ) ) {
            Groups_Admin::add_message( __( 'There was an error adding the category', GROUPS_PLUGIN_DOMAIN ), 'error' );
            return false;
        }

        return $cat['term_id'];
} // function groups_admin_groups_add_submit
