<?php
/*
 * Plugin Name: YOUR PLUGIN NAME
 */

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////


//  number #1 remove post action links for group leaders 
function remove_post_row_actions($actions) {
    global $post;

    // Check if the current user has the "group leader" role
    if (in_array('group_leader', wp_get_current_user()->roles)) {
        // Remove the unwanted actions
        unset($actions['edit']);
        unset($actions['view']);
        unset($actions['trash']);
        unset($actions['quick_edit']);
    }

    return $actions;
}
add_filter('post_row_actions', 'remove_post_row_actions', 10, 1);


// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// number #2 add new column for the questoin in the submitted essays table
add_filter('manage_sfwd-essays_posts_columns', 'custom_posts_table_column');

// Callback function to add a new column
function custom_posts_table_column($columns) {
    // Add a new column with a header
    $columns['custom_column'] = 'Question Value';

    // Return the modified columns array
    return $columns;
}

// Hook into the 'manage_posts_custom_column' action
add_action('manage_sfwd-essays_posts_custom_column', 'populate_custom_posts_table_column', 10, 2);

// Callback function to populate the new column cells
function populate_custom_posts_table_column($column_name, $post_id) {
    if ($column_name === 'custom_column') {
        // Retrieve the value of the 'question_id' column
        $question_id = get_post_meta($post_id, 'question_id', true);

        // Query the wp_learndash_pro_quiz_question table
        global $wpdb;
        $table_name = $wpdb->prefix . 'learndash_pro_quiz_question';
        $question = $wpdb->get_var($wpdb->prepare("SELECT question FROM $table_name WHERE id = %d", $question_id));

        // Output the value in the column cell
        echo $question;
    }
}



// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////



// number #3 rtl direction for submitted essays table
function add_custom_admin_css() {
    echo '<style>
    table [data-colname="Essay Question Title"], 
    table [data-colname="Question Value"] {
        direction: rtl;
        }
        </style>';
    }
    add_action('admin_head', 'add_custom_admin_css');

    

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////


// number #4 Hide post action links
function hide_posts_actions_for_group_leaders() {
    // Check if the current user has the 'group_leader' role
    if (current_user_can('group_leader')) {
        echo '<style>
        .row-actions {
            display: none;
        }
        </style>';
    }
}
add_action('admin_head', 'hide_posts_actions_for_group_leaders');




// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////


// number #5 Set session expiration time to 30 days

// Set session expiration time to 30 days (in seconds)
// add_filter( 'auth_cookie_expiration', 'extend_auth_cookie_expiration' );
// function extend_auth_cookie_expiration( $expiration ) {
//     return 2592000; // 30 days in seconds
// }