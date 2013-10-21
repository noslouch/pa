'use strict';

require.config({
    baseUrl : '../../js/',
    paths : {
        //'QUnit' : '/testing/js/libs/qunit',
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox.pack',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter'
    },
    shim : {
        /*
        'QUnit' : {
            exports : 'QUnit',
            init : function() {
                QUnit.config.autoload = false
                QUnit.config.autostart = false
            }
        },
        */
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
    }
})

require([
    '/testing/js/tests/projectModel',
    '/testing/js/tests/singleproject',
    '/testing/js/tests/albumModel',
    '/testing/js/tests/photoHome',
    '/testing/js/tests/singlealbum',
    '/testing/js/tests/filmHome',
    '/testing/js/tests/singlefilm',
    '/testing/js/tests/profile'
    ],
    function() {
        QUnit.start()
    }
)
