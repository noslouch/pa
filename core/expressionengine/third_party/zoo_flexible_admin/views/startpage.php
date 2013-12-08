
<?php

	$this->table->set_template($cp_table_template);
	$this->table->set_heading(lang('Original menu (all)'), lang('Group menu')." &nbsp;&nbsp;&raquo; &nbsp;&nbsp;".form_dropdown('dropdown_group_id', $groups, null, $dd).'<div id="cpstatus"></div>');
	
	$this->table->add_row(					
		array('class' => 'even', 'style' => 'width:50%; ', 'data'=>''),
		array('class' => 'even', 'style' => 'width:50%;text-align:right;','data'=>$tree2actions)
	);
	$this->table->add_row(					
		array('class' => 'even', 'style' => 'width:50%', 'data'=>$tree1subactions),
		array('class' => 'even', 'style' => 'width:50%; text-align:right;vertical-align:top;', 'data'=>$tree2subactions)
	);
	$this->table->add_row(					
		array('class' => 'even', 'style' => 'width:50%'),
		array('class' => 'even', 'style' => 'width:50%; text-align:right;vertical-align:top;', 'data'=>'')
	);
	$this->table->add_row(					
		array('class' => 'odd', 'style' => 'width:50%', 'data'=>$tree1),
		array('class' => 'odd', 'style' => 'width:50%',  'valign' =>'top', 'id' => 'treetargetblock', 'data'=>$tree2addactions.$tree2)
	);
	
	$this->table->add_row(					
		array('class' => 'odd', 'style' => 'width:50%',),
		array('class' => 'odd', 'style' => 'width:50%', 'data'=>$explanation)
	);
	
	echo $this->table->generate()
?>

<div style="clear:left">&nbsp;</div>
</div>
