<?php

/**
 * Created by PhpStorm.
 * User: Surfer
 * Date: 20/06/19
 * Time: 12:47 PM
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class WCUC_Admin
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    public function __construct(){
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'WC User Collections',
            'WC User Collections',
            'manage_options',
            'wcuc-collections',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'collection_options' );
        ?>
        <div class="wrap">
            <h1><?php _e('Collections Settings', 'wcuc');?></h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'wcuc_collection_settings' );
                do_settings_sections( 'wcuc-collections' );

                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {

        register_setting(
            'wcuc_collection_settings', // Option group
            'collection_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'wcuc_general_settings', // ID
            'Collections Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'wcuc-collections' // Page
        );

        add_settings_field(
            'page_id', // ID
            'Collections Page ID', // Title
            array( $this, 'page_id_callback' ), // Callback
            'wcuc-collections', // Page
            'wcuc_general_settings' // Section
        );
		add_settings_field(
            'inquire_admin_email', // ID
            'Inquire Admin Email', // Title
            array( $this, 'inquire_admin_email_callback' ), // Callback
            'wcuc-collections', // Page
            'wcuc_general_settings' // Section
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['page_id'] ) )
            $new_input['page_id'] = absint( $input['page_id'] );
		
		if( isset( $input['inquire_admin_email'] ) )
            $new_input['inquire_admin_email'] = sanitize_email( $input['inquire_admin_email'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print '';
        //var_dump($this->options);
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function page_id_callback()
    {
        printf(
            '<input type="text" id="page_id" name="collection_options[page_id]" value="%s" />',
            isset( $this->options['page_id'] ) ? esc_attr( $this->options['page_id']) : ''
        );
    }
	public function inquire_admin_email_callback()
    {
        printf(
            '<input type="text" id="inquire_admin_email" name="collection_options[inquire_admin_email]" value="%s" />',
            isset( $this->options['inquire_admin_email'] ) ? esc_attr( $this->options['inquire_admin_email']) : ''
        );
    }

}

if( is_admin() ) $wcuc_admin = new WCUC_Admin();