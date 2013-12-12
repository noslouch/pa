<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tabs for NSM Live Look
 *
 * @package			NsmLiveLook
 * @version			1.2.4
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @link			http://github.com/newism/nsm.live_look.ee-addon
 * @copyright 		Copyright (c) 2007-2013 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 */
class Nsm_live_look_tab
{
	/**
	 * This function creates the fields that will be displayed on the publish page. It must return $settings, a multidimensional associative array specifying the display settings and values associated with each of your custom fields.
	 *
	 * The settings array:
	 * field_id: The name of the field
	 * field_type: The field type
	 * field_label: The field label- typically a language variable is used here
	 * field_instructions: Instructions for the field
	 * field_required: Indicates whether to include the 'required' class next to the field label: y/n
	 * field_data: The current data, if applicable
	 * field_list_items: An array of options, otherwise an empty string.
	 * options: An array of options, otherwise an empty string.
	 * selected: The selected value if applicable to the field_type
	 * field_fmt: Allowed field format options, if applicable: an associative array or empty string.
	 * field_show_fmt: Determines whether the field format dropdown shows: y/n. Note- if 'y', you must specify the options available in field_fmt
	 * field_pre_populate: Allows you to pre-populate a field when it is a new entry.
	 * field_text_direction: The direction of the text: ltr/rtl
	 *
	 * @param int $channel_id The channel_id the entry is currently being created in
	 * @param mixed $entry_id The entry_id if an edit, false for new entries
	 * @return array The settings array
	 */
	public function publish_tabs($channel_id, $entry_id = FALSE)
	{
		$EE =& get_instance();
		$EE->lang->loadfile('nsm_live_look');

		if(!$channel_id) {
			$channel_id = $EE->input->get('channel_id');
		}

		$field_settings[] = array(
			'field_id' => 'preview',
			'field_type' => 'nsm_live_look',
			'field_label' => 'NSM Live Look',
			'field_instructions' => '',
			'field_required' => '',
			'field_data' => '',
			'field_list_items' => '',
			'options' => '',
			'selected' => '',
			'field_fmt' => '',
			'field_show_fmt' => 'n',
			'field_pre_populate' => '',
			'field_text_direction' => 'ltr',
			'channel_id' => $channel_id
		);

		return $field_settings;
	}

	/**
	 * Allows you to validate the data after the publish form has been submitted but before any additions to the database. Returns FALSE if there are no errors, an array of errors otherwise.
	 *
	 * @param $params  multidimensional associative array containing all of the data available on the current submission.
	 * @return mixed Returns FALSE if there are no errors, an array of errors otherwise
	 */
	public function validate_publish($params)
	{
		$errors = FALSE;
		return $errors;
	}

	/**
	 * Allows the insertion of data after the core insert/update has been done, thus making available the current $entry_id. Returns nothing.
	 *
	 * @param array $params an associative array, the top level arrays consisting of: 'meta', 'data', 'mod_data', and 'entry_id'.
	 * @return void
	 */
	public function publish_data_db($params)
	{
	}

	/**
	 * Called near the end of the entry delete function, this allows you to sync your records if any are tied to channel entry_ids. Returns nothing.
	 *
	 * @param array $entry_ids The deleted entrys
	 * @return void
	 */
	public function publish_data_delete_db($entry_ids)
	{
	}
}
