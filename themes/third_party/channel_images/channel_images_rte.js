(function(){
if (typeof(ChannelImages) != 'undefined') {

WysiHat.addButton('channel_images', {
	label: EE.rte.channel_images.label,
	init: function(name, editor) {
		this._editor = editor;
		return this.parent.init(name, editor);
	},
	handler: function(state, finalize) {
		this.state = state;
		this.finalize = finalize;

		this._open_dialog();

		return false;
	},
	_open_dialog: function(){

		if ( $('.rte-channel_images-dialog').length > 0 ) {
			this._dialog_obj = $('.rte-channel_images-dialog');
			this._dialog_obj.dialog('open');
			return;
		}

		this.dialog_obj = $('<div class="rte-channel_images-dialog">' +
							'<div class="dialog-body"></div>' +
							'<p class="buttons">' +
							'<button class="submit" type="submit">' + EE.rte.channel_images.add_image + '</button></p>' +
						'</div>').appendTo('body');

		this.dialog_obj.dialog({
			width: 550,
			resizable: true,
			position: ["center","center"],
			modal: true,
			draggable: true,
			title: EE.rte.channel_images.label,
			autoOpen: true,
			zIndex: 99999,
			open: $.proxy(this, '_ci_fill_modal'),
			close: $.proxy(this, '_ci_close_dialog')
		})
		// Remove link
		.on('click', '.rte-link-remove', function(){
			var $el = $(anchorNode);
			$el.replaceWith($el.html());

			$link_dialog.dialog('close');
		})
		// Select Image
		.on('click', '.CImage img', function(Event){

			var Target = jQuery(Event.target);
			Target.closest('div.tabcontent').find('div.CImage').removeClass('Selected');
			Target.closest('div.CImage').addClass('Selected');
		})
		// Add link
		.on('click', '.submit', $.proxy( this, '_ci_insert_image' ) );

	},
	_ci_fill_modal: function(){
		var HTML = [];
		HTML.push('<ul class="tabs">');
		for (var FIELD1 in ChannelImages.Fields){
			HTML.push('<li><a href="#'+FIELD1+'">'+ChannelImages.Fields[FIELD1].field_label+'</a></li>');
		}

		HTML.push('</ul>');

		for (var FIELD in ChannelImages.Fields){
			HTML.push('<div id="' + FIELD + '" class="tabcontent">');


			if (typeof(ChannelImages.Fields[FIELD].wimages) == 'undefined' || ChannelImages.Fields[FIELD].wimages.length === 0){
				HTML.push('<p>'+EE.rte.channel_images.no_images+'</p>');
			} else {

				HTML.push('<div class="imageholder">');
				for (var i = ChannelImages.Fields[FIELD].wimages.length - 1; i >= 0; i--) {
					var IMG = ChannelImages.Fields[FIELD].wimages[i];
					HTML.push('<div class="CImage"><img src="'+IMG.big_img_url+'" title="'+IMG.title+'" alt="'+IMG.description+'" data-filename="'+IMG.filename+'" width="60px"></div>');
				}
				HTML.push('</div>');

				HTML.push('<br clear="all">');

				HTML.push('<div class="sizeholder">');
				HTML.push('<ul>');
				HTML.push('<li><input name="size_'+FIELD+'" type="radio" value="original" checked> '+EE.rte.channel_images.original+'</li>');

				if (typeof(ChannelImages.Fields[FIELD].settings.action_groups) != 'undefined'){

					for (var ii in ChannelImages.Fields[FIELD].settings.action_groups) {
						if (ChannelImages.Fields[FIELD].settings.action_groups[ii].wysiwyg != 'yes') continue;
						HTML.push('<li><input name="size_'+FIELD+'" type="radio" value="'+ChannelImages.Fields[FIELD].settings.action_groups[ii].group_name+'"> '+ChannelImages.Fields[FIELD].settings.action_groups[ii].group_name+'</li>');
					}

				}
				HTML.push('</ul>');
				HTML.push('<br clear="all">');
				HTML.push('</div>');

			}

			HTML.push('</div>');
		}

		HTML.push('<br>');
		this.dialog_obj.find('.dialog-body').html(HTML.join(''));
		this.dialog_obj.find('.dialog-body').tabs();
	},
	_ci_insert_image: function(Event){
		Event.preventDefault();

		var Parent = $(Event.target).closest('div.rte-channel_images-dialog').find('div.tabcontent:visible');
		var Selected = Parent.find('.Selected img');

		if (Selected.length === 0) {
			alert('No image selected..');
			return false;
		}

		var IMGSRC = Selected.attr('src');
		var filename = Selected.data('filename');
		var dot = filename.lastIndexOf('.');
		var extension = filename.substr(dot,filename.length);
		var Size = Parent.find('.sizeholder input[type=radio]:checked').val();
		var OLDFILENAME = Selected.data('filename');

		if (Size != 'original'){
			var NewName = filename.replace(extension, '__'+Size+extension);
			IMGSRC = IMGSRC.replace(/f\=(.*?)\&/, 'f='+NewName+'&');
		}
		else {
			IMGSRC = IMGSRC.replace(/f\=(.*?)\&/, 'f='+filename+'&');
		}

		var TempImg = $('<img/>').attr({
				'src': IMGSRC,
				'alt': Selected.attr('alt'),
				'title': Selected.attr('title'),
				'class': 'ci-image ci-' + Size
		});

		var	Figure = $('<figure/>').css('text-align','center').append(TempImg);

		if ( ! this._editor.is(':focus'))
		{
			this._editor.focus();
		}

		var sel = window.getSelection();
		var range = document.createRange();

		range.setStart(sel.anchorNode, sel.anchorOffset);
		range.setEnd(sel.focusNode, sel.focusOffset);

		if ((caption_text = prompt(EE.rte.channel_images.caption_text, Selected.attr('alt') )))
		{
			Figure.append(
				$('<figcaption/>').text(caption_text)
			);
		}

		range.insertNode( Figure.get(0) );

		this.dialog_obj.dialog('close');
		this.finalize();
	},
	_ci_close_dialog: function(){
		this.dialog_obj.find('div.dialog-body').attr('class', 'dialog-body').empty().removeData('tabs');
	}
});



}
})();
