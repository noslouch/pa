Title: DataGrab Configuration Options
Base Header Level: 3

# Url Title #

DataGrab's default behaviour is to generate an entry's url title from the 
entry's Title field (as it would if you were publishing an entry manually). 
If you need the entry to have a specific url title then you can specify
which field of the data source to use.

Note, that URL Titles cannot be made entirely from numbers. If your titles are
numeric, then you can use the Publish Page Customization options under the 
Channel Preferences to add a [URL Title Prefix](http://expressionengine.com/user_guide/cp/admin/channels/channel_edit_preferences.html#url-title-prefix).

# Date #

DataGrab will attempt to read your date in whatever format you provide. For
best results use YYYY-MM-DD hh:mm:ss. Try to avoid 'ambiguous' formats, such
as DD/MM/YY or MM/DD/YY.

# Duplicate Entries #

DataGrab can check whether entries already exists and if they do it can skip 
or attempt to update the existing entry.

To check for an existing or duplicate entry, DataGrab will test whether the 
potential new entry has a field value that matches an existing entry. 

For example, an RSS feed often has a `guid` field that uniquely identifies a 
feed element, or a store product usually has a stock id number.

If you save this value into a custom field, you can then, on subsequent imports, 
check this field for an existing entry and update it or ignore it as appropriate.

You can use the **Use this field to check for duplicates** setting to select which
field to check. If you leave this field blank it will always add a new entry.

if you check the **Update existing entries** option then DataGrab will attempt
to update the entry's values from the new record. Otherwise, it will ignore it
(ie, not update it, but not add a new entry).

# Add a timestamp to this field #

DataGrab can add a timestamp to a field that it adds or updates. This can help 
in tracking which entries have been updated or deleting old entries.

# Delete old entries #

If you check this, DataGrab will delete any entries in the channel that have
not been added or updated by this import. This can be useful for maintaining 
an up-to-date product list, for example, but must be used with care.

# Default Author #

This allows you to set the author of any new entries.

# Author #

You can also assign the author from a field in the data source. The **Author
Field Value** tells the system what format the author is stored as (eg, email
address, username) and **Author** specifies which data field it is in.

# Status #

The Status field allows you to specify a data source field to set the entry's
status or you can use Open, Closed or the [channel's default](http://expressionengine.com/user_guide/cp/admin/channels/channel_edit_preferences.html#administrative-preferences).

# Offset #

Sometimes you need to adjust the time to take into account localization. The 
offset option allows you to add or subtract any number of seconds from the 
date/time values.

