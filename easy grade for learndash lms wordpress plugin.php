<?php
/*
 * Plugin Name: Easy Grade for learndash lms
 * Description: This plugin adds extra fields to make it easier for group leaders to grade quizzes or audio assignments from the submitted essay(or assignment) table.  
 * Version: 0.3
 * Author: Nader Makram
 * GitHub Plugin URI:  https://github.com/NaderMakram/easy-grade-for-learndash-lms-wordpress-plugin
 * Primary Branch: master
 * Release Asset: true
 */

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////



if (!class_exists('GitUpdater\GitUpdater')) {
    include_once 'git-updater/GitUpdater.php';
}


//  feature number #1 remove post action links for group leaders 
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

// feature number #2 add new column for the questoin in the submitted essays table
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



// feature number #3 rtl direction for submitted essays table
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


// feature number #4 Hide post action links
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


// feature number #5 Set session expiration time to 30 days

// Set session expiration time to 30 days (in seconds)
// add_filter( 'auth_cookie_expiration', 'extend_auth_cookie_expiration' );
// function extend_auth_cookie_expiration( $expiration ) {
    //     return 2592000; // 30 days in seconds
    // }
    

    

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////


// feature number #6 add new column for the questoin in the audio assignment
add_filter('manage_sfwd-assignment_posts_columns', 'custom_assignment_table_column');

// Callback function to add a new column
function custom_assignment_table_column($columns) {
    // Add a new column with a header
    $columns['custom_assignment_column'] = 'Question Value';
    
    // Return the modified columns array
    return $columns;
}

// Hook into the 'manage_posts_custom_column' action
add_action('manage_sfwd-assignment_posts_custom_column', 'populate_custom_assignment_table_column', 10, 2);

// Callback function to populate the new column cells
function populate_custom_assignment_table_column($column_name, $post_id) {
    if ($column_name === 'custom_assignment_column') {
        // Retrieve the value of the 'lesson_id' column
        $lesson_id = get_post_meta($post_id, 'lesson_id', true);
        
        // Query the wp_posts table to get the lesson content
        global $wpdb;
        $table_name = $wpdb->prefix . 'posts';
        $lesson_content = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM $table_name WHERE ID = %d", $lesson_id));
        
        // Output the value in the column cell
        echo $lesson_content;
    }
}




// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////


// feature number #7 add new column for the audio in the submitted assignment table
// Step 1: Add a new column to the 'sfwd-assignment' post type
add_filter('manage_sfwd-assignment_posts_columns', 'custom_sfwd_assignment_posts_table_column');

// Callback function to add a new column
function custom_sfwd_assignment_posts_table_column($columns) {
    // Add a new column with a header for the audio player
    $columns['custom_audio_column'] = 'Audio Player';
    
    // Return the modified columns array
    return $columns;
}

// Step 2: Hook into the 'manage_posts_custom_column' action for 'sfwd-assignment' post type
add_action('manage_sfwd-assignment_posts_custom_column', 'populate_custom_sfwd_assignment_posts_table_column', 10, 2);

// Callback function to populate the new column cells
function populate_custom_sfwd_assignment_posts_table_column($column_name, $post_id) {
    if ($column_name === 'custom_audio_column') {
        // Retrieve the post content
        $post_content = get_post_field('post_content', $post_id);
        
        // Use the pattern to extract the audio URL from the post_content
        $pattern = '/<a\s+(?:[^>]*?\s+)?href=("|\')(.*?)\1/';
        preg_match($pattern, $post_content, $matches);
        
        // Extract the audio URL from the match or set to empty string
        $audio_url = !empty($matches) && count($matches) >= 3 ? $matches[2] : '';
        
        // Output the audio player in the column cell
        echo '<audio controls>';
        echo '<source src="' . esc_attr($audio_url) . '" type="audio/mpeg">';
        echo 'Your browser does not support the audio element.';
        echo '</audio>';
    }
}







// ////////////////////////////////////////////////////////////////////////////
add_action('init', 'my_custom_plugin_git_updater');
function my_custom_plugin_git_updater()
{
    if (is_admin() && class_exists('GitUpdater\GitUpdater')) {
        $config = array(
            'slug' => plugin_basename(__FILE__),
            'proper_folder_name' => 'my-custom-plugin',
            'api_url' => 'https://api.github.com/repos/NaderMakram/easy-grade-for-learndash-lms-wordpress-plugin',
            'raw_url' => 'https://raw.githubusercontent.com/NaderMakram/easy-grade-for-learndash-lms-wordpress-plugin/main',
            'github_url' => 'https://github.com/NaderMakram/easy-grade-for-learndash-lms-wordpress-plugin',
            'zip_url' => 'https://github.com/NaderMakram/easy-grade-for-learndash-lms-wordpress-plugin/archive/main.zip',
            'sslverify' => true,
            'requires' => '5.0',
            'tested' => '5.8',
            'readme' => 'README.md',
            'access_token' => '', // Optional: Add your GitHub personal access token for private repositories.
        );

        new GitUpdater\GitUpdater($config);
    }
}
