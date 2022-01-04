// Chart.js scripts
// -- Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#292b2c';
// -- Area Chart Example
var ctx = document.getElementById("myAreaChart");
var myLineChart = new Chart(ctx, 
		{  
	   "type":"bar",
	   "data":{  
	      "labels":[  
	         "January",
	         "February",
	         "March",
	         "April",
	         "May",
	         "June",
	         "July"
	      ],
	      "datasets":[  
	         {  
	            "label":"",
	            "data":[  
	               65,
	               59,
	               80,
	               81,
	               56,
	               55,
	               40
	            ],
	            "fill":false,
	            "backgroundColor":[  
	               "rgba(255, 99, 132, 0.2)",
	               "rgba(255, 159, 64, 0.2)",
	               "rgba(255, 205, 86, 0.2)",
	               "rgba(75, 192, 192, 0.2)",
	               "rgba(54, 162, 235, 0.2)",
	               "rgba(153, 102, 255, 0.2)",
	               "rgba(201, 203, 207, 0.2)"
	            ],
	            "borderColor":[  
	               "rgb(255, 99, 132)",
	               "rgb(255, 159, 64)",
	               "rgb(255, 205, 86)",
	               "rgb(75, 192, 192)",
	               "rgb(54, 162, 235)",
	               "rgb(153, 102, 255)",
	               "rgb(201, 203, 207)"
	            ],
	            "borderWidth":1
	         }
	      ]
	   },
  options: {
	  scales:{"yAxes":[{"ticks":{"beginAtZero":true}}]},
	  legend: {
	      display: false
	    }
  }
});
// -- Bar Chart Example
var ctx = document.getElementById("myBarChart");
var myLineChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ["January", "February", "March", "April", "May", "June"],
    datasets: [{
      label: "Revenue",
      backgroundColor: "rgba(2,117,216,1)",
      borderColor: "rgba(2,117,216,1)",
      data: [4215, 5312, 6251, 7841, 9821, 14984],
    }],
  },
  options: {
    scales: {
      xAxes: [{
        time: {
          unit: 'month'
        },
        gridLines: {
          display: false
        },
        ticks: {
          maxTicksLimit: 6
        }
      }],
      yAxes: [{
        ticks: {
          min: 0,
          max: 15000,
          maxTicksLimit: 5
        },
        gridLines: {
          display: true
        }
      }],
    },
    legend: {
      display: false
    }
  }
});
// -- Pie Chart Example
var ctx = document.getElementById("myPieChart");
var myPieChart = new Chart(ctx, {
  type: 'pie',
  data: {
    labels: ["Blue", "Red", "Yellow", "Green"],
    datasets: [{
      data: [12.21, 15.58, 11.25, 8.32],
      backgroundColor: ['#007bff', '#dc3545', '#ffc107', '#28a745'],
    }],
  },
});
