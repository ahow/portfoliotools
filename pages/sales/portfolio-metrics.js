
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

$(function(){
    
    // var chart = new barChart('#chart');

    function print()
    { fprint.title.value = 'Portfolio metrics';     
      fprint.svg1.value = $('#container svg').get(0).outerHTML;
      fprint.svg2.value = $('#container2 svg').get(0).outerHTML;
      fprint.svg3.value = $('#stacked svg').get(0).outerHTML;
      fprint.submit();
    }
     
    function reloadChartData()
    {   var pf1 = $('#portfolio').val();
        var pf2 = $('#comparison').val();
        var mt =  $('#metric').val();
        if (pf2!=null && mt!=null)
        {   $('#portfolio').attr('disabled', true)
            $('#comparison').attr('disabled', true)
            
             var metric_name = '';
            
             ajx('/pages/sales/StackedChart',{pf1:pf1, pf2:pf2, mt:mt},function(d){
                // console.log(d) 
                // chart.setData(d.header, d.data1.data, d.data2.data);
                metric_name = d.metric;
                var ser = [];
                
                for (var i=0; i<d.p1.data.length; i++)
                {  if (ser[i]==undefined) ser[i]={name:d.p1.names[i], data:[]};
                   ser[i].data[0] = 1.0*d.p1.data[i];
                   ser[i].data[1] = 1.0*d.p2.data[i];
                }
                var params = {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: 'Metric by portfolio'
                    },
                    xAxis: {
                        categories: [d.name1, d.name2]
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: metric_name
                        }
                    },
                    legend: { enabled: false },
                    tooltip: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
                        shared: true
                    },
                    plotOptions: {
                        column: {
                            stacking: 'value'
                        }
                    },
                    series: ser
                    };
                    // console.log(JSON.stringify(params));
                    
                    for (var i=1; i<params.series.length; i++)
                     params.series[i].color = Highcharts.getOptions().colors[i+1];
                   
                    console.log(params.series);  
                    Highcharts.chart('container', params);
                    
                    $('#portfolio').attr('disabled', false);
                    $('#comparison').attr('disabled', false);
                    
                    drawStackedChart('stacked', d);
                    $('.b-print').attr('disabled', false);
                } );
                
                ajx('/pages/sales/SectorAllocChart',{pf1:pf1, pf2:pf2, mt:mt},function(d){
                    
                var params =  {
                        chart: { type: 'waterfall' },
                        title: { text: 'Sector vs stock effects' },
                        xAxis: { type: 'category' },
                        yAxis: { title: { text: metric_name } },
                        legend: { enabled: false },
                        tooltip: { pointFormat: '<b>{point.y:,.2f}</b>' },
                        series: [{data:d.xdata, 
                        dataLabels: {
                            enabled: true,
                            formatter: function () {
                                return Highcharts.numberFormat(this.y, 2, '.');
                            },
                            style: {
                                fontWeight: 'bold'
                            }
                        },
                        pointPadding: 0}
                        ]
                     };
                     params.series[0].data[0].color = Highcharts.getOptions().colors[0];
                     params.series[0].data[1].color = '#b5b5b5'; //Highcharts.getOptions().colors[2];
                     params.series[0].data[2].color = '#b5b5b5'; // Highcharts.getOptions().colors[3];
                     params.series[0].data[3].color = Highcharts.getOptions().colors[1];                     
                     for (var i=0; i<params.series[0].data.length; i++)
                        params.series[0].data[i].borderColor="#E5E5E5";

                    // console.log(JSON.stringify(params));
                     Highcharts.chart('container2', params);
                    // drawSectorAllocChart('container2', d);
            });
        }
    }
    
    var portf = new mdSelect('#portfolio');
    portf.select(function(r){
        
    });
    
    portf.loaded(function(rows){
        $('#portfolio').trigger('click');
    });
    
    var metric = new mdSelect('#metric');
    metric.select(function(r){        
        $('#description').val(r.description);
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
