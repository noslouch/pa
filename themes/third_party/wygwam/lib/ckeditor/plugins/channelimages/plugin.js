CKEDITOR.plugins.add('channelimages',
{
	requires : ['dialog'],
	
	init: function(editor)
	{
		// Only if CI is there
		if (typeof(ChannelImages) == 'object' && typeof(ChannelImages.CI_Images) == 'object')
		{
			// Plugin Name
			var pluginName = 'channelimages';
	    	
			// Add Dialog JS
			CKEDITOR.dialog.add(pluginName, this.path + 'dialogs/channelimages.js');
	        
			// Add Command?
			editor.addCommand(pluginName, new CKEDITOR.dialogCommand(pluginName));
	        
			// Add Toolbar Button
			editor.ui.addButton('ChannelImages',
			{
				label: 'Add/Edit Images',
				icon: this.path + 'ci_button.png',
				command: pluginName
			});
		}
	}
});
// http://www.voofie.com/content/2/ckeditor-plugin-development/