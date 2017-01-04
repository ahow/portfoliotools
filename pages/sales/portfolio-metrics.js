/*
 * http://jsfiddle.net/drmrbrewer/215tnLna/33/
 * http://jsfiddle.net/s937de3b/1/
 * http://jsfiddle.net/8zDdA/1/
 */

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
     
    function reloadChartData()
    {   var pf1 = $('#portfolio').val();
        var pf2 = $('#comparison').val();
        var mt =  $('#metric').val();
        if (pf2!=null && mt!=null)
        {   $('#portfolio').attr('disabled', true)
            $('#comparison').attr('disabled', true)
            
             ajx('/pages/sales/StackedChart',{pf1:pf1, pf2:pf2, mt:mt},function(d){
                // console.log(d) 
                // chart.setData(d.header, d.data1.data, d.data2.data);
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
                        text: d.metric
                    }
                },
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
                Highcharts.chart('container', params);
                
                $('#portfolio').attr('disabled', false);
                $('#comparison').attr('disabled', false);
                
                drawStackedChart('stacked', d)
            } );
            
            ajx('/pages/sales/SectorAllocChart',{pf1:pf1, pf2:pf2, mt:mt},function(d){                
                drawSectorAllocChart('container2', d);                
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
    
    
    
});
