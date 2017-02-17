function drawStackedGradient(id, data)
{
    console.log(data);
    var options =  {
        chart: {
            renderTo: id,
            type: 'column'
        },
        title: {
            text: 'Metric by portfolio'
        },
        xAxis: {
            categories: [data.name1, data.name2]
        },
        yAxis: {
            title: {
                text: data.metric
            },
            min: 0,
            max: 100
        },
        tooltip: {
              pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
              shared: true
        },
        legend: {
            enabled: true
        },
        credits: {
            enabled: false
        },
        series: [{name: 'Portfolio', data:[]}, {name: 'Comparison', data:[]}]
    };
    
    var n = -1;
    var raw = [];
    
    // var start_color = "#324778";
    var start_color = Highcharts.getOptions().colors[0];
    
   // var c_delta = 1.0/raw[0].data.length*0.5;
    // attrOptions.fill = colorLumin(Highcharts.getOptions().colors[0], j*0.2);
    
    /*
    for (var i=0; i<raw.length; i++)
    {  options.widths[i] = raw[i].width;
       options.cname[i]=raw[i].cname;
       options.isin[i]=raw[i].isin;
       for (var j=0; j<raw[i].data.length; j++)
       { if (options.series[j]==undefined) options.series[j] = {name:data.p1.names[j], data:[], xdata:[], color:colorLumin(start_color, j*c_delta)};
         options.series[j].xdata[i] = raw[i].data[j];
       }
    }
    */
    
    // console.log(raw);
    // console.log(options);
    
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

        
        for (var i = 0; i < series.length; i++) {
           
            var x = next_x,
                y = points[i] + addMarginY,
                width = (this.xAxis[0].width)*(widths[i]/total_width),
                height = (this.yAxis[0].height)*(total_heights[i]/yMax);
                next_x += width;


            xAll.push(x);
            yAll.push(this.chartHeight-height-addMarginY);
            widthAll.push(width);
            heightAll.push(height);

            attrOptions = {
                    id: i,
                        'stroke-width': 0.75,
                    stroke: 'white',
                    fill: options.series[0].color
                   // fill: Highcharts.getOptions().colors[0]
             };

            // draw rect, y-position is set to yAxis for animation            
            //var tempRect = chart.renderer.rect(x, this.chartHeight-height-this.yAxis[0].bottom, width, 0, 0)
            var tempRect = chart.renderer.rect(x, 0, width, 0, 0)
                .attr(attrOptions)
                .add(rectGroup);

            //animate rect
            tempRect.animate({
                y: this.chartHeight-height-this.yAxis[0].bottom,
                height: height

            }, {
                duration: 1000
            });
            
            // draw other series bars
            var pre = 0;
            
            for (var j=1; j<this.options.series.length; j++)
            {  //attrOptions.fill = Highcharts.getOptions().colors[j];
               attrOptions.fill = options.series[j].color;               
               
               height = (this.yAxis[0].height)/yMax*this.options.series[j].xdata[i];
               
               if (j>1) pre += (this.yAxis[0].height)/yMax*this.options.series[j-1].xdata[i];
               
               var tempRect = chart.renderer.rect(x, 0, width, 0, 0)
                .attr(attrOptions)
                .add(rectGroup);               
                
                tempRect.animate({
                    y: this.chartHeight-height-this.yAxis[0].bottom-pre,
                    height: height

                }, {
                    duration: 1000
                });
            }
            
           
        }; // for loop ends over all rect


        // add tooltip to rectangulars AND labels (rectGroup)
        var tooltipIndex;

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
                var bx = xAll[tooltipIndex], by = yAll[tooltipIndex];            
               // console.log(i);
               // render text for tooltip based on coordinates of rect
                var s = '<b>'+cname[i]+'</b><br>'                
                for (var j=0; j<series.length; j++)
                { s+=series[j].name+': '+series[j].xdata[i]+'<br>';
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



