function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

var dayCount = window.location.pathname.split('/');
dayCount = dayCount[dayCount.length - 1];
var rows = $('tr.transaction-row').toArray();
var data = [];
var dayData = [];
rows.forEach(function(row) {
    if (!data[$(row).data('group')]){
        data[$(row).data('group')] = null;
    }
    if (!dayData[$(row).data('time')]){
        dayData[$(row).data('time')] = null;
    }
    data[$(row).data('group')] += parseFloat($(row).data('value'));
    dayData[$(row).data('time')] += parseFloat($(row).data('value'));
});

var incomePieData = [];
var expenditurePieData = [];
Object.keys(data).forEach(function(dataEntry){
    if (data[dataEntry] >= 0){
        incomePieData.push(
            {
                'value': data[dataEntry],
                'label': dataEntry,
                'color': getRandomColor()
            }
        );
    } else {
        expenditurePieData.push(
            {
                'value': data[dataEntry],
                'label': dataEntry,
                'color': getRandomColor()
            }
        );
    }
});

var lineGraphData = {
    labels: [],
    datasets: [
        {
            label: 'Balance',
            fillColor: 'rgba(220,220,220,0.2)',
            strokeColor: 'rgba(220,220,220,1)',
            pointColor: 'rgba(220,220,220,1)',
            pointStrokeColor: '#fff',
            pointHighlightFill: '#fff',
            pointHighlightStroke: 'rgba(220,220,220,1)',
            data: []
        }
    ]
};
var PredictionGraphData = {
    labels: [],
    datasets: [
        {
            label: 'Prediction',
            fillColor: 'rgba(255,220,220,0.2)',
            strokeColor: 'rgba(255,220,220,1)',
            pointColor: 'rgba(255,220,220,1)',
            pointStrokeColor: '#fff',
            pointHighlightFill: '#fff',
            pointHighlightStroke: 'rgba(255,220,220,1)',
            data: []
        }
    ]
};
var balance = null;
var totalChanges = 0;
Object.keys(dayData).forEach(function(dataEntry){
    if (!balance) {
        balance = parseFloat($('.balance-row').data('balance'));
    }
    lineGraphData.labels.push(dataEntry);
    lineGraphData.datasets[0].data.push(
        Math.round((balance = balance - dayData[dataEntry]) * 100 ) / 100
    );
    totalChanges += dayData[dataEntry];
});

lineGraphData.datasets[0].data.pop();
lineGraphData.datasets[0].data.unshift(parseFloat($('.balance-row').data('balance')));
lineGraphData.labels.reverse();

var change = totalChanges / dayCount;
var date = new Date();
for (var i = dayCount - 1; i >= 0; i--) {
    balance = parseFloat($('.balance-row').data('balance'));
    date.setDate(date.getDate() + 1);
    var dd = date.getDate();
    if (dd < 10) {
        dd = '0' + dd;
    }
    var mm = date.getMonth() + 1;
    if (mm < 10) {
        mm = '0' + mm;
    }
    var y = date.getFullYear();
    var formattedDate = y + '-'+ mm + '-'+ dd;

    PredictionGraphData.labels.push(formattedDate);
    PredictionGraphData.datasets[0].data.push(
        Math.round((balance + (change * (i + 1))) * 100) / 100
    );
}
lineGraphData.datasets[0].data.reverse();
PredictionGraphData.datasets[0].data.reverse();

var maximum = Math.max.apply(0, lineGraphData.datasets[0].data);
if (maximum < Math.max.apply(0, PredictionGraphData.datasets[0].data)){
    maximum = Math.max.apply(0, PredictionGraphData.datasets[0].data);
}
var minimum = Math.min.apply(0, lineGraphData.datasets[0].data);
if (minimum > Math.min.apply(0, PredictionGraphData.datasets[0].data)){
    minimum = Math.min.apply(0, PredictionGraphData.datasets[0].data);
}

window.onload = function(){
    Chart.defaults.global.scaleLabel = " ";
    Chart.defaults.global.scaleOverride = true;
    Chart.defaults.global.scaleSteps = 5;
    Chart.defaults.global.scaleStepWidth = Math.abs(Math.floor((minimum - maximum) / 3)) + 10;
    Chart.defaults.global.scaleStartValue = Math.floor(minimum) - 50;
    var incomeCanvas = document.getElementById("incomeChart").getContext("2d");
    var expenditureCanvas = document.getElementById("expenditureChart").getContext("2d");
    var balanceCanvas = document.getElementById("balanceChart").getContext("2d");
    var predictionCanvas = document.getElementById("predictionChart").getContext("2d");
    var incPie = new Chart(incomeCanvas).Pie(incomePieData);
    var expPie = new Chart(expenditureCanvas).Pie(expenditurePieData);
    var balanceLine = new Chart(balanceCanvas).Line(lineGraphData, {
        pointHitDetectionRadius: 1
    });
    var predictionLine = new Chart(predictionCanvas).Line(PredictionGraphData, {
        pointHitDetectionRadius: 1
    });
};
