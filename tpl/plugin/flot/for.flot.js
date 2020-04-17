
    function showTooltip(x, y, contents) {
        var $tooltip = jQuery('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            'z-index': '99999',
            display: 'none',
            border: '1px solid #000000',
            'box-shadow': '0px 1px 2px rgba(0,0,0,0.5)',
            padding: '5px',
            'background-color': '#333333',
            'color':'#fff'
        }).appendTo("body");
        
        if(x + $tooltip.outerWidth() + 20 > jQuery(window).width()) {
            x = jQuery(window).width() - $tooltip.outerWidth() - 20;
        }
        
        $tooltip.css({top: y - 40, left: x }).fadeIn(200);;
    }
	    
	function hideTooltip()
	{
        jQuery("#tooltip").remove();
	}