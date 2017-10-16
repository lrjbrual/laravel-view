<link href="{{asset('assets/css/pages/flot_charts.css')}}" rel="stylesheet" type="text/css">

<div class="col-md-12 m-t-25">
  <div class="card col-md-12">
      <div class="card-block ">
          <div class="table-responsive">
              <div class="dataTables_processing2 pnl_graph_loading" style="display:block"> <b>Loading graph. . .</b></div>
              <div style="min-width: 900px;">
                  <div id="analyticschart" class="flotLegend"></div>
                  <div id="line-chart" class="flotChart1"></div>
              </div>
          </div>
      </div>
  </div>
</div>

<script type="text/javascript" src="{{asset('assets/vendors/flotchart/js/jquery.flot.js')}}" ></script>
<script type="text/javascript" src="{{asset('assets/vendors/flotchart/js/jquery.flot.resize.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/flotchart/js/jquery.flot.stack.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/flotchart/js/jquery.flot.time.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/flotspline/js/jquery.flot.spline.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/flotchart/js/jquery.flot.categories.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/flotchart/js/jquery.flot.pie.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/flot.tooltip/js/jquery.flot.tooltip.min.js')}}"></script>

<script type="text/javascript">
var plot;
function init_graph(profit, rev){
    var d1, d2, data, Options;
    var df, dt;
    var month_of_range = [];
    var filter = $('.filter-select').val();

    // d1 for rev/profit
    d1 = [];

    // d2 for revenue
    d2 = [];
    labal_profit = [];
    labal_rev = [];
    var i=0;
    
    if (filter) {
        for (var index_filter = 0; index_filter < filter.length; index_filter++) {
            switch(filter[index_filter]) {
                case 'profit':
                    i=0;
                    $.each(profit, function( index, value ) {
                        index = (new Date(index)).getTime();
                        d2[i] = [index, value];
                        d1[i] = [index, value];
                        i++;
                    });
                    labal_profit = [{
                        label: "Profit",
                        data: d1,
                        color: "#0fb0c0"
                    }];
                    data = labal_profit.concat(labal_rev);
                    break;
                case 'revenue':
                    i=0;
                    $.each(rev, function( index, value ) {
                        index = (new Date(index)).getTime();
                        d2[i] = [index, value];
                        i++;
                    });
                    labal_rev = [{
                        label: "Revenue",
                        data: d2,
                        color: "#ff9933"
                    }];
                    data = labal_profit.concat(labal_rev);
                    break;
            }
        }
    }else{
        $.each(rev, function( index, value ) {
        index = (new Date(index)).getTime();
        d2[i] = [index, value];
        i++;
        });

        j=0;
        $.each(profit, function( index, value ) {

            index = (new Date(index)).getTime();
            d1[j] = [index, value];
            j++;
        });

        data = [{
            label: "Profit",
            data: d1,
            color: "#0fb0c0"
        }, {
            label: "Revenue",
            data: d2,
            color: "#ff9933"
        }];

        
    }
    

    //For dayName in Options
    for (var d = new Date(d2[0][0]); d <= new Date(d2[i-1][0]); d.setDate(d.getDate() + 1)) {
        var m = new Date(d);
        month_of_range.push(m.getDay() + 1);
    }

    Options = {
        xaxis: {
            min: d2[0][0],
            max: d2[i-1][0],
            mode: "time",
            tickSize: [1, "day"],
            dayNames: month_of_range,
            tickLength: 0
        },
        yaxis: {

        },
        series: {
            lines: {
                show: true,
                fill: false,
                lineWidth: 2
            },
            points: {
                show: true,
                radius: 4.5,
                fill: true,
                fillColor: "#ffffff",
                lineWidth: 2
            }
        },
        grid: {
            hoverable: true,
            clickable: false,
            borderWidth: 0
        },
        legend: {
            container: '#analyticschart',
            show: true
        },

        tooltip: true,
        tooltipOpts: {
            content: '%s: %y'
        }

    };


    var holder = $('#line-chart');

    if (holder.length) {
        plot = $.plot(holder, data, Options );
    }

}
$(document).ready(function () {
    //line chart start


    var d1, d2, data, Options;

    d1 = [ ];
    d2 = [ ];

    data = [{
        label: "Profit",
        data: d1,
        color: "#0fb0c0"
    }, {
        label: "Revenue",
        data: d2,
        color: "#ff9933"
    }];

    Options = {
        xaxis: {
            min: (new Date(2009, 12, 1)).getTime(),
            max: (new Date(2010, 11, 2)).getTime(),
            mode: "time",
            tickSize: [1, "month"],
            monthNames: ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"],
            tickLength: 0
        },
        yaxis: {

        },
        series: {
            lines: {
                show: true,
                fill: false,
                lineWidth: 2
            },
            points: {
                show: true,
                radius: 4.5,
                fill: true,
                fillColor: "#ffffff",
                lineWidth: 2
            }
        },
        grid: {
            hoverable: true,
            clickable: false,
            borderWidth: 0
        },
        legend: {
            container: '#analyticschart',
            show: true
        },

        tooltip: true,
        tooltipOpts: {
            content: '%s: %y'
        }

    };

    var holder = $('#line-chart');
    var plot;

    if (holder.length) {
        plot = $.plot(holder, data, Options );
    }
    //line chart end
});
</script>
