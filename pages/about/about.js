$(function()
{   $('#view').click(function()
    {  var views = new htviewCached();
       views.view('/pages/about/about','#info');
    });
});
