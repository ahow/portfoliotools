var pager;

$(function(){

  if ($('.model-list').length>0)
  { 
       var model = new modelListController('.model-list');
       model.load();
       model.click(function(e, row){
           // console.log(row);
       });
       
       
       // Search
       $('.model-list .model-search button').click(function(){
           var s = $('.model-list .model-search input').val().trim();
           if (s!='') model.load({search:'%'+s+'%'});
           else model.load();
       });

       $('.model-list .model-search input').keyup(function(d){ 
           if (d.keyCode==13)  $('.model-list .model-search button').trigger('click');
       });
       
       // enable pager
       pager = new modelPagination('.model-list .model-pager');
       
       model.total(function(total, rows_lim){
           pager.setTotal(total, rows_lim);
       })
       pager.change(function(n){
           model.load(n);
       });
   } 
});
