/*jslint vars: true, undef: true, browser: true, plusplus: true, indent: 4 */
/*global jQuery, $, Modernizr, Placeholder, window, Lectric, helpers, methods */

$(document).ready(function () {
    function republic_structure_tweaks_toggle_fields(channel_id)
    {
        $('#sub_channel_'+channel_id).toggle();
        if ($('#sub_channel_'+channel_id).is(':hidden')) {
            $('.sub_channel_'+channel_id + ' input').each(function(){
                $(this).closest('span').hide();
                $(this).attr('checked', false);
            });
        } else {
            $('.sub_channel_'+channel_id + ' input').each(function(){
                $(this).closest('span').show();
                $(this).attr('checked', false);
            });
        }
    }

    $('form').submit(function () {
        $('tr.new-entry select').each(function () {
            $entry_id = $(this).val();
            $(this).closest('tr').find('input').each(function(){
                $(this).attr('name', $(this).attr('name').replace('xxx', $entry_id));
            });
        });
    });

    $("#add_new_row").click(function () {
        $cloned_entry_row = $("#new_entry_row").clone();
        $cloned_entry_row.removeAttr('id');

        var nrOfRows = $("#entries_table tbody tr").size();
        if (nrOfRows % 2 === 0) {
            $cloned_entry_row.addClass('even');
        } else {
            $cloned_entry_row.addClass('odd');
        }
        $("#entries_table tbody").append($cloned_entry_row);

        return false;
    });

    $(".delete_row").live('click', function () {
        $(this).closest('tr').remove();
        var nrOfRows = 0;
        $("#entries_table tbody tr").each(function(){
            $(this).removeClass('odd');
            $(this).removeClass('even');

            if (nrOfRows++ % 2 === 0) {
                $(this).addClass('even');
            } else {
                $(this).addClass('odd');
            }
        });

        return false;
    });

    $('.toggle-select').live('change', function (e) {
        e.preventDefault();
        var isChecked = false;
        if ($(this).is(':checked')) {
            isChecked = true;
        }

        $(this).closest('td').find('input[type=checkbox]').prop('checked', isChecked);
    });
});
