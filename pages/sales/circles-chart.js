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
        },
        yAxis: {
            min: 1,
            max: 100,
            title: {
                text: d.xtitle,
                align: 'high'
            },
            labels: {
                 enabled: false
            }
        },
        tooltip: {
            valueSuffix: ' millions'
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
        }
        };
    options.xdata = d.xdata;
    options.xAxis.plotLines = [];
    var categories = [];
    var zeroes = [];
    for (var i=0; i<d.xdata.length; i++){
    	categories.push(d.xdata[i].name);
        zeroes.push(0);
        // options.xdata.push( $.extend(true,{}, d.series[i]) );
    }
    
    options.xAxis.categories = categories;
    options.series = [{name:'Portfolio', data:zeroes}, {name:'Comparison', data:zeroes}];
    
    for (var i=0; i<categories.length; i++){
        options.xAxis.plotLines.push({color: '#D0D0D0', width: 1, value: i});
    }
    // var xmax = Number.MIN_VALUE;
    // var xmin = Number.MAX_VALUE;

   function asumm(a)
   { var r=0; 
        for (var i=0; i<a.length; i++) r+=1*a[i];
        return r;
   }
      
    new Highcharts.Chart(options, function (chart) {
        var series = this.options.series,
            addMarginX = this.plotLeft,
            addMarginY = this.plotTop;
        
        var lastHover = -1;

        //renderer group for all circles
        rectGroup = chart.renderer.g()
            .attr({
            zIndex: 5
        }).add();

        //draw for each point a rectangular
      var delta_y = this.yAxis[0].height/categories.length;     
      var attr = {"stroke-width":0.75, stroke:"white", fill:'#D0D0D0'};
      var rad = delta_y*0.7/2;

      
      var data = this.options.xdata;   
      var zoom_k =  this.axes[1].transA;      

      for (var i=0; i<data.length; i++)
      {   var pt = [data[i].p, data[i].c];
          var nzoom = 100/(data[i].max-data[i].min)*zoom_k;
          var by = addMarginY+delta_y/2+delta_y*i;
          
          var delta_x = -data[i].min*nzoom;
          
          chart.renderer.text(data[i].min, addMarginX+3, by-3)
          .attr({ zIndex: 105}).css({color:'grey'}).add();
          var tx = chart.renderer.text(data[i].max, addMarginX+100*zoom_k, by-3)
          .attr({ zIndex: 105}).css({color:'grey'}).add();
          tx.attr({x:(addMarginX+100*zoom_k-tx.element.clientWidth-3)});
          
          for (var j=pt.length-1; j>=0; j--)
          {  attr.id=''+i+'-'+j;
             attr.fill = this.series[j].color;
             // attr['data-sum'] = asumm(pt[j].data);
             var nx = delta_x+addMarginX+asumm(pt[j].data)*nzoom;
             var cc = chart.renderer.circle(addMarginX, by, rad).attr(attr).add(rectGroup);
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
                var pt = [data[i].p, data[i].c];  
                var s = '<b>'+series[j].name+'</b><br>'+
                categories[j]+':<br>'+asumm(pt[j].data);
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

    
