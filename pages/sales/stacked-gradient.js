function colorLumin(hex, lum) 
{ // Validate hex string
  hex = String(hex).replace(/[^0-9a-f]/gi, "");
  if (hex.length < 6) {
    hex = hex.replace(/(.)/g, '$1$1');
  }
  lum = lum || 0;
  // Convert to decimal and change luminosity
  var rgb = "#",
    c;
  for (var i = 0; i < 3; ++i) {
    c = parseInt(hex.substr(i * 2, 2), 16);
    c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
    rgb += ("00" + c).substr(c.length);
  }
  return rgb;
}

function drawStackedGradient(id, data)
{
    var options =  {
        chart: {
            renderTo: id,
            type: 'column'
        },
        title: {
            text: 'Metric by portfolio'
        },/*
        xAxis: {
            // categories: [data.name1, data.name2],
            type: 'category' 
        },*/
        xAxis: { type: 'category' },
        yAxis: {
            title: {
                text: data.metric
            },
            min: 0,
            max: 100
        },
        legend: { enabled: false},
        credits: { enabled: false },
        dataLabels: { enabled: true},
        series: [{name: 'Portfolio', data:[{name:'Portfolio'},{name:'Comparison'}]}, {name: 'Comparison', data:[]}]
    };
    
    var n = -1;
    var raw = [];
    
    var total_heights = [];
    var yMax=Number.MIN_VALUE;
    var yMin=Number.MAX_VALUE;
    
    function calcYValues(a)
    {  var t=0, b=0;
       for (var i=0; i<a.length; i++)
       {  if (a[i]>=0) t+=1.0*a[i]; else b+=1.0*a[i];
       }
       if (yMax<t) yMax=t;
       if (yMin>b) yMin=b;
       if (yMin>t) yMin=t;
       return {top:t, bottom:b};
    }
    
    total_heights.push( calcYValues(data.p1.data) );
    total_heights.push( calcYValues(data.p2.data) );    
    
    options.yAxis.max = yMax;
    options.yAxis.min = yMin;

    var chart = new Highcharts.Chart(options,
    //add function for custom renderer
    function (chart) {
        var series = this.options.series,
            addMarginX = this.plotLeft,
            addMarginY = this.plotTop,           
            heightAll = [];
        
        var lastHover = -1;

        //renderer group for all rectangulars
        rectGroup = chart.renderer.g()
            .attr({
            zIndex: 5
        }).add();

        //draw for each point a rectangular
        
        var next_x = addMarginX;
        var delta_x = this.xAxis[0].width/series.length;
        var col_width = delta_x*0.5;
        
        var zoom_k = this.yAxis[0].transA; 
        var pt = [data.p1, data.p2];
        
        var c_delta = 1.0/pt[0].data.length; 
        
        var start_colors = [Highcharts.getOptions().colors[0], Highcharts.getOptions().colors[1]];
        for (var i = 0; i < pt.length; i++) {
            var p = pt[i];
            var ph = 0;
            next_x = addMarginX + delta_x*(i+1)-delta_x/2 - col_width/2;
            // var sy = this.chartHeight-this.yAxis[0].bottom;
            var ny = this.yAxis[0].top+(this.yAxis[0].max-total_heights[i].top)*zoom_k;
        
            
            for (var j=0; j<p.data.length; j++)
            {   if (1.0*p.data[j]>0)
                {   var height = zoom_k*p.data[j];
                    var tempRect = chart.renderer.rect(next_x, ny, col_width, 0).attr({
                    "id":i, "stroke-width":0.75, "stroke":"white", "fill":colorLumin(start_colors[i], j*c_delta)
                    }).add(rectGroup);
                    ny += height;
                    
                    tempRect.animate({
                      height: height
                    }, {
                        duration: 1000
                    });

                }
            }
           for (var j=0; j<p.data.length; j++)
            {   if (1.0*p.data[j]<0)
                {   var height = zoom_k*Math.abs(p.data[j]);
                    var tempRect = chart.renderer.rect(next_x, ny, col_width, 0).attr({                    
                    "id":i, "stroke-width":0.75, "stroke":"white", "fill":colorLumin(start_colors[i], j*c_delta)
                    }).add(rectGroup);
                   
                    tempRect.animate({
                      height: height
                    }, {
                        duration: 1000
                    });
                    ny += height;
                }
            }
            
            
        }


        // add tooltip to rectangulars AND labels (rectGroup)
        var tooltipIndex;
        var cname=[data.name1, data.name2];

        rectGroup.on('mouseover', function (e) {

            //get the active element (or is there a simpler way?)
            var el = (e.target.correspondingUseElement) ? e.target.correspondingUseElement : e.target;

            //determine with the 'id' to which dataPoint this element belongs
            //problem: if label is hovered, use tootltipIndex of rect
            var i = parseFloat(el.getAttribute('id'));
            if (!isNaN(i)) {
                tooltipIndex = i;
            }
            
            if (lastHover!=i)
            {   lastHover=i;
                var bx = 1*el.getAttribute('x'), by = 1*el.getAttribute('y');
               // console.log(i);
               // render text for tooltip based on coordinates of rect
                var s = '<b>'+cname[i]+'</b><br>'                
                for (var j=0; j<pt[i].data.length; j++)
                {   var n = 1.0*pt[i].data[j];                    
                    var proc = Math.abs(n/(total_heights[i].top-total_heights[i].bottom))*100.0;
                    s+=pt[i].names[j]+': '+n.toFixed(2)+' ('+proc.toFixed(1)+'%)<br>';
                }
                text = chart.renderer.text(s, bx, by)
                    .attr({
                    zIndex: 101
                })
                    .add();

                var box = text.getBBox();
               
                //box surrounding the tool tip text                     
                border = chart.renderer.rect(bx-5, by-16, box.width+10, box.height+10, 2)
                    .attr({
                    fill: 'rgba(255, 255, 255, 0.7)',
                    stroke: 'blue',
                        'stroke-width': 0.5,
                    zIndex: 100
                }).add();
            }

        }).on('mouseout', function () {
            if (text.element!=undefined)
            {  text.destroy();
               border.destroy();
               lastHover=-1;
            }
        });


    });
    
}



