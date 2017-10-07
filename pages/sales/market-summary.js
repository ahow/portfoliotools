$(function(){
    
    function toFloat(v, decimals)
    { var n = 1.0*v;            
      if (isNaN(n) || v==null)
      {   return '-';
      }
      return n.toFixed(decimals);
    }
    
    function drawSummary(d)
    {  var selector='#summary';
       var s = '<table class="table table-striped">';
        total = 0.0;
        s+='<tr><th>Subsector</th><th>Total<br>sales</th><th>% top 3</th><th>% top 5</th><th>Stability</th><th>Sales<br>growth</th><th>ROIC</th><th>PE</th><th>EVBIDTA</th><th>Payout</th><th>% reviewed</th></tr>';
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            if (r!=undefined)
            {   s+='<tr><td>'+r.subsector+'</td><td>'+toFloat(r.tsales,1)+'</td><td>'
                +toFloat(r.top3sum, 0)+'</td><td>'
                +toFloat(r.top5sum, 0)+'</td><td>'
                +toFloat(r.stability,1)+'</td><td>'+toFloat(r.asales_growth,1)+'</td><td>'
                +toFloat(r.aroic,1)+'</td><td>'+toFloat(r.ape,1)+'</td><td>'
                +toFloat(r.aevebitda,1)+'</td><td>'+toFloat(r.apayout,1)+'</td><td>'
                +toFloat(r.previewed,0)+'</td></tr>';
            }
        }
        s+='</table>';
        $(selector).html(s);
    }

   

    function drawSIC(d)
    {  $('div.p-chart').css('display','');
       var selector='#summary';
       var s = '<table class="table table-striped">';
        total = 0.0;
        s+='<tr><th>SIC</th><th>Total<br>sales</th><th>% top 3</th><th>% top 5</th><th>Stability</th><th>Sales<br>growth</th><th>ROIC</th><th>PE</th><th>EVBIDTA</th><th>Payout</th><th>% reviewed</th></tr>';
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            if (r!=undefined)
            {   s+='<tr><td>'+r.name+'</td><td>'+toFloat(r.tsales,1)+'</td><td>'
                +toFloat(r.top3sum, 0)+'</td><td>'
                +toFloat(r.top5sum, 0)+'</td><td>'
                +toFloat(r.stability, 1)+'</td><td>'+toFloat(r.asales_growth,1)+'</td><td>'
                +toFloat(r.aroic,1)+'</td><td>'+toFloat(r.ape,1)+'</td><td>'
                +toFloat(r.aevebitda,1)+'</td><td>'+toFloat(r.apayout,1)+'</td><td>'
                +toFloat(r.previewed,0)+'</td></tr>';
            }
        }
        s+='</table>';
        $(selector).html(s);
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
    var dsic, dsubsec, last_sic = null;
    var views = new htviewCached();
    
    function drawDebug(d)
    {   if (d.dbg!=undefined)
        { $('#debug').html('<pre>'+d.dbg+'</pre>')
        }
    }
    
    function loadSubsector(sub)
    {  ajx('/pages/sales/MarketSummarySubsector',{subsector:sub, region:$('#region').val(),
            min_size:$('#minsize').val()
        },function(d){
               drawSummary(d);
               drawDebug(d);
       });
    }

    function loadSIC(sic)
    {  ajx('/pages/sales/MarketSummarySic',{sic:sic, region:$('#region').val(),
            min_size:$('#minsize').val()
        },function(d){
               drawSIC(d);
               drawDebug(d);
       });
    }
        
    views.view('/pages/sales/search','#search_sic', function(){        
        dsic = new searchDialog('#search_sic', "/pages/sales/Model/sic",'Search SIC');
        dsic.select(function(sr, target){
            $('#sic_code input').val(sr.name);
            $('#subsec input').val('');
            last_sic = sr.id;
            loadSIC(last_sic);
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
            loadSubsector(sr.id);
        });
        
        $('#subsec button').click(function(){            
            dsubsec.open();            
        });
    });    
    
    $('#year').val(new Date().getFullYear()-1);
    
    $('#region').click(function(){
        if ($('#subsec input').val()!='') loadSubsector( $('#subsec input').val() );
        if (last_sic!=null &&  $('#sic_code input').val()!='') loadSIC( last_sic );
    });
    
    $('#minsize').click(function(){
        if ($('#subsec input').val()!='') loadSubsector( $('#subsec input').val() );
        if (last_sic!=null &&  $('#sic_code input').val()!='') loadSIC( last_sic );
    });
    
    $('#sic_id button').click(function(){
        var val = $('#sic').val();
        ajx('/pages/sales/Model/sic/row',{id:val},function(d){
            if (d.row!==false)
            {  $('#sic_description').html(d.row.name+'<br>'+d.row.description);
               var year = $('#year').val();
               var region = $('#region').val();
               var params = {sic:val,year:year,region:region};
               var minsize = $('#minsize').val();
               if (minsize!='') params.min_size = minsize;
               ajx('/pages/sales/MarketRanking',params,function(d){                   
                   var years = {};
                   var ytotal = {};
                   var t;
                   for (var i=0; i<d.rows.length; i++)
                   { var r = d.rows[i];
                     r.tsales=1.0*r.tsales;
                     t = r.tsales;
                     if (ytotal[r.syear]==undefined) ytotal[r.syear]=0.0;
                     if (t>0.0) ytotal[r.syear]+=t;
                     
                     if (r.syear!=year)
                     { if (years[r.syear]==undefined) years[r.syear]={};
                       years[r.syear][r.cid] = r.tsales;
                       delete d.rows[i];
                     }                     
                   }
                   
                   var y1 = year-1;
                   var y2 = year-2;
                   
                   for (var i=0; i<d.rows.length; i++)
                   {  var r = d.rows[i];
                      if (r!=undefined)
                      {   if (years[y1]!=undefined && years[y1][r.cid]!=undefined) 
                            d.rows[i].y1 = years[y1][r.cid];
                          else d.rows[i].y1 = null;
                          
                          if (years[y2]!=undefined && years[y2][r.cid]!=undefined) 
                            d.rows[i].y2 = years[y2][r.cid];
                          else d.rows[i].y2 = null;
                          
                          if (r.tsales>=0.0) r.psale = ((r.tsales / ytotal[year])*100.0).toFixed(2);
                          else r.psale=null;
                          
                          if (r.y1>=0.0) r.psaleY1 = ((r.y1 / ytotal[y1])*100.0).toFixed(2);
                          else r.psaleY1=null;
                          
                          if (r.y2>=0.0) r.psaleY2 = ((r.y2 / ytotal[y2])*100.0).toFixed(2);
                          else r.psaleY2=null;
                          
                      }
                   }
                   
                   drawRanking('#ranking',d, ytotal);
                                      
               });
               
            } else 
            { $('#sic_description').html('<div class="alert alert-warning">SIC code not found!</div>');
              $('#ranking').html('');
            }
        });
    });
    
    $('.b-debug').click(function(){
            $('#debug').css('display','');
    });
    
    /* ------------------ Chart ---------------------------*/
    
     function drawChart(d)
    {   Highcharts.chart('chart', {
            chart: {
                zoomType: 'xy'
            },
            title: {
                text: d.title
            },
            xAxis: [{
                categories: d.categories,
                crosshair: true
           }],
            yAxis: [{ // Primary yAxis
                title: {
                    text: d.nameL,
                },
                opposite: false

            }, 
            { // Secondary yAxis
                gridLineWidth: 0,
                title: {
                    text: d.nameR
                },
                labels: {
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                },
                opposite: true
            }],
            tooltip: {
                shared: true
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                x: 80,
                verticalAlign: 'top',
                y: 55,
                floating: true,
                backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
            },
            series: [{
                name: d.nameL,
                type: 'spline',
                yAxis: 0,
                data: d.data[0],
                marker: {
                    enabled: true
                }
            }, {
                name: d.nameR,
                type: 'spline',
                yAxis: 1,
                data: d.data[1],
                marker: {
                    enabled: true
                }
            }]
        });
    }
   
    var sic_totals = null;
    
    function drawSicTotals(l,r)
    {   
		function total_sales()
		{  var d = [];
		   for (var i=0; i<sic_totals.length; i++)
		   { d.push(1.0*sic_totals[i].tsales);
		   }
		   return d;
		}
		
		function top3()
		{  var d = [];
		   return d;
		}
		
				
		function top5()
		{  var d = [];
		   return d;
		}
				
		function stability()
		{  var d = [];
		   return d;
		}
		
		function blank()
		{  var d = [];
		   return d;
		}
		
		function sales_growth()
		{  var d = [null];
		   for (var i=1; i<sic_totals.length; i++)
		   { d.push(100*( ( (1.0*sic_totals[i].tsales) / (1.0*sic_totals[i-1].tsales) )-1 ));
		   }
		   return d;
		}
		
		function sales_growth_3yr()
		{  var d = [null,null,null];
		   for (var i=3; i<sic_totals.length; i++)
		   { d.push(100*( Math.pow( (1.0*sic_totals[i].tsales) / (1.0*sic_totals[i-3].tsales), 1/3 )-1 ));
		   }
		   return d;
		}
		
		function ebit_growth()
		{  var d = [null];
		   for (var i=1; i<sic_totals.length; i++)
		   { d.push(100*( ( (1.0*sic_totals[i].tebit) / (1.0*sic_totals[i-1].tebit) )-1 ));
		   }
		   return d;
		}
		
		function ebit_growth_3yr()
		{  var d = [null,null,null];
		   for (var i=3; i<sic_totals.length; i++)
		   { d.push(100*( Math.pow( (1.0*sic_totals[i].tebit) / (1.0*sic_totals[i-3].tebit), 1/3 )-1 ));
		   }
		   return d;
		}
		
		function ebit_margin()
		{  var d = [];
		   for (var i=0; i<sic_totals.length; i++)
		   { d.push(100*( ( (1.0*sic_totals[i].tebit) / (1.0*sic_totals[i].tsales) ) ));
		   }
		   return d;
		}
		
		function roa()
		{  var d = [];
		   for (var i=0; i<sic_totals.length; i++)
		   { d.push(100*( ( (1.0*sic_totals[i].tebit) / (1.0*sic_totals[i].tassets) ) ));
		   }
		   return d;
		}
		
		function asset_growth()
		{  var d = [null];
		   for (var i=1; i<sic_totals.length; i++)
		   { d.push(100*( ( (1.0*sic_totals[i].tassets) / (1.0*sic_totals[i-1].tassets) )-1 ));
		   }
		   return d;
		}
			
		function asset_growth_3yr()
		{  var d = [null,null,null];
		   for (var i=3; i<sic_totals.length; i++)
		   { d.push(100*( Math.pow( (1.0*sic_totals[i].tassets) / (1.0*sic_totals[i-3].tassets), 1/3 )-1 ));
		   }
		   return d;
		}
		
		var calc = [total_sales,top3,top5,stability,sales_growth,blank,blank,blank,sales_growth_3yr,
		ebit_growth, ebit_growth_3yr, ebit_margin, roa, asset_growth, asset_growth_3yr];
		
		
		var d = {l:null, r:null};
		if (calc[l]!=undefined) d.l = calc[l]();
		if (calc[r]!=undefined) d.r = calc[r]();
		
		var categories = [];
		
		for (var i=0; i<sic_totals.length; i++)
	    {   categories.push(sic_totals[i].syear);
		}
	
		var tl = $('#LHS option')[l].innerHTML;
        var rl = $('#RHS option')[r].innerHTML;
        drawChart({title:tl+' vs '+rl, nameL:tl, nameR:rl, categories:categories, data:[d.l, d.r] });
		console.log(r);	
	}
    
    $('.b-vchart').click(function(){
		
		var l = (1*$('#LHS').val())-1;
        var r = (1*$('#RHS').val())-1;
        
		if ($('#sic_code input').val()!='')
		{	
			//if (sic_totals==null)
			//{ 
				ajx('/pages/sales/MarketSummarySicTotals',{sic:last_sic, region:$('#region').val(),
					min_size:$('#minsize').val()
				},function(d){
					   sic_totals = d.rows;
					   drawSicTotals(l,r)
				});
			//} else drawSicTotals(l,r);
	    }
       
       
    });
    
});
