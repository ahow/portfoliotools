$(function(){
   
    var dsic, dsubsec, last_id = null, last_data = null, select_mode = '';
     
    function toFloat(v, decimals)
    { var n = 1.0*v;            
      if (isNaN(n) || v==null)
      {   return '-';
      }
      return n.toFixed(decimals);
    }
    
    function reloadChartData()
    {   var prm = {};
        if (select_mode!='')
        {   prm.mode = select_mode;
            prm.id = last_id;
            prm.region = $('#region').val();
            prm.xaxis = $('#x-axis').val();
            prm.yaxis = $('#y-axis').val();
            
            var minsize = $('#minsize').val();
            if (minsize!='') prm.min_size = minsize;
            
            ajx('/pages/sales/CompaniesAnalysis',prm,function(d){
                last_data = d;
                var param = {
                    chart: {
                        type: 'scatter',            
                        zoomType: 'xy'
                    },
                    title: {
                        text: d.xtitle+' Versus '+d.ytitle
                    },
                    xAxis: {
                        title: {
                            enabled: true,
                            text: d.xtitle
                        },
                        startOnTick: true,
                        endOnTick: true,
                        showLastLabel: true
                    },
                    yAxis: {
                        title: {
                            text: d.ytitle
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'left',
                        verticalAlign: 'top',
                        x: 100,
                        y: 70,
                        floating: true,
                        backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF',
                        borderWidth: 1
                    },
                    plotOptions: {
                        scatter: {
                            marker: {
                                radius: 5,
                                states: {
                                    hover: {
                                        enabled: true,
                                        lineColor: 'rgb(100,100,100)'
                                    }
                                }
                            },
                            states: {
                                hover: {
                                    marker: {
                                        enabled: false
                                    }
                                }
                            },
                            tooltip: {
                                headerFormat: '<b>{point.key}</b><br>',
                                pointFormat: d.xtitle+':{point.x}, '+d.ytitle+':{point.y}'
                            }
                        },
                        series:{  turboThreshold:150000 }
                    },
                    series: [{
                        name: 'Companies',
                        color: 'rgba(03, 83, 223, .5)',
                        data: d.xdata
                    }]
                };
                 
                Highcharts.chart('container', param);
                $('.b-csv').attr('disabled', false);
            });
       
        }
    }


   $('.bs-model-select').each(function(i,e){
        var sel = $(e);
        var model = sel.attr('data-model')+'/load';
        ajx(model,{},function(d){
            var s = '<option value="Global">Global</option>';
            for (var i=0; i<d.rows.length; i++)
            {   var r = d.rows[i];
                s+='<option value="'+r.id+'">'+r.name+'</option>';
            }
            sel.find('select').html(s);
        });

    });
    
   //  var sic_subsector = new lookupInput('#sic_subsector','/pages/sales/Model/sic_subsector-lookup/load');     
   
    var views = new htviewCached();
    
    function loadSubsector(sub)
    {  ajx('/pages/sales/MarketSummarySubsector',{subsector:sub, region:$('#region').val(),
            min_size:$('#minsize').val()
        },function(d){
             //  drawSummary(d);
       });
    }

    function loadSIC(sic)
    {  ajx('/pages/sales/MarketSummarySic',{sic:sic, region:$('#region').val(),
            min_size:$('#minsize').val()
        },function(d){
              // drawSIC(d);
       });
    }
        
    views.view('/pages/sales/search','#search_sic', function(){        
        dsic = new searchDialog('#search_sic', "/pages/sales/Model/sic",'Search SIC');
        dsic.select(function(sr, target){
            $('#sic_code input').val(sr.name);
            $('#subsec input').val('');
            last_id = sr.id;
            select_mode='SIC';
        });
        
        $('#sic_code button').click(function(){
            dsic.open();
        });
    });

    views.view('/pages/sales/search','#search_subsec', function(){        
        dsubsec = new searchDialog('#search_subsec', "/pages/sales/Model/subsector",'Search Subsector');
        dsubsec.select(function(sr, target){
            $('#subsec input').val(sr.subsector);
            $('#sic_code input').val('');
            select_mode='Subsector';
            last_id = sr.id;
        });
        
        $('#subsec button').click(function(){
            dsubsec.open();            
        });
    });    
    
    $('#year').val(new Date().getFullYear()-1);
    
    $('#region').click(function(){
       reloadChartData();
    });
    
    $('#minsize').click(function(){
       reloadChartData();
    });
    
    $('.b-vchart').click(function(){
        reloadChartData();
    });
    
    $('.b-csv').click(function(){
        var d = last_data;
        var csv = '"Company","'+d.xtitle+'","'+d.ytitle+"\"\n";
        for (var i=0; i<d.xdata.length; i++)
        {   var r = d.xdata[i];
            csv+='"'+r.name.replace("\n",'\\n').replace('"','\"')+'",'+r.x+','+r.y+"\n";
        }
        download(csv,'company_analisys.csv');
     });
    
});
