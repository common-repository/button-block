<?php
class BTNBlock{
	function __construct(){
		add_action( 'enqueue_block_assets', [$this, 'enqueueBlockAssets'] );
		add_action( 'init', [$this, 'onInit'] );
	}

	function enqueueBlockAssets(){
		wp_register_style( 'fontAwesome', BTN_DIR_URL . 'assets/css/font-awesome.min.css', [], '6.4.2' );
		wp_register_style( 'aos', BTN_DIR_URL . 'assets/css/aos.css', [], '3.0.0' );
		wp_register_script( 'aos', BTN_DIR_URL . 'assets/js/aos.js', [], '3.0.0', true );
	}

	function onInit() {
		wp_register_style( 'btn-button-style', BTN_DIR_URL . 'dist/style.css', [ 'fontAwesome', 'aos' ], BTN_VERSION ); // Style
		wp_register_style( 'btn-button-editor-style', BTN_DIR_URL . 'dist/editor.css', [ 'btn-button-style' ], BTN_VERSION ); // Backend Style

		register_block_type( __DIR__, [
			'editor_style'		=> 'btn-button-editor-style',
			'render_callback'	=> [$this, 'render']
		] ); // Register Block

		wp_set_script_translations( 'btn-button-editor-script', 'button-block', BTN_DIR_PATH . 'languages' );
	}


function my_get_current_user_roles() {
 
  if( is_user_logged_in() ) {
 
    $user = wp_get_current_user();
 
    $roles = ( array ) $user->roles;
 
    return $roles;
 
  } else {
 
    return array();
 
  }
 
}
function get_login_url() {
  
    $site_url = wp_login_url();

    return $site_url;
}

	function render( $attributes ){
		extract( $attributes );

		wp_enqueue_style( 'btn-button-style' );
		wp_enqueue_script( 'btn-button-script', BTN_DIR_URL . 'dist/script.js', [ 'wp-util', 'react', 'react-dom', 'aos' ], BTN_VERSION, true );
		wp_set_script_translations( 'btn-button-script', 'button-block', BTN_DIR_PATH . 'languages' );

		$className = $className ?? '';
		$extraClass = btnIsPremium() ? 'premium' : 'free';
		$blockClassName = "wp-block-btn-button $extraClass $className align$align";

		$popup = $popup ?? [ 'type' => 'image', 'content' => '', 'caption' => '' ];

		if ( 'content' === $popup['type'] ) {
			$blocks = parse_blocks( $popup['content'] );
			$popup['content'] = '';
			foreach ( $blocks as $block ) {
				$popup['content'] .= render_block( $block );
			}
		} // Convert the blocks to dom elements

		ob_start(); ?>
		<div class='<?php echo esc_attr( $blockClassName ); ?>' id='btnButton-<?php echo esc_attr( $cId ) ?>' data-role=<?php echo esc_attr(wp_json_encode($this->my_get_current_user_roles())); ?> data-loginurl=<?php echo esc_attr(wp_json_encode($this->get_login_url())); ?> data-attributes='<?php echo esc_attr( wp_json_encode( array_replace( $attributes, [ 'popup' => $popup ] ) ) ); ?>' data-nonce='<?php echo esc_attr( wp_json_encode( wp_create_nonce( 'wp_ajax' ) ) ); ?>'></div>

		<?php return ob_get_clean();
	} // Render
}
new BTNBlock();