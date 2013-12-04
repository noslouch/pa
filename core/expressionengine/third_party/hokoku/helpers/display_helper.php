<?php

/**
* function highlight
* Highlights string based on keyword
* @return	string	<span class="highlight"></span>-highlighted string 
*/
function highlight($text, $rules, $search_field)
{
	$highlight_conds = array("is", "contains", "beginswith", "endswith", "containsexactly");
	
	if( ! empty($rules))
	{
		foreach($rules as $rule)
		{
			if(isset($rule['field']) && $rule['field'] == $search_field && in_array($rule['cond'], $highlight_conds))
			{
				$keyword = $rule['val'];
				return highlight_phrase($text, $keyword, '<span class="highlight">', '</span>');
			}
		}
	} else {
		return $text;
	}
	return $text;
}
	
?>