<?php
/**
 * class-groups-pages-list-table.php
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

if(!class_exists('WP_List_Table')){
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Groups_Categories_List_Table extends WP_List_Table {
    function __construct(){
        global $status, $page;

        parent::__construct( array(
            'singular'  => 'laboratory category',     //singular name of the listed records
            'plural'    => 'laboratory categories',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }


    function column_default($item, $column_name){
        return print_r($item,true); //Show the whole array for troubleshooting purposes
    }

    function column_title($item){
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&category=%s">Edit</a>',$_REQUEST['page'],'edit',$item->term_id)
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item->name,
             /*$2%s*/ $item->term_id,
              /* %3$s */ $this->row_actions($actions) 
        );
    }

    function column_parent($item){
        if ( $item->parent ){
            $parent = get_term( $item->parent, 'laboratory_category' );

            //Return the title contents
            return sprintf('<a href="%1$s">%2$s</a>',
                /*$1%s*/ get_term_link( intval($parent->term_id), 'laboratory_category' ),
                 /*$2%s*/ $parent->name
            );
        } else {
            return 'None';
        }
    }

    function get_columns(){
        $columns = array(
            'title'     => 'Title',
            'parent'    => 'Parent Category'
        );
        return $columns;
    }

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden);
        
                
        $data = get_terms( 'laboratory_category', array( 'hide_empty' => 0 ) );

        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}
