<?php
/**
 * groups-admin-pages.php
 * 
 * Copyright (c) Andy McGuinness andymcguinness.com
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
 * @author Andy McGuinness
 * @package groups
 * @since groups 1.9.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// admin defines
define( 'GROUPS_PAGES_PER_PAGE', 10 );
define( 'GROUPS_ADMIN_PAGES_NONCE_1', 'pages-nonce-1');
define( 'GROUPS_ADMIN_PAGES_NONCE_2', 'pages-nonce-2');
define( 'GROUPS_ADMIN_PAGES_ACTION_NONCE', 'pages-action-nonce');
define( 'GROUPS_ADMIN_PAGES_FILTER_NONCE', 'pages-filter-nonce' );

require_once( GROUPS_ADMIN_LIB . '/class-groups-pages-list-table.php');
require_once( GROUPS_ADMIN_LIB . '/groups-admin-pages-edit.php');

/**
 * Manage Groups: table of groups and add, edit, remove actions.
 */
function groups_admin_pages() {

        // Show the edit form if necessary
	if ( isset( $_POST['action'] ) ) {
	    switch( $_POST['action'] ) {
                case 'edit' :
                    if ( !( $page_id = groups_admin_pages_edit_submit() ) ) {
                        return groups_admin_groups_edit( $_POST['page_id'] );
                    } else {
                        $group = Groups_Group::read( $group_id );
                        Groups_Admin::add_message( sprintf( __( "The <em>%s</em> group has been created.", GROUPS_PLUGIN_DOMAIN ), stripslashes( wp_filter_nohtml_kses( $group->name ) ) ) );
                    } 
                    break;
            }
	} else if ( isset ( $_GET['action'] ) ) {
            switch( $_GET['action'] ) {
                case 'edit' :
                    if ( isset( $_GET['laboratory'] ) ) {
                        return groups_admin_pages_edit( $_GET['laboratory'] );
                    }
                    break;
            }
        }

        $list_table = new Groups_Pages_List_Table();
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            
            <div id="icon-users" class="icon32"><br/></div>
            <h2>Group Pages</h2>
            
            <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
            <form id="groups-pages-filter" method="get">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                <!-- Now we can render the completed list table -->
                <?php $list_table->display(); ?>
            </form>
            
        </div>
        <?php
} // function groups_admin_pages()


/**
 * Edit LD Courses Page
 */
add_action( 'add_meta_boxes', 'add_learndash_metaboxes' );
function add_learndash_metaboxes()
{
    add_meta_box('group_id', 'Associated Organization', 'group_id_callback', 'sfwd-courses', 'side', 'default');
}


function group_id_callback()
{
    $user_id = get_current_user_id();
    $user = new Groups_User( $user_id );
    $groups = $user->__get('groups');
    $selected = get_post_meta(get_the_ID(), 'group_id', true);

    wp_nonce_field(plugin_basename(__FILE__), 'group_id_nonce');
     
    $html = '<p class="description">';
        $html .= 'Associate this content with an organization.';
    $html .= '</p>';

    if ( count($groups) > 0 ) {
        $html .= '<select name="group_id" id="group_id"><option value="">Select from your groups:</option>';

        foreach ( $groups as $group ) {
            if ( $selected == $group->group_id ) {
                $html .= '<option name="' . $group->name . '" value="' . $group->group_id . '" selected="true">' . $group->name . '</option>';
            } else {
                $html .= '<option name="' . $group->name . '" value="' . $group->group_id . '">' . $group->name . '</option>';
            }
        }

        $html .= '</select>';
    } else {
        $html .= '<p>You are not a member of any organizations.</p>';
    }

    echo $html;
}

function save_group_id($id) {
 
    /* --- security verification --- */
    if(!wp_verify_nonce($_POST['group_id_nonce'], plugin_basename(__FILE__))) {
      return $id;
    } // end if
       
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $id;
    } // end if

    if (!empty( $_POST['group_id'] ) ) {
        update_post_meta( $id, 'group_id', $_POST['group_id'] );
    }
       
} // end save_custom_meta_data
add_action('save_post', 'save_group_id');
