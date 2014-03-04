<div id="zoo-flexible-admin">
	<div id="loader">LOADING</div>
	<div id="cpnav" style="display:none;">
		<div id="origtree" style="display:none;"></div>
		<div id="cpnav_message">&nbsp;</div>

		<?php
		$tree1 = '
	<div id="left">
		<div id="dsource">
			<ul id="treesource">	
			</ul>
			<div style="clear:left">&nbsp;</div>
		</div>
	</div>';

		$tree1subactions = '<a title="Expand all" href="#" id="expandsourcetree" >[+] Expand all </a>&nbsp;&nbsp;&nbsp;<a title="Collapse all"  href="#" id="collapsesourcetree">[-] Collapse all</a>';

		$dd = 'id="dropdown_group_id"';
		$tree2actions = '<input type="button" id="submitbutton" value="Save" class="submit" />  <input type="button" id="previewbutton" value="Preview" class="submit" />  <input type="button" id="deletebutton" value="Reset to default" class="submit" />';

		$tree2 = '
	<div id="right">
	<div id="dtarget">
		<ul id="treetarget">
		</ul>
		<div style="clear:left">&nbsp;</div>
	</div>
	</div>';
		$tree2subactions = '<a title="Expand all" href="#" id="expandtargettree" class="subaction" >[+] Expand all </a>&nbsp;&nbsp;&nbsp;<a title="Collapse all"  href="#" id="collapsetargettree" class="subaction" >[-] Collapse all</a> <a title="add folder" class="subaction" id="addfolder" href="#">Add empty folder</a> <a title="add link" class="action-addlink" href="#">Add custom link</a>'; //' <a title="add divider" class="subaction" href="#">Add divider</a>';

		$tree2addactions = '
	<div id="addlinkform">
		<input type="text" id="linkname" class="text" value="link name"/><label>Link name:</label>
		<input type="text" id="linkurl" class="text" value="index.php"/><label>Link url:</label>
		<div style="clear:both;"></div>
		<a title="add link" class="subaction" id="closelinkform" href="#">Done</a>
		<input type="button" class="submit" id="addLink" value="Add menu item">
		<div id="newlinkstatus"></div>
	</div>';

		echo '<div style="text-align:right; padding-bottom:20px;">' . $tree2actions . "</div>";

		$this->table->set_template($cp_table_template);
		$this->table->set_heading(lang('Original menu (all)'), '<div style="text-align:right;">' . lang('Group menu') . " &nbsp;&nbsp;&raquo; &nbsp;&nbsp;" . form_dropdown('dropdown_group_id', $groups, null, $dd) . '&nbsp;&nbsp;&nbsp;<div id="cpstatus"></div></div>');

		/*
			$this->table->add_row(
				array('class' => 'even', 'style' => 'width:50%; ', 'data'=>''),
				array('class' => 'even', 'style' => 'width:50%;text-align:right;','data'=>'')
			);
		*/
		$this->table->add_row(
			array('class' => 'even', 'style' => 'width:50%', 'data' => $tree1subactions),
			array('class' => 'even', 'style' => 'width:50%; text-align:right;vertical-align:top;', 'data' => $tree2subactions)
		);
		/*
			$this->table->add_row(
				array('class' => 'even', 'style' => 'width:50%'),
				array('class' => 'even', 'style' => 'width:50%; text-align:right;vertical-align:top;', 'data'=>'')
			);
		*/
		$this->table->add_row(
			array('class' => 'odd', 'style' => 'width:50%', 'data' => $tree1),
			array('class' => 'odd', 'style' => 'width:50%', 'valign' => 'top', 'id' => 'treetargetblock', 'data' => $tree2addactions . $tree2)
		);

		echo $this->table->generate();

		$this->table->clear();

		$populate_text = ' <b>Auto-populate edit menu:</b><div class="subtext">' . '
	When selecting this option, the "Edit", "Publish" & "Modules" folder will be auto-populated with the available channels/modules. 
	The channels and modules who have been given a custom name will keep this name in the auto-populated list. 
	For this to work, these folders have to be present in the member group navigation.  The name of this folder can be changed freely and the folder can be positioned anywhere.
	</div>';

		$populate = form_checkbox('autopopulate', 'yes', FALSE);

		$startpage_text = '<b>Set control panel homepage for this membergroup</b>';
		$startpage = form_dropdown('dropdown_startpage', array(), null, 'id="dropdown_startpage"');

		$hide_sidebar_text = ' <b>Auto-collapse sidebar for this membergroup:</b><div class="subtext">' . '
	</div>';

		$hide_sidebar = form_checkbox('hide_sidebar', 'yes', FALSE);

		$copy_text = "<b>Copy navigation to: &nbsp;</b>";
		$copy = '<div id="copyholder">' . form_dropdown('dropdown_target_group_id', $groups, null, 'id="dropdown_target_group_id"') . '&nbsp;&nbsp;&nbsp;<input type="button" id="copybutton" value="Copy" class="submit" /></div><div id="cpCopystatus"></div>';


		$this->table->set_heading('Actions', '');

		$this->table->add_row(
			array('class' => 'odd', 'style' => 'width:50%', 'data' => $copy_text),
			array('class' => 'odd', 'style' => 'width:50%', 'data' => $copy)
		);

		$this->table->add_row(
			array('class' => 'odd', 'style' => 'width:50%', 'data' => $startpage_text),
			array('class' => 'odd', 'style' => 'width:50%', 'data' => $startpage)
		);

		$this->table->add_row(
			array('class' => 'odd', 'style' => 'width:50%', 'data' => $hide_sidebar_text),
			array('class' => 'odd', 'style' => 'width:50%', 'data' => $hide_sidebar)
		);

		$this->table->add_row(
			array('class' => 'odd', 'style' => 'width:50%', 'data' => $populate_text),
			array('class' => 'odd', 'style' => 'width:50%', 'data' => $populate)
		);

		echo $this->table->generate()
		?>

		<div style="clear:left">&nbsp;</div>
	</div>
</div>