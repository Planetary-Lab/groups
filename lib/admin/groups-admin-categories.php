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
define( 'GROUPS_GROUPS_PER_PAGE', 10 );
define( 'GROUPS_ADMIN_CATEGORIES_NONCE_1', 'categories-nonce-1');
define( 'GROUPS_ADMIN_CATEGORIES_NONCE_2', 'categories-nonce-2');

require_once( GROUPS_ADMIN_LIB . '/class-groups-categories-list-table.php');
require_once( GROUPS_ADMIN_LIB . '/groups-admin-categories-add.php');
require_once( GROUPS_ADMIN_LIB . '/groups-admin-categories-edit.php');

/**
 * Manage Groups: table of groups and add, edit, remove actions.
 */
function groups_admin_categories() {

        // Show the edit form if necessary
	if ( isset( $_POST['action'] ) ) {
	    switch( $_POST['action'] ) {
                case 'edit' :
                    if ( !( $cat_id = groups_admin_categories_edit_submit() ) ) {
                        return groups_admin_categories_edit( $_POST['cat_id'] );
                    }
                    break;
                case 'add' :
                    if ( !( $cat_id = groups_admin_categories_add_submit() ) ) {
                        return groups_admin_categories_add();
                    } else {
                        $cat = get_term( $cat_id, 'laboratory_category' );
                        Groups_Admin::add_message( sprintf( __( "The <em>%s</em> category has been created.", GROUPS_PLUGIN_DOMAIN ), stripslashes( wp_filter_nohtml_kses( $cat->name ) ) ) );
                    }
                    break;
            }
	} else if ( isset ( $_GET['action'] ) ) {
            switch( $_GET['action'] ) {
                case 'edit' :
                    if ( isset( $_GET['category'] ) ) {
                        return groups_admin_categories_edit( $_GET['category'] );
                    }
                    break;
                case 'add' :
                    return groups_admin_categories_add();
                    break;
            }
        }

        $list_table = new Groups_Categories_List_Table();
        $list_table->prepare_items();

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'paged', $current_url );
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'category', $current_url );
        ?>
        <div class="wrap">
            
            <div id="icon-users" class="icon32"><br/></div>
            <h2>Laboratory Categories</h2>
            <a title="Click to add a new category" class="add button" href="<?php echo esc_url( $current_url ); ?>&action=add"><span class="label">Add New Category</span></a>

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
