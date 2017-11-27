function profileView()
{   function profileCtrl(selector)
    { var onloaded = null;
        
      function getData()
      { var r = {};
        var ctrls = $(selector+' .form-control');
        for (var i=0; i<ctrls.length; i++)
        { var ctrl = $(ctrls[i]);
          var id = ctrl.attr('id');
          r[id] = ctrl.val();
        }
        return r;
      }
      
      function setData(d)
      { var i;
        for (i in d.row)
        {
           $(selector+' #'+i).val(d.row[i]);
        }
        if (onloaded!=null) onloaded(d);
      }
      
      function save()
      {  var r = getData();
         ajx('/psys/profile/Save', r, function(d){
             if (!d.error) setOk(d.info);
         }); 
      }
      
      function load()
      { ajx('/psys/profile/Load', {}, setData );
      }
      
      function loaded(foo){  onloaded=foo;  }
      
      return {save:save, load:load, loaded:loaded};
    }
    
    
    
    var profile  = new profileCtrl('#profile');
    var vld = new formValidator('#form');
    
    function init()
    {   vld.keyupValidateOn('input.form-control');
        profile.load();
        profile.loaded(function(){
            $('#form').removeClass('hidden');
        });
        $('#bsaveprofile').click(function()
        { if ( vld.validate() )profile.save();
        });
    }
    
    return {init:init}
}

$(function()
{   var views = new htviewCached();
    var profile = new profileView();
    
    
    views.view('/psys/profile/profile','#profile', function(){
        profile.init(); 
    });

});
