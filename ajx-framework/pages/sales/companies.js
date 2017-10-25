
var dialog;

function companieEditForm(selector)
{   
    var onclickse = null;
    var data =  null;
    var cid = null; // companie ID    
    
    function toFloat(v, decimals)
    { var n = 1.0*v;            
      if (isNaN(n) || v==null)
      {   return '-';
      }
      return n.toFixed(decimals);
    }
    
    function draw(d)
    {
        var i;
        var divs = [];
        var keys = [];
        var ymin = 65535, ymax=-65535;
        
        if (d.rows.length==0)
        {  $(selector+' .formdata').html('');
           return;
        }
        
        var ytotal = {};
        
        for (i=0; i<d.rows.length; i++)
        { var r=d.rows[i];
          var dv = 1*r.number;
          if (divs[dv]==undefined) divs[dv]={years:{}};
          var yr = 1*r.syear;
          if (yr<ymin) ymin=yr;
          if (yr>ymax) ymax=yr;
          if (divs[dv].years[yr]==undefined) divs[dv].years[yr]={};
          if (ytotal[r.syear]==undefined) ytotal[r.syear]=0.0;
          ytotal[yr]+=1.0*r.sales;
          divs[dv].years[yr].sales = r.sales;
          divs[dv].years[yr].me = r.me;
          divs[dv].years[yr].sic = r.sic;
          divs[dv].years[yr].cid = r.cid;
          divs[dv].years[yr].sicname = r.sicname;
          divs[dv].years[yr].sic_division = r.sic_division;
          divs[dv].years[yr].major_group = r.major_group;
          divs[dv].years[yr].industry_group = r.industry_group;
          divs[dv].years[yr].divdetail_id = r.divdetail_id;
        }
        
        // console.log(ytotal);

        var s = '<table class="table table-striped selectable">';
        s+='<tr>';
        s+='<th>#</th><th>Division</th><th>SIC</th><th>SIC division</th><th>Major group</th><th>Industry group</th><th>Industry</th>';
        s+='<th>Sales '+ymax+'</th>';
        for (var y=ymax; y>=ymin; y--) s+='<th class="a-right">%<br>'+y+'</th>';
        s+='</tr>'; 
        
        for (i=1; i<divs.length; i++)
        {   if (divs[i].years[ymax]!=undefined)
            {
                var sic = divs[i].years[ymax].sic;
                
                keys[i] = {};
                keys[i].year = ymax;
                keys[i].cid = divs[i].years[ymax].cid;
                keys[i].division = i;
                
                s+='<tr data-id="'+i+'">';
                s+='<td>'+i+'</td>';
                s+='<td contenteditable="true" name="me" data-old-value="'+divs[i].years[ymax].me+'">'+divs[i].years[ymax].me+'</td>';
           //     s+='<td><input class="form-control" style="width:80px" name="sic" max="9999" type="number" value="'+sic+'" /></td>';
                s+='<td><span>'+sic+'</span><button type="button" class="btn btn-default btn-xs">...</button></td>';
                s+='<td>'+divs[i].years[ymax].sic_division+'</td>';
                s+='<td>'+divs[i].years[ymax].major_group+'</td>';
                s+='<td>'+divs[i].years[ymax].industry_group+'</td>';
                s+='<td>'+divs[i].years[ymax].sicname+'</td>';
             //   s+='<td class="w-sicname" name="readonly" contenteditable="true">'+divs[i].years[ymax].sicname+'</td>';
              //  s+='<td name="readonly"><input class="w-sicname form-control" value="'+divs[i].years[ymax].sicname+'"></td>';
                var base = divs[i].years[ymax].sales; 
                var base_me = divs[i].years[ymax].me;
                s+='<td contenteditable="true" name="sales" data-old-value="'+base+'">'+base+'</td>';
                for (var y=ymax; y>=ymin; y--)
                {  // if (divs[i].years[y]!=undefined && base!=0.0 && base_me==divs[i].years[y].me)
                    if (divs[i].years[y]!=undefined && base!=0.0)
                        //  s+='<td class="a-right">'+((divs[i].years[y].sales/base)*100).toFixed(2)+'</td>';
                        s+='<td class="a-right">'+toFloat((divs[i].years[y].sales/ytotal[y])*100,0)+'</td>';
                    else
                        s+='<td class="a-right">-</td>';
                }
                s+='</tr>'; 
            }
        }
        data=keys;
        s+='</table>';
        $(selector+' .formdata').html(s);
        
        /*
        $(selector+' .w-sicname').each(function(i,e){ 
            var lk = new lookupInput($(e),'/pages/sales/Model/sic-lookup/load',{items:'all'}); 
            lk.select(function(d){
                  var tds = $(e).parents('tr:first').find('td');
                  $(tds[2]).html(d.id);
                  $(tds[2]).trigger('blur');
            });
        });*/

        $(selector+' td button').click(function(e){                        
            dialog.open(e.target);
        });
        
        $(selector+' .formdata tr td').blur(function(e){
            var td = $(e.target);
            var value = td.html();
            if (td.attr('data-old-value')!=value)
            {  // value  changed 
               var name = td.attr('name');
               if (name=='readonly') return;
               var prow = $(e.target).parents('tr:first');
               var id = prow.attr('data-id'); 
               var r = getrow(id);
               
               r[name] = value;
               r.syear = r.year;
               delete r.year;
               ajx('/pages/sales/Model/editdiv/update', r, function(d){                   
                   td.attr('data-old-value', value);
                   if (!d.error) setOk(d.info); 
                   // reload sic data
                   /*
                   if (name=='sic')
                   { 
                       ajx('/pages/sales/Model/sicgroup/row',{id:value}, function(d){
                           var tds =  prow.find('td');
                           $(tds[3]).html(d.row.sic_division);
                           $(tds[4]).html(d.row.major_group);
                           $(tds[5]).html(d.row.industry_group);
                           $(tds[6]).html(d.row.industry);
                       });
                   }
                   */
               });
            }
            
        });
        
        
        $(selector+' .formdata tr').click(function(row){
           $(row.target).parents('table:first').find('tr').removeClass('active');
           var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
           $('button.b-edit-div').removeClass('disabled');
           if(onclick!=null) onclick(data[id]);
        });
        $('button.b-edit-div').addClass('disabled');        
    }
    
    function click(fu) { onclick=fu }
 
    function getrow(id){ return data[id]; }
    
    // row is optional
    function load(row)
    {   if (row!=undefined) cid = row.id;
        ajx('/pages/sales/Form1',{ cid:cid, year:$('#form1 #year').val() }, draw );
    }
    
    var cyear = new Date().getFullYear()-1;
    // remove control from updates
    $(selector+' #year').val(cyear).attr('data-control-type',null);
        
    $(selector+' #year').change( function(){        
        if (cid!=null)
        {  var year_timer = setTimeout(function(){
                if (year_timer!=null) clearTimeout(year_timer);
                load();
            }, 750);
        }
    });
    
    function getCID(){ return cid }

    return {click:click, draw:draw, getrow:getrow, load:load, getCID:getCID}
}

function modelCompaniesView(selector,d,onclick,ondblclick)
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
       var cl = '';
       if (r.reviewed==1) cl=' class="w-reviewed"';
       if (r.id!=undefined) s+='<tr data-id="'+i+'"'+cl+'>'; else s+='<tr>';
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

var compData, filterData;

$(function(){

   // --------------- Search tab  --------------------
   var pager =  null;
   var is_comp_edited = false;

   var model = new modelListController('#tabsearch .model-list', modelCompaniesView);
   model.load();
   
   var selected_row = null;

   var editF = new companieEditForm('#form1');
   compData = new modelFormController('#company-data');
   filterData = new modelFormController('#tabsearch');
   
   var views = new htviewCached();

   model.click(function(e, row){        
        selected_row = row;
        editF.load(row);
        
        compData.loadrow({cid:row.id});
        // compData.setData(row);
        $('#tbedit').removeClass('disabled');
        // console.log(row);
   });
   
   // Search
   $('#tabsearch .model-list .model-search button.b-search').click(function(){
       var s = $('.model-list .model-search input').val().trim();
       var p = filterData.getData(true);
       if (p.sic!=undefined) delete p.sic; //remove unused data
       if (s!='' || p.filter!='')
       {   if (s!='') p.search = '%'+s+'%';
           model.load(p);
       } else model.load();
   });

   $('#tabsearch  .model-list .model-search button.b-clean').click(function(){
        $('#tabsearch .model-list .model-search input').val('');
        $('#tabsearch .model-list #fregion').val('');
        $('#tabsearch .model-list #industry_group').val('');
        $('#tabsearch .model-list #major_group').val('');
        $('#tabsearch .model-list #subsector').val('');
        $('#tabsearch .model-list #division').val('');
        $('#tabsearch .model-list #sic').val('');
        $('#tabsearch .model-list #sic_code').attr('data-value','');
        model.load();
   });
   
   $('#tabsearch  .model-list .model-search input').keyup(function(d){ 
       if (d.keyCode==13)  $('.model-list .model-search button.b-search').trigger('click');
   });
   
   // enable pager
   pager = new modelPagination('#tabsearch .model-list .model-pager');
   
   model.total(function(total, rows_lim){
       pager.setTotal(total, rows_lim);
        $('#tbedit').addClass('disabled');
   })
   pager.change(function(n){
       model.load(n);
   });
   
   var href = window.location.href.split('/sales/companies/');
   // Get companie ID from URL
   if (href[1]!=undefined)
   {   editF.load({id:href[1]});
       compData.loadrow({cid:href[1]});
       $('#tbedit a').tab('show');
   }

   // --------------- Edit tab  --------------------
   // when tab selected  
   $('.nav-tabs a').on('shown.bs.tab', function(event){
        if ($(event.target).attr('href')=='#tabedit')
        {   
            if (selected_row!=null)
            {   
            }
        }        
   });
   
   // ---------------- Direct Update -----------------
   $('#company-data [data-control-type]').blur(function(e){
        var r = compData.getData();
        if (!$.isEmptyObject(r))
        {   ajx('/pages/sales/Model/companies/update', r, function(d){                   
                   if (!d.error) setOk(d.info); 
                   is_comp_edited = true;
            });
        }
   });
   
    // ------- First tab opened -----------------------
   $("a[href='#tabsearch']").on('show.bs.tab', function(e) {
      if (is_comp_edited)
      {   is_comp_edited = false;
          model.refresh();
      }      
   });

   var dsic;
   
   views.view('/pages/sales/search','#search_sic2', function(){        
        dsic = new searchDialog('#search_sic2', "/pages/sales/Model/sic-search",'Search SIC');
        dsic.select(function(sr, target){
            console.log(sr);
            $('.model-list #sic').val(sr.name);
            $('.model-list #sic_code').attr('data-value',sr.id);
        });
        
        $('#sic_code button').click(function(){
            dsic.open();
        });
   });
         
    // search SIC 
    views.view('/pages/sales/search','#search_sic', function(){        
        dialog = new searchDialog('#search_sic', "/pages/sales/Model/sic-search",'Search SIC');
        dialog.select(function(sr, target){
            // console.log(id, r);
            
            var prow = $(target).parents('tr:first');
            var id = prow.attr('data-id'); 
            var r = editF.getrow(id);            
            r.sic = sr.id;            
            r.syear = r.year;
            delete r.year;
               ajx('/pages/sales/Model/editdiv/update', r, function(d){
                   //td.attr('data-old-value', value);
                   if (!d.error) setOk(d.info); 
                   // reload sic data                    
                   ajx('/pages/sales/Model/sicgroup/row',{id:sr.id}, function(d){
                     //console.log(d.row);
                       var tds =  prow.find('td');
                       $(tds[2]).find('span').html(sr.id);
                       $(tds[3]).html(d.row.sic_division);
                       $(tds[4]).html(d.row.major_group);
                       $(tds[5]).html(d.row.industry_group);
                       $(tds[6]).html(d.row.industry);                      
                   });
                   
               });
        });
        
        $('#id button').click(function(){
            dialog.open();
        });
    });
    
   var lkregions = new lookupInput('#region','/pages/sales/Model/regions/load'); 
   
    // Region options init
    $('.bs-model-select').each(function(i,e){
        var sel = $(e);
        var model = sel.attr('data-model')+'/load';
        ajx(model,{},function(d){
            var s = '<option value=""></option>';
            for (var i=0; i<d.rows.length; i++)
            {   var r = d.rows[i];
                s+='<option value="'+r.id+'">'+r.name+'</option>';
            }
            sel.find('select').html(s);
        });

    });
    
});