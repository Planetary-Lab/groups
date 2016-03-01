<?php
/**
 * groups-admin-spheres.php
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
define( 'GROUPS_ADMIN_SPHERES_NONCE_1', 'spheres-nonce-1');
define( 'GROUPS_ADMIN_SPHERES_NONCE_2', 'spheres-nonce-2');

require_once( GROUPS_ADMIN_LIB . '/class-groups-spheres-list-table.php');
require_once( GROUPS_ADMIN_LIB . '/groups-admin-spheres-add.php');
require_once( GROUPS_ADMIN_LIB . '/groups-admin-spheres-edit.php');

/**
 * Manage Groups: table of groups and add, edit, remove actions.
 */
function groups_admin_spheres() {

        // Show the edit form if necessary
	if ( isset( $_POST['action'] ) ) {
	    switch( $_POST['action'] ) {
                case 'edit' :
                    if ( !( $sphere_id = groups_admin_spheres_edit_submit() ) ) {
                        return groups_admin_spheres_edit( $_POST['sphere_id'] );
                    }
                    break;
                case 'add' :
                    if ( !( $sphere_id = groups_admin_spheres_add_submit() ) ) {
                        return groups_admin_spheres_add();
                    } else {
                        $sphere = get_term( $sphere_id, 'laboratory_sphere_of_science' );
                        Groups_Admin::add_message( sprintf( __( "The <em>%s</em> sphere has been created.", GROUPS_PLUGIN_DOMAIN ), stripslashes( wp_filter_nohtml_kses( $sphere->name ) ) ) );
                    }
                    break;
            }
	} else if ( isset ( $_GET['action'] ) ) {
            switch( $_GET['action'] ) {
                case 'edit' :
                    if ( isset( $_GET['sphere'] ) ) {
                        return groups_admin_spheres_edit( $_GET['sphere'] );
                    }
                    break;
                case 'add' :
                    return groups_admin_spheres_add();
                    break;
            }
        }

        $list_table = new Groups_Spheres_List_Table();
        $list_table->prepare_items();
	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'paged', $current_url );
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'category', $current_url );
        ?>
        <div class="wrap">
            
            <div id="icon-users" class="icon32"><br/></div>
            <h2>Laboratory Spheres</h2>
            <a title="Click to add a new sphere" class="add button" href="<?php echo esc_url( $current_url ); ?>&action=add"><span class="label">Add New Sphere of Science</span></a>

            <?php echo Groups_Admin::render_messages(); ?>
            
            
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
