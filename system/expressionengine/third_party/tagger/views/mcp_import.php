<?php echo $this->view('mcp_header'); ?>

<div class="dbody">

	<div class="dtitle" id="actionbar">
        <h2><?=lang('tagger:import')?></h2>
    </div>

    <div style="padding:10px 10px">

    		<div class="ftable">
            	<?=form_open($base_url_short.AMP.'method=do_import_text')?>
                <h2><?=lang('tagger:import:text')?></h2>
                <table>
                    <tbody>
                    	<tr>
	                        <td><strong><?=lang('tagger:import:source')?></strong></td>
	                    	<td><?=form_dropdown('source', $fields_normal)?></td>
	                    </tr>
	                    <tr>
	                        <td><strong><?=lang('tagger:import:dest')?></strong></td>
	                    	<td><?=form_dropdown('dest', $fields_tagger)?></td>
	                    </tr>
	                    <tr>
	                        <td><strong><?=lang('tagger:import:separator')?></strong></td>
	                    	<td><?=form_input('sep', ',', 'style="width:50px"')?></td>
	                    </tr>
						<tr><td colspan="5"><input name="submit" class="submit" type="submit" value="<?=lang('tagger:import')?>"/></td></tr>
                    </tbody>
                </table>
                <?=form_close()?>
            </div>

            <div class="ftable">
            	<?=form_open($base_url_short.AMP.'method=do_import_solspace')?>
                <h2><?=lang('tagger:import:solspace')?></h2>
                <table>
                    <tbody>
                    	<?php if ($solspace_tags != FALSE):?>
                    	<tr>
	                        <td><strong><?=lang('tagger:import:channel')?></strong></td>
							<td>
								<ul class="ulcols">
									<?php foreach($channels AS $channel_id => $channel_title):?>
									<li><input name="channels[]" type="checkbox" value="<?=$channel_id?>" />&nbsp;&nbsp;<?=$channel_title?></li>
									<?php endforeach;?>
								</ul>
							</td>
						</tr>
						<tr><td colspan="5"><input name="submit" class="submit" type="submit" value="<?=lang('tagger:import')?>"/></td></tr>
						<?php else:?>
						<tr>
							<td colspan="5"><strong><?=lang('tagger:import:missing_solspace')?></strong></td>
						</tr>
						<?php endif;?>
                    </tbody>
                </table>
                <?=form_close()?>
            </div>

            <div class="ftable">
            	<?=form_open($base_url_short.AMP.'method=do_import_taggable')?>
                <h2><?=lang('tagger:import:taggable')?></h2>
                <table>
                    <tbody>
                    	<?php if ($taggable_tags != FALSE):?>
                    	<tr>
	                        <td><strong><?=lang('tagger:import:channel')?></strong></td>
							<td>
								<ul class="ulcols">
									<?php foreach($channels AS $channel_id => $channel_title):?>
									<li><input name="channels[]" type="checkbox" value="<?=$channel_id?>" />&nbsp;&nbsp;<?=$channel_title?></li>
									<?php endforeach;?>
								</ul>
							</td>
						</tr>
						<tr><td colspan="5"><input name="submit" class="submit" type="submit" value="<?=lang('tagger:import')?>"/></td></tr>
						<?php else:?>
						<tr>
							<td colspan="5"><strong><?=lang('tagger:import:missing_taggable')?></strong></td>
						</tr>
						<?php endif;?>
                    </tbody>
                </table>
                <?=form_close()?>
            </div>



        <br clear="all">
    </div>

</div> <!-- dbody -->
