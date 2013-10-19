'use strict';

define(
    ['jquery'],
    function($) {

        asyncTest('Valid JSON response', 1, function() {
            $.get('/api/pro/alessi-product-design').done(function(d) {
                console.dir(d)
                ok( d.length, 'Valid Response' )
                start()
            })
        })
    }
)
