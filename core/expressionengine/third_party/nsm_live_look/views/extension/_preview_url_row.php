<?php
/**
 * Preview URL partial - Loaded in the extension settings table
 *
 * @package			NsmLiveLook
 * @version			1.1.0
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @link			http://github.com/newism/nsm.live_look.ee-addon
 * @copyright 		Copyright (c) 2007-2011 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 */
?>
<tr id="nsm_live_look_config_<?= $row["channel_id"].'_'.$count ?>" class="<?= $row_class ?>">
	<td>
		<select name="<?= $input_prefix ?>[urls][][channel_id]">
			<?php 
				foreach ($channels as $channel) :
				$selected = ($row["channel_id"] == $channel->channel_id) ? " selected='selected'" : "";
			?>
				<option value="<?= $channel->channel_id ?>"<?= $selected ?>>
					<?= $channel->channel_title ?>
				</option>
			<?php endforeach; ?>
		</select>
	</td>
	<th scope="row">
		<input 
			type="text"
			name="<?= $input_prefix ?>[urls][][title]"
			value="<?= $row["title"] ?>"
		 />
	</th>
	<td>
		<input 
			type="text"
			name="<?= $input_prefix ?>[urls][][url]"
			value="<?= $row["url"] ?>"
		 />
	</td>
	<td>
		<input 
			type="text"
			name="<?= $input_prefix ?>[urls][][height]"
			value="<?= $row["height"] ?>"
		 />
	</td>
	<td>
		<input 
			type="hidden"
			name="<?= $input_prefix ?>[urls][][page_url]"
			value=""
		/>
		<input 
			type="checkbox"
			name="<?= $input_prefix ?>[urls][][page_url]"
			<?php if($row["page_url"]) print("checked='checked'"); ?>
			value="1"
		 />
	</td>
	<td>
		<span class="icon delete">Delete</span>
	</td>
</tr>