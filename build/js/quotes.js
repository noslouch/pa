"use strict";
/*jshint -W002*/

var quotes = $('.quotes h3'),
    container = document.getElementById('container'),
    slides = [],
    animation


function Slide(h1){
    var openFlag = false
    var self = this
    self.blinds = h1.children

    self.init()
    
    function open(index){
        if ( index === self.blinds.length ) {
            return
        }
        $(self.blinds[index]).addClass('opened ')
        $(self.blinds[index]).removeClass('closed')
        setTimeout(open.bind(self,index+1), 110)
    }

    function close(index){
        if ( index === self.blinds.length ) {
            return
        }
        $(self.blinds[index]).removeClass('opened')
        $(self.blinds[index]).addClass('closed')
        setTimeout(close.bind(self,index+1), 110)
    }

    self.staggerOpen = function(){
        openFlag = true
        open(0)
    }

    self.staggerClose = function(){
        openFlag = false
        close(0)
    }

    self.isOpen = function() {
        return openFlag
    }

    self.lastBlind = function(){
        return self.blinds[self.blinds.length-1]
    }
}

Slide.prototype.init = function(){
    Slide.prototype.slides = Slide.prototype.slides || []
    Slide.prototype.slides.push(this)
}


Slide.prototype.getCurrent = function(){
    var current;
    var slides = Slide.prototype.slides
    for (var i = 0; i < slides.length; i++){
        if (slides[i].isOpen()){
            current = slides[i]
            break
        }
    }
    if (!current){
        current = slides[0]
    }

    return current
}

Slide.prototype.getNext = function(){
    var current = Slide.prototype.getCurrent()
    var next;

    for (var i = 0; i < Slide.prototype.slides.length; i++){
        if (slides[i] === current){
            next = slides[i+1]
        }
    }

    return next
}

function animationHandler(e){
    var slide = Slide.prototype.getCurrent(),
        next = Slide.prototype.getNext(),
        lastBlind = slide.lastBlind(),
        blind = e.target

    if (blind === lastBlind && $(lastBlind).hasClass('opened')) {
        setTimeout(slide.staggerClose, 2000)
        try {
            animation = setTimeout(next.staggerOpen, 4500)
        } catch(e) {
            next = Slide.prototype.slides[0]
            animation = setTimeout(next.staggerOpen, 4500)
        }

    }
}

$(container).on('webkitTransitionEnd', animationHandler)
$(container).on('transitionEnd', animationHandler)
$(container).on('transitionend', animationHandler)

/*
function valueSwap($input1, $input2){
    $input1.change(function(e){
        $input2.val(e.target.value)
    })
    $input2.change(function(e){
        $input1.val(e.target.value)
    })
}
*/


$(function(){

    for (var i = 0; i < quotes.length; i++){
        slides.push(new Slide(quotes[i]))
    }

    slides[0].staggerOpen()
})
