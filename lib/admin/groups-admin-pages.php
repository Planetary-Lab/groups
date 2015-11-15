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

/**
 * Manage Groups: table of groups and add, edit, remove actions.
 */
function groups_admin_pages() {

	if ( !current_user_can( GROUPS_ADMINISTER_GROUPS ) ) {
		wp_die( __( 'Access denied.', GROUPS_PLUGIN_DOMAIN ) );
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
 * Edit Group Pages Page
 */
// Create our meta boxes
add_action( 'add_meta_boxes', 'add_organization_metaboxes' );
function add_organization_metaboxes()
{
    add_meta_box('organization_logo', 'Organization Logo', 'organization_logo_callback', 'group_pages', 'side', 'default');
}


function organization_logo_callback()
{
    wp_nonce_field(plugin_basename(__FILE__), 'organization_logo_custom_nonce');
     
    $html = '<p class="description">';
        $html .= 'Upload organization logo here.';
    $html .= '</p>';
    $html .= '<input type="file" id="organization_logo" name="organization_logo" value="" size="25" />';

    // Grab the array of file information currently associated with the post
    $doc = get_post_meta(get_the_ID(), 'organization_logo', true);
     
    // Create the input box and set the file's URL as the text element's value
    $html .= '<img src="' . $doc['url'] . '" class="organization_logo" style="height: auto; width: 100%;"/>';
     
    // Display the 'Delete' option if a URL to a file exists
    if(strlen(trim($doc['url'])) > 0) {
        $html .= '<a href="javascript:;" id="organization_logo_delete">' . __('Delete File') . '</a>';
    } // endif 

    echo $html;
}

function save_custom_meta_data($id) {
 
    /* --- security verification --- */
    if(!wp_verify_nonce($_POST['organization_logo_custom_nonce'], plugin_basename(__FILE__))) {
      return $id;
    } // end if
       
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $id;
    } // end if
       
     
    // Make sure the file array isn't empty
    if(!empty($_FILES['organization_logo']['name'])) {
         
        // Setup the array of supported file types. In this case, it's just PDF.
        $supported_types = array('image/jpeg', 'image/png');
         
        // Get the file type of the upload
        $arr_file_type = wp_check_filetype(basename($_FILES['organization_logo']['name']));
        $uploaded_type = $arr_file_type['type'];
         
        // Check if the type is supported. If not, throw an error.
        if(in_array($uploaded_type, $supported_types)) {
 
            // Use the WordPress API to upload the file
            $upload = wp_upload_bits($_FILES['organization_logo']['name'], null, file_get_contents($_FILES['organization_logo']['tmp_name']));
     
            if(isset($upload['error']) && $upload['error'] != 0) {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            } else {
                add_post_meta($id, 'organization_logo', $upload);
                update_post_meta($id, 'organization_logo', $upload);     
            } // end if/else
 
        } else {
            wp_die("The file type that you've uploaded was: " . $uploaded_type);
        } // end if/else
     
    } else {
 
        // Grab a reference to the file associated with this post
        $doc = get_post_meta($id, 'organization_logo', true);
         
        // Grab the value for the URL to the file stored in the text element
        $delete_flag = get_post_meta($id, 'organization_logo_url', true);
         
        // Determine if a file is associated with this post and if the delete flag has been set (by clearing out the input box)
        if(strlen(trim($doc['url'])) > 0 && strlen(trim($delete_flag)) == 0) {
         
            // Attempt to remove the file. If deleting it fails, print a WordPress error.
            if(unlink($doc['file'])) {
                 
                // Delete succeeded so reset the WordPress meta data
                update_post_meta($id, 'organization_logo', null);
                update_post_meta($id, 'organization_logo_url', '');
                 
            } else {
                wp_die('There was an error trying to delete your file.');
            } // end if/el;se
             
        } // end if
 
    } // end if/else
} // end save_custom_meta_data
add_action('save_post', 'save_custom_meta_data');

function update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form
add_action('post_edit_form_tag', 'update_edit_form');


function add_custom_attachment_script() {
    wp_register_script('custom-attachment-script', plugins_url( 'groups/js/custom_attachment.js' ) );
    wp_enqueue_script('custom-attachment-script');
 
} // end add_custom_attachment_script
add_action('admin_enqueue_scripts', 'add_custom_attachment_script');

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
