function mdSelect(selector, value)
{   var rows = [];
    var model = $(selector).parents('.bs-model-select:first').attr('data-model')+'/load';
    var onselect = null;
    var onloaded = null;
    ajx(model,{},function(d){        
        var s = ''; 
        for (var i=0; i<d.rows.length; i++)
        {   var r = d.rows[i];
            rows[r.id] = r;
            s+='<option value="'+r.id+'">'+r.name+'</option>';
        }
        $(selector).html(s);
        if (value!=undefined) $(selector).val(value);
        if (onloaded!=null) onloaded(rows);
    });
    
    $(selector).click(function(e){
        var id = $(selector).val();        
        if (onselect!=null) onselect( rows[id], id );
    });
    
    function select(fu){ onselect = fu; }
    
    function loaded(fu){ onloaded = fu; }
    
    return {select:select, loaded:loaded};
}
