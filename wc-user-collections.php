<?php
/*
Plugin Name: WC User Collections
Plugin URI: #
Description: Let user create a collection/collections of products and inquire from merchant or share it with friends.
Version: 1.0
Author: Logicfire
Author URI: #
License: GPLv2 or later
Text Domain: wcuc
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('WCUS_VERSION', '1.0');
define('WCUS_MINIMUM_WP_VERSION', '4.0');
define('WCUS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCUS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCUS_PLUGIN_ASSETS', plugin_dir_url(__FILE__) . 'assets/');

require_once WCUS_PLUGIN_DIR . 'inc/WCUC_Collections.php';
require_once WCUS_PLUGIN_DIR . 'inc/WCUC_Collection_Products.php';
require_once WCUS_PLUGIN_DIR . 'inc/WCUC_front_display.php';
require_once WCUS_PLUGIN_DIR . 'admin/WCUC_Admin.php';

add_action('wp_enqueue_scripts', 'wcuc_initialize_scripts');
function wcuc_initialize_scripts()
{
    wp_register_style('wcuc-bs-css', WCUS_PLUGIN_ASSETS . 'MDB_4_8_2/css/bootstrap.min.css');
    wp_register_style('wcuc-mdb-css', WCUS_PLUGIN_ASSETS . 'MDB_4_8_2/css/mdb.min.css');

    //wp_enqueue_style('wcuc-bs-css');
    //wp_enqueue_style('wcuc-mdb-css');

    wp_register_style('wcuc-css', WCUS_PLUGIN_ASSETS . 'css/style.css');
    wp_enqueue_style('wcuc-css');

    wp_register_script('wcuc-bs-js', WCUS_PLUGIN_ASSETS . 'MDB_4_8_2/js/bootstrap.min.js', array('jquery'), WCUS_VERSION, true);
    wp_register_script('wcuc-mdb-js', WCUS_PLUGIN_ASSETS . 'MDB_4_8_2/js/mdb.min.js', array('jquery'), WCUS_VERSION, true);
    wp_register_script('wcuc-mdb-select-js', WCUS_PLUGIN_ASSETS . 'MDB_4_8_2/js/material-select.js', array('jquery', 'wcuc-mdb-js'), WCUS_VERSION, true);

    //wp_enqueue_script('wcuc-bs-js');
    //wp_enqueue_script('wcuc-mdb-js');
    //wp_enqueue_script('wcuc-mdb-select-js');

    wp_enqueue_script('wcuc-js', WCUS_PLUGIN_ASSETS . 'js/functions.js', array('jquery'), WCUS_VERSION);
    wp_localize_script('wcuc-js', 'wcuc', array('ajaxurl' => admin_url('admin-ajax.php'), 'homeurl' => home_url('/')));
}

function wcuc_generate_unique_key($keys = null)
{
    $length = 6;
    $key = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length);
    //$code = mt_rand(100000, 999999);

    if ($keys) {
        $found_key = array_search($key, $keys);
        if ($found_key) {
            wcuc_generate_unique_key($keys);
        } else {
            return $key;
        }
    } else {
        return $key;
    }
}

function wcuc_first_collection($login)
{
    $user = get_user_by('login', $login);
    $user_id = $user->ID;

    $collections = get_user_meta($user_id, 'wcuc_user_collections', true);
    // var_dump($collections);
    // wp_die();
	if (!$collections) {
        $collections = array();
        $collections[wcuc_generate_unique_key()] = "My Collection";

        //Save first collection
        update_user_meta($user_id, 'wcuc_user_collections', $collections);
        //return $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);
    }
}

add_action('wp_login', 'wcuc_first_collection', 99);