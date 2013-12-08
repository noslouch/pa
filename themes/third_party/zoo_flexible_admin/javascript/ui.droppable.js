/*
 * jQuery UI Droppable 1.7.1
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Droppables
 *
 * Depends:
 *	ui.core.js
 *	ui.draggable.js
 *
 * changed by Titkov Anton, ElSoft company, http://elsoft.tomsk.ru
 * changed intersect function, now you can add your tolerance mode function and use over and out events for intersect states. droppable widget and $.ui.ddmanager changed too
 */
(function($) {

$.widget("ui.droppable", {

	_init: function() {

		var o = this.options, accept = o.accept;
		this.overState = false; // Instead of properties this.isover and this.isout

		this.options.accept = this.options.accept && $.isFunction(this.options.accept) ? this.options.accept : function(d) {
			return d.is(accept);
		};

		//Store the droppable's proportions
		this.proportions = { width: this.element[0].offsetWidth, height: this.element[0].offsetHeight };

		// Add the reference and positions to the manager
		$.ui.ddmanager.droppables[this.options.scope] = $.ui.ddmanager.droppables[this.options.scope] || [];
		$.ui.ddmanager.droppables[this.options.scope].push(this);

		(this.options.addClasses && this.element.addClass("ui-droppable"));

	},

	destroy: function() {
		var drop = $.ui.ddmanager.droppables[this.options.scope];
		for ( var i = 0; i < drop.length; i++ )
			if ( drop[i] == this )
				drop.splice(i, 1);

		this.element
			.removeClass("ui-droppable ui-droppable-disabled")
			.removeData("droppable")
			.unbind(".droppable");
	},

	_setData: function(key, value) {

		if(key == 'accept') {
			this.options.accept = value && $.isFunction(value) ? value : function(d) {
				return d.is(value);
			};
		} else {
			$.widget.prototype._setData.apply(this, arguments);
		}

	},

	_activate: function(event) {
		var draggable = $.ui.ddmanager.current;
		if(this.options.activeClass) this.element.addClass(this.options.activeClass);
		(draggable && this._trigger('activate', event, this.ui(draggable)));
	},

	_deactivate: function(event) {
		var draggable = $.ui.ddmanager.current;
		if(this.options.activeClass) this.element.removeClass(this.options.activeClass);
		(draggable && this._trigger('deactivate', event, this.ui(draggable)));
	},

	_over: function(event, newOverState) {

		var draggable = $.ui.ddmanager.current;
		if (!draggable || (draggable.currentItem || draggable.element)[0] == this.element[0]) return; // Bail if draggable and droppable are same element

		if (this.options.accept.call(this.element[0],(draggable.currentItem || draggable.element))) {
			if(this.options.hoverClass) this.element.addClass(this.options.hoverClass);
			this._trigger('over', event, this.ui(draggable, newOverState));
			// trigger over and out events for overState (overtop, overcenter and other - overState return from ui.intersect function)
			this._changeOverState(event, newOverState);
		}

	},

	_out: function(event, newOverState) {

		var draggable = $.ui.ddmanager.current;
		if (!draggable || (draggable.currentItem || draggable.element)[0] == this.element[0]) return; // Bail if draggable and droppable are same element

		if (this.options.accept.call(this.element[0],(draggable.currentItem || draggable.element))) {
			if(this.options.hoverClass) this.element.removeClass(this.options.hoverClass);
			// trigger over and out events for overState (overtop, overcenter and other - overState return from ui.intersect function)
			this._changeOverState(event, newOverState);
			this._trigger('out', event, this.ui(draggable));
		}
	},
	
	// trigger over and out events for overState (overtop, overcenter and other - overState return from ui.intersect function)
	_changeOverState: function(event, newOverState) {
		var draggable = $.ui.ddmanager.current;
		(this.overState && newOverState != this.overState && this._trigger('out' + this.overState, event, this.ui(draggable)));
		(newOverState && newOverState != this.overState && this._trigger('over' + newOverState, event, this.ui(draggable, newOverState)));
		this.overState = newOverState;
	},

	_drop: function(event,custom) {

		var draggable = custom || $.ui.ddmanager.current;
		if (!draggable || (draggable.currentItem || draggable.element)[0] == this.element[0]) return false; // Bail if draggable and droppable are same element

		var childrenIntersection = false;
		this.element.find(":data(droppable)").not(".ui-draggable-dragging").each(function() {
			var inst = $.data(this, 'droppable');
			if(inst.options.greedy && $.ui.intersect(draggable, $.extend(inst, { offset: inst.element.offset() }), inst.options.tolerance)) {
				childrenIntersection = true; return false;
			}
		});
		if(childrenIntersection) return false;

		if(this.options.accept.call(this.element[0],(draggable.currentItem || draggable.element))) {
			if(this.options.activeClass) this.element.removeClass(this.options.activeClass);
			if(this.options.hoverClass) this.element.removeClass(this.options.hoverClass);
			this._trigger('drop', event, this.ui(draggable));
			return this.element;
		}

		return false;

	},

	ui: function(c, overState) {
		return {
			draggable: (c.currentItem || c.element),
			helper: c.helper,
			position: c.position,
			absolutePosition: c.positionAbs, //deprecated
			offset: c.positionAbs,
			overState: overState || this.overState // It is added for possibility to check up an draggable object overstate on drop event
		};
	}

});

$.extend($.ui.droppable, {
	version: "1.7.1",
	eventPrefix: 'drop',
	defaults: {
		accept: '*',
		activeClass: false,
		addClasses: true,
		greedy: false,
		hoverClass: false,
		scope: 'default',
		tolerance: 'pointer'
	}
});

/*
changes:
now $.ui.intersect is object with intersect functions for each toleranceMode
you can add your tolerance mode functions:

$.extend($.ui.intersect, {
	yourToleranceModeFunction : function(draggable, droppable, x1, x2, y1, y2, l, r, t, b) {
		state = true;// or false or 'state'. if return string $.ui.ddmanager triggered event for that state: over+'state' and out+'state'
		return state;
	}
}
*/
$.ui.intersect = function(draggable, droppable, toleranceMode) {

	if (!droppable.offset) return false;

	var x1 = (draggable.positionAbs || draggable.position.absolute).left, x2 = x1 + draggable.helperProportions.width,
		y1 = (draggable.positionAbs || draggable.position.absolute).top, y2 = y1 + draggable.helperProportions.height;
	var l = droppable.offset.left, r = l + droppable.proportions.width,
		t = droppable.offset.top, b = t + droppable.proportions.height;

	if ($.ui.intersect[toleranceMode]) {
		return $.ui.intersect[toleranceMode].call($.ui.intersect, draggable, droppable, x1, x2, y1, y2, l, r, t, b);
	} else return false;

};

$.extend($.ui.intersect, {

	fit : function(draggable, droppable, x1, x2, y1, y2, l, r, t, b) {
		return (l < x1 && x2 < r && t < y1 && y2 < b);
	},

	intersect : function(draggable, droppable, x1, x2, y1, y2, l, r, t, b) {
		return (l < x1 + (draggable.helperProportions.width / 2) // Right Half
			&& x2 - (draggable.helperProportions.width / 2) < r // Left Half
			&& t < y1 + (draggable.helperProportions.height / 2) // Bottom Half
			&& y2 - (draggable.helperProportions.height / 2) < b ); // Top Half
	},

	pointer : function(draggable, droppable, x1, x2, y1, y2, l, r, t, b) {
		var draggableLeft = ((draggable.positionAbs || draggable.position.absolute).left + (draggable.clickOffset || draggable.offset.click).left),
			draggableTop = ((draggable.positionAbs || draggable.position.absolute).top + (draggable.clickOffset || draggable.offset.click).top),
		isOver = $.ui.isOver(draggableTop, draggableLeft, t, l, droppable.proportions.height, droppable.proportions.width);
		return isOver;
	},

	touch : function(draggable, droppable, x1, x2, y1, y2, l, r, t, b) {
		return (
			(y1 >= t && y1 <= b) ||	// Top edge touching
			(y2 >= t && y2 <= b) ||	// Bottom edge touching
			(y1 < t && y2 > b)		// Surrounded vertically
		) && (
			(x1 >= l && x1 <= r) ||	// Left edge touching
			(x2 >= l && x2 <= r) ||	// Right edge touching
			(x1 < l && x2 > r)		// Surrounded horizontally
		);
	},

	around : function(draggable, droppable, x1, x2, y1, y2, l, r, t, b) {
		//console.log(y1+"  "+y2+"   "+t+"   "+b)
		t = t -15
		b = b -14
 		l = l - 15
		var middle_y = y1 /* + (y2-y1)/2*/, middle_x = x1/* + (x2-x1)/2*/;
		var height = b - t, width = r - l;
	
		var normalize = function(n, m) {
			if (n == undefined || n == null) return 0;
			if (typeof n === 'number') return n;
			var _n = parseInt(n);
			if (/px/i.test(n)) return _n;
			else if (/%/.test(n)) return _n * m / 100;
			else return _n;
		}

		var top = normalize(droppable.options.aroundTop, height) + t, bottom  = b - normalize(droppable.options.aroundBottom, height);
		var left = normalize(droppable.options.aroundLeft, width) + l, right = r - normalize(droppable.options.aroundRight, width);
		
		var over_location = ((middle_y > t && middle_y <= top) && (middle_x > left && middle_x < right)) ? 'top' : 
			((middle_y > top && middle_y < bottom) && (middle_x > left && middle_x < right)) ? 'center' :
			((middle_y >= bottom && middle_y < b) && (middle_x > left && middle_x < right)) ? 'bottom' :
			((middle_y > t && middle_y < b) && (middle_x > l && middle_x < left)) ? 'left' :
			((middle_y > t && middle_y < b) && (middle_x > right && middle_x < r)) ? 'right' :
			false;

		return over_location;
	}

});
/*
	This manager tracks offsets of draggables and droppables
*/
$.ui.ddmanager = {
	current: null,
	droppables: { 'default': [] },
	prepareOffsets: function(t, event) {

		var m = $.ui.ddmanager.droppables[t.options.scope];
		var type = event ? event.type : null; // workaround for #2317
		var list = (t.currentItem || t.element).find(":data(droppable)").andSelf();

		droppablesLoop: for (var i = 0; i < m.length; i++) {

			if(m[i].options.disabled || (t && !m[i].options.accept.call(m[i].element[0],(t.currentItem || t.element)))) continue;	//No disabled and non-accepted
			for (var j=0; j < list.length; j++) { if(list[j] == m[i].element[0]) { m[i].proportions.height = 0; continue droppablesLoop; } }; //Filter out elements in the current dragged item
			m[i].visible = m[i].element.css("display") != "none"; if(!m[i].visible) continue; 									//If the element is not visible, continue

			m[i].offset = m[i].element.offset();
			m[i].proportions = { width: m[i].element[0].offsetWidth, height: m[i].element[0].offsetHeight };

			if(type == "mousedown") m[i]._activate.call(m[i], event); //Activate the droppable if used directly from draggables

		}

	},
	drop: function(draggable, event) {

		var dropped = false;
		$.each($.ui.ddmanager.droppables[draggable.options.scope], function() {

			if(!this.options) return;
			if (!this.options.disabled && this.visible && $.ui.intersect(draggable, this, this.options.tolerance))
				dropped = this._drop.call(this, event);

			if (!this.options.disabled && this.visible && this.options.accept.call(this.element[0],(draggable.currentItem || draggable.element))) {
				this.overState = false; // reset overState for droppable object
				this._deactivate.call(this, event);
			}

		});
		return dropped;

	},
	drag: function(draggable, event) {

		//If you have a highly dynamic page, you might try this option. It renders positions every time you move the mouse.
		if(draggable.options.refreshPositions) $.ui.ddmanager.prepareOffsets(draggable, event);

		//Run through all droppables and check their positions based on specific tolerance options

		$.each($.ui.ddmanager.droppables[draggable.options.scope], function() {

			if (this.options.disabled || this.greedyChild || !this.visible) return;
			var intersects = $.ui.intersect(draggable, this, this.options.tolerance);

			// inspect overState changes
			var c = !intersects && this.overState ? 'isout' : (intersects && !this.overState ? 'isover' : (intersects != this.overState ? 'changed' : null));
			if (!c) return;

			var parentInstance;
			if (this.options.greedy) {
				var parent = this.element.parents(':data(droppable):eq(0)');
				if (parent.length) {
					parentInstance = $.data(parent[0], 'droppable');
					parentInstance.greedyChild = (c == 'isover' ? 1 : 0);
				}
			}

			// we just moved into a greedy child
			if (parentInstance && c == 'isover') {
				parentInstance._out.call(parentInstance, event, false);
			}

			this[c == 'isover' ? '_over' : (c == 'changed' ? '_changeOverState' : '_out')].call(this, event, intersects);

			// we just moved out of a greedy child
			if (parentInstance && c == 'isout') {
				intersects = $.ui.intersect(draggable, parentInstance, parentInstance.options.tolerance);
				parentInstance._over.call(parentInstance, event, intersects);
			}
		});

	}
};
 

})(jQuery);
