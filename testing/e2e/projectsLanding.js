'use strict';

module.exports = {
    "Cover Images by default" : function(browser) {
        browser
            .url('http://pa.dev')
            .click('#projects')
            .waitForElementVisible('.thumb:first-child', 5000)
            .assert.urlContains('filter=*')
            .assert.urlContains('sort=name')
            .assert.urlContains('view=cover')
            .click('#sorts h3').click('#date', function() {
                var first, last
                this.getAttribute('.thumb:first-child .year', 'id', function(result) {
                    first = result.value
                })
                this.getAttribute('.thumb:last-child .year', 'id', function(result) {
                    last = result.value
                })
                this.pause(1000)
                console.log(first > last)
                //this.assert(lastDate > firstDate)
            })
            .end()
    }
}
