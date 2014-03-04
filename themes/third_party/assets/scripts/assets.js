/*!
 * Assets
 *
 * @copyright 2013 Pixel & Tonic, Inc.. All rights reserved.
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @version   2.2.4
 */
(function($){

// define the Assets global
if (typeof window.Assets == 'undefined')
{
	window.Assets = {};
}


// -------------------------------------------
//  Utility functions
// -------------------------------------------

/**
 * Case Insensative Sort
 */
Assets.caseInsensativeSort = function(a, b)
{
	a = a.toLowerCase();
	b = b.toLowerCase();
	return a < b ? -1 : (a > b ? 1 : 0);
}

/**
 * Parse Tag
 */
Assets.parseTag = function(str, tag, val)
{
	return str.replace('{'+tag+'}', val);
};


/**
 * Ajax Queue Manager
 * @param workers amount of simultaneous workers
 * @param callback callback to perform when a queue is finished
 * @constructor
 * @package Assets
 *
 * @author Andris Sevcenko <andris@pixelandtonic.com>
 * @copyright Copyright (c) 2012 Pixel & Tonic, Inc
 */
Assets.AjaxQueueManager = Garnish.Base.extend({

    /**
     * Constructor
     */
    init: function(workers, callback)
    {
        this._workers = workers;
        this._queue = [];
        this._callback = callback;
        this._busyWorkers = 0;
    },

    /**
     * Add item to the queue
     * @param target Ajax POST target
     * @param parameters POST parameters
     * @param callback callback to perform on data
     */
    addItem: function(target, parameters, callback)
    {
        this._queue.push({
            target: target,
            parameters: parameters,
            callback: callback
        });
    },

    /**
     * Process an item from the queue
     */
    processItem: function()
    {
        if (this._queue.length == 0)
        {
            if (this._busyWorkers == 0)
            {
                this._callback();
            }
            return;
        }

        this._busyWorkers++;
        var item = this._queue.shift();
        var _t = this;
        $.post(item.target, item.parameters, function(data)
        {
            if (typeof item.callback == "function")
            {
                item.callback(data);
            }

            _t._busyWorkers--;

            // call final callback, if all done or queue another job if we can
            if (_t._busyWorkers == 0 && _t._queue.length == 0 && typeof _t._callback == "function")
            {
                _t._callback();
            }
            else if (_t._queue.length > 0 && _t._busyWorkers < _t._workers)
            {
                // always keep the workers busy, even if we started  slow
                while (_t._busyWorkers < _t._workers && _t._queue.length > 0)
                {
                    _t.processItem();
                }
            }

        });
    },

    /**
     * Start the queue
     */
    startQueue: function()
    {
        while (this._busyWorkers < this._workers && this._queue.length > 0)
        {
            this.processItem();
        }
    }
});


/**
 * Field
 */
Assets.Field = Garnish.Base.extend({

	/**
	 * Constructor
	 */
	init: function(fieldId, fieldName, settings)
	{
		this.fieldId = fieldId;
		this.fieldName = fieldName;

		this.setSettings(settings);

		this.$field = $('#'+this.fieldId);

		var $btns = this.$field.next();
		this.$addBtn = $('.assets-add', $btns);
		this.$removeBtn = $('.assets-remove', $btns);

		this.$css = $('<style type="text/css" />').appendTo(document.body);

		this.filesView;
		this.fileSelect;
		this.filesSort;
		this.sheet;

		this.orderby;
		this.sort;

		this.orderFilesRequestId = 0;
		this.selectFilesRequestId = 0;

		this.$addBtn.click($.proxy(this, '_showSheet'));

		this.$removeBtn.click($.proxy(function()
		{
			// ignore if disabled
			if (this.$removeBtn.hasClass('assets-disabled')) return;

			var $files = this.settings.multi ? this.fileSelect.getSelectedItems() : this.filesView.getItems();
			this._removeFiles($files);
		}, this));

		this._initFilesView();
	},

	/**
	 * Initialize Files View
	 */
	_initFilesView: function()
	{
		// Initialize the Files View
		if (this.settings.view == 'thumbs')
		{
			this.filesView = new Assets.ThumbView($('> .assets-thumbview', this.$field));
		}
		else
		{
			this.filesView = new Assets.ListView($('> .assets-listview', this.$field), {
				orderby: this.orderby,
				sort: this.sort,
				onSortChange: $.proxy(this, '_onListViewSortChange')
			});
		}

		// initialize the multiselect
		this.fileSelect = new Garnish.Select(this.$field, {
			selectedClass:     'assets-selected',
			multi:             true,
			vertical:          (this.settings.view == 'list'),
			onSelectionChange: $.proxy(this, '_onSelectionChange')
		});

		// initialize the dragger
		this.filesSort = new Garnish.DragSort({
			container:    this.filesView.getContainer(),
			axis:         (this.settings.view == 'list' ? Garnish.Y_AXIS : null),
			removeDraggee: true,
			filter:       '.assets-selected',
			helper:       $.proxy(this.filesView, 'getDragHelper'),
			caboose:      $.proxy(this.filesView, 'getDragCaboose'),
			insertion:    $.proxy(this.filesView, 'getDragInsertion'),
			onSortChange: $.proxy(function()
			{
				this.fileSelect.resetItemOrder();
			}, this)
		});

		// initialize the files
		var $files = this.filesView.getItems();
		this._initFiles($files);
	},

	/**
	 * Initialize Files
	 */
	_initFiles: function($files)
	{
		// ignore if no files
		if (! $files.length) return;

		// add them to the multi-select
		this.fileSelect.addItems($files);

		if (this.settings.multi)
		{
			// make them draggable
			this.filesSort.addItems($files);
		}
		else
		{
			this.$addBtn.addClass('assets-disabled');
			this.$removeBtn.removeClass('assets-disabled');
		}

		// show properties on double-click
		$files.dblclick($.proxy(this, '_showProperties'));

		// add the context menus
		this._singleFileMenu = new Garnish.ContextMenu($files, [
			{ label: Assets.lang.view_file, onClick: $.proxy(this, '_viewFile') },
			{ label: Assets.lang.edit_file, onClick: $.proxy(this, '_showProperties') },
			'-',
			{ label: Assets.lang.remove_file, onClick: $.proxy(this, '_removeFiles') }
		], {
			menuClass: 'assets-contextmenu'
		});

		if (this.settings.multi)
		{
			this._multiFileMenu = new Garnish.ContextMenu($files, [
				{ label: Assets.lang.remove_files, onClick: $.proxy(this, '_removeFiles') }
			], {
				menuClass: 'assets-contextmenu'
			});

			this._multiFileMenu.disable();
		}
	},

	/**
	 * On Selection Change
	 */
	_onSelectionChange: function()
	{
		if (this.settings.multi)
		{
			var totalSelected = this.fileSelect.getTotalSelected();

			// enable/disable buttons based on selection
			if (totalSelected)
			{
				this.$removeBtn.removeClass('assets-disabled');
			}
			else
			{
				this.$removeBtn.addClass('assets-disabled');
			}

			if (totalSelected == 1)
			{
				this._singleFileMenu.enable();

				if (this.settings.multi)
				{
					this._multiFileMenu.disable();
				}
			}
			else
			{
                if (typeof(this._singleFileMenu) != "undefined")
                {
				    this._singleFileMenu.disable();
                }

				if (this.settings.multi && typeof(this._multiFileMenu) != "undefined")
				{
					this._multiFileMenu.enable();
				}
			}
		}
	},

	/**
	 * On List View Sort Change
	 */
	_onListViewSortChange: function(orderby, sort)
	{
		this.orderby = orderby;
		this.sort = sort;

		this.orderFilesRequestId++;

		data = {
			ACT:        Assets.actions.get_ordered_files_view,
			requestId:  this.orderFilesRequestId,
			view:       this.settings.view,
			field_id:   this.fieldId,
			field_name: this.fieldName,
			orderby:    this.orderby,
			sort:       this.sort
		};

		for (var i = 0; i < this.settings.show_cols.length; i++)
		{
			data['show_cols['+i+']'] = this.settings.show_cols[i];
		}

		this.filesView.getItems().each(function(i)
		{
			data['files['+i+']'] = $(this).attr('data-id');
		});

		this.fileSelect.destroy();
		this.filesView.destroy();

		$.post(Assets.siteUrl, data, $.proxy(function(data, textStatus)
		{
			if (textStatus == 'success')
			{
				// ignore if this isn't the current request
				if (data.requestId != this.orderFilesRequestId) return;

				// update the HTML
				this.$field.html(data.html);

				this._initFilesView();
			}
		}, this), 'json');
	},

	/**
	 * Show Sheet
	 */
	_showSheet: function()
	{
		if (! this.sheet)
		{
			this.sheet = new Assets.Sheet({
				multiSelect: this.settings.multi,
				filedirs:    this.settings.filedirs,
				onSelect:    $.proxy(this, '_selectFiles'),
                namespace:   this.settings.namespace
			});
		}

		// get currently selected files
		var selectedFiles = [],
			$selectedFiles = this.filesView.getItems();

		for (var i = 0; i < $selectedFiles.length; i++)
		{
			var fileId = $selectedFiles[i].getAttribute('data-id');
			selectedFiles.push(fileId);
		};

		this.sheet.show({
			disabledFiles: selectedFiles
		});
	},

	/**
	 * View File
	 */
	_viewFile: function(event)
	{
		var fileId = event.currentTarget.getAttribute('data-id'),
			url = Assets.siteUrl + '?ACT=' + Assets.actions.view_file+'&file_id='+fileId;

		window.open(url);
	},

	/**
	 * Show Properties
	 */
	_showProperties: function(event)
	{
		this.propertiesHud = new Assets.Properties($(event.currentTarget));
	},

	/**
	 * Remove Files
	 */
	_removeFiles: function($files)
	{
		if ($files.currentTarget) $files = this.fileSelect.getSelectedItems();

		this.fileSelect.removeItems($files);
		this.filesSort.removeItems($files);
		this.filesView.removeItems($files);

		if (! this.settings.multi)
		{
			this.$addBtn.removeClass('assets-disabled');
			this.$removeBtn.addClass('assets-disabled');
		}
	},

	/**
	 * Select Files
	 */
	_selectFiles: function(files)
	{
		this.selectFilesRequestId++;

		var data = {
			ACT:            Assets.actions.get_selected_files,
			requestId:      this.selectFilesRequestId,
			view:           this.settings.view,
			thumb_size:     this.settings.thumb_size,
			show_filenames: this.settings.show_filenames,
			prev_total:     this.filesView.totalFiles,
			field_id:       this.fieldId,
			field_name:     this.fieldName
		};

		if (this.settings.view == 'list')
		{
			// pass the show_cols setting
			for (var i = 0; i < this.settings.show_cols.length; i++)
			{
				data['show_cols['+i+']'] = this.settings.show_cols[i];
			}
		}

		for (var i = 0; i < files.length; i++)
		{
			data['file_id['+i+']'] = files[i].id;
		}

		$.post(Assets.siteUrl, data, $.proxy(function(data, textStatus)
		{
			if (textStatus == 'success')
			{
				// ignore if this isn't the current request
				if (data.requestId != this.selectFilesRequestId) return;

				// initialize the files
				var $files = $(data.html);

				if (this.settings.view == 'list')
				{
					$files = $files.filter('tr');
				}
				else
				{
					$files = $files.filter('li');
				}

				this.filesView.addItems($files);
				this._initFiles($files);


                $('<style>' + data.css + '</style>').appendTo('head');

			}
		}, this), 'json');
	}
});


/**
 * File Manager
 */
Assets.FileManager = Garnish.Base.extend({

	/**
	 * Constructor
	 */
	init: function($fm, settings)
	{
		this.$fm = $fm;

		this.setSettings(settings, Assets.FileManager.defaults);

		if (this.settings.mode == 'full')
		{
			this.$pageContents = $('.pageContents');
		}

		this.$main = $('.assets-fm-main', this.$fm);
		this.$toolbar = $('> .assets-fm-toolbar', this.$main);

		if (this.settings.mode == 'full')
		{
			this.$scrollpane = Garnish.$win;
		}
		else
		{
			this.$scrollpane = this.$main;
		}

		// view buttons
		this.viewBtns = [];
		var $viewBtnContainer = $('.assets-fm-view:first', this.$toolbar),
			$viewBtns = $('li', $viewBtnContainer);

		for (var i = 0; i < $viewBtns.length; i++)
		{
			var $btn = $($viewBtns[i]),
				view = $btn.attr('data-view');
			this.viewBtns[view] = $btn;
		}

		this.viewSelect = new Garnish.Select($viewBtnContainer, {
			selectedClass:         'assets-active',
			multi:                 false,
			waitForDblClick:       false,
			horizontal:            true,
			arrowsChangeSelection: false,
			onSelectionChange:     $.proxy(this, 'switchView')
		});

		this.viewSelect.addItems($viewBtns);

        // Refresh button
        this.$refresh = $('.assets-fm-refresh', this.$toolbar);

		this.$sidebar = $('> .assets-fm-sidebar', this.$fm);
		this.$upload = $('> .assets-fm-upload', this.$sidebar);
		this.$folders = $('> .assets-fm-folders', this.$sidebar);

		this.$files = $('> .assets-fm-files', this.$main);

		this.$uploadProgress = $('> .assets-fm-uploadprogress', this.$fm);
		this.$uploadProgressBar = $('.assets-fm-pb-bar', this.$uploadProgress);

		this.css = [];

		this.folders = {};

		this.selectedFolderIds = [];
		this.selectedFileIds = [];
		this.searchTimeout = null;
		this.searchVal = '';
        this.showingSearchOptions = false;

		this.filesRequestId = 0;
		this.propsRequestId = 0;

		this.fileSelect = null;
        this.filesView = null;

        this.responseArray = [];
        this.promptArray = [];

        this.currentIndexItem = 0;

        this.lastPageReached = true;
        this.nextOffset = 0;

        this._singleFileMenu = [];
        this._multiFileMenu = [];

		this.currentSource = 0;

		this.defaultSourceState = {
			view: 'thumbs',
			searchMode: 'shallow',
			orderby: 'name',
			sort: 'asc'
		};

        // -------------------------------------------
        // Application State
        // -------------------------------------------

        // set the default state
        this.instanceState = {
            folders: {},
            selectedFolders: []
        };

		this.sourceState = {

		};

        this.instanceStateStorageKey = 'PT_Assets_' + this.settings.namespace;

        // Merge in the previous instance state if available
        if (typeof Storage !== 'undefined' && typeof localStorage[this.instanceStateStorageKey] != 'undefined')
        {
	        var storedState = $.evalJSON(localStorage[this.instanceStateStorageKey]);
	        $.extend(this.instanceState, storedState);
        }

		this.sourceStateStorageKey = 'PT_Assets_Source_State_' + this.settings.context;

		// Merge in the previous source state if available
		if (typeof Storage !== 'undefined' && typeof localStorage[this.sourceStateStorageKey] != 'undefined')
		{
			var storedSoureState = $.evalJSON(localStorage[this.sourceStateStorageKey]);
			$.extend(this.sourceState, storedSoureState);
		}


		// -------------------------------------------
		//  File Uploads
		// -------------------------------------------

		this.uploader = new Assets.qq.FileUploader({
			element:      this.$upload[0],
			action:       Assets.siteUrl,
			template:     '<div class="assets-qq-uploader">'
			              +   '<div class="assets-qq-upload-drop-area"></div>'
			              +   '<div class="assets-qq-upload-button assets-btn assets-submit assets-disabled">'+Assets.lang.upload_files+'</div>'
			              +   '<ul class="assets-qq-upload-list"></ul>'
			              + '</div>',

			fileTemplate: '<li>'
			              +   '<span class="assets-qq-upload-file"></span>'
			              +   '<span class="assets-qq-upload-spinner"></span>'
			              +   '<span class="assets-qq-upload-size"></span>'
			              +   '<a class="assets-qq-upload-cancel" href="#">Cancel</a>'
			              +   '<span class="assets-qq-upload-failed-text">Failed</span>'
			              + '</li>',

			classes:      {
			                  button:     'assets-qq-upload-button',
			                  drop:       'assets-qq-upload-drop-area',
			                  dropActive: 'assets-qq-upload-drop-area-active',
			                  list:       'assets-qq-upload-list',

			                  file:       'assets-qq-upload-file',
			                  spinner:    'assets-qq-upload-spinner',
			                  size:       'assets-qq-upload-size',
			                  cancel:     'assets-qq-upload-cancel',

			                  success:    'assets-qq-upload-success',
			                  fail:       'assets-qq-upload-fail'
			              },

			onSubmit:     $.proxy(this, '_onUploadSubmit'),
			onProgress:   $.proxy(this, '_onUploadProgress'),
			onComplete:   $.proxy(this, '_onUploadComplete')
		});

		// -------------------------------------------
		//  Folders
		// -------------------------------------------

		// initialize the folder select
		this.folderSelect = new Garnish.Select(this.$folders, {
			selectedClass:     'assets-selected',
			multi:             false,
			waitForDblClick:   false,
			vertical:          true,
			onSelectionChange: $.proxy(this, '_updateSelectedFolders')
		});

		// initialize top-level folders
		this.$topFolderUl = this.$folders.children().filter('ul');
		this.$topFolderLis = this.$topFolderUl.children().filter('li');

		// stop initializing everything if there are no folders
		if (! this.$topFolderLis.length)
        {
            this.disableUploadBtn();
            return;
        }

		for (var i = 0; i < this.$topFolderLis.length; i++)
		{

			var folder = new Assets.FileManagerFolder(this, this.$topFolderLis[i], 1);
		}

		// set it right off the bat in case there are any really long upload directory names
		this.setFoldersWidth();

        // refresh the current folder
        this.addListener(this.$refresh, 'activate', 'refreshFiles');

		if (this.settings.mode == 'full')
		{
			this.expandDropTargetFolderTimeout = null;

			// -------------------------------------------
			//  Folder dragging
			// -------------------------------------------

			this.folderDrag = new Garnish.DragDrop({
				activeDropTargetClass: 'assets-selected assets-fm-dragtarget',
                helperOpacity: 0.5,

				filter: $.proxy(function()
				{
					// return each of the selected <a>'s parent <li>s
					var $selected = this.folderSelect.getSelectedItems(),
						draggees = [];

					for (var i = 0; i < $selected.length; i++)
					{
						var li = $($selected[i]).parent()[0];

						// ignore top-level folders
						if ($.inArray(li, this.$topFolderLis) != -1)
						{
							this.folderSelect.deselectItem($($selected[i]));
							continue;
						}

						draggees.push(li);
					};

					return $(draggees);
				}, this),

				helper: $.proxy(function($folder)
				{
					var $helper = $('<ul class="assets-fm-folderdrag" />').append($folder);

					// collapse this folder
					$('> a', $folder).removeClass('assets-fm-expanded');
					$('> ul', $folder).hide();

					// set the helper width to the folders container width
					$helper.width(this.$folders[0].scrollWidth);

					return $helper;
				}, this),

				dropTargets: $.proxy(function()
				{
					var targets = [];

					for (var folderId in this.folders)
					{
						var folder = this.folders[folderId];

						if (folder.visible && $.inArray(folder.$li[0], this.folderDrag.$draggee) == -1)
						{
							targets.push(folder.$a);
						}
					}

					return targets;
				}, this),

				onDragStart: $.proxy(function()
				{
					this.tempExpandedFolders = [];

					// hide the expanded draggees' subfolders
					$('> a.assets-fm-expanded + ul', this.folderDrag.$draggee).hide();
				}, this),

				onDropTargetChange: $.proxy(this, '_onDropTargetChange'),

				onDragStop: $.proxy(function()
				{
					// show the expanded draggees' subfolders
					$('> a.assets-fm-expanded + ul', this.folderDrag.$draggee).show();

                    // Only move if we have a valid target and we're not trying to move into our direct parent
					if (
                        this.folderDrag.$activeDropTarget
                        && this.folderDrag.$activeDropTarget.siblings('ul').find('>li').filter(this.folderDrag.$draggee).length == 0)
					{

						var targetFolderId = this.folderDrag.$activeDropTarget.attr('data-id');

						this._collapseExtraExpandedFolders(targetFolderId);

						// get the old folder IDs, and sort them so that we're moving the most-nested folders first
						var folderIds = [], folderNames = [];

						for (var i = 0; i < this.folderDrag.$draggee.length; i++)
						{
							var $a = $('> a', this.folderDrag.$draggee[i]),
								folderId = $a.attr('data-id'),
								folder = this.folders[folderId];
                                folderNames[folderId] = $a.text();

							// make sure it's not already in the target folder
							if (folder.parent.id != targetFolderId)
							{
                                folderIds.push(folderId);
							}
						}

						if (folderIds.length)
						{
                            folderIds.sort();
                            folderIds.reverse();

                            this.$fm.addClass('assets-loading');
                            this.$uploadProgress.show();
                            this.$uploadProgressBar.width('0%');

                            var parameterArray = [];

                            for (var i = 0; i < folderIds.length; i++)
                            {
                                parameterArray.push({
                                    ACT: Assets.actions.move_folder,
                                    old_id: folderIds[i],
                                    parent_id: targetFolderId,
                                    folder_name: folderNames[i]
                                });
                            }

                            this.responseArray = [];

                            // increment, so to avoid displaying folder files that are being moved
                            this.filesRequestId++;

                            /*
                                Here's the rundown:
                                1) Send all the folders being moved
                                2) Get results:
                                    a) For all conflicting, receive prompts and resolve them to get:
                                    b) For all valid move operations: by now server has created the needed folders
                                        in target destination. Server returns an array of file move operations
                                    c) server also returns a list of all the folder id changes
                                    d) and the data-id of node to be removed, in case of conflict
                                    e) and a list of folders to delete after the move
                                3) From data in 2) build a large file move operation array
                                4) Create a request loop based on this, so we can display progress bar
                                5) when done, delete all the folders and perform other maintenance
                                6) Champagne
                             */

                            // this will hold the final list of files to move
                            var fileMoveList = [];

                            // these folders have to be deleted at the end
                            var folderDeleteList = [];

                            // this one tracks the changed folder ids
                            var changedFolderIds = {};

                            var removeFromTree = [];

                            var onMoveFinish = $.proxy(function(responseArray)
                            {
                                this.promptArray = [];

                                // loop trough all the responses
                                for (var i = 0; i < responseArray.length; i++)
                                {
                                    var data = $.evalJSON(responseArray[i]);

                                    // if succesful and have data, then update
                                    if (data.success)
                                    {
                                        if (data.transfer_list && data.delete_list && data.changed_folder_ids)
                                        {
                                            for (var ii = 0; ii < data.transfer_list.length; ii++)
                                            {
                                                fileMoveList.push(data.transfer_list[ii]);
                                            }
                                            for (var ii = 0; ii < data.delete_list.length; ii++)
                                            {
                                                folderDeleteList.push(data.delete_list[ii]);
                                            }
                                            for (var old_folder_id in data.changed_folder_ids)
                                            {
                                                changedFolderIds[old_folder_id] = data.changed_folder_ids[old_folder_id];
                                            }
                                            removeFromTree.push(data.remove_from_tree);
                                        }
                                    }

                                    // push prompt into prompt array
                                    if (data.prompt)
                                    {
                                        this.promptArray.push(data);
                                    }

                                    if (data.error)
                                    {
                                        alert(data.error);
                                    }
                                }

                                if (this.promptArray.length > 0)
                                {
                                    // define callback for completing all prompts
                                    var promptCallback = $.proxy(function(returnData)
                                    {
                                        this.$files.html('');

                                        var newParameterArray = [];

                                        // loop trough all returned data and prepare a new request array
                                        for (var i = 0; i < returnData.length; i++)
                                        {
                                            if (returnData[i].choice == 'cancel')
                                            {
                                                continue;
                                            }
                                            var lookingFor = returnData[i].file;

                                            // find the matching request parameters for this file and modify them slightly
                                            for (var ii = 0; ii < parameterArray.length; ii++)
                                            {
                                                if (parameterArray[ii].old_id == lookingFor)
                                                {
                                                    parameterArray[ii].action = returnData[i].choice;
                                                    newParameterArray.push(parameterArray[ii]);
                                                }
                                            }
                                        }

                                        // start working on them lists, baby
                                        if (newParameterArray.length == 0)
                                        {
                                            $.proxy(this, '_performActualFolderMove', fileMoveList, folderDeleteList, changedFolderIds, removeFromTree)();
                                        }
                                        else
                                        {
                                            // start working
                                            this.$fm.addClass('assets-loading');
                                            this.$uploadProgress.show();
                                            this.$uploadProgressBar.width('0%');

                                            // move conflicting files again with resolutions now
                                            moveFolder(newParameterArray, 0, onMoveFinish);
                                        }
                                    }, this);

                                    this._showBatchPrompts(this.promptArray, promptCallback);

                                    this.$fm.removeClass('assets-loading');
                                    this.$uploadProgress.hide();
                                }
                                else
                                {
                                    $.proxy(this, '_performActualFolderMove', fileMoveList, folderDeleteList, changedFolderIds, removeFromTree)();
                                }

                            }, this);

                            var moveFolder = $.proxy(function(parameterArray, parameterIndex, callback)
                            {
                                if (parameterIndex == 0)
                                {
                                    this.responseArray = [];
                                }

                                $.post(Assets.siteUrl, parameterArray[parameterIndex], $.proxy(function(data)
                                {
                                    this.responseArray.push(data);
                                    var width = Math.min(100, Math.round(100 * ++parameterIndex / parameterArray.length)) + '%';
                                    this.$uploadProgressBar.width(width);

                                    if (parameterIndex >= parameterArray.length)
                                    {
                                        callback(this.responseArray);
                                    }
                                    else
                                    {
                                        moveFolder(parameterArray, parameterIndex, callback);
                                    }
                                }, this));
                            }, this);

                            // initiate the folder move with the built array, index of 0 and callback to use when done
                            moveFolder(parameterArray, 0, onMoveFinish);

							// skip returning dragees until we get the Ajax response
							return;
						}
					}
					else
					{
						this._collapseExtraExpandedFolders();
					}

					this.folderDrag.returnHelpersToDraggees();
				}, this)
			});

			// -------------------------------------------
			//  File dragging
			// -------------------------------------------

			this.fileDrag = new Garnish.DragDrop({
				activeDropTargetClass: 'assets-selected assets-fm-dragtarget',
                helperOpacity: 0.5,

				filter: $.proxy(function()
				{
					return this.fileSelect.getSelectedItems();
				}, this),

				helper: $.proxy(function($file)
				{
					return this.filesView.getDragHelper($file);
				}, this),

				dropTargets: $.proxy(function()
				{
					var targets = [];

					for (var folderId in this.folders)
					{
						var folder = this.folders[folderId];

						if (folder.visible)
						{
							targets.push(folder.$a);
						}
					}

					return targets;
				}, this),

				onDragStart: $.proxy(function()
				{
					this.tempExpandedFolders = [];

					$selectedFolders = this.folderSelect.getSelectedItems();
					$selectedFolders.removeClass('assets-selected');
				}, this),

				onDropTargetChange: $.proxy(this, '_onDropTargetChange'),

				onDragStop: $.proxy(function()
				{
					if (this.fileDrag.$activeDropTarget)
					{
						// keep it selected
						this.fileDrag.$activeDropTarget.addClass('assets-selected');

						var targetFolderId = this.fileDrag.$activeDropTarget.attr('data-id');

						var originalFileIds = [],
                            newFileNames = [];


						for (var i = 0; i < this.fileDrag.$draggee.length; i++)
						{
							var originalFileId = this.fileDrag.$draggee[i].getAttribute('data-id'),
								fileName = this.fileDrag.$draggee[i].getAttribute('data-file_name');

                            originalFileIds.push(originalFileId);
                            newFileNames.push(fileName);
						}

						// are any files actually getting moved?
						if (originalFileIds.length)
						{
							this.$fm.addClass('assets-loading');
                            this.$uploadProgress.show();
                            this.$uploadProgressBar.width('0%');

                            // for each file to move a separate request
                            var parameterArray = [];
                            for (var i = 0; i < originalFileIds.length; i++)
                            {
                                parameterArray.push({
                                    ACT: Assets.actions.move_file,
                                    old_id: originalFileIds[i],
                                    folder_id: targetFolderId,
                                    file_name: newFileNames[i]
                                });
                            }

                            // define the callback for when all file moves are complete
                            var onMoveFinish = $.proxy(function(responseArray)
                            {
                                this.promptArray = [];

                                // loop trough all the responses
                                for (var i = 0; i < responseArray.length; i++)
                                {
                                    var data = $.evalJSON(responseArray[i]);

                                    // if succesful and have data, then update
                                    if (data.success)
                                    {
                                        if (data.old_file_id && data.file_id)
                                        {
                                            this._updateFileId(data.old_file_id, data.file_id);
                                        }
                                    }

                                    // push prompt into prompt array
                                    if (data.prompt)
                                    {
                                        this.promptArray.push(data);
                                    }

                                    // ugh, we don't want these..
                                    if (data.error)
                                    {
                                        alert(data.error);
                                    }
                                }

                                this.$fm.removeClass('assets-loading');
                                this.$uploadProgress.hide();

                                if (this.promptArray.length > 0)
                                {
                                    // define callback for completing all prompts
                                    var promptCallback = $.proxy(function(returnData)
                                    {
                                        this.$files.html('');

                                        var newParameterArray = [];

                                        // loop trough all returned data and prepare a new request array
                                        for (var i = 0; i < returnData.length; i++)
                                        {
                                            if (returnData[i].choice == 'cancel')
                                            {
                                                continue;
                                            }

                                            // find the matching request parameters for this file and modify them slightly
                                            for (var ii = 0; ii < parameterArray.length; ii++)
                                            {
                                                if (parameterArray[ii].file_name == returnData[i].file)
                                                {
                                                    parameterArray[ii].action = returnData[i].choice;
                                                    newParameterArray.push(parameterArray[ii]);
                                                }
                                            }
                                        }

                                        // nothing to do, carry on
                                        if (newParameterArray.length == 0)
                                        {
                                            this._updateSelectedFolders();
                                        }
                                        else
                                        {
                                            // start working
                                            this.$fm.addClass('assets-loading');
                                            this.$uploadProgress.show();
                                            this.$uploadProgressBar.width('0%');

                                            // move conflicting files again with resolutions now
                                            this._moveFile(newParameterArray, 0, onMoveFinish);
                                        }
                                    }, this);

                                    this.fileDrag.fadeOutHelpers();
                                    this._showBatchPrompts(this.promptArray, promptCallback);

                                }
                                else
                                {
                                    this.fileDrag.fadeOutHelpers();
                                    this._updateSelectedFolders();
                                }
                            }, this);

                            // initiate the file move with the built array, index of 0 and callback to use when done
                            this._moveFile(parameterArray, 0, onMoveFinish);

							// skip returning dragees
							return;
						}
					}
					else
					{
						this._collapseExtraExpandedFolders();
					}

					// re-select the previously selected folders
					$selectedFolders.addClass('assets-selected');

					this.fileDrag.returnHelpersToDraggees();
				}, this)
			});

		}

        this._moveFile = $.proxy(function(parameterArray, parameterIndex, callback)
        {
            if (parameterIndex == 0)
            {
                this.responseArray = [];
            }

            $.post(Assets.siteUrl, parameterArray[parameterIndex], $.proxy(function(data)
            {
                this.responseArray.push(data);
                var width = Math.min(100, Math.round(100 * ++parameterIndex / parameterArray.length)) + '%';
                this.$uploadProgressBar.width(width);

                if (parameterIndex >= parameterArray.length)
                {
                    callback(this.responseArray);
                }
                else
                {
                    this._moveFile(parameterArray, parameterIndex, callback);
                }
            }, this));
        }, this);


        this._performActualFolderMove = $.proxy(function(fileMoveList, folderDeleteList, changedFolderIds, removeFromTree)
        {
            this.$fm.addClass('assets-loading');
            this.$uploadProgress.show();
            this.$uploadProgressBar.width('0%');

            var moveCallback = $.proxy(function(folderDeleteList, changedFolderIds, removeFromTree)
            {
                var folder;

                // change the folder ids
                for (var old_folder_id in changedFolderIds)
                {
                    var new_value = changedFolderIds[old_folder_id].new_id;
                    var new_parent = changedFolderIds[old_folder_id].new_parent_id;

                    var old_folder = this.folders[old_folder_id];
                    this.folders[new_value] = old_folder;

					$('li.assets-fm-folder > a[data-id="'+old_folder_id+'"]:first').attr('data-id', new_value);
                    folder = this.folders[new_value];
                    folder.moveTo(new_parent);
                }

                // delete the old folders
                for (var i = 0; i < folderDeleteList.length; i++)
                {
                    var postData = {
                        ACT:       Assets.actions.delete_folder,
                        folder_id: folderDeleteList[i]
                    };

                    // TODO: Handle the response to this
                    $.post(Assets.siteUrl, postData);
                }

                if (removeFromTree.length > 0)
                {
                    // remove from tree the obsolete nodes
                    for (var i = 0; i < removeFromTree.length; i++)
                    {
                        if (removeFromTree[i].length)
                        {
                            this.folders[removeFromTree[i]].onDelete(true);
                        }
                    }
                }

                this.$fm.removeClass('assets-loading');
                this.$uploadProgress.hide();

                this._updateSelectedFolders();
                this.folderDrag.returnHelpersToDraggees();

            }, this);

            // just add the correct action
            for (var i = 0; i < fileMoveList.length; i++)
            {
                fileMoveList[i].ACT = Assets.actions.move_file;
            }

            if (fileMoveList.length > 0)
            {
                this._moveFile(fileMoveList, 0, $.proxy(function()
                {
                	moveCallback(folderDeleteList, changedFolderIds, removeFromTree);
                }, this));
            }
            else
            {
                moveCallback(folderDeleteList, changedFolderIds, removeFromTree);
            }
        }, this);


		// -------------------------------------------
		//  Search
		// -------------------------------------------

		this.$searchInput = $('.assets-fm-search:first input', this.$toolbar);
		this.$searchOptions = $('.assets-fm-searchoptions:first', this.$toolbar);
        this.$searchModeCheckbox = $('input', this.$searchOptions);

		if (this.getSourceState('searchMode') == 'deep')
		{
			this.$searchModeCheckbox.prop('checked', true);
		}

		this.$searchInput.keydown($.proxy(this, '_onSearchKeyDown'));
        this.$searchModeCheckbox.change($.proxy(this, '_onSearchModeChange'));

        // -------------------------------------------
        // Bring Assets to the stored state
        // -------------------------------------------

        // expand folders
        for (var folder in this.instanceState.folders)
        {
            if (this.instanceState.folders[folder] == 'expanded'
                && typeof this.folders[folder] !== 'undefined'
                && this.folders[folder].hasSubfolders())
            {
                this.folders[folder]._prepForSubfolders();
                this.folders[folder].expand();
            }
        }

        // mark selected
        var folderSelected = false;
        for (var i = 0; i < this.instanceState.selectedFolders.length; i++)
        {
            if (typeof this.folders[this.instanceState.selectedFolders[i]] !== 'undefined')
            {
                var folder = this.folders[this.instanceState.selectedFolders[i]];
                this.folderSelect.selectItem(folder.$a, true);
				this.currentSource = folder.$a.parents('[data-source_id]').data('source_id');
                folderSelected = true;
                break;
            }
        }

        if (!folderSelected)
        {
            this.folderSelect.selectItem(this.$folders.find('li:first a:first'), true);
        }

        // Keep the folders in view
        if (this.settings.mode == 'full')
        {
        	this.folderOffset = this.$folders.offset().top;
        	this.foldersFixed = false;

        	this.addListener(Garnish.$win, 'resize,scroll', '_onWindowScroll');
        }

        // -------------------------------------------
        //  Initialize the files view
        // -------------------------------------------

		this.viewSelect.selectItem(this.viewBtns[this.getSourceState('view')]);

		this._updateSelectedFolders();
	},

	_onWindowScroll: function()
	{
		if (Garnish.$win.scrollTop() > this.folderOffset)
		{
			if (!this.foldersFixed)
			{
				this.$folders.addClass('assets-fixed');
				this.foldersFixed = true;
			}

			// Make sure that the folders don't bleed into the page footer
			this._onWindowScroll._maxFoldersHeight = this.$pageContents.offset().top + this.$pageContents.outerHeight() - Garnish.$win.scrollTop();

			if (this._onWindowScroll._maxFoldersHeight < Garnish.$win.height())
			{
				this.$folders.height(this._onWindowScroll._maxFoldersHeight);
			}
			else
			{
				this.$folders.height('100%');
			}
		}
		else
		{
			if (this.foldersFixed)
			{
				this.$folders.removeClass('assets-fixed');
				this.$folders.height('auto');
				this.foldersFixed = false;
			}
		}
	},

	/**
	 * Update Selected Folders
	 */
	_updateSelectedFolders: function()
	{

		// get the new list of selected folder IDs
		this.selectedFolderIds = [];

		var $selected = this.folderSelect.getSelectedItems();

		for (var i = 0; i < $selected.length; i++)
		{
			this.selectedFolderIds.push($($selected[i]).attr('data-id'));
			this.currentSource = $($selected[i]).parents('[data-source_id]').data('source_id');
		}

        this.setInstanceState('selectedFolders', this.selectedFolderIds);

		// clear the keyword search and reset the offset
		this.eraseSearch();

        this.offset = 0;

		this.updateFiles();

		// -------------------------------------------
		//  Upload button state
		// -------------------------------------------

		if (this.selectedFolderIds.length == 1 && !$($selected[0]).data('no_uploads'))
		{
			// enable the upload button
            this.enableUploadBtn();
		}
		else
		{
			this.disableUploadBtn();
		}

		// -------------------------------------------
		//  View button state
		// -------------------------------------------

		this.viewBtns[this.getSourceState('view')].addClass('assets-active').siblings().removeClass('assets-active');
	},

     enableUploadBtn: function ()
     {
         this.uploader._button._input.removeAttribute('disabled');
         this.uploader._button._input.style.cursor = 'pointer';
         Assets.qq.removeClass(this.uploader._button._element, 'assets-disabled');

         this.uploader.setParams({
             folder: this.selectedFolderIds[0],
             ACT: Assets.actions.upload_file
         });

     },

     disableUploadBtn: function ()
     {
         this.uploader._button._input.setAttribute('disabled', 'disabled');
         this.uploader._button._input.style.cursor = 'default';
         Assets.qq.addClass(this.uploader._button._element, 'assets-disabled');
     },

	/**
	 * Set Folders Width
	 *
	 * This is called by Assets.FileManagerFolder instances when their toggle button has been clicked.
	 * It makes sure that the folders content width is equal to the pane's scroll width,
	 * which prevents inner elements from spanning the full width if there's horizontal scrolling
	 */
	setFoldersWidth: function()
	{
		// clear the ul's current width
		this.$topFolderUl.width('auto');

		// now we have an accurate scrollWidth
		var scrollWidth = this.$folders[0].scrollWidth,
			width = Math.max(scrollWidth, 225);

		this.$topFolderUl.width(width);

		// Set min-height on the files view
		if (this.settings.mode == 'full')
		{
			this.$files.css('min-height', this.$folders.outerHeight());
			this._onWindowScroll();
		}
	},

	/**
	 * On Drop Target Change
	 */
	_onDropTargetChange: function($dropTarget)
	{
		clearTimeout(this.expandDropTargetFolderTimeout);

		if ($dropTarget)
		{
			var folderId = $dropTarget.attr('data-id');

			if (folderId && typeof this.folders[folderId] != 'undefined')
			{
				this.dropTargetFolder = this.folders[folderId];

				if (this.dropTargetFolder.hasSubfolders() && ! this.dropTargetFolder.expanded)
				{
					this.expandDropTargetFolderTimeout = setTimeout($.proxy(this, '_expandDropTargetFolder'), 750);
				}
			}
			else
			{
				this.dropTargetFolder = null;
			}
		}
	},

	/**
	 * Expand Drop Target Folder
	 */
	_expandDropTargetFolder: function()
	{
		// collapse any temp-expanded drop targets that aren't parents of this one
		this._collapseExtraExpandedFolders(this.dropTargetFolder.id);

		this.dropTargetFolder.expand();

		// keep a record of that
		this.tempExpandedFolders.push(this.dropTargetFolder);

		// what's currently being dragged -- folders or files?
		var dragger = this.folderDrag.dragging ? this.folderDrag : this.fileDrag;

		// add the subfolders to the drop targets
		for (var i = 0; i < this.dropTargetFolder.subfolders.length; i++)
		{
			var subfolder = this.dropTargetFolder.subfolders[i];
			dragger.$dropTargets.push(subfolder.$a);
		}
	},

	/**
	 * Collapse Extra Expanded Folders
	 */
	_collapseExtraExpandedFolders: function(dropTargetFolderId)
	{
		clearTimeout(this.expandDropTargetFolderTimeout);

		for (var i = this.tempExpandedFolders.length-1; i >= 0; i--)
		{
			var folder = this.tempExpandedFolders[i];

			if (! dropTargetFolderId || !folder.isParent(dropTargetFolderId))
			{
				folder.collapse();
				this.tempExpandedFolders.splice(i, 1);
			}
		}
	},

	// -------------------------------------------
	//  Uploading
	// -------------------------------------------

	/**
	 * On Upload Submit
	 */
	_onUploadSubmit: function(id, fileName)
	{
		// is this the first file?
		if (! this.uploader.getInProgress())
		{
            this.promptArray = [];

			this.$fm.addClass('assets-loading');

			// prepare the progress bar
			this.$uploadProgress.show();
			this.$uploadProgressBar.width('0%');
			this._uploadFileProgress = {};

			this._uploadTotalFiles = 1;
			this._uploadedFiles = 0;
		}
		else {
			this._uploadTotalFiles++;
		}

		// get ready to start recording the progress for this file
		this._uploadFileProgress[id] = 0;
	},

	/**
	 * On Upload Progress
	 */
	_onUploadProgress: function(id, fileName, loaded, total)
	{
		this._uploadFileProgress[id] = loaded / total;
		this._updateProgressBar();
	},

	/**
	 * On Upload Complete
	 */
	_onUploadComplete: function(id, fileName, response)
	{
        if (typeof response.success == "undefined" && typeof response.prompt == "undefined" && typeof response.error == "undefined")
        {
            alert(Assets.lang.couldnt_upload);
        }

		this._uploadFileProgress[id] = 1;
		this._updateProgressBar();

		if (response.success || response.prompt)
		{
			this._uploadedFiles++;

			if (this.settings.multiSelect || !this.selectedFileIds.length)
			{
				this.selectedFileIds.push(response.file_id);
			}

            if (response.prompt)
            {
                this.promptArray.push(response);
            }

		}

		// is this the last file?
		if (! this.uploader.getInProgress())
		{
            if (this.promptArray.length > 0)
            {
                this._hideProgressBar();
                this.$fm.removeClass('assets-loading');
                this._showBatchPrompts(this.promptArray, this._uploadFollowup);
            }
            else
            {
                if (this._uploadedFiles)
                {
                    this.updateFiles($.proxy(this, '_hideProgressBar'));
                }
                else
                {
                    // just skip to hiding the progress bar
                    this._hideProgressBar();
                    this.$fm.removeClass('assets-loading');
                }
            }
		}
	},

    _uploadFollowup: function(returnData)
    {
        this.$files.html('');
        this.$uploadProgress.show();
        this.$uploadProgressBar.width('0%');

        var finalCallback = $.proxy(function()
        {
            this.$fm.removeClass('assets-loading');
            this.updateFiles($.proxy(this, '_hideProgressBar'));
        }, this);

        var doFollowup = $.proxy(function(parameterArray, parameterIndex, callback)
        {
            var postData = {
                ACT:             Assets.actions.upload_file,
                additional_info: parameterArray[parameterIndex].additional_info,
                file_name:       parameterArray[parameterIndex].file,
                action:          parameterArray[parameterIndex].choice
            };

            $.post(Assets.siteUrl, postData, $.proxy(function(data)
            {
                var width = Math.min(100, Math.round(100 * ++parameterIndex / parameterArray.length)) + '%';

                data = $.evalJSON(data);
                this.selectedFileIds.push(data.file_id);

                this.$uploadProgressBar.width(width);

                if (parameterIndex == parameterArray.length)
                {
                    callback();
                }
                else
                {
                    doFollowup(parameterArray, parameterIndex, callback);
                }
            }, this));
        }, this);

        doFollowup(returnData, 0, finalCallback);
    },

	/**
	 * Update Progress Bar
	 */
	_updateProgressBar: function()
	{
		var totalPercent = 0;

		for (var id in this._uploadFileProgress)
		{
			totalPercent += this._uploadFileProgress[id];
		}

		var width = Math.round(100 * totalPercent / this._uploadTotalFiles) + '%';
		this.$uploadProgressBar.width(width);
	},

	/**
	 * Hide Progress Bar
	 */
	_hideProgressBar: function()
	{
		this.$uploadProgress.fadeOut($.proxy(function()
		{
			this.$uploadProgress.hide();
		}, this));
	},

	// -------------------------------------------
	//  Files
	// -------------------------------------------

	/**
	 * Rename File
	 */
	_renameFile: function(event)
	{
		var fileId = event.currentTarget.getAttribute('data-id'),
			oldName = event.currentTarget.getAttribute('data-file_name'),
			newName = prompt(Assets.lang.rename, oldName);

		if (newName && newName != oldName)
		{
			this.$fm.addClass('assets-loading');

			var postData = {
				ACT:      Assets.actions.move_file,
				old_id:   fileId,
				folder_id: event.currentTarget.getAttribute('data-folder'),
                file_name: newName
			};

            var handleRename = function(data, textStatus)
            {
                if (typeof data == "string")
                {
                    data = $.evalJSON(data);
                }
                this.$fm.removeClass('assets-loading');

                if (textStatus == 'success')
                {
                    if (data.prompt)
                    {
                        this._showPrompt(data.prompt, data.choices, $.proxy(function (choice) {
                            if (choice != 'cancel')
                            {
                                postData.action = choice;
                                $.post(Assets.siteUrl, postData, $.proxy(handleRename, this));
                            }
                        }, this));
                    }

                    if (data.success)
                    {
                        this._updateFileId(data.old_file_id, data.file_id);
                        this.updateFiles();
                    }

                    if (data.error)
                    {
                        alert(data.error);
                    }
                }
            };

			$.post(Assets.siteUrl, postData, $.proxy(handleRename, this), 'json');
		}
	},

	/**
	 * Update File Id in the selected file list
	 *
	 * Keeps a selected file's name up-to-date through file refreshes
	 */
	_updateFileId: function(oldId, newId)
	{
		var selIndex = $.inArray(oldId, this.selectedFileIds);

		if (selIndex != -1)
		{
			this.selectedFileIds[selIndex] = newId;
		}
	},

	/**
	 * Delete File
	 */
	_deleteFile: function(event)
	{
		var fileId = event.currentTarget.getAttribute('data-id');

        var fileName = event.currentTarget.getAttribute('data-file_name');
		if (confirm(Assets.parseTag(Assets.lang.confirm_delete_file, 'file', fileName)))
		{
			this.$fm.addClass('assets-loading');

			var postData = {
				ACT:  Assets.actions.delete_file,
				file_id: fileId
			};

			$.post(Assets.siteUrl, postData, $.proxy(function(data, textStatus)
			{
				this.$fm.removeClass('assets-loading');

				if (textStatus == 'success')
				{
					if (data.success)
					{
						this.updateFiles();
					}

					if (data.error)
					{
						alert(data.error);
					}
				}
			}, this), 'json');
		}
	},

	/**
	 * Delete Files
	 */
	_deleteFiles: function(event)
	{
		if (confirm(Assets.parseTag(Assets.lang.confirm_delete_files, 'num', this.fileSelect.getTotalSelected())))
		{
			this.$fm.addClass('assets-loading');

			var postData = {
				ACT: Assets.actions.delete_file
			};

			var $selected = this.fileSelect.getSelectedItems();

			for (var i = 0; i < $selected.length; i++)
			{
				postData['file_id['+i+']'] = $selected[i].getAttribute('data-id');
			}

			$.post(Assets.siteUrl, postData, $.proxy(function(data, textStatus)
			{
				this.$fm.removeClass('assets-loading');

				if (textStatus == 'success')
				{
					var updateFiles = false;

					for (var i = 0; i < data.length; i++)
					{
						if (data[i].success)
						{
							updateFiles = true;
						}

						if (data[i].error)
						{
							alert(data[i].error);
						}
					}

					if (updateFiles)
					{
						this.updateFiles();
					}
				}
			}, this), 'json');
		}
	},

	/**
	 * View File
	 */
	_viewFile: function(event)
	{
		var fileId = event.currentTarget.getAttribute('data-id'),
			url = Assets.siteUrl + '?ACT=' + Assets.actions.view_file+'&file_id='+fileId;

		window.open(url);
	},

	/**
	 * Show Properties
	 */
	_showProperties: function(event)
	{
		new Assets.Properties($(event.currentTarget));
	},

	// -------------------------------------------
	//  Keyword Search
	// -------------------------------------------

	/**
	 * On Search Key Down
	 */
	_onSearchKeyDown: function(event)
	{
		// ignore if meta/ctrl key is down
		if (event.metaKey || event.ctrlKey) return;

		event.stopPropagation();

		// clear the last timeout
		clearTimeout(this.searchTimeout);

		setTimeout($.proxy(function()
		{
			switch (event.keyCode)
			{
				case 13: // return
				{
					event.preventDefault();
					this._checkKeywordVal();
					break;
				}

				case 27: // esc
				{
					event.preventDefault();
					this.$searchInput.val('');
					this._checkKeywordVal();
					break;
				}

				default:
				{
					this.searchTimeout = setTimeout($.proxy(this, '_checkKeywordVal'), 500);
				}
			}

		}, this), 0);
	},

	_checkKeywordVal: function()
	{
		// has the value changed?
		if (this.searchVal !== (this.searchVal = this.$searchInput.val()))
		{
			if (this.searchVal && !this.showingSearchOptions)
			{
				this._showSearchOptions();
			}
			else if (!this.searchVal && this.showingSearchOptions)
			{
				this._hideSearchOptions()
			}


			this.updateFiles();
		}
	},

	_showSearchOptions: function()
	{
		this.showingSearchOptions = true;
		this.$searchOptions.css({ display: 'block', height: 'auto' });
		var height = this.$searchOptions.height();
		this.$searchOptions.height(0).animate({ height: height }, 'fast', $.proxy(function()
		{
			this.$searchOptions.css('height', 'auto');
		}, this));
	},

	_hideSearchOptions: function()
	{
		this.showingSearchOptions = false;
		this.$searchOptions.animate({ height: 0 }, 'fast', $.proxy(function()
		{
			this.$searchOptions.hide();
		}, this));
	},

	_onSearchModeChange: function()
	{
    	if (this.$searchModeCheckbox.prop('checked'))
    	{
    		var searchMode = 'deep';
    	}
    	else
    	{
    		var searchMode = 'shallow';
    	}

		this.setSourceState('searchMode', searchMode);
        this.updateFiles();
	},

	eraseSearch: function()
	{
		this.$searchInput.val('');
		this.searchVal = '';
	},

	// -------------------------------------------
	//  Application State
	// -------------------------------------------

	/**
	 * Set the new state of the instance, updating localStorage if possible.
	 *
	 * Accepts two formats: setInstanceState('property', 'newValue') and setInstanceState{ property1: 'newValue', property2: 'newValue', ... }
	 */
    setInstanceState: function(key, value)
    {
    	if (typeof key == 'string')
    	{
    		var newState = {};
    		newState[key] = value;
    	}
    	else
    	{
    		var newState = key;
    	}

        $.extend(this.instanceState, newState);

        if (typeof Storage !== 'undefined')
        {
            localStorage[this.instanceStateStorageKey] = $.toJSON(this.instanceState);
        }
    },

    /**
     * Sets the new state of a folder.
     */
    setFolderState: function(folder, state)
    {
        var newStates = this.instanceState.folders;
        newStates[folder] = state;
        this.setInstanceState('folders', newStates);
    },

	/**
	 * Set a state for a source.
	 *
	 * Accepts two formats: setInstanceState('property', 'newValue') and setInstanceState{ property1: 'newValue', property2: 'newValue', ... }
	 */
	setSourceState: function(key, value)
	{
		if (typeof(this.sourceState[this.currentSource]) == "undefined")
		{
			this.sourceState[this.currentSource] = this.defaultSourceState;
		}

		if (typeof key == 'string')
		{
			var newState = {};
			newState[key] = value;
		}
		else
		{
			var newState = key;
		}

		$.extend(this.sourceState[this.currentSource], newState);

		if (typeof Storage !== 'undefined')
		{
			localStorage[this.sourceStateStorageKey] = $.toJSON(this.sourceState);
		}
	},

	getSourceState: function (key)
	{
		if (typeof this.sourceState[this.currentSource] == "undefined")
		{
			this.sourceState[this.currentSource] = this.defaultSourceState;
		}
		return this.sourceState[this.currentSource][key];
	},

	// -------------------------------------------
	//  Update Files
	// -------------------------------------------

	switchView: function()
	{
		var $btn = this.viewSelect.getSelectedItems(),
			view = $btn.attr('data-view');

		this.setSourceState('view', view);
		this.updateFiles();
	},

	/**
	 * Update Files
	 */
	updateFiles: function(callback)
	{
        this.nextOffset = 0;
		this.filesRequestId++;
        this._singleFileMenu = [];
        this._multiFileMenu = [];

        if (this.settings.mode == 'full')
        {
            this.fileDrag.removeAllItems();
        }

        this._beforeLoadFiles();

        var postData = this._prepareFileViewPostData();

		// destroy previous select & view
		if (this.fileSelect) this.fileSelect.destroy();
		if (this.filesView) this.filesView.destroy();
		this.fileSelect = this.filesView = null;

		this.$fm.addClass('assets-loading');

		// run the ajax post request
		$.post(Assets.siteUrl, postData, $.proxy(function(data, textStatus)
		{

            this.$fm.removeClass('assets-loading');

			if (textStatus == 'success')
			{
				// ignore if this isn't the current request
				if (data.requestId != this.filesRequestId) return;

                this.discardStyles();

                // update the HTML
                this.$files.html(data.html);

                // initialize the files view
				if (this.getSourceState('view') == 'list')
				{
					this.filesView = new Assets.ListView($('> .assets-listview', this.$files), {
						orderby: this.getSourceState('orderby'),
						sort:    this.getSourceState('sort'),
						onSortChange: $.proxy(function(orderby, sort)
						{
							this.setSourceState({
								orderby: orderby,
								sort: sort
							});
							this.updateFiles();
						}, this)
					});
				}
				else
				{
					this.filesView = new Assets.ThumbView($('> .assets-thumbview', this.$files));
				}

                // initialize the files multiselect
                this.fileSelect = new Garnish.Select(this.$files, {
                    selectedClass:     'assets-selected',
                    multi:             this.settings.multiSelect,
                    waitForDblClick:   (this.settings.multiSelect && this.settings.mode == 'select'),
                    vertical:          (this.getSourceState('view') == 'list'),
                    onSelectionChange: $.proxy(this, '_onFileSelectionChange'),
                    $scrollpane:       this.$scrollpane
                });


                // get the files
                var $files = this.filesView.getItems().not('.assets-disabled');

                this._afterLoadFiles(data, $files);

				// did this happen immediately after an upload?
				this._onFileSelectionChange();

				// scroll to the first selected file
				if (this.selectedFileIds.length)
				{
					var $selected = this.fileSelect.getSelectedItems();
					Garnish.scrollContainerToElement(this.$scrollpane, $selected);
				}

				// -------------------------------------------
				//  callback
				//
					if (typeof callback == 'function')
					{
						callback();
					}
				//
				// -------------------------------------------

                // Initialize the next-page loader if necessary

                this._initializePageLoader();
			}
		}, this), 'json');
	},


    /**
     * Discard loaded styles for thumbnails
     */
    discardStyles: function () {

        // Empty the previous css storage
        $.each(this.css, $.proxy(function (index) {
            this.css[index].remove();
        }, this));
        this.css = [];
    },

    /**
     * Called right before loading files
     */
    _beforeLoadFiles: function ()
    {
        // -------------------------------------------
        //  onBeforeUpdateFiles callback
        //
        if (typeof this.settings.onBeforeUpdateFiles == 'function')
        {
            this.settings.onBeforeUpdateFiles();
        }
        //
        // -------------------------------------------
    },

    /**
     * Called right after loading files
     */
    _afterLoadFiles: function (data, $files)
    {
        var newCss = $('<style type="text/css">' + data.css + '</style>');
        this.css.push(newCss.appendTo(document.body));

        // This way we will suffer an extra request upon reaching the last page, but we don't have to set the page size
        if (data.total > 0)
        {
            this.nextOffset += data.total;
            this.lastPageReached = false;
        }
        else
        {
            this.lastPageReached = true;
        }

        this.fileSelect.addItems($files);

        if (this.settings.mode == 'full')
        {
            // file dragging
            this.fileDrag.addItems($files);
        }

        // double-click handling
        this.addListener($files, 'dblclick', function(ev)
        {
            switch (this.settings.mode)
            {
                case 'select':
                {
                    clearTimeout(this.fileSelect.clearMouseUpTimeout());
                    this.settings.onSelect();
                    break;
                }

                case 'full':
                {
                    this._showProperties(ev);
                    break;
                }
            }
        });

        // -------------------------------------------
        //  Context Menus
        // -------------------------------------------

        var menuOptions = [{ label: Assets.lang.view_file, onClick: $.proxy(this, '_viewFile') }];

        if (this.settings.mode == 'full')
        {
            menuOptions.push({ label: Assets.lang.edit_file, onClick: $.proxy(this, '_showProperties') });
            menuOptions.push({ label: Assets.lang.rename, onClick: $.proxy(this, '_renameFile') });
            menuOptions.push('-');
            menuOptions.push({ label: Assets.lang._delete, onClick: $.proxy(this, '_deleteFile') });
        }

        this._singleFileMenu.push(
            new Garnish.ContextMenu($files, menuOptions, {
                menuClass: 'assets-contextmenu'
            })
        );

        if (this.settings.mode == 'full')
        {

            var menu = new Garnish.ContextMenu($files, [
                { label: Assets.lang._delete, onClick: $.proxy(this, '_deleteFiles') }
            ], {
                menuClass: 'assets-contextmenu'
            });

            menu.disable();

            this._multiFileMenu.push(menu);
        }
    },

    _prepareFileViewPostData: function ()
    {
        var postData = {
            ACT:         Assets.actions.get_files_view_by_folders,
            requestId:   this.filesRequestId,
            view:        this.getSourceState('view'),
            keywords:    this.searchVal,
            search_type: this.getSourceState('searchMode')
        };

        if (this.getSourceState('view') == 'list')
        {
            postData.orderby = this.getSourceState('orderby');
            postData.sort = this.getSourceState('sort');
        }

		// Check if this is the recent uploads folder
		if (this.selectedFolderIds.length && this.selectedFolderIds[0] == 'recent')
		{
			//If so, mark that property and add *all* the folders we have in the current view.
			var i = 0;
			for (var folderId in this.folders)
			{
				postData['folders['+ (i++) +']'] = folderId;
			}
			postData['special'] = "recent";
		}
		// Just the folder(s), please.
		else
		{
			for (var i in this.selectedFolderIds)
			{
				postData['folders['+i+']'] = this.selectedFolderIds[i];
			}
		}

        // pass the file kinds
        if (! this.settings.kinds || this.settings.kinds == 'any')
        {
            postData.kinds = 'any';
        }
        else
        {
            for (var i = 0; i < this.settings.kinds.length; i++)
            {
                postData['kinds['+i+']'] = this.settings.kinds[i];
            }
        }

        // pass the disabled files
        for (var i = 0; i < this.settings.disabledFiles.length; i++)
        {
            postData['disabled_files['+i+']'] = this.settings.disabledFiles[i];
        }

        // pass the selected files
        for (var i = 0; i < this.selectedFileIds.length; i++)
        {
            if (typeof this.selectedFileIds[i] != "undefined")
            {
                postData['selected_files['+i+']'] = this.selectedFileIds[i];
            }
        }

        return postData;
    },

    /**
     * Initialize the page loader.
     */
    _initializePageLoader: function ()
    {
        if (!this.lastPageReached)
        {
            var $scrollElement = Garnish.$win;
            var $docElement = Garnish.$doc;
            if (this.settings.mode == "select")
            {
                $scrollElement = this.$main;
                $docElement = this.$files;
            }

            var handler = function () {
                if(!this.$fm.hasClass('assets-page-loading') && $scrollElement.scrollTop() + $scrollElement.height() > $docElement.height() - 400) {
                    this.$fm.addClass('assets-page-loading');
                    $scrollElement.unbind('scroll', $.proxy(handler, this));
                    this.loadMoreFiles();
                }
            };
            handler.call(this);
            $scrollElement.bind('scroll', $.proxy(handler, this));
        }


    },

    /**
     * Load more files
     */
    loadMoreFiles: function ()
    {
        this.filesRequestId++;
        this._beforeLoadFiles();
        var postData = this._prepareFileViewPostData();

        postData.offset = this.nextOffset;

        // run the ajax post request
        $.post(Assets.siteUrl, postData, $.proxy(function(data, textStatus)
        {
            data = $.evalJSON(data);
            this.$fm.removeClass('assets-page-loading');

            if (textStatus == 'success')
            {
                // ignore if this isn't the current request
                if (data.requestId != this.filesRequestId) return;

                if (this.getSourceState('view') == 'list')
                {
                    $newFiles = $(data.html).find('tbody>tr');
                }
                else
                {
                    $newFiles = $(data.html).find('ul li');
                }

                if ($newFiles.length > 0)
                {
                    $enabledFiles = $newFiles.not('.assets-disabled');
                    if (this.filesView != null)
                    {
                        this.filesView.addItems($newFiles);
                        this._afterLoadFiles(data, $enabledFiles);
                    }


                    this._initializePageLoader();
                }

            }
        }, this));
    },

	/**
	 * Re-indexes the currently selected folder, and reloads the files view.
	 */
	refreshFiles: function()
	{
		var folderId = this.selectedFolderIds[0];
		if (folderId == "recent")
		{
			this._updateSelectedFolders();
			return;
		}

		this.$fm.addClass('assets-loading');

		// prepare the progress bar
		this.$uploadProgress.show();
		this.$uploadProgressBar.width('0%');

		var indexItems = 0;
		this.currentIndexItem = 0;

		var postData = {
			ACT: Assets.actions.get_session_id
		};

		$.post(Assets.siteUrl, postData, $.proxy(function(data)
		{
		    data = $.evalJSON(data);
		    var session = data.session;

		    // Got session - create the Queue Manager
		    var queue = new Assets.AjaxQueueManager(10, $.proxy(function()
		    {
		        this.$uploadProgress.hide();
		        this._updateSelectedFolders();

		        // Send a request for statistics to clean up the indexing data table
		        $.post(Assets.siteUrl, {
		            ACT:     Assets.actions.finish_index,
		            session: session,
		            command: $.toJSON({command: 'statistics'})
		        });

		    }, this));

		    // Start folder re-index
		    var postData = {
		        ACT: Assets.actions.start_index,
		        folder_id: folderId,
		        session: session
		    };

		    queue.addItem(Assets.siteUrl, postData, $.proxy(function(data)
		    {
		        data = $.evalJSON(data);
		        indexItems = data.total;
		        for (var i = 0; i < data.total; i++)
		        {
		            var postData = {
		                ACT: Assets.actions.perform_index,
		                session: session,
		                folder_id: folderId,
		                offset: i
		            };

		            queue.addItem(Assets.siteUrl, postData, $.proxy(function()
		            {
		                this.$uploadProgressBar.width(Math.min(Math.ceil(100 / indexItems * (++this.currentIndexItem)), 100) + '%');
		            }, this));

		        }

		    }, this));
		    queue.startQueue();
		}, this));
	},

	/**
	 * Show the user prompt with a given message and choices, plus an optional "Apply to remaining" checkbox.
	 *
	 * @param string message
	 * @param array choices
	 * @param function callback
	 * @param int itemsToGo
	 */
	_showPrompt: function(message, choices, callback, itemsToGo)
	{
		this._promptCallback = callback;

		if (!this.$prompt)
		{
			this.$shade = $('<div class="assets-shade" />').appendTo(document.body).hide();
			this.$prompt = $('<div class="assets-prompt"/>').appendTo(document.body).hide();
			this.$promptMessage = $('<p class="assets-prompt-msg"/>').appendTo(this.$prompt);
			$('<p>').html(Assets.lang.how_to_proceed).appendTo(this.$prompt);
			this.$promptButtons = $('<div class="assets-buttons"/>').appendTo(this.$prompt);

			this.$promptApplyToRemainingContainer = $('<label class="assets-applytoremaining"/>').appendTo(this.$prompt).hide();
			this.$promptApplyToRemainingCheckbox = $('<input type="checkbox"/>').appendTo(this.$promptApplyToRemainingContainer);
			this.$promptApplyToRemainingLabel = $('<span/>').appendTo(this.$promptApplyToRemainingContainer);

			this.addListener(this.$shade, 'click', '_cancelPrompt');
		}
		else
		{
			this.$promptButtons.html('');
			this.$promptApplyToRemainingContainer.hide();
			this.$promptApplyToRemainingCheckbox.prop('checked', false);
		}

		this.$promptMessage.html(message);

		for (var i = 0; i < choices.length; i++)
		{
			var $btn = $('<div class="assets-btn" data-choice="'+choices[i].value+'"/>').html(choices[i].title).appendTo(this.$promptButtons);

			this.addListener($btn, 'activate', function(ev)
			{
				var choice = ev.currentTarget.getAttribute('data-choice'),
					applyToRemaining = this.$promptApplyToRemainingCheckbox.prop('checked');

				this._selectPromptChoice(choice, applyToRemaining);
			});
		}

		if (itemsToGo)
		{
			this.$promptApplyToRemainingContainer.show();
			this.$promptApplyToRemainingLabel.html(Assets.lang.apply_to_remaining_conflicts.replace('{number}', itemsToGo));
		}

		this.$prompt.show();
		this.$prompt.css({
			marginTop: -(Math.round(this.$prompt.outerHeight() / 2))
		});

		this.$shade.fadeIn('fast');
		this.$prompt.hide().fadeIn('fast');
	},

	/**
	 * Handles when a user selects one of the prompt choices.
	 *
	 * @param object ev
	 */
	_selectPromptChoice: function(choice, applyToRemaining)
	{
		this.$shade.fadeOut('fast');
		this.$prompt.fadeOut('fast', $.proxy(function() {
			this.$prompt.hide();
			this._promptCallback(choice, applyToRemaining);
		}, this));
	},

	/**
	 * Cancels the prompt.
	 */
	_cancelPrompt: function()
	{
		this._selectPromptChoice('cancel', true);
	},

	/**
	 * Shows a batch of prompts.
	 *
	 * @param array   promts
	 * @param funtion callback
	 */
	_showBatchPrompts: function(prompts, callback)
	{
		this._promptBatchData = prompts;
		this._promptBatchCallback = callback;
		this._promptBatchReturnData = [];
		this._promptBatchNum = 0;

		this._showNextPromptInBatch();
	},

	/**
	 * Shows the next prompt in the batch.
	 */
	_showNextPromptInBatch: function()
	{
		var prompt = this._promptBatchData[this._promptBatchNum],
			remainingInBatch = this._promptBatchData.length - (this._promptBatchNum + 1);

		this._showPrompt(prompt.prompt, prompt.choices, $.proxy(this, '_handleBatchPromptSelection'), remainingInBatch);
	},

	/**
	 * Handles a prompt choice selection.
	 *
	 * @param string choice
	 * @param bool   applyToRemaining
	 */
	_handleBatchPromptSelection: function(choice, applyToRemaining)
	{
		var prompt = this._promptBatchData[this._promptBatchNum],
			remainingInBatch = this._promptBatchData.length - (this._promptBatchNum + 1);

		// Record this choice
		this._promptBatchReturnData.push({
		    file:            prompt.file_name,
		    choice:          choice,
		    additional_info: prompt.additional_info
		});

        // Are there any remaining items in the batch?
        if (remainingInBatch)
        {
        	// Get ready to deal with the next prompt
    	    this._promptBatchNum++;

        	// Apply the same choice to the remaining items?
    	    if (applyToRemaining)
    	    {
    	    	this._handleBatchPromptSelection(choice, true);
    	    }
    	    else
    	    {
    	    	// Show the next prompt
    	    	this._showNextPromptInBatch();
    	    }
        }
        else
        {
        	// All done! Call the callback
        	if (typeof this._promptBatchCallback == 'function')
        	{
        		this._promptBatchCallback(this._promptBatchReturnData);
        	}
        }
	},

	/**
	 * On Selection Change
	 */
	_onFileSelectionChange: function()
	{
		if (this.settings.mode == 'full')
		{
            var i = 0;
			if (this.fileSelect.getTotalSelected() == 1)
			{
                for (i = 0; i < this._singleFileMenu.length; i++)
                {
                    this._singleFileMenu[i].enable();
                    this._multiFileMenu[i].disable();
                }

			}
            else if (this.fileSelect.getTotalSelected() > 1)
			{
                for (i = 0; i < this._singleFileMenu.length; i++)
                {
                    this._singleFileMenu[i].disable();
                    this._multiFileMenu[i].enable();
                }
			}
		}

		// update our internal array of selected files
		this.selectedFileIds = [];
		var $selected = this.fileSelect.getSelectedItems();

		for (var i = 0; i < $selected.length; i++)
		{
			this.selectedFileIds.push($($selected[i]).attr('data-id'));
		}

		// -------------------------------------------
		//  onSelectionChange callback
		//
			if (typeof this.settings.onSelectionChange == 'function')
			{
				this.settings.onSelectionChange();
			}
		//
		// -------------------------------------------
	}
},
{
	defaults: {
		mode:          'full',
		multiSelect:   true,
		kinds:         'any',
		disabledFiles: [],
	    namespace:     'panel',
		context:       'sheet'
	}
});


/**
 * File Manager Folder
 */
Assets.FileManagerFolder = Garnish.Base.extend({

	/**
	 * Constructor
	 */
	init: function(fm, li, depth, parent)
	{
		this.fm = fm;
		this.li = li;
		this.depth = depth;
		this.parent = parent;

		this.$li = $(this.li);
		this.$a = $('> a', this.$li);
		this.$toggle;
		this.$ul;

		this.id = this.$a.attr('data-id');

		this.visible = false;
		this.visibleBefore = false;
		this.expanded = false;
		this.subfolders = [];

		this.fm.folders[this.id] = this;

		this.folderName = this.$a.text().replace(/\s+$/,"").replace(/^\s+/, '');

		// -------------------------------------------
		//  Make top-level folders visible
		// -------------------------------------------

		if (this.depth == 1)
		{
			this.onShow();
		}

		// -------------------------------------------
		//  Create the context menu
		// -------------------------------------------

		var menuOptions = [];

		if (! this.$a.data('no_menu'))
		{

			if (this.fm.settings.mode == 'full' && this.depth > 1)
			{
				menuOptions.push({ label: Assets.lang.rename, onClick: $.proxy(this, '_rename') });
				menuOptions.push('-');
			}

			menuOptions.push({ label: Assets.lang.new_subfolder, onClick: $.proxy(this, '_createSubfolder') });

			if (this.fm.settings.mode == 'full' && this.depth > 1)
			{
				menuOptions.push('-');
				menuOptions.push({ label: Assets.lang._delete, onClick: $.proxy(this, '_delete') });
			}

			new Garnish.ContextMenu(this.$a, menuOptions, {
				menuClass: 'assets-contextmenu'
			});
		}
	},

	// -------------------------------------------
	//  Subfolders and the toggle button
	// -------------------------------------------

	/**
	 * Has Subfolders
	 */
	hasSubfolders: function()
	{
		return this.$li.find('ul li').length > 0;
	},

	/**
	 * Prep for Subfolders
	 */
	_prepForSubfolders: function()
	{
		// add the toggle
		if (! this.$toggle)
		{
			this.$toggle = $('<span class="assets-fm-toggle"></span>');
		}

		this.$toggle.prependTo(this.$a);

		// prevent toggle button clicks from triggering multi select functions
		this.addListener(this.$toggle, 'mouseup,mousedown,click', function(ev)
		{
			ev.stopPropagation();
		});

		// toggle click handling. unbind events beforehand, to avoid double-toggling
		this.removeListener(this.$toggle, 'click');
		this.addListener(this.$toggle, 'click', '_toggle');

		// add the $ul
		if (! this.$ul)
		{
			if (this.$li.children().filter('ul').length == 0)
			{
				this.$li.append('<ul></ul>');
			}
			this.$ul = this.$li.children().filter('ul');
		}

		this.$ul.appendTo(this.$li);
	},

	/**
	 * Unprep for Subfolders
	 */
	_unprepForSubfolders: function()
	{
		this.$toggle.remove();
		this.$ul.remove();
		this.collapse();
	},

	/**
	 * Add Subfolder
	 */
	addSubfolder: function(subfolder)
	{
		// is this our first subfolder?
		if (! this.hasSubfolders())
		{
			this._prepForSubfolders();

			var pos = 0;
		}
		else
		{
			var folders = [ {name: subfolder.folderName, id: subfolder.id} ];

			for (var i = 0; i < this.subfolders.length; i++)
			{

                folders.push({name: this.subfolders[i].folderName, id: this.subfolders[i].id});
			}

            folders.sort(Assets.FileManagerFolder.folderSort);

            for (i = 0; i < folders.length; i++)
            {
                if (folders[i].name == subfolder.folderName)
                {
                    pos = i;
                    break;
                }
            }
		}

		if (pos == 0)
		{
			subfolder.$li.prependTo(this.$ul);
			this.$ul.prepend(subfolder.$li);
		}
		else
		{
			var prevSibling = this.fm.folders[folders[pos-1].id];
			subfolder.$li.insertAfter(prevSibling.$li);
		}

		this.subfolders.push(subfolder);
	},

	/**
	 * Remove Subfolder
	 */
	removeSubfolder: function(subfolder)
	{
		this.subfolders.splice($.inArray(subfolder, this.subfolders), 1);

		// was this the only subfolder?
		if (! this.hasSubfolders())
		{
			this._unprepForSubfolders();
		}
	},

	/**
	 * Toggle
	 */
	_toggle: function()
	{
		if (this.expanded)
		{
			this.collapse();
		}
		else
		{
			this.expand();
		}
	},

	/**
	 * Expand
	 */
	expand: function()
	{
		if (this.expanded) return;

		this.expanded = true;

		this.$a.addClass('assets-fm-expanded');

		this.$ul.show();
		this._onShowSubfolders();
		this.fm.setFoldersWidth();

		// Store folder state
		this.fm.setFolderState(this.id, 'expanded');
	},

	/**
	 * Collapse
	 */
	collapse: function()
	{
		if (! this.expanded) return;

		this.expanded = false;
		this.$a.removeClass('assets-fm-expanded');

		this.$ul.hide();
		this._onHideSubfolders();
		this.fm.setFoldersWidth();

		// Store folder state
		this.fm.setFolderState(this.id, 'collapsed');
	},

	// -------------------------------------------
	//  Showing and hiding
	// -------------------------------------------

	/**
	 * On Show
	 */
	onShow: function()
	{
		this.visible = true;

		this.fm.folderSelect.addItems(this.$a);

		if (this.depth > 1)
		{
			if (this.fm.settings.mode == 'full')
			{
				this.fm.folderDrag.addItems(this.$li);
			}
		}

		if (! this.visibleBefore)
		{
			this.visibleBefore = true;


			if (this.hasSubfolders())
			{
				this._prepForSubfolders();

				// initialize sub folders
				var $lis = this.$ul.children().filter('li');

				for (var i = 0; i < $lis.length; i++)
				{
					var subfolder = new Assets.FileManagerFolder(this.fm, $lis[i], this.depth + 1, this);
					this.subfolders.push(subfolder);
				};
			}
		}

		if (this.expanded)
		{
			this._onShowSubfolders();
		}
	},

	/**
	 * On Hide
	 */
	onHide: function()
	{
		this.visible = false;
		this.fm.folderSelect.removeItems(this.$a);

		if (this.expanded)
		{
			this._onHideSubfolders();
		}
	},

	/**
	 * On Show Subfolders
	 */
	_onShowSubfolders: function()
	{
		for (var i in this.subfolders)
		{
			this.subfolders[i].onShow();
		}
	},

	/**
	 * On Hide Subfolders
	 */
	_onHideSubfolders: function()
	{
		for (var i in this.subfolders)
		{
			this.subfolders[i].onHide();
		}
	},

	/**
	 * On Delete
	 */
	onDelete: function(isTopDeletedFolder)
	{
		// remove the master record of this folder
		delete this.fm.folders[this.id];

		var selIndex = this.fm.selectedFolderIds.indexOf(this.id);
		if (selIndex != -1)
		{
			// remove this folder from the selected folders array
			this.fm.selectedFolderIds.splice(selIndex, i);
		}

		if (isTopDeletedFolder)
		{
			// remove the parent folder's record of this folder
			this.parent.removeSubfolder(this);

			// remove the LI
			this.$li.remove();
		}

		for (var i = 0; i < this.subfolders.length; i++)
		{
			this.subfolders[i].onDelete();
		}

		if (! this.parent.hasSubfolders())
		{
			this.parent._unprepForSubfolders();
		}

        this.deselect();
        this.parent.select();
    },

	// -------------------------------------------
	//  Operations
	// -------------------------------------------

    deselect: function ()
    {
        this.fm.folderSelect.deselectItem(this.$a);
    },

    select: function ()
    {
        this.fm.folderSelect.selectItem(this.$a);
    },


	/**
	 * Move to...
	 */
	moveTo: function(newId)
	{
		var newParent = this.fm.folders[newId];

		// is the old boss the same as the new boss?
		if (newParent == this.parent) return;

		// add this to the new parent
		// (we need to do this first so that the <li> is always in the DOM, and keeps its events)
		newParent.addSubfolder(this);

		// remove this from the old parent
		this.parent.removeSubfolder(this);

		// set the new depth
		this.updateDepth(newParent.depth + 1);

		// make sure the new parent is expanded
		newParent.expand();

		this.parent = newParent;

	},

	/**
	 * Update Id
	 */
	updateId: function(id)
	{
		delete this.fm.folders[this.id];

		var selIndex = this.fm.selectedFolderIds.indexOf(this.id);
		if (selIndex != -1)
		{
			// update the selected folders array
			this.fm.selectedFolderIds[selIndex] = path;
		}

		this.id = id;
		this.$a.attr('data-id', this.id);
		this.fm.folders[this.id] = this;

		// update subfolders
		for (var i = 0; i < this.subfolders.length; i++)
		{
			var subfolder = this.subfolders[i],
				newId = this.id + subfolder.id+'/';

			subfolder.updateId(newId);
		}
	},

	/**
	 * Update Name
	 */
	updateName: function(name)
	{
		$('span.assets-fm-label', this.$a).html(name);

		// -------------------------------------------
		//  Re-sort this folder among its siblings
		// -------------------------------------------

        var folders = [ {name: name, id: this.id} ];

        for (var i = 0; i < this.parent.subfolders.length; i++)
        {
            if (this.parent.subfolders[i].folderName != this.folderName) {
                folders.push({name: this.parent.subfolders[i].folderName, id: this.parent.subfolders[i].id});
            }
        }

        folders.sort(Assets.FileManagerFolder.folderSort);

        for (i = 0; i < folders.length; i++) {
            if (folders[i].name == name) {
                pos = i;
                break;
            }
        }

		if (pos == 0)
		{
			this.$li.prependTo(this.parent.$ul);
		}
		else
		{
			var prevSibling = this.fm.folders[folders[pos-1].id];
			this.$li.insertAfter(prevSibling.$li);
		}

		this.folderName = name;
	},

	/**
	 * Update Depth
	 */
	updateDepth: function(depth)
	{
		if (depth == this.depth) return;

		this.depth = depth;

		var padding = 20 + (18 * this.depth);
		this.$a.css('padding-left', padding);

		for (var i = 0; i < this.subfolders.length; i++)
		{
			this.subfolders[i].updateDepth(this.depth + 1);
		}
	},

	/**
	 * Rename
	 */
	_rename: function()
	{
		var oldName = this.folderName,
			newName = prompt(Assets.lang.rename, oldName);

		if (newName && newName != oldName)
		{
			var data = {
				ACT:      Assets.actions.rename_folder,
				folder_id: this.$a.attr('data-id'),
				new_name: newName
			};

			this.fm.$fm.addClass('assets-loading');

			$.post(Assets.siteUrl, data, $.proxy(function(data, textStatus)
			{
				this.fm.$fm.removeClass('assets-loading');

				if (textStatus == 'success')
				{
					if (data.success)
					{
						this.updateName(data.new_name);

						// refresh the files view
						this.fm.updateFiles();
					}

					if (data.error)
					{
						alert(data.error);
					}
				}
			}, this), 'json');
		}
	},

	/**
	 * Create Subfolder
	 */
	_createSubfolder: function()
	{
		var subfolderName = prompt(Assets.lang.new_subfolder);

		if (subfolderName)
		{

			var data = {
				ACT:  Assets.actions.create_folder,
                parent_folder: this.id,
                folder_name: subfolderName
			}

			this.fm.$fm.addClass('assets-loading');

			$.post(Assets.siteUrl, data, $.proxy(function(data, textStatus)
			{
				this.fm.$fm.removeClass('assets-loading');

				if (textStatus == 'success')
				{
					if (data.success)
					{
                        subfolderName = data.folder_name;
						var subfolderDepth = this.depth + 1,
							padding = 20 + (18 * subfolderDepth),
							$li = $('<li class="assets-fm-folder">'
								  +   '<a data-id="' + data.folder_id + '" style="padding-left: '+padding+'px;">'
								  +     '<span class="assets-fm-label">' + subfolderName + '</span>'
								  +   '</a>'
								  + '</li>'),
							subfolder = new Assets.FileManagerFolder(this.fm, $li[0], subfolderDepth, this);

						this.addSubfolder(subfolder);

						subfolder.onShow();
					}

					if (data.error)
					{
						alert(data.error);
					}
				}
			}, this), 'json')
		}
	},

	/**
	 * Delete
	 */
	_delete: function()
	{
		if (confirm(Assets.parseTag(Assets.lang.confirm_delete_folder, 'folder', this.folderName)))
		{
			var data = {
				ACT:  Assets.actions.delete_folder,
                folder_id: this.$a.attr('data-id')
			};

			this.fm.$fm.addClass('assets-loading');

			$.post(Assets.siteUrl, data, $.proxy(function(data, textStatus)
			{
				this.fm.$fm.removeClass('assets-loading');

				if (textStatus == 'success')
				{
					if (data.success)
					{
						this.onDelete(true);

						// refresh the files view
						this.fm.updateFiles();
					}

					if (data.error)
					{
						alert(data.error);
					}
				}
			}, this), 'json');
		}
	},

    /**
     * Returns true if tested folder is a parent folder of this one.
     *
     * @param folderId
     * @return {Boolean}
     */
    isParent: function (folderId)
    {
        var current = this;
        while (typeof current.parent != "undefined" && current.parent != null)
        {
            if (current.id == folderId)
            {
                return true;
            }
            current = current.parent;
        }

        return false;
    }
},
{
	folderSort: function (a, b) {
        a = a.name.toLowerCase();
        b = b.name.toLowerCase();
        return a < b ? -1 : (a > b ? 1 : 0);
    }
});


/**
 * List View
 */
Assets.ListView = Garnish.Base.extend({

	/**
	 * Constructor
	 */
	init: function($container, settings)
	{
		this.$container = $container;
		this.settings = (settings || {});

		this.$table = $('> table', this.$container);
		this.$ths = $('> thead > tr > th', this.$table);
		this.$tbody = $('> tbody', this.$table);
		this.$tds = $('> tbody > tr:first > td', this.$table);
		this.$items;

		this.scrollbarWidth;
		this.scrollLeft = 0;

		this.orderby = this.settings.orderby;
		this.sort = this.settings.sort;

		this._findItems();

		// -------------------------------------------
		//  Column Sorting
		// -------------------------------------------

		if (typeof this.settings.onSortChange == 'function')
		{
			this.addListener(this.$ths, 'click', function(event)
			{
				var orderby = $(event.currentTarget).attr('data-orderby');

				if (orderby != this.orderby)
				{
					// ordering by something new
					this.orderby = orderby;
					this.sort = 'asc';
				}
				else
				{
					// just reverse the sort
					this.sort = (this.sort == 'asc' ? 'desc' : 'asc');
				}

				this.settings.onSortChange(this.orderby, this.sort);
			});
		}
	},

	/**
	 * Find Items
	 */
	_findItems: function(second)
	{
		this.$items = $('> tr', this.$tbody);
	},

	// -------------------------------------------
	//  Public methods
	// -------------------------------------------

	/**
	 * Get Items
	 */
	getItems: function()
	{
		return this.$items;
	},

	/**
	 * Add Items
	 */
	addItems: function($add)
	{
		this.$tbody.append($add);
		this._findItems();
	},

	/**
	 * Remove Items
	 */
	removeItems: function($remove)
	{
		$remove.remove();
		this._findItems();
	},

	/**
	 * Reset Items
	 */
	reset: function()
	{
		this._findItems();
	},

	/**
	 * Get Container
	 */
	getContainer: function()
	{
		return this.$tbody;
	},

	/**
	 * Set Drag Wrapper
	 */
	getDragHelper: function($file)
	{
		var $container = $('<div class="assets-listview assets-lv-drag" />'),
			$table = $('<table cellpadding="0" cellspacing="0" border="0" />').appendTo($container),
			$tbody = $('<tbody />').appendTo($table);

		$table.width(this.$table.width());
		$tbody.append($file);

		return $container;
	},

	/**
	 * Get Drag Caboose
	 */
	getDragCaboose: function()
	{
		return $('<tr class="assets-lv-file assets-lv-dragcaboose" />');
	},

	/**
	 * Get Drag Insertion Placeholder
	 */
	getDragInsertion: function()
	{
		return $('<tr class="assts-lv-file assets-lv-draginsertion"><td colspan="'+this.$ths.length+'">&nbsp;</td></tr>');
	}
});


/**
 * Properties
 */
Assets.Properties = Garnish.Base.extend({

	/**
	 * Constructor
	 */
	init: function($file)
	{
		this.$file = $file;
		this.path = $file.attr('data-id');

		this.saveEnabled = false;

		// -------------------------------------------
		//  Only one active HUD at a time
		// -------------------------------------------

		// is there already an active props HUD?
		if (Assets.Properties.active)
		{
			Assets.Properties.active.close();
		}

		// register this one
		Assets.Properties.active = this;

		// -------------------------------------------
		//  Load the contents
		// -------------------------------------------

		// get the next request ID
		Assets.Properties.requestId++;

		var data = {
			ACT:       Assets.actions.get_props,
			requestId: Assets.Properties.requestId,
			file_id:   this.path
		};

		// run the ajax post request
		$.post(Assets.siteUrl, data, $.proxy(this, '_init'), 'json');
	},

	/**
	 * Initialize
	 */
	_init: function(data, textStatus)
	{
		// ignore if this is a bad response
		if (textStatus != 'success' || data.requestId != Assets.Properties.requestId) return;

		// -------------------------------------------
		//  Set up the HUD
		// -------------------------------------------

		this.hud = new Garnish.HUD(this.$file, data.html, {
			hudClass:       'assets-hud assets-props',
			tipClass:       'assets-tip',
			bodyClass:      'assets-contents',
			triggerSpacing: 5,
			windowSpacing:  10,
			tipWidth:       30
		});

		this.$form = $('> .assets-filedata', this.hud.$body);

		this.$saveBtn = $('.assets-submit', this.$form);
		this.$cancelBtn = $('.assets-cancel', this.$form);

		this.$cancelBtn.click($.proxy(this, 'close'));
		this.$form.submit($.proxy(this, 'submit'));

		// -------------------------------------------
		//  Get the meta rows
		// -------------------------------------------

		// get the metadata rows
		this.$trs = $('> table > tbody > tr:not(.assets-fileinfo):not(.assets-spacer)', this.$form);

		// mark the odd rows (not sure why we need to use :even here instead of :odd)
		this.$trs.filter(':even').addClass('assets-odd');

		// get all the actual form inputs
		this.$inputs = $('input,textarea,select', this.$trs);

		// treat the entire TR like a <label>
		this.$trs.click($.proxy(this, '_onRowClick'));

		// -------------------------------------------
		//  Enable Save button when any inputs have changed
		// -------------------------------------------

		// get the initial values
		for (var i = 0; i < this.$inputs.length; i++)
		{
			var input = this.$inputs[i];
			$.data(input, 'initialVal', $(input).val());
		};

		// check their state each time an input has changed
		this.addListener(this.$inputs, 'keydown,keypress,change', '_onInputChange');

		// -------------------------------------------
		//  Initialize text fields
		// -------------------------------------------

		var $textareas = $('textarea', this.$trs);

		for (var i = 0; i < $textareas.length; i++)
		{
			var $textarea = $($textareas[i]);

			new Assets.PropertiesTextField($textarea);

			// submit on return
			$textarea.keydown($.proxy(this, '_onTextKeydown'));
		};

		// -------------------------------------------
		//  Initialize date fields
		// -------------------------------------------

		var date = new Date(),
			hours = date.getHours(),
			minutes = date.getMinutes();

		if (minutes < 10) minutes = '0'+minutes;

		if (hours >= 12)
		{
			hours = hours - 12;
			var meridiem = ' PM';
		}
		else
		{
			var meridiem = ' AM';
		}

		var time = " \'"+hours+':'+minutes+meridiem+"\'";

		var $dates = this.$inputs.filter('[data-type=date]');

		for (var i = 0; i < $dates.length; i++)
		{
			var $input = $($dates[i]);
			$input.datepicker({
                constrainInput: false,
				dateFormat: $.datepicker.W3C + time,
				defaultDate: new Date(parseInt($input.attr('data-default-date')))
			});
		}

		// Prevent the datepicker from closing the HUD
		this.addListener($('#ui-datepicker-div'), 'click', function(ev)
		{
			ev.stopPropagation();
		});
	},

	/**
	 * On Row Click
	 */
	_onRowClick: function(ev)
	{
		// ignore if they clicked on an actual input
		if (ev.target.nodeName == 'INPUT' || ev.target.nodeName == 'TEXTAREA' || ev.target.nodeName == 'SELECT') return;

		var $firstInput = $('input,textarea,select', ev.currentTarget).first();
		$firstInput.focus();
	},

	/**
	 * On Input Change
	 */
	_onInputChange: function(ev)
	{
		setTimeout($.proxy(this, '_checkAllInputs'), 0);
	},

	/**
	 * Check All Inputs
	 */
	_checkAllInputs: function()
	{
		this.saveEnabled = false;

		for (var i = 0; i < this.$inputs.length; i++)
		{
			var input = this.$inputs[i];
			if (this._inputChanged(input))
			{
				this.saveEnabled = true;
				break;
			}
		};

		if (this.saveEnabled)
		{
			this.$saveBtn.removeClass('assets-disabled');
		}
		else
		{
			this.$saveBtn.addClass('assets-disabled');
		}
	},

	/**
	 * Input Changed?
	 */
	_inputChanged: function(input)
	{
		return ($(input).val() != $.data(input, 'initialVal'))
	},

	/**
	 * On Text Keydown
	 */
	_onTextKeydown: function(ev)
	{
		var $textarea = $(ev.currentTarget);
		if (ev.keyCode == Garnish.RETURN_KEY && (! $textarea.attr('data-multiline') || ev.altKey))
		{
			ev.preventDefault();
			setTimeout($.proxy(this, 'submit'), 1);
		}
	},

	/**
	 * Submit
	 */
	submit: function(ev)
	{
		if (ev)
		{
			ev.preventDefault();
		}

		// ignore if the save button is disabled
		if (! this.saveEnabled) return;

		var saveData = {};

		for (var i = 0; i < this.$inputs.length; i++)
		{
			var input = this.$inputs[i];
			if (this._inputChanged(input))
			{
				saveData['data['+$(input).attr('name')+']'] = $(input).val();
			}
		};

		if (saveData)
		{
			var data = $.extend({
				ACT:  Assets.actions.save_props,
				file_id: this.path
			}, saveData);

			$.post(Assets.siteUrl, data);

			// close the HUD
			this.close();
		}
	},

	/**
	 * Close
	 */
	close: function()
	{
		this.hud.$hud.fadeOut('fast');
	}
},
{
	requestId: 0
});


/**
 * Property Text
 */
Assets.PropertiesTextField = Garnish.Base.extend({

	/**
	 * Constructor
	 */
	init: function($input)
	{
		this.$input = $input;

		// ignore if not a textarea
		if (this.$input[0].nodeName != 'TEXTAREA') return;

		this.settings = {
			maxl:      (parseInt(this.$input.attr('data-maxl')) || false),
			multiline: (!! this.$input.attr('data-multiline') || false)
		};

		this.val = this.$input.val();
		this.clicked = false,
		this.focussed = false;

		// -------------------------------------------
		//  Keep textarea height updated to match contents
		// -------------------------------------------

		// create the stage
		this.$stage = $('<stage />').insertAfter(this.$input);
		this.textHeight;

		// replicate the textarea's text styles
		this.$stage.css({
			position: 'absolute',
			top: -9999,
			left: -9999,
			width: this.$input.width(),
			lineHeight: this.$input.css('lineHeight'),
			fontSize: this.$input.css('fontSize'),
			fontFamily: this.$input.css('fontFamily'),
			fontWeight: this.$input.css('fontWeight'),
			letterSpacing: this.$input.css('letterSpacing'),
			wordWrap: 'break-word'
		});

		this._updateInputHeight();

		// -------------------------------------------
		//  Bind events
		// -------------------------------------------

		this.$input.mousedown($.proxy(this, '_onInputMousedown'));
		this.$input.focus($.proxy(this, '_onInputFocus'));
		this.$input.blur($.proxy(this, '_onInputBlur'));
		this.$input.change($.proxy(this, '_checkInputVal'));

		if (! this.settings.multiline || this.settings.maxl)
		{
			this.$input.keydown($.proxy(this, '_onInputKeydown'));
		}
	},

	/**
	 * On Input Mousedown
	 */
	_onInputMousedown: function(){
		this.clicked = true;
	},

	/**
	 * On Input Focus
	 */
	_onInputFocus: function(){
		this.focussed = true;

		// make the textarea behave like a text input
		setTimeout($.proxy(this, '_fakeTextInputOnFocus'), 0);

		// start checking the input value
		this.interval = setInterval($.proxy(this, '_checkInputVal'), 100);
	},

	/**
	 * Fake Text Input On Focus
	 */
	_fakeTextInputOnFocus: function(){
		if (! this.clicked)
		{
			// focus was *given* to the textarea, so we'll do our best
			// to make it seem like the entire $td is a normal text input

			this.val = this.$input.val();

			if (this.$input[0].setSelectionRange)
			{
				var length = this.val.length * 2;
				this.$input[0].setSelectionRange(0, length);
			}
			else
			{
				// browser doesn't support setSelectionRange so try refreshing
				// the value as a way to place the cursor at the end
				this.$input.val(this.val);
			}
		}
		else
		{
			this.clicked = false;
		}
	},

	/**
	 * On Input Blur
	 */
	_onInputBlur: function(){
		this.focussed = false;

		clearInterval(this.interval);
		this._checkInputVal();
	},

	/**
	 * On Input Keydown
	 */
	_onInputKeydown: function(ev){
		if (! ev.metaKey && ! ev.ctrlKey
				&& $.inArray(ev.keyCode, Assets.PropertiesTextField.traversingKeys) == -1 && (
				(! this.settings.multiline && ev.keyCode == 13)
				|| (this.settings.maxl && this.$input.val().length >= this.settings.maxl)))
		{
			ev.preventDefault();
		}
	},

	/**
	 * Check Input Value
	 */
	_checkInputVal: function(){
		// has the input value changed?
		if (this.val !== (this.val = this.$input.val()))
		{
			this._updateInputHeight();
		}
	},

	/**
	 * Update Input Height
	 */
	_updateInputHeight: function()
	{
		if (! this.val)
		{
			var html = '&nbsp;';
		}
		else
		{
			// html entities
			var html = this.val.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/[\n\r]$/g, '<br/>&nbsp;').replace(/[\n\r]/g, '<br/>');
		}

		if (this.focussed) html += 'm';
		this.$stage.html(html);

		// has the height changed?
		if ((this.textHeight !== (this.textHeight = this.$stage.height())) && this.textHeight)
		{
			// update the textarea height
			this.$input.height(this.textHeight);
		}
	}
},
{
	traversingKeys: [8 /* delete */ , 37,38,39,40 /* (arrows) */]
});


/**
 * Sheet
 */
Assets.Sheet = Garnish.Base.extend({

	loaded: false,

	/**
	 * Constructor
	 */
	init: function(settings)
	{
		this.setSettings(settings, Assets.Sheet.defaults);
	},

	/**
	 * Load
	 */
	_load: function()
	{
		var postData = {
			ACT:     Assets.actions.build_sheet,
			site_id: Assets.siteId,
			multi:   this.settings.multiSelect ? 'y' : 'n'
		};

		if (this.settings.filedirs == 'all')
		{
			postData.filedirs = 'all';
		}
		else
		{
			for (var i = 0; i < this.settings.filedirs.length; i++)
			{
				postData['filedirs['+i+']'] = this.settings.filedirs[i];
			}
		}

		this.$sheet.load(Assets.siteUrl, postData, $.proxy(function(responseText, textStatus, XMLHttpRequest)
		{
			// find dom nodes
			var $field = $(' .assets-fm', this.$sheet);

			this.fileManager = new Assets.FileManager($field, {
				mode:                'select',
				multiSelect:         this.settings.multiSelect,
				onSelect:            $.proxy(this, '_onSelect'),
				kinds:               this.settings.kinds,
				disabledFiles:       this.settings.disabledFiles,
                namespace:           this.settings.namespace,
                onSelectionChange:   $.proxy(this, '_selectionChange')
			});

			// now show it
			this.loaded = true;
			this._updateFiles();

            $(' .assets-buttons .assets-cancel', this.$sheet).click($.proxy(function ()
            {
                this.hide();
            }, this));

            // Trigger the onSelect behaviour if it is not disabled only.
            $(' .assets-buttons .assets-add', this.$sheet).click($.proxy(function (event)
            {
                if ($(event.target).hasClass('assets-disabled'))
                {
                    return;
                }

                this._onSelect();

            }, this));

		}, this));
	},

    /**
     * Check if we need to enabled or disable the "add files" button.
     */
    _selectionChange: function ()
    {
        if (this.fileManager.fileSelect.getTotalSelected() > 0)
        {
            $(' .assets-buttons .assets-add', this.$sheet).removeClass('assets-disabled');
        }
        else
        {
            $(' .assets-buttons .assets-add', this.$sheet).addClass('assets-disabled');
        }
    },

	/**
	 * On Select
	 */
	_onSelect: function()
	{
		// ignore if nothing is selected
		if (! this.fileManager.fileSelect.getTotalSelected())
        {
            return;
        }

		var $files = this.fileManager.fileSelect.getSelectedItems(),
			files = [];

		for (var i = 0; i < $files.length; i++)
		{
			var $file = $($files[i]);
			files.push({
				id:  parseInt($file.attr('data-id')),
				url: $file.attr('data-file-url')
			});
		}

		this.settings.onSelect(files);

		this.hide();
	},

	/**
	 * Show
	 */
	show: function(settings)
	{
		this.setSettings(settings);

		// showing for the first time?
		if (! this.$sheet)
		{
			this.$shade = $('<div class="assets-shade" />').appendTo(document.body).hide();
			this.$sheet = $('<div class="assets-sheet assets-no-outline" tabindex="0" />').appendTo(document.body).hide();

			this.addListener(this.$shade, 'click', 'hide');
			this.addListener(this.$sheet, 'keydown', 'onKeyDown');
		}

		$(document.body).addClass('assets-noscroll');

		this.$shade.fadeIn('fast');
		this.$sheet.show().animate({ top: 0 }, 'fast', $.proxy(this, '_onAfterShow'));
	},

	_onAfterShow: function()
	{
		this.$sheet.focus();

		if (!this.loaded)
		{
			this._load();
		}
		else
		{
			this._updateFiles();
		}
	},

	_updateFiles: function()
	{
		// update the list of disabled files
		var updateFiles = false;

		if (this.fileManager.settings.disabledFiles.length != this.settings.disabledFiles.length)
		{
			updateFiles = true;
		}
		else
		{
			for (var i = 0; i < this.fileManager.settings.disabledFiles.length; i++)
			{
				if (this.fileManager.settings.disabledFiles[i] != this.settings.disabledFiles[i])
				{
					updateFiles = true;
					break;
				}
			}
		}

		if (updateFiles)
		{
			this.fileManager.settings.disabledFiles = this.settings.disabledFiles;
			this.fileManager.updateFiles();
		}
	},

	/**
	 * Hide
	 */
	hide: function()
	{
		this.$shade.fadeOut();
		this.$sheet.animate({ top: -358 }, 300, $.proxy(function()
		{
			this.$shade.hide();
			this.$sheet.hide();
		}, this));

		$(document.body).removeClass('assets-noscroll');
	},

	/**
	 * On Key Down
	 */
	onKeyDown: function(event)
	{
		switch (event.keyCode)
		{
			case 27: // esc
			{
				this.hide();
				break;
			}

			case 13: // return
			{
				this._onSelect();
				break;
			}
		}
	}
},
{
	defaults: {
		multiSelect:   false,
		filedirs:      'all',
		kinds:         'any',
		onSelect:      function(){},
		disabledFiles: []
	}
});


/**
 * Thumb View
 */
Assets.ThumbView = Garnish.Base.extend({

	/**
	 * Constructor
	 */
	init: function($container)
	{
		this.$container = $container;
		this.$ul = $('> ul', this.$container);

		this._findItems();
	},

	/**
	 * Find Items
	 */
	_findItems: function(second)
	{
		this.$items = $('> li', this.$ul);
	},

	// -------------------------------------------
	//  Public methods
	// -------------------------------------------

	/**
	 * Get Items
	 */
	getItems: function()
	{
		return this.$items;
	},

	/**
	 * Add Items
	 */
	addItems: function($add)
	{
		this.$ul.append($add);
		this._findItems()
	},

	/**
	 * Remove Items
	 */
	removeItems: function($remove)
	{
		$remove.remove();
		this._findItems();
	},

	/**
	 * Reset Items
	 */
	reset: function()
	{
		this._findItems();
	},

	/**
	 * Destroy
	 */
	destroy: function()
	{
		// delete this ThumbView instance
		delete obj;
	},

	/**
	 * Get Container
	 */
	getContainer: function()
	{
		return this.$ul;
	},

	/**
	 * Set Drag Wrapper
	 */
	getDragHelper: function($file)
	{
		return $('<ul class="assets-tv-drag" />').append($file.removeClass('assets-selected'));
	},

	/**
	 * Get Drag Caboose
	 */
	getDragCaboose: function()
	{
		return $('<li class="assets-tv-file assets-tv-dragcaboose" />');
	},

	/**
	 * Get Drag Insertion Placeholder
	 */
	getDragInsertion: function($draggee)
	{
		return $draggee.first().clone().show().css({ 'margin-right': 0, visibility: 'visible' }).addClass('assets-draginsertion');
	}

});


/*

 http://github.com/valums/file-uploader

 Multiple file upload component with progress-bar, drag-and-drop.

 Copyright (C) 2011 by Andris Valums

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.

 */

(function(){

//
// Helper functions
//

    var qq = qq || {};

    /**
     * Adds all missing properties from second obj to first obj
     */

    qq.extend = function(first, second){
        for (var prop in second){
            first[prop] = second[prop];
        }
    };

    /**
     * Searches for a given element in the array, returns -1 if it is not present.
     * @param {Number} [from] The index at which to begin the search
     */
    qq.indexOf = function(arr, elt, from){
        if (arr.indexOf) return arr.indexOf(elt, from);

        from = from || 0;
        var len = arr.length;

        if (from < 0) from += len;

        for (; from < len; from++){

            if (from in arr && arr[from] === elt){

                return from;
            }
        }

        return -1;

    };

    qq.getUniqueId = (function(){
        var id = 0;
        return function(){ return id++; };
    })();

//
// Events

    qq.attach = function(element, type, fn){
        if (element.addEventListener){
            element.addEventListener(type, fn, false);
        } else if (element.attachEvent){
            element.attachEvent('on' + type, fn);
        }
    };
    qq.detach = function(element, type, fn){
        if (element.removeEventListener){
            element.removeEventListener(type, fn, false);
        } else if (element.attachEvent){
            element.detachEvent('on' + type, fn);
        }
    };

    qq.preventDefault = function(e){
        if (e.preventDefault){
            e.preventDefault();
        } else{
            e.returnValue = false;
        }
    };

//
// Node manipulations

    /**
     * Insert node a before node b.
     */
    qq.insertBefore = function(a, b){
        b.parentNode.insertBefore(a, b);
    };
    qq.remove = function(element){
        element.parentNode.removeChild(element);
    };

    qq.contains = function(parent, descendant){

        // compareposition returns false in this case
        if (parent == descendant) return true;

        if (parent.contains){
            return parent.contains(descendant);
        } else {
            return !!(descendant.compareDocumentPosition(parent) & 8);
        }
    };

    /**
     * Creates and returns element from html string
     * Uses innerHTML to create an element
     */
    qq.toElement = (function(){
        var div = document.createElement('div');
        return function(html){
            div.innerHTML = html;
            var element = div.firstChild;
            div.removeChild(element);
            return element;
        };
    })();

//
// Node properties and attributes

    /**
     * Sets styles for an element.
     * Fixes opacity in IE6-8.
     */
    qq.css = function(element, styles){
        if (styles.opacity != null){
            if (typeof element.style.opacity != 'string' && typeof(element.filters) != 'undefined'){
                styles.filter = 'alpha(opacity=' + Math.round(100 * styles.opacity) + ')';
            }
        }
        qq.extend(element.style, styles);
    };
    qq.hasClass = function(element, name){
        var re = new RegExp('(^| )' + name + '( |$)');
        return re.test(element.className);
    };
    qq.addClass = function(element, name){
        if (!qq.hasClass(element, name)){
            element.className += ' ' + name;
        }
    };
    qq.removeClass = function(element, name){
        var re = new RegExp('(^| )' + name + '( |$)');
        element.className = element.className.replace(re, ' ').replace(/^\s+|\s+$/g, "");
    };
    qq.setText = function(element, text){
        element.innerText = text;
        element.textContent = text;
    };

//
// Selecting elements

    qq.children = function(element){
        var children = [],
            child = element.firstChild;

        while (child){
            if (child.nodeType == 1){
                children.push(child);
            }
            child = child.nextSibling;
        }

        return children;
    };

    qq.getByClass = function(element, className){
        if (element.querySelectorAll){
            return element.querySelectorAll('.' + className);
        }

        var result = [];
        var candidates = element.getElementsByTagName("*");
        var len = candidates.length;

        for (var i = 0; i < len; i++){
            if (qq.hasClass(candidates[i], className)){
                result.push(candidates[i]);
            }
        }
        return result;
    };

    /**
     * obj2url() takes a json-object as argument and generates
     * a querystring. pretty much like jQuery.param()
     *

     * how to use:
     *
     *    `qq.obj2url({a:'b',c:'d'},'http://any.url/upload?otherParam=value');`
     *
     * will result in:
     *
     *    `http://any.url/upload?otherParam=value&a=b&c=d`
     *
     * @param  Object JSON-Object
     * @param  String current querystring-part
     * @return String encoded querystring
     */
    qq.obj2url = function(obj, temp, prefixDone){
        var uristrings = [],
            prefix = '&',
            add = function(nextObj, i){
                var nextTemp = temp

                    ? (/\[\]$/.test(temp)) // prevent double-encoding
                    ? temp
                    : temp+'['+i+']'
                    : i;
                if ((nextTemp != 'undefined') && (i != 'undefined')) {

                    uristrings.push(
                        (typeof nextObj === 'object')

                            ? qq.obj2url(nextObj, nextTemp, true)
                            : (Object.prototype.toString.call(nextObj) === '[object Function]')
                            ? encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj())
                            : encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj)

                    );
                }
            };

        if (!prefixDone && temp) {
            prefix = (/\?/.test(temp)) ? (/\?$/.test(temp)) ? '' : '&' : '?';
            uristrings.push(temp);
            uristrings.push(qq.obj2url(obj));
        } else if ((Object.prototype.toString.call(obj) === '[object Array]') && (typeof obj != 'undefined') ) {
            // we wont use a for-in-loop on an array (performance)
            for (var i = 0, len = obj.length; i < len; ++i){
                add(obj[i], i);
            }
        } else if ((typeof obj != 'undefined') && (obj !== null) && (typeof obj === "object")){
            // for anything else but a scalar, we will use for-in-loop
            for (var i in obj){
                add(obj[i], i);
            }
        } else {
            uristrings.push(encodeURIComponent(temp) + '=' + encodeURIComponent(obj));
        }

        return uristrings.join(prefix)
            .replace(/^&/, '')
            .replace(/%20/g, '+');

    };

//
//
// Uploader Classes
//
//

    var qq = qq || {};

    /**
     * Creates upload button, validates upload, but doesn't create file list or dd.

     */
    qq.FileUploaderBasic = function(o){
        this._options = {
            // set to true to see the server response
            debug: false,
            action: '/server/upload',
            params: {},
            button: null,
            multiple: true,
            maxConnections: 3,
            // validation

            allowedExtensions: [],

            sizeLimit: 0,

            minSizeLimit: 0,

            // events
            // return false to cancel submit
            onSubmit: function(id, fileName){},
            onProgress: function(id, fileName, loaded, total){},
            onComplete: function(id, fileName, responseJSON){},
            onCancel: function(id, fileName){},
            // messages

            messages: {
                typeError: "{file} has invalid extension. Only {extensions} are allowed.",
                sizeError: "{file} is too large, maximum file size is {sizeLimit}.",
                minSizeError: "{file} is too small, minimum file size is {minSizeLimit}.",
                emptyError: "{file} is empty, please select files again without it.",
                onLeave: "The files are being uploaded, if you leave now the upload will be cancelled."

            },
            showMessage: function(message){
                alert(message);
            }

        };
        qq.extend(this._options, o);

        // number of files being uploaded
        this._filesInProgress = 0;
        this._handler = this._createUploadHandler();

        if (this._options.button){

            this._button = this._createUploadButton(this._options.button);
        }

        this._preventLeaveInProgress();

    };

    qq.FileUploaderBasic.prototype = {
        setParams: function(params){
            this._options.params = params;
        },
        getInProgress: function(){
            return this._filesInProgress;

        },
        _createUploadButton: function(element){
            var self = this;

            return new qq.UploadButton({
                element: element,
                multiple: this._options.multiple && qq.UploadHandlerXhr.isSupported(),
                onChange: function(input){
                    self._onInputChange(input);
                }

            });

        },

        _createUploadHandler: function(){
            var self = this,
                handlerClass;

            if(qq.UploadHandlerXhr.isSupported()){

                handlerClass = 'UploadHandlerXhr';

            } else {
                handlerClass = 'UploadHandlerForm';
            }

            var handler = new qq[handlerClass]({
                debug: this._options.debug,
                action: this._options.action,

                maxConnections: this._options.maxConnections,

                onProgress: function(id, fileName, loaded, total){

                    self._onProgress(id, fileName, loaded, total);
                    self._options.onProgress(id, fileName, loaded, total);

                },

                onComplete: function(id, fileName, result){
                    self._onComplete(id, fileName, result);
                    self._options.onComplete(id, fileName, result);
                },
                onCancel: function(id, fileName){
                    self._onCancel(id, fileName);
                    self._options.onCancel(id, fileName);
                }
            });

            return handler;
        },

        _preventLeaveInProgress: function(){
            var self = this;

            qq.attach(window, 'beforeunload', function(e){
                if (!self._filesInProgress){return;}

                var e = e || window.event;
                // for ie, ff
                e.returnValue = self._options.messages.onLeave;
                // for webkit
                return self._options.messages.onLeave;

            });

        },

        _onSubmit: function(id, fileName){
            this._filesInProgress++;

        },
        _onProgress: function(id, fileName, loaded, total){

        },
        _onComplete: function(id, fileName, result){
            this._filesInProgress--;

            if (result.error){
                this._options.showMessage(result.error);
            }

        },
        _onCancel: function(id, fileName){
            this._filesInProgress--;

        },
        _onInputChange: function(input){
            if (this._handler instanceof qq.UploadHandlerXhr){

                this._uploadFileList(input.files);

            } else {

                if (this._validateFile(input)){

                    this._uploadFile(input);

                }

            }

            this._button.reset();

        },

        _uploadFileList: function(files){
            for (var i=0; i<files.length; i++){
                if ( !this._validateFile(files[i])){
                    return;
                }

            }

            for (var i=0; i<files.length; i++){
                this._uploadFile(files[i]);

            }

        },

        _uploadFile: function(fileContainer){

            var id = this._handler.add(fileContainer);
            var fileName = this._handler.getName(id);

            if (this._options.onSubmit(id, fileName) !== false){
                this._onSubmit(id, fileName);
                this._handler.upload(id, this._options.params);
            }
        },

        _validateFile: function(file){
            var name, size;

            if (file.value){
                // it is a file input

                // get input value and remove path to normalize
                name = file.value.replace(/.*(\/|\\)/, "");
            } else {
                // fix missing properties in Safari
                name = file.fileName != null ? file.fileName : file.name;
                size = file.fileSize != null ? file.fileSize : file.size;
            }

            if (! this._isAllowedExtension(name)){

                this._error('typeError', name);
                return false;

            } else if (size === 0){

                this._error('emptyError', name);
                return false;

            } else if (size && this._options.sizeLimit && size > this._options.sizeLimit){

                this._error('sizeError', name);
                return false;

            } else if (size && size < this._options.minSizeLimit){
                this._error('minSizeError', name);
                return false;

            }

            return true;

        },
        _error: function(code, fileName){
            var message = this._options.messages[code];

            function r(name, replacement){ message = message.replace(name, replacement); }

            r('{file}', this._formatFileName(fileName));

            r('{extensions}', this._options.allowedExtensions.join(', '));
            r('{sizeLimit}', this._formatSize(this._options.sizeLimit));
            r('{minSizeLimit}', this._formatSize(this._options.minSizeLimit));

            this._options.showMessage(message);

        },
        _formatFileName: function(name){
            if (name.length > 33){
                name = name.slice(0, 19) + '...' + name.slice(-13);

            }
            return name;
        },
        _isAllowedExtension: function(fileName){
            var ext = (-1 !== fileName.indexOf('.')) ? fileName.replace(/.*[.]/, '').toLowerCase() : '';
            var allowed = this._options.allowedExtensions;

            if (!allowed.length){return true;}

            for (var i=0; i<allowed.length; i++){
                if (allowed[i].toLowerCase() == ext){ return true;}

            }

            return false;
        },

        _formatSize: function(bytes){
            var i = -1;

            do {
                bytes = bytes / 1024;
                i++;

            } while (bytes > 99);

            return Math.max(bytes, 0.1).toFixed(1) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];

        }
    };

    /**
     * Class that creates upload widget with drag-and-drop and file list
     * @inherits qq.FileUploaderBasic
     */
    qq.FileUploader = function(o){
        // call parent constructor
        qq.FileUploaderBasic.apply(this, arguments);

        // additional options

        qq.extend(this._options, {
            element: null,
            // if set, will be used instead of qq-upload-list in template
            listElement: null,

            template: '<div class="qq-uploader">' +

                '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' +
                '<div class="qq-upload-button">Upload a file</div>' +
                '<ul class="qq-upload-list"></ul>' +

                '</div>',

            // template for one item in file list
            fileTemplate: '<li>' +
                '<span class="qq-upload-file"></span>' +
                '<span class="qq-upload-spinner"></span>' +
                '<span class="qq-upload-size"></span>' +
                '<a class="qq-upload-cancel" href="#">Cancel</a>' +
                '<span class="qq-upload-failed-text">Failed</span>' +
                '</li>',

            classes: {
                // used to get elements from templates
                button: 'qq-upload-button',
                drop: 'qq-upload-drop-area',
                dropActive: 'qq-upload-drop-area-active',
                list: 'qq-upload-list',

                file: 'qq-upload-file',
                spinner: 'qq-upload-spinner',
                size: 'qq-upload-size',
                cancel: 'qq-upload-cancel',

                // added to list item when upload completes
                // used in css to hide progress spinner
                success: 'qq-upload-success',
                fail: 'qq-upload-fail'
            }
        });
        // overwrite options with user supplied

        qq.extend(this._options, o);

        this._element = this._options.element;
        this._element.innerHTML = this._options.template;

        this._listElement = this._options.listElement || this._find(this._element, 'list');

        this._classes = this._options.classes;

        this._button = this._createUploadButton(this._find(this._element, 'button'));

        this._bindCancelEvent();
        this._setupDragDrop();
    };

// inherit from Basic Uploader
    qq.extend(qq.FileUploader.prototype, qq.FileUploaderBasic.prototype);

    qq.extend(qq.FileUploader.prototype, {
        /**
         * Gets one of the elements listed in this._options.classes
         **/
        _find: function(parent, type){

            var element = qq.getByClass(parent, this._options.classes[type])[0];

            if (!element){
                throw new Error('element not found: ' + type);
            }

            return element;
        },
        _setupDragDrop: function(){
            var self = this,
                dropArea = this._find(this._element, 'drop');

            var dz = new qq.UploadDropZone({
                element: dropArea,
                onEnter: function(e){
                    qq.addClass(dropArea, self._classes.dropActive);
                    e.stopPropagation();
                },
                onLeave: function(e){
                    e.stopPropagation();
                },
                onLeaveNotDescendants: function(e){
                    qq.removeClass(dropArea, self._classes.dropActive);

                },
                onDrop: function(e){
                    dropArea.style.display = 'none';
                    qq.removeClass(dropArea, self._classes.dropActive);
                    self._uploadFileList(e.dataTransfer.files);

                }
            });

            dropArea.style.display = 'none';

            qq.attach(document, 'dragenter', function(e){

                if (!dz._isValidFileDrag(e)) return;

                dropArea.style.display = 'block';

            });

            qq.attach(document, 'dragleave', function(e){
                if (!dz._isValidFileDrag(e)) return;

                var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);
                // only fire when leaving document out
                if ( ! relatedTarget || relatedTarget.nodeName == "HTML"){

                    dropArea.style.display = 'none';

                }
            });

        },
        _onSubmit: function(id, fileName){
            qq.FileUploaderBasic.prototype._onSubmit.apply(this, arguments);
            this._addToList(id, fileName);

        },
        _onProgress: function(id, fileName, loaded, total){
            qq.FileUploaderBasic.prototype._onProgress.apply(this, arguments);

            var item = this._getItemByFileId(id);
            var size = this._find(item, 'size');
            size.style.display = 'inline';

            var text;

            if (loaded != total){
                text = Math.round(loaded / total * 100) + '% from ' + this._formatSize(total);
            } else {

                text = this._formatSize(total);
            }

            qq.setText(size, text);

        },
        _onComplete: function(id, fileName, result){
            qq.FileUploaderBasic.prototype._onComplete.apply(this, arguments);

            // mark completed
            var item = this._getItemByFileId(id);

            qq.remove(this._find(item, 'cancel'));
            qq.remove(this._find(item, 'spinner'));

            if (result.success){
                qq.addClass(item, this._classes.success);

            } else {
                qq.addClass(item, this._classes.fail);
            }

        },
        _addToList: function(id, fileName){
            var item = qq.toElement(this._options.fileTemplate);

            item.qqFileId = id;

            var fileElement = this._find(item, 'file');

            qq.setText(fileElement, this._formatFileName(fileName));
            this._find(item, 'size').style.display = 'none';

            this._listElement.appendChild(item);
        },
        _getItemByFileId: function(id){
            var item = this._listElement.firstChild;

            // there can't be txt nodes in dynamically created list
            // and we can  use nextSibling
            while (item){

                if (item.qqFileId == id) return item;

                item = item.nextSibling;
            }

        },
        /**
         * delegate click event for cancel link

         **/
        _bindCancelEvent: function(){
            var self = this,
                list = this._listElement;

            qq.attach(list, 'click', function(e){

                e = e || window.event;
                var target = e.target || e.srcElement;

                if (qq.hasClass(target, self._classes.cancel)){

                    qq.preventDefault(e);

                    var item = target.parentNode;
                    self._handler.cancel(item.qqFileId);
                    qq.remove(item);
                }
            });
        }

    });

    qq.UploadDropZone = function(o){
        this._options = {
            element: null,

            onEnter: function(e){},
            onLeave: function(e){},

            // is not fired when leaving element by hovering descendants

            onLeaveNotDescendants: function(e){},

            onDrop: function(e){}

        };
        qq.extend(this._options, o);

        this._element = this._options.element;

        this._disableDropOutside();
        this._attachEvents();

    };

    qq.UploadDropZone.prototype = {
        _disableDropOutside: function(e){
            // run only once for all instances
            if (!qq.UploadDropZone.dropOutsideDisabled ){

                qq.attach(document, 'dragover', function(e){
                    if (e.dataTransfer){
                        e.dataTransfer.dropEffect = 'none';
                        e.preventDefault();

                    }

                });

                qq.UploadDropZone.dropOutsideDisabled = true;

            }

        },
        _attachEvents: function(){
            var self = this;

            qq.attach(self._element, 'dragover', function(e){
                if (!self._isValidFileDrag(e)) return;

                var effect = e.dataTransfer.effectAllowed;
                if (effect == 'move' || effect == 'linkMove'){
                    e.dataTransfer.dropEffect = 'move'; // for FF (only move allowed)

                } else {

                    e.dataTransfer.dropEffect = 'copy'; // for Chrome
                }

                e.stopPropagation();
                e.preventDefault();

            });

            qq.attach(self._element, 'dragenter', function(e){
                if (!self._isValidFileDrag(e)) return;

                self._options.onEnter(e);
            });

            qq.attach(self._element, 'dragleave', function(e){
                if (!self._isValidFileDrag(e)) return;

                self._options.onLeave(e);

                var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);

                // do not fire when moving a mouse over a descendant
                if (qq.contains(this, relatedTarget)) return;

                self._options.onLeaveNotDescendants(e);

            });

            qq.attach(self._element, 'drop', function(e){
                if (!self._isValidFileDrag(e)) return;

                e.preventDefault();
                self._options.onDrop(e);
            });

        },
        _isValidFileDrag: function(e){
            var dt = e.dataTransfer,
            // do not check dt.types.contains in webkit, because it crashes safari 4

                isWebkit = navigator.userAgent.indexOf("AppleWebKit") > -1;

            // dt.effectAllowed is none in Safari 5
            // dt.types.contains check is for firefox

            return dt && dt.effectAllowed != 'none' &&

                (dt.files || (!isWebkit && dt.types.contains && dt.types.contains('Files')));

        }

    };

    qq.UploadButton = function(o){
        this._options = {
            element: null,

            // if set to true adds multiple attribute to file input

            multiple: false,
            // name attribute of file input
            name: 'file',
            onChange: function(input){},
            hoverClass: 'qq-upload-button-hover',
            focusClass: 'qq-upload-button-focus'

        };

        qq.extend(this._options, o);

        this._element = this._options.element;

        // make button suitable container for input
        qq.css(this._element, {
            position: 'relative',
            overflow: 'hidden',
            // Make sure browse button is in the right side
            // in Internet Explorer
            direction: 'ltr'
        });

        this._input = this._createInput();
    };

    qq.UploadButton.prototype = {
        /* returns file input element */

        getInput: function(){
            return this._input;
        },
        /* cleans/recreates the file input */
        reset: function(){
            if (this._input.parentNode){
                qq.remove(this._input);

            }

            qq.removeClass(this._element, this._options.focusClass);
            this._input = this._createInput();
        },

        _createInput: function(){

            var input = document.createElement("input");

            if (this._options.multiple){
                input.setAttribute("multiple", "multiple");
            }

            input.setAttribute("type", "file");
            input.setAttribute("name", this._options.name);

            qq.css(input, {
                position: 'absolute',
                // in Opera only 'browse' button
                // is clickable and it is located at
                // the right side of the input
                right: 0,
                top: 0,
                fontFamily: 'Arial',
                // 4 persons reported this, the max values that worked for them were 243, 236, 236, 118
                fontSize: '118px',
                margin: 0,
                padding: 0,
                cursor: 'pointer',
                opacity: 0
            });

            this._element.appendChild(input);

            var self = this;
            qq.attach(input, 'change', function(){
                self._options.onChange(input);
            });

            qq.attach(input, 'mouseover', function(){
                qq.addClass(self._element, self._options.hoverClass);
            });
            qq.attach(input, 'mouseout', function(){
                qq.removeClass(self._element, self._options.hoverClass);
            });
            qq.attach(input, 'focus', function(){
                qq.addClass(self._element, self._options.focusClass);
            });
            qq.attach(input, 'blur', function(){
                qq.removeClass(self._element, self._options.focusClass);
            });

            // IE and Opera, unfortunately have 2 tab stops on file input
            // which is unacceptable in our case, disable keyboard access
            if (window.attachEvent){
                // it is IE or Opera
                input.setAttribute('tabIndex', "-1");
            }

            return input;

        }

    };

    /**
     * Class for uploading files, uploading itself is handled by child classes
     */
    qq.UploadHandlerAbstract = function(o){
        this._options = {
            debug: false,
            action: '/upload.php',
            // maximum number of concurrent uploads

            maxConnections: 999,
            onProgress: function(id, fileName, loaded, total){},
            onComplete: function(id, fileName, response){},
            onCancel: function(id, fileName){}
        };
        qq.extend(this._options, o);

        this._queue = [];
        // params for files in queue
        this._params = [];
    };
    qq.UploadHandlerAbstract.prototype = {
        log: function(str){
            if (this._options.debug && window.console) console.log('[uploader] ' + str);

        },
        /**
         * Adds file or file input to the queue
         * @returns id
         **/

        add: function(file){},
        /**
         * Sends the file identified by id and additional query params to the server
         */
        upload: function(id, params){
            var len = this._queue.push(id);

            var copy = {};

            qq.extend(copy, params);
            this._params[id] = copy;

            // if too many active uploads, wait...
            if (len <= this._options.maxConnections){

                this._upload(id, this._params[id]);
            }
        },
        /**
         * Cancels file upload by id
         */
        cancel: function(id){
            this._cancel(id);
            this._dequeue(id);
        },
        /**
         * Cancells all uploads
         */
        cancelAll: function(){
            for (var i=0; i<this._queue.length; i++){
                this._cancel(this._queue[i]);
            }
            this._queue = [];
        },
        /**
         * Returns name of the file identified by id
         */
        getName: function(id){},
        /**
         * Returns size of the file identified by id
         */

        getSize: function(id){},
        /**
         * Returns id of files being uploaded or
         * waiting for their turn
         */
        getQueue: function(){
            return this._queue;
        },
        /**
         * Actual upload method
         */
        _upload: function(id){},
        /**
         * Actual cancel method
         */
        _cancel: function(id){},

        /**
         * Removes element from queue, starts upload of next
         */
        _dequeue: function(id){
            var i = qq.indexOf(this._queue, id);
            this._queue.splice(i, 1);

            var max = this._options.maxConnections;

            if (this._queue.length >= max && i < max){
                var nextId = this._queue[max-1];
                this._upload(nextId, this._params[nextId]);
            }
        }

    };

    /**
     * Class for uploading files using form and iframe
     * @inherits qq.UploadHandlerAbstract
     */
    qq.UploadHandlerForm = function(o){
        qq.UploadHandlerAbstract.apply(this, arguments);

        this._inputs = {};
    };
// @inherits qq.UploadHandlerAbstract
    qq.extend(qq.UploadHandlerForm.prototype, qq.UploadHandlerAbstract.prototype);

    qq.extend(qq.UploadHandlerForm.prototype, {
        add: function(fileInput){
            fileInput.setAttribute('name', 'qqfile');
            var id = 'qq-upload-handler-iframe' + qq.getUniqueId();

            this._inputs[id] = fileInput;

            // remove file input from DOM
            if (fileInput.parentNode){
                qq.remove(fileInput);
            }

            return id;
        },
        getName: function(id){
            // get input value and remove path to normalize
            return this._inputs[id].value.replace(/.*(\/|\\)/, "");
        },

        _cancel: function(id){
            this._options.onCancel(id, this.getName(id));

            delete this._inputs[id];

            var iframe = document.getElementById(id);
            if (iframe){
                // to cancel request set src to something else
                // we use src="javascript:false;" because it doesn't
                // trigger ie6 prompt on https
                iframe.setAttribute('src', 'javascript:false;');

                qq.remove(iframe);
            }
        },

        _upload: function(id, params){

            var input = this._inputs[id];

            if (!input){
                throw new Error('file with passed id was not added, or already uploaded or cancelled');
            }

            var fileName = this.getName(id);

            var iframe = this._createIframe(id);
            var form = this._createForm(iframe, params);
            form.appendChild(input);

            var self = this;
            this._attachLoadEvent(iframe, function(){

                self.log('iframe loaded');

                var response = self._getIframeContentJSON(iframe);

                self._options.onComplete(id, fileName, response);
                self._dequeue(id);

                delete self._inputs[id];
                // timeout added to fix busy state in FF3.6
                setTimeout(function(){
                    qq.remove(iframe);
                }, 1);
            });

            form.submit();

            qq.remove(form);

            return id;
        },

        _attachLoadEvent: function(iframe, callback){
            qq.attach(iframe, 'load', function(){
                // when we remove iframe from dom
                // the request stops, but in IE load
                // event fires
                if (!iframe.parentNode){
                    return;
                }

                // fixing Opera 10.53
                if (iframe.contentDocument &&
                    iframe.contentDocument.body &&
                    iframe.contentDocument.body.innerHTML == "false"){
                    // In Opera event is fired second time
                    // when body.innerHTML changed from false
                    // to server response approx. after 1 sec
                    // when we upload file with iframe
                    return;
                }

                callback();
            });
        },
        /**
         * Returns json object received by iframe from server.
         */
        _getIframeContentJSON: function(iframe){
            // iframe.contentWindow.document - for IE<7
            var doc = iframe.contentDocument ? iframe.contentDocument: iframe.contentWindow.document,
                response;

            this.log("converting iframe's innerHTML to JSON");
            this.log("innerHTML = " + doc.body.innerHTML);

            try {
                response = eval("(" + doc.body.innerHTML + ")");
            } catch(err){
                response = {};
            }

            return response;
        },
        /**
         * Creates iframe with unique name
         */
        _createIframe: function(id){
            // We can't use following code as the name attribute
            // won't be properly registered in IE6, and new window
            // on form submit will open
            // var iframe = document.createElement('iframe');
            // iframe.setAttribute('name', id);

            var iframe = qq.toElement('<iframe src="javascript:false;" name="' + id + '" />');
            // src="javascript:false;" removes ie6 prompt on https

            iframe.setAttribute('id', id);

            iframe.style.display = 'none';
            document.body.appendChild(iframe);

            return iframe;
        },
        /**
         * Creates form, that will be submitted to iframe
         */
        _createForm: function(iframe, params){
            // We can't use the following code in IE6
            // var form = document.createElement('form');
            // form.setAttribute('method', 'post');
            // form.setAttribute('enctype', 'multipart/form-data');
            // Because in this case file won't be attached to request
            var form = qq.toElement('<form method="post" enctype="multipart/form-data"></form>');

            var queryString = qq.obj2url(params, this._options.action);

            form.setAttribute('action', queryString);
            form.setAttribute('target', iframe.name);
            form.style.display = 'none';
            document.body.appendChild(form);

            return form;
        }
    });

    /**
     * Class for uploading files using xhr
     * @inherits qq.UploadHandlerAbstract
     */
    qq.UploadHandlerXhr = function(o){
        qq.UploadHandlerAbstract.apply(this, arguments);

        this._files = [];
        this._xhrs = [];

        // current loaded size in bytes for each file

        this._loaded = [];
    };

// static method
    qq.UploadHandlerXhr.isSupported = function(){
        var input = document.createElement('input');
        input.type = 'file';

        return (
            'multiple' in input &&
                typeof File != "undefined" &&
                typeof (new XMLHttpRequest()).upload != "undefined" );

    };

// @inherits qq.UploadHandlerAbstract
    qq.extend(qq.UploadHandlerXhr.prototype, qq.UploadHandlerAbstract.prototype)

    qq.extend(qq.UploadHandlerXhr.prototype, {
        /**
         * Adds file to the queue
         * Returns id to use with upload, cancel
         **/

        add: function(file){
            if (!(file instanceof File)){
                throw new Error('Passed obj in not a File (in qq.UploadHandlerXhr)');
            }

            return this._files.push(file) - 1;

        },
        getName: function(id){

            var file = this._files[id];
            // fix missing name in Safari 4
            return file.fileName != null ? file.fileName : file.name;

        },
        getSize: function(id){
            var file = this._files[id];
            return file.fileSize != null ? file.fileSize : file.size;
        },

        /**
         * Returns uploaded bytes for file identified by id

         */

        getLoaded: function(id){
            return this._loaded[id] || 0;

        },
        /**
         * Sends the file identified by id and additional query params to the server
         * @param {Object} params name-value string pairs
         */

        _upload: function(id, params){
            var file = this._files[id],
                name = this.getName(id),
                size = this.getSize(id);

            this._loaded[id] = 0;

            var xhr = this._xhrs[id] = new XMLHttpRequest();
            var self = this;

            xhr.upload.onprogress = function(e){
                if (e.lengthComputable){
                    self._loaded[id] = e.loaded;
                    self._options.onProgress(id, name, e.loaded, e.total);
                }
            };

            xhr.onreadystatechange = function(){

                if (xhr.readyState == 4){
                    self._onComplete(id, xhr);

                }
            };

            // build query string
            params = params || {};
            params['qqfile'] = name;
            var queryString = qq.obj2url(params, this._options.action);

            xhr.open("POST", queryString, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.setRequestHeader("X-File-Name", encodeURIComponent(name));
            xhr.setRequestHeader("Content-Type", "application/octet-stream");
            xhr.send(file);
        },
        _onComplete: function(id, xhr){
            // the request was aborted/cancelled
            if (!this._files[id]) return;

            var name = this.getName(id);
            var size = this.getSize(id);

            this._options.onProgress(id, name, size, size);

            if (xhr.status == 200){
                this.log("xhr - server response received");
                this.log("responseText = " + xhr.responseText);

                var response;

                try {
                    response = eval("(" + xhr.responseText + ")");
                } catch(err){
                    response = {};
                }

                this._options.onComplete(id, name, response);

            } else {

                this._options.onComplete(id, name, {});
            }

            this._files[id] = null;
            this._xhrs[id] = null;

            this._dequeue(id);

        },
        _cancel: function(id){
            this._options.onCancel(id, this.getName(id));

            this._files[id] = null;

            if (this._xhrs[id]){
                this._xhrs[id].abort();
                this._xhrs[id] = null;

            }
        }
    });

    Assets.qq = qq;

})();


/*! jQuery JSON plugin 2.4.0 | code.google.com/p/jquery-json */
(function($){'use strict';var escape=/["\\\x00-\x1f\x7f-\x9f]/g,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},hasOwn=Object.prototype.hasOwnProperty;$.toJSON=typeof JSON==='object'&&$.toJSON?$.toJSON:function(o){if(o===null){return'null';}
var pairs,k,name,val,type=$.type(o);if(type==='undefined'){return undefined;}
if(type==='number'||type==='boolean'){return String(o);}
if(type==='string'){return $.quoteString(o);}
if(typeof o.toJSON==='function'){return $.toJSON(o.toJSON());}
if(type==='date'){var month=o.getUTCMonth()+1,day=o.getUTCDate(),year=o.getUTCFullYear(),hours=o.getUTCHours(),minutes=o.getUTCMinutes(),seconds=o.getUTCSeconds(),milli=o.getUTCMilliseconds();if(month<10){month='0'+month;}
if(day<10){day='0'+day;}
if(hours<10){hours='0'+hours;}
if(minutes<10){minutes='0'+minutes;}
if(seconds<10){seconds='0'+seconds;}
if(milli<100){milli='0'+milli;}
if(milli<10){milli='0'+milli;}
return'"'+year+'-'+month+'-'+day+'T'+
hours+':'+minutes+':'+seconds+'.'+milli+'Z"';}
pairs=[];if($.isArray(o)){for(k=0;k<o.length;k++){pairs.push($.toJSON(o[k])||'null');}
return'['+pairs.join(',')+']';}
if(typeof o==='object'){for(k in o){if(hasOwn.call(o,k)){type=typeof k;if(type==='number'){name='"'+k+'"';}else if(type==='string'){name=$.quoteString(k);}else{continue;}
type=typeof o[k];if(type!=='function'&&type!=='undefined'){val=$.toJSON(o[k]);pairs.push(name+':'+val);}}}
return'{'+pairs.join(',')+'}';}};$.evalJSON=typeof JSON==='object'&&$.evalJSON?$.evalJSON:function(str){return eval('('+str+')');};$.secureEvalJSON=typeof JSON==='object'&&$.evalJSON?$.evalJSON:function(str){var filtered=str.replace(/\\["\\\/bfnrtu]/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*$/.test(filtered)){return eval('('+str+')');}
throw new SyntaxError('Error parsing JSON, source is not valid.');};$.quoteString=function(str){if(str.match(escape)){return'"'+str.replace(escape,function(a){var c=meta[a];if(typeof c==='string'){return c;}
c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+str+'"';};}(jQuery));

})(jQuery);
