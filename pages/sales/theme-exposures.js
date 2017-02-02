function mdSelect(selector)
{   var rows = [];
    var model = $(selector).parents('.bs-model-select:first').attr('data-model')+'/load';
    var onselect = null;
    var onloaded = null;
    ajx(model,{},function(d){        
        var s = ''; 
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            rows[r.id] = r;
            s+='<option value="'+r.id+'">'+r.name+'</option>';
        }
        $(selector).html(s);
        if (onloaded!=null) onloaded(rows);
    });
    
    $(selector).click(function(e){
        var id = $(selector).val();        
        if (onselect!=null) onselect( rows[id], id );
    });
    
    function select(fu){ onselect = fu; }
    
    function loaded(fu){ onloaded = fu; }
    
    return {select:select, loaded:loaded};
}

function barChart(selector)
{   var ctx, width,  height, stepy, stepx;
    var pad = 20;
    var minh = -2;
    var maxh = 2;
    var header = ['Climate change', 'Demographics','Regulation','Another theme'];
    var series = {r:[-1.5,1.9,0.7,1.2], l:[0.5, 1.5, 0.3, 1.7]};

    ctx = $(selector)[0].getContext("2d");
    ctx.font = "bold 12px sans";
    
    var wbar; // width of a bar
    
    function barL(y1,y2,c)
    {   var x1 = pad+c*stepx+stepx/2-wbar;
        var y1 = height/2 + y1*stepy;
        var y2 = height/2 + y2*stepy;
        ctx.fillRect(x1, y1, wbar, y2-y1);
    }
    function barR(y1,y2,c)
    {   var x1 = pad+c*stepx+stepx/2;
        var y1 = height/2 + y1*stepy;
        var y2 = height/2 + y2*stepy;
        ctx.fillRect(x1, y1, wbar, y2-y1);
    }
    
    function wrapText(text, x, y, maxWidth, lineHeight) 
    {   var words = text.split(' ');
        var line = '';
        var center = true;
        var cntr = 0;
        
        for(var n = 0; n < words.length; n++) 
        {
          var testLine = line + words[n] + ' ';
          var metrics = ctx.measureText(testLine);
          var testWidth = metrics.width;          
          
          if (testWidth > maxWidth && n > 0) 
          {  if (center) cntr = (maxWidth-ctx.measureText(line).width)/2;        
             ctx.fillText(line, x+cntr, y);
             line = words[n] + ' ';             
             y += lineHeight;
          }
          else 
          {  line = testLine;
          }
        }
        if (center) cntr = (maxWidth-ctx.measureText(line).width)/2;        
        ctx.fillText(line, x+cntr, y);
    }

    function draw()
    {   width = $(selector).width();
        height = $(selector).height()-20;
        stepy = (height-pad*2)/(Math.abs(minh)+maxh);
        stepx = (width-pad*2)/header.length;
        wbar= stepx*0.25;
        ctx.clearRect(0,0,width,height);
        ctx.strokeStyle = "#000000";
        ctx.fillStyle   = "#000000";

        ctx.moveTo(pad, pad);
        ctx.lineTo(pad, height-pad);        
        var y;
        var j = minh;
        for (y=pad; y<height; y+=stepy)
        {  ctx.moveTo(pad-3, y);
           ctx.lineTo(pad+3, y);  
           ctx.fillText(j, pad-18, y+4);
           j++;           
        }
        
        var x;
        ctx.stroke(); 
        ctx.strokeStyle = "#D7D7D7";
        j=0;
        
        for (x=pad+stepx; x<width; x+=stepx)
        {  //ctx.moveTo(x, pad);
           //ctx.lineTo(x, height-pad);
           wrapText(header[j], x-stepx, height-5, stepx, 20);
           j++;
        }
        
        ctx.stroke(); 
        
        for (var i=0; i<header.length; i++)
        {   ctx.fillStyle = "#0772A4";
            barL(0, series.l[i], i);
            ctx.fillStyle = "#7F7F7F";
            barR(series.r[i], 0, i);
        }
        
    }
    
    function setData(h, leftarr, rightarr)
    { header = h;
      series.l = leftarr;
      series.r = rightarr;
      draw();
      
    }
    
    return {draw:draw, setData:setData};
}


function arrayList(selector)
{  var pager = null;
   var rows_onpage = 10;
   var d = null;
   var rows_total = 0;
   
   pager = new modelPagination(selector+' .list-pager');
   pager.change(function(page){
        //console.log(page);
        drawPage(page);
   });

   function toSgFloat(v, decimals)
   { var n = 1.0*v;            
      if (isNaN(n) || v==null)
      {   return '-';
      }
      if (n>0) return '+'+n.toFixed(decimals);
      else return n.toFixed(decimals);
   }   
 
   function setData(data)
   {   d = data;
       rows_total = d.data1.clist.length;
       pager.setTotal(rows_total, rows_onpage);
       var s = '<tr><th>Company</th><th>ISIN</th><th>Subsector</th>';
       var h = d.header;
       for (var i=0; i<h.length; i++) s+='<th>'+h[i]+'</th>';
       s+='</tr>';
       $(selector+' .table thead').html(s);
       drawPage(1);
   }
   
   function drawPage(p)
   {  var rr = d.data1.clist;
      var s = '';      
      var start = (p-1)*rows_onpage;
      var h = d.header;
      for (i=start; i<(start+rows_onpage) && i<rows_total; i++)
      { s +='<tr>';
        if (rr[i].name==undefined) console.log('i='+i);
        s +='<td>'+rr[i].name+'</td>';
        s +='<td>'+rr[i].isin+'</td>';
        s +='<td>'+rr[i].subsector+'</td>';
        for (var j=0; j<h.length; j++) s+='<td>'+toSgFloat( rr[i]['p'+(j+1)], 1)+'</td>';
        s +='</tr>';
      }
      $(selector+' .table tbody').html(s);
   }
   
   return {setData:setData}
}


$(function(){
    
    // var chart = new barChart('#chart');
   var list_companies = new arrayList('#list-companies');
    
   function print()
   { fprint.title.value = 'Theme exposures';     
     fprint.svg.value = $('#container svg').get(0).outerHTML;
     fprint.submit();
   }
    
    function reloadChartData()
    {   var pf1 = $('#portfolio').val();
        var pf2 = $('#comparison').val();
        // if (pf2!=pf1 && pf2!=null)
        if (pf2!=null)
        {   $('#portfolio').attr('disabled', true)
            $('#comparison').attr('disabled', true)
            ajx('/pages/sales/ComparePortfolio',{pf1:pf1, pf2:pf2},function(d){
                list_companies.setData(d);
                // console.log(d) 
                // chart.setData(d.header, d.data1.data, d.data2.data);
                for (var i=0; i<d.data1.data.length; i++)
                {  d.data1.data[i] = 1.0*d.data1.data[i];
                   d.data2.data[i] = 1.0*d.data2.data[i];
                }
                var params = {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: 'Theme exposures'
                    },
                    xAxis: {
                        categories: d.header
                    },
                    yAxis: {
                        title: {text:'Exposure (positive or negative)'}
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: d.name1,
                        data: d.data1.data
                    }, {
                        name: d.name2,
                        data: d.data2.data
                    }]
                };
                // console.log(params);
                Highcharts.chart('container', params);
                
                $('#portfolio').attr('disabled', false);
                $('#comparison').attr('disabled', false);
                $('.b-print').attr('disabled', false);
            } );
        }
    }
    
    var portf = new mdSelect('#portfolio');
    portf.select(function(r){
        $('#description').val(r.description);       
        
    });
    
    portf.loaded(function(rows){
        $('#portfolio').trigger('click');
    });
    
    
    var compar = new mdSelect('#comparison');
    compar.select(function(r){
        // reloadChartData();
    });
    
    $('.b-vchart').click(function(){
        reloadChartData();
    });
    
    $('.b-print').click(print);
    
    
});
