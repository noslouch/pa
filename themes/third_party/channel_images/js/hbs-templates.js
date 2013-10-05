this["ChannelImages"] = this["ChannelImages"] || {};
this["ChannelImages"]["Templates"] = this["ChannelImages"]["Templates"] || {};

this["ChannelImages"]["Templates"]["mcp_batch_action_row"] = Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression, self=this;

function program1(depth0,data) {
  
  
  return "action_loading";
  }

function program3(depth0,data) {
  
  
  return "\n    <strong class=\"action_done\">DONE</strong>\n";
  }

function program5(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    ID: ";
  if (stack1 = helpers.id) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.id; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "&nbsp;&nbsp;\n\n    ";
  stack1 = helpers['if'].call(depth0, depth0.ajax_error, {hash:{},inverse:self.noop,fn:self.program(6, program6, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n\n";
  return buffer;
  }
function program6(depth0,data) {
  
  var buffer = "", stack1, stack2;
  buffer += "\n    <strong style=\"color:red\">(ERROR)</strong>&nbsp;&nbsp;\n    <span class=\"action_error\">\n        <span class=\"channel\"><strong>Channel:</strong> ";
  if (stack1 = helpers.channel) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.channel; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</span>&nbsp;&nbsp;\n        <span class=\"entry\"><strong>Entry:</strong> ";
  if (stack1 = helpers.entry) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.entry; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</span>&nbsp;&nbsp;\n        <span class=\"field\"><strong>Field:</strong> ";
  if (stack1 = helpers.field) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.field; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</span>&nbsp;&nbsp;\n        <span class=\"field\"><strong>Image:</strong> "
    + escapeExpression(((stack1 = ((stack1 = depth0.image),stack1 == null || stack1 === false ? stack1 : stack1.title)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "</span>\n\n        <strong class=\"show_error\">SHOW ERROR</strong>&nbsp;&nbsp;\n\n        ";
  stack2 = helpers['if'].call(depth0, depth0.retry, {hash:{},inverse:self.noop,fn:self.program(7, program7, data),data:data});
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n        <script type=\"text/x-ci_debug\">\n        ";
  if (stack2 = helpers.ajax_error) { stack2 = stack2.call(depth0, {hash:{},data:data}); }
  else { stack2 = depth0.ajax_error; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n        </span>\n\n    </span>\n    ";
  return buffer;
  }
function program7(depth0,data) {
  
  
  return "\n        (<span class=\"retry\">Retrying in 3 seconds..</span>)\n        ";
  }

  buffer += "<td class=\"action_row ";
  stack1 = helpers['if'].call(depth0, depth0.loading, {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\">Action</td>\n<td>\n";
  stack1 = helpers['if'].call(depth0, depth0.action_done, {hash:{},inverse:self.program(5, program5, data),fn:self.program(3, program3, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n</td>\n";
  return buffer;
  });