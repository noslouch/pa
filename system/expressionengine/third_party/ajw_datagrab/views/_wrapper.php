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
</style>

<?php if( isset( $errors ) && count( $errors ) ) {
	foreach( $errors as $error ) {
		echo '<p class="notice">Error: ' . $error . '</p>';
	}
}
?>

<?php $this->view($content); ?>