$(document).ready(function() {
	var entryUrl = {
		init: function() {
			this.setVars();
			this.attachEvents();

			//Append the input to store the url
			$('#mainContent').find('.heading').prepend(this.$entryUrl);

			//Run it on load to deal with existing entries
			this.formatUrl();
		},

		attachEvents: function() {
			this.$title.add(this.$pageUrl).add(this.$urlTitle).add(this.$structureUrl).on('keyup', $.proxy(this.formatUrl, this));
			this.$entryDate.on('change', $.proxy(this.formatUrl, this));
			this.$structureParent.on('change', $.proxy(this.formatUrl, this));

			this.$entryUrl.on('click', function() {
				this.select();
			});
		},

		formatBase: function() {
			return (vl_entry_url.base.charAt(vl_entry_url.base.length - 1) === '/') ? vl_entry_url.base.substring(0, vl_entry_url.base.length - 1) : vl_entry_url.base;
		},

		formatUrl: function() {
			var self = this;

			setTimeout(function() {
				var urlTitle = self.$urlTitle.val() || '';
				var pageUrl = self.$pageUrl.val() || '';
				var date = new Date(self.$entryDate.val());
				var month = self.padDateString(date.getMonth() + 1) || '';
				var day = self.padDateString(date.getDate()) || '';
				var year = date.getFullYear() || '';

				var structureUrl = self.$structureUrl.val() || '';
				var structureParent = self.$structureParent.val() || '';
				structureUrl = (vl_entry_url.structureTree) ?
								(vl_entry_url.structureTree[structureParent] + structureUrl) :
								('/' + structureUrl);

				var newUrl = self.fullPattern.replace('{url_title}', urlTitle);
				newUrl = newUrl.replace('{page_url}', pageUrl);
				newUrl = newUrl.replace('{month}', month);
				newUrl = newUrl.replace('{day}', day);
				newUrl = newUrl.replace('{year}', year);
				newUrl = newUrl.replace('{structure_url}', structureUrl);

				self.$entryUrl.val(newUrl);
			}, 100);
		},

		padDateString: function(datePiece) {
			return ('0' + datePiece).slice(-2);
		},

		setVars: function() {
			this.$entryUrl = $('<input type="text" readonly id="entry-url">');
			this.$title = $('#title');
			this.$urlTitle = $('#url_title');
			this.$pageUrl = $('#pages__pages_uri');
			this.$entryDate = $('#entry_date');
			this.$structureUrl = $('#structure__uri');
			this.$structureParent = $('#structure__parent_id');
			this.fullPattern = this.formatBase() + vl_entry_url.pattern;
		}
	};

	//Should we do this thing?
	if(vl_entry_url) {
		entryUrl.init();
	}
});