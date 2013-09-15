"use strict";

function checkScroll(){

    function inspect(){
        $('.header-wrapper').toggleClass('shadow', window.scrollY > 0)
        setTimeout(inspect, 150) 
    }

    setTimeout(inspect, 150) 
}

function checkQuoteHeight(){

    function inspect() {
        $('#quotes').toggleClass('small', $('#quotes').height() < 370)
        setTimeout(inspect, 150)
    }

    inspect()
}


