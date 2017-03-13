function circlesChart(div,d)
{   
    var options = {
        chart: {
            type: 'bar',
            renderTo: div,
        },
        title: {
            text: d.title
        },
        xAxis: {
            categories: d.categories,
            title: {
                text: null
            }
        },
        yAxis: {
            min: 0,
            max: 300,
            title: {
                text: d.xtitle,
                align: 'high'
            },
            labels: {
                overflow: 'justify'
            }
        },
        tooltip: {
            valueSuffix: ' millions'
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: false
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -40,
            y: 80,
            floating: true,
            borderWidth: 1,
            backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
            shadow: true
        },
        credits: {
            enabled: false
        },
        series: d.series
        };
    options.xdata = [];
    options.xAxis.plotLines = [];
    for (var i=0; i<d.series.length; i++){
    	options.xdata.push( $.extend(true,{}, d.series[i]) );
    }
    for (var i=0; i<d.categories.length; i++){
        options.xAxis.plotLines.push({color: '#D0D0D0', width: 1, value: i});
    }
    var xmax = Number.MIN_VALUE;
    var xmin = Number.MAX_VALUE;
    for (var i=0; i<d.series.length; i++)
    {  for (var j=0; j<d.series[i].data.length; j++)
       { var n = d.series[i].data[j];
         if (typeof(n)=='array')
         {	for (k=0; k<n.length; k++)
            {  if (xmax<n) xmax=n;
         		   if (xmin>n) xmin=n;             
            }
         }
         if (xmax<n) xmax=n;
         if (xmin>n) xmin=n;         
       }
       d.series[i].data = [];
       for (var j=0; j<d.categories.length; j++) d.series[i].data[j]=0;
    }
    
    
    var decim = (xmax-xmin)/10.0;
    options.yAxis.max = xmax+decim;
    options.yAxis.min = xmin-decim;
    
    new Highcharts.Chart(options, function (chart) {
        var series = this.options.series,
            addMarginX = this.plotLeft,
            addMarginY = this.plotTop,           
            heightAll = [];
        
        var lastHover = -1;

        //renderer group for all circles
        rectGroup = chart.renderer.g()
            .attr({
            zIndex: 5
        }).add();

        //draw for each point a rectangular
                
      var delta_y = this.yAxis[0].height/series[0].data.length;     
      var attr = {"stroke-width":0.75, stroke:"white", fill:'#D0D0D0'};
      var rad = delta_y*0.7/2;

      
      var data = this.options.xdata;   
      var zoom_k =  this.axes[1].transA;
      var delta_x = -this.axes[1].min*zoom_k;
      
      for (var i=0; i<data.length; i++)
      {		
          for (var j=0; j<data[i].data.length; j++)
          {  attr.id=''+i+'-'+j;
             attr.fill = this.series[i].color;
             var nx = delta_x+addMarginX+data[i].data[j]*zoom_k;
            // console.log(nx, data[i].data[j], zoom_k);
             var cc = chart.renderer.circle(addMarginX, addMarginY+delta_y/2+delta_y*j, rad).attr(attr).add(rectGroup);
             cc.animate({
                  cx: nx
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
            var sid = el.getAttribute('id');
            var aid = sid.split('-');
            var i = 1*aid[0];
            var j = 1*aid[1];
            
            
            if (lastHover!=sid)
            {   lastHover=sid;
                var bx = 1*el.getAttribute('cx'), by = 1*el.getAttribute('cy');
               // console.log(i);
               // render text for tooltip based on coordinates of rect
                var s = '<b>'+series[i].name+'</b> '+data[i].data[j];                                
                text = chart.renderer.text(s, bx, by)
                    .attr({
                    zIndex: 101
                }).add();

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


// example of use
circlesChart('container', {
        title: 'Test title',
        xtitle: 'Sales',
        categories: ['Metric1', 'Metric2', 'Metric3', 'Metric4'],
        series:[
            {name:'Metric', data:[10,8,7,16]}, 
            {name:'Comparison', data:[5,6,6,9]} 
        ]
    });
    
    
