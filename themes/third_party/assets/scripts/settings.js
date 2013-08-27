(function($){


// define the Assets global
if (typeof window.Assets == 'undefined') window.Assets = {};


Assets.onAllFiledirsChange = function(all)
{
	var $all = $(all),
		allChecked = !!$all.attr('checked'),
		$others = $('input', $all.parent().parent().next());

	$others.attr({
		checked:  allChecked,
		disabled: allChecked
	});
};


$('.assets-view').each(function()
{
	var $thumbs = $('.assets-view-thumbs', this),
		$list = $('.assets-view-list', this),
		$showColsTr = $(this).parent().parent().next().hide();

	$thumbs.change(function()
    {
		$showColsTr.hide();
	});

	$list.change(function()
    {
		$showColsTr.show();
	});
});

$('form#source_form').keydown(function (e){
    var key = (e.which) ? e.which : event.keyCode;
    if (key == 13)
    {
        e.stopPropagation();
        $(this).find('input:last').click();
    }
}).submit(function (e)
{
    if ($('input#source_name').val().length < 1)
    {
        e.preventDefault();
    }
});

var $bucketSelect = $();

function onBucketSelectChange()
{
    var _sel = $bucketSelect.find(':selected');
    $('#s3_bucket_location').val(_sel.attr('data-location'));
    $('#s3_bucket_url_prefix').val(_sel.attr('data-url-prefix'));
}

$('.refresh_buckets').live('click', function (e)
{
    e.preventDefault();

    var params = {
        ACT: Assets.actions.get_s3_buckets
    };

    $('input.setting_field[data-type=s3]').each(function ()
    {
        params[$(this).prop('name')] = $(this).val();
    });
    $('#s3_buckets').html('<div class="assets-loading"></div>');
    $.post(Assets.siteUrl, params, function (data)
    {
        $('#s3_buckets').html(data);
        $bucketSelect = $('#s3_buckets > select');
        $bucketSelect.change(onBucketSelectChange);
        onBucketSelectChange();
    });
});

var $googleBucketSelect = $();

function onGoogleBucketSelectChange()
{
    var _sel = $googleBucketSelect.find(':selected');
    $('#gc_bucket_url_prefix').val(_sel.attr('data-url-prefix'));
}

$('.refresh_gc_buckets').live('click', function (e)
{
    e.preventDefault();

    var params = {
        ACT: Assets.actions.get_gc_buckets
    };

    $('input.setting_field[data-type=gc]').each(function ()
    {
        params[$(this).prop('name')] = $(this).val();
    });
    $('#gc_buckets').html('<div class="assets-loading"></div>');
    $.post(Assets.siteUrl, params, function (data)
    {
        $('#gc_buckets').html(data);
        $googleBucketSelect = $('#gc_buckets > select');
        $googleBucketSelect.change(onGoogleBucketSelectChange);
        onGoogleBucketSelectChange();
    });
});

$containerSelect = $();
function onContainerSelectChange()
{
    var _sel = $containerSelect.find(':selected');
    $('#rs_container_url_prefix').val(_sel.attr('data-url-prefix'));
}


$('.refresh_containers').live('click', function (e)
{
    e.preventDefault();

    var params = {
        ACT: Assets.actions.get_rs_containers
    };

    $('input.setting_field[data-type=rs], select.setting_field[data-type=rs]').each(function ()
    {
        params[$(this).prop('name')] = $(this).val();
    });
    $('#rs_containers').html('<div class="assets-loading"></div>');
    $.post(Assets.siteUrl, params, function (data)
    {
        $('#rs_containers').html(data);
        $containerSelect = $('#rs_containers > select');
        $containerSelect.change(onContainerSelectChange);
        onContainerSelectChange();

    });
});



    $('.delete_source').click(function ()
{
    var source_name = $(this).attr('data-source-name');
    if (confirm(Assets.lang.confirm_delete_source.replace('{source}', source_name)))
    {
        var sourceId = $(this).attr('data-source-id');
        $('#source_id').val(sourceId);
        $('form#delete_source').submit();
    }
});

var typeSelect = $('select#source_type');
var selectedType = typeSelect.val();
$('.asset-source-settings[data-type=' + selectedType + ']').show();

typeSelect.change(function () {
    $('.asset-source-settings').hide().filter('[data-type=' + $(this).val() + ']').show();
});

// Indexing scripts
$('input.assets-index').click(function ()
{
    var _button = $(this);
    if (_button.hasClass('disabled'))
    {
        return;
    }
    _button.addClass('disabled');
    var sources_to_index = $('input.indexing:checked');

    $('div#assets-dialog div#index-status-report').empty();
    $('div#assets-dialog div#index-message').empty();

    if (sources_to_index.length > 0)
    {
        $.post(Assets.siteUrl, {ACT: Assets.actions.get_session_id}, function (data)
        {
            data = $.evalJSON(data);

            var session = data.session;

            var missing_folders = [];

            // got session - create the Queue Manager
            var queue = new Assets.AjaxQueueManager(10, function ()
            {
                $('input.assets-index.disabled').removeClass('disabled');
                $('div.progress-bar').remove();
                var sources = [];
                sources_to_index.each(function ()
                {
                    sources.push($(this).attr('id'));
                });

                var params = {
                    ACT: Assets.actions.finish_index,
                    session: session,
                    command: $.toJSON({command: 'statistics'}),
                    sources: sources.join(",")
                };

                $.post(Assets.siteUrl, params, function (data)
                {
                    data = $.evalJSON(data);
                    if (missing_folders.length > 0 || typeof data.files != "undefined")
                    {
                        $('div#assets-dialog div#index-message').html(Assets.lang.index_stale_entries_message);

                        var html = '';

                        if (missing_folders.length > 0)
                        {
                            html += '<div class="index-data-container"><strong>' + Assets.lang.index_folders + '</strong>';
                            for (var i = 0; i < missing_folders.length; i++)
                            {
                                html += '<div><label><input type="checkbox" checked="checked" class="delete_folder" value="' + missing_folders[i].folder_id + '" /> ' + missing_folders[i].folder_name + '</label></div>';
                            }
                            html += '</div>'
                        }

                        if (typeof data.files != "undefined")
                        {
                            html += '<div class="index-data-container"><strong>' + Assets.lang.index_files + '</strong>';
                            for (var file_id in data.files)
                            {
                                html += '<div><label><input type="checkbox" checked="checked" class="delete_file" value="' + file_id + '" /> ' + data.files[file_id] + '</label></div>';
                            }
                            html += '</div>'
                        }

                        html += '<br /><input type="button" class="submit" value="' + Assets.lang._delete + '" onclick="deleteSelectedFiles();"/>';
                        $('#index-status-report').empty().append(html);

                    }
                    else
                    {
                        $('div#assets-dialog div#index-status-report').empty();
                        $('div#index-message').html(Assets.lang.index_complete);
                    }
                    $('div#assets-dialog').show();
                });
            });


            sources_to_index.each(function ()
            {
                $(this).parent().find('.progress-bar').remove().end().append('<div class="progress-bar"><div class="progress-filler"></div></div>');

                var progress_bar = $(this).siblings('.progress-bar');

                var params = {
                    ACT: Assets.actions.start_index,
                    source: $(this).attr('id'),
                    session: session
                };

                // add the initial requests for each source
                queue.addItem(Assets.siteUrl, params, function (data)
                {
                    data = $.evalJSON(data);
                    progress_bar.attr('total', data.total).attr('current', 0);
                    for (var i = 0; i < data.total; i++)
                    {
                        params = {
                            ACT: Assets.actions.perform_index,
                            session: session,
                            source_type: data.source_type,
                            source_id: data.source_id,
                            offset: i
                        };

                        // add the received items to the queue
                        queue.addItem(Assets.siteUrl, params, function ()
                        {
                            var current = parseInt(progress_bar.attr('current'), 10);
                            progress_bar.attr('current', ++current);

                            var fillerWidth = Math.min(Math.ceil(100 / progress_bar.attr('total') * current), 100) + '%';
                            progress_bar.find('.progress-filler').stop().animate({width: fillerWidth}, 70, 'linear');
                        });
                    }

                    // add the missing folder info
                    for (var folder_id in data.missing_folders)
                    {
                        missing_folders.push({folder_id: folder_id, folder_name: data.missing_folders[folder_id]});
                    }
                });
            });
            queue.startQueue();
        });
    }
    else
    {
        _button.removeClass('disabled');
    }
});

})(jQuery);

function deleteSelectedFiles()
{
    var command = {};
    command.command = 'delete';
    command.folder_ids = [];
    command.file_ids = [];

    $('div#assets-dialog input.delete_folder:checked').each(function (){
        command.folder_ids.push($(this).val());
    });

    $('div#assets-dialog input.delete_file:checked').each(function (){
        command.file_ids.push($(this).val());
    });

    var post_data = {
        ACT: Assets.actions.finish_index,
        command: $.toJSON(command)
    };

    $.post(Assets.siteUrl, post_data, function ()
    {
        $('div#assets-dialog div#index-status-report').empty();
        $('#index-message').html(Assets.lang.index_complete);
    });
}
