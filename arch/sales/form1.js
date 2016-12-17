
function searchDialog(selector, datamodel, title)
{  var selected = null;
   var onselect = null;
   $(selector+' div.model-list').attr('data-model', datamodel);
   $(selector+' .modal-title').html(title);
    
   var model = new modelListController(selector+' .model-list');
   model.load();
   model.click(function(e,row){
        selected = row;
        $(selector+' button.b-select').removeClass('disabled');        
   });
   
   
   // Search
   $(selector+' .model-list .model-search button').click(function(){
       var s = $('.model-list .model-search input').val().trim();
       if (s!='') model.load({search:'%'+s+'%'});
       else model.load();
   });

   $(selector+' .model-list .model-search input').keyup(function(d){ 
       if (d.keyCode==13)  $(selector+' .model-list .model-search button').trigger('click');
   });
   
   $(selector+' button.b-select').click(function(){
       if (onselect!=null) onselect(selected);
       $(selector+' .modal').modal('hide');
   });
   
   // enable pager
   pager = new modelPagination(selector+' .model-list .model-pager');
   
   model.total(function(total, rows_lim){
       pager.setTotal(total, rows_lim);
        $(selector+' button.b-select').addClass('disabled');  
   })
   pager.change(function(n){
       model.load(n);
   });
   
   function select(fu){ onselect = fu; }
   
   function open()
   {  $(selector+' div.modal').modal();
       setTimeout(function(){            
             $(selector+' .model-search input').focus();
           }, 300);
   }
   
   return {select:select, open:open}
}

function drawForm1(d)
{   console.log(d); 
    var i;
    var divs = [];
    var ymin = 65535, ymax=-65535;
    
    if (d.rows.length==0)
    {  $('#formdata').html('');
       return;
    }
    
    for (i=0; i<d.rows.length; i++)
    { var r=d.rows[i];
      var dv = 1*r.number;
      if (divs[dv]==undefined) divs[dv]={years:{}};
      var yr = 1*r.syear;
      if (yr<ymin) ymin=yr;
      if (yr>ymax) ymax=yr;
      if (divs[dv].years[yr]==undefined) divs[dv].years[yr]={};
      divs[dv].years[yr].sales = r.sales;
      divs[dv].years[yr].me = r.me;
      divs[dv].years[yr].sic = r.sic;
      divs[dv].years[yr].sicname = r.sicname;
      divs[dv].years[yr].sic_division = r.sic_division;
      divs[dv].years[yr].major_group = r.major_group;
      divs[dv].years[yr].industry_group = r.industry_group;
      divs[dv].years[yr].divdetail_id = r.divdetail_id;
    }
    console.log(divs);
    console.log(ymin, ymax);
    var s = '<table class="table table-striped">';
    s+='<tr>';
    s+='<th>#</th><th>Division</th><th>SIC</th><th>SIC division</th><th>Major group</th><th>Industry group</th><th>Industry</th>';
    s+='<th>Sales '+ymax+'</th>';
    for (var y=ymax; y>=ymin; y--) s+='<th class="a-right">%<br>'+y+'</th>';
    s+='</tr>'; 

    for (i=1; i<divs.length; i++)
    {   var sic = divs[i].years[ymax].sic;
        s+='<tr>';
        s+='<td>'+i+'</td>';
        s+='<td>'+divs[i].years[ymax].me+'</td>';
        s+='<td><input class="form-control" style="width:80px" name="sic" data-id="'+divs[i].years[ymax].divdetail_id+'" type="number" value="'+sic+'" /></td>';
        s+='<td>'+divs[i].years[ymax].sic_division+'</td>';
        s+='<td>'+divs[i].years[ymax].major_group+'</td>';
        s+='<td>'+divs[i].years[ymax].industry_group+'</td>';
        s+='<td>'+divs[i].years[ymax].sicname+'</td>';
        var base = divs[i].years[ymax].sales; 
        s+='<td>'+base+'</td>';
        for (var y=ymax; y>=ymin; y--)
        {   if (divs[i].years[y]!=undefined && base!=0.0)
                // s+='<td class="a-right">'+((divs[i].years[y].sales/base)*100)+' '+divs[i].years[y].sales+'</td>';
                s+='<td class="a-right">'+((divs[i].years[y].sales/base)*100).toFixed(2)+'</td>';
            else
                s+='<td class="a-right">-</td>';
        }
        s+='</tr>'; 
    }
    s+='</table>';
    $('#formdata').html(s);
}

$(function()
{
    var views = new htviewCached();
    var dialog;
    var form1 = new modelFormController('#form1');
    
    $('#form1 #year').val(new Date().getFullYear()-1);
    
    var year_timer = null;
    
    $('#form1 #year').change( function(){
        var id = $('#form1 #id').attr('data-value');
        
        if (id!='')
        {
            year_timer = setTimeout(function(){
                if (year_timer!=null) clearTimeout(year_timer);
                ajx('/pages/sales/Form1',{ cid:id, year:$('#form1 #year').val() }, drawForm1 );
            }, 1000)
        }
        
    });
    
    
    views.view('/pages/sales/search','#search_company', function(){        
        dialog = new searchDialog('#search_company', "/pages/sales/Model/companies-search",'Search Company');
        dialog.select(function(id){
            form1.setData(id);
            //console.log(id);
            ajx('/pages/sales/Form1',{ cid:id.id, year:$('#form1 #year').val() }, drawForm1 );
        });
        
        $('#id button').click(function(){
            dialog.open();            
        });
    });
    
    

});
