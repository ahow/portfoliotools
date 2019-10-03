
$(function(){
   
   $("input.bs-range").slider({});

   function getControlValues()
   {
      let themes = $('.w-themes');
      let tdata = []
      for (let i=0; i<themes.length; i++)
      {  let e = themes[i]
         let inputs = $(e).find('input')
         tdata.push( {theme_id:$(e).attr('data-id'), 
            range:$(inputs[0]).val(), weight: $(inputs[1]).val() } )      
      }

      let sels = $('.w-selectors');
      let sdata = []
      for (let i=0; i<sels.length; i++)
      {  let e = sels[i]
         let r = { field: $(e).find('select').val(), range: $(e).find('input').val() }
         if (r.field!='') sdata.push( r )
      }

      return { themes:tdata, fields:sdata }
   }


   const alist = new arrayListTable('.array-list');
   // const alist. = new alist.ListController('.alist.-list', alist.CompaniesView);

   function loadData(prm)
   {
      ajx('/pages/sales/Screener', prm ,function(d){
      
     
         last_data = d;
         var link = "<?php echo mkURL('/sales/sic'); ?>";
         // if (prm.alist.!=1) link = "<?php echo mkURL('/sales/companies'); ?>";
          
         alist.setHeader([
                 {title:"Name",f:'name', ondraw:function(v, r){ 
                     return '<a target="_blank" href="'+link+'/'+r.cid+'">'+v+'</a>';
                 }},
                 {title:'Weight theme exposure', f:'weight_theme_exp'},
               ]
         );
         alist.setData(d.rows);
      })
   
   }

   $('.bt-view').click(function(){
      loadData( getControlValues() );
      // alist.setParam('__filter', getControlValues())      
   });

   // Search
   $('#tabsearch .alist.-list .alist.-search button.b-search').click(function(){
         var s = $('.alist.-list .alist.-search input').val().trim();
         var p = filterData.getData(true);
         if (p.sic!=undefined) delete p.sic; //remove unused data
         if (s!='' || p.filter!='')
         {   if (s!='') p.search = '%'+s+'%';
             alist.load(p);
         } else alist.load();
   });
   
   $('#tabsearch  .alist.-list .alist.-search input').keyup(function(d){ 
      if (d.keyCode==13)  $('.alist.-list .alist.-search button.b-search').trigger('click');
   });
  
   // enable pager
   /*
   pager = new alist.Pagination('.alist.-list .alist.-pager');
  

   alist..total(function(total, rows_lim){
      pager.setTotal(total, rows_lim);
      $('#tbedit').addClass('disabled');
   })
   
   pager.change(function(n){
      alist..load(n);
   });
   */

});
