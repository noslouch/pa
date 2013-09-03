this.Handlebars={};
(function(b){b.VERSION="1.0.0-rc.3";b.COMPILER_REVISION=2;b.REVISION_CHANGES={1:"<= 1.0.rc.2",2:">= 1.0.0-rc.3"};b.helpers={};b.partials={};b.registerHelper=function(a,c,b){b&&(c.not=b);this.helpers[a]=c};b.registerPartial=function(a,c){this.partials[a]=c};b.registerHelper("helperMissing",function(a){if(2!==arguments.length)throw Error("Could not find property '"+a+"'");});var d=Object.prototype.toString;b.registerHelper("blockHelperMissing",function(a,c){var e=c.inverse||function(){},g=c.fn,f=d.call(a);
"[object Function]"===f&&(a=a.call(this));return!0===a?g(this):!1===a||null==a?e(this):"[object Array]"===f?0<a.length?b.helpers.each(a,c):e(this):g(a)});b.K=function(){};b.createFrame=Object.create||function(a){b.K.prototype=a;a=new b.K;b.K.prototype=null;return a};b.logger={DEBUG:0,INFO:1,WARN:2,ERROR:3,level:3,methodMap:{"0":"debug",1:"info",2:"warn",3:"error"},log:function(a,c){if(b.logger.level<=a){var e=b.logger.methodMap[a];"undefined"!==typeof console&&console[e]&&console[e].call(console,
c)}}};b.log=function(a,c){b.logger.log(a,c)};b.registerHelper("each",function(a,c){var e=c.fn,d=c.inverse,f=0,h="",j;c.data&&(j=b.createFrame(c.data));if(a&&"object"===typeof a)if(a instanceof Array)for(var k=a.length;f<k;f++)j&&(j.index=f),h+=e(a[f],{data:j});else for(k in a)a.hasOwnProperty(k)&&(j&&(j.key=k),h+=e(a[k],{data:j}),f++);0===f&&(h=d(this));return h});b.registerHelper("if",function(a,c){"[object Function]"===d.call(a)&&(a=a.call(this));return!a||b.Utils.isEmpty(a)?c.inverse(this):c.fn(this)});
b.registerHelper("unless",function(a,c){var e=c.fn;c.fn=c.inverse;c.inverse=e;return b.helpers["if"].call(this,a,c)});b.registerHelper("with",function(a,c){return c.fn(a)});b.registerHelper("log",function(a,c){var e=c.data&&null!=c.data.level?parseInt(c.data.level,10):1;b.log(e,a)})})(this.Handlebars);var errorProps="description fileName lineNumber message name number stack".split(" ");
Handlebars.Exception=function(b){for(var d=Error.prototype.constructor.apply(this,arguments),a=0;a<errorProps.length;a++)this[errorProps[a]]=d[errorProps[a]]};Handlebars.Exception.prototype=Error();Handlebars.SafeString=function(b){this.string=b};Handlebars.SafeString.prototype.toString=function(){return this.string.toString()};
(function(){var b={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#x27;","`":"&#x60;"},d=/[&<>"'`]/g,a=/[&<>"'`]/,c=function(a){return b[a]||"&amp;"};Handlebars.Utils={escapeExpression:function(b){return b instanceof Handlebars.SafeString?b.toString():null==b||!1===b?"":!a.test(b)?b:b.replace(d,c)},isEmpty:function(a){return!a&&0!==a?!0:"[object Array]"===Object.prototype.toString.call(a)&&0===a.length?!0:!1}}})();
Handlebars.VM={template:function(b){var d={escapeExpression:Handlebars.Utils.escapeExpression,invokePartial:Handlebars.VM.invokePartial,programs:[],program:function(a,b,e){var d=this.programs[a];if(e)return Handlebars.VM.program(b,e);d||(d=this.programs[a]=Handlebars.VM.program(b));return d},programWithDepth:Handlebars.VM.programWithDepth,noop:Handlebars.VM.noop,compilerInfo:null};return function(a,c){c=c||{};var e=b.call(d,Handlebars,a,c.helpers,c.partials,c.data),g=d.compilerInfo||[],f=g[0]||1,
h=Handlebars.COMPILER_REVISION;if(f!==h){if(f<h)throw"Template was precompiled with an older version of Handlebars than the current runtime. Please update your precompiler to a newer version ("+Handlebars.REVISION_CHANGES[h]+") or downgrade your runtime to an older version ("+Handlebars.REVISION_CHANGES[f]+").";throw"Template was precompiled with a newer version of Handlebars than the current runtime. Please update your runtime to a newer version ("+g[1]+").";}return e}},programWithDepth:function(b,
d,a){var c=Array.prototype.slice.call(arguments,2);return function(a,g){g=g||{};return b.apply(this,[a,g.data||d].concat(c))}},program:function(b,d){return function(a,c){c=c||{};return b(a,c.data||d)}},noop:function(){return""},invokePartial:function(b,d,a,c,e,g){c={helpers:c,partials:e,data:g};if(void 0===b)throw new Handlebars.Exception("The partial "+d+" could not be found");if(b instanceof Function)return b(a,c);if(Handlebars.compile)return e[d]=Handlebars.compile(b,{data:void 0!==g}),e[d](a,
c);throw new Handlebars.Exception("The partial "+d+" could not be compiled when running in runtime-only mode");}};Handlebars.template=Handlebars.VM.template;



// Compare Helper
Handlebars.registerHelper('compare', function (lvalue, operator, rvalue, options) {

    var operators, result;

    if (arguments.length < 3) {
        throw new Error("Handlerbars Helper 'compare' needs 2 parameters");
    }

    if (options === undefined) {
        options = rvalue;
        rvalue = operator;
        operator = "===";
    }

    operators = {
        '==': function (l, r) { return l == r; },
        '===': function (l, r) { return l === r; },
        '!=': function (l, r) { return l != r; },
        '!==': function (l, r) { return l !== r; },
        '<': function (l, r) { return l < r; },
        '>': function (l, r) { return l > r; },
        '<=': function (l, r) { return l <= r; },
        '>=': function (l, r) { return l >= r; },
        'typeof': function (l, r) { return typeof l == r; }
    };

    if (!operators[operator]) {
        throw new Error("Handlerbars Helper 'compare' doesn't know the operator " + operator);
    }

    result = operators[operator](lvalue, rvalue);

    if (result) {
        return options.fn(this);
    } else {
        return options.inverse(this);
    }

});

// ideq helper
Handlebars.registerHelper('ifeq', function (a, b, options) {
  if (a == b) { return options.fn(this); }
});
