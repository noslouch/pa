/* init.js */
/*global document,window*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.1386456723909372935',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.1386456723909372935',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox',
        swipe : 'lib/swipe/swipe',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.1386456723909372935',
        'app/collections/covergallery' : 'app/collections/covergallery.1386456723909372935',
        'app/collections/films' : 'app/collections/films.1386456723909372935',
        'app/collections/instagrams' : 'app/collections/instagrams.1386456723909372935',
        'app/collections/photography' : 'app/collections/photography.1386456723909372935',
        'app/collections/profile' : 'app/collections/profile.1386456723909372935',
        'app/collections/projects' : 'app/collections/projects.1386456723909372935',
        'app/collections/showcases' : 'app/collections/showcases.1386456723909372935',
        'app/models/cover' : 'app/models/cover.1386456723909372935',
        'app/models/film' : 'app/models/film.1386456723909372935',
        'app/models/photo' : 'app/models/photo.1386456723909372935',
        'app/models/profile' : 'app/models/profile.1386456723909372935',
        'app/models/project' : 'app/models/project.1386456723909372935',
        'app/models/searchQuery' : 'app/models/searchQuery.1386456723909372935',
        'app/models/showcase' : 'app/models/showcase.1386456723909372935',
        'app/views/chrome' : 'app/views/chrome.1386456723909372935',
        //'app/views/details/album' : 'app/views/details/album.1386456723909372935',
        'app/views/details/books' : 'app/views/details/books.1386456723909372935',
        'app/views/details/films' : 'app/views/details/films.1386456723909372935',
        'app/views/details/photography' : 'app/views/details/photography.1386456723909372935',
        'app/views/details/projects' : 'app/views/details/projects.1386456723909372935',
        'app/views/partials/album' : 'app/views/partials/album.1386456723909372935',
        'app/views/partials/filterviews' : 'app/views/partials/filterviews.1386456723909372935',
        'app/views/partials/grid' : 'app/views/partials/grid.1386456723909372935',
        'app/views/partials/jumplist' : 'app/views/partials/jumplist.1386456723909372935',
        'app/views/sections/books' : 'app/views/sections/books.1386456723909372935',
        'app/views/sections/contact' : 'app/views/sections/contact.1386456723909372935',
        'app/views/sections/film' : 'app/views/sections/film.1386456723909372935',
        'app/views/sections/home' : 'app/views/sections/home.1386456723909372935',
        'app/views/sections/photography' : 'app/views/sections/photography.1386456723909372935',
        'app/views/sections/profile' : 'app/views/sections/profile.1386456723909372935',
        'app/views/sections/projects' : 'app/views/sections/projects.1386456723909372935',
        'app/views/sections/search' : 'app/views/sections/search.1386456723909372935',
        'app/views/sections/stream' : 'app/views/sections/stream.1386456723909372935',
        'app/views/showcases/gallery' : 'app/views/showcases/gallery.1386456723909372935',
        'app/views/showcases/list' : 'app/views/showcases/list.1386456723909372935',
        'app/views/showcases/starfield' : 'app/views/showcases/starfield.1386456723909372935',
        'app/views/showcases/text' : 'app/views/showcases/text.1386456723909372935',
        'app/views/showcases/video' : 'app/views/showcases/video.1386456723909372935',
        'utils/spinner' : 'utils/spinner.1386456723909372935',
        'utils/quotes' : 'utils/quotes.1386456723909372935',
        'utils/fbLoader' : 'utils/fbLoader.1386456723909372935',
        'tpl/jst' : 'tpl/jst.1386456723909372935'
    },
    map : {
        '*' : {
            'is' : 'lib/require-is/is'
        }
    },
    config : {
        'lib/require-is/is' : {
            mobile : 'ontouchstart' in window
        }
    },
    shim : {
        'jquery': {
            exports: '$'
        },
        'isotope' : ['jquery'],
        'bbq' : {
            deps : ['jquery']
        },
        'foundation' : {
            deps : ['jquery']
        },
        'tooltips' : {
            deps : ['jquery', 'foundation']
        },
        'fancybox' : {
            deps : ['jquery']
        },
        'json' : {
            deps : ['jquery']
        }
    },
    waitSeconds : 20
})

require( ['jquery', 'underscore', 'backbone', 'app/router', 'lib/fastclick/fastclick.min', 'app/views/chrome'],
function( $, _, Backbone, Router, fastClick ){
    Backbone.history.start({ pushState : true, root : '/' })
    fastClick.attach(document.body)
} )
