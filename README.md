Peter Arnell Knowledgebase
--------------------------

### Super Search

`super_search/mod.super_search.php` line 7862
Added error supressing `@` at beginning of line so wouldn't throw PHP errors to logged in Super Admins when searching multiple words.

### Wygwam / CKEditor

Added `config.scayt_autoStartup = true;` to `/themes/third_paty/wgywam/lib/ckeditor/config.js`.

### Remove Channel Images icon in Wygwam fields by default

In ext.channel_images.php, around line 60, change to:

    //if (isset($config['extraPlugins']) != FALSE)
    if (isset($config['extraPlugins']) != FALSE && strpos($config['extraPlugins'],'channelimages') !== FALSE )
    {
        //$config['extraPlugins'] .= ',channelimages';
        $config['toolbar'][] = array('ChannelImages');
    }

Then in channels where the CI plugin is required, add `channelimages` to the extraPlugins setting for that Wygwam field.

See [here for more](http://www.devdemon.com/forums/viewthread/985/#5741).

### Structure

I enable additional reorder options by setting the boolean switch to true on line 40 of mcp.structure.php
