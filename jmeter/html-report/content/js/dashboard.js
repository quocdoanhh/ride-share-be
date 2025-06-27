/*
   Licensed to the Apache Software Foundation (ASF) under one or more
   contributor license agreements.  See the NOTICE file distributed with
   this work for additional information regarding copyright ownership.
   The ASF licenses this file to You under the Apache License, Version 2.0
   (the "License"); you may not use this file except in compliance with
   the License.  You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
var showControllersOnly = false;
var seriesFilter = "";
var filtersOnlySampleSeries = true;

/*
 * Add header in statistics table to group metrics by category
 * format
 *
 */
function summaryTableHeader(header) {
    var newRow = header.insertRow(-1);
    newRow.className = "tablesorter-no-sort";
    var cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 1;
    cell.innerHTML = "Requests";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 3;
    cell.innerHTML = "Executions";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 7;
    cell.innerHTML = "Response Times (ms)";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 1;
    cell.innerHTML = "Throughput";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 2;
    cell.innerHTML = "Network (KB/sec)";
    newRow.appendChild(cell);
}

/*
 * Populates the table identified by id parameter with the specified data and
 * format
 *
 */
function createTable(table, info, formatter, defaultSorts, seriesIndex, headerCreator) {
    var tableRef = table[0];

    // Create header and populate it with data.titles array
    var header = tableRef.createTHead();

    // Call callback is available
    if(headerCreator) {
        headerCreator(header);
    }

    var newRow = header.insertRow(-1);
    for (var index = 0; index < info.titles.length; index++) {
        var cell = document.createElement('th');
        cell.innerHTML = info.titles[index];
        newRow.appendChild(cell);
    }

    var tBody;

    // Create overall body if defined
    if(info.overall){
        tBody = document.createElement('tbody');
        tBody.className = "tablesorter-no-sort";
        tableRef.appendChild(tBody);
        var newRow = tBody.insertRow(-1);
        var data = info.overall.data;
        for(var index=0;index < data.length; index++){
            var cell = newRow.insertCell(-1);
            cell.innerHTML = formatter ? formatter(index, data[index]): data[index];
        }
    }

    // Create regular body
    tBody = document.createElement('tbody');
    tableRef.appendChild(tBody);

    var regexp;
    if(seriesFilter) {
        regexp = new RegExp(seriesFilter, 'i');
    }
    // Populate body with data.items array
    for(var index=0; index < info.items.length; index++){
        var item = info.items[index];
        if((!regexp || filtersOnlySampleSeries && !info.supportsControllersDiscrimination || regexp.test(item.data[seriesIndex]))
                &&
                (!showControllersOnly || !info.supportsControllersDiscrimination || item.isController)){
            if(item.data.length > 0) {
                var newRow = tBody.insertRow(-1);
                for(var col=0; col < item.data.length; col++){
                    var cell = newRow.insertCell(-1);
                    cell.innerHTML = formatter ? formatter(col, item.data[col]) : item.data[col];
                }
            }
        }
    }

    // Add support of columns sort
    table.tablesorter({sortList : defaultSorts});
}

$(document).ready(function() {

    // Customize table sorter default options
    $.extend( $.tablesorter.defaults, {
        theme: 'blue',
        cssInfoBlock: "tablesorter-no-sort",
        widthFixed: true,
        widgets: ['zebra']
    });

    var data = {"OkPercent": 92.72727272727273, "KoPercent": 7.2727272727272725};
    var dataset = [
        {
            "label" : "FAIL",
            "data" : data.KoPercent,
            "color" : "#FF6347"
        },
        {
            "label" : "PASS",
            "data" : data.OkPercent,
            "color" : "#9ACD32"
        }];
    $.plot($("#flot-requests-summary"), dataset, {
        series : {
            pie : {
                show : true,
                radius : 1,
                label : {
                    show : true,
                    radius : 3 / 4,
                    formatter : function(label, series) {
                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'
                            + label
                            + '<br/>'
                            + Math.round10(series.percent, -2)
                            + '%</div>';
                    },
                    background : {
                        opacity : 0.5,
                        color : '#000'
                    }
                }
            }
        },
        legend : {
            show : true
        }
    });

    // Creates APDEX table
    createTable($("#apdexTable"), {"supportsControllersDiscrimination": true, "overall": {"data": [0.8636363636363636, 500, 1500, "Total"], "isController": false}, "titles": ["Apdex", "T (Toleration threshold)", "F (Frustration threshold)", "Label"], "items": [{"data": [1.0, 500, 1500, "Create Trip"], "isController": false}, {"data": [1.0, 500, 1500, "Accept Trip"], "isController": false}, {"data": [1.0, 500, 1500, "Location Trip"], "isController": false}, {"data": [1.0, 500, 1500, "Start Trip"], "isController": false}, {"data": [1.0, 500, 1500, "End Trip"], "isController": false}, {"data": [0.9, 500, 1500, "Get Code"], "isController": false}, {"data": [1.0, 500, 1500, "Create Driver"], "isController": false}, {"data": [1.0, 500, 1500, "Get Trip"], "isController": false}, {"data": [0.5, 500, 1500, "Login"], "isController": false}, {"data": [0.1, 500, 1500, "Verify"], "isController": false}, {"data": [1.0, 500, 1500, "Get Driver"], "isController": false}]}, function(index, item){
        switch(index){
            case 0:
                item = item.toFixed(3);
                break;
            case 1:
            case 2:
                item = formatDuration(item);
                break;
        }
        return item;
    }, [[0, 0]], 3);

    // Create statistics table
    createTable($("#statisticsTable"), {"supportsControllersDiscrimination": true, "overall": {"data": ["Total", 55, 4, 7.2727272727272725, 344.8363636363635, 154, 1031, 235.0, 795.1999999999996, 988.9999999999999, 1031.0, 13.590313812700765, 12.016520725228565, 2.6652169044971585], "isController": false}, "titles": ["Label", "#Samples", "FAIL", "Error %", "Average", "Min", "Max", "Median", "90th pct", "95th pct", "99th pct", "Transactions/s", "Received", "Sent"], "items": [{"data": ["Create Trip", 5, 0, 0.0, 180.8, 155, 196, 189.0, 196.0, 196.0, 196.0, 15.625, 15.5487060546875, 3.4332275390625], "isController": false}, {"data": ["Accept Trip", 5, 0, 0.0, 266.0, 226, 301, 270.0, 301.0, 301.0, 301.0, 12.755102040816327, 12.692821269132653, 2.678073182397959], "isController": false}, {"data": ["Location Trip", 5, 0, 0.0, 274.4, 247, 298, 275.0, 298.0, 298.0, 298.0, 11.363636363636363, 11.308149857954545, 2.707741477272727], "isController": false}, {"data": ["Start Trip", 5, 0, 0.0, 188.6, 170, 228, 177.0, 228.0, 228.0, 228.0, 15.479876160990711, 15.404290828173375, 3.235052244582043], "isController": false}, {"data": ["End Trip", 5, 0, 0.0, 184.8, 154, 206, 194.0, 206.0, 206.0, 206.0, 16.949152542372882, 16.866393008474578, 3.509004237288136], "isController": false}, {"data": ["Get Code", 5, 0, 0.0, 469.2, 350, 670, 472.0, 670.0, 670.0, 670.0, 6.082725060827251, 3.807643324209246, 0.8553832116788321], "isController": false}, {"data": ["Create Driver", 5, 0, 0.0, 256.0, 193, 324, 274.0, 324.0, 324.0, 324.0, 11.441647597254004, 11.385780177345538, 2.7151566075514872], "isController": false}, {"data": ["Get Trip", 5, 0, 0.0, 179.6, 155, 197, 176.0, 197.0, 197.0, 197.0, 18.115942028985508, 18.0274852807971, 2.1937273550724634], "isController": false}, {"data": ["Login", 5, 0, 0.0, 998.0, 983, 1031, 984.0, 1031.0, 1031.0, 1031.0, 3.8940809968847354, 2.563096281152648, 0.8480274045950156], "isController": false}, {"data": ["Verify", 5, 4, 80.0, 587.6, 514, 627, 606.0, 627.0, 627.0, 627.0, 6.105006105006105, 2.9368418040293043, 1.442784645909646], "isController": false}, {"data": ["Get Driver", 5, 0, 0.0, 208.2, 179, 245, 205.0, 245.0, 245.0, 245.0, 16.339869281045754, 16.260084763071895, 1.9626991421568627], "isController": false}]}, function(index, item){
        switch(index){
            // Errors pct
            case 3:
                item = item.toFixed(2) + '%';
                break;
            // Mean
            case 4:
            // Mean
            case 7:
            // Median
            case 8:
            // Percentile 1
            case 9:
            // Percentile 2
            case 10:
            // Percentile 3
            case 11:
            // Throughput
            case 12:
            // Kbytes/s
            case 13:
            // Sent Kbytes/s
                item = item.toFixed(2);
                break;
        }
        return item;
    }, [[0, 0]], 0, summaryTableHeader);

    // Create error table
    createTable($("#errorsTable"), {"supportsControllersDiscrimination": false, "titles": ["Type of error", "Number of errors", "% in errors", "% in all samples"], "items": [{"data": ["401/Unauthorized", 4, 100.0, 7.2727272727272725], "isController": false}]}, function(index, item){
        switch(index){
            case 2:
            case 3:
                item = item.toFixed(2) + '%';
                break;
        }
        return item;
    }, [[1, 1]]);

        // Create top5 errors by sampler
    createTable($("#top5ErrorsBySamplerTable"), {"supportsControllersDiscrimination": false, "overall": {"data": ["Total", 55, 4, "401/Unauthorized", 4, "", "", "", "", "", "", "", ""], "isController": false}, "titles": ["Sample", "#Samples", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors"], "items": [{"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": ["Verify", 5, 4, "401/Unauthorized", 4, "", "", "", "", "", "", "", ""], "isController": false}, {"data": [], "isController": false}]}, function(index, item){
        return item;
    }, [[0, 0]], 0);

});
