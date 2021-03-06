function lookupInput(selector, model, opt)
{  var onselect = null;
    
   function lookup(text,foo)
   {  ajx(model,{search:'%'+text+'%'},function(d){
            return foo(d.rows);
       });
   }
   
   if (opt==undefined) opt={};
   
   opt.source = lookup;
   
   if (opt.delay==undefined) opt.delay = 650;
   if (opt.minLength==undefined) opt.minLength = 1;
   if (opt.items==undefined) opt.items = 'all';

   
   opt.updater = function(d){
            $(selector).attr('data-id', d.id);
            if (onselect!=null) onselect(d);
            return d;
    }
   
   $(selector).attr('data-control-type','lkvalue');   
   $(selector).typeahead(opt);
   
   function select(fu){ onselect=fu; }
   
   return {select:select}; 
}

function searchDialog(selector, datamodel, title)
{  let selected = null;
   let onselect = null;
   let openTarget = null;
   let pager = null;
   
   $(selector+' div.model-list').attr('data-model', datamodel);
   $(selector+' .modal-title').html(title);
    
   let model = new modelListController(selector+' .model-list');
   model.load();
   model.click(function(e,row){
        selected = row;
        $(selector+' button.b-select').removeClass('disabled');        
   });
   
   model.dblclick(function(e,row){
        selected = row;       
        if (onselect!=null) onselect(selected,openTarget);
        $(selector+' .modal').modal('hide');       
   });
   
   // Search
   $(selector+' .model-list .model-search button').click(function(){
       var s = $(selector+' .model-list .model-search input').val().trim();
       if (s!='') 
       { model.load({search:'%'+s+'%'});
       } else model.load();
   });

   $(selector+' .model-list .model-search input').keyup(function(d){ 
       if (d.keyCode==13)  $(selector+' .model-list .model-search button').trigger('click');
   });
   
   $(selector+' button.b-select').click(function(){
       if (onselect!=null) onselect(selected,openTarget);
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
   
   function open(target)
   {  openTarget = target;
      $(selector+' div.modal').modal();
      setTimeout(function(){            
             $(selector+' .model-search input').focus();
           }, 300);
   }

   function getModel()
   {
        return model;
   }
   
   
   return {select:select, open:open, getModel:getModel}
}
