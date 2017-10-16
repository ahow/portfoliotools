function circlesBestWorstChart(div,d)
{   
    var options = {
        chart: {
            type: 'bar',
            renderTo: div,
        },
        title: {
            text: 'ESG analysis'
        },
        xAxis: {
        },
        yAxis: {
            min: 1,
            max: 100,
            labels: {
                 enabled: true
            }
        },
        tooltip: {
            valueSuffix: ' millions'
        },
        legend: {
            enabled: false,
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -140,
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
    options.xdata = d.rows;
    options.xAxis.plotLines = [];
    var categories = ['Portfolio','Holdings'];
    var zeroes = [0,0];
    var xmin = Number.MAX_VALUE, xmax=Number.MIN_VALUE;
    var pwmax = xmax;
    for (var i=0; i<d.rows.length; i++){
    	d.rows[i].val*=1.0;
        var r = d.rows[i];
        var p = 1.0*r.pval;
        if (p>pwmax) pwmax=p;
        if (r.val>xmax) xmax=r.val;
        if (r.val<xmin) xmin=r.val;
    }
    
    // xmax += xmax/5;
    // xmin -= xmin/5;
    
    var dx = xmax-xmin;
    var bw = [d.pfsum, d.cmsum];
      
    xmin -= dx*0.2;
    xmax += dx*0.2;
        
    options.yAxis.min = xmin;
    options.yAxis.max = xmax;
    
    options.xAxis.categories = categories;
    options.series = [{name:'Portfolio', data:zeroes}, {name:'Comparison', data:zeroes}];
    
    for (var i=0; i<categories.length; i++){
        options.xAxis.plotLines.push({color: '#D0D0D0', width: 1, value: i});
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
      var rad = delta_y*0.7/2;

      
      var data = this.options.xdata;   
      var ax = this.yAxis[0];
      // var zoom_k =  ax.transA;      

      //var pt = [xmin, xmax];
      // var by = addMarginY+delta_y/2+delta_y*i;
      var by = addMarginY+delta_y/2;
      
      // var nzoom = ax.width/(ax.max-ax.min);
      var nzoom = ax.transA;

      var attr = {"stroke-width":0.75, stroke:"white", fill:'#D0D0D0'};
            
      var delta_x = -ax.min*nzoom;
      for (var i=bw.length-1; i>=0; i--)
      {  attr['data-value']=bw[i];
         attr['data-name']=this.options.series[i].name;
         attr.fill = this.series[i].color;         
         var nx = delta_x+addMarginX+bw[i]*nzoom;        
         var cc = chart.renderer.circle(addMarginX, by, rad).attr(attr).add(rectGroup);
         cc.animate({
               cx: nx
         }, {
               duration: 1000
         });
      }


      by = addMarginY+delta_y/2+delta_y;
      for (var i=0; i<data.length; i++)
      {  attr.id=''+i+'-0';
         attr.fill = '#ADD8E6';
         attr['data-value']=data[i].val;
         attr['data-name']=data[i].name;
         var nx = delta_x+addMarginX+nzoom*data[i].val;
         var r = rad*0.8*data[i].pval/pwmax+0.2*rad;
         var cc = chart.renderer.circle(addMarginX, by, r).attr(attr).css({'fill-opacity':0.5}).add(rectGroup);
         cc.animate({
               cx: nx
         }, {
               duration: 1000
         });
      }

      
      

        // add tooltip to rectangulars AND labels (rectGroup)
        var tooltipIndex;
        var cname=[data.name1, data.name2];
        var lastVal = null;
        
        rectGroup.on('mouseover', function (e) {

            //get the active element (or is there a simpler way?)
            var el = (e.target.correspondingUseElement) ? e.target.correspondingUseElement : e.target;

            //determine with the 'id' to which dataPoint this element belongs
            //problem: if label is hovered, use tootltipIndex of rect
            var val = el.getAttribute('data-value');            
            var name = el.getAttribute('data-name'); 
                        
            if (lastVal!=val)
            {   lastVal=val;
                var bx = 1*el.getAttribute('cx'), by = 1*el.getAttribute('cy');
               // render text for tooltip based on coordinates of rect
                var s = '<b>'+name+'</b><br>'+val;
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
               lastVal=null;
            }
        });


    });
}

    
