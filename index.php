<?php
/*
Plugin name: PartnerComm Wordpress poll plugin
Plugin URI: https://johnfajardo.dev
Description: A simple plugin to add API endpoints for polls, developed for PartnerComm.
Author: John Fajardo
Author URI: https://johnfajardo.dev
Version: 1.0
*/

function plugin_install () {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // First we define our tables. We need 3, so let's create the names
    $pollsTable = $wpdb->prefix . "polls_" ."polls";
    $optionsTable = $wpdb->prefix . "polls_" ."options";
    $votesTable = $wpdb->prefix . "polls_" ."votes";

    // Let's now create the tables with the names we built

    // Poll is the "master container" of our little system
    $createPollTable = "CREATE TABLE $pollsTable (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        creationDate TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    // Then we need to create options that are tied to each poll
    $createOptionsTable = "CREATE TABLE $optionsTable (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        poll_id mediumInt(9) NOT NULL,
        name tinytext NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (poll_id) REFERENCES {$pollsTable} (id)
    ) $charset_collate;";

    // And finally, we need to store each vote as a row. Each vote belongs to a poll
    // and represents one option from it
    $createVotesTable = "CREATE TABLE $votesTable (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        poll_id mediumInt(9) NOT NULL,
        option_id mediumInt(9) NOT NULL,
        creationDate TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (poll_id) REFERENCES {$pollsTable} (id),
        FOREIGN KEY (option_id) REFERENCES {$optionsTable} (id)
    ) $charset_collate;";

    // We need to import the upgrade script to make the plugin create the tables upon activation:
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // And now let's execute our queries
    dbDelta($createPollTable);
    dbDelta($createOptionsTable);
    dbDelta($createVotesTable);
}

// However, these have just been definitions so far. Let's actually really really call them this time:
register_activation_hook( __FILE__, 'plugin_install' );

function addCustomStyles() {
    wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . 'assets/css/styles.css', false, '1.0.0' );
    wp_enqueue_style( 'custom_wp_admin_css' );
}

add_action('admin_enqueue_scripts', 'addCustomStyles'); 

// Now let's add a menu item to manage our polls:
// We add an action telling Wordpress what to add and the name of the function
add_action("admin_menu", "add_menu");

// And this is the function that contains our menu
// The last argument is the name of the function to execute:
function add_menu() {
    add_menu_page("Polls", "Polls", "administrator", "polls", "managePollsPage");
}

// And this is the function that gets called. We're just importing a file,
// which we'll use to create our polls and their items:
function managePollsPage() {
    include(plugin_dir_path(__FILE__) . '/add-poll.php');
}

// And finally, let's include the file where we define our new endpoints:
include(plugin_dir_path(__FILE__) . '/api-endpoints.php');