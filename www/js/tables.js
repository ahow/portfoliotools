function itemList(selector)
{ var lastItem = null;
  var onclick = null;
 
  function click(f) { onclick = f;  }
 
  function selectItem(item)
  {    if (lastItem!=null) lastItem.removeClass('active');
       lastItem = $(item.target);
       lastItem.addClass('active');
       if (onclick!=null) onclick(lastItem);
  }
    
  function draw(d)
  {  if (d.rows!=undefined)
     { var s='';
       for (i in d.rows)
       { var r = d.rows[i];
         var tname = r[0];
         if (tname.substr(0,3)!='md_') s+='<a href="#" class="list-group-item">'+tname+'</a>';
       }
       $(selector).html(s);
       $(selector).find('a').click(selectItem);
     }
  }
  
  ajx('/pages/tables/Load', {}, draw);
  return {click:click};
}

function tableStuct(selector)
{ var lastTable = '';
    
  function draw(d)
  {  if (d.rows!=undefined)
     { var s='<h2>'+lastTable+'</h2>';
       s+='<table class="table table-striped">';
       s+='<tr>';
       for (i in d.heads)
       { s+='<th>'+d.heads[i]+'</th>';
       }
       s+='</tr>';
       for (i in d.rows)
       { s+='<tr>';         
         var r = d.rows[i];
         for (j in r) s+='<td>'+r[j]+'</td>';
         s+='</tr>';
       }
       $(selector).html(s);       
     }
  }
  
  function load(tname)
  { lastTable = tname;
    ajx('/pages/tables/Table/'+tname, {}, draw);   
  }
  
  function getActive()
  { return lastTable;
  }
  
  return {load:load, getActive:getActive};
}


$(function()
{   var items = new itemList('#tables');
    var table = new tableStuct('#table');
    
    items.click(function(d)
    {   table.load(d.html());
        $('#add_view').removeClass('hidden');
    });
    
    $('#add_view').click( function(){ 
        ajx('/pages/tables/AddView/'+table.getActive(), {}, function(d){
             if (!d.error) setOk(d.success);
        });
    });
    
    $('#btCrTable').click(function(){
        $('#mdCrTable').modal();
    });

    // create table
    $('#bt-create-table').click(function(){
        var form = $('#mdCrTable');
        var d = {};
        d.table = form.find('#tableName').val().trim();
        d.cols = [];
        var rows = form.find('.entry');
        for(var i=0; i<rows.length; i++)
        { var e=$(rows[i]);
          var r={};
          r.name = e.find('.db-col-name').val();
          r.dtype = e.find('.db-col-type').val();
          r.required = e.find('.db-col-req')[0].checked;
          r.dsize = e.find('.db-col-dsize').val();
          d.cols.push(r);
        }
        ajx('/pages/tables/CreateTable', d, function(d){
             if (!d.error) setOk(d.success);
        });
    });

    function onTypeSelect(d)
    {  var entry = $(d.target).parents('.entry:first');
       var t = d.target.value;
       if ( t=='VARCHAR' || t=='CHAR')
       {  entry.find('.db-col-dsize').removeAttr('disabled');
       }  else entry.find('.db-col-dsize').attr('disabled','disabled');
    }

    // On selecting type datasize state changed
    $('#fields .db-col-type').click(onTypeSelect)
    
    // Dynamic rows 
    $('#fields').on('click', '.btn-add', function(e)
    {
      e.preventDefault();
      var controlForm = $('.controls form:first'),
      currentEntry = $(this).parents('.entry:first'),
      newEntry = $(currentEntry.clone()).appendTo(controlForm);
      newEntry.find('input').val('');
      newEntry.find('.db-col-type').click(onTypeSelect);
      newEntry.find('.db-col-type').trigger('click');
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

