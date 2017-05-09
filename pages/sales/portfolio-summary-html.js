function updateSummaryView(row_id)
   {   ajx('/pages/sales/LoadPortfolioSummaries', {id:row_id}, function(d){
            
            ajx('/pages/sales/LoadPortfolioSummariesSettings', {}, function(dd){

                var i;
                var st = dd.row;

                if (d.row.options!=undefined)
                {  var s = '<tr>';
                    for (i=0; i<d.row.options.length; i++)
                    {   var r = d.row.options[i];
                        if (r.checked=='false') s+='<td class="disabled">'+r.name+'</td>';
                        else  s+='<td>'+r.name+'</td>';
                    }
                    s += '<tr>';
                    $('#p-options').html(s);
                }
                $('#pfsummary').attr('data-id', row_id);
                if (d.row.portfolio!=undefined) $('#p-name').html(d.row.portfolio);
                if (d.row.description!=undefined) 
                { $('#p-description').html(d.row.description);
                  $('#pfname').html(d.row.description);      
                }
                if (d.row.bar!=undefined) renderBarChart(d.row.bar);
                if (d.row.line!=undefined) renderLineChart(d.row.line);
                if (d.row.comparison_id!=undefined) themeExposuresChart(d.row.portfolio_id, d.row.comparison_id);
                if (d.row.comparison_id!=undefined && st.metric_id!=undefined)                     
                    socialChart(d.row.portfolio_id, d.row.comparison_id, st.metric_id);
                if (st.metrics!=undefined) metricsAnalysis(st.metrics, d.row.portfolio_id, d.row.comparison_id);
                if (st.esg_metric_id!=undefined) esgAnalysis2(d.row.portfolio_id, st.esg_metric_id, d.row.comparison_id);                    
                if (d.row.descriptions!=undefined)
                {   var desc = d.row.descriptions;
                    $('#pfsummary .sum-title').each(function(i, e){
                            if (desc[i]!=undefined)
                            {  $(e).html(desc[i].title);
                               $(e).next().html(desc[i].descr);
                            }           
                       });
                }
                $('h3.sum-title:first').focus()
                $('.w-save-desc').click(function(e){
                    var desc = [];
                       $('#pfsummary .sum-title').each(function(i, e){
                            desc[i] = {title:$(e).html(), descr:$(e).next().html()};
                       });
                       ajx('/pages/sales/UpdateSummaryDescriptions', {id:row_id, rows:desc}, function(d){
                           if (!d.error) setOk(d.info);
                      });
                });

            }); // Load settings                
            
           }); 
   }

$(function(){
   var pf_id = $('.w-portfolio-id').attr('data-id');
   if (pf_id!=undefined) updateSummaryView(pf_id);
});
