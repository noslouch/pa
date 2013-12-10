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

this["ChannelImages"]["Templates"]["mcp_regen_fieldsizes"] = Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression, self=this;

function program1(depth0,data,depth1) {
  
  var buffer = "", stack1;
  buffer += "\n		<input type=\"checkbox\" name=\"sizes["
    + escapeExpression(((stack1 = depth1.field_id),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "][]\" value=\""
    + escapeExpression((typeof depth0 === functionType ? depth0.apply(depth0) : depth0))
    + "\" checked> "
    + escapeExpression((typeof depth0 === functionType ? depth0.apply(depth0) : depth0))
    + " &nbsp;&nbsp;&nbsp;\n		";
  return buffer;
  }

  buffer += "<tr>\n	<td>";
  if (stack1 = helpers.group) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.group; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</td>\n	<td>";
  if (stack1 = helpers.field) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.field; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</td>\n	<td>\n		";
  stack1 = helpers.each.call(depth0, depth0.sizes, {hash:{},inverse:self.noop,fn:self.programWithDepth(1, program1, data, depth0),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	</td>\n</tr>";
  return buffer;
  });

this["ChannelImages"]["Templates"]["pbf_table_tr"] = Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression, self=this;

function program1(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n<tr class=\"Image image-table ";
  stack1 = helpers['if'].call(depth0, depth0.is_cover, {hash:{},inverse:self.noop,fn:self.program(2, program2, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\" data-filename=\"";
  if (stack1 = helpers.filename) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.filename; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\">\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_row_num, {hash:{},inverse:self.noop,fn:self.program(4, program4, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_id, {hash:{},inverse:self.noop,fn:self.program(6, program6, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_image, {hash:{},inverse:self.noop,fn:self.program(8, program8, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_filename, {hash:{},inverse:self.noop,fn:self.program(10, program10, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_title, {hash:{},inverse:self.noop,fn:self.program(12, program12, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_url_title, {hash:{},inverse:self.noop,fn:self.program(14, program14, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_desc, {hash:{},inverse:self.noop,fn:self.program(16, program16, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_category, {hash:{},inverse:self.noop,fn:self.program(18, program18, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_cifield_1, {hash:{},inverse:self.noop,fn:self.program(20, program20, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_cifield_2, {hash:{},inverse:self.noop,fn:self.program(22, program22, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_cifield_3, {hash:{},inverse:self.noop,fn:self.program(24, program24, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_cifield_4, {hash:{},inverse:self.noop,fn:self.program(26, program26, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	";
  stack1 = helpers['if'].call(depth0, depth0.show_cifield_5, {hash:{},inverse:self.noop,fn:self.program(28, program28, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	<td>\n		";
  stack1 = helpers['if'].call(depth0, depth0.show_image_action, {hash:{},inverse:self.noop,fn:self.program(30, program30, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n		<a href='javascript:void(0)' class='gIcon ImageMove'></a>\n		";
  stack1 = helpers['if'].call(depth0, depth0.show_cover, {hash:{},inverse:self.noop,fn:self.program(33, program33, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n		";
  stack1 = helpers['if'].call(depth0, depth0.show_image_edit, {hash:{},inverse:self.noop,fn:self.program(38, program38, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n		";
  stack1 = helpers['if'].call(depth0, depth0.show_image_replace, {hash:{},inverse:self.noop,fn:self.program(40, program40, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n		<a href=\"#\" ";
  stack1 = helpers['if'].call(depth0, depth0.is_linked, {hash:{},inverse:self.program(44, program44, data),fn:self.program(42, program42, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "></a>\n		<textarea name=\"";
  if (stack1 = helpers.field_name) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.field_name; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "[images][][data]\" class=\"ImageData cihidden\">";
  if (stack1 = helpers.json_data) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.json_data; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</textarea>\n	</td>\n</tr>\n";
  return buffer;
  }
function program2(depth0,data) {
  
  
  return "PrimaryImage";
  }

function program4(depth0,data) {
  
  
  return "<td class=\"num\"></td>";
  }

function program6(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td>";
  if (stack1 = helpers.image_id) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.image_id; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</td>";
  return buffer;
  }

function program8(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td>\n		<a href='";
  if (stack1 = helpers.big_img_url) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.big_img_url; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "' class='ImgUrl' rel='ChannelImagesGal' title='";
  if (stack1 = helpers.image_title) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.image_title; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "'>\n			<img src=\"";
  if (stack1 = helpers.small_img_url) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.small_img_url; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\" width=\"50px\" alt=\"";
  if (stack1 = helpers.image_title) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.image_title; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\">\n		</a></td>\n	";
  return buffer;
  }

function program10(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td>";
  if (stack1 = helpers.filename) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.filename; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</td>";
  return buffer;
  }

function program12(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td data-field=\"title\"><input type=\"text\" value=\"";
  if (stack1 = helpers.image_title) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.image_title; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\" class=\"image_title\"></td>";
  return buffer;
  }

function program14(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td data-field=\"url_title\"><textarea>";
  if (stack1 = helpers.image_url_title) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.image_url_title; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</textarea></td>";
  return buffer;
  }

function program16(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td data-field=\"description\"><textarea>";
  if (stack1 = helpers.description) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.description; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</textarea></td>";
  return buffer;
  }

function program18(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td data-field=\"category\">";
  if (stack1 = helpers.category) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.category; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</td>";
  return buffer;
  }

function program20(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td data-field=\"cifield_1\"><textarea>";
  if (stack1 = helpers.cifield_1) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.cifield_1; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</textarea></td>";
  return buffer;
  }

function program22(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td data-field=\"cifield_2\"><textarea>";
  if (stack1 = helpers.cifield_2) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.cifield_2; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</textarea></td>";
  return buffer;
  }

function program24(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td data-field=\"cifield_3\"><textarea>";
  if (stack1 = helpers.cifield_3) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.cifield_3; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</textarea></td>";
  return buffer;
  }

function program26(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td data-field=\"cifield_4\"><textarea>";
  if (stack1 = helpers.cifield_4) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.cifield_4; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</textarea></td>";
  return buffer;
  }

function program28(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<td data-field=\"cifield_5\"><textarea>";
  if (stack1 = helpers.cifield_5) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.cifield_5; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</textarea></td>";
  return buffer;
  }

function program30(depth0,data) {
  
  var stack1;
  stack1 = helpers.unless.call(depth0, depth0.is_linked, {hash:{},inverse:self.noop,fn:self.program(31, program31, data),data:data});
  if(stack1 || stack1 === 0) { return stack1; }
  else { return ''; }
  }
function program31(depth0,data) {
  
  
  return "<a href='#' class='gIcon ImageProcessAction'></a>";
  }

function program33(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<a href='#' class='gIcon ";
  stack1 = helpers['if'].call(depth0, depth0.is_cover, {hash:{},inverse:self.program(36, program36, data),fn:self.program(34, program34, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "'></a>";
  return buffer;
  }
function program34(depth0,data) {
  
  
  return "StarIcon ImageCover";
  }

function program36(depth0,data) {
  
  
  return "ImageCover";
  }

function program38(depth0,data) {
  
  
  return "<a href='#' class='gIcon ImageEdit'></a>";
  }

function program40(depth0,data) {
  
  
  return "<a href='#' class='gIcon ImageReplace'></a>";
  }

function program42(depth0,data) {
  
  
  return "class=\"gIcon ImageDel ImageLinked\"";
  }

function program44(depth0,data) {
  
  
  return "class=\"gIcon ImageDel\"";
  }

function program46(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n<li class=\"Image image-tile ";
  stack1 = helpers['if'].call(depth0, depth0.is_cover, {hash:{},inverse:self.noop,fn:self.program(2, program2, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\" data-filename=\"";
  if (stack1 = helpers.filename) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.filename; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\">\n	<a href='";
  if (stack1 = helpers.big_img_url) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.big_img_url; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "' class='ImgUrl' rel='ChannelImagesGal' title='";
  if (stack1 = helpers.image_title) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.image_title; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "'>\n		<img src=\"";
  if (stack1 = helpers.small_img_url) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.small_img_url; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\" alt=\"";
  if (stack1 = helpers.image_title) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.image_title; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\">\n	</a>\n	<div class=\"filename\">\n		<div class=\"name\" data-field=\"title\"><input type=\"text\" value=\"";
  if (stack1 = helpers.image_title) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.image_title; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\" class=\"image_title\"></div>\n	</div>\n	<div class=\"actions\">\n		";
  stack1 = helpers['if'].call(depth0, depth0.show_cover, {hash:{},inverse:self.noop,fn:self.program(47, program47, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n		<span ";
  stack1 = helpers['if'].call(depth0, depth0.is_linked, {hash:{},inverse:self.noop,fn:self.program(49, program49, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += " ";
  stack1 = helpers.unless.call(depth0, depth0.is_linked, {hash:{},inverse:self.noop,fn:self.program(51, program51, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "></span>\n		";
  stack1 = helpers['if'].call(depth0, depth0.show_image_edit, {hash:{},inverse:self.noop,fn:self.program(53, program53, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n		";
  stack1 = helpers['if'].call(depth0, depth0.show_image_replace, {hash:{},inverse:self.noop,fn:self.program(55, program55, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n	</div>\n\n	<textarea name=\"";
  if (stack1 = helpers.field_name) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.field_name; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "[images][][data]\" class=\"ImageData cihidden\">";
  if (stack1 = helpers.json_data) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.json_data; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</textarea>\n</li>\n";
  return buffer;
  }
function program47(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "<span class=\"abtn btn-star ";
  stack1 = helpers['if'].call(depth0, depth0.is_cover, {hash:{},inverse:self.program(36, program36, data),fn:self.program(34, program34, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\"></span>";
  return buffer;
  }

function program49(depth0,data) {
  
  
  return "class=\"abtn ImageDel ImageLinked\"";
  }

function program51(depth0,data) {
  
  
  return "class=\"abtn btn-delete ImageDel\"";
  }

function program53(depth0,data) {
  
  
  return "<span class=\"abtn btn-edit ImageEdit\"></span>";
  }

function program55(depth0,data) {
  
  
  return "<span class=\"abtn btn-replace ImageReplace\"></span>";
  }

  stack1 = helpers['if'].call(depth0, depth0.table_view, {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n\n";
  stack1 = helpers['if'].call(depth0, depth0.tile_view, {hash:{},inverse:self.noop,fn:self.program(46, program46, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  return buffer;
  });