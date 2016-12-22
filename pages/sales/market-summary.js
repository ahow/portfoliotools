$(function(){
    
    function drawSummary(d)
    {  var selector='#summary';
       var s = '<table class="table table-striped">';
        total = 0.0;
        s+='<tr><th>Subsector</th><th>Total<br>sales</th><th>% top 3</th><th>% top 5</th><th>Stability</th><th>Sales<br>growth</th><th>ROIC</th><th>PE</th><th>EVBIDTA</th><th>Payout</th><th>% reviewed</th></tr>';
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            if (r!=undefined)
            {   s+='<tr><td>'+r.subsector+'</td><td>'+r.tsales+'</td><td>'
                +((100.0*r.top3sum)/(1.0*r.tsales)).toFixed(0)+'</td><td>'
                +((100.0*r.top5sum)/(1.0*r.tsales)).toFixed(0)+'</td><td>'
                +(1.0*r.stability).toFixed(2)+'</td><td>'+(1.0*r.asales_growth).toFixed(2)+'</td><td>'
                +(1.0*r.aroic).toFixed(2)+'</td><td>'+(1.0*r.ape).toFixed(2)+'</td><td>'
                +(1.0*r.aevebitda).toFixed(2)+'</td><td>'+(1.0*r.apayout).toFixed(2)+'</td><td>'
                +(1.0*r.previewed).toFixed(0)+'</td></tr>';
            }
        }
        s+='</table>';
        $(selector).html(s);
    }

    function drawSIC(d)
    {  var selector='#summary';
       var s = '<table class="table table-striped">';
        total = 0.0;
        s+='<tr><th>SIC</th><th>Name</th><th>Total<br>sales</th><th>% top 3</th><th>% top 5</th><th>Stability</th><th>Sales<br>growth</th><th>ROIC</th><th>PE</th><th>EVBIDTA</th><th>Payout</th><th>% reviewed</th></tr>';
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            if (r!=undefined)
            {   s+='<tr><td>'+r.sic+'</td><td>'+r.name+'</td><td>'+(1.0*r.tsales).toFixed(1)+'</td><td>'
                +((100.0*r.top3sum)/(1.0*r.tsales)).toFixed(0)+'</td><td>'
                +((100.0*r.top5sum)/(1.0*r.tsales)).toFixed(0)+'</td><td>'
                +(1.0*r.stability).toFixed(1)+'</td><td>'+(1.0*r.asales_growth).toFixed(1)+'</td><td>'
                +(1.0*r.aroic).toFixed(1)+'</td><td>'+(1.0*r.ape).toFixed(1)+'</td><td>'
                +(1.0*r.aevebitda).toFixed(1)+'</td><td>'+(1.0*r.apayout).toFixed(1)+'</td><td>'
                +(1.0*r.previewed).toFixed(0)+'</td></tr>';
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
    
    function loadSubsector(sub)
    {  ajx('/pages/sales/MarketSummarySubsector',{subsector:sub, region:$('#region').val(),
            min_size:$('#minsize').val()
        },function(d){
               drawSummary(d);
       });
    }

    function loadSIC(sic)
    {  ajx('/pages/sales/MarketSummarySic',{sic:sic, region:$('#region').val(),
            min_size:$('#minsize').val()
        },function(d){
               drawSIC(d);
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
    
 
    
});
