function drawStackedGradient(id, data)
{
    var options =  {
        chart: {
            renderTo: id,
            type: 'waterfall'
        },
        title: {
            text: 'Metric by portfolio'
        },
        xAxis: {
            // categories: [data.name1, data.name2],
            type: 'category' 
        },
        yAxis: {
            title: {
                text: data.metric
            },
            min: 0,
            max: 100
        },
        legend: { enabled: true },
        credits: { enabled: false },
        dataLabels: { enabled: true},
        series: [{name: 'Portfolio', data:[]}, {name: 'Comparison', data:[]}]
    };
    
    var n = -1;
    var raw = [];
    
    var total_heights = [];
    var yMax=0; //options.series[0].xdata[0];
    
    
    for (var i = 0; i < data.p1.data.length; i++)
    {  var p = data.p1.data;
       if (total_heights[0]==undefined) total_heights[0]=0;
       total_heights[0]+=1.0*p[i];
       if (yMax<total_heights[0]) yMax=total_heights[0];

       p = data.p2.data;
       if (total_heights[1]==undefined) total_heights[1]=0;
       total_heights[1]+=1.0*p[i];           
       if (yMax<total_heights[1]) yMax=total_heights[1];       
    }
    
    options.yAxis.max = yMax;
    
    var chart = new Highcharts.Chart(options,
    //add function for custom renderer
    function (chart) {
        var series = this.options.series,
            addMarginX = this.plotLeft,
            addMarginY = this.plotTop,
            xAll = [],
            yAll = [],
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
        var delta_x = this.xAxis[0].width/(series.length+1);
        var col_width = delta_x*0.75;        
        
        var zoom_k = this.yAxis[0].transA;                
        var sy = this.chartHeight-this.yAxis[0].bottom;        
        var pt = [data.p1, data.p2];   
        
        var c_delta = 1.0/pt.length*0.5; 
        
        var start_colors = [Highcharts.getOptions().colors[0], Highcharts.getOptions().colors[1]];
        for (var i = 0; i < pt.length; i++) {
            var p = pt[i];
            var ph = 0;
            next_x = addMarginX + delta_x*(i+1) - col_width/2;
            
            for (var j=p.data.length-1; j>=0; j--)
            {   var height = zoom_k*p.data[j];
                var tempRect = chart.renderer.rect(next_x, sy-height-ph, col_width, 0).attr({
                "id":i, "stroke-width":0.75, "stroke":"white", "fill":colorLumin(start_colors[i], j*c_delta)
                }).add(rectGroup);
                ph = height;
               
                tempRect.animate({
                  height: height
                }, {
                    duration: 1000
                });
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
                { s+=pt[i].names[j]+': '+pt[i].data[j]+'<br>';
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
                    fill: 'rgba(255, 255, 255, 0.95)',
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



