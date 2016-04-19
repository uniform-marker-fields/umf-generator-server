var KERNEL_TYPE_SQUARE_SIZE = "16";
var KERNEL_TYPE_BORDER_SIZE = "6";

var kernel_type_array = Array();

			
function generator_get_page()
{
	$.get("ajax/page_generator.php",
		function(res){
			$("#page_content").html(res);
		});
};


//add new type into the database
function post_generate_new()
{
	$("#gen_loading").show();
	$("#gen_result").hide();
	$("#gen_submit").hide();
	var data = {w: $("#gen_width")[0].value,
			h: $("#gen_height")[0].value, k: $("#gen_kernel")[0].value,
			c: $("#gen_count")[0].value,
			t: $("#gen_type")[0].value,
			n: $("#gen_name")[0].value,
			t_c: $("#gen_type_color")[0].value,
			t_r: $("#gen_type_range")[0].value,
			k_t: $("#gen_kernel_type")[0].value,
			colors: $("#gen_colors")[0].value,
			img_id: ($("#gen_img_use")[0].value == 0 ? 0 : $("#gen_img_id")[0].value),
			img_alg: ($("#gen_img_use")[0].value == 0 ? "" : $("#gen_img_alg")[0].value),
			img_rnd: $("#gen_img_rnd")[0].value,
			m_t: $("#gen_module_type")[0].value,
			t_e: $("#gen_t_equal")[0].value,
			c_n: $("#gen_c_neighbors")[0].value,
			c_s: $("#gen_c_similarity")[0].value,
			img_conv: $("#gen_img_conv")[0].value};
	$.post("ajax/generate_new.php", data,
		function(res) {
			$("#gen_loading").hide();
			if(res.success)
			{
				$("#gen_result").html(
					"Successfully generated " + res.name + " " +
					res.count +
					" with size" + res.width + "x" + res.height + "<br />"+
					"Check the queue..."
					);
			} else {
				$("#gen_result").html("Generation failed " + res);
			}
			$("#gen_result").show();
			$("#gen_submit").show();
		}, "json");
		
};

//add new type into the database
function post_expand_existing()
{
	$("#expand_loading").show();
	$("#expand_result").hide();
	$("#expand_submit").hide();
	var fields = $("#expand_select").val().split('_');
	var data = {l: parseInt($("#expand_left")[0].value),
			r: parseInt($("#expand_right")[0].value),
			t: parseInt($("#expand_top")[0].value),
			b: parseInt($("#expand_bottom")[0].value),
			y: parseInt($("#expand_type")[0].value),
			t_c: parseInt($("#expand_type_color")[0].value),
			colors: $("#expand_colors")[0].value,
			id: fields[0]};
	$.post("ajax/expand_new.php", data,
		function(res) {
			$("#expand_loading").hide();
			if(res.success)
			{
				$("#expand_result").html(
					"Successfully expanded " +
					" to size" + res.width + "x" + res.height + "<br />"+
					"Check the queue..."
					);
			} else {
				$("#expand_result").html("Generation failed");
			}
			$("#expand_result").show();
			$("#expand_submit").show();
		}, "json");
		
};

//reproduction continue where left off
function post_reproduction_continue(res)
{
	$("#reproduction_loading").hide();
	var cont_calc = false;
	if(res.success)
	{
		if(res.continue)
		{
			cont_calc = true;
			$("#reproduction_result").show();
			$("#reproduction_result").append("Ready: " +
				res.count_ready + " queue: " + res.count_queue +
				" Continue from " + 
				res.ready_width + "x" + res.ready_height + "<br />");
			$("#reproduction_loading").show();
			var data = {w: res.min_width,
				h: res.min_height,
				t: res.type,
				c: true,
				sw: res.ready_width,
				sh: res.ready_height,
				id: res.id};
			$.post("ajax/generate_children.php", data,
				post_reproduction_continue, "json");
		} else {
			$("#reproduction_result").append(
				"Successfully generated " +
				res.count_ready + " ready and " + res.count_queue + " were inserted into queue<br />"+
				"Done! Check the queue..."
				);
		}
	} else {
		$("#reproduction_result").html("Reproduction failed");
	}
	if(!cont_calc)
	{
		$("#reproduction_result").show();
		$("#reproduction_submit").show();
	}
};

//reproduct existing
function post_reproduct_new()
{
	$("#reproduction_loading").show();
	$("#reproduction_result").hide();
	$("#reproduction_result").html("");
	$("#reproduction_submit").hide();
	var fields = $("#reproduction_select").val().split('_');
	var data = {w: parseInt($("#reproduction_width")[0].value),
			h: parseInt($("#reproduction_height")[0].value),
			t: parseInt($("#reproduction_type")[0].value),
			id: fields[0]};
	$.post("ajax/generate_children.php", data,
		post_reproduction_continue, "json");
};

function calculate_new_size()
{
	var fields = $("#expand_select").val().split('_');
	$("#expanded_size").html(
		(parseInt(fields[1]) + parseInt($("#expand_left")[0].value) + parseInt($("#expand_right")[0].value))
		+ "x" +
		(parseInt(fields[2]) + parseInt($("#expand_top")[0].value) + parseInt($("#expand_bottom")[0].value))
		);
};


function gen_initialize_picker($id, $color)
{

	$($id).ColorPicker({
		color: $color,
		onShow: function (colpkr) {
			$(colpkr).fadeIn(100);
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(100);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$($id+' div').css('backgroundColor', '#' + hex);
			update_colors($id, hex);
		}
	});
};



function colorToHex(color) {
    if (color.substr(0, 1) === '#') {
        return color;
    }
    var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(color);
    
    var red = parseInt(digits[2]);
    var green = parseInt(digits[3]);
    var blue = parseInt(digits[4]);
    
    var rgb = blue | (green << 8) | (red << 16);
    return digits[1] + '#' + ("000000" + rgb.toString(16)).substr(-6);
};



function update_colors($id, hex)
{
	var count = $("#gen_type_range")[0].value;

	var colors = Array();
	for( var i = 0; i < count; i++)
	{
		var picker_id = "#gen_picker"+i;
		color = $(picker_id + ' div')[0].style.backgroundColor;
		colors[i] = colorToHex(color);
	}
	$("#gen_colors")[0].value = colors.join(';');
}

function generator_colors_changed()
{
	if($("#gen_type_color")[0].value == 0)
	{
		$("#gen_colors")[0].disabled = true;
		$("#color_pickers").html("");
		$("#gen_colors")[0].value = "";
	} else {
		$("#gen_colors")[0].disabled = false;
		$("#color_pickers").html("");
		var els = $("#color_pickers");
		var count = $("#gen_type_range")[0].value;

		var old_colors = $("#gen_colors")[0].value.split(';');
		
		var colors_value = Array();
		for( var i = 0; i < count; i++)
		{
			var picker_id = "gen_picker"+i;
			var color = "#ffffff";
			
			if(i < old_colors.length && old_colors[i].length > 0)
			{
				color = old_colors[i];
			}

			colors_value[i] = color;
			els.append("<div id=\""+picker_id+
				"\" class=\"colorselector\"><div style=\"background-color: "
				+color+"\"></div> </div>");
			gen_initialize_picker("#"+picker_id, color);
		}
		els.append("<br style=\"clear:both\"/><div class=\"note\">(click colors for color chooser)</div>");		

		$("#gen_colors")[0].value = colors_value.join(';');
	}
}

function kernel_type_set()
{
	
	var v = 0;
	if ($('#gen_module_type')[0].value == 0)
	{
		for (var i in kernel_type_array)
		{
			v <<= 1;
			if (kernel_type_array[i])
			{
				v += 1;
			}
		}
	}
	else
	{
		var kernel = parseInt($("#gen_kernel")[0].value);
		var kernel_2 = Math.floor(kernel/2);
		var kernel_2_i = Math.floor((kernel+1)/2);
		var hexaKernelType = kernel_2 * (kernel_2+1) / 2 + kernel_2_i * kernel_2_i;
		for (var i = 0; i < hexaKernelType; i++)
		{
			v <<= 1;
			v += 1;
		}
	}
	
	$("#gen_kernel_type")[0].value = v;
}

function kernel_type_click(border)
{
	kernel_type_array[border] = !kernel_type_array[border];
	
	if (kernel_type_array[border])
	{
		$(".border_" + border).removeClass("kernel_type_border_not");
		$(".border_" + border).addClass("kernel_type_border_sel");
	}
	else
	{
		$(".border_" + border).removeClass("kernel_type_border_sel");
		$(".border_" + border).addClass("kernel_type_border_not");
	}
	
	kernel_type_set();
}

function kernel_type_init()
{
	//v = vector<vector<pair<unsigned long, unsigned long> > >(4, vector<pair<unsigned long, unsigned long> >());
	var kernel = parseInt($("#gen_kernel")[0].value);
	var kernel_type_borders = Array();
	kernel_type_array = Array();
	if (kernel < 1)
	{
		return;
	}
	
	var index = (kernel-1) * kernel / 2;
	for (var n = 0; n < kernel-1; n++)
	{
		var n_2 = Math.floor(n/2);
		var ws_n_2 = kernel - 1 - Math.floor(n/2);
		
		if (n % 2 == 0) {
			
			for (var i = 0; i < kernel-n-1; i++) {
				index--;
				kernel_type_array[index] = true;
				kernel_type_borders[(n_2 * kernel + n_2 + i) + "_" + (n_2 * kernel + n_2 + i+1)] = index;
				kernel_type_borders[((n_2 + i) * kernel + ws_n_2) + "_" + ((n_2 + i+1) * kernel + ws_n_2)] = index;
				kernel_type_borders[(ws_n_2 * kernel + ws_n_2 - i-1) + "_" + (ws_n_2 * kernel + ws_n_2 - i)] = index;
				kernel_type_borders[((ws_n_2 - i-1) * kernel + n_2) + "_" + ((ws_n_2 - i) * kernel + n_2)] = index;
			}
			
		} else {
			
			for (var i = 0; i < kernel-n-1; i++) {
				index--;
				kernel_type_array[index] = true;
				kernel_type_borders[(n_2 * kernel + n_2 + i+1) + "_" + ((n_2+1) * kernel + n_2 + i+1)] = index;
				kernel_type_borders[((n_2 + i+1) * kernel + ws_n_2-1) + "_" + ((n_2 + i+1) * kernel + ws_n_2)] = index;
				kernel_type_borders[((ws_n_2-1) * kernel + ws_n_2 - i-1) + "_" + (ws_n_2 * kernel + ws_n_2 - i-1)] = index;
				kernel_type_borders[((ws_n_2 - i-1) * kernel + n_2) + "_" + ((ws_n_2 - i-1) * kernel + n_2+1)] = index;
			}
			
		}
	}
	
	/*for (var i in kernel_type_array)
	{
		alert(i+": "+kernel_type_array[i]);
	}*/
	
	var new_size = KERNEL_TYPE_SQUARE_SIZE * kernel + KERNEL_TYPE_BORDER_SIZE * (kernel-1);
	$("#kernel_type_container").width(new_size);
	$("#kernel_type_container").height(new_size);
	
	var cont_html = "";
	var border_index = 0;
	
	for (var y = 0; y < kernel; y++)
	{
		for (var x = 0; x < kernel; x++)
		{
			cont_html += "<div class=\"kernel_type_square\" style=\"width: " + KERNEL_TYPE_SQUARE_SIZE + "px; height: " + KERNEL_TYPE_SQUARE_SIZE + "px;\" />\n";
			
			if (x+1 < kernel)
			{
				border_index = kernel_type_borders[(y * kernel + x) + "_" + (y * kernel + x+1)];
				cont_html += "<div class=\"kernel_type_border_sel border_" + border_index + "\" style=\"width: " + KERNEL_TYPE_BORDER_SIZE + "px; height: " + KERNEL_TYPE_SQUARE_SIZE + "px;\" onClick=\"kernel_type_click(" + border_index + ")\"/>\n";
			}
		}
		
		cont_html += "\n";
		
		if (y+1 < kernel) 
		{
			for (var x = 0; x < kernel; x++)
			{
				border_index = kernel_type_borders[(y * kernel + x) + "_" + ((y+1) * kernel + x)];
				cont_html += "<div class=\"kernel_type_border_sel border_" + border_index + "\" style=\"width: " + KERNEL_TYPE_SQUARE_SIZE + "px; height: " + KERNEL_TYPE_BORDER_SIZE + "px;\" onClick=\"kernel_type_click(" + border_index + ")\"/>\n";

				if (x+1 < kernel)
				{
					cont_html += "<div class=\"kernel_type_square\" style=\"width: " + KERNEL_TYPE_BORDER_SIZE + "px; height: " + KERNEL_TYPE_BORDER_SIZE + "px;\" />\n";
				}
			}
		
			cont_html += "\n";
		}
	}
	
	$("#kernel_type_container").html(cont_html);
	
	kernel_type_set();
}

function generator_img_use_change()
{
	if($("#gen_img_use")[0].value == 0)
	{
		$("#image_in_use").hide();
		$("#gen_img_id").val(0);
	} else {
		$("#image_in_use").show();
	}
}

function image_picker_database(starting)
{
	$.post("ajax/image_database.php", {start: starting},
		function(res) {
			if (res.success)
			{
				var cont = "";
				if (res.left)
				{
					cont += "<div class=\"image_nav image_left\" onclick=\"image_picker_database(" + (starting-9) + ")\">&lt;</div>";
				}
				
				cont += "<div style=\"float: left; height: 31px\">";
				var x;
				for (x in res.images)
				{
					cont += "<div class=\"image_thumb wraptocenter\" onclick=\"image_picker_set(" + res.images[x].id + ", '" + res.images[x].url + "')\"><img src=\"" + res.images[x].url + "\" alt=\"..\"/></div>";
				}
				cont += "</div>";
				
				if (res.right)
				{
					cont += "<div class=\"image_nav image_right\" onclick=\"image_picker_database(" + (starting+9) + ")\">&gt;</div>";
				}
				
				$("#image_database").html(cont);
			} else
			{
				$("#image_database").html("<span style=\"color: red\">no images in the database</span>");
			}
		}, "json");
}

function image_picker_set(id, url)
{
	$.modal.close();
	$("#gen_img_id")[0].value = id;
	$("#image_selected > .image").css("background-image", "url('" + url + "')");
	$("#image_no_selected").hide();
}

function image_upload_init()
{
	$("#image_upload_form").show();
	$("#image_upload_process").hide();
	
	$("#image_upload_wrapper").click(function() {
		$("#image_upload").click();
	});
	
	$("#image_upload").change(function() {
		$("#image_upload_process").show();
		$("#image_upload_wrapper").hide();
		$("#image_upload_submit").click();
	});
	
	$('iframe#upload_target').load(function() {
		var res = eval('(' + frames[0].document.body.innerHTML + ')');
		if (res.success)
		{
			image_picker_set(res.img_id, res.img_url);
		} else {
			$("#image_upload_process").hide();
			$("#image_upload_wrapper").html("Try again the image upload!");
			$("#image_upload_wrapper").show();
			alert("Error: " + res.error);
		}
	});
}

function image_picker_show()
{
	$('#image_picker').modal();
	
	image_picker_database(0);
}

function generator_module_changed()
{
	if ($('#gen_module_type')[0].value == 0)
	{
		$('#block_kernel').show();
		$('#block_kernel_type').show();
		$('#block_img').show();
		kernel_type_init();
		$('#gen_c_neighbors').val("8.0,8.0:0;10.0,0.0:128");
	}
	else
	{
		$('#gen_img_use').val(0);
		kernel_type_init();
		$('#block_kernel_type').hide();
		$('#block_img').hide();
		$('#image_in_use').hide();
		$('#gen_c_neighbors').val("8.0,8.0:0;10.0,0.0:64");
	}
}

function show_advanced_settings()
{
	$('.show_advanced_settings').hide();
	$('.advanced_settings').show();
}

function show_note(v)
{
	$('#note-'+v).show();
}

function hide_note(v)
{
	$('#note-'+v).hide();
}

/*function stopUpload(success){
      var result = '';
      if (success == 1){
         result = '<span class="msg">The file was uploaded successfully!<\/span><br/><br/>';
      }
      else {
         result = '<span class="emsg">There was an error during file upload!<\/span><br/><br/>';
      }
      document.getElementById('f1_upload_process').style.visibility = 'hidden';
      document.getElementById('f1_upload_form').innerHTML = result + '<label>File: <input name="myfile" type="file" size="30" /><\/label><label><input type="submit" name="submitBtn" class="sbtn" value="Upload" /><\/label>';
      document.getElementById('f1_upload_form').style.visibility = 'visible';      
      return true;   
}*/
