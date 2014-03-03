/*global Modernizr*/
/* utils/quotes.js
 * Homepage quotes */
'use strict';

define([
    'jquery',
    'underscore',
    'lib/requirejs/domReady!'
], function( $, _ ) {

    function Slide(h3, gallery){
        var self = this
        self.$el = $(h3).parent()
        self.blinds = _.toArray( h3.children )
        self.g = gallery

        _.each( self.blinds, function(blind, index) {
            if ( $(blind).html()[0] === '*' ) {
                $(blind).empty()
            }
        })

        function open(blindIndex){
            if ( blindIndex === self.blinds.length ) {
                // console.log('returning')
                if ( $(self.blinds).hasClass('closed') ) {
                    // console.log('if everything isn\t open, needs a restart')
                }
                return
            }
            // console.log('opening blind:', self.blinds[blindIndex].innerHTML)
            $(self.blinds[blindIndex]).addClass('opened ')
            $(self.blinds[blindIndex]).removeClass('closed')
            setTimeout(open.bind(self,blindIndex+1), 110)
        }

        function close(blindIndex){
            if ( blindIndex === self.blinds.length ) {
                // console.log('returning')
                if ( $(self.blinds).hasClass('opened') ) {
                    // console.log('if everything isn\'t closed, needs a restart')
                }
                return
            }
            // console.log('closing blind:', self.blinds[blindIndex].innerHTML)
            $(self.blinds[blindIndex]).removeClass('opened')
            $(self.blinds[blindIndex]).addClass('closed')
            setTimeout(close.bind(self,blindIndex+1), 110)
        }

        self.staggerOpen = function(){
            // console.log('opening slide:', self)
            open(0)
        }

        self.staggerClose = function(){
            // console.log('closing slide:', self)
            close(0)
        }

    }

    Slide.prototype.lastBlind = function(){
        return this.blinds[this.blinds.length-1]
    }

    Slide.prototype.getBlind = function(n){
        return this.blinds[n-1]
    }

    Slide.prototype.animate = function(){
        // console.log('calling animate on:', this)
        this.g.openSlide = this
        setTimeout(this.staggerClose, 4500)
        this.staggerOpen()
        this.$el.removeClass('closed')
    }

    function Gallery(c){
        var self = this,
            quotes = $(c).find('h3'),
            i,
            dotHandler,
            galleryHandler,
            transEndEventName,
            transEndEventNames

        // Slides array for reference
        this.slides = []

        // Animation Queue
        this.q = []


        for ( i = 0; i < quotes.length; i++ ){
            this.slides.push( new Slide(quotes[i], self) )
            this.q.push(i)
        }

        dotHandler = function(dot){
            // get current li based on front of queue
            var li = self.els[self.q[0]]

            $(self.els).removeClass('active-slide')
            var p = $(li).addClass('active-slide').position()
            $('#dot').animate({
                left: p.left
            })
        }

        galleryHandler = function(e){
            // console.log('tranitionend handler')
            if ( e.target.nodeName.toLowerCase() === 'a' &&
                    !e.target.classList.contains('back') ||
                    e.target.id === 'bullets' ) {
                // console.log('do nothing')
                return 
            }
            // console.log('check slides')
            var openSlide = self.getCurrent(),
                lastBlind = openSlide.lastBlind(),
                thirdFromEnd = openSlide.getBlind(openSlide.blinds.length-1),
                currentBlind = e.target

            if ( ( self.getCurrent().blinds.length === 1 && $(openSlide.blinds[0]).hasClass('closed') ) ||
                 currentBlind === thirdFromEnd && $(thirdFromEnd).hasClass('closed') ) {
                if (self.q.userChoice){
                    // userChoice flags when the user selects a specific slide
                    // the flag notifies us to override the typical update operation,
                    // which would just load whatever is sequentially next.
                    self.getNext().animate()
                    self.q.userChoice = false
                    openSlide.$el.addClass('closed')
                } else {
                    // otherwise call update(), which updates the queue with the
                    // following slide in sequential order
                    //
                    // there's probably a way to refactor this so we can respect
                    // user choices without needing to force an override.
                    // console.log('need to grab next slide in queue and start animation')
                    self.update().animate()
                    openSlide.$el.addClass('closed')
                }
                dotHandler()
            }
        }

        transEndEventNames = {
            'WebkitTransition' : 'webkitTransitionEnd',// Saf 6, Android Browser
            'MozTransition'    : 'transitionend',      // only for FF < 15
            'transition'       : 'transitionend'       // IE10, Opera, Chrome, FF 15+, Saf 7+
        }
        transEndEventName = transEndEventNames[ Modernizr.prefixed('transition') ];

        $(c).on(transEndEventName, galleryHandler)
        // $(c).on('transitionend', galleryHandler)
        // $(c).on('webkitTransitionEnd', galleryHandler)
        // $(c).on('transitionEnd', galleryHandler)

        $(c).on('click', '.indicators a', function(e){
            e.preventDefault()
            self.update(e.target.id)
            dotHandler()
        })
    }

    Gallery.prototype.getCurrent = function(){
        return this.openSlide || this.slides[0]
    }

    Gallery.prototype.getNext = function(){
        if (this.openSlide) {
            return this.slides[this.q[0]]
        } else {
            return this.slides[1]
        }
    }

    Gallery.prototype.update = function(){
        if (arguments.length < 1) {
            // console.log('calling update')
            // move closed slide to end of queue
            // next slide to open moves to first position
            var justClosed = this.q.shift()
            this.q.push(justClosed)
        } else {
            // move user selected slide to first position
            var nextIndex = parseInt(arguments[0], 10)
            var end = this.q.splice(0, this.q.indexOf(nextIndex))
            this.q = this.q.concat(end)
            // userChoice flag to override standard update calls
            this.q.userChoice = true
        }
        // next slide, either standard sequence or user-selected is in q[0]
        return this.slides[this.q[0]]
    }

    Gallery.prototype.getQueue = function(){
        return this.q
    }

    Gallery.prototype.init = function(){
        var $ul = $('<ul/>'),
            $dot = $('<div/>').attr('id', 'dot').addClass('dot')

        $ul.append($dot)

        for (var i = 0; i < this.slides.length; i++){
            var $li = $('<li/>')
            if (i === 0) {
                $li.addClass('active-slide')
            }
            var $a = $('<a/>').attr('id', i)
            $li.append($a)
            $ul.append($li)
        }
        $ul.appendTo('#bullets')

        // Slide bullets
        this.els = $('#bullets li')

        this.getCurrent().animate()
    }

    function checkQuoteHeight(){

        function inspect() {
            $('#quotes').toggleClass('small', $('#quotes').height() < 370)
            setTimeout(inspect, 150)
        }

        inspect()
    }

    return {
        Quotes : Gallery,
        inspector : checkQuoteHeight
    }
})
