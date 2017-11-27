$(function()
{   $('#view').click(function()
    {  var views = new htviewCached();
       views.view('/psys/about/about','#info');
    });
});
