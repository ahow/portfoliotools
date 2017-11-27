/* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */

function rawItemList(selector)
{ var lastItem = null;
  var onclick = null;
 
  function click(f) { onclick = f;  }
 
  function selectItem(item)
  {    if (lastItem!=null) lastItem.removeClass('active');
       lastItem = $(item.target);
       lastItem.addClass('active');
       if (onclick!=null) onclick(lastItem);
  }
    
  function bind()
  {  $(selector).find('a').click(selectItem);     
  }
  
  function select(item)
  { selectItem({ target:item.get() });
  }
  
  bind();
  return {click:click, select:select};
}


function tableList(selector, table, columns)
{  var ontotal = null;
   var onclick = null;
   var onloaded = null;

   function setData(d)
   {   var s = '';
       var i;
       for (i in d.rows)
       {   var j;
           var r = d.rows[i];
           if (r.id!=undefined) s+='<tr data-id="'+r.id+'">'; else s+='<tr>';
           for (j in columns) s+='<td>'+r[ columns[j] ]+'</td>';
           s+='</tr>';
       }
       $(selector+' tbody').html(s);
       if (ontotal!=null) ontotal(d.total);    
       if (onclick!=null)  $(selector+' tbody tr').click(onclick);
       if (onloaded!=null) onloaded(d);
   }
  
   function load(id)
   {   if ($.type(id)!=='object')
       {   var pf = '';
           if (id!=undefined) pf+='/'+id;
           ajx('/psys/admin/LoadTable/'+table+pf, {}, setData);
       } else  ajx('/psys/admin/LoadTable/'+table, id, setData);
   }
   
   function total(fu){ ontotal = fu;}
   
   function click(fu){ onclick = fu;}
   
   function loaded(fu){ onloaded = fu;}
   
   return {load:load, total:total, click:click, loaded:loaded};
}


function userlistView()
{
    /*------------- Group List ---------------------------*/
    function groupsList(selector, table)
    {  var ontotal = null;
       var onclick = null;
       var onloaded = null;
       var user_id = null;  // ID пользователя

       function setData(d)
       {   var s = '<div class="modal-body form-horizontal usergroups">';
           var i;
           for (i in d.rows)
           {  var r = d.rows[i];
              var ch = '';
              if (r.user_id!=null) ch = ' checked';
              s+='<div class="form-group">';
              s+='<div class="col-xs-4"><label>'+r.grname+'</label></div>';
              s+='<div class="col-xs-4"><input data-id="'+r.id+'" type="checkbox" '+ch+'/></div>';
              s+='</div>';          
           }
           s+='</div>';
           $(selector).html(s);
           if (ontotal!=null) ontotal(d.total);    
           if (onclick!=null)  $(selector+' tbody tr').click(onclick);
           if (onloaded!=null) onloaded(d);
       }
       
       function getData()
       {   var r = {};
           var ctrls = $(selector+' .usergroups input');       
           r.user_id = user_id;
           r.groups = [];
           for (var i=0; i<ctrls.length; i++)
           { var ctrl = ctrls[i];
             var gid = ctrl.getAttribute('data-id');
             if (ctrl.checked) r.groups[gid]=1; else r.groups[gid]=0;         
           }
           return r;
        }
      
       function load(id)
       {   var pf = '';
           if (id!=undefined) pf+='/'+id;
           user_id = id;
           ajx('/psys/admin/LoadTable/'+table+pf, {}, setData);       
       }
       
       function total(fu){ ontotal = fu;}
       
       function click(fu){ onclick = fu;}
       
       function loaded(fu){ onloaded = fu;}
       
       function save()
       {  var r = getData();
          ajx('/psys/admin/SaveUserGroups', r, function(d){
                 if (!d.error) setOk(d.info);
          }); 
       }
       
       return {load:load, total:total, click:click, loaded:loaded, save:save};
    }

/* ---------------- User Groups Init    ----------------------*/
    var usersEdit = new modelEditableListView(); 
    var userForm = null;
    
    function addUser()
    {  userForm.clearData();
       $('#useradd-form').modal();
    }
    
    usersEdit.onmninsert(addUser);  
    $('#btnew').click(addUser);

    usersEdit.onmnedit(function(row){
       userForm.loadrow({id:row.id});
       $('#useradd-form').modal();
    });  
    
    usersEdit.onmndelete(function(rows){
       if (confirm('Remove selected user(s)?'))
       {   var model = $('#useradd-form').attr('data-model');
           ajx(model+'/deleteRows', {rows:rows}, function(d){
             if (!d.error)                  
             {  users.refresh();
                setOk('Deleted!');
             }
           });
       }
    });
    
    var users = new modelListController('.model-list', usersEdit.draw);
    var usergroups = new groupsList('#user-groups','usergroups');
    var pager = new modelPagination('.model-list .model-pager');;
    
    function init()
    {
        users.load();
        users.total(function(t, rows_lim){
           $('span.records-total').html(t); 
           pager.setTotal(t, rows_lim);           
       });
       pager.change(function(n){
         users.load(n);
       });
       
       userForm = new modelFormController('#useradd-form');
       
       userForm.loaded(function(){
          $('#useradd-form #pass').attr('data-old-value','');
          $('#useradd-form #pass').val('');
          $('#useradd-form #pass2').val('');
       });
       
       userForm.updated(function(d){
                 if (!d.error) 
                 {  $('#useradd-form').modal('hide');
                    users.refresh();
                 }
      });
       
       var vld = new formValidator('#useradd-form');
        
        users.click(function(id, row){           
           $('#editform').addClass('disabled-input');
           var tr = $(row.target).parents('table:first').find('tr').removeClass('active');    
           var id = row.id;       
           // var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');
           users.current_row = id;
           usergroups.load(id);           
        });
    
       usergroups.loaded(function(){
            $('#editform').removeClass('hidden').removeClass('disabled-input'); 
            $('#btgrsave').removeClass('hidden');
        });
       
       $('#btgrsave').click(function(){
           usergroups.save();           
          // userForm.save();
       });
       
       $('#btdelete').click(function(e){
           if (confirm('Remove selected user?'))
           {   var model = $('#useradd-form').attr('data-model');
               ajx(model+'/delete', {id:users.current_row}, function(d){
                 if (!d.error)                  
                 {  users.load();
                    setOk('User deleted!');
                 }
               });
               
           }
       });
       
       $('button.b-useradd').click(function(){          
          if (vld.validate()) 
          {   var id = $('#useradd-form #id').val();
              if (id=='') userForm.insert(function(d){
                 if (!d.error) 
                 {  $('#useradd-form').modal('hide');
                    users.load();
                 }
              });
              else userForm.update();
          }
       });
       
       $('#btsearch').click(function(){
           var s = $('#tsearch').val().trim();
           $('#editform').addClass('hidden');
           if (s!='') users.load({search:s+'%'});
           else users.load();
       });
       
       $('#tsearch').keyup(function(d){ 
           if (d.keyCode==13)  $('#btsearch').trigger('click');
       });
       

    } 
    
    return {init:init};
}

function vipreservView()
{
    var vipreserv = new tableList('#vipreserv-table','vipreserv',['bookdate','firstname','lastname','email','phone','guests','package']);
    
    function init()
    {
        vipreserv.load();
        vipreserv.total(function(t){
           $('#vipreserv-list span.records-total').html(t); 
       });
    }
    
    return {init:init};
}

function evnameglView()
{   var evnamegl = new tableList('#evnamegl-table','evnamegl',['created','email','firstname','lastname','guests']);
    var evnamegl_guests = new tableList('#table-guests','evnamegl_guests',['firstname','lastname']);

     function init()
     {
       
       evnamegl.load();
       evnamegl.total(function(t){
           $('#evnamegl-list span.records-total').html(t); 
       });
       
       evnamegl.click(function(row){
           var id = $(row.target).parents('tr:first').attr('data-id');
           evnamegl_guests.loaded(function(d){
               var hds = $('#viewrecord p');
               for (var i=0; i<hds.length; i++)
               {   var r=$(hds[i]);
                   var fname = r.attr('data-name');
                   r.html( d.head[fname] );
               }
               $('#viewrecord').modal('show');
           });
           evnamegl_guests.load(id);
                     
           
       });
       
    }
    return {init:init};
}


function emailtmplView()
{
    /*---- Base class -------------------*/
    function emailTemplates(selector)
    {   var tmpl;
        
        function setTemplate(tmp)
        { tmpl = tmp;
          load();
        }
        
        function getData()
        {   var r = {};
            var ctrls = $(selector+' .form-control');
            for (var i=0; i<ctrls.length; i++)
            { var ctrl = $(ctrls[i]);
              var id = ctrl.attr('id');
              r[id] = ctrl.val();
            }
            return r;
        }

        function setData(d)
        {   var i;
            for (i in d.row)
            {
               $(selector+' #'+i).val(d.row[i]);
            }
        }
        
       function save()
       {   var r = getData();
           ajx('/psys/admin/SaveTmpl', r, function(d)
           { if (!d.error) setOk(d.info);
           });  
       }
      
       function load()
       {  ajx('/psys/admin/LoadTmpl/'+tmpl, {}, setData);       
       }
       
       return {save:save, load:load, setTemplate:setTemplate };
    }
    
    
    /* ------------ Init ------------*/
     var templates = new emailTemplates('#email-templates');
    
    function init()
    {
        $('#seltmpl').change(function(item){
           var tmpl = item.target.value;
           templates.setTemplate(tmpl);
        });
        $('#seltmpl').trigger('change');
        $('#btchangepw').click(templates.save)
    }
    return {init:init};
}

function  signuplistView()
{   var signups = new tableList('#signup-table','signup',['created','email']);
        
    function init()
    {  signups.load();
       signups.total(function(t){
           $('#signup-list span.records-total').html(t); 
       })
    }
    return {init:init};
}

function emailsettingsView()
{
/*---------------- Base class ---------------------- */
    function emailSettings(selector)
    { 
      function getSettings()
      { var r = {};
        var ctrls = $(selector+' .form-control');
        for (var i=0; i<ctrls.length; i++)
        { var ctrl = $(ctrls[i]);
          var id = ctrl.attr('id');
          r[id] = ctrl.val();
        }
        return r;
      }
      
      function setSettings(d)
      { var i;
        for (i in d.row)
        {
           $(selector+' #'+i).val(d.row[i]);
        }
      }
      
      function save()
      {  var r = getSettings();
         ajx('/psys/admin/SaveEmailSettings', r, function(d){
             if (!d.error) setOk(d.info);
         }); 
      }
      
      function load()
      { ajx('/psys/admin/LoadEmailSettings', {}, setSettings );
      }
      
      return {save:save, load:load};
    }
    
 /* ----------------- Init --------------------*/   
    var esettings = new emailSettings('#email-settings');
 
    function init()
    {    $('#btsmtpsave').click(esettings.save);
         esettings.load();
    }
    return {init:init};
}


function changepassView()
{
    // --------- base class --------------------------
    function changePassword(selector)
    { 
      function getSettings()
      { var r = {};
        var ctrls = $(selector+' .form-control');
        for (var i=0; i<ctrls.length; i++)
        { var ctrl = $(ctrls[i]);
          var id = ctrl.attr('id');
          r[id] = ctrl.val();
        }
        return r;
      }
      
      
      function save()
      {  var r = getSettings();
          
         ajx('/psys/admin/SavePassword', r, function(d){
             if (!d.error) setOk(d.info);
         }); 
      }
        
      return {save:save};
    }
    
    
    // --------- Init ------------------
    var chpwd = new changePassword('#change-password');

    function init()
    {   $('#btchangepw').click(chpwd.save);
    }
    return {init:init};
}

$(function()
{   var menu = new rawItemList('#admin-menu');
    var views = new htviewCached();
    var views_init = {};
    
    menu.click(function(it)
    {  var view = it.attr('data-view');
       if (view==undefined) return;
       views.view('/psys/admin/'+view,'#views', function(){ 
           if (views_init[view]!=undefined && $.type(views_init[view])=='object')
                views_init[view].init();           
           else 
           {   var classN = view+'View';
               var e;
               // View Class autocreation
               try
               {   views_init[view] = new window[classN]();
                   views_init[view].init(); 
               } catch(e)
               {  setError(classN+' class not found! (admin.js)');
               }
               
           }
       });
    });
    
    menu.select( $('#admin-menu a:first') );
});
