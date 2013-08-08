<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div id="woothemes-updater" class="wrap">
	<?php screen_icon( 'index' ); ?><div class="wrap"><h2><?php echo $this->name; ?></h2>
<div id="col-container">
	<?php
	echo '<div class="updated fade">' . wpautop( __( 'See below for a list of the WooThemes products active on this installation. You can <a href="https://www.woothemes.com/my-account/my-licenses">view your licenses here</a>, as well as our <a href="https://www.woothemes.com/my-account/my-licenses/#faq">FAQ</a> on how this works.', 'woothemes-updater' ) ) . '</div>' . "\n";
	?>
		<div class="col-wrap">
			<form id="activate-products" method="post" action="" class="validate">
				<input type="hidden" name="action" value="activate-products" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->page_slug ); ?>" />
				<?php
				require_once( $this->classes_path . 'class-woothemes-updater-licenses-table.php' );
				$this->list_table = new WooThemes_Updater_Licenses_Table();
				$this->list_table->data = $this->get_detected_products();
				$this->list_table->prepare_items();
				$this->list_table->display();
				submit_button( __( 'Activate Products', 'woothemes-updater' ), 'button-primary' );
				?>
			</form>
		</div><!--/.col-wrap-->
</div><!--/#col-container-->
</div><!--/#woothemes-updater-->