<?php
class BTNUpgradePage{
	public function __construct(){
		add_action( 'admin_menu', [$this, 'adminMenu'] );
		add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
	}

	function adminMenu(){
		add_submenu_page(
			'edit.php?post_type=button-block',
			__( 'Advanced Posts - Upgrade', 'button-block' ),
			__( 'Upgrade', 'button-block' ),
			'manage_options',
			'btn-upgrade',
			[$this, 'upgradePage']
		);
	}

	function upgradePage(){ ?>
		<div id='bplUpgradePage'></div>
	<?php }

	function adminEnqueueScripts( $hook ) {
		if( strpos( $hook, 'btn-upgrade' ) ){
			wp_enqueue_script( 'btn-admin-upgrade', BTN_DIR_URL . 'dist/admin-upgrade.js', [ 'react', 'react-dom' ], BTN_VERSION );
			wp_set_script_translations( 'btn-admin-upgrade', 'button-block', BTN_DIR_PATH . 'languages' );
		}
	}
}
new BTNUpgradePage;