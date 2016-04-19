
var queue_page=1;

function queue_get_page($page)
{
	clearTimeout(timeout);
	queue_page = $page;
	$("#page_loading").show();
	$.get("ajax/page_queue.php?s="+$page,
		function(res){
			$("#page_content").html(res);
			auto_reload_func("queue_get_page("+$page+")", 2000);
			$("#page_loading").hide();
		});
};

function queue_details_get_page($id)
{
	clearTimeout(timeout);
	$("#page_loading").show();
	$.get("ajax/page_queue_detail.php?id="+$id,
		function(res){
			$("#page_content").html(res);
			$("#page_loading").hide();
		});
};

function toggle_map_boost($id)
{
	$.get("ajax/toggle_map_boost.php?id="+$id,
		function(res){
			//queue_get_page(queue_page);
		});
};
