
function renderBarChart(d)
{  Highcharts.chart('ch-bar', {
    chart: {
        type: 'column'
    },
    title: {
        text: d.title
    },
    xAxis: {
        categories: d.columns
    },
    yAxis: {
        min: 0,
        title: {
            text: d.title
        }
    },
    legend: {
        reversed: false
    },
    credits: { enabled: false },
    plotOptions: {
        series: {
            stacking: 'normal'
        }
    },
    series: fixSeries(d.series)
  });

}

function renderLineChart(d)
{ 
  Highcharts.chart('ch-line', {

    title: {
        text: d.title
    },

    yAxis: {
        title: {
            text: d.title
        }
    },
    legend: {
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'middle'
    },
    credits: { enabled: false },
    plotOptions: {
        series: {
            pointStart: 2010
        }
    },

    series: fixSeries(d.series)

  });
}


function drawStakeHolder(div, d)
{   var p = {
    chart: {
        type: 'column'
    },
    title: {
        text: 'By stakeholder'
    },
    xAxis: {
    },
    credits: {
        enabled: false
    }
   };
   
   p.xAxis.categories = [];
   p.series = [];
   
   for (var i=0; i<d.p1.names.length; i++)
   {   var name = d.p1.names[i];
       p.xAxis.categories.push(name);       
   }
   
   var dat=[];
   for (var i=0; i<d.p1.data.length; i++)
   { dat.push(1.0*d.p1.data[i]);
   }
   p.series.push({name:'Portfolio', data:dat})
   
   dat=[];
   for (var i=0; i<d.p2.data.length; i++)
   { dat.push(1.0*d.p2.data[i]);
   }   
   p.series.push({name:'Comparison', data:dat})
   
   Highcharts.chart(div, p);
}

function esgAnalysis2(pf, mt, comp)
{     $("#esg-analys").LoadingOverlay("show");
      ajx('/pages/sales/ESGData',{pf_id:pf, mt_id:mt, comp_id:comp},function(d){
            $("#esg-analys").LoadingOverlay("hide", true);
            circlesBestWorstChart('esg-analys', d);
      });
}


function socialChart(pf1, pf2, mt)
{   if (pf2!=undefined  && pf1!=undefined && mt!=undefined)
    {       
        if (pf2!=null && mt!=null)
        {              
             var metric_name = '';
             $("#ch-social").LoadingOverlay("show");
             $("#ch-by-company").LoadingOverlay("show");
             $("#ch-by-stakeholder").LoadingOverlay("show");
             
             ajx('/pages/sales/StackedChart',{pf1:pf1, pf2:pf2, mt:mt},function(d){

                $("#ch-social").LoadingOverlay("hide", true);
                $("#ch-by-company").LoadingOverlay("hide", true);
                $("#ch-by-stakeholder").LoadingOverlay("hide", true);
                    
                metric_name = d.metric;
                var ser = [];
                
                for (var i=0; i<d.p1.data.length; i++)
                {  if (ser[i]==undefined) ser[i]={name:d.p1.names[i], data:[]};
                   ser[i].data[0] = 1.0*d.p1.data[i];
                   ser[i].data[1] = 1.0*d.p2.data[i];
                }

                    $('#portfolio').attr('disabled', false);
                    $('#comparison').attr('disabled', false);
                    $('#ch-by-stakeholder').attr('disabled', false);
               
                    drawStackedGradient('ch-social', d);
                    drawStakeHolder('ch-by-stakeholder', d)
                    drawStackedChart('ch-by-company', d);

                    $('.b-print').attr('disabled', false);

                } );
        }
    }
}
    

function metricsAnalysis(d, pf1, pf2)
{  $("#met-analys").LoadingOverlay("show");
    ajx('/pages/sales/MetricsAnalysis',{rows:d, p:pf1, c:pf2},function(r){
        circlesChart('met-analys', {xdata:r.rows, title:'Other analysis and external'});        
        $("#met-analys").LoadingOverlay("hide", true);
    });    
}

function themeExposuresChart(pf1, pf2)
{   if (pf2!=undefined  && pf1!=undefined)
    {  $("#ch-theme-exposures").LoadingOverlay("show");
        pf1*=1;
        pf2*=1;
        //$('#portfolio').attr('disabled', true)
        //$('#comparison').attr('disabled', true)
        ajx('/pages/sales/ComparePortfolio',{pf1:pf1, pf2:pf2},function(d){
            // chart.setData(d.header, d.data1.data, d.data2.data);
            for (var i=0; i<d.data1.data.length; i++)
            {  d.data1.data[i] = 1.0*d.data1.data[i];
               d.data2.data[i] = 1.0*d.data2.data[i];
            }
            var params = {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Theme exposures'
                },
                xAxis: {
                    categories: d.header
                },
                yAxis: {
                    title: {text:'Exposure (positive or negative)'}
                },
                credits: {
                    enabled: false
                },
                series: [{
                    name: d.name1,
                    data: d.data1.data
                }, {
                    name: d.name2,
                    data: d.data2.data
                }]
            };
            // console.log(params);
            $("#ch-theme-exposures").LoadingOverlay("hide", true);
            Highcharts.chart('ch-theme-exposures', params);            
           // $('#portfolio').attr('disabled', false);
           // $('#comparison').attr('disabled', false);
           // $('.b-print').attr('disabled', false);
        } );
    }
}

