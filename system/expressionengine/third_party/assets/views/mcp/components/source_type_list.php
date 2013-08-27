<?php

if (!empty($sources))
{

	$this->table->set_heading(
		array('data' => lang('id'), 'style' => 'width: 5%;'),
		array('data' => lang('source_name')),
		array('data' => lang('source_type')),
		array('data' => lang('edit'), 'style' => 'width: 5%; text-align: center;'),
		array('data' => lang('delete'), 'style' => 'width: 5%; text-align: center;')
	);

	foreach ($sources as $source)
	{
		$this->table->add_row(
		array('data' => $source->source_id),
		array('data' => '<strong>'.$source->name.'</strong>'),
		array('data' => '<strong>'.lang('source_type_' . $source->source_type).'</strong>'),
		array('data' => '<a href="'.BASE.AMP.$base.AMP.'method=edit_source'.AMP.'source_id='.$source->source_id.'"><img src="'.$cp_theme_url.'images/icon-edit.png" alt="'.lang('edit').'" /></a>', 'style' => 'text-align: center'),
		array('data' => '<a class="delete_source" href="javascript:;" data-source-id="'.$source->source_id.'" data-source-name="'.$source->name.'"><img src="'.$cp_theme_url.'images/icon-delete.png" alt="'.lang('delete').'" /></a>', 'style' => 'text-align: center')
		);
	}

	echo $this->table->generate();
}
else
{
	echo '<p>'.lang('no_sources_exist').'</p>';
}