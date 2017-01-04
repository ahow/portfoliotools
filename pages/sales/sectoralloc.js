function drawSectorAllocChart(id, data)
{

    var options =  {
        chart: {
            renderTo: id,
            type: 'column'
        },
        title: {
            text: 'Stock selection, Sector allocation'
        },
        xAxis: {
            categories: data.names,
            type: "category"
        },
        yAxis: [{
            title: {
                text: 'Metric'
            },
            min: 0,
            max: 100
        }],
        tooltip: {
            min: 0,
            max: 100,
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
        series: [{name:data.names[0], data:[]},
                {name:data.names[1], data:[]},
                {name:data.names[2], data:[]},
                {name:data.names[3], data:[]}]
    };
    
    var n = -1;
    var raw = [];
    
    var total_width = 0;
    var total_heights = [];
    var yMax=data.xdata[0].h; // max Height
    
    for (var i = 0; i < data.xdata.length; i++)
    {  var h = data.xdata[i].y+data.xdata[i].h;
        if (yMax<h) yMax=h;
    }
    
    options.yAxis[0].max = yMax;
    // options.yAxis[1].max = yMax;
    // options.yAxis[0].title = {text:data.metric};
    
    var chart = new Highcharts.Chart(options,
    //add function for custom renderer
    function (chart) {
        var series = this.options.series,
            cname = data.names,
            points = data.xdata,
            addMarginX = this.plotLeft,
            addMarginY = this.plotTop;
        
        var lastHover = -1;

        //renderer group for all rectangulars
        rectGroup = chart.renderer.g()
            .attr({
            zIndex: 5
        }).add();

        //draw for each point a rectangular
        
        var next_x = addMarginX;
        
        yscale = this.yAxis[0].height/this.yAxis[0].max;

        var col_width = (this.xAxis[0].width-addMarginX/2)/points.length;
        var bar_width = col_width*0.8;
        var bar_step = (col_width - bar_width)/2;
        
        next_x +=  bar_step;
        
        
        for (var i = 0; i < points.length; i++) {
           
            var x = next_x,
                y = addMarginY,
                height = points[i].h*yscale;
                // height = (this.yAxis[0].height)*(total_heights[i]/yMax);
                
                next_x += bar_step+col_width;

            attrOptions = {
                    id: i,
                        'stroke-width': 0.75,
                    stroke: 'white',
                    fill: Highcharts.getOptions().colors[i]
             };

            // draw rect, y-position is set to yAxis for animation            
            //var tempRect = chart.renderer.rect(x, this.chartHeight-height-this.yAxis[0].bottom, width, 0, 0)
            var tempRect = chart.renderer.rect(x, 0, bar_width, 0, 0)
                .attr(attrOptions)
                .add(rectGroup);

            //animate rect
            tempRect.animate({
                y: this.chartHeight-height-this.yAxis[0].bottom-points[i].y*yscale,
                // y: this.chartHeight-height,
                height: height
            }, {
                duration: 1000
            });
            
           
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
                //var bx = tooltipIndex*col_width+bar_step, by = this.chartHeight-height-(points[tooltipIndex].y+points[tooltipIndex].y)*yscale;
                //console.log(el.getAttribute('x'));
                var bx = el.getAttribute('x');
                var by = el.getAttribute('y')-20;
                
        
                
               // render text for tooltip based on coordinates of rect
                var s = '<b>'+cname[i]+'</b><br>'                
                s+=' '+(1.0*points[tooltipIndex].h).toFixed(2)+'<br>';
               
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



