var PlayaSingleSelect;

(function($) {

var $window = $(window),
	$document = $(document);


/**
 * Drop Panes
 */
PlayaSingleSelect = function($field, opts) {

	var obj = this;
	obj.opts = opts;

	obj.showingEntries = false;
	obj.filteredEntries = false;
	obj.searchVal;


	// -------------------------------------------
	//  Gather the main DOM elements
	// -------------------------------------------

	obj.dom = { $field: $field };

	$tds = $('> table > tbody > tr > td', obj.dom.$field);
	obj.dom.$input  = $tds.filter('.playa-ss-input');
	obj.dom.$button = $tds.filter('.playa-ss-button');

	obj.dom.$selectedEntry = $('> li', obj.dom.$input);
	obj.dom.$preppedEntry;

	obj.dom.$entriesContainer = $('> div', obj.dom.$field);
	obj.dom.$scrollpane = $('> div', obj.dom.$entriesContainer);
	obj.dom.$ul = $('> ul', obj.dom.$scrollpane);
	obj.dom.$entries;

	obj.dom.$search = $('<input type="text" />');

	// Move the entries dropdown to the end of the DOM
	// so it doesn't get cut off by any overflow:hidden's
	obj.dom.$entriesContainer.appendTo(document.body);

	// --------------------------------------------------------------------

	/**
	 * Update Scrollpane Height 
	 */
	var updateScrollpaneHeight = function() {
		// are there any entries?
		if (! obj.dom.$entries.length) {
			obj.dom.$entriesContainer.hide();
		} else {
			obj.dom.$entriesContainer.show();

			// how much room do we have?
			var fieldTop = obj.dom.$field.offset().top,
				windowHeight = $window.height(),
				windowScrollTop = $window.scrollTop(),
				maxScrollpaneHeight = windowHeight - (fieldTop - windowScrollTop) - 44;

			// how much can we even use?
			var ulHeight = obj.dom.$ul.outerHeight() + 7;

			// use the shorter one
			var scrollpaneHeight = (maxScrollpaneHeight < ulHeight) ? maxScrollpaneHeight : ulHeight;

			obj.dom.$scrollpane.height(scrollpaneHeight);
		}
	};

	/**
	 * Show Entries
	 */
	var showEntries = function(event) {
		// ignore if already showing entries
		if (obj.showingEntries) return;

		// show the search
		showSearch();

		// place the entries dropdown below the field
		var offset = obj.dom.$field.offset();
		obj.dom.$entriesContainer.css({
			top: (offset.top + 24),
			left: offset.left
		});

		// set the scrollpane height, and keep keep it updated as the window scrolls
		updateScrollpaneHeight();
		$window.bind('scroll.playa-ss resize.playa-ss', updateScrollpaneHeight);

		if (obj.filteredEntries) {
			resetEntries();
		}

		obj.dom.$entriesContainer.show();

		// prevent clicks within the search input and entries container from hiding the entries
		$([obj.dom.$entriesContainer[0], obj.dom.$search[0]]).click(function(event) {
			event.stopPropagation();
		});

		// hide when they click anywhere
		var ignoreNextClick = true;
		$(document.body).bind('click.playa-ss', function() {
			if (ignoreNextClick) {
				ignoreNextClick = false;
			}
			else if (obj.showingEntries) {
				selectPreppedEntryOrSelectedEntry();
			}
		});

		obj.showingEntries = true;
	};

	/**
	 * Hide Entries
	 */
	var hideEntries = function() {
		// forget about the prepped entry
		unprepEntry();

		// stop worrying about keeping the scrollpane height updated
		$window.unbind('.playa-ss');

		// stop listening for document clicks
		$(document.body).unbind('click.playa-ss');

		obj.dom.$entriesContainer.hide();
		obj.showingEntries = false;
	};

	/**
	 * Select Entry
	 */
	var selectEntry = function($entry) {
		// it's possible we're just re-selecting the previous selected entry,
		// so make sure that's not the case before going to all this trouble
		if ($entry != obj.dom.$selectedEntry) {

			// copy the selected entry
			obj.dom.$selectedEntry = $entry.clone().removeClass('playa-dp-active');

			// enable the input if there is one
			var $input = $('input', obj.dom.$selectedEntry);

			if ($input.length) {
				var name = $input.attr('name').match(/^(.*)\[options\](.*)$/);
				$input.attr('name', name[1]+'[selections]'+name[2]);
				$input.removeAttr('disabled');
			}
		}

		// copy the selected entry into the input cell
		obj.dom.$input.html(obj.dom.$selectedEntry);

		// hide the entries list
		hideEntries();

		// focus on the input
		setTimeout(function() {
			obj.dom.$field.focus().removeClass('playa-hidefocus');
		},1);

		// trigger the 'change' event
		obj.dom.$field.trigger('change');
	};

	/**
	 * Reselect Selected Entry
	 */
	var reselectSelectedEntry = function() {
		selectEntry(obj.dom.$selectedEntry);
	};

	/**
	 * Select Prepped Entry
	 */
	var selectPreppedEntry = function() {
		selectEntry(obj.dom.$preppedEntry);
	};

	/**
	 * Selec Prepped Entry or Selected Entry
	 */
	var selectPreppedEntryOrSelectedEntry = function() {
		if (obj.dom.$preppedEntry) {
			selectPreppedEntry();
		} else {
			reselectSelectedEntry();
		}
	};

	/**
	 * Prep Entry
	 */
	var prepEntry = function($entry) {
		unprepEntry();

		obj.dom.$preppedEntry = $entry;
		obj.dom.$preppedEntry.addClass('playa-dp-active');

		// -------------------------------------------
		//  Scroll to the entry
		// -------------------------------------------

		var scrollTop = obj.dom.$scrollpane.attr('scrollTop'),
			entryOffset = obj.dom.$preppedEntry.offset().top,
			scrollpaneOffset = obj.dom.$scrollpane.offset().top,
			offsetDiff = entryOffset - scrollpaneOffset;

		if (offsetDiff < 0) {
			obj.dom.$scrollpane.attr('scrollTop', scrollTop + offsetDiff);
		}
		else {
			var entryHeight = obj.dom.$preppedEntry.outerHeight(),
				scrollpaneHeight = obj.dom.$scrollpane.outerHeight();

			if (offsetDiff > scrollpaneHeight - entryHeight) {
				obj.dom.$scrollpane.attr('scrollTop', scrollTop + (offsetDiff - (scrollpaneHeight - entryHeight)));
			}
		}

	};

	/**
	 * Prep First Entry
	 */
	var prepFirstEntry = function() {
		prepEntry(obj.dom.$entries.first());
	};

	/**
	 * Un-prep Entry
	 */
	var unprepEntry = function() {
		if (obj.dom.$preppedEntry) {
			obj.dom.$preppedEntry.removeClass('playa-dp-active');
			obj.dom.$preppedEntry = null;
		}
	};

	/**
	 * Update Entries
	 */
	var updateEntries = function() {
		// get the full list of LIs
		obj.dom.$entries = $('li', obj.dom.$ul);

		// add event listener
		obj.dom.$entries.bind('mousedown.playa-ss', function() {
			selectEntry($(this));
		});
	};

	updateEntries();
	obj.dom.$originalEntries = obj.dom.$entries;

	/**
	 * Reset Entries
	 */
	var resetEntries = function() {
		obj.dom.$ul.html(obj.dom.$originalEntries);
		updateEntries();
		updateScrollpaneHeight();
	};

	// -------------------------------------------
	//  Search
	// -------------------------------------------

	var searchTimeout,
		originalSearchVal;

	var isOriginalSearchVal = function() {
		return (obj.dom.$search.val() && obj.dom.$search.val() == originalSearchVal);
	};

	var checkKeywordVal = function() {
		// has the value changed?
		var _searchVal = obj.dom.$search.val();
		if (_searchVal == obj.searchVal) return;

		obj.searchVal = _searchVal;

		if (obj.searchVal) {
			applyFilters();
			obj.filteredEntries = true;
		} else {
			obj.filteredEntries = false;
			resetEntries();
		}
	};

	/**
	 * Show Search
	 */
	var showSearch = function() {
		// get the initial value
		if (obj.dom.$selectedEntry.length && ! obj.dom.$selectedEntry.hasClass('playa-ss-noval')) {
			// remove the status span and grab the remaining HTML
			var $a = $('> a', obj.dom.$selectedEntry).clone();
			$('.playa-entry-status', $a).remove();
			obj.searchVal = $a.text();
		} else {
			obj.searchVal = '';
		}

		originalSearchVal = obj.searchVal;

		// replace the selected entry with the text input
		obj.dom.$input.html(obj.dom.$search);

		// set the initial value
		obj.dom.$search.val(obj.searchVal);

		// select the entire value
		if(obj.dom.$search[0].setSelectionRange) {
			var length = obj.searchVal.length * 2;
			obj.dom.$search[0].setSelectionRange(0, length);
		} else {
			// browser doesn't support setSelectionRange so try refreshing
			// the value as a way to place the cursor at the end
			obj.dom.$search.val(obj.dom.$search.val());
		}

		// wait a moment for the current click to propagate, then focus on the text input
		setTimeout(function() {
			// focus on the search input
			obj.dom.$search.focus();
		}, 1);

		// listen for keydowns
		obj.dom.$search.keydown(function(event) {
			// ignore if meta key is down
			if (event.metaKey || event.ctrlKey) return;

			event.stopPropagation();

			// clear the last timeout
			clearTimeout(searchTimeout);

			switch (event.keyCode) {
				case 13: // return
					event.preventDefault();
					if (obj.dom.$preppedEntry) {
						selectPreppedEntry();
					} else if (isOriginalSearchVal()) {
						reselectSelectedEntry();
					} else {
						selectEntry(obj.dom.$originalEntries.first());
					}
					break;

				case 27: // esc	
					reselectSelectedEntry();
					event.preventDefault();
					break;

				case 38: // up
					if (obj.dom.$entries.length) {
						if (obj.dom.$preppedEntry) {
							var $prev = obj.dom.$preppedEntry.prev();
							if ($prev.length) {
								prepEntry($prev);
							}
						} else {
							prepEntry(obj.dom.$entries.last());
						}
					}
					break;

				case 40: // down
					if (obj.dom.$entries.length) {
						if (obj.dom.$preppedEntry) {
							var $next = obj.dom.$preppedEntry.next();
							if ($next.length) {
								prepEntry($next);
							}
						} else {
							prepFirstEntry();
						}
					}
					break;

				default:
					searchTimeout = setTimeout(checkKeywordVal, 500);
			}
		});
	};

	/**
	 * Apply filters
	 */
	var applyFilters = function() {
		// kill all mouse events within the field
		obj.dom.$entries.unbind('.playa-ss');

		// dim the options
		obj.dom.$ul.css('opacity', 0.5);

		var data = {
			ACT:        PlayaFilterResources.ACT,
			field_id:   obj.dom.$field.attr('id'),
			field_name: opts.fieldName,
			keywords:   obj.dom.$search.val()
		};

		// defaults
		for (i in opts.defaults) {
			data[i] = opts.defaults[i];
		}

		// run the ajax post request
		$.post(PlayaFilterResources.filterUrl, data, function(data, textStatus) {
			if (textStatus == 'success') {
				// update the options
				obj.dom.$ul.html(data).css('opacity', 1);

				// reset $entries
				updateEntries();
				updateScrollpaneHeight();

				// unprep the prepped entry
				unprepEntry();

				// prep the first entry for selection
				if (obj.dom.$entries.length) {
					prepFirstEntry();
				}
			}
		});
	};

	obj.dom.$input.mousedown(showEntries);
	obj.dom.$button.mousedown(showEntries);

	// -------------------------------------------
	//  Handle field focus
	// -------------------------------------------

	obj.dom.$field.keydown(function(event) {
		if (event.metaKey || event.ctrlKey) return;

		if (event.keyCode == 32) { // space
			showEntries(event);
			event.preventDefault();
		}
	});

	// -------------------------------------------
	//  Keep drop panes from getting outlines on click
	// -------------------------------------------

	obj.dom.$field.mousedown(function() {
		obj.dom.$field.addClass('playa-hidefocus');
	});

};


})(jQuery);
