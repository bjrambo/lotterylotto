/*
 * The MIT License

Copyright (c) 2010, 2011, 2012 by Juergen Marsch

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
/*
Flot plugin for Bubbles data sets

  series: {
    Bubbles: null or true
  }
data: [

  $.plot($("#placeholder"), [{ data: [ ... ], Bubble: true }])

*/

(function ($) {
    var options = {
		series: { 
			bubbles: {
				active: false
				, show: false
				, fill: true
				, lineWidth: 2
				, highlight: { opacity: 0.5 }
				, drawbubble: drawbubbleDefault
				, bubblelabel: { show:false, fillStyle:"black"}
			}
		}
	};
	function drawbubbleDefault(ctx,series,axes,x,y,v,r,c,overlay){
		ctx.fillStyle = c;
		ctx.strokeStyle = c;
		ctx.lineWidth = series.bubbles.lineWidth;
		ctx.beginPath()
		ctx.arc(x,y,r,0,Math.PI*2,true);
		ctx.closePath();
		if (series.bubbles.fill) { ctx.fill();} else { ctx.stroke(); }
		if(series.bubbles.bubblelabel.show) {drawbubbleLabel(ctx,series,axes,x,y,v); }
	}
	// based on a patch from Nikola Milikic
	function drawbubbleLabel(ctx,series,axes,x,y,v){	
		var xtext,ytext,vsize,f;
		ctx.fillStyle = series.bubbles.bubblelabel.fillStyle;
		f = axes.xaxis.font;
		ctx.font = f.style + " " + f.variant + " " + f.weight + " " + f.size + "px '" + f.family + "'";
		vsize = ctx.measureText(v); 
		xtext = x - vsize.width/2;
		ytext = y + f.size/2;
		ctx.fillText(v,xtext,ytext);
	}
    function init(plot) {
		var  data = null, canvas = null, target = null, axes = null, offset = null,hl = null;
		plot.hooks.processOptions.push(processOptions);
		function processOptions(plot,options){
		if (options.series.bubbles.active){	
				plot.hooks.draw.push(draw);
				plot.hooks.bindEvents.push(bindEvents);
				plot.hooks.drawOverlay.push(drawOverlay);
			}
		}
		function draw(plot, ctx){
			var series;
			canvas = plot.getCanvas();
			target = $(canvas).parent();
			axes = plot.getAxes();
			offset = plot.getPlotOffset();
			data = plot.getData();
			for (var i = 0; i < data.length; i++){
				series = data[i];
				if (series.bubbles.show) {
					for (var j = 0; j < series.data.length; j++) {
						drawbubble(ctx,series, series.data[j], series.color);
					}
				}
			}
		}
		function drawbubble(ctx,series,data,c,overlay){
			var x,y,r,v;
			x = offset.left + axes.xaxis.p2c(data[0]);
			y = offset.top + axes.yaxis.p2c(data[1]);
			v = data[2];
			r = parseInt(axes.yaxis.scale * data[2] / 2);
			series.bubbles.drawbubble(ctx,series,axes,x,y,v,r,c,overlay);
		}
		function bindEvents(plot, eventHolder){
			var r = null;
			var options = plot.getOptions();
			hl = new HighLighting(plot, eventHolder, findNearby, options.series.bubbles.active)
		}
		function findNearby(mousex, mousey){
			var series, r;
			axes = plot.getAxes();
			data = plot.getData();
			r = new NearByReturn();
			r.item = findNearByItem(mousex,mousey);
			return r;			
			function findNearByItem(mousex, mousey){
				var r = new NearByReturnData();
				for (var i = 0; i < data.length; i++) {
					series = data[i];
					if (series.bubbles.show) {
						for (var j = 0; j < series.data.length; j++) {
							var dataitem = series.data[j];
							var dx = Math.abs(axes.xaxis.p2c(dataitem[0]) - mousex)
							  , dy = Math.abs(axes.yaxis.p2c(dataitem[1]) - mousey)
							  , dist = Math.sqrt(dx * dx + dy * dy);
							if (dist <= dataitem[2]) { r = CreateNearBy(i,j);};
						}
					}
				}
				return r;
			}
			function CreateNearBy(i,j){
				var r = new NearByReturnData();
				r.found = true;
				r.serie = i;
				r.datapoint = j;
				r.value = data[i].data[j];
				return r;
			}
		}
		function drawOverlay(plot, octx){
			octx.save();
			octx.clearRect(0, 0, target.width(), target.height());
			for (i = 0; i < hl.highlights.length; ++i) { drawHighlight(hl.highlights[i]);}
			octx.restore();
			function drawHighlight(item){
				var s = data[item.item.serie];
				var c = "rgba(255, 255, 255, " + s.bubbles.highlight.opacity + ")";
				drawbubble(octx, s, s.data[item.item.datapoint], c, true);
			}
		}
	}

	$.plot.plugins.push({
		init: init,
		options: options,
		name: 'bubbles',
		version: '0.2'
    });
})(jQuery);