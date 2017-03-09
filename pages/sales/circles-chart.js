function circlesChart(div,d)
{   
    var options = {
        chart: {
            type: 'bar'
        },
        title: {
            text: d.title
        },
        xAxis: {
            categories: d.categories,
            title: {
                text: null
            },
            plotLines: [{
                color: '#D0D0D0',
                width: 1,
                value: 0},
                {
                color: '#D0D0D0',
                width: 1,
                value: 1},
                {
                color: '#D0D0D0',
                width: 1,
                value: 2},
                {
                color: '#D0D0D0',
                width: 1,
                value: 3}]
        },
        yAxis: {
            min: 0,
            max: 300,
            title: {
                text: d.xtitle,
                align: 'high'
            },
            labels: {
                overflow: 'justify'
            }
        },
        tooltip: {
            valueSuffix: ' millions'
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: false
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -40,
            y: 80,
            floating: true,
            borderWidth: 1,
            backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
            shadow: true
        },
        credits: {
            enabled: false
        },
        series: [{
            name: 'Best',
            data: [0,0,0,0]
        }, {
            name: 'Worst',
            data: [0,0,0,0]
        }]
        };
    options.xdata = d.series;
    var xmax = -1000;
    for (var i=0; i<d.series.length; i++)
    {  for (var j=0; j<d.series[i].data.length; j++)
       { var n = d.series[i].data[j];
         if (xmax<n) xmax=n;
         d.series[i].data[j]=0;  
       }
    }
    options.yAxis.max = xmax;
    Highcharts.chart(div, options);
}


// example of use
circlesChart('container', {
        title: 'Test title',
        xtitle: 'Sales',
        categories: ['Metric1', 'Metric2', 'Metric3', 'Metric4'],
        series:[
            {name:'Best', data:[10,8,7,16]}, 
            {name:'Worst', data:[3,2,1,5]} 
        ]
    });
    
    
