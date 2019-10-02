
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


   function modelCompaniesView(selector,d,onclick,ondblclick)
   {  var s = '';
      var i;
      
      if (d.titles!=undefined)
      {   var h = '<tr>';
         for (i in d.titles)
         { h+='<th>'+d.titles[i]+'</th>';               
         }
         h+='</tr>';
         $(selector).find('thead').html(h);
      }
      for (i in d.rows)
      {   var j;
         var r = d.rows[i];
         var cl = '';
         if (r.reviewed==1) cl=' class="w-reviewed"';
         if (r.id!=undefined) s+='<tr data-id="'+i+'"'+cl+'>'; else s+='<tr>';
         for (j in d.columns) 
         {   if (d.columns[j]=='sales_bn') s+='<td>'+(1.0*r[ d.columns[j] ]).toFixed(2)+'</td>';
            else s+='<td>'+r[ d.columns[j] ]+'</td>';
         }
         s+='</tr>';
      }
      $(selector).find('tbody').html(s);
      if (onclick!=null)  $(selector+' tbody tr').click(function(row){
         $(row.target).parents('table:first').find('tr').removeClass('active');
         var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
         onclick(row, d.rows[id]);
      });
      
      if (ondblclick!=undefined && ondblclick!=null)  $(selector+' tbody tr').dblclick(function(row){
         $(row.target).parents('table:first').find('tr').removeClass('active');
         var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
         ondblclick(row, d.rows[id]);
      });
   }


   const model = new modelListController('.model-list', modelCompaniesView);
   model.morder = 0;
   
   model.loaded(function(d){
       var th = $('#tabsearch .model-list thead th:eq(6)');       
       th.attr('title','Click to change order');
       th.html(th.html()+'&nbsp;<span></span>');
       
       // Enable an order by sales_bn
       if (model.morder==1)
          th.find('span').addClass('glyphicon glyphicon-sort-by-attributes-alt');
       else if (model.morder==2)
          th.find('span').addClass('glyphicon glyphicon-sort-by-attributes');
       
       th.click(function(e){
           model.morder++;
           if (model.morder>2) model.morder=0;
           if (model.morder==1)
           {  model.setParam('order', [ {col:'sales_bn', desc:true} ]);
           } else
           if (model.morder==2)
           {  model.setParam('order', [ {col:'sales_bn'} ]);
           } else
           if (model.morder==0)
           {  model.setParam('order', null);
           }
       });       
   });

   $('.bt-view').click(function(){
      model.setParam('__filter', getControlValues())      
   });

   // Search
   $('#tabsearch .model-list .model-search button.b-search').click(function(){
         var s = $('.model-list .model-search input').val().trim();
         var p = filterData.getData(true);
         if (p.sic!=undefined) delete p.sic; //remove unused data
         if (s!='' || p.filter!='')
         {   if (s!='') p.search = '%'+s+'%';
             model.load(p);
         } else model.load();
   });
   
   model.load();

   $('#tabsearch  .model-list .model-search input').keyup(function(d){ 
      if (d.keyCode==13)  $('.model-list .model-search button.b-search').trigger('click');
   });
  
   // enable pager
   pager = new modelPagination('.model-list .model-pager');
  

   model.total(function(total, rows_lim){
      pager.setTotal(total, rows_lim);
      $('#tbedit').addClass('disabled');
   })
   
   pager.change(function(n){
      model.load(n);
   });



});
