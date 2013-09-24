/*
 Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
*/
CKEDITOR.dialog.add("link",function(g){var p,q;function r(a){return a.replace(/'/g,"\\$&")}function t(a){var h,c=p,f,d;h=[q,"("];for(var b=0;b<c.length;b++)f=c[b].toLowerCase(),d=a[f],0<b&&h.push(","),h.push("'",d?r(encodeURIComponent(a[f])):"","'");h.push(")");return h.join("")}function u(a){for(var h,c=a.length,f=[],d=0;d<c;d++)h=a.charCodeAt(d),f.push(h);return"String.fromCharCode("+f.join(",")+")"}function v(a){return(a=a.getAttribute("class"))?a.replace(/\s*(?:cke_anchor_empty|cke_anchor)(?:\s*$)?/g,
""):""}var w=CKEDITOR.plugins.link,s=function(){var a=this.getDialog(),h=a.getContentElement("target","popupFeatures"),a=a.getContentElement("target","linkTargetName"),c=this.getValue();if(h&&a)switch(h=h.getElement(),h.hide(),a.setValue(""),c){case "frame":a.setLabel(g.lang.link.targetFrameName);a.getElement().show();break;case "popup":h.show();a.setLabel(g.lang.link.targetPopupName);a.getElement().show();break;default:a.setValue(c),a.getElement().hide()}},x=/^javascript:/,y=/^mailto:([^?]+)(?:\?(.+))?$/,
z=/subject=([^;?:@&=$,\/]*)/,A=/body=([^;?:@&=$,\/]*)/,B=/^#(.*)$/,C=/^((?:http|https|ftp|news):\/\/)?(.*)$/,D=/^(_(?:self|top|parent|blank))$/,E=/^javascript:void\(location\.href='mailto:'\+String\.fromCharCode\(([^)]+)\)(?:\+'(.*)')?\)$/,F=/^javascript:([^(]+)\(([^)]+)\)$/,G=/\s*window.open\(\s*this\.href\s*,\s*(?:'([^']*)'|null)\s*,\s*'([^']*)'\s*\)\s*;\s*return\s*false;*\s*/,H=/(?:^|,)([^=]+)=(\d+|yes|no)/gi,I=function(a,h){var c=h&&(h.data("cke-saved-href")||h.getAttribute("href"))||"",f,d,b=
{};c.match(x)&&("encode"==o?c=c.replace(E,function(a,b,c){return"mailto:"+String.fromCharCode.apply(String,b.split(","))+(c&&c.replace(/\\'/g,"'"))}):o&&c.replace(F,function(a,c,d){if(c==q){b.type="email";for(var a=b.email={},c=/(^')|('$)/g,d=d.match(/[^,\s]+/g),e=d.length,f,h,g=0;g<e;g++)f=decodeURIComponent,h=d[g].replace(c,"").replace(/\\'/g,"'"),h=f(h),f=p[g].toLowerCase(),a[f]=h;a.address=[a.name,a.domain].join("@")}}));if(!b.type)if(f=c.match(B))b.type="anchor",b.anchor={},b.anchor.name=b.anchor.id=
f[1];else if(f=c.match(y)){var e=c.match(z),c=c.match(A);b.type="email";d=b.email={};d.address=f[1];e&&(d.subject=decodeURIComponent(e[1]));c&&(d.body=decodeURIComponent(c[1]))}else{var g="",i;a:for(i in a.config.link_types)for(e in f=a.config.link_types[i],f)if(f[e].url==c){g=generateElementIdByLinkTypeName(i);break a}g?(b.type=g,b.customLinkUrl=c):c&&(d=c.match(C))?(b.type="url",b.url={},b.url.protocol=d[1],b.url.url=d[2]):b.type="url"}if(h){e=h.getAttribute("target");b.target={};b.adv={};if(e)e.match(D)?
b.target.type=b.target.name=e:(b.target.type="frame",b.target.name=e);else if(e=(e=h.data("cke-pa-onclick")||h.getAttribute("onclick"))&&e.match(G)){b.target.type="popup";for(b.target.name=e[1];c=H.exec(e[2]);)("yes"==c[2]||"1"==c[2])&&!(c[1]in{height:1,width:1,top:1,left:1})?b.target[c[1]]=!0:isFinite(c[2])&&(b.target[c[1]]=c[2])}e=function(a,c){var d=h.getAttribute(c);null!==d&&(b.adv[a]=d||"")};e("advId","id");e("advLangDir","dir");e("advAccessKey","accessKey");b.adv.advName=h.data("cke-saved-name")||
h.getAttribute("name")||"";e("advLangCode","lang");e("advTabIndex","tabindex");e("advTitle","title");e("advContentType","type");CKEDITOR.plugins.link.synAnchorSelector?b.adv.advCSSClasses=v(h):e("advCSSClasses","class");e("advCharset","charset");e("advStyles","style");e("advRel","rel")}var c=b.anchors=[],k;if(CKEDITOR.plugins.link.emptyAnchorFix){f=a.document.getElementsByTag("a");e=0;for(d=f.count();e<d;e++)if(k=f.getItem(e),k.data("cke-saved-name")||k.hasAttribute("name"))c.push({name:k.data("cke-saved-name")||
k.getAttribute("name"),id:k.getAttribute("id")})}else{f=new CKEDITOR.dom.nodeList(a.document.$.anchors);e=0;for(d=f.count();e<d;e++)k=f.getItem(e),c[e]={name:k.getAttribute("name"),id:k.getAttribute("id")}}if(CKEDITOR.plugins.link.fakeAnchor){f=a.document.getElementsByTag("img");e=0;for(d=f.count();e<d;e++)(k=CKEDITOR.plugins.link.tryRestoreFakeAnchor(a,f.getItem(e)))&&c.push({name:k.getAttribute("name"),id:k.getAttribute("id")})}this._.selectedElement=h;return b},j=function(a){a.target&&this.setValue(a.target[this.id]||
"")},l=function(a){a.adv&&this.setValue(a.adv[this.id]||"")},m=function(a){a.target||(a.target={});a.target[this.id]=this.getValue()||""},n=function(a){a.adv||(a.adv={});a.adv[this.id]=this.getValue()||""},o=g.config.emailProtection||"";o&&"encode"!=o&&(q=p=void 0,o.replace(/^([^(]+)\(([^)]+)\)$/,function(a,b,c){q=b;p=[];c.replace(/[^,\s]+/g,function(a){p.push(a)})}));var i=g.lang.common,b=g.lang.link;return{title:b.title,minWidth:350,minHeight:230,contents:[{id:"info",label:b.info,title:b.info,elements:[{id:"linkType",
type:"select",label:b.type,"default":"url",items:[[b.toUrl,"url"],[b.toAnchor,"anchor"],[b.toEmail,"email"]],onChange:function(){var a=this.getDialog(),b=["urlOptions","anchorOptions","emailOptions"],c=this.getValue(),f=a.definition.getContents("upload"),f=f&&f.hidden;if(c=="url"){g.config.linkShowTargetTab&&a.showPage("target");f||a.showPage("upload")}else{a.hidePage("target");f||a.hidePage("upload")}for(f=0;f<b.length;f++){var d=a.getContentElement("info",b[f]);if(d){d=d.getElement().getParent().getParent();
b[f]==c+"Options"?d.show():d.hide()}}a.layout()},setup:function(a){a.type&&this.setValue(a.type)},commit:function(a){a.type=this.getValue()}},{type:"vbox",id:"urlOptions",children:[{type:"hbox",widths:["25%","75%"],children:[{id:"protocol",type:"select",label:i.protocol,"default":"http://",items:[["http://‎","http://"],["https://‎","https://"],["ftp://‎","ftp://"],["news://‎","news://"],[b.other,""]],setup:function(a){a.url&&this.setValue(a.url.protocol||"")},commit:function(a){if(!a.url)a.url={};
a.url.protocol=this.getValue()}},{type:"text",id:"url",label:i.url,required:!0,onLoad:function(){this.allowOnChange=true},onKeyUp:function(){this.allowOnChange=false;var a=this.getDialog().getContentElement("info","protocol"),b=this.getValue(),c=/^((javascript:)|[#\/\.\?])/i,f=/^(http|https|ftp|news):\/\/(?=.)/i.exec(b);if(f){this.setValue(b.substr(f[0].length));a.setValue(f[0].toLowerCase())}else c.test(b)&&a.setValue("");this.allowOnChange=true},onChange:function(){if(this.allowOnChange)this.onKeyUp()},
validate:function(){var a=this.getDialog();if(a.getContentElement("info","linkType")&&a.getValueOf("info","linkType")!="url")return true;if(/javascript\:/.test(this.getValue())){alert(i.invalidValue);return false}return this.getDialog().fakeObj?true:CKEDITOR.dialog.validate.notEmpty(b.noUrl).apply(this)},setup:function(a){this.allowOnChange=false;a.url&&this.setValue(a.url.url);this.allowOnChange=true},commit:function(a){this.onChange();if(!a.url)a.url={};a.url.url=this.getValue();this.allowOnChange=
false}}],setup:function(){this.getDialog().getContentElement("info","linkType")||this.getElement().show()}},{type:"button",id:"browse",hidden:"true",filebrowser:"info:url",label:i.browseServer}]},{type:"vbox",id:"anchorOptions",width:260,align:"center",padding:0,children:[{type:"fieldset",id:"selectAnchorText",label:b.selectAnchor,setup:function(a){a.anchors.length>0?this.getElement().show():this.getElement().hide()},children:[{type:"hbox",id:"selectAnchor",children:[{type:"select",id:"anchorName",
"default":"",label:b.anchorName,style:"width: 100%;",items:[[""]],setup:function(a){this.clear();this.add("");for(var b=0;b<a.anchors.length;b++)a.anchors[b].name&&this.add(a.anchors[b].name);a.anchor&&this.setValue(a.anchor.name);(a=this.getDialog().getContentElement("info","linkType"))&&a.getValue()=="email"&&this.focus()},commit:function(a){if(!a.anchor)a.anchor={};a.anchor.name=this.getValue()}},{type:"select",id:"anchorId","default":"",label:b.anchorId,style:"width: 100%;",items:[[""]],setup:function(a){this.clear();
this.add("");for(var b=0;b<a.anchors.length;b++)a.anchors[b].id&&this.add(a.anchors[b].id);a.anchor&&this.setValue(a.anchor.id)},commit:function(a){if(!a.anchor)a.anchor={};a.anchor.id=this.getValue()}}],setup:function(a){a.anchors.length>0?this.getElement().show():this.getElement().hide()}}]},{type:"html",id:"noAnchors",style:"text-align: center;",html:'<div role="note" tabIndex="-1">'+CKEDITOR.tools.htmlEncode(b.noAnchors)+"</div>",focus:!0,setup:function(a){a.anchors.length<1?this.getElement().show():
this.getElement().hide()}}],setup:function(){this.getDialog().getContentElement("info","linkType")||this.getElement().hide()}},{type:"vbox",id:"emailOptions",padding:1,children:[{type:"text",id:"emailAddress",label:b.emailAddress,required:!0,validate:function(){var a=this.getDialog();return!a.getContentElement("info","linkType")||a.getValueOf("info","linkType")!="email"?true:CKEDITOR.dialog.validate.notEmpty(b.noEmail).apply(this)},setup:function(a){a.email&&this.setValue(a.email.address);(a=this.getDialog().getContentElement("info",
"linkType"))&&a.getValue()=="email"&&this.select()},commit:function(a){if(!a.email)a.email={};a.email.address=this.getValue()}},{type:"text",id:"emailSubject",label:b.emailSubject,setup:function(a){a.email&&this.setValue(a.email.subject)},commit:function(a){if(!a.email)a.email={};a.email.subject=this.getValue()}},{type:"textarea",id:"emailBody",label:b.emailBody,rows:3,"default":"",setup:function(a){a.email&&this.setValue(a.email.body)},commit:function(a){if(!a.email)a.email={};a.email.body=this.getValue()}}],
setup:function(){this.getDialog().getContentElement("info","linkType")||this.getElement().hide()}}]},{id:"target",requiredContent:"a[target]",label:b.target,title:b.target,elements:[{type:"hbox",widths:["50%","50%"],children:[{type:"select",id:"linkTargetType",label:i.target,"default":"notSet",style:"width : 100%;",items:[[i.notSet,"notSet"],[b.targetFrame,"frame"],[b.targetPopup,"popup"],[i.targetNew,"_blank"],[i.targetTop,"_top"],[i.targetSelf,"_self"],[i.targetParent,"_parent"]],onChange:s,setup:function(a){a.target&&
this.setValue(a.target.type||"notSet");s.call(this)},commit:function(a){if(!a.target)a.target={};a.target.type=this.getValue()}},{type:"text",id:"linkTargetName",label:b.targetFrameName,"default":"",setup:function(a){a.target&&this.setValue(a.target.name)},commit:function(a){if(!a.target)a.target={};a.target.name=this.getValue().replace(/\W/gi,"")}}]},{type:"vbox",width:"100%",align:"center",padding:2,id:"popupFeatures",children:[{type:"fieldset",label:b.popupFeatures,children:[{type:"hbox",children:[{type:"checkbox",
id:"resizable",label:b.popupResizable,setup:j,commit:m},{type:"checkbox",id:"status",label:b.popupStatusBar,setup:j,commit:m}]},{type:"hbox",children:[{type:"checkbox",id:"location",label:b.popupLocationBar,setup:j,commit:m},{type:"checkbox",id:"toolbar",label:b.popupToolbar,setup:j,commit:m}]},{type:"hbox",children:[{type:"checkbox",id:"menubar",label:b.popupMenuBar,setup:j,commit:m},{type:"checkbox",id:"fullscreen",label:b.popupFullScreen,setup:j,commit:m}]},{type:"hbox",children:[{type:"checkbox",
id:"scrollbars",label:b.popupScrollBars,setup:j,commit:m},{type:"checkbox",id:"dependent",label:b.popupDependent,setup:j,commit:m}]},{type:"hbox",children:[{type:"text",widths:["50%","50%"],labelLayout:"horizontal",label:i.width,id:"width",setup:j,commit:m},{type:"text",labelLayout:"horizontal",widths:["50%","50%"],label:b.popupLeft,id:"left",setup:j,commit:m}]},{type:"hbox",children:[{type:"text",labelLayout:"horizontal",widths:["50%","50%"],label:i.height,id:"height",setup:j,commit:m},{type:"text",
labelLayout:"horizontal",label:b.popupTop,widths:["50%","50%"],id:"top",setup:j,commit:m}]}]}]}]},{id:"upload",label:b.upload,title:b.upload,hidden:!0,filebrowser:"uploadButton",elements:[{type:"file",id:"upload",label:i.upload,style:"height:40px",size:29},{type:"fileButton",id:"uploadButton",label:i.uploadSubmit,filebrowser:"info:url","for":["upload","upload"]}]},{id:"advanced",label:b.advanced,title:b.advanced,elements:[{type:"vbox",padding:1,children:[{type:"hbox",widths:["45%","35%","20%"],children:[{type:"text",
id:"advId",requiredContent:"a[id]",label:b.id,setup:l,commit:n},{type:"select",id:"advLangDir",requiredContent:"a[dir]",label:b.langDir,"default":"",style:"width:110px",items:[[i.notSet,""],[b.langDirLTR,"ltr"],[b.langDirRTL,"rtl"]],setup:l,commit:n},{type:"text",id:"advAccessKey",requiredContent:"a[accesskey]",width:"80px",label:b.acccessKey,maxLength:1,setup:l,commit:n}]},{type:"hbox",widths:["45%","35%","20%"],children:[{type:"text",label:b.name,id:"advName",requiredContent:"a[name]",setup:l,commit:n},
{type:"text",label:b.langCode,id:"advLangCode",requiredContent:"a[lang]",width:"110px","default":"",setup:l,commit:n},{type:"text",label:b.tabIndex,id:"advTabIndex",requiredContent:"a[tabindex]",width:"80px",maxLength:5,setup:l,commit:n}]}]},{type:"vbox",padding:1,children:[{type:"hbox",widths:["45%","55%"],children:[{type:"text",label:b.advisoryTitle,requiredContent:"a[title]","default":"",id:"advTitle",setup:l,commit:n},{type:"text",label:b.advisoryContentType,requiredContent:"a[type]","default":"",
id:"advContentType",setup:l,commit:n}]},{type:"hbox",widths:["45%","55%"],children:[{type:"text",label:b.cssClasses,requiredContent:"a(cke-xyz)","default":"",id:"advCSSClasses",setup:l,commit:n},{type:"text",label:b.charset,requiredContent:"a[charset]","default":"",id:"advCharset",setup:l,commit:n}]},{type:"hbox",widths:["45%","55%"],children:[{type:"text",label:b.rel,requiredContent:"a[rel]","default":"",id:"advRel",setup:l,commit:n},{type:"text",label:b.styles,requiredContent:"a{cke-xyz}","default":"",
id:"advStyles",validate:CKEDITOR.dialog.validate.inlineStyle(g.lang.common.invalidInlineStyle),setup:l,commit:n}]}]}]}],onShow:function(){var a=this.getParentEditor(),b=a.getSelection(),c=null;(c=w.getSelectedLink(a))&&c.hasAttribute("href")?b.selectElement(c):c=null;this.setupContent(I.apply(this,[a,c]))},onOk:function(){var a={},b=[],c={},f=this.getParentEditor();this.commitContent(c);switch(c.type||"url"){case "url":var d=c.url&&c.url.protocol!=void 0?c.url.protocol:"http://",g=c.url&&CKEDITOR.tools.trim(c.url.url)||
"";a["data-cke-saved-href"]=g.indexOf("/")===0?g:d+g;break;case "anchor":d=c.anchor&&c.anchor.id;a["data-cke-saved-href"]="#"+(c.anchor&&c.anchor.name||d||"");break;case "email":var e=c.email,d=e.address;switch(o){case "":case "encode":var g=encodeURIComponent(e.subject||""),i=encodeURIComponent(e.body||""),e=[];g&&e.push("subject="+g);i&&e.push("body="+i);e=e.length?"?"+e.join("&"):"";if(o=="encode"){d=["javascript:void(location.href='mailto:'+",u(d)];e&&d.push("+'",r(e),"'");d.push(")")}else d=
["mailto:",d,e];break;default:d=d.split("@",2);e.name=d[0];e.domain=d[1];d=["javascript:",t(e)]}a["data-cke-saved-href"]=d.join("");break;default:g=c.customLinkUrl||"";a["data-cke-saved-href"]=g}if(c.target)if(c.target.type=="popup"){for(var d=["window.open(this.href, '",c.target.name||"","', '"],j=["resizable","status","location","toolbar","menubar","fullscreen","scrollbars","dependent"],g=j.length,e=function(a){c.target[a]&&j.push(a+"="+c.target[a])},i=0;i<g;i++)j[i]=j[i]+(c.target[j[i]]?"=yes":
"=no");e("width");e("left");e("height");e("top");d.push(j.join(","),"'); return false;");a["data-cke-pa-onclick"]=d.join("");b.push("target")}else{c.target.type!="notSet"&&c.target.name?a.target=c.target.name:b.push("target");b.push("data-cke-pa-onclick","onclick")}if(c.adv){d=function(d,e){var f=c.adv[d];f?a[e]=f:b.push(e)};d("advId","id");d("advLangDir","dir");d("advAccessKey","accessKey");c.adv.advName?a.name=a["data-cke-saved-name"]=c.adv.advName:b=b.concat(["data-cke-saved-name","name"]);d("advLangCode",
"lang");d("advTabIndex","tabindex");d("advTitle","title");d("advContentType","type");d("advCSSClasses","class");d("advCharset","charset");d("advStyles","style");d("advRel","rel")}d=f.getSelection();a.href=a["data-cke-saved-href"];if(this._.selectedElement){f=this._.selectedElement;g=f.data("cke-saved-href");e=f.getHtml();f.setAttributes(a);f.removeAttributes(b);c.adv&&(c.adv.advName&&CKEDITOR.plugins.link.synAnchorSelector)&&f.addClass(f.getChildCount()?"cke_anchor":"cke_anchor_empty");if(g==e||c.type==
"email"&&e.indexOf("@")!=-1)f.setHtml(c.type=="email"?c.email.address:a["data-cke-saved-href"]);d.selectElement(f);delete this._.selectedElement}else{d=d.getRanges(1)[0];if(d.collapsed){f=new CKEDITOR.dom.text(c.type=="email"?c.email.address:a["data-cke-saved-href"],f.document);d.insertNode(f);d.selectNodeContents(f)}f=new CKEDITOR.style({element:"a",attributes:a});f.type=CKEDITOR.STYLE_INLINE;f.applyToRange(d);d.select()}},onLoad:function(){g.config.linkShowAdvancedTab||this.hidePage("advanced");
g.config.linkShowTargetTab||this.hidePage("target")},onFocus:function(){var a=this.getContentElement("info","linkType");if(a&&a.getValue()=="url"){a=this.getContentElement("info","url");a.select()}}}});var generateElementIdByLinkTypeName=function(g){g=g.replace(" ","_").toLowerCase();g=g.replace(/\'/g,"");return g=g.replace(/\"/g,"")};