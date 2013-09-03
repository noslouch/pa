this["Updater"] = this["Updater"] || {};
this["Updater"]["Templates"] = this["Updater"]["Templates"] || {};

this["Updater"]["Templates"]["action_row"] = Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, stack2, options, self=this, helperMissing=helpers.helperMissing, functionType="function", escapeExpression=this.escapeExpression;

function program1(depth0,data) {
  
  var buffer = "", stack1, stack2, options;
  buffer += "\n            ";
  options = {hash:{},inverse:self.noop,fn:self.program(2, program2, data),data:data};
  stack2 = ((stack1 = helpers.compare),stack1 ? stack1.call(depth0, depth0.type, "!=", "ee", options) : helperMissing.call(depth0, "compare", depth0.type, "!=", "ee", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n        ";
  return buffer;
  }
function program2(depth0,data) {
  
  
  return "\n            <div class=\"move\">&nbsp;</div>\n            ";
  }

function program4(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    Backup Files "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.full)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\n    ";
  return buffer;
  }

function program6(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    Backup Database "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.full)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\n    ";
  return buffer;
  }

function program8(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    ExpressionEngine "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.full)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\n    ";
  return buffer;
  }

function program10(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    Multiple Site Manager "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.full)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\n    ";
  return buffer;
  }

function program12(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    ExpressionEngine Forum "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.version)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\n    ";
  return buffer;
  }

function program14(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    CP Theme: "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.label)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + " "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.version)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\n    ";
  return buffer;
  }

function program16(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    Forum Theme: "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.label)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + " "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.version)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\n    ";
  return buffer;
  }

function program18(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.label)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + " "
    + escapeExpression(((stack1 = ((stack1 = depth0.info),stack1 == null || stack1 === false ? stack1 : stack1.version)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\n    ";
  return buffer;
  }

function program20(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n        <span class=\"loading\">";
  if (stack1 = helpers.loadingMsg) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.loadingMsg; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</span>\n        ";
  return buffer;
  }

function program22(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n        <div class=\"progress\" id=\"single_action_progress\">\n            <div class=\"inner\">\n                ";
  if (stack1 = helpers.progressMsg) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.progressMsg; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\n            </div>\n        </div>\n        ";
  return buffer;
  }

function program24(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n        <div class=\"error\">\n            ";
  if (stack1 = helpers.errorMsg) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.errorMsg; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n\n            ";
  stack1 = helpers['if'].call(depth0, depth0.errorDetail, {hash:{},inverse:self.noop,fn:self.program(25, program25, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n        </div>\n        ";
  return buffer;
  }
function program25(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n            <a href=\"#\" class=\"js-show_error\"><strong>Show Error</strong></a>\n            <span class=\"label label-invert js-retrybtn\" style=\"float:right\">Retry</span>\n            <textarea style=\"display:none\">";
  if (stack1 = helpers.errorDetail) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.errorDetail; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</textarea>\n            ";
  return buffer;
  }

  buffer += "<tr id=\"";
  if (stack1 = helpers.id) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.id; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\" class=\"action type-";
  if (stack1 = helpers.type) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.type; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + " status-";
  if (stack1 = helpers.status) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.status; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\">\n    <td style=\"width:20px\">\n        ";
  options = {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data};
  stack2 = ((stack1 = helpers.compare),stack1 ? stack1.call(depth0, depth0.status, "!=", "done", options) : helperMissing.call(depth0, "compare", depth0.status, "!=", "done", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n    </td>\n    <td style=\"width:50px\">\n    <strong>";
  if (stack2 = helpers.updaterAction) { stack2 = stack2.call(depth0, {hash:{},data:data}); }
  else { stack2 = depth0.updaterAction; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
  buffer += escapeExpression(stack2)
    + "</strong>\n    </td>\n    <td style=\"width:300px\">\n    ";
  options = {hash:{},inverse:self.noop,fn:self.program(4, program4, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.type, "backup_files", options) : helperMissing.call(depth0, "ifeq", depth0.type, "backup_files", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n    ";
  options = {hash:{},inverse:self.noop,fn:self.program(6, program6, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.type, "backup_db", options) : helperMissing.call(depth0, "ifeq", depth0.type, "backup_db", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n    ";
  options = {hash:{},inverse:self.noop,fn:self.program(8, program8, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.type, "ee", options) : helperMissing.call(depth0, "ifeq", depth0.type, "ee", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n    ";
  options = {hash:{},inverse:self.noop,fn:self.program(10, program10, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.type, "ee_msm", options) : helperMissing.call(depth0, "ifeq", depth0.type, "ee_msm", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n    ";
  options = {hash:{},inverse:self.noop,fn:self.program(12, program12, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.type, "ee_forum", options) : helperMissing.call(depth0, "ifeq", depth0.type, "ee_forum", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n    ";
  options = {hash:{},inverse:self.noop,fn:self.program(14, program14, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.type, "cp_theme", options) : helperMissing.call(depth0, "ifeq", depth0.type, "cp_theme", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n    ";
  options = {hash:{},inverse:self.noop,fn:self.program(16, program16, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.type, "forum_theme", options) : helperMissing.call(depth0, "ifeq", depth0.type, "forum_theme", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n    ";
  options = {hash:{},inverse:self.noop,fn:self.program(18, program18, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.type, "addon", options) : helperMissing.call(depth0, "ifeq", depth0.type, "addon", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n    </td>\n\n    <td style=\"text-align:center;width:75px\" class=\"status status-";
  if (stack2 = helpers.status) { stack2 = stack2.call(depth0, {hash:{},data:data}); }
  else { stack2 = depth0.status; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
  buffer += escapeExpression(stack2)
    + "\">\n        <span class=\"queued label\">Queued</span>\n        <span class=\"forced label label-warning\">Forced</span>\n        <span class=\"processing label label-info\">Processing</span>\n        <span class=\"done label label-success\">Done</span>\n        <span class=\"error label label-important\">Error</span>\n    </td>\n\n    <td class=\"thirdcol\">\n        ";
  stack2 = helpers['if'].call(depth0, depth0.loadingMsg, {hash:{},inverse:self.noop,fn:self.program(20, program20, data),data:data});
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n        ";
  stack2 = helpers['if'].call(depth0, depth0.progressMsg, {hash:{},inverse:self.noop,fn:self.program(22, program22, data),data:data});
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n        ";
  options = {hash:{},inverse:self.noop,fn:self.program(24, program24, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.status, "error", options) : helperMissing.call(depth0, "ifeq", depth0.status, "error", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n    </td>\n</tr>\n";
  return buffer;
  });

this["Updater"]["Templates"]["addon"] = Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression;


  buffer += "<div class=\"entry\">\n  <h1>";
  if (stack1 = helpers.title) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.title; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</h1>\n  <div class=\"body\">\n    ";
  if (stack1 = helpers.body) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.body; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\n  </div>\n</div>\n";
  return buffer;
  });

this["Updater"]["Templates"]["browse_server"] = Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression, self=this;

function program1(depth0,data) {
  
  var buffer = "";
  buffer += "\n        <li class=\"dir\">\n            <span class=\"chdir\">"
    + escapeExpression((typeof depth0 === functionType ? depth0.apply(depth0) : depth0))
    + "</span>\n        </li>\n        ";
  return buffer;
  }

function program3(depth0,data) {
  
  var buffer = "";
  buffer += "\n        <li class=\"file\">"
    + escapeExpression((typeof depth0 === functionType ? depth0.apply(depth0) : depth0))
    + "</li>\n        ";
  return buffer;
  }

  buffer += "<div class=\"browse\">\n    <span class=\"cdup\">Parent Directory</span>\n    <ul>\n        ";
  stack1 = helpers.each.call(depth0, depth0.dirs, {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n        ";
  stack1 = helpers.each.call(depth0, depth0.files, {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n    </ul>\n</div>\n";
  return buffer;
  });

this["Updater"]["Templates"]["upload_filerow"] = Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, stack2, options, functionType="function", escapeExpression=this.escapeExpression, self=this, helperMissing=helpers.helperMissing;

function program1(depth0,data) {
  
  
  return "\n        <div class=\"progress\"></div>\n        ";
  }

function program3(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n        <div class=\"error\">\n            ";
  if (stack1 = helpers.errorMsg) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.errorMsg; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\n\n            ";
  stack1 = helpers['if'].call(depth0, depth0.errorDetail, {hash:{},inverse:self.noop,fn:self.program(4, program4, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n        </div>\n        ";
  return buffer;
  }
function program4(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n            <a href=\"#\" class=\"js-show_error\"><strong>Show Error</strong></a>\n            <textarea style=\"display:none\">";
  if (stack1 = helpers.errorDetail) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.errorDetail; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</textarea>\n            ";
  return buffer;
  }

  buffer += "<tr id=\"";
  if (stack1 = helpers.id) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.id; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\" class=\"file status-";
  if (stack1 = helpers.status) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.status; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\" data-filename=\"";
  if (stack1 = helpers.filename) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.filename; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\">\n    <td style=\"width:250px\">";
  if (stack1 = helpers.filename) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.filename; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + " (";
  if (stack1 = helpers.filesize) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.filesize; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + ")</td>\n\n    <td style=\"text-align:center;width:75px\" class=\"status status-";
  if (stack1 = helpers.status) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.status; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\">\n        <span class=\"queued label\">Queued</span>\n        <span class=\"uploading label label-info\">Uploading</span>\n        <span class=\"done label label-success\">Done</span>\n        <span class=\"action label label-warning\">Action</span>\n        <span class=\"error label label-important\">Error</span>\n    </td>\n\n    <td class=\"thirdcol\">\n        ";
  options = {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.status, "uploading", options) : helperMissing.call(depth0, "ifeq", depth0.status, "uploading", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n        ";
  options = {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data};
  stack2 = ((stack1 = helpers.ifeq),stack1 ? stack1.call(depth0, depth0.status, "error", options) : helperMissing.call(depth0, "ifeq", depth0.status, "error", options));
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n    </td>\n</tr>\n";
  return buffer;
  });