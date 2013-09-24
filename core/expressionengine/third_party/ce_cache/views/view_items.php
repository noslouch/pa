<?php

if ( ! empty( $pagination ) )
{
	echo '<p class="ce_cache_pagination">' . $pagination . '</p>';
}
?>
<p id="ce_cache_breadcrumbs"><!-- --></p><!-- #ce_cache_breadcrumbs -->
<div id="ce_cache_tree">
	<p>
	<span class="ce_cache_items"><?php
		echo lang( 'ce_cache_items' ); ?></span><span class="ce_cache_made"><?php echo lang( 'ce_cache_created' ); ?></span><span class="ce_cache_expiry"><?php echo lang( 'ce_cache_expires' ); ?></span>
	</p>

	<div id="ce_cache_items_holder">
		<div id="ce_cache_items_list"><!-- --></div>
		<div id="ce_cache_loader" class="ce_cache_empty"><!-- --></div>
	</div><!-- #ce_cache_items_holder -->
</div><!-- #ce_cache_tree -->
<div id="ce_cache_delete_dialog"><!-- --></div>
<?php
if ( ! empty( $pagination ) )
{
	echo '<p class="ce_cache_pagination">' . $pagination . '</p>';
}
echo '<div style="display: block; clear: both; margin-bottom: 15px;"><!-- --></div>';
echo $back_link;
?>