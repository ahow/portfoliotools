$(function(){
    
    function toFloat(v, decimals)
    { var n = 1.0*v;            
      if (isNaN(n) || v==null)
      {   return '-';
      }
      return n.toFixed(decimals);
    }
   
   

    function drawThemes(d)
    {  $('div.p-chart').css('display','');
       var selector='#summary';
       var s = '<table class="table table-striped">';
        total = 0.0;
        s+='<tr><th>Theme</th><th>Total<br>sales</th><th>% top 3</th><th>% top 5</th><th>Stability</th><th>Sales<br>growth</th><th>ROIC</th><th>PE</th><th>EVBIDTA</th><th>Payout</th><th>% reviewed</th></tr>';
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            if (r!=undefined)
            {  // s+='<tr><td>'+$('#themes option[value="'+r.theme_id+'"]').html()+'</td><td>'+toFloat(r.tsales,1)+'</td><td>'
                 s+='<tr><td>'+r.name+'</td><td>'+toFloat(r.tsales,1)+'</td><td>'
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
    var dsic, dsubsec, last_sic = null;
    var views = new htviewCached();
    
    function drawDebug(d)
    {   if (d.dbg!=undefined)
        { $('#debug').html('<pre>'+d.dbg+'</pre>')
        }
    }
    
    function loadThemesSummary()
    {   $("#mranking").LoadingOverlay("show");
        var range = $('#theme_range').val().split(',');
        ajx('/pages/sales/ThemesSummary',{region:$('#region').val(),
            theme_min:range[0], theme_max:range[1], theme_id:$('#themes').val()
        },function(d){
               drawThemes(d);
               drawDebug(d);
               $("#mranking").LoadingOverlay("hide", true);
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
        // if ($('#subsec input').val()!='') loadSubsector( $('#subsec input').val() );
        // if (last_sic!=null &&  $('#sic_code input').val()!='') loadSIC( last_sic );
    });

    $('.b-summary').click(function(){
        loadThemesSummary();
    });


    
    // Range slider setup
 
    function onChanheVal(onChange)
    {   var val = '';
        var fu = onChange;
        
        function check(e)
        { var v = $(e.target).val();
          if (v!=val)
          {  if (fu!=undefined) fu(e.target);
             val = v;
          }
        }
        return {check:check};
    }
           
    $("input.bs-range").slider({});
    
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
        
    $('.b-vchart').click(function(){
		
		// var l = (1*$('#LHS').val())-1;
        // var r = (1*$('#RHS').val())-1;
        
		if ($('#sic_code input').val()!='')
		{	
			//if (sic_totals==null)
			//{ 
            var lp= $('#LHS').val();
            var rp= $('#RHS').val();
            var range = $('#theme_range').val().split(',');
			ajx('/pages/sales/ThemesSummarySicTotals',{region:$('#region').val(),
            theme_min:range[0], theme_max:range[1], theme_id:$('#themes').val(),
            lhs:lp, rhs:rp},
                function(d){
					   sic_totals = d.rows;
					   drawSicTotals2(d,lp,rp)
				});
			//} else drawSicTotals(l,r);
	    }
       
       
    });
    
});
