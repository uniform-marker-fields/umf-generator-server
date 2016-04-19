
var marker_page = 1;



function queue_markers_get_page($page, $init)
{
	$init = typeof $init !== 'undefined' ? $init : false;

	clearTimeout(timeout);
	marker_page = $page;
	$("#page_loading").show();
	$.get("ajax/page_queue_markers.php?s="+$page,
		function(res){
			$("#page_content").html(res);
			auto_reload_func("queue_markers_get_page("+$page+")", 5000);
			init_marker_filters();
			$("#page_loading").hide();
		});
};



function update_marker_filters()
{
	//general
	$.cookie('inqueue.filter_name', $('#filter_name')[0].value);
	$.cookie('inqueue.filter_kernel_low', $('#filter_kernel_low')[0].value);
	$.cookie('inqueue.filter_kernel_high', $('#filter_kernel_high')[0].value);
	
	//colors
	$.cookie('inqueue.filter_type', $('#filter_type')[0].value);
	$.cookie('inqueue.filter_range_low', $('#filter_range_low')[0].value);
	$.cookie('inqueue.filter_range_high', $('#filter_range_high')[0].value);
	$.cookie('inqueue.filter_color_enable', $('#filter_color_enable')[0].checked);
	$.cookie('inqueue.filter_color', $('#filter_color')[0].value);
	
	//update our pages
	queue_markers_get_page(marker_page);
}


function load_marker_filters()
{
	//general
	$('#filter_name')[0].value = $.cookie('inqueue.filter_name');
	$('#filter_kernel_low')[0].value = $.cookie('inqueue.filter_kernel_low');
	$('#filter_kernel_high')[0].value = $.cookie('inqueue.filter_kernel_high');
	
	//colors
	$('#filter_type')[0].value = $.cookie('inqueue.filter_type');
	$('#filter_range_low')[0].value = $.cookie('inqueue.filter_range_low');
	$('#filter_range_high')[0].value = $.cookie('inqueue.filter_range_high');
	$('#filter_color_enable')[0].checked = $.cookie('inqueue.filter_color_enable') == 'true';
	$('#filter_color')[0].value = $.cookie('inqueue.filter_color');
	
}

function init_marker_filters() {
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
	
	if(!$.cookie('inqueue.filter_type'))
	{
		reset_marker_filters();
	} else {
		load_marker_filters();
	}
};

function reset_marker_filters()
{
	
	$.cookie('inqueue.filter_name', "");
	$.cookie('inqueue.filter_kernel_low', 2);
	$.cookie('inqueue.filter_kernel_high', 32);
	
	//colors
	$.cookie('inqueue.filter_type', "A");
	$.cookie('inqueue.filter_range_low', 2);
	$.cookie('inqueue.filter_range_high', 32);
	$.cookie('inqueue.filter_color_enable', false);
	$.cookie('inqueue.filter_color', 'ffffff');
	
	queue_markers_get_page(marker_page);
}

function sort_inqueue($sort_id)
{
	$.cookie('inqueue.sort_by', $sort_id);
	
	queue_markers_get_page(marker_page);
}

			
			
function remove_map($gid)
{
	$.get("ajax/remove_map.php?gid="+$gid,
		function(res){
			queue_markers_get_page(marker_page);
		});
};


function toggle_gid_state($gid)
{
	$.get("ajax/toggle_gid_state.php?gid="+$gid,
		function(res){
			//queue_markers_get_page(marker_page);
		});
};

function toggle_gid_testing($gid)
{
	$.get("ajax/toggle_gid_testing.php?gid="+$gid,
		function(res){
			//queue_markers_get_page(marker_page);
		});
};

function toggle_gid_force_continue($gid, $reload_list)
{
	$reload_list = typeof $reload_list !== 'undefined' ? $reload_list : true;
	
	$.get("ajax/toggle_gid_force_continue.php?gid="+$gid,
		function(res){
			if($reload_list)
			{
				queue_markers_get_page(marker_page);
			} else {
				window.location.href=window.location.href;
			}
		});
};

function marker_details_get_page($id)
{
	clearTimeout(timeout);
	$("#page_loading").show();
	$.get("ajax/page_marker_detail.php?gid="+$id,
		function(res){
			$("#page_content").html(res);
			$("#page_loading").hide();
		});
};

function marker_change_name($input, $id)
{
	$.post("ajax/change_gid_name.php?gid="+$id, {'new_name': $input.value}, 
		function(data) {
			alert("name update " + data);
		});
}

function marker_update_properties()
{
	$.post("ajax/add_new_with_updated_properties.php",
		{
			gid: $("#update_gid")[0].value,
			name: $("#update_name")[0].value,
			c_n: $("#update_c_neighbors")[0].value,
			c_s: $("#update_c_similarity")[0].value,
			img_conv: $("#update_img_conv")[0].value,
			count: $("#update_count")[0].value,
			keep: $("#update_keep").is(':checked')
		},
		function(data) {
			alert("new gid: " + data);
		});
}
