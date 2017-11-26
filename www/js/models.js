/* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */

var ctrlKey = false;
$('body').keydown(function(e){ ctrlKey = e.ctrlKey }).keyup(function(){ctrlKey=false;}); // ctrlKey state


function modelTableView(selector,d,onclick,ondblclick)
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
       if (!ctrlKey) $(row.target).parents('table:first').find('tr').removeClass('active');
       var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
       onclick(row, d.rows[id]);
   });
   
   if (ondblclick!=undefined && ondblclick!=null)  $(selector+' tbody tr').dblclick(function(row){
       $(row.target).parents('table:first').find('tr').removeClass('active');
       var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
       ondblclick(row, d.rows[id]);
   });
}


function modelEditableListView(selector)
{   var data = null;
    var T = function(t){return t;};
    var update_form = null;
    var insert_form = null;
    var on_mnedit = null;
    var on_mninsert = null;
    var on_mndelete = null;
    
    
    var locale = new localeLoader('lang/editablelist');
    locale.onload(function(){ T = locale.T; });
    
    function draw(selector,d,onclick,ondblclick)
    {  var s = '';
       var i;
       data = d;
       
       if (d.acl!=undefined)
       {  if (update_form==null && on_mnedit==null) d.acl.upd = false;
          if (insert_form==null && on_mninsert==null) d.acl.ins = false;
          if (on_mndelete==null) d.acl.del = false;
       }
       
       if (d.titles!=undefined)
       {   var h = '<tr><th style="white-space: nowrap;width:1px;">';
           
           if (d.acl!=undefined && (d.acl.upd||d.acl.ins||d.acl.del))
           { 
           h+='<div class="dropdown">\
  <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">\
  <span class="glyphicon glyphicon-menu-hamburger"></span></button>\
  <ul class="dropdown-menu">';
                if (d.acl.ins) h+='<li class="w-add-row"><a href="javascript:">'
                    +'<span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;'
                    +T('New')+'</a></li>';
                if (d.acl.del) h+='<li class="w-remove-rows disabled"><a href="javascript:">'
                    +'<span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;'
                    +T('REMOVE_SELECTED')+'</a></li>';
  h+='</ul>\
</div>';    
           }
           
           h+='</th>';
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
           
           // draw edit buttons
           s+='<td>';
           if (d.acl!=undefined) 
           {  if (d.acl.upd) s+='<button type="button" title="'+T('Edit')
                +'" class="btn btn-default b-edit-row"><span class="glyphicon glyphicon-edit"></span></button>';
           }
           s+='</td>'; // draw buttons
           
           for (j in d.columns) s+='<td>'+r[ d.columns[j] ]+'</td>';
           s+='</tr>';
       }
       $(selector).find('tbody').html(s);
       if (onclick!=null)  $(selector+' tbody tr').click(function(row){
           if (!ctrlKey) $(row.target).parents('table:first').find('tr').removeClass('active');
           var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
           onclick(row, d.rows[id]);
           $(selector+' li.w-remove-rows').removeClass('disabled');
       });
       
       if (ondblclick!=undefined && ondblclick!=null)  $(selector+' tbody tr').dblclick(function(row){
           $(row.target).parents('table:first').find('tr').removeClass('active');
           var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
           ondblclick(row, d.rows[id]);
       });
       if (on_mninsert!=null)  $(selector+' li.w-add-row').click(on_mninsert);
       if (on_mnedit!=null)  $(selector+' .b-edit-row').click(function(row){
            var id = $(row.target).parents('tr:first').attr('data-id');
            on_mnedit(d.rows[id]);
       });
       if (on_mndelete!=null) $(selector+' li.w-remove-rows').click(function(row){
           var trs =  $(selector+' tbody tr.active');
           if (d.pk==undefined) 
           { setError('No primary_key option in model!');
             return;
           }
           if (trs.length>0)
           {  var rows = [];
              for (var i=0; i<trs.length; i++)
              { var id=$(trs[i]).attr('data-id');
                var r = {};
                for (var j=0; j<d.pk.length; j++)
                {   var k = d.pk[j];
                    r[k]=d.rows[id][k];
                }
                rows.push(r);
              }
              on_mndelete(rows);
           }
       });       
    }

    function setUpdateForm(form) {  update_form = form; }    
    function setInsertForm(form) {  insert_form = form; }
    
    // Menu items
    function onmnedit(fu) {    on_mnedit = fu; }
    function onmninsert(fu) {    on_mninsert = fu; }
    function onmndelete(fu) {    on_mndelete = fu; }
    
    return { draw:draw, setUpdateForm:setUpdateForm, setInsertForm:setInsertForm,
        onmnedit:onmnedit, onmninsert:onmninsert, onmndelete:onmndelete};
}


function modelListController(selector, customView)
{  var ontotal = null;
   var onclick = null;
   var ondblclick = null;
   var onloaded = null;
   var model = '';
   var ondraw = null;
   var last_params; // last parametrs
   var last_id = null;
  
   function load(id)
   {   // console.log(id,last_params);
       if ($.type(id)!=='object')
       {   var pf = '';
           if (id!=undefined && id!=null)
           { pf+='/'+id;
             last_id=id;
           }
           else last_params={};
           ajx(model+'/load'+pf, last_params, draw);
       } else  
       { last_params = id;         
         ajx(model+'/load', id, draw);
       }
   }
   
   function refresh(){ load(last_id); }
   
   function total(fu){ ontotal = fu;}
   
   function click(fu){ onclick = fu;}
   
   function dblclick(fu){ ondblclick = fu;}
   
   function loaded(fu){ onloaded = fu;}
   
   function draw(d)
   { if (ondraw!=null) ondraw(selector, d, onclick, ondblclick);      
     if (ontotal!=null) 
     {  if (d.rows_number_limit!=undefined)  
            ontotal(d.total, d.rows_number_limit);
        else ontotal(d.total);
     }
     if (onloaded!=null) onloaded(d);
   }
   
   // name: filter, search or order
   // p: parameter of filter search or order
   function setParam(name, p)
   { if (p==null && last_params[name]!=undefined) delete last_params[name];
     else last_params[name] = p;
     if (last_id==null) last_id=1;
     load(last_id);    
   }
   
   model = $(selector).attr('data-model');
   
   // Redefine draw function if needed
   if (customView==undefined) ondraw = modelTableView;
   else  ondraw = customView;
   
   return {load:load, total:total, click:click, dblclick:dblclick, loaded:loaded, refresh:refresh, setParam:setParam };
}

function modelPagination(selector)
{   var current_page = 1;
    var start_page = 1;
    var links_num = 10;
    var total_pages = 6; // Всего страниц
    var pages = 5;
    var rows_on_page = 12;
    var onchange = null;
    
    function draw()
    {  var s = '<nav aria-label="Page navigation"><ul class="pagination">';
       if (current_page>links_num) s+='<li><a data-id="-" href="javascript:" aria-label="Previous">&laquo;</a></li>';
       for (var i=start_page; i<=total_pages && i<start_page+links_num; i++) 
       {  if (i==current_page) 
            s+='<li class="active"><a data-id="'+i+'" href="javascript:">'+i+'</a></li>'; 
          else
            s+='<li><a  data-id="'+i+'" href="javascript:">'+i+'</a></li>';
       }
       
       if (i<=total_pages) s+='<li><a data-id="+" href="javascript:" aria-label="Next">&raquo;</a></li>';
       s+='</ul></nav>';
       if (total_pages==1) s='';
       $(selector).html(s);
       $(selector+' li').click(onPageClick);
    }
    
    function onPageClick(e)
    {   var n = e.target.getAttribute('data-id');
        if (n=='+') 
        {   start_page+=links_num; current_page=start_page; 
        } else 
        if (n=='-') 
        { start_page-=links_num;           
          current_page=start_page;
        }
        else current_page = 1*n;
        if (onchange!=null) onchange(current_page);
       draw();
    }
    
    // set Total rows
    function setTotal(total_rows, rows_number_limit)
    {  if ( rows_number_limit!=undefined) rows_on_page = rows_number_limit;
       total_pages = Math.ceil(total_rows/rows_on_page);
       // console.log(start_page,current_page, total_pages);
       if (start_page>total_pages || current_page>total_pages)
       { current_page = 1;
         start_page = 1;
       }
       draw();
    }
        
    function change(fu) { onchange = fu; }
    
    function currentPage() { return current_page; }
    
    return {draw:draw, currentPage:currentPage, setTotal:setTotal, change:change}
}

function modelFormController(selector)
{      var model = '';
       var insert_redirect = '';
       var onloaded = null;
       var onupdated = null;
       
       function loaded(fu){ onloaded = fu;}
       
       function setData(d)
       {   var ctrls = $(selector).find('[data-control-type]');           
           for (var i=0; i<ctrls.length; i++)
           { var ctrl = ctrls[i];
             var id = ctrl.getAttribute('id'), val=null;
             if (d[id]!=undefined)
             {   var c_type = ctrl.getAttribute('data-control-type');           
                 if (gl_formvalidator!=undefined && gl_formvalidator.controls[c_type]!=undefined && id!=null)
                 { var getter = new gl_formvalidator.controls[c_type]('#'+id);
                   getter.setData(d[id]);
                   // console.log(id, d[id]);
                   ctrl.setAttribute('data-old-value', d[id]);
                 }
              }
           }
           if (onloaded!=null) onloaded(d);
       }
       
       function clearData()
       {   var ctrls = $(selector).find('[data-control-type]');           
           for (var i=0; i<ctrls.length; i++)
           { var ctrl = ctrls[i];
             var id = ctrl.getAttribute('id'), val=null;
			 var c_type = ctrl.getAttribute('data-control-type');           
			 if (gl_formvalidator!=undefined && gl_formvalidator.controls[c_type]!=undefined && id!=null)
			 { var getter = new gl_formvalidator.controls[c_type]('#'+id);
			   getter.setData('');			   
			   ctrl.setAttribute('data-old-value', '');
			 }  
           }
           if (onloaded!=null) onloaded({});
       }
       
       function getData(is_insert)
       {   var r = {};
           var ctrls = $(selector).find('[data-control-type]');
           var values = 0;
           if (is_insert==undefined) is_insert = false;
           for (var i=0; i<ctrls.length; i++)
           { var ctrl = ctrls[i];
             var id = ctrl.getAttribute('id'), val=null;             
             var c_type = ctrl.getAttribute('data-control-type');
             var old_value = ctrl.getAttribute('data-old-value');             
             var is_key = (ctrl.getAttribute('data-key')=='true');
             if (gl_formvalidator!=undefined && gl_formvalidator.controls[c_type]!=undefined && id!=null)
             { var getter = new gl_formvalidator.controls[c_type]('#'+id);
               var value = getter.getData();
               if (value=='') value=null;
               if (is_key)
               {  r[id] = value;
               }
               else if (value!=old_value)  // (old_value!=null || is_insert) &&
               { r[id] = value;
                 if (!is_insert) ctrl.setAttribute('data-old-value', value);
                 values++;
                 // console.log(id, value, old_value, is_insert); 
               }  
             }
           }
           if (values==0) return {};           
           return r;
       }
        
       function load(id)
       {   if ($.type(id)!=='object')
           {   var pf = '';
               if (id!=undefined) pf+='/'+id;
               ajx(model+'/load/'+pf, {}, setData);
           } else  ajx(model+'/load', id, setData);
       }

       function loadrow(id)
       {   function onLoad(d)
           { setData(d.row);
           }
           
           if ($.type(id)!=='object')
           {   var pf = '';
               if (id!=undefined) pf+='/'+id;
               ajx(model+'/row/'+pf, {}, onLoad);
           } else  ajx(model+'/row', id, onLoad);
       }
       
       function total(fu){ ontotal = fu;}
       
       function click(fu){ onclick = fu;}
       
       function insert(fu)
       {  var r = getData(true);           
          ajx(model+'/insert', r, function(d){
                 if (!d.error) 
                 {   if (fu!=undefined) fu(d);
                     if (insert_redirect!='') window.location = insert_redirect;
                     setOk(d.info);
                 }
          });
       }

       function deleteRow(r, fu)
       {    ajx(model+'/delete', r, function(d){
                 if (!d.error) 
                 {   if (fu!=undefined) fu(d);                     
                 }
          });
       }
              
       function update()
       {  var r = getData();           
          if (!$.isEmptyObject(r))
          ajx(model+'/update', r, function(d){
                 if (!d.error) 
                 {   setOk(d.info);
                     if (onupdated!=null) onupdated(d);
                 }
          }); 
       }

       
       function bind()
       {   model = $(selector).attr('data-model');
           insert_redirect = $(selector).attr('data-if-inserted-redirect');           
           if (insert_redirect==undefined) insert_redirect='';
           $(selector).find('.model-insert').click(function(){
            insert();
           });
           $(selector).find('.model-update').click(function(){
            update();
           });
       }
       
       function updated(fu){ onupdated = fu; }
    
      bind();
       
      return {load:load, total:total, bind:bind, click:click, loaded:loaded,
           insert:insert, update:update,
           getData:getData, setData:setData, loadrow:loadrow, updated:updated,
           clearData:clearData, deleteRow:deleteRow};
}
