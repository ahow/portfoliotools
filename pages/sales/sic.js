var pager;

$(function(){

  if ($('.model-list').length>0)
  { 
       var model = new modelListController('.model-list');
       model.load();
       model.click(function(e, row){
           // console.log(row);
           $('#sic').val(row.id);
           marketRanking(row.id);
           $('#tbedit').removeClass('disabled');
       });
       
       
       // Search
       $('.model-list .model-search button').click(function(){
           var s = $('.model-list .model-search input').val().trim();
           if (s!='') model.load({search:'%'+s+'%'});
           else model.load();
       });

       $('.model-list .model-search input').keyup(function(d){ 
           if (d.keyCode==13)  $('.model-list .model-search button').trigger('click');
       });
       
       // enable pager
       pager = new modelPagination('.model-list .model-pager');
       
       model.total(function(total, rows_lim){
           pager.setTotal(total, rows_lim);
       })
       pager.change(function(n){
           model.load(n);
       });
   } 
   
    /*
    function lookupRegion(text,foo)
    {   console.log(text);
        ajx('/pages/sales/Model/regions/load',{search:text+'%'},function(d){
            console.log(d);
        });
    }*/
    
    // $('#region').typeahead({source:lookupRegion});
    
    function drawRanking(selector, d, totals)
    {  var s = '<table class="table table-striped">';
        total = 0.0;
        s+='<tr><th style="width:50%">Company</th><th style="width:8%">% of sales</th><th style="width:26%">&nbsp;</th><th style="width:8%">-1Yr</th><th style="width:8%">-2Yr</th></tr>';
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            if (r!=undefined)
            {  var pbar = '<div class="progress"><div class="progress-bar progress-bar-success" aria-valuemin="0" aria-valuemax="100" style="width:'+r.psale+'%"></div></div>';                
                s+='<tr><td>'+r.name+'</td><td>'+r.psale+'</td><td>'+pbar+'</td><td>'+r.psaleY1+'</td><td>'+r.psaleY2+'</td></tr>';
               total+=1.0*r.psale;
            }
        }
        s+='</table>';
        $(selector).html(s);
        // console.log(total, totals);
        // console.log(d);
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
    
    $('#year').val(new Date().getFullYear()-1);
    
    function marketRanking(val)
    { ajx('/pages/sales/Model/sic/row',{id:val},function(d){
            if (d.row!==false)
            {  $('#sic_description').html('<b>'+d.row.name+'</b><br>'+d.row.description);
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
    }
    
    $('#sic_id button').click(function(){
        var val = $('#sic').val();
            marketRanking(val);
    });
    
});
