$(function()
{  
    function HHACarePlan(selector)
    {  var onclick = null;
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
       
       function getData()
       {   var r = {};
           var ctrls = $(selector+' input');
           
           for (var i=0; i<ctrls.length; i++)
           { var ctrl = ctrls[i];
             var id = ctrl.getAttribute('id');
             if ($(ctrl).attr('type')=='checkbox')
             {  if (ctrl.checked) r[id]=1; else r[id]=0;
             } else r[id] = $(ctrl).val();
           }
           
           ctrls = $(selector+' textarea');
           for (var i=0; i<ctrls.length; i++)
           { var ctrl = ctrls[i];
             var id = ctrl.getAttribute('id');
              r[id] = $(ctrl).val();
           }
           
           ctrls = $(selector+' .bradio');
           for (var i=0; i<ctrls.length; i++)
           { var ctrl = ctrls[i];
             var id = ctrl.getAttribute('id');
              r[id] = ctrl.getAttribute('data-value');
           }

           return r;
        }
        
       function load(id)
       {   if ($.type(id)!=='object')
           {   var pf = '';
               if (id!=undefined) pf+='/'+id;
               ajx('/pages/medinfo/LoadTable/hha_care_plan'+pf, {}, setData);
           } else  ajx('/pages/medinfo/LoadTable/hha_care_plan', id, setData);
       }
       
       function total(fu){ ontotal = fu;}
       
       function click(fu){ onclick = fu;}
       
       function save()
       {  var r = getData();
          ajx('/pages/medinfo/SaveTable/hha_care_plan', r, function(d){
                 if (!d.error) setOk(d.info);
          }); 
       }
       
       function loaded(fu){ onloaded = fu;}
       
       return {load:load, total:total, click:click, loaded:loaded, save:save};
    }
    
     var hha = new HHACarePlan('#hha_care_plan');
     
     $('#bthhasave').click(function(){
        hha.save();
     });
    
     $('.bradio button').click(function(e){
        var b = $(e.target);
        b.parents('.bradio:first').find('button').removeClass('active');
        b.addClass('active');
        b.parents('.bradio:first').attr('data-value',  b.attr('data-id') );
    });
    
     $('.vwtabs-na').click(function(e){
        var b = $(e.target);
        var chb = b.parents('tr:first').find('input[data-control-type]');
        
        if (e.target.checked)
            chb.each(function(i,e){ e.checked=false; $(e).attr('disabled',true)}); 
        else
            chb.each(function(i,e){ $(e).attr('disabled',null)});
    });
    

    
    $('#vital_sign_na').click(function(e){
        if (e.target.checked) $('#collapse1').collapse('hide');
        else $('#collapse1').collapse('show');
    });
    
});


