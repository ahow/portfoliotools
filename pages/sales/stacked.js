function drawStackedChart(id, data)
{

    var options =  {
        chart: {
            renderTo: id,
            type: 'column'
        },
        title: {
            text: 'Metric by company'
        },
        xAxis: {
            title: {
                text: 'Weight'
            },
            min: 0,
            max: 100,
            reversed: false
        },
        yAxis: {
            title: {
                text: 'Metric'
            },
            min: 0,
            max: 100
        },
        tooltip: {
            enabled: false
        },
        legend: {
            enabled: true
        },
        credits: {
            enabled: false
        },
        widths: [],
        isin: [],
        cname: [],
        series: []
    };
    
    var n = -1;
    var raw = [];

    // Fill data from query
    for (var i=0; i<data.chart2.length; i++)
    { var r = data.chart2[i];
      var j = 1*r.col-1;
      if (j==0) 
      {  n++;
         if (raw[n]==undefined) raw[n]={};
         raw[n].width = 1.0*r.weight;
         raw[n].cname = r.name;
         raw[n].data = [];
         raw[n].total = 0;
      }
      raw[n].data[j] = 1.0*r.val;
      raw[n].total += 1.0*r.val;
    }
    
    raw.sort(function(a,b){ return b.total-a.total });
    
    // var start_color = "#324778";
    var start_color = Highcharts.getOptions().colors[0];
    
    var c_delta = 1.0/raw[0].data.length*0.6;
    // attrOptions.fill = colorLumin(Highcharts.getOptions().colors[0], j*0.2);
    
    for (var i=0; i<raw.length; i++)
    {  options.widths[i] = raw[i].width;
       options.cname[i]=raw[i].cname;
       options.isin[i]=raw[i].isin;
       for (var j=0; j<raw[i].data.length; j++)
       { if (options.series[j]==undefined) options.series[j] = {name:data.p1.names[j], data:[], xdata:[], color:colorLumin(start_color, j*c_delta)};
         options.series[j].xdata[i] = raw[i].data[j];
       }
    }
    
    // console.log(raw);
    // console.log(options);
    
    var total_width = 0;
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
    
    for (var i = 0; i < options.series[0].xdata.length; i++)
    {  total_width+=options.widths[i];
       var a = [];
       for (var j=0; j<options.series.length; j++) a.push(options.series[j].xdata[i]);
       total_heights.push( calcYValues(a) );
    }
    
    options.yAxis.max = yMax;
    options.yAxis.min = yMin;
    options.yAxis.title = {text:data.metric};
    
    
    var chart = new Highcharts.Chart(options,
    //add function for custom renderer
    function (chart) {
        var series = this.options.series,
            isin = this.options.isin,
            cname = this.options.cname,
            points = series[0].xdata,
            widths = this.options.widths,
            addMarginX = this.plotLeft,
            addMarginY = this.plotTop,
            widthAll = [],
            heightAll = [];
        
        var lastHover = -1;

        //renderer group for all rectangulars
        rectGroup = chart.renderer.g()
            .attr({
            zIndex: 5
        }).add();

        //draw for each point a rectangular
        
        var next_x = addMarginX;
        var zoom_k = this.yAxis[0].transA; 
        // console.log('yMax='+yMax);

        
        for (var i = 0; i < points.length; i++) {
           
            var x = next_x,
                y = points[i] + addMarginY,
                width = (this.xAxis[0].width)*(widths[i]/total_width),
                height = (this.yAxis[0].height)*(total_heights[i]/yMax);
                next_x += width;

            widthAll.push(width);
            heightAll.push(height);
            
            // draw other series bars
            var pre = 0;
            
            var ny = this.yAxis[0].top+(this.yAxis[0].max-total_heights[i].top)*zoom_k;
            
            for (var j=0; j<this.options.series.length; j++)
            {   var h = 1.0*this.options.series[j].xdata[i];
                if (h>0)
                {   var height = zoom_k*h;
                    var tempRect = chart.renderer.rect(x, ny, width, 0).attr({
                    "data-id":i, "stroke-width":0.75, "stroke":"white", "fill":options.series[j].color
                    }).add(rectGroup);
                    ny += height;
                    
                    tempRect.animate({
                      height: height
                    }, {
                        duration: 1000
                    });

                }
            }
            for (var j=0; j<this.options.series.length; j++)
            {   var h = 1.0*this.options.series[j].xdata[i];
                if (h<=0)
                {   var height = zoom_k*Math.abs(h);
                    var tempRect = chart.renderer.rect(x, ny, width, 0).attr({
                    "data-id":i, "stroke-width":0.75, "stroke":"white", "fill":options.series[j].color
                    }).add(rectGroup);
                    ny += height;
                    
                    tempRect.animate({
                      height: height
                    }, {
                        duration: 1000
                    });

                }
            }
            
           
        }; // for loop ends over all rect


        // add tooltip to rectangulars AND labels (rectGroup)
        var tooltipIndex;

        rectGroup.on('mouseover', function (e) {

            //get the active element (or is there a simpler way?)
            var el = (e.target.correspondingUseElement) ? e.target.correspondingUseElement : e.target;

            //determine with the 'id' to which dataPoint this element belongs
            //problem: if label is hovered, use tootltipIndex of rect
            var i = parseFloat(el.getAttribute('data-id'));
            if (!isNaN(i)) {
                tooltipIndex = i;
            }
            
            if (lastHover!=i)
            {   lastHover=i;                
                var bx = el.getAttribute('x'), by = 20;
                
               // render text for tooltip based on coordinates of rect
                var s = '<b>'+cname[i]+'</b><br>';
                for (var j=0; j<series.length; j++)
                { s+=series[j].name+': '+series[j].xdata[i]+'<br>';
                }
                text = chart.renderer.text(s, bx, by)
                    .attr({
                    zIndex: 101
                }).add();
                
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



