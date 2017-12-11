<?php
/**
 * typography.php
 * @package logo-carousel
 */
?>
<div id="wpllc-tab-4" class="wplmb-tab-content">
	<?php
	$the_pro_handler = new WPLLC_Free_Loader();
	$pro_notice      = $the_pro_handler->admin_page_handler();
	echo $pro_notice;
	?>
</div>
