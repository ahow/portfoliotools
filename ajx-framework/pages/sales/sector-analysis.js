$(function(){

    
    function toFloat(v, decimals)
    { var n = 1.0*v;            
      if (isNaN(n) || v==null)
      {   return '-';
      }
      return n.toFixed(decimals);
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
    let dsic, dsubsec, dsector, last_sic = null;
    let views = new htviewCached();
    
    let arrayList = new arrayListTable('.array-list');
    // arrayList.setHeader([
    //     {title:"Name",f:'name', ondraw:function(v, r){ 
    //         return '<a target="_blank" href="'+link+'/'+r.id+'">'+v+'</a>';
    //     }},
    //     {title:d.xtitle, f:'x'},
    //     {title:d.ytitle, f:'y'}]
    // );
    

    function reloadData()
    {   let p = {};
        p.region = $('#region').val();   
        p.sector = $('#sector').val();     
        p.subsector = $('#subsector').val(); 
        p.sic = last_sic;        
        ajx('/pages/sales/SectorAnalysis',p, function(d){            
            arrayList.setHeader(d.header);
            arrayList.setData(d.rows);            
            drawChart(d);         
        });
    }
        
    views.view('/pages/sales/search','#search_sic', function(){        
        dsic = new searchDialog('#search_sic', "/pages/sales/Model/sic",'Search SIC');
        dsic.select(function(sr, target){
            $('#sic_code input').val(sr.name);
            $('#subsec input').val('');
            last_sic = sr.id;
            reloadData();
        });
        
        $('#sic_code .w-open-modal').click(function(){
            dsic.open();
        });

        $('#sic_code .w-bclear').click(function(){
            last_sic = null;
            $('#sic_code input').val('');
            reloadData();
        });
        
    });

    views.view('/pages/sales/search','#search_subsec', function(){        
        dsubsec = new searchDialog('#search_subsec', "/pages/sales/Model/subsector",'Search Subsector');
        dsubsec.select(function(sr, target){
            $('#subsec input').val(sr.subsector); 
            $('#sic_code input').val('');            
            reloadData();
            // loadSubsector(sr.id);
        });
        
        $('#subsec .w-open-modal').click(function(){            
            dsubsec.open();            
        });

        $('#subsec .w-bclear').click(function(){                        
            $('#subsec input').val('');
            reloadData();          
        });
    });
    
    views.view('/pages/sales/search','#search_sector', function(){        
        dsector = new searchDialog('#search_sector', "/pages/sales/Model/sector",'Search Sector');
        dsector.select(function(sr, target){                        
            $('#sector_id input').val(sr.sector);
            const model = dsubsec.getModel();
            // model.setParam('sector',sr.sector);            
            reloadData();
        });
        
        $('#sector_id .w-open-modal').click(function(){                        
            dsector.open();            
        });

        $('#sector_id .w-bclear').click(function(){                        
            $('#sector_id input').val('');
            reloadData();          
        });
    });
    
    
    $('#region').click(function(){
        reloadData();        
    });
    

    
    /* ------------------ Chart ---------------------------*/
    
    //  function drawChart(d)
    // {   Highcharts.chart('chart', {
    //         chart: {
    //             zoomType: 'xy'
    //         },
    //         title: {
    //             text: d.title
    //         },
    //         xAxis: [{
    //             categories: d.categories,
    //             crosshair: true
    //        }],
    //         yAxis: [{ // Primary yAxis
    //             title: {
    //                 text: d.nameL,
    //             },
    //             opposite: false

    //         }, 
    //         { // Secondary yAxis
    //             gridLineWidth: 0,
    //             title: {
    //                 text: d.nameR
    //             },
    //             labels: {
    //                 style: {
    //                     color: Highcharts.getOptions().colors[1]
    //                 }
    //             },
    //             opposite: true
    //         }],
    //         tooltip: {
    //             shared: true
    //         },
    //         legend: {
    //             layout: 'vertical',
    //             align: 'left',
    //             x: 80,
    //             verticalAlign: 'top',
    //             y: 55,
    //             floating: true,
    //             backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
    //         },
    //         series: [{
    //             name: d.nameL,
    //             type: 'spline',
    //             yAxis: 0,
    //             data: d.data[0],
    //             marker: {
    //                 enabled: true
    //             }
    //         }, {
    //             name: d.nameR,
    //             type: 'spline',
    //             yAxis: 1,
    //             data: d.data[1],
    //             marker: {
    //                 enabled: true
    //             }
    //         }]
    //     });
    // }

    function drawChart(d)
    {   let header = [];
        let data = [];
        for (let i=0; i<d.chart.length; i++)
        {  let r = d.chart[i]
           data.push(1.0*r.theam_value)
           header.push(r.theam)           
        }
        var params = {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Theme exposures'
            },
            xAxis: {
                categories: header
            },
            yAxis: {
                title: {text:'Exposure (positive or negative)'}
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'Themes',
                data: data
            }]
        };
        console.log(params);
        Highcharts.chart('chart', params);
        
        // $('#portfolio').attr('disabled', false);
        // $('#comparison').attr('disabled', false);
        // $('.b-print').attr('disabled', false);
    }

 
});
