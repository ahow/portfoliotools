
$(function(){
   
    var dsic, dsubsec, last_id, last_data = null;
     
    function toFloat(v, decimals)
    { var n = 1.0*v;            
      if (isNaN(n) || v==null)
      {   return '-';
      }
      return n.toFixed(decimals);
    }
    
        
   function print()
   { fprint.title.value = 'Industry analysis';
     fprint.svg.value = $('#container svg').get(0).outerHTML;
     fprint.submit();
   }
   
    var arrayList = new arrayListTable('.array-list');
    
    function reloadChartData()
    { 
        var range = $('#theme_range').val().split(',');
        var tnames = [,'SIC','Subsector'];
        $("#mranking").LoadingOverlay("show");
        ajx('/pages/sales/ThematicIndustryComparison',{region:$('#region').val(),
            theme_min:range[0], theme_max:range[1],
            theme_id:$('#themes').val(),
            xaxis:$('#x-axis').val(),
            yaxis:$('#y-axis').val()
        }, function(d){
            
            var x = $('#x-axis').val();
            var y = $('#y-axis').val();
            d.xtitle = $('#x-axis option[value="'+x+'"]').html();
            d.ytitle = $('#y-axis option[value="'+y+'"]').html();
            // console.log(d);

            var link = "<?php echo mkURL('/sales/sic'); ?>";
            
            arrayList.setHeader([
                    {title:"Name",f:'name', ondraw:function(v, r){ 
                        return '<a target="_blank" href="'+link+'/'+r.id+'">'+v+'</a>';
                    }},
                    {title:d.xtitle, f:'x'},
                    {title:d.ytitle, f:'y'}]
            );
            arrayList.setData(d.xdata);
            
             $("#mranking").LoadingOverlay("hide", true);

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
                            pointFormat: d.xtitle+': {point.x:.2f}, '+d.ytitle+': {point.y:.2f}'
                        }
                    },
                    series:{  turboThreshold:150000 }
                },
                series: [{
                    name: tnames[$('#sic_subsector').val()],
                    color: 'rgba(03, 83, 223, .5)',
                    data: d.xdata
                }]
            };
             
            Highcharts.chart('container', param);
            $('.b-print').attr('disabled', false);
            $('.b-csv').attr('disabled', false);
        });
       
    }


$('.bs-model-select').each(function(i,e){
        var sel = $(e);
        var model = sel.attr('data-model')+'/load';
        var firstOption = sel.attr('data-option');        
        ajx(model,{},function(d){
            var s = '';
            if (firstOption==undefined) s = '<option value="Global">Global</option>';  
            else if (firstOption!='')  s = '<option value="'+firstOption+'">'+firstOption+'</option>';
            for (var i=0; i<d.rows.length; i++)
            {   var r = d.rows[i];
                s+='<option value="'+r.id+'">'+r.name+'</option>';
            }
            sel.find('select').html(s);
        });

    });
    
   //  var sic_subsector = new lookupInput('#sic_subsector','/pages/sales/Model/sic_subsector-lookup/load');     
   
    var views = new htviewCached();
    
    $("input.bs-range").slider({});

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
    

    $('.b-vchart').click(function(){
        reloadChartData();
    });
    
     $('.b-csv').click(function(){
        var d = last_data;
        var csv = '"Name","'+d.xtitle+'","'+d.ytitle+"\"\n";
        for (var i=0; i<d.xdata.length; i++)
        {   var r = d.xdata[i];
            csv+='"'+r.name.replace("\n",'\\n').replace('"','\"')+'",'+r.x+','+r.y+"\n";
        }
        download(csv,'industry_analisys.csv');
     });
    
    $('.b-print').click(print);

    
});
