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


        // Check to see if the user can actually edit this page
        $group_id = get_post_meta( $page_id, 'group_id', true );
        var_dump($page_id);
        $group = new Groups_Group( $group_id );
        $groups_users = $group->__get( 'users' );
        var_dump($groups_users);
        $in_group = false;
        foreach ( $groups_users as $user ) {
            if ( $user->ID == get_current_user_id() ) {
                $in_group = true;
            }
        }
        if ( !$in_group ) {
	    wp_die( __( 'Only group members can edit this page. You do not have sufficient permissions to edit this page.', GROUPS_PLUGIN_DOMAIN ) );
        }

        $args = array(
            'role'  => 'author'
        );
        $users = get_users( );
        $page = get_post( $page_id );
        $upload_link = esc_url( get_upload_iframe_src( 'image', $page->ID ) );
        $org_logo_id = get_post_meta( $page->ID, 'organization_logo', true );
        $org_logo_src = wp_get_attachment_image_src( $org_logo_id, 'full' );
        $has_logo = is_array( $org_logo_src );

	$output = '';

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'laboratory', $current_url );
	$current_url = remove_query_arg( 'group_id', $current_url );
	$current_url = remove_query_arg( 'paged', $current_url );

        // Header for the form
	$output .= '<form id="edit-page" action="' . esc_url( $current_url ) . '" method="post">';
            $output .= '<div class="group-page edit">';
                $output .= '<h1 class="group-page-edit__title">Edit Laboratory</h1>';

        // Core of the form
                // Org Title
                $output .= '<div class="left">';
                    $output .= '<div class="field">';
                        $output .= '<input type="text" name="title" id="title" value="' . $page->post_title . '">';
                        $output .= '<p class="permalink-box"><strong>Permalink:</strong><span class="the-permalink"> ' . get_permalink( $page->ID ) . '</span><a class="button button-small" href="' . get_permalink( $page->ID ) . '">View Laboratory</a></p>';
                    $output .= '</div>';

                    // Org Description
                    $output .= '<div class="field">';

                        ob_start();
                        wp_editor( $page->post_content, 'description', array() );
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
                                $output .= wp_nonce_field( 'groups-pages-edit', GROUPS_ADMIN_PAGES_NONCE_1, true, false );
                                $output .= '<input class="button button-primary button-large" type="submit" value="' . __( 'Update', GROUPS_PLUGIN_DOMAIN ) . '"/>';
                                $output .= '<input type="hidden" name="page_id" value="' . $page->ID . '">';
                                $output .= '<input type="hidden" name="groups_group_id" value="' . $group_id . '">';
                                $output .= '<input type="hidden" value="edit" name="action" />';
                            $output .= '</div>';
                        $output .= '</div>';
                    $output .= '</div>';

                    $output .= '<div class="postbox">';
                        $output .= '<h3 class="hndle">Laboratory Logo</h3>';

                        $output .= '<div class="inside">';
                            $output .= '<div class="custom-img-container">';
                                if ( $has_logo ) {
                                    $output .= '<img src="' . $org_logo_src[0] . '">';
                                }
                            $output .= '</div>';

                            if ( $has_logo ) {
                                $output .= '<a class="upload-custom-img hidden" href="' . $upload_link . '">Upload Laboratory Logo</a>';
                                $output .= '<a class="delete-custom-img" href="#">Remove Logo</a>';
                            } else {
                                $output .= '<a class="upload-custom-img" href="' . $upload_link . '">Upload Laboratory Logo</a>';
                                $output .= '<a class="delete-custom-img hidden" href="#">Remove Logo</a>';
                            }

                            $output .= '<input class="custom-img-id" name="custom-img-id" type="hidden" value="' . esc_attr( $org_logo_id ) . '">';
                        $output .= '</div>';
                    $output .= '</div>';

                    // Org Link
                    $output .= '<div class="postbox">';
                        $output .= '<h3 class="hndle">Laboratory External Link</h3>';

                        $output .= '<div class="inside">';
                            $output .= '<input type="text" name="organization_link" id="organization_link" value="' . get_post_meta( $page->ID, 'organization_link', true ) . '">';
                        $output .= '</div>';
                    $output .= '</div>';

                    // Groups Users
                    $output .= '<div class="postbox">';
                        $output .= '<h3 class="hndle">Laboratory Members</h3>';

                        $output .= sprintf(
                            '<select class="select members" name="members[]" multiple="multiple" placeholder="%s" id="members">',
                            __( 'Edit members &hellip;', GROUPS_PLUGIN_DOMAIN )
                        );
                        foreach ( $users as $user ) {
                            $selected = '';

                            foreach ( $groups_users as $group_user ) {
                                if ( $user->ID === $group_user->ID ) {
                                    $selected = 'selected="true"';
                                }
                            }
                            $output .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $user->ID ), $selected, wp_filter_nohtml_kses( $user->first_name ) . ' ' . wp_filter_nohtml_kses( $user->last_name ) );
                        }
                        $output .= '</select>';
                        $output .= Groups_UIE::render_select( '.select.members' );

                        $output .= '<div class="inside">';
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
function groups_admin_pages_edit_submit() {
	global $wpdb;


	if ( !wp_verify_nonce( $_POST[GROUPS_ADMIN_PAGES_NONCE_1],  'groups-pages-edit' ) ) {
		wp_die( __( 'Access denied.', GROUPS_PLUGIN_DOMAIN ) );
	}

        if ($_POST) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $logo = $_POST['custom-img-id'];
            $link = $_POST['organization_link'];
            $page_id = $_POST['page_id'];
            $updates = array();

            if ($_POST['members'])
                $members = $_POST['members'];

            $group = new Groups_Group( $_POST['groups_group_id'] );
            $groups_users = $group->__get( 'users' );

            $page = get_post( $page_id );

            if ( $page->post_title != $title ) {
                $updates['post_title'] = $title;
                $group_id = Groups_Group::update( array( 'group_id' => $group->id, "name" => $title ) );
            }
            if ( $page->post_content != $description ) {
                $updates['post_content'] = $description;
                $group_id = groups_group::update( array( 'group_id' => $group->id, "description" => $description ) );
            }
            if ( get_post_meta( $page->ID, 'organization_logo', true ) != $logo ) {
                update_post_meta( $page->ID, 'organization_logo', $logo );
            }
            if ( get_post_meta( $page->ID, 'organization_link', true ) != $link ) {
                update_post_meta( $page->ID, 'organization_link', $link );
            }

            if ( $members ) {
                foreach ( $groups_users as $user ) {
                    $user_in_group = false;

                    foreach ( $members as $member ) {
                        if ( $user->ID == $member ) {
                            $user_in_group = true;
                        }
                    }

                    if ( !$user_in_group ) {
                        Groups_User_Group::delete( $user->ID, $group->group_id ); 
                    }
                }

                foreach ( $members as $member ) {
                    $found = false;
                    
                    foreach ( $groups_users as $user ) {

                        if ( $user->ID == $member ) {
                            $found = true;
                        }
                    }

                    if ( !$found ) {
                        Groups_User_Group::create( array( 'user_id' => $member, 'group_id' => $group->group_id ) ); 
                    }
                }
            }

            if ( !empty( $updates ) ) {
                $updates['ID'] = $page->ID;
                $result = wp_update_post( $updates );

                if ( !is_wp_error( $result ) ) {
                    return $group->group_id;
                } else {
                    Groups_Admin::add_message(
                            sprintf(
                                __( 'The <em>%s</em> group already exists and cannot be used to name this one.', GROUPS_PLUGIN_DOMAIN ), stripslashes( wp_filter_nohtml_kses( $result->getMessage() ) )
                            ),
                            'error'
                    );
                    return false;
                }
            } else {
                return $group->group_id;
            }
        }


} // function groups_admin_groups_edit_submit
