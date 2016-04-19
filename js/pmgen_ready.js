
var ready_page = 1;

function ready_get_page($page)
{
	document.location.hash = $page;
	ready_page = $page;
	$.get("ajax/page_ready.php?s="+$page,
		function(res){
			$("#page_content").html(res);
			init_ready_filters();
		});
};

function update_ready_filters()
{
	//general
	$.cookie('ready.filter_name', $('#filter_name')[0].value);
	$.cookie('ready.filter_kernel_low', $('#filter_kernel_low')[0].value);
	$.cookie('ready.filter_kernel_high', $('#filter_kernel_high')[0].value);
	
	//colors
	$.cookie('ready.filter_type', $('#filter_type')[0].value);
	$.cookie('ready.filter_range_low', $('#filter_range_low')[0].value);
	$.cookie('ready.filter_range_high', $('#filter_range_high')[0].value);
	$.cookie('ready.filter_color_enable', $('#filter_color_enable')[0].checked);
	$.cookie('ready.filter_color', $('#filter_color')[0].value);
	
	//update our pages
	ready_get_page(ready_page);
};


function load_ready_filters()
{
	//general
	$('#filter_name')[0].value = $.cookie('ready.filter_name');
	$('#filter_kernel_low')[0].value = $.cookie('ready.filter_kernel_low');
	$('#filter_kernel_high')[0].value = $.cookie('ready.filter_kernel_high');
	
	//colors
	$('#filter_type')[0].value = $.cookie('ready.filter_type');
	$('#filter_range_low')[0].value = $.cookie('ready.filter_range_low');
	$('#filter_range_high')[0].value = $.cookie('ready.filter_range_high');
	$('#filter_color_enable')[0].checked = $.cookie('ready.filter_color_enable') == 'true';
	$('#filter_color')[0].value = $.cookie('ready.filter_color');
	
}

function init_ready_filters() {
	$('#filter_color').ColorPicker({
		color: '#ffffff',
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el)[0].style.backgroundColor = '#' + hex;
			$(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		}
	})
	.bind('keyup', function(){
		$(this).ColorPickerSetColor(this.value);
	});
	
	if(!$.cookie('ready.filter_type'))
	{
		reset_ready_filters();
	} else {
		load_ready_filters();
	}
};

function reset_ready_filters()
{
	
	$.cookie('ready.filter_name', "");
	$.cookie('ready.filter_kernel_low', 2);
	$.cookie('ready.filter_kernel_high', 32);
	
	//colors
	$.cookie('ready.filter_type', "A");
	$.cookie('ready.filter_range_low', 2);
	$.cookie('ready.filter_range_high', 32);
	$.cookie('ready.filter_color_enable', false);
	$.cookie('ready.filter_color', 'ffffff');
	
	ready_get_page(ready_page);
}

function sort_ready($sort_id)
{
	$.cookie('ready.sort_by', $sort_id);
	
	ready_get_page(ready_page);
}


function ready_force_continue($id)
{
	$.get("ajax/ready_force_continue.php?id="+$id,
		function(res){
			queue_markers_get_page(1);
		});
};


function ready_details_get_page($id)
{
	clearTimeout(timeout);
	$("#page_loading").show();
	$.get("ajax/page_ready_detail.php?id="+$id,
		function(res){
			$("#page_content").html(res);
			$("#page_loading").hide();
		});
};

function ready_update_name($input, $id)
{
	$.post("ajax/change_ready_name.php?id="+$id, {'new_name': $input.value}, 
		function(data) {
			alert("name update " + data);
		});
}


