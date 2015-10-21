<?php

/*
Plugin Name: Adminizr
Plugin URI: http://on.tinternet.co.uk
Description: Customise the WordPress admin UI interface from the WordPress customizer
Version: 1.0.0
Author: ontiuk
Author URI: http://on.tinternet.co.uk
Text Domain: adminizr

------------------------------------------------------------------------
Copyright 2015 OnTiUK.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses.
*/

// Access restriction
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die();
}

// Add plugin defines
define( 'ADMINIZR_VERSION', '1.0.0' );
define( 'ADMINIZR_WP_VERSION', '4.0' );
define( 'ADMINIZR_CONTROLS', plugin_dir_path( __FILE__ ) . 'inc/controls' );

/**
 * Admin UI Customizer functionality
 *
 * @package WordPress
 */
final class Adminizr {

    /**
     * Adminizr panel name
     *
     * @var     string
     * @access  private
     */
    private $panel = 'adminizr_panel';

    /**
     * Default Adminizr panel priority
     *
     * @var     integer
     * @access  private
     */
    private $priority = 25;

    /**
     * Text domain id
     *
     * @var     string
     * @access  private
     */
    private $text = 'adminizr';

    /**
     * Do the roles allow it?
     *
     * @var     boolean $is_approved
     * @access  private
     */
    private $is_approved = FALSE;

    /**
     * Class constructor
     */
    public function __construct() {

        // Check WP Version
        if ( version_compare( get_bloginfo( 'version' ), ADMINIZR_WP_VERSION, '<' ) ) {
            add_action( 'admin_notices', array( $this, 'version_notice' ) );
            return;
        }

        // Set Admin panel id
        $this->panel = apply_filters( 'adminizr_panel', __( $this->panel, $this->text ) );

        // Add custom controls first
        add_action( 'customize_register', array( $this, 'load_controls' ), 0 );

        // Set up customizer menu
        add_action( 'customize_register', array( $this, 'register_menu' ) );

        // Roles settings actions & filters
        $this->roles();

        // theme settings actions & filters
        $this->theme();

        // header settings actions & filters
        $this->head();

        // footer settings actions & filters
        $this->foot();

        // dashboard settings actions & filters
        $this->dashboard();
        
        // screen settings actions & filters
        $this->screen();

        // layout settings actions & filters
        $this->layout();

        // columns settings actions & filters
        $this->columns();

        // editor settings actions & filters
        $this->editor();

        // menu settings actions & filters
        $this->menu();

        // other settings actions & filters
        $this->other();
    }

    /*********************************/
    /**  Core Functionality         **/
    /*********************************/

    /**
     * Load custom controls
     *
     * @access public
     */
    public function load_controls() {
        require_once( ADMINIZR_CONTROLS . '/checkbox-multiple.php' );
    }

    /**
     * Main register function
     *
     * @param   object $wp_customizer
     * @access  public
     */
    public function register_menu( $wp_customizer ) {

        // do the login panel
        $this->panels($wp_customizer);

        // do the sections & controls
        $this->sections($wp_customizer);
    }

    /**
     * Loginizr Panel
     *
     * @param   object  $wpc    WP_Customiser
     * @access  protected
     */
    protected function panels( $wpc ) { 

        //add panel
        $wpc->add_panel( $this->panel, array(
            'priority'       => apply_filters( 'adminizr_priority', $this->priority ),
            'capability'     => 'edit_theme_options',
            'title'          => apply_filters( 'adminizr_title', __( 'Admin Customizer', $this->text ) ),
            'description'    => apply_filters( 'adminizr_description', __( 'Customize the WordPress admin interface.', $this->text ) )
        ) );
    }

    /**
     * Add the Adminizr sections to the Adminizr panel
     *
     * @param   object  $wpc    WP_Customiser
     * @access  protected    
     */
    protected function sections( $wpc ) { 

        /*********************************/
        /**  Sections                   **/
        /*********************************/

        // Roles Section
        $wpc->add_section( 'adminizr_roles_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 5, 'roles' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Roles', $this->text), 'roles' ),
            'description'   => 'Set the roles to which the changes apply.',
            'panel'         => $this->panel
        ) );

        // Theme Section
        $wpc->add_section( 'adminizr_theme_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 10, 'theme' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Theme', $this->text), 'theme' ),
            'description'   => 'Set the theme styling settings.',
            'panel'         => $this->panel
        ) );

        // Header Section
        $wpc->add_section( 'adminizr_header_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 15, 'header' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Header', $this->text), 'header' ),
            'description'   => 'Set the admin menu bar settings.',
            'panel'     => $this->panel
        ) );

        // Footer Section
        $wpc->add_section( 'adminizr_footer_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 20, 'footer' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Footer', $this->text), 'footer' ),
            'description'   => 'Set the admin interface footer settings.',
            'panel'     => $this->panel
        ) );

        // Dashboard Section
        $wpc->add_section( 'adminizr_dashboard_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 25, 'dashboard' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Dashboard', $this->text), 'dashboard' ),
            'description'   => 'Set the admin dashboard settings. Will only apply to administrator roles.',
            'panel'         => $this->panel
        ) );

        // Screen Section
        $wpc->add_section( 'adminizr_screen_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 30, 'screen' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Screen', $this->text), 'screen' ),
            'description'   => 'Set the screen notices, messages & help.',
            'panel'         => $this->panel
        ) );

        // Layout Section
        $wpc->add_section( 'adminizr_layout_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 35, 'layout' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Layout & Metaboxes', $this->text), 'layout' ),
            'description'   => 'Set the layout & metabox settings.',
            'panel'         => $this->panel
        ) );

        // Columns Section
        $wpc->add_section( 'adminizr_columns_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 40, 'columns' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Columns & Listings', $this->text), 'columns' ),
            'description'   => 'Set the columns & listings settings.',
            'panel'         => $this->panel
        ) );

        // Editor Section
        $wpc->add_section( 'adminizr_editor_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 45, 'editor' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Editor', $this->text), 'editor' ),
            'description'   => 'Set the editor settings. Most of these settings apply to the Visual Editor and require this to be active, and not disabled in the user profile.',
            'panel'         => $this->panel
        ) );
        
        // Menu Section
        $wpc->add_section( 'adminizr_menu_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 50, 'menu' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Menu', $this->text), 'menu' ),
            'description'   => 'Set the admin menu settings.',
            'panel'         => $this->panel
        ) );

        // Meta Section
        $wpc->add_section( 'adminizr_meta_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 55, 'meta' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Meta', $this->text), 'meta' ),
            'description'   => 'Set the admin metadata settings.',
            'panel'         => $this->panel
        ) );

        // Other Section
        $wpc->add_section( 'adminizr_other_section', array(
            'priority'      => apply_filters( 'adminizr_section_priority', 60, 'other' ),
            'title'         => apply_filters( 'adminizr_section_title', __( 'Other', $this->text), 'other' ),
            'description'   => 'Set the miscellaneous settings.',
            'panel'         => $this->panel
        ) );

        /*********************************/
        /**  Roles                      **/
        /*********************************/

        // Roles Setting
        $wpc->add_setting( 'adminizr_roles', array(
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => array( $this, 'sanitize_roles' )
        ) );
        
        // Roles Control
        $wpc->add_control( new WP_Customize_Control_Checkbox_Multiple( $wpc, 'adminizr_roles', array(
            'label'         => __( 'Adminizr Roles', $this->text ),
            'section'       => 'adminizr_roles_section',
            'priority'      => 5,
            'settings'      => 'adminizr_roles',
            'choices'       => $this->get_roles(),
            'description'   => 'Settings in the other sections will be applied to users with one or more of the selected roles.'
        ) ) );

        /*********************************/
        /**  Theme                      **/
        /*********************************/

        /*********************************/
        /**  Header                     **/
        /*********************************/

        // Header Hide Setting
        $wpc->add_setting( 'adminizr_header_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Hide Control
        $wpc->add_control( 'adminizr_header_hide', array(
            'label'     => __( 'Hide Admin Bar In Front-End', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_header_section',
            'priority'  => 5,
            'settings'  => 'adminizr_header_hide'
        ) );

        // Header Hide Admin Setting
        $wpc->add_setting( 'adminizr_header_hide_admin', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Hide Control
        $wpc->add_control( 'adminizr_header_hide_admin', array(
            'label'     => __( 'Exclude Admin', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_header_section',
            'priority'  => 10,
            'settings'  => 'adminizr_header_hide_admin',
            'description'   => '<hr />'
        ) );

        // Header Show Setting
        $wpc->add_setting( 'adminizr_header_show', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Hide Control
        $wpc->add_control( 'adminizr_header_show', array(
            'label'         => __( 'Always show the admin bar', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_header_section',
            'priority'      => 15,
            'settings'      => 'adminizr_header_show',
            'description'   => 'Shows the admin bar for logged out users. Includes login link.<hr />'
        ) );

        // Header Logo Setting
        $wpc->add_setting( 'adminizr_header_logo', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Logo Control
        $wpc->add_control( new WP_Customize_Image_Control( $wpc, 'adminizr_header_logo', array(
            'label'     => __( 'Replace WordPress Logo', $this->text ),
            'section'   => 'adminizr_header_section',
            'priority'  => 20,
            'settings'  => 'adminizr_header_logo'
        ) ) );

        // Header Remove Logo
        $wpc->add_setting( 'adminizr_header_logo_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Remove Logo Control
        $wpc->add_control( 'adminizr_header_logo_hide', array(
            'label'     => __( 'Hide WordPress Logo', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_header_section',
            'priority'  => 25,
            'settings'  => 'adminizr_header_logo_hide',
            'description'   => '<hr />'
        ) );

        // Header Remove Site
        $wpc->add_setting( 'adminizr_header_site_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Remove Site Control
        $wpc->add_control( 'adminizr_header_site_hide', array(
            'label'         => __( 'Hide Site Name', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_header_section',
            'priority'      => 30,
            'settings'      => 'adminizr_header_site_hide',
            'description'   => 'Also hides the edit & visit site link.<br/><hr />'
        ) );

        // Header Remove My Account
        $wpc->add_setting( 'adminizr_header_account_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Remove My Account Control
        $wpc->add_control( 'adminizr_header_account_hide', array(
            'label'     => __( 'Hide My Account', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_header_section',
            'priority'  => 35,
            'settings'  => 'adminizr_header_account_hide'
        ) );

        // Header Remove Profile
        $wpc->add_setting( 'adminizr_header_profile_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Remove Profile Control
        $wpc->add_control( 'adminizr_header_profile_hide', array(
            'label'     => __( 'Hide Profile', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_header_section',
            'priority'  => 40,
            'settings'  => 'adminizr_header_profile_hide'
        ) );

        // Header Remove Search
        $wpc->add_setting( 'adminizr_header_search_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Remove Search Control
        $wpc->add_control( 'adminizr_header_search_hide', array(
            'label'         => __( 'Hide Search', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_header_section',
            'priority'      => 45,
            'settings'      => 'adminizr_header_search_hide',
            'description'   => '<br/><hr />'
        ) );

        // Header Remove Comments
        $wpc->add_setting( 'adminizr_header_comments_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Remove Comments Control
        $wpc->add_control( 'adminizr_header_comments_hide', array(
            'label'     => __( 'Hide Comments', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_header_section',
            'priority'  => 50,
            'settings'  => 'adminizr_header_comments_hide'
        ) );

        // Header Remove New 
        $wpc->add_setting( 'adminizr_header_new_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Remove New Control
        $wpc->add_control( 'adminizr_header_new_hide', array(
            'label'     => __('Hide New Content', $this->text),
            'type'      => 'checkbox',
            'section'   => 'adminizr_header_section',
            'priority'  => 55,
            'settings'  => 'adminizr_header_new_hide'
        ) );

        // Header Remove Updates
        $wpc->add_setting( 'adminizr_header_updates_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Remove Updates Control
        $wpc->add_control( 'adminizr_header_updates_hide', array(
            'label'     => __( 'Hide Updates', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_header_section',
            'priority'  => 60,
            'settings'  => 'adminizr_header_updates_hide',
            'description'   => '<br /><hr />'
        ) );

        // Header Confirm Logout
        $wpc->add_setting( 'adminizr_header_logout_confirm', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Header Logout Confirm Control
        $wpc->add_control( 'adminizr_header_logout_confirm', array(
            'label'     => __( 'Logout Confirm', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_header_section',
            'priority'  => 65,
            'settings'  => 'adminizr_header_logout_confirm'
        ) );

        /*********************************/
        /**  Footer                     **/
        /*********************************/

        // Footer Hide Setting
        $wpc->add_setting( 'adminizr_footer_hide', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Footer Hide Control
        $wpc->add_control( 'adminizr_footer_hide', array(
            'label'         => __( 'Hide Footer?', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_footer_section',
            'priority'      => 5,
            'settings'      => 'adminizr_footer_hide',
            'description'   => '<br/><hr/>'
        ) );

        // Footer Custom Version Section
        $wpc->add_setting( 'adminizr_footer_custom_version', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Footer Custom Version Control
        $wpc->add_control( 'adminizr_footer_custom_version', array(
            'label'     => __( 'Custom WordPress Version', $this->text ),
            'type'      => 'text',
            'section'   => 'adminizr_footer_section',
            'priority'  => 10,
            'settings'  => 'adminizr_footer_custom_version'
        ) );

        // Footer Hide Version Section
        $wpc->add_setting( 'adminizr_footer_hide_version', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Footer Hide Version Control
        $wpc->add_control( 'adminizr_footer_hide_version', array(
            'label'         => __( 'Hide WordPress Version?', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_footer_section',
            'priority'      => 15,
            'settings'      => 'adminizr_footer_hide_version',
            'description'   => '<br/><hr/>'
        ) );

        // Footer Custom Credits Section
        $wpc->add_setting( 'adminizr_footer_custom_credits', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Footer Custom Credits Section
        $wpc->add_control( 'adminizr_footer_custom_credits', array(
            'label'     => __( 'Custom WordPress Credits', $this->text ),
            'type'      => 'text',
            'section'   => 'adminizr_footer_section',
            'priority'  => 20,
            'settings'  => 'adminizr_footer_custom_credits'
        ) );

        // Footer Hide Credits Section
        $wpc->add_setting( 'adminizr_footer_hide_credits', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Footer Hide Credits Control
        $wpc->add_control( 'adminizr_footer_hide_credits', array(
            'label'         => __( 'Hide WordPress Credits?', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_footer_section',
            'priority'      => 25,
            'settings'      => 'adminizr_footer_hide_credits',
            'description'   => '<br/><hr/>'
        ) );

        // Footer Logo Setting
        $wpc->add_setting( 'adminizr_footer_logo', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Footer Logo Control
        $wpc->add_control( new WP_Customize_Image_Control( $wpc, 'adminizr_footer_logo', array(
            'label'     => __( 'Footer Logo', $this->text ),
            'section'   => 'adminizr_footer_section',
            'priority'  => 30,
            'settings'  => 'adminizr_footer_logo'
        ) ) );

       // Footer Background Color Setting
       $wpc->add_setting( 'adminizr_footer_bg_color', array(
            'default'       => apply_filters( 'adminizr_footer_bg_color', '' ),
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Footer Background Color Control
        $wpc->add_control( new WP_Customize_Color_Control( $wpc, 'adminizr_footer_bg_color', array(
            'label'     => __( 'Footer Background Color', $this->text ),
            'section'   => 'adminizr_footer_section',
            'priority'  => 35,
            'settings'  => 'adminizr_footer_bg_color'
        ) ) );

       // Footer Text Color Setting
       $wpc->add_setting( 'adminizr_footer_color', array(
            'default'       => apply_filters( 'adminizr_footer_color', '' ),
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Footer Text Color Control
        $wpc->add_control( new WP_Customize_Color_Control( $wpc, 'adminizr_footer_color', array(
            'label'     => __( 'Footer Text Color', $this->text ),
            'section'   => 'adminizr_footer_section',
            'priority'  => 40,
            'settings'  => 'adminizr_footer_color'
        ) ) );

        /**********************************/
        /**  Dashboard                   **/
        /**********************************/

        // Dashboard Columns
        $wpc->add_setting( 'adminizr_dashboard_columns', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Dashboard Columns
        $wpc->add_control( 'adminizr_dashboard_columns', array(
            'label'     => __( 'Show Dashboard Columns', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 1,
            'settings'  => 'adminizr_dashboard_columns',
            'description'   => '<br/><hr/>'
        ) );

        // Welcome Widget Setting
        $wpc->add_setting( 'adminizr_dashboard_welcome', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Welcome Widget Control
        $wpc->add_control( 'adminizr_dashboard_welcome', array(
            'label'     => __( 'Hide Welcome Widget', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 5,
            'settings'  => 'adminizr_dashboard_welcome'
        ) );

        // QuickPress Widget Setting
        $wpc->add_setting( 'adminizr_dashboard_quickpress', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // QuickPress Widget Control
        $wpc->add_control( 'adminizr_dashboard_quickpress', array(
            'label'     => __( 'Hide QuickPress Widget', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 10,
            'settings'  => 'adminizr_dashboard_quickpress'
        ) );

        // Activity Widget Setting
        $wpc->add_setting( 'adminizr_dashboard_activity', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options',
        ));

        // Welcome Widget Control
        $wpc->add_control( 'adminizr_dashboard_activity', array(
            'label'     => __( 'Hide Activity Widget?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 15,
            'settings'  => 'adminizr_dashboard_activity'
        ) );

        // Incoming Links Widget Setting
        $wpc->add_setting( 'adminizr_dashboard_links', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Incoming Links Widget Control
        $wpc->add_control( 'adminizr_dashboard_links', array(
            'label'     => __( 'Hide Incoming Links Widget?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 20,
            'settings'  => 'adminizr_dashboard_links'
        ) );

        // Right Now Widget Setting
        $wpc->add_setting( 'adminizr_dashboard_right_now', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Right Now Widget Control
        $wpc->add_control( 'adminizr_dashboard_right_now', array(
            'label'     => __( 'Hide Right Now Widget?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 25,
            'settings'  => 'adminizr_dashboard_right_now'
        ) );

        // Plugins Widget Setting
        $wpc->add_setting( 'adminizr_dashboard_plugins', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Plugins Widget Control
        $wpc->add_control( 'adminizr_dashboard_plugins', array(
            'label'     => __('Hide Plugins Widget?', $this->text),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 30,
            'settings'  => 'adminizr_dashboard_plugins'
        ) );

        // Recent Drafts Widget Setting
        $wpc->add_setting('adminizr_dashboard_recent_drafts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ));

        // Recent Drafts Widget Control
        $wpc->add_control('adminizr_dashboard_recent_drafts', array(
            'label'     => __('Hide Recent Drafts Widget?', $this->text),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 35,
            'settings'  => 'adminizr_dashboard_recent_drafts'
        ) );

        // Recent Comments Widget Setting
        $wpc->add_setting( 'adminizr_dashboard_recent_comments', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Recent Comments Widget Control
        $wpc->add_control( 'adminizr_dashboard_recent_comments', array(
            'label'     => __( 'Hide Recent Comments Widget?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 40,
            'settings'  => 'adminizr_dashboard_recent_comments'
        ) );

        // Primary Widget Setting
        $wpc->add_setting( 'adminizr_dashboard_primary', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Primary Widget Control
        $wpc->add_control( 'adminizr_dashboard_primary', array(
            'label'     => __( 'Hide Primary Widget?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 45,
            'settings'  => 'adminizr_dashboard_primary'
        ) );

        // Secondary Widget Setting
        $wpc->add_setting( 'adminizr_dashboard_secondary', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Welcome Widget Control
        $wpc->add_control( 'adminizr_dashboard_secondary', array(
            'label'     => __( 'Hide Secondary Widget?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_dashboard_section',
            'priority'  => 50,
            'settings'  => 'adminizr_dashboard_secondary'
        ) );

        /**********************************/
        /**  Screen                      **/
        /**********************************/

        // Screen Tabs Setting
        $wpc->add_setting( 'adminizr_screen_opts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Screen Tabs Control
        $wpc->add_control( 'adminizr_screen_opts', array(
            'label'     => __( 'Hide Screen Options?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_screen_section',
            'priority'  => 5,
            'settings'  => 'adminizr_screen_opts'
        ) );

        // Help Tabs Setting
        $wpc->add_setting( 'adminizr_screen_help', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Help Tabs Control
        $wpc->add_control( 'adminizr_screen_help', array(
            'label'     => __( 'Hide Help Tab?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_screen_section',
            'priority'  => 10,
            'settings'  => 'adminizr_screen_help'
        ) );

        // Screen & Help Admin Setting
        $wpc->add_setting( 'adminizr_screen_help_opts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Screen & Help Admin Control
        $wpc->add_control( 'adminizr_screen_help_opts', array(
            'label'         => __( 'Exclude Admin?', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_screen_section',
            'priority'      => 15,
            'settings'      => 'adminizr_screen_help_opts',
            'description'   => '<br/><hr />'
        ) );
 
        // Updates Notice Setting
        $wpc->add_setting( 'adminizr_screen_updates', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Updates Notice Control
        $wpc->add_control( 'adminizr_screen_updates', array(
            'label'     => __( 'Hide Updates Notice?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_screen_section',
            'priority'  => 20,
            'settings'  => 'adminizr_screen_updates'
        ) );

        // Updates Notice Admin Setting
        $wpc->add_setting( 'adminizr_screen_updates_admin', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        $wpc->add_control( 'adminizr_screen_updates_admin', array(
            'label'         => __( 'Exclude Admin?', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_screen_section',
            'priority'      => 25,
            'settings'      => 'adminizr_screen_updates_admin',
            'description'   => '<br/><hr />'
        ) );

        // Hide AutoGen Password
        $wpc->add_setting( 'adminizr_screen_pass_notice', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // AutoGen Password Notice Control
        $wpc->add_control( 'adminizr_screen_pass_notice', array(
            'label'         => __( 'Hide AutoGen Password Notice?', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_screen_section',
            'priority'      => 30,
            'settings'      => 'adminizr_screen_pass_notice',
            'description'   => '<br/><hr/>'
        ) );

        // Remove Trash/Bin in posts & pages
        $wpc->add_setting( 'adminizr_screen_no_trash', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Trash/Bin Control
        $wpc->add_control( 'adminizr_screen_no_trash', array(
            'label'     => __( 'Remove Trash / Bin Functionality?', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_screen_section',
            'priority'  => 35,
            'settings'  => 'adminizr_screen_no_trash'
        ) );

        // Empty Trash After x Days Setting
        $wpc->add_setting( 'adminizr_screen_empty_trash', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Empty Trash Control
        $wpc->add_control( 'adminizr_screen_empty_trash', array(
            'label'     => __( 'Empty Trash After X Days', $this->text ),
            'section'   => 'adminizr_screen_section',
            'priority'  => 40,
            'settings'  => 'adminizr_screen_empty_trash'
        ) );

        // Set Autosave Interval Setting
        $wpc->add_setting( 'adminizr_screen_autosave', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Set Autosave Control
        $wpc->add_control( 'adminizr_screen_autosave', array(
            'label'     => __( 'Set Autosave Interval', $this->text ),
            'section'   => 'adminizr_screen_section',
            'priority'  => 45,
            'settings'  => 'adminizr_screen_autosave'
        ) );

        // Limit Post Revisions
        $wpc->add_setting( 'adminizr_screen_revisions', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Limit Revisions Control
        $wpc->add_control( 'adminizr_screen_revisions', array(
            'label'     => __( 'Limit Post Revisions', $this->text ),
            'section'   => 'adminizr_screen_section',
            'priority'  => 50,
            'settings'  => 'adminizr_screen_revisions'
        ) );

        /**********************************/
        /**  Layout & Metaboxes          **/
        /**********************************/

        // Layout Slug Metabox
        $wpc->add_setting( 'adminizr_layout_slug_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Slug Metabox Control
        $wpc->add_control( 'adminizr_layout_slug_posts', array(
            'label'     => __( 'Remove Post Slug Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 5,
            'settings'  => 'adminizr_layout_slug_posts'
        ) );

        // Layout Submit Metabox
        $wpc->add_setting( 'adminizr_layout_submit_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Submit Metabox Control
        $wpc->add_control( 'adminizr_layout_submit_posts', array(
            'label'     => __( 'Remove Post Submit Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 10,
            'settings'  => 'adminizr_layout_submit_posts'
        ) );

        // Layout Author Metabox
        $wpc->add_setting( 'adminizr_layout_author_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Author Metabox Control
        $wpc->add_control( 'adminizr_layout_author_posts', array(
            'label'     => __( 'Remove Post Author Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 15,
            'settings'  => 'adminizr_layout_author_posts'
        ) );

        // Layout Categories Metabox
        $wpc->add_setting( 'adminizr_layout_category_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Author Metabox Control
        $wpc->add_control( 'adminizr_layout_category_posts', array(
            'label'     => __( 'Remove Post Category Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 20,
            'settings'  => 'adminizr_layout_category_posts'
        ) );

        // Layout Comments Metabox
        $wpc->add_setting( 'adminizr_layout_comments_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Comments Metabox Control
        $wpc->add_control( 'adminizr_layout_comments_posts', array(
            'label'     => __( 'Remove Post Comments Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 25,
            'settings'  => 'adminizr_layout_comments_posts'
        ) );

        // Layout Formats Metabox
        $wpc->add_setting( 'adminizr_layout_formats_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Comments Metabox Control
        $wpc->add_control( 'adminizr_layout_formats_posts', array(
            'label'     => __( 'Remove Post Formats Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 30,
            'settings'  => 'adminizr_layout_formats_posts'
        ) );

        // Layout Attributes Metabox
        $wpc->add_setting( 'adminizr_layout_attributes_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Comments Metabox Control
        $wpc->add_control( 'adminizr_layout_attributes_posts', array(
            'label'     => __( 'Remove Post Attributes Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 35,
            'settings'  => 'adminizr_layout_attributes_posts'
        ) );

        // Layout Custom Metabox
        $wpc->add_setting( 'adminizr_layout_custom_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Custom Metabox Control
        $wpc->add_control( 'adminizr_layout_custom_posts', array(
            'label'     => __( 'Remove Post Custom Fields Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 40,
            'settings'  => 'adminizr_layout_custom_posts'
        ) );

        // Layout Excerpt Metabox
        $wpc->add_setting( 'adminizr_layout_excerpt_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Excerpt Metabox Control
        $wpc->add_control( 'adminizr_layout_excerpt_posts', array(
            'label'     => __( 'Remove Post Excerpt Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 45,
            'settings'  => 'adminizr_layout_excerpt_posts'
        ) );

        // Layout Featured  Metabox
        $wpc->add_setting( 'adminizr_layout_featured_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Featured Metabox Control
        $wpc->add_control( 'adminizr_layout_featured_posts', array(
            'label'     => __( 'Remove Post Feat. Image Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 50,
            'settings'  => 'adminizr_layout_featured_posts'
        ) );

        // Layout Revisions Metabox
        $wpc->add_setting( 'adminizr_layout_revisions_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Revisions Metabox Control
        $wpc->add_control( 'adminizr_layout_revisions_posts', array(
            'label'     => __( 'Remove Post Revisions Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 55,
            'settings'  => 'adminizr_layout_revisions_posts'
        ) );

        // Layout Tags Metabox
        $wpc->add_setting( 'adminizr_layout_tags_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Submit Metabox Control
        $wpc->add_control( 'adminizr_layout_tags_posts', array(
            'label'     => __( 'Remove Post Tags Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 60,
            'settings'  => 'adminizr_layout_tags_posts'
        ) );

        // Layout Trackbacks Metabox
        $wpc->add_setting( 'adminizr_layout_trackbacks_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Trackbacks Metabox Control
        $wpc->add_control( 'adminizr_layout_trackbacks_posts', array(
            'label'         => __( 'Remove Post Trackbacks Metabox', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_layout_section',
            'priority'      => 65,
            'settings'      => 'adminizr_layout_trackbacks_posts',
            'description'   => '<br/><hr/>'
        ) );

        // Layout Slug Metabox
        $wpc->add_setting( 'adminizr_layout_slug_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Slug Metabox Control
        $wpc->add_control( 'adminizr_layout_slug_pages', array(
            'label'     => __( 'Remove Page Slug Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 70,
            'settings'  => 'adminizr_layout_slug_pages'
        ) );

        // Layout Submit Metabox
        $wpc->add_setting( 'adminizr_layout_submit_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Submit Metabox Control
        $wpc->add_control( 'adminizr_layout_submit_pages', array(
            'label'     => __( 'Remove Post Submit Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 75,
            'settings'  => 'adminizr_layout_submit_pages'
        ) );

        // Layout Author Metabox
        $wpc->add_setting( 'adminizr_layout_author_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Author Metabox Control
        $wpc->add_control( 'adminizr_layout_author_pages', array(
            'label'     => __( 'Remove Page Author Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 80,
            'settings'  => 'adminizr_layout_author_pages'
        ) );

        // Layout Comments Metabox
        $wpc->add_setting( 'adminizr_layout_comments_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Comments Metabox Control
        $wpc->add_control( 'adminizr_layout_comments_pages', array(
            'label'     => __( 'Remove Page Comments Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 85,
            'settings'  => 'adminizr_layout_comments_pages'
        ) );

        // Layout Attributes Metabox
        $wpc->add_setting( 'adminizr_layout_attributes_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Attributes Metabox Control
        $wpc->add_control( 'adminizr_layout_attributes_pages', array(
            'label'     => __( 'Remove Page Attributes Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 90,
            'settings'  => 'adminizr_layout_attributes_pages'
        ) );

        // Layout Custom Metabox
        $wpc->add_setting( 'adminizr_layout_custom_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Custom Metabox Control
        $wpc->add_control( 'adminizr_layout_custom_pages', array(
            'label'     => __( 'Remove Page Custom Fields Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 95,
            'settings'  => 'adminizr_layout_custom_pages'
        ) );

        // Layout Featured Images Metabox
        $wpc->add_setting( 'adminizr_layout_featured_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Featured Images Metabox Control
        $wpc->add_control( 'adminizr_layout_featured_pages', array(
            'label'     => __( 'Remove Page Feat. Image Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 100,
            'settings'  => 'adminizr_layout_featured_pages'
        ) );

        // Layout Revisions Metabox
        $wpc->add_setting( 'adminizr_layout_revisions_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Revisions Metabox Control
        $wpc->add_control( 'adminizr_layout_revisions_pages', array(
            'label'     => __( 'Remove Page Revisions Metabox', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_layout_section',
            'priority'  => 105,
            'settings'  => 'adminizr_layout_revisions_pages'
        ) );

        // Layout Trackbacks Metabox
        $wpc->add_setting( 'adminizr_layout_trackbacks_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Trackbacks Metabox Control
        $wpc->add_control( 'adminizr_layout_trackbacks_pages', array(
            'label'         => __( 'Remove Page Trackbacks Metabox', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_layout_section',
            'priority'      => 110,
            'settings'      => 'adminizr_layout_trackbacks_pages',
            'description'   => '<br/><hr/>'
        ) );

        // Layout Links Metabox
        $wpc->add_setting( 'adminizr_layout_box_links', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Layout Links Metabox Control
        $wpc->add_control( 'adminizr_layout_box_links', array(
            'label'         => __( 'Remove Links Metaboxes', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_layout_section',
            'priority'      => 115,
            'settings'      => 'adminizr_layout_box_links'
        ) );

        /**********************************/
        /**  Columns & Listings          **/
        /**********************************/

        // Columns Checkbox Posts
        $wpc->add_setting( 'adminizr_columns_checkbox_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Checkbox Posts Control
        $wpc->add_control( 'adminizr_columns_checkbox_posts', array(
            'label'     => __( 'Hide Checkbox Column In Posts List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 5,
            'settings'  => 'adminizr_columns_checkbox_posts'
        ) );

        // Columns Title Posts
        $wpc->add_setting( 'adminizr_columns_title_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Title Posts Control
        $wpc->add_control( 'adminizr_columns_title_posts', array(
            'label'     => __( 'Hide Title Column In Posts List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 10,
            'settings'  => 'adminizr_columns_title_posts'
        ) );

        // Columns Author Posts
        $wpc->add_setting( 'adminizr_columns_author_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Author Posts Control
        $wpc->add_control( 'adminizr_columns_author_posts', array(
            'label'     => __( 'Hide Author Column In Posts List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 15,
            'settings'  => 'adminizr_columns_author_posts'
        ) );

        // Columns Categories Posts
        $wpc->add_setting( 'adminizr_columns_categories_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Categories Posts Control
        $wpc->add_control( 'adminizr_columns_categories_posts', array(
            'label'     => __( 'Hide Categories Column In Posts List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 20,
            'settings'  => 'adminizr_columns_categories_posts'
        ) );

        // Columns Tags Posts
        $wpc->add_setting( 'adminizr_columns_tags_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Tags Posts Control
        $wpc->add_control( 'adminizr_columns_tags_posts', array(
            'label'     => __( 'Hide Tags Column In Posts List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 25,
            'settings'  => 'adminizr_columns_tags_posts'
        ) );

        // Columns Comments Posts
        $wpc->add_setting( 'adminizr_columns_comments_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Comments Posts Control
        $wpc->add_control( 'adminizr_columns_comments_posts', array(
            'label'     => __( 'Hide Comments Column In Posts List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 30,
            'settings'  => 'adminizr_columns_comments_posts'
        ) );

        // Columns Date Posts
        $wpc->add_setting( 'adminizr_columns_date_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Checkbox Posts Control
        $wpc->add_control( 'adminizr_columns_date_posts', array(
            'label'         => __( 'Hide Date Column In Posts List', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_columns_section',
            'priority'      => 35,
            'settings'      => 'adminizr_columns_date_posts',
            'description'   => '<br /><hr />'
        ));

        // Columns Checkbox Pages
        $wpc->add_setting( 'adminizr_columns_checkbox_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Checkbox Pages Control
        $wpc->add_control( 'adminizr_columns_checkbox_pages', array(
            'label'     => __( 'Hide Checkbox Column In Pages List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 40,
            'settings'  => 'adminizr_columns_checkbox_pages'
        ) );

        // Columns Title Pages
        $wpc->add_setting( 'adminizr_columns_title_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Title Pages Control
        $wpc->add_control( 'adminizr_columns_title_pages', array(
            'label'     => __( 'Hide Title Column In Pages List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 45,
            'settings'  => 'adminizr_columns_title_pages'
        ) );

        // Columns Author Pages
        $wpc->add_setting( 'adminizr_columns_author_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Author Posts Control
        $wpc->add_control( 'adminizr_columns_author_pages', array(
            'label'     => __('Hide Author Column In Pages List', $this->text),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 50,
            'settings'  => 'adminizr_columns_author_pages'
        ));

        // Columns Categories Pages
        $wpc->add_setting( 'adminizr_columns_categories_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Categories Pages Control
        $wpc->add_control( 'adminizr_columns_categories_pages', array(
            'label'     => __( 'Hide Categories Column In Pages List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 55,
            'settings'  => 'adminizr_columns_categories_pages'
        ) );

        // Columns Tags Pages
        $wpc->add_setting( 'adminizr_columns_tags_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Tags Pages Control
        $wpc->add_control( 'adminizr_columns_tags_pages', array(
            'label'     => __( 'Hide Tags Column In Pages List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 60,
            'settings'  => 'adminizr_columns_tags_pages'
        ) );

        // Columns Comments Pages
        $wpc->add_setting( 'adminizr_columns_comments_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Comments Pages Control
        $wpc->add_control( 'adminizr_columns_comments_pages', array(
            'label'     => __( 'Hide Comments Column In Pages List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 65,
            'settings'  => 'adminizr_columns_comments_pages'
        ) );

        // Columns Date Pages
        $wpc->add_setting( 'adminizr_columns_date_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Checkbox Pages Control
        $wpc->add_control( 'adminizr_columns_date_pages', array(
            'label'         => __( 'Hide Date Column In Pages List', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_columns_section',
            'priority'      => 70,
            'settings'      => 'adminizr_columns_date_pages',
            'description'   => '<br /><hr />'
        ) );

        // Columns Checkbox Media
        $wpc->add_setting( 'adminizr_columns_checkbox_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Checkbox Media Control
        $wpc->add_control( 'adminizr_columns_checkbox_media', array(
            'label'     => __( 'Hide Checkbox Column In Media List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 75,
            'settings'  => 'adminizr_columns_checkbox_media'
        ) );

        // Columns Icon Media
        $wpc->add_setting( 'adminizr_columns_icon_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Icon Media Control
        $wpc->add_control( 'adminizr_columns_icon_media', array(
            'label'     => __( 'Hide Icon Column In Media List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 80,
            'settings'  => 'adminizr_columns_icon_media'
        ) );

        // Columns Title Media
        $wpc->add_setting( 'adminizr_columns_title_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Title Media Control
        $wpc->add_control( 'adminizr_columns_title_media', array(
            'label'     => __( 'Hide Title Column In Media List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 85,
            'settings'  => 'adminizr_columns_title_media'
        ) );

        // Columns Author Media
        $wpc->add_setting( 'adminizr_columns_author_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Author Media Control
        $wpc->add_control( 'adminizr_columns_author_media', array(
            'label'     => __( 'Hide Author Column In Media List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 90,
            'settings'  => 'adminizr_columns_author_media'
        ) );

        // Columns Parent Media
        $wpc->add_setting( 'adminizr_columns_parent_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Parent Media Control
        $wpc->add_control( 'adminizr_columns_parent_media', array(
            'label'     => __( 'Hide Parent Column In Media List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 95,
            'settings'  => 'adminizr_columns_parent_media'
        ) );

        // Columns Comments Media
        $wpc->add_setting( 'adminizr_columns_comments_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Comments Media Control
        $wpc->add_control( 'adminizr_columns_comments_media', array(
            'label'     => __( 'Hide Comments Column In Media List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 100,
            'settings'  => 'adminizr_columns_comments_media'
        ) );

        // Columns Date Media
        $wpc->add_setting( 'adminizr_columns_date_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Columns Date Media Control
        $wpc->add_control( 'adminizr_columns_date_media', array(
            'label'     => __( 'Hide Date Column In Media List', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_columns_section',
            'priority'  => 105,
            'settings'  => 'adminizr_columns_date_media'
        ) );

        /**********************************/
        /**  Editor                      **/
        /**********************************/

        // Editor Font Size Setting
        $wpc->add_setting( 'adminizr_editor_font_size', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Font Size Control
        $wpc->add_control( 'adminizr_editor_font_size', array(
            'label'     => __( 'Editor Font Size Select', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 5,
            'settings'  => 'adminizr_editor_font_size'
        ) );

        // Editor Font Family Setting
        $wpc->add_setting( 'adminizr_editor_font_family', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Font Family Control
        $wpc->add_control( 'adminizr_editor_font_family', array(
            'label'     => __( 'Editor Font Family Select', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 10,
            'settings'  => 'adminizr_editor_font_family'
        ) );

        // Editor Formats Setting
        $wpc->add_setting( 'adminizr_editor_formats', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Font Family Control
        $wpc->add_control( 'adminizr_editor_formats', array(
            'label'     => __( 'Editor Formats Select', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 15,
            'settings'  => 'adminizr_editor_formats'
        ) );

        // Editor Shortlink
        $wpc->add_setting( 'adminizr_editor_shortlink', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Shortlink
        $wpc->add_control( 'adminizr_editor_shortlink', array(
            'label'     => __( 'Editor Remove Get Shortlink', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 20,
            'settings'  => 'adminizr_editor_shortlink'
        ) );

        // Editor New Document
        $wpc->add_setting( 'adminizr_editor_new_btn', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor New Document Control
        $wpc->add_control( 'adminizr_editor_new_btn', array(
            'label'     => __( 'Editor New Document Button', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 25,
            'settings'  => 'adminizr_editor_new_btn'
        ) );

        // Editor Cut Setting
        $wpc->add_setting( 'adminizr_editor_cut_btn', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Cut Control
        $wpc->add_control( 'adminizr_editor_cut_btn', array(
            'label'     => __( 'Editor Cut Button', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 30,
            'settings'  => 'adminizr_editor_cut_btn'
        ) );

        // Editor Copy Setting
        $wpc->add_setting( 'adminizr_editor_copy_btn', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Copy Control
        $wpc->add_control( 'adminizr_editor_copy_btn', array(
            'label'     => __( 'Editor Copy Button', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 35,
            'settings'  => 'adminizr_editor_copy_btn'
        ) );

        // Editor Paste Setting
        $wpc->add_setting( 'adminizr_editor_paste_btn', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Paste Control
        $wpc->add_control( 'adminizr_editor_paste_btn', array(
            'label'     => __( 'Editor Paste Button', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 40,
            'settings'  => 'adminizr_editor_paste_btn'
        ) );

        // Editor BG Color Setting
        $wpc->add_setting( 'adminizr_editor_bg_color_btn', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor BG Color Control
        $wpc->add_control( 'adminizr_editor_bg_color_btn', array(
            'label'         => __( 'Editor BG Color Button', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_editor_section',
            'priority'      => 45,
            'settings'      => 'adminizr_editor_bg_color_btn',
            'description'   => '<br/><hr/>'
        ) );

        // Editor Modal Setting
        $wpc->add_setting( 'adminizr_editor_modal_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Modal Control
        $wpc->add_control( 'adminizr_editor_modal_media', array(
            'label'     => __( 'Remove Insert Media In Media Modal', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 50,
            'settings'  => 'adminizr_editor_modal_media'
        ) );

        // Editor Modal Setting
        $wpc->add_setting( 'adminizr_editor_modal_uploads', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Modal Control
        $wpc->add_control( 'adminizr_editor_modal_uploads', array(
            'label'     => __( 'Remove Upload Files In Media Modal', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 55,
            'settings'  => 'adminizr_editor_modal_uploads'
        ) );

        // Editor Media Setting
        $wpc->add_setting( 'adminizr_editor_modal_library', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Modal Control
        $wpc->add_control( 'adminizr_editor_modal_library', array(
            'label'     => __( 'Remove Media Library In Media Modal', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 60,
            'settings'  => 'adminizr_editor_modal_library'
        ) );

        // Editor Modal Gallery
        $wpc->add_setting( 'adminizr_editor_modal_gallery', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Modal Gallery Control
        $wpc->add_control( 'adminizr_editor_modal_gallery', array(
            'label'     => __( 'Remove Create Gallery In Media Modal', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 65,
            'settings'  => 'adminizr_editor_modal_gallery'
        ) );

        // Editor Modal Playlist
        $wpc->add_setting( 'adminizr_editor_modal_playlist', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Modal Gallery Control
        $wpc->add_control( 'adminizr_editor_modal_playlist', array(
            'label'     => __( 'Remove Create Playlist In Media Modal', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 70,
            'settings'  => 'adminizr_editor_modal_playlist'
        ) );

        // Editor Modal Featured
        $wpc->add_setting( 'adminizr_editor_modal_featured', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Modal Gallery Control
        $wpc->add_control( 'adminizr_editor_modal_featured', array(
            'label'     => __( 'Remove Set Featured Image In Media Modal', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 75,
            'settings'  => 'adminizr_editor_modal_featured'
        ) );

        // Editor Modal Image URL
        $wpc->add_setting( 'adminizr_editor_modal_url', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Editor Modal Gallery Control
        $wpc->add_control( 'adminizr_editor_modal_url', array(
            'label'     => __( 'Remove Insert From URL In Media Modal', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_editor_section',
            'priority'  => 80,
            'settings'  => 'adminizr_editor_modal_url'
        ) );

        /**********************************/
        /**  Menu                        **/
        /**********************************/

        // Rename Dashboard Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_dashboard', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Dashboard Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_dashboard', array(
            'label'     => __( 'Rename Dashboard', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 5,
            'settings'  => 'adminizr_menu_rename_dashboard'
        ) );

        // Rename Posts Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Posts Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_posts', array(
            'label'     => __( 'Rename Posts', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 15,
            'settings'  => 'adminizr_menu_rename_posts'
        ) );

        // Rename Media Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Media Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_media', array(
            'label'     => __( 'Rename Media', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 25,
            'settings'  => 'adminizr_menu_rename_media'
        ) );

        // Rename Links Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_links', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Links Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_links', array(
            'label'     => __( 'Rename Links', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 35,
            'settings'  => 'adminizr_menu_rename_links'
        ) );

        // Rename Pages Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Pages Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_pages', array(
            'label'     => __( 'Rename Pages', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 45,
            'settings'  => 'adminizr_menu_rename_pages'
        ) );

        // Rename Comments Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_comments', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Comments Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_comments', array(
            'label'     => __( 'Rename Comments', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 55,
            'settings'  => 'adminizr_menu_rename_comments'
        ) );

        // Rename Appearance Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_appearance', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Dashboard Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_appearance', array(
            'label'     => __( 'Rename Appearance', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 65,
            'settings'  => 'adminizr_menu_rename_appearance'
        ) );

        // Rename Plugins Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_plugins', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Plugins Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_plugins', array(
            'label'     => __( 'Rename Plugins', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 75,
            'settings'  => 'adminizr_menu_rename_plugins'
        ) );

        // Rename Users Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_users', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Users Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_users', array(
            'label'     => __( 'Rename Users', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 85,
            'settings'  => 'adminizr_menu_rename_users'
        ) );

        // Rename Tools Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_tools', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Tools Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_tools', array(
            'label'     => __( 'Rename Tools', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 95,
            'settings'  => 'adminizr_menu_rename_tools'
        ) );

        // Rename Settings Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_rename_settings', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Rename Dashboard Menu Item Control
        $wpc->add_control( 'adminizr_menu_rename_settings', array(
            'label'     => __( 'Rename Settings', $this->text ),
            'section'   => 'adminizr_menu_section',
            'priority'  => 105,
            'settings'  => 'adminizr_menu_rename_settings'
        ) );
 
        // Remove Dashboard Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_dashboard', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Dashboard Menu Item Control
        $wpc->add_control( 'adminizr_menu_dashboard', array(
            'label'         => __( 'Remove Dashboard', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 10,
            'settings'      => 'adminizr_menu_dashboard',
            'description'   => '<br/><hr/>'
        ) );

        // Remove Posts Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_posts', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Posts Menu Item Control
        $wpc->add_control( 'adminizr_menu_posts', array(
            'label'         => __( 'Remove Posts', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 20,
            'settings'      => 'adminizr_menu_posts',
            'description'  => '<br/><hr/>'
        ) );

        // Remove Media Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_media', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Media Menu Item Control
        $wpc->add_control( 'adminizr_menu_media', array(
            'label'         => __( 'Remove Media', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 30,
            'settings'      => 'adminizr_menu_media',
            'description'   => '<br/><hr/>'
        ) );

        // Remove Links Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_links', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Media Menu Item Control
        $wpc->add_control( 'adminizr_menu_links', array(
            'label'         => __( 'Remove Links', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 40,
            'settings'      => 'adminizr_menu_links',
            'description'   => '<br/><hr/>'
        ) );

        // Remove Pages Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_pages', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Pages Menu Item Control
        $wpc->add_control( 'adminizr_menu_pages', array(
            'label'         => __( 'Remove Pages', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 50,
            'settings'      => 'adminizr_menu_pages',
            'description'   => '<br/><hr/>'
        ) );

        // Remove Comments Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_comments', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Comments Menu Item Control
        $wpc->add_control('adminizr_menu_comments', array(
            'label'         => __( 'Remove Comments', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 60,
            'settings'      => 'adminizr_menu_comments',
            'description'   => '<br/><hr/>'
        ) );

        // Remove Appearance Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_appearance', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Appearance Menu Item Control? Doh!
        $wpc->add_control( 'adminizr_menu_appearance', array(
            'label'         => __( 'Remove Appearance', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 70,
            'settings'      => 'adminizr_menu_appearance',
            'description'   => '<br/><hr/>'
        ) );

        // Remove Plugins Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_plugins', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Plugins Menu Item Control
        $wpc->add_control( 'adminizr_menu_plugins', array(
            'label'         => __( 'Remove Plugins', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 80,
            'settings'      => 'adminizr_menu_plugins',
            'description'   => '<br/><hr/>'
        ) );

        // Remove Users Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_users', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Users Menu Item Control
        $wpc->add_control( 'adminizr_menu_users', array(
            'label'         => __( 'Remove Users', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 90,
            'settings'      => 'adminizr_menu_users',
            'description'   => '<br/><hr/>'
        ) );

        // Remove Tools Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_tools', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Tools Menu Item Control
        $wpc->add_control( 'adminizr_menu_tools', array(
            'label'         => __( 'Remove Tools', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 100,
            'settings'      => 'adminizr_menu_tools',
            'description'   => '<br/><hr/>'
        ));

        // Remove Settings Menu Item Setting
        $wpc->add_setting( 'adminizr_menu_settings', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Settings Menu Item Control
        $wpc->add_control( 'adminizr_menu_settings', array(
            'label'         => __( 'Remove Settings', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_menu_section',
            'priority'      => 110,
            'settings'      => 'adminizr_menu_settings',
            'description'   => '<br/><hr/>'
        ) );

        /**********************************/
        /** Meta                         **/
        /**********************************/

        // Remove WP_Generator
        $wpc->add_setting( 'adminizr_meta_wp_generator', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove WP_Generator Control
        $wpc->add_control( 'adminizr_meta_wp_generator', array(
            'label'         => __( 'Hide wp_generator tag', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_meta_section',
            'priority'      => 5,
            'settings'      => 'adminizr_meta_wp_generator',
            'description'   => 'meta name="generator" ... '
        ) );

        // Remove wlwmanifest_link
        $wpc->add_setting( 'adminizr_meta_wlwmanifest_link', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove WP_Generator Control
        $wpc->add_control( 'adminizr_meta_wlwmanifest_link', array(
            'label'         => __( 'Hide wlwmanifest_link tag', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_meta_section',
            'priority'      => 10,
            'settings'      => 'adminizr_meta_wlwmanifest_link',
            'description'   => 'link rel="wlwmanifest" type="application/wlwmanifest+xml" ... '
        ) );

        // Remove RSD_Link
        $wpc->add_setting( 'adminizr_meta_rsd_link', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove WP_Generator Control
        $wpc->add_control( 'adminizr_meta_rsd_link', array(
            'label'         => __( 'Hide rsd_link tag', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_meta_section',
            'priority'      => 15,
            'settings'      => 'adminizr_meta_rsd_link',
            'description'   => 'link rel="EditURI" type="application/rsd+xml" ...'
        ) );

        // Remove Feed_Links
        $wpc->add_setting( 'adminizr_meta_feed_links', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove WP_Generator Control
        $wpc->add_control( 'adminizr_meta_feed_links', array(
            'label'         => __( 'Hide feed_links tag', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_meta_section',
            'priority'      => 20,
            'settings'      => 'adminizr_meta_feed_links',
            'description'   => 'link rel="alternate" type="application/rss+xml" ... '
        ) );

        // Remove Feed_Links
        $wpc->add_setting( 'adminizr_meta_feed_links_extra', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove WP_Generator Control
        $wpc->add_control( 'adminizr_meta_feed_links_extra', array(
            'label'         => __( 'Hide feed_links_extra tag', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_meta_section',
            'priority'      => 25,
            'settings'      => 'adminizr_meta_feed_links_extra',
            'description'   => 'link rel="alternate" type="application/rss+xml" ...'
        ) );

        /**********************************/
        /**  Other                       **/
        /**********************************/

        // Remove Disable Visual Editor
        $wpc->add_setting( 'adminizr_other_visual_editor', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Settings Menu Item Control
        $wpc->add_control( 'adminizr_other_visual_editor', array(
            'label'         => __( 'Remove Disable Visual Editor', $this->text ),
            'type'          => 'checkbox',
            'section'       => 'adminizr_other_section',
            'priority'      => 5,
            'settings'      => 'adminizr_other_visual_editor',
            'description'   => '<br /><hr />'
        ) );

        // Custom CSS setting
        $wpc->add_setting( 'adminizr_other_css', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Custom CSS control
        $wpc->add_control( 'adminizr_other_css', array(
            'label'     => __( 'Custom CSS', $this->text ),
            'type'      => 'textarea',
            'section'   => 'adminizr_other_section',
            'priority'  => 10,
            'settings'  => 'adminizr_other_css',
        ) );

        // Admin Favicon Setting
        $wpc->add_setting( 'adminizr_other_icon', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Admin Favicon Control
        $wpc->add_control( new WP_Customize_Image_Control( $wpc, 'adminizr_other_icon', array(
            'label'     => __( 'Admin Favicon', $this->text ),
            'section'   => 'adminizr_other_section',
            'priority'  => 15,
            'settings'  => 'adminizr_other_icon',
        ) ) );

        // Remove Admin Color Scheme
        $wpc->add_setting( 'adminizr_other_color_scheme', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Remove Admin Color Scheme Control
        $wpc->add_control( 'adminizr_other_color_scheme', array(
            'label'     => __( 'Remove Admin Color Scheme', $this->text ),
            'type'      => 'checkbox',
            'section'   => 'adminizr_other_section',
            'priority'  => 20,
            'settings'  => 'adminizr_other_color_scheme'
        ) );

        // Default Color Scheme
        $wpc->add_setting( 'adminizr_other_default_color_scheme', array(
            'type'          => 'theme_mod',
            'capability'    => 'edit_theme_options'
        ) );

        // Set up colors
        $colors = array (
            'none'      => 'None', 
            'default'   => 'Default', 
            'light'     => 'Light', 
            'blue'      => 'Blue', 
            'coffee'    => 'Coffee', 
            'ectoplasm' => 'Ectoplasm', 
            'midnight'  => 'Midnight', 
            'ocean'     => 'Ocean', 
            'sunrise'   => 'Sunrise' 
        );

        // Hook in extras?
        if ( class_exists( 'ACS_Color_Schemes' ) ) {
            $colors_extra = array(
                'vinyard'   => 'Vinyard',
                'primary'   => 'Primary',
                '80s-kid'   => '80s Kid',
                'aubergine' => 'Aubergine',
                'cruise'    => 'Cruise',
                'flat'      => 'Flat',
                'lawn'      => 'Lawn',
                'seashore'  => 'Seashore'
            );
            $colors = array_merge( $colors, $colors_extra );
        }

        // Default Color Scheme Control
        $wpc->add_control( 'adminizr_other_default_color_scheme', array(
            'label'     => __( 'Default Admin Color Scheme', $this->text ),
            'type'      => 'select',
            'section'   => 'adminizr_other_section',
            'priority'  => 25,
            'choices'   => $colors, 
            'settings'  => 'adminizr_other_default_color_scheme'
        ) );
    }

    /*********************************************/
    /**  Admin Customizer Functionality         **/
    /*********************************************/

    /**
     * Roles functions, actions & filters
     *
     * @access protected
     */
    protected function roles() {

        // Get allowed roles - csv to array
        $adminizr_roles = get_theme_mod( 'adminizr_roles', '' );
        if ( empty( $adminizr_roles ) ) { return; }

        // Strip out values & remove empty
        $adminizr_roles = array_values( $adminizr_roles );

        // Get current user roles
        $user_role = $this->get_user_role();

        //allowed?
        $this->is_approved = ( in_array( $user_role, $adminizr_roles ) ) ? TRUE : FALSE;
    }

    /**
     * Theme functions, actions & filters
     *
     * @access protected
     */
    protected function theme() {
        
        // role approval check
        if ( !$this->is_approved ) { return; }
    }

    /**
     * Header functions, actions & filters
     *
     * @access protected
     */
    protected function head() {

        // role approval check
        if ( !$this->is_approved ) { return; }

        // Disable admin bar in front-end: Admin
        $hide_header_admin  = (int)get_theme_mod( 'adminizr_header_hide_admin', 0 );
        $hide_header_do     = ( current_user_can( 'manage_options' ) && $hide_header_admin === 1 ) ? FALSE : TRUE;

        // Disable admin bar in front-end
        $hide_header = (int)get_theme_mod( 'adminizr_header_hide', 0 );
        if ( $hide_header === 1  && $hide_header_do ) {
	        add_filter( 'show_admin_bar', '__return_false');
            return;
        }

        $show_header = (int)get_theme_mod( 'adminizr_header_show', 0 );
        if ( $show_header === 1 ) {
	        add_filter( 'show_admin_bar', '__return_true');
            add_action( 'admin_bar_menu', array( $this, 'head_login_adminbar' ) );
        }

        // Remove selected items, admin & front-end
        add_action( 'admin_bar_menu', array( $this, 'adminizr_bar_setup' ), 99 ); 

        // Logout Confirm
        $head_logout_confirm = (int)get_theme_mod( 'adminizr_header_logout_confirm', 0 );
        if ( $head_logout_confirm === 1 ) {
            add_action( 'admin_head', array( $this, 'head_logout_confirm' ) );
            add_action( 'wp_head', array( $this, 'head_logout_confirm' ) );
        }
    }

    /**
     * Footer functions, actions & filters
     *
     * @access protected
     */
    protected function foot() {

        // role approval check
        if ( !$this->is_approved ) { return; }

        // hide footer?
        $hide_footer = (int)get_theme_mod( 'adminizr_footer_hide', 0 );
        if ( $hide_footer === 1 ) { ?>
        <style>#wpfooter { display:none!important; }</style>
<?php   }

        // hide footer wordpress version?
        $hide_footer_version = (int)get_theme_mod( 'adminizr_footer_hide_version', 0 );
        if ( $hide_footer_version === 1 ) {
            add_action( 'admin_menu', array( $this, 'hide_version_footer' ) );
        }

        // custom footer wordpress version
        $custom_footer_version = get_theme_mod( 'adminizr_footer_custom_version', '' );
        if ( !empty( $custom_footer_version ) ) {
            add_action( 'admin_menu', array( $this, 'hide_version_footer' ) );
            add_filter( 'update_footer', array( $this, 'custom_version_footer' ) );
        }

        // hide footer wordpress credits?
        $hide_footer_credits = get_theme_mod( 'adminizr_footer_hide_credits', '' );
        if ( !empty( $hide_footer_credits ) ) {
            add_filter( 'admin_footer_text', array( $this, 'hide_credits_footer') );        
        }

        // custom footer wordpress version
        $custom_footer_credits = get_theme_mod( 'adminizr_footer_custom_credits', '' );
        $custom_footer_logo = get_theme_mod( 'adminizr_footer_logo', '' );
        if ( !empty( $custom_footer_credits ) || !empty( $custom_footer_logo ) ) {
	        add_filter( 'admin_footer_text', array( $this, 'custom_credits_footer' ) );
        }
    }

    /**
     * Dashboard functions, actions & filters
     *
     * @access protected
     */
    protected function dashboard() {

        // role approval check
        if ( !$this->is_approved ) { return; }

        // remove welcome widget
        $welcome_widget = (int)get_theme_mod( 'adminizr_dashboard_welcome', 0 );
        if ( $welcome_widget === 1 ) {
    		remove_action( 'welcome_panel', 'wp_welcome_panel' ); 
        }

        add_filter( 'screen_layout_columns', array( $this, 'dashboard_columns' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'dashboard_setup' ) );
    }

    /**
     * Screen functions, actions & filters
     *
     * @access protected
     */
    protected function screen() {

        // role approval check
        if ( !$this->is_approved ) { return; }

        // screen help & options admin?
        $screen_help_opts_admin = (int)get_theme_mod( 'adminizr_screen_help_opts', 0 );
        $screen_help_opts_do    = ( current_user_can( 'manage_options' ) && $screen_help_opts_admin === 1 ) ? FALSE : TRUE;

        // Screen options tab
        $screen_opts = (int)get_theme_mod( 'adminizr_screen_opts', 0 );
        if ( $screen_opts === 1 && $screen_help_opts_do ) {
        	add_filter( 'screen_options_show_screen', '__return_false');
        }

        // Screen help tab
        $screen_help = (int)get_theme_mod( 'adminizr_screen_help', 0 );
        if ( $screen_help === 1 && $screen_help_opts_do ) {
        	add_filter( 'contextual_help', array( $this, 'remove_help' ), 999, 3 );                
        }

        // screen help & options admin?
        $screen_updates_admin = (int)get_theme_mod( 'adminizr_screen_updates_admin', 0 );
        $screen_updates_do    = ( current_user_can( 'manage_options' ) && $screen_updates_admin === 1 ) ? FALSE : TRUE;

        // Screen updates notice
        $screen_updates = (int)get_theme_mod( 'adminizr_screen_updates', 0 );
        if ( $screen_updates === 1 && $screen_updates_do ) {
	        add_action( 'after_setup_theme','wpui_remove_core_updates');
            
            remove_action( 'load-update-core.php','wp_update_plugins');
	        add_filter( 'pre_site_transient_update_plugins','__return_null');

	        add_filter( 'pre_site_transient_update_core', array( $this, 'remove_core_updates' ) );
	        add_filter( 'pre_site_transient_update_plugins', array( $this, 'remove_core_updates' ) );
	        add_filter( 'pre_site_transient_update_themes', array( $this, 'remove_core_updates' ) );

        	add_action( 'admin_menu', array( $this, 'wp_hide_nag' ) );
        }

        // Screen autogen password notice
        $screen_pass_notice = (int)get_theme_mod( 'adminizr_screen_pass_notice', 0 );
        if ( $screen_pass_notice === 1 ) {
        	add_filter( 'get_user_option_default_password_nag' , array( $this, 'stop_password_nag' ) , 10 );
        }

        // Screen no trash
        $screen_no_trash = (int)get_theme_mod( 'adminizr_screen_no_trash', 0 );
        if ( $screen_no_trash === 1 ) {
        	define('EMPTY_TRASH_DAYS', 0 );
        }

        // Screen empty trash
        $screen_empty_trash = (int)get_theme_mod( 'adminizr_screen_empty_trash', 0 );
        if ( $screen_empty_trash >= 1 ) {
            if ( !defined( 'EMPTY_TRASH_DAYS' ) ) {
            	define( 'EMPTY_TRASH_DAYS', $screen_empty_trash );
            }
        }
        // Screen autosave interval
        $screen_autosave = (int)get_theme_mod( 'adminizr_screen_autosave', 0 );
        if ( $screen_autosave >= 1 ) {
            if ( !defined( 'AUTOSAVE_INTERVAL' ) ) {
                define( 'AUTOSAVE_INTERVAL', $screen_autosave );
            }
        }

        // Screen autosave interval
        $screen_revisions = (int)get_theme_mod( 'adminizr_screen_revisions', 0 );
        if ( $screen_revisions >= 1 ) {
            if ( !defined( 'WP_POST_REVISIONS' ) ) {
            	define( 'WP_POST_REVISIONS', $screen_revisions );
            }
        }   
    }

    /**
     * Layout functions, actions & filters
     *
     * @access protected
     */
    protected function layout() {

        // role approval check
        if ( !$this->is_approved ) { return; }

        // remove some post & pages metaboxes
        add_action( 'admin_menu', array( $this, 'remove_meta_boxes' ) );
    }

    /**
     * Columns functions, actions & filters
     *
     * @access protected
     */
    protected function columns() {

        // role approval check
        if ( !$this->is_approved ) { return; }

        // remove columns as required
        add_action( 'admin_init' , array( $this, 'columns_init' ) );
    }

    /**
     * Editor functions, actions & filters
     *
     * @access protected
     */
    protected function editor() {

        // role approval check
        if ( !$this->is_approved ) { return; }

        // font size
        $font_size = (int)get_theme_mod( 'adminizr_editor_font_size', 0 );
        if ( $font_size === 1 ) {
            add_filter( 'mce_buttons_2', array( $this, 'font_size_select' ) );
        }

        // font family
        $font_family = (int)get_theme_mod( 'adminizr_editor_font_family', 0 );
        if ( $font_family === 1 ) {
            add_filter( 'mce_buttons_2', array( $this, 'font_family_select' ) );
        }

        // formats
        $formats = (int)get_theme_mod( 'adminizr_editor_formats', 0 );
        if ( $formats === 1 ) {
        	add_filter( 'mce_buttons', array( $this, 'formats_select' ) );
        }

        // shortlinks
        $shortlinks = (int)get_theme_mod( 'adminizr_editor_shortlinks', 0 );
        if ( $shortlinks === 1 ) {
        	add_filter( 'pre_get_shortlink', '__return_empty_string' );
        }

        // mce buttons
        add_filter( 'mce_buttons', array( $this, 'add_buttons_tinymce' ) );

        // media elements
        add_filter( 'media_view_strings', array( $this, 'media_uploader' ) );
    }

    /**
     * Menu functions, actions & filters
     *
     * @access protected
     */
    protected function menu() {

        // Role approval check
        if ( !$this->is_approved ) { return; }

        // Remove menu display
        add_action( 'admin_menu', array( $this, 'remove_menus' ) );

        // Rename menu display
        add_action( 'admin_menu', array( $this, 'rename_menus' ) );

        // Post Type Labels
        add_action( 'init', array( $this, 'post_object' ) );
    }

    /**
     * Meta Functions
     *
     * @access public
     */
    protected function meta() {
        
        // role approval check
        if ( !$this->is_approved ) { return; }

        // wp_generator meta
        $meta_wp_generator = (int)get_theme_mod( 'adminizr_meta_wp_generator', 0 );
        if ( $meta_wp_generator === 1 ) {
			remove_action( 'wp_head', 'wp_generator' );
        }

        // wmwmanifest_link meta
        $meta_wlwmanifest_link = (int)get_theme_mod( 'adminizr_meta_wlwmanifest_link', 0 );
        if ( $meta_wlwmanifest_link === 1 ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
        }

        // rsd_link meta
        $meta_rsd_link = get_theme_mod( 'adminizr_meta_rsd_link', 0 );
        if ( $meta_rsd_link === 1 ) {
			remove_action( 'wp_head', 'rsd_link' );
        }

        // feed_links meta
        $meta_feed_links = get_theme_mod( 'adminizr_meta_feed_links', 0 );
        if ( $meta_feed_links === 1 ) {
			remove_action( 'wp_head', 'feed_links', 2 );
        }
        
        // feed_links extra meta
        $meta_feed_links_extra = get_theme_mod( 'adminizr_meta_feed_links_extra', 0 );
        if ( $meta_feed_links_extra === 1 ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
        }
    }

    /**
     * Other functions, actions & filters
     *
     * @access protected
     */
    protected function other() {

        // role approval check
        if ( !$this->is_approved ) { return; }

        // favicon
        $other_favicon = get_theme_mod( 'adminizr_other_icon', '' );
        if ( !empty( $other_favicon ) ) {
        	add_action( 'admin_head', array( $this, 'adminizr_favicon' ) );
        }

        // visual editor
        $other_visual_editor = (int)get_theme_mod( 'adminizr_other_visual_editor', 0 );
        if ( $other_visual_editor === 1 ) {
            add_action( 'admin_print_styles-profile.php', array( $this, 'remove_visual_editor' ) );
	        add_action( 'admin_print_styles-user-edit.php', array( $this, 'remove_visual_editor' ) );
        }

        // remove color scheme
        $other_color_scheme = (int)get_theme_mod( 'adminizr_other_color_scheme', 0 );
        if ( $other_color_scheme === 1 ) {
            remove_action( 'admin_color_scheme_picker', 'adminizr_color_scheme_picker' );
        }
        
        // default color sheme
        $other_default_color_scheme = get_theme_mod( 'adminizr_other_default_color_scheme', '' );
        if ( !empty( $other_default_color_scheme ) ) {
            add_action( 'after_setup_theme', array( $this, 'set_default_color_scheme' ) );
        }

        // other css
        $other_css = get_theme_mod( 'adminizr_other_css', '' );
        if ( !empty( $other_css ) ) { ?>
            <style><?php echo $other_css; ?></style>
<?php   }
    }

    /*********************************************/
    /** Action & Filter Functionality - ROLES   **/
    /*********************************************/

    /**
     * Retrieve a list of available roles to build checkboxes
     *
     * 'choices' => array(
     *    'apple'  => __( 'Apple',  'adminizr' ),
     *    'banana' => __( 'Banana', 'adminizr' ),
     *    'date'   => __( 'Date',   'adminizr' ),
     *    'orange' => __( 'Orange', 'adminizr' )
     * )
     *  @access     public
     *  @return     array
     */
    public function get_roles() {
        
        global $wp_roles;

        $the_roles = $wp_roles->roles;

        $roles = array();
        foreach ( $the_roles as $k=>$v ) { 
            $roles[$k] = __( $v['name'], $this->text );
        }

        return $roles;
    }

    /**
     * Returns current user's role. Assumes single main role
     *
     * @return string 
     * @access public
     */
    public function get_user_role() { 
    
        global $current_user;
    
        //sometimes user not set
        if ( !($current_user instanceof WP_User) ) { 
        
            // try again
            $current_user = wp_get_current_user();
        
            //last chance
            if ( !($current_user instanceof WP_User) ) { return; }
        }

        //ok...
        $user_roles = $current_user->roles;
	    $user_role = array_shift($user_roles);

        return $user_role;
    }
    
    /**
     * Samitise roles
     *
     * @param   array   $values
     * @return  array
     * @access  public
     */
    public function sanitize_roles( $values ) {

        $multi_values = !is_array( $values ) ? explode( ',', $values ) : $values;
        return empty( $multi_values ) ? array() : array_map( 'sanitize_text_field', $multi_values );
    }

    /*********************************************/
    /** Action & Filter Functionality - HEADER  **/
    /*********************************************/

    /**
     * Add login menu option if admin bar always on
     *
     * @param   object  $wp_admin_bar
     * @access  public
     */
    public function head_login_adminbar( $wp_admin_bar) {
    	if ( !is_user_logged_in() ) {
	        $wp_admin_bar->add_menu( array( 'title' => __( 'Log In', $this->text ), 'href' => wp_login_url() ) );
        }
    }

    /**
     * Set up the admin bar header
     *
     * @param   object  $wp_admin_bar
     * @access  public
     */
    public function adminizr_bar_setup( $wp_admin_bar ) {

        // Hide Logo
        $head_hide_logo = (int)get_theme_mod( 'adminizr_header_logo_hide', 0 );
        if ( $head_hide_logo === 1 ) {
    		$wp_admin_bar->remove_menu( 'wp-logo' );
            $wp_admin_bar->remove_menu( 'about' );
            $wp_admin_bar->remove_menu( 'wporg');
            $wp_admin_bar->remove_menu( 'documentation' );
            $wp_admin_bar->remove_menu( 'support-forums' );
            $wp_admin_bar->remove_menu( 'feedback' );
        }

        // Custom Logo 
        $head_logo = get_theme_mod( 'adminizr_header_logo', '' );
        if ( !empty( $head_logo ) && !$head_hide_logo ) {
            $head_logo_url = esc_url( $head_logo );
?>     <script type="text/javascript">
            jQuery(document).ready(function($) {
                var image = $('<img/>', {
                    src: '<?php echo $head_logo_url; ?>',
                    alt: '<?php echo get_bloginfo('name') . ' Admin'; ?>',
                    style: 'padding-top:3px'
                });
                var anchorlink = $('<a/>', {
                    href: '<?php echo admin_url(); ?>',
                    html: image,
                    title: '<?php echo get_bloginfo('name'); ?>'
                });
                jQuery('<li/>', {
                    html: anchorlink,
                    class: 'menupop'
                }).prependTo('#wp-admin-bar-root-default');
            });
        </script>
<?php   }

        // Hide Site Name
        $head_hide_site = (int)get_theme_mod( 'adminizr_header_site_hide', 0 );
        if ( $head_hide_site === 1 ) {
    		$wp_admin_bar->remove_menu( 'site-name' );
	    	$wp_admin_bar->remove_menu( 'view-site' );
            $wp_admin_bar->remove_menu( 'edit' );
        }
        
        // Hide My Account
        $head_hide_account = (int)get_theme_mod( 'adminizr_header_account_hide', 0 );
        if ( $head_hide_account === 1 ) {
    		$wp_admin_bar->remove_menu( 'my-account' );
    		$wp_admin_bar->remove_menu( 'my-account-with-avatar' );
        }

        // Hide Profile
        $head_hide_profile = (int)get_theme_mod( 'adminizr_header_profile_hide', 0 );
        if ( $head_hide_profile === 1 ) {
            $wp_admin_bar->remove_menu( 'user-info' );
            $wp_admin_bar->remove_menu( 'edit-profile' );   
	    }

        // Hide Search
        $head_hide_search = (int)get_theme_mod( 'adminizr_header_search_hide', 0 );
        if ( $head_hide_search === 1 ) {
	    	$wp_admin_bar->remove_menu( 'search' );
        }

        // Hide Comments
        $head_hide_comments = (int)get_theme_mod( 'adminizr_header_comments_hide', 0 );
        if ( $head_hide_comments === 1 ) {
	    	$wp_admin_bar->remove_menu( 'comments' );
        }

        // Hide New Content
        $head_hide_new = (int)get_theme_mod( 'adminizr_header_new_hide', 0 );
        if ( $head_hide_new === 1 ) {
	    	$wp_admin_bar->remove_menu( 'new-content' );
        }

        // Hide Updates
        $head_hide_updates = (int)get_theme_mod( 'adminizr_header_updates_hide', 0 );
        if ( $head_hide_updates === 1 ) {
	    	$wp_admin_bar->remove_menu( 'updates' );
        }
    }

    /**
     * Confirm logout
     *
     * @access public
     */
    public function head_logout_confirm() { ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#wp-admin-bar-logout a').click(function() {
                    var confirmation = confirm('Are you sure?');
                    return (confirmation) ? true : false;
                });
            });
        </script>
<?php   
    }

    /*********************************************/
    /** Action & Filter Functionality - FOOTER  **/
    /*********************************************/

    /**
     * Hide the footer wordpress version 
     *
     * @access public
     */
    public function hide_version_footer() {
		remove_filter( 'update_footer', 'core_update_footer' ); 
	}

    /**
     * Custom wordpress version. Called if set & not empty.
     *
     * @access public
     */
    public function custom_version_footer( $footer ) {

        $footer .= get_theme_mod( 'adminizr_footer_custom_version' );
        return $footer;
	}

    /**
     * Hide the footer wordpress credits 
     *
     * @access public
     */
    public function hide_credits_footer() { 
        return ''; 
    }

    /**
     * Custom wordpress credits. Called if set & not empty.
     *
     * @access public
     */
	public function custom_credits_footer() {

        $custom_footer_credits = '';
    
        $custom_footer_logo = get_theme_mod( 'adminizr_footer_logo', '' );
        if ( !empty( $custom_footer_logo ) ) {
            $custom_footer_credits .= '<img class="footer-logo" height="32px" width="32px" src="' . $custom_footer_logo .'" alt="">';
        }

        $custom_footer_credits .= get_theme_mod( 'adminizr_footer_custom_credits' );
		return $custom_footer_credits;
	}

    /*************************************************/
    /** Action & Filter Functionality - DASHBOARD   **/
    /*************************************************/
    
    /**
     * Dashboard Columns
     *
     * @access public
     * @return array
     */
    public function dashboard_columns( $columns ) {

        // remove welcome widget
        $dashboard_columns = (int)get_theme_mod( 'adminizr_dashboard_columns', 0 );
        if ( $dashboard_columns === 1 ) { 
            $columns['dashboard'] = 2;
        }
        return $columns;
    }

    /**
     * Set up Dashboard Widgets
     *
     * @access public
     */
    public function dashboard_setup() {

        // current metaboxes
        global $wp_meta_boxes; 

        // activity widget
        $activity_widget = (int)get_theme_mod( 'adminizr_dashboard_activity', 0 );
        if ( $activity_widget === 1 ) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']); 
        }

        // incoming links widget
        $links_widget = (int)get_theme_mod( 'adminizr_dashboard_links', 0 );
        if ( $links_widget === 1 ) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']); 
        }

        // right now widget
        $right_now_widget = (int)get_theme_mod( 'adminizr_dashboard_right_now', 0 );
        if ( $right_now_widget === 1 ) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']); 
        }

        // plugins widget
        $plugins_widget = (int)get_theme_mod( 'adminizr_dashboard_plugins', 0 );
        if ( $plugins_widget === 1 ) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']); 
        }

        // recent comments widget
        $recent_comments_widget = (int)get_theme_mod( 'adminizr_dashboard_recent_comments', 0 );
        if ( $recent_comments_widget === 1 ) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']); 
        }

        // recent drafts widget
        $recent_drafts_widget = (int)get_theme_mod( 'adminizr_dashboard_recent_drafts', 0 );
        if ( $recent_drafts_widget === 1 ) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']); 
        }

        // remove quickpress widget
        $quickpress_widget = (int)get_theme_mod( 'adminizr_dashboard_quickpress', 0 );
        if ( $quickpress_widget === 1 ) {
            unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']); 
        }
 
        // primary widget
        $primary_widget = (int)get_theme_mod( 'adminizr_dashboard_primary', 0 );
        if ( $primary_widget === 1 ) {
            unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']); 
        }

        // secondary widget
        $secondary_widget = (int)get_theme_mod( 'adminizr_dashboard_secondary', 0 );
        if ( $secondary_widget === 1 ) {
            unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']); 
        }
    }
        
    /*************************************************/
    /** Action & Filter Functionality - SCREEN      **/
    /*************************************************/

    /**
     * Remove help tabs
     *
     * @access public
     */
    public function remove_help( $old_help, $screen_id, $screen ){
	    $screen->remove_help_tabs();
	    return $old_help;
	}

    /**
     * Remove core updates
     *
     * @access public
     */
	public function remove_core_updates(){
        global $wp_version;
        return(object) array( 'last_checked'=> time(), 'version_checked'=> $wp_version );
	}

    /**
     * Remove update notices
     *
     * @access public
     */
	public function wp_hide_nag() {
		remove_action('adminizr_notices', 'update_nag', 3);
        remove_action( 'network_adminizr_notices', 'update_nag', 3 );
	}

    /**
     * Remove password nag
     */
    public function stop_password_nag( $val ){
		return 0;
	}

    /*************************************************/
    /** Action & Filter Functionality - EDITOR      **/
    /*************************************************/

    /**
     * Font Size Select
     *
     * @param   array $buttons
     * @return  array $buttons
     * @access  public
     */
	public function font_size_select( $buttons ) {
		array_unshift( $buttons, 'fontsizeselect' );
		return $buttons;
	}

    /**
     * Font Family Select
     *
     * @param   array $buttons
     * @return  array $buttons
     * @access  public
     */
    public function font_family_select( $buttons ) {
		array_unshift( $buttons, 'fontselect' );
		return $buttons;
	}

    /**
     * Formats Select
     *
     * @param   array $buttons
     * @return  array $buttons
     * @access  public
     */
    public function formats_select( $buttons ) {
		array_push( $buttons, 'styleselect' );
		return $buttons;
    }

    /**
     * Formats Select
     *
     * @param   array $buttons
     * @return  array $buttons
     * @access  public
     */
    public function add_buttons_tinymce( $buttons ) {

        // new doc button
        $new_btn = (int)get_theme_mod( 'adminizr_editor_new_btn', 0 );
        if ( $new_btn === 1 ) { $buttons[] = 'newdocument'; }

        // cut button
        $cut_btn = (int)get_theme_mod( 'adminizr_editor_cut_btn', 0 );
        if ( $cut_btn === 1 ) { $buttons[] = 'cut'; }
    
        // copy button
        $copy_btn = (int)get_theme_mod( 'adminizr_editor_copy_btn', 0 );
        if ( $copy_btn === 1 ) { $buttons[] = 'copy'; }

        // paste button
        $paste_btn = (int)get_theme_mod( 'adminizr_editor_paste_btn', 0 );
        if ( $paste_btn === 1 ) { $buttons[] = 'paste'; }
    
        // copy button
        $bg_color_btn = (int)get_theme_mod( 'adminizr_editor_bg_color_btn', 0 );
        if ( $bg_color_btn === 1 ) { $buttons[] = 'backcolor'; }
    
        return $buttons;
    }

    /**
     * Formats Select
     *
     * @param   array $buttons
     * @return  array $buttons
     * @access  public
     */
    public function media_uploader( $strings ) {
    
        // Insert Media
        $modal_media = (int)get_theme_mod( 'adminizr_editor_modal_media', 0 );
        if ( $modal_media === 1 ) { 
    		unset( $strings['insertMediaTitle'] );
	    }

        // Upload Files
        $modal_uploads = (int)get_theme_mod( 'adminizr_editor_modal_uploads', 0 );
        if ( $modal_uploads === 1 ) { 
    		unset( $strings['uploadFilesTitle'] ); 
	    }

        // Media Library
        $modal_library = (int)get_theme_mod( 'adminizr_editor_modal_library', 0 );
        if ( $modal_library === 1 ) { 
    		unset( $strings['mediaLibraryTitle'] );
	    }

        // Media Gallery
        $modal_gallery = (int)get_theme_mod( 'adminizr_editor_modal_gallery', 0 );
        if ( $modal_gallery === 1 ) { 
    		unset( $strings['createGalleryTitle'] );
        }

        // Media Playlist
        $modal_playlist = (int)get_theme_mod( 'adminizr_editor_modal_playlist', 0 );
        if ( $modal_playlist === 1 ) { 
    		unset( $strings['createPlaylistTitle'] ); 
        }

        // Media Featured Image
        $modal_featured = (int)get_theme_mod( 'adminizr_editor_modal_featured', 0 );
        if ( $modal_featured === 1 ) { 
    		unset( $strings['setFeaturedImageTitle'] ); 
	    }

        // Media Insert from URL
        $modal_url = (int)get_theme_mod( 'adminizr_editor_modal_url', 0 );
        if ( $modal_url === 1 ) { 
    		unset( $strings['insertFromUrlTitle'] );
	    }

        return $strings;
    }

    /*************************************************/
    /** Action & Filter Functionality - LAYOUT      **/
    /*************************************************/

    /**
     * Remove unwanted metaboxes from posts & pages layouts
     *
     * @access public
     */
    public function remove_meta_boxes() {

        /*********************/
        /** Post Metaboxes  **/
        /*********************/

        // post slug
        $slug_posts = (int)get_theme_mod( 'adminizr_layout_slug_posts', 0 );
        if ( $slug_posts === 1 ) { 
		    remove_meta_box('slugdiv', 'post', 'normal');
	    }

        // post submit
        $submit_posts = (int)get_theme_mod( 'adminizr_layout_submit_posts', 0 );
        if ( $submit_posts === 1 ) { 
		    remove_meta_box('submitdiv', 'post', 'normal');
	    }

        // post author
        $author_posts = (int)get_theme_mod( 'adminizr_layout_author_posts', 0 );
        if ( $author_posts === 1 ) { 
		    remove_meta_box('authordiv', 'post', 'normal');
	    }

        // post category
        $category_posts = (int)get_theme_mod( 'adminizr_layout_category_posts', 0 );
        if ( $category_posts === 1 ) { 
    		remove_meta_box('categorydiv', 'post', 'normal');
	    }

        // post commments
        $comments_posts = (int)get_theme_mod( 'adminizr_layout_comments_posts', 0 );
        if ( $comments_posts === 1 ) { 
    		remove_meta_box('commentsdiv', 'post', 'normal');
    		remove_meta_box('commentstatusdiv', 'post', 'normal');
	    }

        // post commments
        $formats_posts = (int)get_theme_mod( 'adminizr_layout_formats_posts', 0 );
        if ( $formats_posts === 1 ) { 
    		remove_meta_box('formatdiv', 'post', 'normal');
        }

        // post attributes
        $attributes_posts = (int)get_theme_mod( 'adminizr_layout_attributes_posts', 0 );
        if ( $attributes_posts === 1 ) { 
    		remove_meta_box('pageparentdiv', 'post', 'normal');
	    }

        // post custom fields
        $custom_posts = (int)get_theme_mod( 'adminizr_layout_custom_posts', 0 );
        if ( $custom_posts === 1 ) { 
    		remove_meta_box('postcustom', 'post', 'normal');
	    }

        // post excerpts
        $excerpt_posts = (int)get_theme_mod( 'adminizr_layout_excerpt_posts', 0 );
        if ( $excerpt_posts === 1 ) { 
    		remove_meta_box('postexcerpt', 'post', 'normal');
	    }

        // post featured Image
        $featured_posts = (int)get_theme_mod( 'adminizr_layout_featured_posts', 0 );
        if ( $featured_posts === 1 ) { 
            remove_meta_box( 'postimagediv', 'post', 'normal' );
        }

        // post revisions
        $revisions_posts = (int)get_theme_mod( 'adminizr_layout_revisions_posts', 0 );
        if ( $revisions_posts === 1 ) { 
            remove_meta_box('revisionsdiv', 'post', 'normal'); 
	    }

        // post tags
        $tags_posts = (int)get_theme_mod( 'adminizr_layout_tags_posts', 0 );
        if ( $tags_posts === 1 ) { 
    		remove_meta_box('tagsdiv-post_tag', 'post', 'normal');
	    }
        
        // post trackbacks
        $track_posts = (int)get_theme_mod( 'adminizr_layout_trackbacks_posts', 0 );
        if ( $track_posts === 1 ) { 
    		remove_meta_box('trackbacksdiv', 'post', 'normal');
        }

        /*********************/
        /** Page Metaboxes  **/
        /*********************/

        // post slug
        $slug_page = (int)get_theme_mod( 'adminizr_layout_slug_pages', 0 );
        if ( $slug_page === 1 ) { 
		    remove_meta_box('slugdiv', 'page', 'normal');
	    }

        // post submit
        $submit_page = (int)get_theme_mod( 'adminizr_layout_submit_pages', 0 );
        if ( $submit_page === 1 ) { 
		    remove_meta_box('submitdiv', 'page', 'normal');
	    }

        // page author
        $author_pages = (int)get_theme_mod( 'adminizr_layout_author_pages', 0 );
        if ( $author_pages === 1 ) { 
		    remove_meta_box('authordiv', 'page', 'normal');
	    }

        // page commments
        $comments_pages = (int)get_theme_mod( 'adminizr_layout_comments_pages', 0 );
        if ( $comments_pages === 1 ) { 
    		remove_meta_box('commentsdiv', 'page', 'normal');
	    }

        // page attributes
        $attributes_pages = (int)get_theme_mod( 'adminizr_layout_attributes_pages', 0 );
        if ( $attributes_pages === 1 ) { 
    		remove_meta_box('pageparentdiv', 'page', 'normal');
	    }

        // page custom fields
        $custom_pages = (int)get_theme_mod( 'adminizr_layout_custom_pages', 0 );
        if ( $custom_pages === 1 ) { 
    		remove_meta_box('postcustom', 'page', 'normal');
	    }

        // page featured Image
        $featured_pages = (int)get_theme_mod( 'adminizr_layout_featured_pages', 0 );
        if ( $featured_pages === 1 ) { 
    		remove_meta_box('postimagediv', 'page', 'side'); 
	    }

        // page revisions
        $revisions_pages = (int)get_theme_mod( 'adminizr_layout_revisions_pages', 0 );
        if ( $revisions_pages === 1 ) { 
            remove_meta_box('revisionsdiv', 'page', 'normal'); 
	    }

        // page commments
        $tags_pages = (int)get_theme_mod( 'adminizr_layout_tags_pages', 0 );
        if ( $tags_pages === 1 ) { 
    		remove_meta_box('tagsdiv-post_tag', 'page', 'normal');
	    }
        
        // page trackbacks
        $track_pages = (int)get_theme_mod( 'adminizr_layout_trackbacks_pages', 0 );
        if ( $track_pages === 1 ) { 
    		remove_meta_box('trackbacksdiv', 'page', 'normal');
        }

        /*********************/
        /** Link Metaboxes  **/
        /*********************/

        // links
        $box_links = (int)get_theme_mod( 'adminizr_layout_box_links', 0 );
        if ( $box_links === 1 ) { 
            remove_meta_box('linktargetdiv', 'link', 'normal');
            remove_meta_box('linkxfndiv', 'link', 'normal');
            remove_meta_box('linkadvanceddiv', 'link', 'normal');
	    }
    }

    /*************************************************/
    /** Action & Filter Functionality - COLUMNS     **/
    /*************************************************/

    /**
     * Set up the columns in the admin UI
     *
     * @access public
     */
    public function columns_init() {

        // posts columns
        add_filter( 'manage_posts_columns', array( $this, 'posts_columns' ) );
        
        // pages columns
        add_filter( 'manage_pages_columns', array( $this, 'pages_columns' ) );

        // media columns
        add_filter( 'manage_media_columns', array( $this, 'media_columns' ) );
    }

    /**
     * Manage Posts List View Columns
     *
     * @param   array   $columns
     * @return  array   $columns
     * @acess   public
     */
    public function posts_columns( $columns ) {

        // checkbox
        $checkbox_posts = (int)get_theme_mod( 'adminizr_columns_checkbox_posts', 0 );
        if ( $checkbox_posts === 1 ) { 
            unset( $columns['cb'] );
	    }

        // title
        $title_posts = (int)get_theme_mod( 'adminizr_columns_title_posts', 0 );
        if ( $title_posts === 1 ) { 
    	    unset( $columns['title'] );
	    }

        // author
        $author_posts = (int)get_theme_mod( 'adminizr_columns_author_posts', 0 );
        if ( $author_posts === 1 ) { 
    	    unset( $columns['author'] );
	    }

        // categories
        $categories_posts = (int)get_theme_mod( 'adminizr_columns_categories_posts', 0 );
        if ( $categories_posts === 1 ) { 
	        unset( $columns['categories'] );
	    }

        // tags
        $tags_posts = (int)get_theme_mod( 'adminizr_columns_tags_posts', 0 );
        if ( $tags_posts === 1 ) { 
    	    unset( $columns['tags'] );
	    }

        // comments
        $comments_posts = (int)get_theme_mod( 'adminizr_columns_comments_posts', 0 );
        if ( $comments_posts === 1 ) { 
    	    unset( $columns['comments'] );
	    }

        // date
        $date_posts = (int)get_theme_mod( 'adminizr_columns_date_posts', 0 );
        if ( $date_posts === 1 ) { 
    	    unset( $columns['date'] );
	    }

        return $columns;
    }

    /**
     * Manage Page List View Columns
     *
     * @param   array   $columns
     * @return  array   $columns
     * @acess   public
     */
    public function pages_columns( $columns ) {

        // checkbox
        $checkbox_pages = (int)get_theme_mod( 'adminizr_columns_checkbox_pages', 0 );
        if ( $checkbox_pages === 1 ) { 
    	    unset( $columns['cb'] );
	    }

        // title
        $title_pages = (int)get_theme_mod( 'adminizr_columns_title_pages', 0 );
        if ( $title_pages === 1 ) { 
    	    unset( $columns['title'] );
	    }
        
        // author
        $author_pages = (int)get_theme_mod( 'adminizr_columns_author_pages', 0 );
        if ( $author_pages === 1 ) { 
    	    unset( $columns['author'] );
    	}
        
        // categories
        $categories_pages = (int)get_theme_mod( 'adminizr_columns_categories_pages', 0 );
        if ( $categories_pages === 1 ) { 
    	    unset( $columns['categories'] );
	    }

        // tags
        $tags_pages = (int)get_theme_mod( 'adminizr_columns_tags_pages', 0 );
        if ( $tags_pages === 1 ) { 
    	    unset( $columns['tags'] );
    	}
        
        // comments
        $comments_pages = (int)get_theme_mod( 'adminizr_columns_comments_pages', 0 );
        if ( $comments_pages === 1 ) { 
    	    unset( $columns['comments'] );
	    }
    
        // date
        $date_pages = (int)get_theme_mod( 'adminizr_columns_date_pages', 0 );
        if ( $date_pages === 1 ) { 
    	    unset( $columns['date'] );
    	}

        return $columns;
    }

    /**
     * Manage Media List View Columns
     *
     * @param   array   $columns
     * @return  array   $columns
     * @acess   public
     */
    public function media_columns( $columns ) {

        // checkbox
        $checkbox_media = (int)get_theme_mod( 'adminizr_columns_checkbox_media', 0 );
        if ( $checkbox_media === 1 ) { 
    	    unset( $columns['cb'] );
	    }

        // icon
        $icon_media = (int)get_theme_mod( 'adminizr_columns_icon_media', 0 );
        if ( $icon_media === 1 ) { 
    	    unset( $columns['icon'] );
	    }

        // title
        $title_media = (int)get_theme_mod( 'adminizr_columns_title_media', 0 );
        if ( $title_media === 1 ) { 
    	    unset( $columns['title'] );
	    }

        // author
        $author_media = (int)get_theme_mod( 'adminizr_columns_author_media', 0 );
        if ( $author_media === 1 ) { 
    	    unset( $columns['author'] );
	    }
    
        // parent
        $parent_media = (int)get_theme_mod( 'adminizr_columns_parent_media', 0 );
        if ( $parent_media === 1 ) { 
    	    unset( $columns['parent'] );
	    }
    
        // comments
        $comments_media = (int)get_theme_mod( 'adminizr_columns_comments_media', 0 );
        if ( $comments_media === 1 ) { 
    	    unset( $columns['comments'] );
	    }
    
        // date
        $date_media = (int)get_theme_mod( 'adminizr_columns_date_media', 0 );
        if ( $date_media === 1 ) {  
    	    unset( $columns['date'] );
	    }
        
        return $columns;
    }

    /*************************************************/
    /** Action & Filter Functionality - MENU        **/
    /*************************************************/
    
    /**
     * Remove menu items from admin menu
     *
     * @access public
     */
    public function remove_menus() {

        // Remove Dashboard
        $menu_dashboard = (int)get_theme_mod( 'adminizr_menu_dashboard', 0 );
        if ( $menu_dashboard === 1 ) {
            remove_menu_page( 'index.php' );
        }

        // Remove Posts
        $menu_posts = (int)get_theme_mod( 'adminizr_menu_posts', 0 );
        if ( $menu_posts === 1 ) {
            remove_menu_page( 'edit.php' );
        }

        // Remove Media
        $menu_media = (int)get_theme_mod( 'adminizr_menu_media', 0 );
        if ( $menu_media === 1 ) {
            remove_menu_page( 'upload.php' );
        }

        // Remove Links
        $menu_links = (int)get_theme_mod( 'adminizr_menu_links', 0 );
        if ( $menu_links === 1 ) {
            remove_menu_page( 'edit-tags.php?taxonomy=link_category' );
        }

        // Remove Pages
        $menu_pages = (int)get_theme_mod( 'adminizr_menu_pages', 0 );
        if ( $menu_pages === 1 ) {
            remove_menu_page( 'edit.php?post_type=page' );
        }

        // Remove Comments
        $menu_comments = (int)get_theme_mod( 'adminizr_menu_comments', 0 );
        if ( $menu_comments === 1 ) {
            remove_menu_page( 'edit-comments.php' );
        }

        // Remove Appearance
        $menu_appearance = (int)get_theme_mod( 'adminizr_menu_appearance', 0 );
        if ( $menu_appearance === 1 ) {
            remove_menu_page( 'themes.php' );
        }

        // Remove Plugins
        $menu_plugins = (int)get_theme_mod( 'adminizr_menu_plugins', 0 );
        if ( $menu_plugins === 1 ) {
            remove_menu_page( 'plugins.php' );
        }

        // Remove Users
        $menu_users = (int)get_theme_mod( 'adminizr_menu_users', 0 );
        if ( $menu_users === 1 ) {
            remove_menu_page( 'users.php' );
        }  

        // Remove Tools
        $menu_tools = (int)get_theme_mod( 'adminizr_menu_tools', 0 );
        if ( $menu_tools === 1 ) {
            remove_menu_page( 'tools.php' );
        }  

        // Remove Settings
        $menu_settings = (int)get_theme_mod( 'adminizr_menu_settings', 0 );
        if ( $menu_settings === 1 ) {
            remove_menu_page( 'options-general.php' );
        }    
    }

    /**
     * Remove menu items from admin menu
     *
     * @access public
     */
    public function rename_menus() {

        global $menu, $submenu;
    
        // Rename Dashboard
        $menu_rename_dashboard = get_theme_mod( 'adminizr_menu_rename_dashboard', '' );
        if ( !empty( $menu_rename_dashboard ) ) {
            $menu[2][0] = $menu_rename_dashboard;
        }

        // Rename Posts
        $menu_rename_posts = get_theme_mod( 'adminizr_menu_rename_posts', '' );
        if ( !empty( $menu_rename_posts ) ) {
            $menu[5][0] = $menu_rename_posts;
            $submenu['edit.php'][5][0] = $menu_rename_posts;
        }

        // Rename Media
        $menu_rename_media = get_theme_mod( 'adminizr_menu_rename_media', '' );
        if ( !empty( $menu_rename_media ) ) {
            $menu[10][0] = $menu_rename_media;
        }

        // Rename Links
        $menu_rename_links = get_theme_mod( 'adminizr_menu_rename_links', '' );
        if ( !empty( $menu_rename_links ) ) {
            $menu[15][0] = $menu_rename_links;
        }

        // Rename Pages
        $menu_rename_pages = get_theme_mod( 'adminizr_menu_rename_pages', '' );
        if ( !empty( $menu_rename_pages ) ) {
            $menu[20][0] = $menu_rename_pages;
            $submenu['edit.php?post_type=page'][5][0] = $menu_rename_pages;
        }
    
        // Rename Comments
        $menu_rename_comments = get_theme_mod( 'adminizr_menu_rename_comments', '' );
        if ( !empty( $menu_rename_comments ) ) {
            $menu[25][0] = $menu_rename_comments;
        }
    
        // Rename Appearance
        $menu_rename_appearance = get_theme_mod( 'adminizr_menu_rename_appearance', '' );
        if ( !empty( $menu_rename_appearance ) ) {
            $menu[60][0] = $menu_rename_appearance;
        }
    
        // Rename Plugins
        $menu_rename_plugins = get_theme_mod( 'adminizr_menu_rename_plugins', '' );
        if ( !empty( $menu_rename_plugins ) ) {
            $menu[65][0] = $menu_rename_plugins;
            $submenu['plugins.php'][10][0] = 'Installed ' . $menu_rename_plugins;
        }
    
        // Rename Users
        $menu_rename_users = get_theme_mod( 'adminizr_menu_rename_users', '' );
        if ( !empty( $menu_rename_users ) ) {
            $menu[70][0] = $menu_rename_users;
            $submenu['users.php'][5][0] = 'All ' . $menu_rename_users;
        }

        // Rename Tools
        $menu_rename_tools = get_theme_mod( 'adminizr_menu_rename_tools', '' );
        if ( !empty( $menu_rename_tools ) ) {
            $menu[75][0] = $menu_rename_tools;
            $submenu['tools.php'][5][0] = 'Available ' . $menu_rename_tools;
        }
    
        // Rename Settings
        $menu_rename_settings = get_theme_mod( 'adminizr_menu_rename_settings', '' );
        if ( !empty( $menu_rename_settings ) ) {
            $menu[80][0] = $menu_rename_settings;
        }
    }

    /**
     * Post type objects
     *
     * @access public
     */
    public function post_object() {
        global $wp_post_types;

        $menu_rename_posts = get_theme_mod( 'adminizr_menu_rename_posts', '' );
        if ( !empty( $menu_rename_posts ) ) { return; }

        $labels = &$wp_post_types['post']->labels;
        $labels->name = $menu_rename_posts;
        $labels->singular_name = $menu_rename_posts;
        $labels->add_new = 'Add ' . $menu_rename_posts;
        $labels->add_new_item = 'Add ' . $menu_rename_posts;
        $labels->edit_item = 'Edit ' . $menu_rename_posts;
        $labels->new_item = $menu_rename_posts;
        $labels->view_item = 'View ' . $menu_rename_posts;
        $labels->search_items = 'Search ' . $menu_rename_posts;
        $labels->not_found = 'No ' . $menu_rename_posts.' found';
        $labels->not_found_in_trash = 'No ' . $menu_rename_posts . ' found in Trash';
        $labels->all_items = 'All ' . $menu_rename_posts;
        $labels->menu_name = $menu_rename_posts;
        $labels->name_admin_bar = $menu_rename_posts;
    }

    /*************************************************/
    /** Action & Filter Functionality - OTHER       **/
    /*************************************************/

    /**
     * Output admin favicon head link
     *
     * @access public
     */
    public function adminizr_favicon() {
        $other_favicon = get_theme_mod( 'adminizr_other_icon', '' );
		echo '<link rel="Shortcut Icon" type="image/x-icon" href="' . $other_favicon . '" />';
    }
    
    /**
     * Remove disable visual editor
     *
     * @param   array $hook
     * @access  public
     */
	public function remove_visual_editor( $hook ) { ?>
	    <style type="text/css">
	        #your-profile .form-table .user-rich-editing-wrap { display:none!important;visibility:hidden!important; }
	    </style>
	    <?php
	} 
    
    /**
     * Set the default user color scheme
     *
     * @access public
     */
    public function set_default_color_scheme() {

        // default
        $default_color_scheme = get_theme_mod( 'adminizr_other_default_color_scheme', '' );
        if ( empty( $default_color_scheme ) ) { return; }

        //get users
        $users = get_users();
        //set per user
		foreach ($users as $user) {
			if (!user_can( $user->ID, 'administrator' )) {
				update_user_meta($user->ID, 'adminizr_color', $default_color_scheme );
			}
		}
    }

   /**
    * Set the version admin notice
    *
    * @access public
    */
    public function version_notice() { 
?>        
    <div class="update-nag">
        <p><?php __( 'This version of WordPress ( < 4.0 ) is incompatible with Adminizr. Please update', $this->text ); ?></p>
    </div> 
<?php
    }
}

global $adminizr;
$adminizr = new Adminizr;
//end
