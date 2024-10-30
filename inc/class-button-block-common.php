<?php
namespace BTN\Inc;
class Button_Common{
  function __construct(){
		add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
		add_action( 'init', [$this, 'btn_pro_post_type'] );
		add_action( 'admin_menu', [$this, 'helpSubMenu'] );
    add_action('post_row_actions', array($this, 'btn_block_add_duplicate_link'), 10, 2);
    add_action('admin_action_duplicate_btn_block_post', array($this, 'btn_block_duplicate_post'));
    add_filter('use_block_editor_for_post', array($this ,'btn_block_use_block_editor_callback'), 999, 2);
    add_filter( 'manage_button-block_posts_columns', [$this, 'manageBTNBlockPostsColumns'], 10 );  
		add_action( 'manage_button-block_posts_custom_column', [$this, 'manageBTNBlockPostsCustomColumns'], 10, 2 );
    add_shortcode( 'btn_block', [$this, 'onBtnBlockAddShortcode'] );
    add_action('wp_ajax_custom_get_user_roles', array($this, 'btn_custom_get_user_roles_callback'));
    add_action( 'admin_init', array($this, 'add_option_in_general_settings') );
	}

  function adminEnqueueScripts( $hook ) {
		if( strpos( $hook, 'button-block-help' ) ){
			wp_enqueue_style( 'btn-admin-style', BTN_DIR_URL . 'dist/admin-help.css', false, BTN_VERSION );
			wp_enqueue_script( 'btn-admin-script', BTN_DIR_URL . 'dist/admin-help.js', [ 'react', 'react-dom' ], BTN_VERSION, true );
		}
      if( 'edit.php' === $hook || 'post.php' === $hook ){
			wp_enqueue_style( 'btn-block-admin-post', BTN_DIR_URL . 'assets/css/admin-post.css', [], BTN_VERSION );
			wp_enqueue_script( 'btn-block-admin-post', BTN_DIR_URL . 'assets/js/admin-post.js', [], BTN_VERSION );
			
		}

     wp_enqueue_script('rest-uploader', plugins_url('src/Components/Backend/Settings/settings.js', dirname(__FILE__)), ['jquery' ]);
        
        $js_vars = [
           'endpoint' => admin_url('admin-ajax.php'),
           'nonce' => wp_create_nonce('wp_rest'),
        ];       
        wp_localize_script('rest-uploader', 'RestVars', $js_vars);
	}

  function helpSubMenu(){
    add_submenu_page("edit.php?post_type=button-block", __( 'Button Block Help', 'button-block' ), __( 'Help', 'button-block' ), 'manage_options', 'button-block-help',[$this, 'helpPage'], 10);
  }

  function btn_pro_post_type()
{
    $menuIcon = "<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 48 48' fill='#fff'><path d='M7 34Q5.75 34 4.875 33.125Q4 32.25 4 31V17Q4 15.75 4.875 14.875Q5.75 14 7 14H41Q42.25 14 43.125 14.875Q44 15.75 44 17V31Q44 32.25 43.125 33.125Q42.25 34 41 34H37.8V31H41Q41 31 41 31Q41 31 41 31V17Q41 17 41 17Q41 17 41 17H7Q7 17 7 17Q7 17 7 17V31Q7 31 7 31Q7 31 7 31H20.2V34ZM29 38 27.2 34 23.2 32.2 27.2 30.4 29 26.4 30.8 30.4 34.8 32.2 30.8 34ZM34 27.4 32.95 25.05 30.6 24 32.95 22.95 34 20.6 35.05 22.95 37.4 24 35.05 25.05Z'></path></svg>";
    $hide_form_menu = get_option( 'button_block_option', 'false' );
    $show_ui = ($hide_form_menu === 'true') ? false : true;
    register_post_type(
        'button-block',
        array(
            'labels' => array(
                'name' => __('Button Block'),
                'singular_name' => __('Button Block'),
                'add_new' => __('Add Button Block'),
                'add_new_item' => __('Add New Button '),
                'edit_item' => __('Edit Button '),
                'new_item' => __('New Button '),
                'view_item' => __('View Button '),
                'search_items' => __('Search Button '),
                'not_found' => __('Sorry, we couldn\'t find any item you are looking for.')
            ),
            'public' => false,
            'show_ui' => $show_ui,
            'publicly_queryable' => false,
            'exclude_from_search' => false,
            'show_in_rest'			=> true,
            'menu_position' => 58,
            'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode($menuIcon),	
           	'has_archive'			=> false,
			'hierarchical'			=> false,
			'capability_type'		=> 'page',
			'rewrite'				=> [ 'slug' => 'button-block' ],
			'supports'				=> [ 'title', 'editor' ],
			'template'				=> [ ['btn/button'] ],
			'template_lock'			=> 'all',
        )
    );
}

function helpPage(){ ?>
		<div class='bplAdminHelpPage'></div>
	<?php }


	function btn_block_add_duplicate_link($actions, $post)
	{
	   if ($post->post_type == 'button-block') {
		  $actions['duplicate'] = '<a href="' . admin_url("admin.php?action=duplicate_btn_block_post&post={$post->ID}") . '">Duplicate</a>';
	   }
	   return $actions;
	}

    function btn_block_duplicate_post()
    {
        if (!isset($_GET['post']) || !current_user_can('edit_posts')) {
            wp_die('Permission denied');
        }

        $post_id = $_GET['post'];
        $post = get_post($post_id);

        if (!$post) {
            wp_die('Invalid post ID');
        }

        $new_post = array(
            'post_title' => $post->post_title . '(copy)',
            'post_content' => $post->post_content,
            'post_status' => $post->post_status,
            'post_type' => $post->post_type,
        );

        $new_post_id = wp_insert_post($new_post);
        wp_redirect(admin_url("post.php?action=edit&post={$new_post_id}"));
        exit;
    }

    function btn_block_use_block_editor_callback($use_block_editor, $post_type){
        if ($post_type->post_type === "button-block") {
            return true;
        }
        return $use_block_editor;
    }

  function manageBTNBlockPostsColumns( $defaults ) {
		unset( $defaults['date'] );
		$defaults['shortcode'] = 'ShortCode';
		$defaults['date'] = 'Date';
		return $defaults;
	}

   function manageBTNBlockPostsCustomColumns( $column_name, $post_ID ) {
		if ( $column_name == 'shortcode' ) {
			echo "<div class='bPlAdminShortcode' id='bPlAdminShortcode-$post_ID'>
				<input value='[btn_block id=$post_ID]' onclick='copyBPlAdminShortcode($post_ID)'>
				<span class='tooltip'>Copy To Clipboard</span>
			</div>";
		}
	}

  function onBtnBlockAddShortcode( $atts ) {
		$post_id = $atts['id'];

		$post = get_post( $post_id );
		$blocks = parse_blocks( $post->post_content );

		ob_start();
		echo render_block($blocks[0]);

		return ob_get_clean();
	}

    function btn_custom_get_user_roles_callback()
  {
    
      global $wp_roles;
      $roles = $wp_roles->get_names();

      wp_send_json_success($roles);
  }

  function add_option_in_general_settings(){
          register_setting(
          'general',    
          'button_block_option', 
          'sanitize_text_field' 
      );

      add_settings_field(
          'button_block_option_field', 
          'Hide Button Block from admin Menu',    
          array($this , "button_block_option_callback"), 
          'general'                 
      );

  }

  function button_block_option_callback() {
      // Get the current value from the database, default is 'off'
      $value = get_option( 'button_block_option', 'false' );
      ?>
      <label class="switch">
        <input type="checkbox" id="button_block_option" name="button_block_option" value="true" <?php checked( $value, 'true' ); ?>>
        <span class="slider round"></span>
      </label>
      <p class="description">Turn this setting on or off.</p>

      <style>
        /* Add styles for a nice looking switcher */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input { 
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }
      </style>
      <?php
  }


  
}

new Button_Common();