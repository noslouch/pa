var PicPullerIG;
// prevent IE errors when using console
if (typeof console === "undefined") {
		window.console = {
			log: function () {}
		};
} 

$(function() {
	// Handler for .ready() called.

	PicPullerIG = {
		callbacks: {
			afterThumbnailGeneration: {}
		}, 
		init: function() {
			// The Field Type's photo viewer won't work properly without JS, so it's hidden until the JS has loaded. Attempt to show both the User Stream Browser and the Search Browser. If they aren't present based on the preferences, they won't be able to be shown.
			$('.igbrowserbt').show();
			$('.igsearchbt').show();

			// Attach ColorBox listeners to both buttons
			$('.igbrowserbt').ppcolorbox({width:"830px", height:"525px", title: 'Choose a photo from your Instagram feed',
				onOpen: function() {
						$(this).parent().find('input').attr('id', 'activePPtarget');
					},
				onCleanup: function() {
						// check to see if the field actually had an ID input, if so
						// we turn on the search button
						if (checkForValueinPPfield($('#activePPtarget')) ){
							var myLookupBt = $(this).parent().find($('.ig_preview_bt'));
							myLookupBt.trigger('click');
						}
						$('#activePPtarget').removeAttr('id');
					}
			});

			$('.igsearchbt').ppcolorbox({width:"830px", height:"525px", title: '<input type="text" id="ig_search_field" name="ig_tag" placeholder="Search for a single tag"><input type="submit" id="ig_search_button" value="Search">',
				onOpen: function() {
						$(this).parent().find('input').attr('id', 'activePPtarget');
					},
				onComplete: function() {
					$("#ig_search_button").attr("disabled", true);

					$("#ig_search_field").keyup(function(event) {
						if($("#ig_search_field").val() !== '') {
							$("#ig_search_button").attr("disabled", false);
						} else {
							$("#ig_search_button").attr("disabled", true);
						}
					});
				},

				onCleanup: function() {
						// check to see if the field actually had an ID input, if so
						// we turn on the search button
						if (checkForValueinPPfield($('#activePPtarget')) ){
							var myLookupBt = $(this).parent().find($('.ig_preview_bt'));
							myLookupBt.trigger('click');
						}
						$('#activePPtarget').removeAttr('id');
					}
			});

			/*
			Make the Preview button work
			 */
			
			//
			// Since the click event might be triggered in the $('.ig_media_id_field').each loop,
			// I need to define the listener before doing that each loop
			//
			function addClickEventToPPPreview() {
				$('.ig_preview_bt').on('click', function(e) {
					e.preventDefault();
					var media_id = $(this).parent().find($('.ig_media_id_field')).val();
					// Searching for something like the following...
					// http://instagram.com/p/abC123jfm9/
					// http://instagr.am/p/abC123jfm9/
					if ( media_id.indexOf('instagr') === 7 ) {
						translateURLtoMediaID(trim11(media_id), $(this));
					} else {
						// we were proved a MediaID, or at least not an Instagram 
						// link, so let's try to find that image
						generatePreviewFromMediaID(media_id, $(this));
					}
				});
			}

			addClickEventToPPPreview();

			function translateURLtoMediaID (url, targetField) {
				var media_id = null;
				$.ajax({
					url: "http://api.instagram.com/oembed?url="+url,
					contentType: 'text/plain',
					xhrFields: {
					// The 'xhrFields' property sets additional fields on the XMLHttpRequest.
					// This can be used to set the 'withCredentials' property.
					// Set the value to 'true' if you'd like to pass cookies to the server.
					// If this is enabled, your server must respond with the header
					// 'Access-Control-Allow-Credentials: true'.
					withCredentials: false
					},
					dataType: 'jsonp',
					success: function(data) {
						console.log('Data received from Instagram oembed.');
						console.log(data);
						console.log(data.media_id);
						if (data.media_id){
							media_id = data.media_id;
							targetField.parent().find($('.ig_media_id_field')).val(media_id);
							generatePreviewFromMediaID(media_id, targetField);
						} else {
							return null;
						}
					}
				});
			}

			function generatePreviewFromMediaID (media_id, targetField) {
				var myPreviewFrame = targetField.parent().find($('.thumbnail'));
				myPreviewFrame.slideDown();
				var theURL = targetField.attr('href')+media_id;
				var theImage = targetField.parent().find($('.theImage'));
				var theHeadline = targetField.parent().find($('.theHeadline'));
				var ig_pp_loader_gr = targetField.parent().find($('.ig_pp_loader_gr'));
				ig_pp_loader_gr.removeClass('hidden');

				$.ajax({
						url: theURL,
						dataType: 'json',
						success: function(data) {
							console.log('Data received from Instagram.');
							console.log(data);
							ig_pp_loader_gr.addClass('hidden');
							if (data.code === 200 ){
								theImage.removeClass('hidden');
								theImage.attr("src",data.imageURL);
								theHeadline.html(data.imageTitle + " <em>by " + data.theUsername + "</em>");
								myPreviewFrame.attr('data-id', data.imageID);
								myPreviewFrame.attr('data-username', data.theUsername);
								myPreviewFrame.attr('data-profile_picture', data.theProfilePicture);
								myPreviewFrame.attr('data-fullurl', data.theLink);
								// call the callback function and pass in the previewframe that was created
								PicPullerIG.callback('afterThumbnailGeneration', myPreviewFrame);
							} else {
								theImage.addClass('hidden');
								if( data.error_type )
									{theHeadline.html("<strong>"+data.error_type+": </strong>" + data.error_message);}
								else {
									{theHeadline.html("<strong>Unknown error: </strong> Instagram returned no information.");}
								}	
							}
						},
						error: function(data) {
							console.log('ERROR');
							console.log(data);
						}
						});
			}

			// helper trim function
			function trim11 (str) {
				str = str.replace(/^\s+/, '');
				for (var i = str.length - 1; i >= 0; i--) {
					if (/\S/.test(str.charAt(i))) {
						str = str.substring(0, i + 1);
					break;
					}
				}
				return str;
			}

			if (typeof Matrix !== 'function'){
				// preview button is hidden until there is something to look up
				// first check all PP fields to see if they contain something...
				$('.ig_media_id_field').each(function(e){
					//console.log('checking to see if I need to turn on that magnifying glass');
					if (checkForValueinPPfield($(this)) ){
						console.log('There was a value in the checked PP field, so trigger an automated lookup.');
						var myLookupBt = $(this).parent().find($('.ig_preview_bt'));
						myLookupBt.trigger('click');
					} else {
						console.log('No need for an automated look up.');
					}
				});
				// and watch for someone entering a media ID manually
				// $('.ig_media_id_field').keyup(function(e){
				// 	checkForValueinPPfield($(this));
				// });

				$('.ig_pp_fieldset').delegate('.ig_media_id_field', "keyup", function( e ) {
					checkForValueinPPfield($(this));
				} );
			}


			function checkForValueinPPfield(theTarget) {
				var myValue= theTarget.val();
				var myLookupBt = theTarget.parent().find($('.ig_preview_bt'));
				if(myValue !== '') {
					myLookupBt.removeClass('hidden');
					return true;
				} else {
					myLookupBt.addClass('hidden');
					return false;
				}
			}



			if (typeof Matrix == 'function'){
				console.log("PP detected a Matrix field.");

				// Bind the PicPuller preparation events to the Matrix display event
				Matrix.bind('ig_picpuller', 'display', function(cell){
					// Upon the display of each new PP browser row within a Matrix field, this JS is fired
					// Show the matrix PP buttons
					$('.igbrowserbtmatrix').show();
					$('.igsearchbtmatrix').show();
					addClickEventToPPPreview();
					// attach the ColorBox to them
					$('.igbrowserbtmatrix').ppcolorbox({
						width:"830px",
						height:"525px",
						title: 'Choose a photo from your Instagram feed',
						onOpen: function() {
								$(this).parent().find('input').attr('id', 'activePPtarget');
							},
						onCleanup: function() {
							// Look up whatever image might have chosen
							if (checkForValueinPPfield($('#activePPtarget')) ){
								console.log('trying to trigger a lookup in Matrix personal stream update');
								var myLookupBt = $(this).parent().find($('.ig_preview_bt'));
								myLookupBt.trigger('click');
							}

							// then remove the target ID from the text input box
							$('#activePPtarget').removeAttr('id');
						}
					});

					$('.igsearchbtmatrix').ppcolorbox({
							width:"830px",
							height:"525px",
							title: '<input type="text" id="ig_search_field" name="ig_tag" placeholder="Search for a single tag"><input type="submit" id="ig_search_button" value="Search">',
							onOpen: function() {
									$(this).parent().find('input').attr('id', 'activePPtarget');
								},
							onComplete: function() {
								$("#ig_search_button").attr("disabled", true);

								$("#ig_search_field").keyup(function(event) {
									console.log('testing search field : ' + $("#ig_search_field").val());
									if($("#ig_search_field").val() !== '') {
										$("#ig_search_button").attr("disabled", false);
									} else {
										$("#ig_search_button").attr("disabled", true);
									}
								});
							},
							onCleanup: function() {
									// Look up whatever image might have chosen
									if (checkForValueinPPfield($('#activePPtarget')) ){
										console.log('trying to trigger a lookup in Matrix search update');
										var myLookupBt = $(this).parent().find($('.ig_preview_bt'));
										myLookupBt.trigger('click');
									}
									// then remove the target ID from the text input box
									$('#activePPtarget').removeAttr('id');
							}
						});

					$('.ig_pp_fieldset').delegate('.ig_media_id_field', "keyup", function( e ) {
						checkForValueinPPfield($(this));
					} );



				// for compatibility with Better Workflow
				// check for the presence of Bwf, and if present
				// re-add the click event to the PP preview button
				// since it disappears after closing a preview window
				// when Matix fields are used.
				if (typeof Bwf == 'object') {
					Bwf.bind('ig_picpuller', 'previewClose', function(){
						//console.log("BWF is present & the preview window was just closed.");
						addClickEventToPPPreview();

						$('.ig_media_id_field').each(function(e){
							//console.log('checking to see if I need to turn on that magnifying glass');
							if (checkForValueinPPfield($(this)) ){
								console.log('There was a value in the checked PP field, so trigger an automated lookup.');
								var myLookupBt = $(this).parent().find($('.ig_preview_bt'));
								myLookupBt.trigger('click');
							} else {
								console.log('No need for an automated look up.');
							}
						});
					});
				}

				}); // end Matrix.bind
			}
		}, // end init
		bind : function(myUniqueIdentifier, event, callback) {
			//console.log('binding');
			if (typeof PicPullerIG.callbacks[event] == 'undefined') return;
			PicPullerIG.callbacks[event][myUniqueIdentifier] = callback;
			console.log('The "' +event+ '" event with the identifer of "' + myUniqueIdentifier + '" has been bound.');
		},
		unbind : function(myUniqueIdentifier, event) {
			
			// is this a legit event?
			if (typeof PicPullerIG.callbacks[event] == 'undefined') return;

			// is the celltype even listening?
			if (typeof PicPullerIG.callbacks[event][myUniqueIdentifier] == 'undefined') return;

			delete PicPullerIG.callbacks[event][myUniqueIdentifier];
			console.log('The "' +event+ '" event with the identifer of "' + myUniqueIdentifier + '" has been unbound.');
		},
		callback: function(callback, that){
			// 'that' is the ig_preview_frame that was just generated
			for (var myIdentifier in PicPullerIG.callbacks[callback]) {
				if (typeof PicPullerIG.callbacks[callback][myIdentifier] == 'function') {
					//console.log(myIdentifier + ' has a function.');
					PicPullerIG.callbacks[callback][myIdentifier].call(that, PicPullerIG.callbacks[callback]);
				}
			}
		}
	};

	PicPullerIG.init();

	/* ******************************** /
	/ Example Code for adding callbacks /
	/ ******************************** */

	// // Attach an event stored with the uniqueID of myUniqueIdentifier1234 which allows its removal if needed.
	// PicPullerIG.bind('myUniqueIdentifier1234', 'afterThumbnailGeneration', function() {
	//	console.log('call back fired');
	//	console.log(this);
	//	// 'this' will hold a reference to newly generated thumbnail box
	//	// Just to show it works, let's turn the background color of the field to green
	// this.css('backgroundColor', '#66ff33');
	// });

	// // The event stored at myUniqueIdentifier1234 is now removed using unbind
	// PicPullerIG.unbind('myUniqueIdentifier1234', 'afterThumbnailGeneration');




});

