/* init.js */
/*global document,window*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.@@hash',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.@@hash',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox',
        swipe : 'lib/swipe/swipe',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.@@hash',
        'app/collections/covergallery' : 'app/collections/covergallery.@@hash',
        'app/collections/films' : 'app/collections/films.@@hash',
        'app/collections/instagrams' : 'app/collections/instagrams.@@hash',
        'app/collections/photography' : 'app/collections/photography.@@hash',
        'app/collections/profile' : 'app/collections/profile.@@hash',
        'app/collections/projects' : 'app/collections/projects.@@hash',
        'app/collections/showcases' : 'app/collections/showcases.@@hash',
        'app/models/cover' : 'app/models/cover.@@hash',
        'app/models/film' : 'app/models/film.@@hash',
        'app/models/photo' : 'app/models/photo.@@hash',
        'app/models/profile' : 'app/models/profile.@@hash',
        'app/models/project' : 'app/models/project.@@hash',
        'app/models/searchQuery' : 'app/models/searchQuery.@@hash',
        'app/models/showcase' : 'app/models/showcase.@@hash',
        'app/views/chrome' : 'app/views/chrome.@@hash',
        //'app/views/details/album' : 'app/views/details/album.@@hash',
        'app/views/details/books' : 'app/views/details/books.@@hash',
        'app/views/details/films' : 'app/views/details/films.@@hash',
        'app/views/details/photography' : 'app/views/details/photography.@@hash',
        'app/views/details/projects' : 'app/views/details/projects.@@hash',
        'app/views/partials/album' : 'app/views/partials/album.@@hash',
        'app/views/partials/filterviews' : 'app/views/partials/filterviews.@@hash',
        'app/views/partials/grid' : 'app/views/partials/grid.@@hash',
        'app/views/partials/jumplist' : 'app/views/partials/jumplist.@@hash',
        'app/views/sections/books' : 'app/views/sections/books.@@hash',
        'app/views/sections/contact' : 'app/views/sections/contact.@@hash',
        'app/views/sections/film' : 'app/views/sections/film.@@hash',
        'app/views/sections/home' : 'app/views/sections/home.@@hash',
        'app/views/sections/photography' : 'app/views/sections/photography.@@hash',
        'app/views/sections/profile' : 'app/views/sections/profile.@@hash',
        'app/views/sections/projects' : 'app/views/sections/projects.@@hash',
        'app/views/sections/search' : 'app/views/sections/search.@@hash',
        'app/views/sections/stream' : 'app/views/sections/stream.@@hash',
        'app/views/showcases/gallery' : 'app/views/showcases/gallery.@@hash',
        'app/views/showcases/list' : 'app/views/showcases/list.@@hash',
        'app/views/showcases/starfield' : 'app/views/showcases/starfield.@@hash',
        'app/views/showcases/text' : 'app/views/showcases/text.@@hash',
        'app/views/showcases/video' : 'app/views/showcases/video.@@hash',
        'utils/spinner' : 'utils/spinner.@@hash',
        'utils/quotes' : 'utils/quotes.@@hash',
        'utils/fbLoader' : 'utils/fbLoader.@@hash',
        'tpl/jst' : 'tpl/jst.@@hash'
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
