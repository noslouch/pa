/* init.js */
/*global document,window*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.1399065019155535623',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.1399065019155535623',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : '../bower_components/foundation/js/foundation.min',
        tooltips : '../bower_components/foundation/js/foundation/foundation.tooltip',
        fancybox : 'lib/fancybox/jquery.fancybox',
        swipe : 'lib/swipe/swipe',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        mixitup : '../bower_components/mixitup/jquery.mixitup.min',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.1399065019155535623',
        'app/collections/covergallery' : 'app/collections/covergallery.1399065019155535623',
        'app/collections/films' : 'app/collections/films.1399065019155535623',
        'app/collections/instagrams' : 'app/collections/instagrams.1399065019155535623',
        'app/collections/photography' : 'app/collections/photography.1399065019155535623',
        'app/collections/profile' : 'app/collections/profile.1399065019155535623',
        'app/collections/projects' : 'app/collections/projects.1399065019155535623',
        'app/collections/showcases' : 'app/collections/showcases.1399065019155535623',
        'app/models/cover' : 'app/models/cover.1399065019155535623',
        'app/models/film' : 'app/models/film.1399065019155535623',
        'app/models/photo' : 'app/models/photo.1399065019155535623',
        'app/models/profile' : 'app/models/profile.1399065019155535623',
        'app/models/project' : 'app/models/project.1399065019155535623',
        'app/models/searchQuery' : 'app/models/searchQuery.1399065019155535623',
        'app/models/showcase' : 'app/models/showcase.1399065019155535623',
        'app/views/chrome' : 'app/views/chrome.1399065019155535623',
        //'app/views/details/album' : 'app/views/details/album.1399065019155535623',
        'app/views/details/books' : 'app/views/details/books.1399065019155535623',
        'app/views/details/films' : 'app/views/details/films.1399065019155535623',
        'app/views/details/photography' : 'app/views/details/photography.1399065019155535623',
        'app/views/details/projects' : 'app/views/details/projects.1399065019155535623',
        'app/views/partials/album' : 'app/views/partials/album.1399065019155535623',
        'app/views/partials/filterviews' : 'app/views/partials/filterviews.1399065019155535623',
        'app/views/partials/grid' : 'app/views/partials/grid.1399065019155535623',
        'app/views/partials/jumplist' : 'app/views/partials/jumplist.1399065019155535623',
        'app/views/sections/books' : 'app/views/sections/books.1399065019155535623',
        'app/views/sections/contact' : 'app/views/sections/contact.1399065019155535623',
        'app/views/sections/film' : 'app/views/sections/film.1399065019155535623',
        'app/views/sections/home' : 'app/views/sections/home.1399065019155535623',
        'app/views/sections/photography' : 'app/views/sections/photography.1399065019155535623',
        'app/views/sections/profile' : 'app/views/sections/profile.1399065019155535623',
        'app/views/sections/projects' : 'app/views/sections/projects.1399065019155535623',
        'app/views/sections/search' : 'app/views/sections/search.1399065019155535623',
        'app/views/sections/stream' : 'app/views/sections/stream.1399065019155535623',
        'app/views/showcases/gallery' : 'app/views/showcases/gallery.1399065019155535623',
        'app/views/showcases/list' : 'app/views/showcases/list.1399065019155535623',
        'app/views/showcases/starfield' : 'app/views/showcases/starfield.1399065019155535623',
        'app/views/showcases/text' : 'app/views/showcases/text.1399065019155535623',
        'app/views/showcases/video' : 'app/views/showcases/video.1399065019155535623',
        'utils/spinner' : 'utils/spinner.1399065019155535623',
        'utils/quotes' : 'utils/quotes.1399065019155535623',
        'utils/fbLoader' : 'utils/fbLoader.1399065019155535623',
        'tpl/jst' : 'tpl/jst.1399065019155535623'
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
