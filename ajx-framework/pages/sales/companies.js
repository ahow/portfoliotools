
var dialog;

function dlgDivision(selector)
{   var after_save = null;
    
    function onsave(fu)  { after_save = fu; }
    
    function show()
    {  $(selector+' div.modal').modal();         
    }

    function hide()
    {  $(selector+' div.modal').modal('hide');
    }
    
    function getFloat(s)
    { if (s=='') return null;
      if (isNaN(s)) return null;
      return 1.0*s;
    }

    function getString(s)
    { if (s=='') return null;
      return s;
    }    

    function getInt(s)
    { if (s=='') return null;
      if (isNaN(s)) return null;
      return 1*s;
    }  

    function save()
    {  var res = [];
       var num = getInt( $(selector+' #num').val() );
       var me = getString( $(selector+' #me').val() ); 
       var rows = $(selector+' .w-entry-body tr');
       var cid = getString( $(selector+' .w-company-name').attr('data-id') );
       for (var i=0; i<rows.length; i++)
       { var r = $(rows[i]);
         var d = {};
         d.division = num;
         d.me = me;
         d.syear = getInt( r.find('#syear').val() );
         d.sic= getInt( r.find('.w-select-sic').attr('data-id') );
         d.cid = cid;
         d.sales = getFloat( r.find('td:eq(2)').html() );
         d.ebit = getFloat( r.find('td:eq(3)').html() );
         d.assets = getFloat( r.find('td:eq(4)').html() );
         d.capex = getFloat( r.find('td:eq(5)').html() );
         res.push(d);
       }
       
       if (res.length>0)
       {   ajx('/pages/sales/InsertDivisions', {rows:res}, function(d){                                      
               if (!d.error) 
               {  setOk(d.info); 
                  if (after_save!=null) after_save(d);
               }
           });
       }
    }
    
    return {show:show, hide:hide,  save:save, onsave:onsave}
}

function companieEditForm(selector)
{   
    var onclick_ = null;
    var ondelete_ = null;
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
          if (divs[dv]==undefined) divs[dv]={years:{}, ymax:-1000000};
          var yr = 1*r.syear;
          if (yr<ymin) ymin=yr;
          if (yr>ymax) ymax=yr;
          if (divs[dv].ymax<yr) divs[dv].ymax = yr;
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

        var s = '<table class="table table-striped selectable">';
        s+='<tr>';
        s+='<th>#</th><th>Division</th><th>SIC</th>'
        +'<th>Industry</th><th>Major group</th><th>SIC description</th>'; // <th>Industry group</th>
        s+='<th>Sales '+ymax+'</th>';
        for (var y=ymax; y>=ymin; y--) s+='<th class="a-right">%<br>'+y+'</th>';
        s+='</tr>'; 
        
        for (i=1; i<divs.length; i++)
        {   var ym = divs[i].ymax;
            if (divs[i].years[ym]!=undefined)
            {
                var sic = divs[i].years[ym].sic;                
                keys[i] = {};
                keys[i].year = ym;
                keys[i].cid = divs[i].years[ym].cid;
                keys[i].division = i;
                
                s+='<tr data-id="'+i+'">';
                s+='<td><button type="button" title="Delete" class="btn btn-danger b-delete-division"><span class="glyphicon glyphicon-remove"></span>&nbsp;'+i+'</button></td>';
                s+='<td contenteditable="true" name="me" data-old-value="'+divs[i].years[ym].me+'">'+divs[i].years[ym].me+'</td>';
           //     s+='<td><input class="form-control" style="width:80px" name="sic" max="9999" type="number" value="'+sic+'" /></td>';
                // s+='<td><span>'+sic+'</span><button type="button" class="btn btn-default btn-xs">...</button></td>';
                s+='<td class="w-open-sic"><a href="javascript:">'+sic+'</a></td>';
                s+='<td>'+divs[i].years[ym].sicname+'</td>';  // Industry
                s+='<td>'+divs[i].years[ym].major_group+'</td>';
                s+='<td>'+divs[i].years[ym].sic_division+'</td>'; // SIC Division
                // s+='<td>'+divs[i].years[ymax].industry_group+'</td>';
                var base = divs[i].years[ym].sales; 
                var base_me = divs[i].years[ym].me;
                s+='<td contenteditable="true" name="sales" data-old-value="'+base+'">'+(1.0*base).toFixed(2)+'</td>';
                for (var y=ymax; y>=ymin; y--)
                {  // if (divs[i].years[y]!=undefined && base!=0.0 && base_me==divs[i].years[y].me)
                    if (divs[i].years[y]!=undefined && base!=0.0)
                        //  s+='<td class="a-right">'+((divs[i].years[y].sales/base)*100).toFixed(2)+'</td>';
                        s+='<td class="a-right">'+toFloat((divs[i].years[y].sales/ytotal[y])*100,2)+'</td>';
                    else
                        s+='<td class="a-right">-</td>';
                }
                s+='</tr>'; 
            }
        }
        data=keys;
        s+='</table>';
        $(selector+' .formdata').html(s);

        // $(selector+' td button').click(function(e){                        
        $(selector+' td.w-open-sic').click(function(e){                        
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
               });
            }
            
        });
        
        
        $(selector+' .formdata tr').click(function(row){
           $(row.target).parents('table:first').find('tr').removeClass('active');
           var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
           $('button.b-edit-div').removeClass('disabled');
           if(onclick_!=null) onclick_(data[id]);
        });
        $('button.b-edit-div').addClass('disabled'); 
        
        $(selector+' .b-delete-division').click(function(e){
           var id = $(e.target).parents('tr:first').attr('data-id');   
           if(ondelete_!=null) ondelete_(data[id]);
        }); 
              
    }
    
    function click(fu) { onclick_=fu }
    
    function ondelete(fu) { ondelete_=fu }
 
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

    return {click:click, draw:draw, getrow:getrow, load:load, ondelete:ondelete, getCID:getCID}
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
       for (j in d.columns) 
       {   if (d.columns[j]=='sales_bn') s+='<td>'+(1.0*r[ d.columns[j] ]).toFixed(2)+'</td>';
           else s+='<td>'+r[ d.columns[j] ]+'</td>';
       }
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
   model.morder = 0;
   
   model.loaded(function(d){
       var th = $('#tabsearch .model-list thead th:eq(6)');       
       th.attr('title','Click to change order');
       th.html(th.html()+'&nbsp;<span></span>');
       
       // Enable an order by sales_bn
       if (model.morder==1)
          th.find('span').addClass('glyphicon glyphicon-sort-by-attributes-alt');
       else if (model.morder==2)
          th.find('span').addClass('glyphicon glyphicon-sort-by-attributes');
       
       th.click(function(e){
           model.morder++;
           if (model.morder>2) model.morder=0;
           if (model.morder==1)
           {  model.setParam('order', [ {col:'sales_bn', desc:true} ]);
           } else
           if (model.morder==2)
           {  model.setParam('order', [ {col:'sales_bn'} ]);
           } else
           if (model.morder==0)
           {  model.setParam('order', null);
           }
       });       
    });
   model.load();
   
   var selected_row = null;

   var editF = new companieEditForm('#form1');
   compData = new modelFormController('#company-data');
   filterData = new modelFormController('#tabsearch');
   
   var views = new htviewCached();

   editF.ondelete(function(r){
       if (confirm('Delete division #'+r.division+'?'))
       {   console.log(r);
           ajx('/pages/sales/Model/editdiv/delete', {cid:r.cid, division:r.division}, function(d){                   
                   if (!d.error) 
                   { setOk(d.info); 
                     editF.load(selected_row);  
                   }                   
           });           
       }
   });

   model.click(function(e, row){        
        selected_row = row;
        editF.load(row);
        
        compData.loadrow({cid:row.id});
        // compData.setData(row);
        $('#tbedit').removeClass('disabled');
        
       $('button.b-new-div').removeClass('disabled');
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
        model.morder=0;
        model.load();
        $('button.b-new-div').addClass('disabled');
        $('button.b-edit-div').addClass('disabled');
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

   var dlgDiv = null; 
   
      
   
    views.view('/pages/sales/newdivision','#newdivision', function(){
       var dsic3;
       
       dlgDiv = new dlgDivision('#newdivision');
       $('button.b-new-div').click(function(){
            if (selected_row!=null) 
            {   $('label.w-company-name').html(selected_row.name).attr('data-id', selected_row.id);
                $('.w-division .entry:not(:last)').remove();
                $('.w-division td[contenteditable="true"]').html('');
                $('.w-division a.w-select-sic').html('[Select SIC...]').attr('data-id',null);
                dlgDiv.show();
            }
        });

        dlgDiv.onsave(function(d){
            editF.load(selected_row);
            dlgDiv.hide();
        });
        
        $('button.b-save-division').click(function(){        
            dlgDiv.save();
        });
        
       views.view('/pages/sales/search','#search_sic3', function(){        
            dsic3 = new searchDialog('#search_sic3', "/pages/sales/Model/sic-search",'Search SIC');
            
            dsic3.select(function(sr, target){
               $(target).html(sr.name).attr('data-id', sr.id);
            });
            
            $('.w-division a.w-select-sic').click(function(e){
                dsic3.open(e.target);                
            });
        
        });
        
         // clone entries
        $(document).on('click', '.btn-add', function(e)
        {
          e.preventDefault();
          var controlForm = $('.w-entry-body'),
          currentEntry = $(this).parents('.entry:first'),
          newEntry = $(currentEntry.clone()).appendTo(controlForm);
          // vld.keyupValidateOn( newEntry.find('input') );          
          newEntry.find('td[contenteditable="true"]').html('');
          newEntry.find('a.w-select-sic').click(function(e){
                dsic3.open(e.target);
          });
          controlForm.find('.entry:not(:last) .btn-add')
          .removeClass('btn-add').addClass('btn-remove')
          .removeClass('btn-success').addClass('btn-danger')
          .html('<span class="glyphicon glyphicon-minus"></span>');
        }).on('click', '.btn-remove', function(e)
        {
          $(this).parents('.entry:first').remove();
          e.preventDefault();
          return false;
        });
       
    });
   
   views.view('/pages/sales/search','#search_sic2', function(){        
        dsic = new searchDialog('#search_sic2', "/pages/sales/Model/sic-search",'Search SIC');
        dsic.select(function(sr, target){
            $('.model-list #sic').val(sr.name);
            $('.model-list #sic_code').attr('data-value',sr.id);
        });
        
        $('.model-list #sic_code button').click(function(){
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
                       // $(tds[2]).find('span').html(sr.id);
                       $(tds[2]).find('a').html(sr.id);
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
