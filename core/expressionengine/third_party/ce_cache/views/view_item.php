<p><?php echo sprintf( lang( "{$module}_viewing_item_meta" ), preg_replace( '@^' . preg_quote( $prefix, '@' ) . '@', '', $item ) ) ?></p>

<p><?php echo '<b>' . lang( "{$module}_created" ) . '</b>: ' . $made; ?></p>
<p><?php echo '<b>' . lang( "{$module}_ttl" ) . '</b>: ' . $ttl ?></p>
<p><?php
	echo '<b>' . lang( "{$module}_expires" ) . '</b>: ' . $expiry;
	if ( $ttl_remaining != 0 )
	{
		echo ' (' . $ttl_remaining . ' ' . lang( "{$module}_seconds_from_now" ) . ')';
	}
?></p>
<p><?php echo '<b>' . lang( "{$module}_size" ) . '</b>: ' . $size . ' (' . $size_raw . ' ' . lang( "{$module}_bytes" ) . ')' ?></p>
<?php
$tag_count = count( $tags );
if ( ! empty( $tag_count ) )
{
	echo '<p><b>';
	echo ( $tag_count === 1 ) ? lang( "{$module}_tag" ) : lang( "{$module}_tags" );
	echo '</b>: <code>' . implode('</code>|<code>', $tags ) . '</code></p>';
}
?>
<p><?php echo '<b>' . lang( "{$module}_content" ) . '</b>: ' ?></p>

<pre id="ce_cache_code_holder">
<?php echo htmlentities( $content ) ?>
</pre><!-- #ce_cache_code_holder -->

<p><?php echo $back_link; ?></p>