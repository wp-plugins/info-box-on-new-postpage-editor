<?php
/*
Plugin Name: Info Box on New Post/Page Editor
Plugin URI: http://roesapps.com/2010/01/adding-a-remin…ox-to-add-post/
Description: Add a text (reminder) box on the New/Edit Post editing page
Author: Courtney Roes
Version: 0.1
Author URI: http://www.RoesApps.com
*/   
   
/*  Copyright 2010  

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Guess the wp-content and plugin urls/paths
*/
// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );


if (!class_exists('InfoBox')) {
    class InfoBox {
        //This is where the class variables go, don't forget to use @var to tell what they're for
        /**
        * @var string The options string name for this plugin
        */
        var $optionsName = 'InfoBox_options';
        
        /**
        * @var string $localizationDomain Domain used for localization
        */
        var $localizationDomain = "InfoBox";
        
        /**
        * @var string $pluginurl The path to this plugin
        */ 
        var $thispluginurl = '';
        /**
        * @var string $pluginurlpath The path to this plugin
        */
        var $thispluginpath = '';
            
        /**
        * @var array $options Stores the options for this plugin
        */
        var $options = array();
        
        //Class Functions
        /**
        * PHP 4 Compatible Constructor
        */
        function InfoBox(){$this->__construct();}
        
        /**
        * PHP 5 Constructor
        */        
        function __construct(){
            //Language Setup
            $locale = get_locale();
            $mo = dirname(__FILE__) . "/languages/" . $this->localizationDomain . "-".$locale.".mo";
            load_textdomain($this->localizationDomain, $mo);

            //"Constants" setup
            $this->thispluginurl = PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
            $this->thispluginpath = PLUGIN_PATH . '/' . dirname(plugin_basename(__FILE__)).'/';
            
            //Initialize the options
            //This is REQUIRED to initialize the options when the plugin is loaded!
            $this->getOptions();
            
            //Actions        
            add_action("admin_menu", array(&$this,"admin_menu_link"));
			add_action("admin_menu", array(&$this,"InfoBox_MakeBox"));

            
            //Widget Registration Actions
            add_action('plugins_loaded', array(&$this,'register_widgets'));
            
            /*
            add_action("wp_head", array(&$this,"add_css"));
            add_action('wp_print_scripts', array(&$this, 'add_js'));
            */
            
            //Filters
            /*
            add_filter('the_content', array(&$this, 'filter_content'), 0);
            */
        }
        

		
		/* Adds a custom section to the "advanced" Post and Page edit screens */
		function InfoBox_MakeBox() {
		
		  if( function_exists( 'add_meta_box' )) {
			add_meta_box( 'InfoBox_MakeBoxID2', __( $this->options['InfoBox_Title'], 'InfoBox_textdomain' ), 
						array(&$this,'InfoBox_inner_custom_box'), 'post', 'side', 'high' );
			add_meta_box( 'InfoBox_MakeBoxID2', __( $this->options['InfoBox_Title'], 'InfoBox_textdomain' ), 
						array(&$this,'InfoBox_inner_custom_box'), 'page', 'side', 'high' );
		   }
		}
		
		/* Prints the inner fields for the custom post/page section */
		function InfoBox_inner_custom_box() {
		
		  // Use nonce for verification
		
		  echo '<input type="hidden" name="myplugin_noncename" id="myplugin_noncename" value="' . 
			wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
		
		  // The actual fields for data entry
		  echo $this->options['InfoBox_Text'];
		}
        
        
        /**
        * Retrieves the plugin options from the database.
        * @return array
        */
        function getOptions() {
            //Don't forget to set up the default options
            if (!$theOptions = get_option($this->optionsName)) {
                $theOptions = array('default'=>'options');
                update_option($this->optionsName, $theOptions);
            }
            $this->options = $theOptions;
            
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            //There is no return here, because you should use the $this->options variable!!!
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        }
        /**
        * Saves the admin options to the database.
        */
        function saveAdminOptions(){
            return update_option($this->optionsName, $this->options);
        }
        
        /**
        * @desc Adds the options subpanel
        */
        function admin_menu_link() {
            //If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to
            //reflect the page filename (ie - options-general.php) of the page your plugin is under!
            add_options_page('Info Box on New Post Editor', 'Info Box on New Post Editor', 10, basename(__FILE__), array(&$this,'admin_options_page'));
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
        }
        
        /**
        * @desc Adds the Settings link to the plugin activate/deactivate page
        */
        function filter_plugin_actions($links, $file) {
           //If your plugin is under a different top-level menu than Settiongs (IE - you changed the function above to something other than add_options_page)
           //Then you're going to want to change options-general.php below to the name of your top-level page
           $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
           array_unshift( $links, $settings_link ); // before other links

           return $links;
        }
        
        /**
        * Adds settings/options page
        */
        function admin_options_page() { 
            if($_POST['InfoBox_save']){
                if (! wp_verify_nonce($_POST['_wpnonce'], 'InfoBox-update-options') ) die('Whoops! There was a problem with the data you posted. Please go back and try again.'); 
                $this->options['InfoBox_Title'] = $_POST['InfoBox_Title'];                   
                $this->options['InfoBox_Text'] = $_POST['InfoBox_Text'];
                                        
                $this->saveAdminOptions();
                
                echo '<div class="updated"><p>Success! Your changes were sucessfully saved!</p></div>';
            }
?>                                   
                <div class="wrap">
                <h2>Info Box on New Post Editor</h2>
                <form method="post" id="InfoBox_options">
                <?php wp_nonce_field('InfoBox-update-options'); ?>
                    <table width="100%" cellspacing="2" cellpadding="5" class="form-table"> 
                        <tr valign="top"> 
                            <th width="33%" scope="row"><?php _e('Box Title:', $this->localizationDomain); ?></th> 
                            <td><input name="InfoBox_Title" type="text" id="InfoBox_Title" size="45" value="<?php echo $this->options['InfoBox_Title'] ;?>"/>
                        </td> 
                        </tr>
                        <tr valign="top"> 
                            <th width="33%" scope="row"><?php _e('Box Text:', $this->localizationDomain); ?></th> 
                            <td>
                            <textarea name="InfoBox_Text" id="InputBox" cols="45" rows="5" ><?php echo $this->options['InfoBox_Text'] ;?></textarea>
                            </td> 
                        </tr>
                        <tr valign="top">
                        	<th width="33%" scope="row"><?php _e('Note:', $this->localizationDomain); ?></th>
                            <td>
                            <strong>You may include basic html however do not include single quotes, double quotes or backslashes.  To get a carriage return to show up, insert &lt;br/&gt; in the text</strong>
                            </td>
                        <tr>
                            <th colspan=2><input type="submit" name="InfoBox_save" value="Save" /></th>
                        </tr>
                    </table>
                </form>
                <?php
        }
        
        /*
        * ============================
        * Plugin Widgets
        * ============================
        */                        
        function register_widgets() {
            //Make sure the widget functions exist
            if ( function_exists('wp_register_sidebar_widget') ) {
                //============================
                //Example Widget 1
                //============================
                function display_InfoBoxWidget($args) {                    
                    extract($args);
                    echo $before_widget . $before_title . $this->options['title'] . $after_title;
                    echo '<ul>';
                    //!!! Widget 1 Display Code Goes Here!
                    echo '</ul>';
                    echo $after_widget;
                }                                                                             
                function InfoBoxWidget_control() {            
                    if ( $_POST["InfoBox_InfoBoxWidget_submit"] ) {
                        $this->options['InfoBox-comments-title'] = stripslashes($_POST["InfoBox-comments-title"]);        
                        $this->options['InfoBox-comments-template'] = stripslashes($_POST["InfoBox-comments-template"]);
                        $this->options['InfoBox-hide-admin-comments'] = ($_POST["InfoBox-hide-admin-comments"]=='on'?'':'1');
                        $this->saveAdminOptions();
                    }                                                                  
                    $title = htmlspecialchars($options['InfoBox-comments-title'], ENT_QUOTES);
                    $template = htmlspecialchars($options['InfoBox-comments-template'], ENT_QUOTES);
                    $hide_admin_comments = $options['InfoBox-hide-admin-comments'];      
                ?>
                    <p><label for="InfoBox-comments-title"><?php _e('Title:', $this->localizationDomain); ?> <input style="width: 250px;" id="InfoBox-comments-title" name="InfoBox-comments-title" type="text" value="<?= $title; ?>" /></label></p>               
                    <p><label for="InfoBox-comments-template"><?php _e('Template:', $this->localizationDomain); ?> <input style="width: 250px;" id="InfoBox-comments-template" name="InfoBox-comments-template" type="text" value="<?= $template; ?>" /></label></p>
                    <p><?php _e('The template is made up of HTML and tokens. You can get a list of available tokens at the', $this->localizationDomain); ?> <a href='http://pressography.com/plugins/wp-InfoBox/#tokens-recent' target='_blank'><?php _e('plugin page', $this->localizationDomain); ?></a></p>
                    <p><input id="InfoBox-hide-admin-comments" name="InfoBox-hide-admin-comments" type="checkbox" <?= ($hide_admin_comments=='1')?'':'checked="CHECKED"'; ?> /> <label for="InfoBox-hide-admin-comments"><?php _e('Show Admin Comments', $this->localizationDomain); ?></label></p>
                    <input type="hidden" id="InfoBox_InfoBoxWidget_submit" name="InfoBox_InfoBoxWidget_submit" value="1" />
                <?php
                }
                $widget_ops = array('classname' => 'InfoBoxWidget', 'description' => __( 'Widget Description', $this->localizationDomain ) );
                wp_register_sidebar_widget('InfoBox-InfoBoxWidget', __('Widget Title', $this->localizationDomain), array($this, 'display_InfoBoxWidget'), $widget_ops);
                wp_register_widget_control('InfoBox-InfoBoxWidget', __('Widget Title', $this->localizationDomain), array($this, 'InfoBoxWidget_control'));
                
            }  
        }

      
        
  } //End Class

} //End if class exists statement

//instantiate the class
if (class_exists('InfoBox')) {
    $InfoBox_var = new InfoBox();
}
?>