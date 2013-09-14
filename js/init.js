/* init.js */
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone',
        isotope : 'lib/library/isotope/jquery.isotope.min.js',
        fancybox : 'lib/library/fancybox/jquery.fancybox.pack.js',
        bbq : 'lib/library/bbq/ba-bbq.no-legacy.js',
        foundation : 'lib/library/foundation/foundation.js',
        tooltips : 'lib/library/foundatino/foundation.tooltips.js'
    }
})

require( ['jquery', 'underscore', 'backbone', 'app'],
function( $, _, Backbone, App ) {
    App.init()
} )
