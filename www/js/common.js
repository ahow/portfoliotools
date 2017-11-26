// path exampe lang/js 
function cachedLocaleLoader() 
{   var lc = {};
    var ploaded = {};

    function translate(path, onload)
    {   if (ploaded[path]!=undefined) 
        {  if (onload!=undefined) onload(T);
        } else
        {  ajx('/lang/lang/Locale',{path:path}, function(d){ 
               lc = $.extend(lc, d.locale);
               ploaded[path] = true;
               if (onload!=undefined) onload(T);
           });
        }
    }
    
    
    function T(name)
    {  if (lc[name]!=undefined) return lc[name];
       return name;
    }
    
    function log()
    {   console.log(ploaded);
        console.log(lc); 
    }

    return {T:T, log:log, translate:translate};
}

gl_Locales = new cachedLocaleLoader();

function ajx(path, param, onOk, onErr)
{   $.post('/ajax.php'+path, param , function(d)
   {  if (d.error==undefined) setError('Ajax: unknown error'); else
      if (d.error) 
      { setError(d.errmsg);
        if (onErr!=undefined) onErr(d);
      } else if (onOk!=undefined) onOk(d);
   }, 'json').fail(function(e,msg){
       setError(e.status+": "+e.statusText);
       if (onErr!=undefined) onErr(e);
   });
}


function htview(path, selector, onOk)
{  $.post('/html.php'+path, function(d)
   {  $(selector).html(d);
      if (onOk!=undefined) onOk(d);
   }, 'html');
}

function htviewCached()
{  var cache = {};
   function view(path, selector, onOk)
   {  if (cache[path]==undefined)
      {    $.post('/html.php'+path, function(d)
           {  $(selector).html(d);
              if (onOk!=undefined) onOk(d);
              cache[path]=d;
           }, 'html');
      } else
      {  var d = cache[path];
          $(selector).html(d);
         if (onOk!=undefined) onOk(d);         
      }
   }
   return {view:view}  
}

function setError(error)
{ var a = $('.w-alert-error');
  a.find('.w-alert-content').append(error+'<br>');
  a.fadeIn(700);
}



function setOk(msg)
{ var a = $('.w-alert-ok');
  a.find('.w-alert-content').append(msg+'<br>');
  a.fadeIn(500);
  if (setOk.timout!=undefined) window.clearTimeout(setOk.timout);
  setOk.timout = window.setTimeout(function () { a.fadeOut(500);  a.find('.w-alert-content').html(''); }, 4000);
}


$(function()
{  // Enable multi modal for bootstrap
    $(document).on('show.bs.modal', '.modal', function (event) {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);            
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });

    // This just makes all bootstrap native .modals jive together
  $(document).on("hidden.bs.modal", function (e) {
          if ($('.modal:visible').length) $('body').addClass('modal-open');
          else $('body').removeClass('modal-open');
      }
      );
     $('.w-alert-error button').click(function (){ $('.w-alert-error').fadeOut(500).find('.w-alert-content').html(''); });
     $('.w-alert-ok').click(function(){ $('.w-alert-ok').css('display','none');  }); 
});


