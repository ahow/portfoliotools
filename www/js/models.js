/* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */

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
     if (ontotal!=null) ontotal(d.total);    
     if (onloaded!=null) onloaded(d);
   }
   
   model = $(selector).attr('data-model');
   
   // Redefine draw function if needed
   if (customView==undefined) ondraw = modelTableView;
   else  ondraw = customView;
   
   return {load:load, total:total, click:click, dblclick:dblclick, loaded:loaded, refresh:refresh};
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
       
       function update()
       {  var r = getData();           
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
           getData:getData, setData:setData, loadrow:loadrow, updated:updated};
}
