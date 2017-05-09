$(function(){
 var pf_id = $('#chart').attr('data-id');
 
   ajx('/pages/sales/GetPortfolioName',{id:pf_id}, function(d){
     if (d.row)
     {   ajx('/pages/sales/GetSnapshots',{pf_id:pf_id}, function(dd){
                    dd.title = d.row.portfolio;
                    portfolioSnapshotChart('chart', dd);
         });
     } else setError('Portfolio snapshot not found!');
  });


});
