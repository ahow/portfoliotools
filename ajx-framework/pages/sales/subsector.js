var pager;

function modelSICView(selector,d,onclick,ondblclick)
{  var s = '';
   var i;
   
   if (d.titles!=undefined)
   {   var h = '<tr>';
       for (i in d.titles)
       { h+='<th>'+d.titles[i]+'</th>';               
       }
       h+='</tr>';
       $(selector).find('thead').html(h);
   }
   for (i in d.rows)
   {   var j;
       var r = d.rows[i];
       if (r.id!=undefined) s+='<tr data-id="'+i+'">'; else s+='<tr>';
       for (j in d.columns) s+='<td>'+r[ d.columns[j] ]+'</td>';
       s+='</tr>';
   }
   $(selector).find('tbody').html(s);
   if (onclick!=null)  $(selector+' tbody tr').click(function(row){
       $(row.target).parents('table:first').find('tr').removeClass('active');
       var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
       onclick(row, d.rows[id]);
   });
   
   if (ondblclick!=undefined && ondblclick!=null)  $(selector+' tbody tr').dblclick(function(row){
       $(row.target).parents('table:first').find('tr').removeClass('active');
       var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
       ondblclick(row, d.rows[id]);
   });
}

$(function(){

  function print()
  {   fprint.title.value = 'Market ranking';
      last_data.descr = $('#sic_description').html();
      last_data.region = $('#region').val();
      fprint.data.value = JSON.stringify(last_data);
      console.log(last_data);
      // $('#container svg').get(0).outerHTML;
      fprint.submit();
  }


  if ($('.model-list').length>0)
  { 
       var model = new modelListController('.model-list', modelSICView);
       model.load();
       model.click(function(e, row){
           // console.log(row);
           $('#subsector').val(row.id);
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
   
    function toFloat(v, decimals)
    { var n = 1.0*v;
      if (isNaN(n) || v==null)
      {   return '-';
      }
      return n.toFixed(decimals);
    }
    
    var last_data = null;
    
    function drawRanking(selector, d, totals)
    {  last_data = d;
       var s = '<table class="table table-striped">';
       var link = "<?php echo mkURL('/sales/companies'); ?>";
        total = 0.0;
        s+='<tr><th style="width:50%">Company</th><th style="width:8%">% of sales</th><th style="width:26%">&nbsp;</th><th style="width:8%">-1Yr</th><th style="width:8%">-2Yr</th></tr>';
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            if (r!=undefined)
            {  var pbar = '<div class="progress"><div class="progress-bar progress-bar-success" aria-valuemin="0" aria-valuemax="100" style="width:'+r.psale+'%"></div></div>';                
                s+='<tr><td><a href="'+link+'/'+r.cid+'">'+r.name+'</a></td><td>'+toFloat(r.psale,2)+'</td><td>'+pbar+'</td><td>'+toFloat(r.psaleY1,2)+'</td><td>'+toFloat(r.psaleY2,2)+'</td></tr>';
               total+=1.0*r.psale;
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
    
    ajx('/pages/sales/GetMaxYear', function(d){ 
        $('#year').val(d.maxyear);
    } );
    
    function marketRanking(val)
    {   console.log(val);
        $('#subsector_description').html('<b>'+val+'</b><br>');
        var year = $('#year').val();
        var region = $('#region').val();
        var params = {subsector:val,year:year,region:region};
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
    }
    
    $('#sic_id button').click(function(){
        var val = $('#sic').val();
         marketRanking(val);
    });
    
    
    $('.b-csv').click(function(){
        
        function N(v){ if (isNaN(v) || v==undefined) return ''; return v; }
        
        var d = last_data; 
        var csv = '"CID","NAME","% OF SALES","-1Yr","-2Yr"'+"\n";
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            if (r!=undefined) csv+='"'+r.cid+'","'+r.name.replace("\n",'\\n').replace('"','\"')+'",'+r.psale+','+N(r.psaleY1)+','+N(r.psaleY2)+"\n";
        }
        download(csv,'market_ranking.csv');
     });
     
     $('.b-print').click(print);
    
});
