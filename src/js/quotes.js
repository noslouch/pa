"use strict";
/*jshint -W002*/

var quotes = $('.quotes h3'),
    container = document.getElementById('container')


function Slide(h1, gallery){
    var self = this
    self.blinds = h1.children
    self.g = gallery

    function open(blindIndex){
        if ( blindIndex === self.blinds.length ) {
            return
        }
        $(self.blinds[blindIndex]).addClass('opened ')
        $(self.blinds[blindIndex]).removeClass('closed')
        setTimeout(open.bind(self,blindIndex+1), 110)
    }

    function close(blindIndex){
        if ( blindIndex === self.blinds.length ) {
            return
        }
        $(self.blinds[blindIndex]).removeClass('opened')
        $(self.blinds[blindIndex]).addClass('closed')
        setTimeout(close.bind(self,blindIndex+1), 110)
    }

    self.staggerOpen = function(){
        open(0)
    }

    self.staggerClose = function(){
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
    this.g.openSlide = this
    setTimeout(this.staggerClose, 2500)
    this.staggerOpen()
}

function Gallery(c){
    var self = this

    // Slides array for reference
    this.slides = []

    // Animation Queue
    this.q = []

    for (var i = 0; i < quotes.length; i++){
        this.slides.push(new Slide(quotes[i],self))
        this.q.push(i)
    }

    var galleryHandler = function(e){
        var openSlide = self.getCurrent(),
            lastBlind = openSlide.lastBlind(),
            thirdFromEnd = openSlide.getBlind(openSlide.blinds.length-1),
            currentBlind = e.target


        if (currentBlind === thirdFromEnd && $(thirdFromEnd).hasClass('closed')) {
            if (self.q.userChoice){
                self.getNext().animate()
                self.q.userChoice = false
            } else {
                self.update().animate()
            }

            var els = $('#bullets li')
            var li = els[self.q[0]]
            $(els).removeClass('active-slide')
            var p = $(li).addClass('active-slide').position()
            $('#dot').animate({
                top: p.top
            })
        }

    }

    $(c).on('webkitTransitionEnd', galleryHandler)
    $(c).on('transitionEnd', galleryHandler)
    $(c).on('transitionend', galleryHandler)

    $(c).on('click', 'a', function(e){
        e.preventDefault()
        self.update(e.target.id)
            var els = $('#bullets li')
            var li = els[self.q[0]]
            $(els).removeClass('active-slide')
            var p = $(li).addClass('active-slide').position()
            $('#dot').animate({
                top: p.top
            })
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
        // move closed slide to end of queue
        // next slide to open moves to first position
        var justClosed = this.q.shift()
        this.q.push(justClosed)
    } else {
        // move user selected slide to first position
        var nextIndex = parseInt(arguments[0], 10)
        var end = this.q.splice(0, this.q.indexOf(nextIndex))
        this.q = this.q.concat(end)
        // userChoice flag to override other update calls
        this.q.userChoice = true
    }
    // next slide, either sequential or user-selected is in q[0]
    return this.slides[this.q[0]]
}

Gallery.prototype.getQueue = function(){
    return this.q
}

Gallery.prototype.bullets = function(){
    var $ul = $('<ul/>')
    var $wrap = $('<div />').addClass('wrapper')
    var $dot = $('<div/>').attr('id', 'dot').addClass('dot')
    $ul.appendTo($wrap)
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
    $wrap.appendTo('#bullets')

    this.getCurrent().animate()
}

var g = new Gallery(container)
g.bullets()
