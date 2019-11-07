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
        console.log(p);
        ajx('/pages/sales/SectorAnalysis',p, function(d){            
            arrayList.setHeader(d.header);
            arrayList.setData(d.rows);            
            //   drawSummary(d);         
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
    
    
    $('#year').val(new Date().getFullYear()-1);
    
    $('#region').click(function(){
        reloadData();        
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
    
   
    var sic_totals = null;

   function drawSicTotals2(dd,lp,rp)
    {   var d = {l:[],r:[]};
        var categories = [];
        var yr_idx = {};
        var yr;
        var min = Number.MAX_VALUE;
        var max = Number.MIN_VALUE;        
        
        for (var i=0; i<dd.lrows.length;i++) 
        {  var r = dd.lrows[i];
           var y = 1*r.syear;
           if (r[lp]!=undefined) dd.lrows[i].v = r[lp];
           if (y > 0)  
           {  if (min>y) min=y;
              if (max<y) max=y;
           }
        }
        for (var i=0; i<dd.rrows.length;i++) 
        {  var r = dd.rrows[i];
           var y = 1*r.syear;
           if (r[rp]!=undefined) dd.rrows[i].v = r[rp];
           if (y > 0)  
           {  if (min>y) min=y;
              if (max<y) max=y;
           }
        }
        var n=0;
        for (var i=min; i<=max;i++)
        { categories.push(i);
          yr_idx[i]=n;
          d.l[n]=null;
          d.r[n]=null;
          n++;
        }
        
        function to_n(v)
        { if (v==null) return v;
          return 1.0*v;
        }

        for (var i=0; i<dd.lrows.length;i++) 
        {  var r = dd.lrows[i];
           if (1*r.syear > 0) 
           {   d.l[ yr_idx[r.syear] ] = to_n(r.v);
           }
        }
        
        for (var i=0; i<dd.rrows.length;i++) 
        {  var r = dd.rrows[i];
           if (1*r.syear > 0) 
           {   d.r[ yr_idx[r.syear] ] = to_n(r.v);
           }
        }

        var lv = $('#LHS').val();
        var rv = $('#RHS').val();
        
		var tl = $('#LHS [value="'+lv+'"]').html();
        var tr = $('#LHS [value="'+rv+'"]').html();
        
        drawChart({title:tl+' vs '+tr, nameL:tl, nameR:tr, categories:categories, data:[d.l, d.r] });		
    }    
    
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
		
//		var l = (1*$('#LHS').val())-1;
        //var r = (1*$('#RHS').val())-1;
		var lhs = $('#LHS').val();
        var rhs = $('#RHS').val();
        
		if ($('#sic_code input').val()!='')
		{	
			//if (sic_totals==null)
			//{ 
				ajx('/pages/sales/MarketSummarySicTotals',{sic:last_sic, 
                    region:$('#region').val(), lhs:lhs, rhs:rhs, 
					min_size:$('#minsize').val()
				},function(d){
					   sic_totals = d.rows;
					   drawSicTotals2(d, lhs,rhs)
				});
			//} else drawSicTotals(l,r);
	    }
       
       
    });
    


    // var model = new modelListController('.model-list');
    // model.morder = 0;
    
    // model.loaded(function(d){
        
    //    //  model.setParam('order', null);
            
              
    // });

    // model.load();

    // // enable pager
    // pager = new modelPagination('.model-list .model-pager');
    
    // model.total(function(total, rows_lim){
    //     pager.setTotal(total, rows_lim);
    //         $('#tbedit').addClass('disabled');
    // })
    
    // pager.change(function(n){
    //     model.load(n);
    // });
 
 
});
