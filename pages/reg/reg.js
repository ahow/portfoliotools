
function guestList(selector)
{   function getData()
    {   var r = {};
        r.rows = [];
        
        var ctrls = $(selector+' .form-control');
        for (var i=0; i<ctrls.length; i++)
        { var ctrl = $(ctrls[i]);
          var id = ctrl.attr('name');  
          if ( $(ctrl).parents('.entry').length==0 )
          {   r[id] = ctrl.val();
          }
        }
        
        var entryes = $(selector+' .entry');
        for (var i=0; i<entryes.length; i++)
        {  var e = $(entryes[i]);
           var row = {};
           row.firstname=e.find("input[name='firstname']").val();
           row.lastname=e.find("input[name='lastname']").val();
           if (row.firstname!='' && row.lastname!='')
           { r.rows.push(row);
           }
        }

        return r;
    }
    
   function save()
   {   var r = getData();
       ajx('/pages/reg/Save', r, function(d)
       {  if (!d.error)
          { htview('/pages/reg/thank', selector);
          }
       });  
   }
   
    return {save:save};
}

$(function()
{   var vld = new formValidator('#guestlist');
    vld.keyupValidateOn('input.form-control');
 
    var glist = new guestList('#guestlist');
    
    $('#bsubscribe').click(function(){
        if ( vld.validate() ) glist.save();
    });
    
    
    $(document).on('click', '.btn-add', function(e)
    {
      e.preventDefault();
      var controlForm = $('.controls form:first'),
      currentEntry = $(this).parents('.entry:first'),
      newEntry = $(currentEntry.clone()).appendTo(controlForm);
      vld.keyupValidateOn( newEntry.find('input') );
      newEntry.find('input').val('');
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
