<script type="text/javascript">
	$().ready( function() {
		$("a.help").click( function() {
			$( this ).attr({ 
				target: "_blank"
			});
		})
	})
</script>
<style type="text/css" media="screen">
	table.mainTable tr td.box { 
		background: #fdfcd1; 
		line-height:130%;
	}
	.info {
		border: 1px solid #d0d7df;
		background-color: #f4f6f6;
		padding: 8px 16px;
		line-height: 150%;
	}
	table.noHeader td,
	table.noHeader td:last-child {
		border: 0 !important;
	}
	.help {
	font-size: 11px;
	padding: 0 0 0 14px;
width: 12px;
height: 12px;
xdisplay: block;
xtext-indent: 100%;
xwhite-space: nowrap;
xoverflow: hidden;
background-repeat: no-repeat;
background-position: left 50%;
background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABV0RVh0Q3JlYXRpb24gVGltZQAyMS84LzEzEDoYzwAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNXG14zYAAAEgSURBVCiRfZG/S8NwFMQ/+Vp/QCGhIigu6iqIwUUnCXRykQyCLkXBueCkg0sQ+geo4KR1CAUFhQ4dirVQMhXqUMziZnWwolMEB3Vx6FO+1ugb790d9+4ZyEzNZ8YBD3ABS+AIKAJeGPgtAEPINlDTiN0TAU4Y+E1DnJs6edlNM5gyyRdKvL1/6CJbSYxv8tVlnoX0HLMzkzQqR/T39X6tLMBTkrmDmEke2s+sZXPsH54BMDoypEdzle4evbyymNnCMpMc721Trta5vW/rAkvFXbib26BcrbO5c/Brl5BjfrRTOL+I8wGIEnR6XtXR7PoSAJVao1tQjK31L3fAVvJBR4D/yE4Y+K0egKe768fhsekTIAVMAAMa8RRYCQP/BuATFnVSVQYE6VoAAAAASUVORK5CYII=);
	}
</style>

<?php if( isset( $errors ) && count( $errors ) ) {
	foreach( $errors as $error ) {
		echo '<p class="notice">Error: ' . $error . '</p>';
	}
}
?>

<?php $this->view($content); ?>