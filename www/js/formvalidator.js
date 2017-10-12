// Simple form validator by Fedotov V.V. 2016

var gl_formvalidator = { controls:{} }

gl_formvalidator.controls.basic = function(selector)
{    function setData(value)
     {  $(selector).val( value );
     }
     
     function getData()
     {  return $(selector).val();
     }
     
     return {getData:getData, setData:setData}
}

gl_formvalidator.controls.data = function(selector)
{    function setData(value)
     {  $(selector).attr('data-value', value );
     }
     
     function getData()
     {  return $(selector).attr('data-value');
     }
     
     return {getData:getData, setData:setData}
}

gl_formvalidator.controls.lkvalue = function(selector)
{    function setData(value)
     {  $(selector).attr('data-id', value );
        $(selector).val( value );
     }
     
     function getData()
     {  return $(selector).attr('data-id');
     }
     
     return {getData:getData, setData:setData}
}

gl_formvalidator.controls.checkbox = function(selector)
{    function setData(value)
     {   if (value==1) value=true;
         else if (value==0) value=false;
         $(selector)[0].checked = value;
     }
     
     function getData()
     {   var v = $(selector)[0].checked;
         if (v) v=1; else v=0;
         return v;
     }
     
     return {getData:getData, setData:setData}
}

// popoverErrors type
function popoverErrors()
{   var props = {
         trigger:'click',
         container:'body',
         placement: 'left',
         content:function(){         
            return $(this).attr('title'); 
         }
      };

    function setInvalid(ctrl, hints)
    { $(ctrl).attr('title', hints);
      $(ctrl).popover('show');
    }
   
    function setValid(ctrl)
    { $(ctrl).attr('title', '');
      $(ctrl).popover('hide');
    }
    
    function init(obj)
    { obj.popover(props);
    }
    
    return  {setInvalid:setInvalid, setValid:setValid, init:init};
}

// redlineErrors type
function redlineErrors()
{   function setInvalid(ctrl, hints)
    { var ct = $(ctrl);     
      var rl = ct.parents('.form-group:first').find('.redline-error');
      if (rl.length==0)
        ct.parents('.form-group:first').append('<div class="redline-error">'+hints+'</div>');
      else 
        rl.html(hints); 
    }
   
    function setValid(ctrl)
    { $(ctrl).parents('.form-group:first').find('.redline-error').remove();
    }
    
    function init(obj)
    {
    }
    
    return  {setInvalid:setInvalid, setValid:setValid, init:init};
}



   
// form validator
function formValidator(selector)
{   var locale = new localeLoader('lang/formvalidator');
    var hints = {};
    
    locale.onload(function(){
       var T = locale.T;
       hints = {req:T('REQ_VAL'), minlen:T('TOO_SHORT'), 
       email:T('WRONG_EMAIL_FMT'), equalto:T('CNFRM_FIELD'),
       maxlen:T('TOO_LONG'),reqradio:T('NO_OPTIONS_SELECTED'),
       gt:T('GRATER_THAN'),lt:T('LESS_THAN')}
    });
    
    var vErrors = null;
        
    // delayed run
    function delayedRun(msec)
    { var timer = null;
      function run(fu)
      {  if (timer!=null)  clearTimeout(timer);
         timer = setTimeout(fu, msec);
      }   
      return {run:run};
    }
    
    function setDelayedCheck(i,v)
    {    var d = new delayedRun(700); 
         $(v).keyup(function(){
             d.run(function(){ 
                 validateSingle(v);
             });
         }).parent().removeClass('has-error').removeClass('has-success');
    }
    
    function keyupValidateOn(ctrls_selector)
    {   if ( typeof(ctrls_selector)=="object" ) 
            ctrls_selector.each(setDelayedCheck);
        else 
            $(selector+' '+ctrls_selector).each(setDelayedCheck);
    }

    function validateEmail(email) 
    { var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

   function parseParams(s)
    { var a = s.split(',');
      var c = [];
      var r = {};
      for(var i in a)
      { b = a[i].split('=');
        if (b.length>0)
        {   var id = b[0];
            if (b.length==2) 
            {   var v = b[1];
                if ( $.isNumeric(v) ) v = 1*v; else
                if (v=='true') v=true; else
                if (v=='false') v=false; else
                if (v.charAt(0)=="'") v = v.replace(/^\'+|\'+$/g, '');
                r[id]=v;
            } else r[id]=true;
        }
      }      
      return r;
    }

    function setInvalid(ctrl, hints)
    { $(ctrl).parent().removeClass('has-success').addClass('has-error');
      vErrors.setInvalid(ctrl, hints);
    }
   
    function setValid(ctrl)
    { $(ctrl).parent().removeClass('has-error').addClass('has-success');
      vErrors.setValid(ctrl);
    }
    
    function validateSingle(ctrl)
    {   var prm;
        var valid = true;
        if (ctrl.getAttribute!=undefined) prm = ctrl.getAttribute('data-validate');
        if (prm!=undefined)
        {   var h = [];
            p = parseParams(prm);
            
            if (p.req!=undefined && p.req && ctrl.value=='')
            {  valid=false;
               h.push(hints.req);
            }
            
            if (p.minlen!=undefined 
                && ctrl.value!='' 
                && ctrl.value.trim().length<p.minlen) 
            { 
                valid=false;
                h.push(hints.minlen);
            }
            
            if (p.maxlen!=undefined 
                && ctrl.value!='' 
                && ctrl.value.trim().length>p.maxlen) 
            { 
                valid=false;
                h.push(hints.maxlen);
            }

            if (p.gt!=undefined 
                && ctrl.value!='' 
                && 1.0*ctrl.value>p.gt) 
            { 
                valid=false;
                h.push(hints.gt+' '+p.gt);
            }

            if (p.lt!=undefined 
                && ctrl.value!='' 
                && 1.0*ctrl.value<p.lt) 
            { 
                valid=false;
                h.push(hints.lt+' '+p.lt);
            }
                        
            if (p.email!=undefined && ctrl.value!='' && !validateEmail(ctrl.value)) 
            {
                valid=false;
                h.push(hints.email);
                
            }
            
            if (p.equalto!=undefined && $(p.equalto).val()!=ctrl.value ) 
            {
                valid=false; 
                h.push(hints.equalto);
            }
            
            if (p.reqradio!=undefined) 
            {   var c = 0;
                var ctrls = $(selector+' input[type="radio"]');
                for (var i=0; i<ctrls.length; i++)
                {   var r = $(ctrls[i]);
                    if (r.attr('name')==p.reqradio && r[0].checked) c++;                    
                }
                if (c==0)
                {  valid=false; 
                    h.push(hints.reqradio);
                }
            }
            
            if (p.regexp!=undefined && p.msg!=undefined && ctrl.value!='')
            {   var r = new RegExp(p.regexp);
                if (!r.test(ctrl.value)) 
                { h.push(p.msg);
                  valid=false;
                }
            }
            
            if (valid) setValid(ctrl); else setInvalid(ctrl, h.join("\n"));
        }
        return valid;
    }
    
    function validate()
    {   var res = true;
        var ctrls = $(selector+' .form-control');        
        for (var i=0; i<ctrls.length; i++)
        {  res &= validateSingle(ctrls[i]);
        }
        var ctrls = $(selector+' input[type="radio"]');
        for (var i=0; i<ctrls.length; i++)
        {  res &= validateSingle(ctrls[i]);
        }
        return res;
    }
   
    function validateRadioGroups()
    {  var ctrls = $(selector+' input[type="radio"]');
       for (var i=0; i<ctrls.length; i++)
       {  validateSingle(ctrls[i]);
       }
    }
   

    // vErrors = new popoverErrors(); // set  errors render
    vErrors = new redlineErrors(); // set  errors render
    
    // Text inputs initialisation
    vErrors.init( $(selector+' input.form-control') );
    
    // Radio groups initialisation
    $(selector+' input[type="radio"]').each(function(i,e)
    {   if ($(e).attr('data-validate')!=undefined)
        {  vErrors.init( $(e) );         
        }
        $(e).click(function()
        { validateRadioGroups();
        });
    });
    
    return {validate:validate, validateSingle:validateSingle, keyupValidateOn:keyupValidateOn}
}



