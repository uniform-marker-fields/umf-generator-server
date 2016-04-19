
var timeout; //just a timeout function
var auto_reload = true;

$(document).ready( function() {
		if($.cookie('auto_reload'))
		{
			auto_reload = $.cookie('auto_reload') == 'true';
		}
		$("#reload_checkbox")[0].checked = auto_reload;
	});

function toggle_auto_reload(box)
{
	var cval = box.checked;
	auto_reload = cval;
	$.cookie('auto_reload', cval);
	if(auto_reload)
	{
		window.location.href=window.location.href;
	} else {
		clearTimeout(timeout);
	}
};


function auto_reload_func($action, $wait)
{
	if(auto_reload)
	{
		timeout = setTimeout($action, $wait);
	}
}

//TODO add some hash processing

