jQuery(document).ready(function ($) {
    am5.ready(function () {
        const root = am5.Root.new("amchartAPImonth");

        root.setThemes([am5themes_Animated.new(root)]);

        const chart = root.container.children.push(
            am5xy.XYChart.new(root, {
                panX: true,
                panY: false,
                wheelX: "panX",
                wheelY: "zoomX",
                layout: root.verticalLayout
            })
        );

        const xAxis = chart.xAxes.push(
            am5xy.CategoryAxis.new(root, {
                categoryField: "days",
                renderer: am5xy.AxisRendererX.new(root, {}),
                tooltip: am5.Tooltip.new(root, {})
            })
        );

        const yAxis = chart.yAxes.push(
            am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {}),
                min: 0,
                strictMinMax: false,
                extraMax: 0.1
            })
        );

        xAxis.get("renderer").grid.template.setAll({
            stroke: am5.color(0xcccccc),
            strokeOpacity: 0.2
        });
        yAxis.get("renderer").grid.template.setAll({
            stroke: am5.color(0xcccccc),
            strokeOpacity: 0.2
        });

        const seriesDefinitions = [
            { name: "Total", field: "total", color: 0xE2E2E2 },
            { name: "Proxy", field: "proxies", color: 0x628FDF },
            { name: "VPN", field: "vpns", color: 0x85DFC3 },
            { name: "Compromised", field: "compromised", color: 0xDF6262 },
            { name: "Scraper", field: "scraper", color: 0xE88FDD },
            { name: "Hosting Provider", field: "hosting", color: 0x92400E },
            { name: "Tor", field: "tor", color: 0xA45EB5 },
            { name: "Disposable Emails", field: "disposable emails", color: 0xF3E741 },
            { name: "Reusable Emails", field: "reusable emails", color: 0xE6AA39 },
            { name: "Refused Queries", field: "refused queries", color: 0xBA3023 },
            { name: "Custom Rules", field: "custom rules", color: 0xAA34DC },
            { name: "Blacklisted", field: "blacklisted", color: 0x4A4A4A },
        ];

        const seriesMap = {}; // Keyed by field name

        function pvb_fetch_apigraph_data(callback) {
            if (document.hidden) return;

            $.ajax({
                url: pvb_fetch_apigraph.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'pvb_fetch_apigraph',
                    nonce: pvb_fetch_apigraph.nonce
                },
                success: function (response) {
                    if (response.success) {
                        callback(response.data);
                    } else {
                        console.error("Chart data fetch failed");
                    }
                },
                error: function (xhr) {
                    console.error("AJAX error:", xhr.status, xhr.responseText);
                }
            });
        }

        function buildChart(data) {
            document.getElementById("amchart-loading").style.display = "none";

            data.forEach(item => {
                seriesDefinitions.forEach(def => {
                    item[def.field] = Number(item[def.field] || 0);
                });
            });

            xAxis.data.setAll(data);

            const allSeries = [];

            seriesDefinitions.forEach(def => {
                const color = am5.color(def.color);

                const series = am5xy.SmoothedXLineSeries.new(root, {
                    name: def.name,
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: def.field,
                    categoryXField: "days",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: `{name}: {valueY}`
                    })
                });

                if (def.field === "total") {
                    series.strokes.template.setAll({
                        stroke: color,
                        strokeWidth: 2,
                        strokeDasharray: [6, 4] // Dashed line
                    });
                } else {
                    series.strokes.template.setAll({
                        stroke: color,
                        strokeWidth: 2
                    });
                }

                series.bullets.push(() => {
                    return am5.Bullet.new(root, {
                        sprite: am5.Circle.new(root, {
                            radius: 0,
                            fill: color,
                            stroke: color
                        })
                    });
                });

                series.adapters.add("stroke", () => color);
                series.adapters.add("fill", () => color);
                
                
                

                series.data.setAll(data);
                chart.series.push(series);
                seriesMap[def.field] = series;
                allSeries.push(series);
            });

            const legend = chart.children.push(
                am5.Legend.new(root, {
                    centerX: am5.p50,
                    x: am5.p50
                })
            );
            legend.data.setAll(allSeries);

            chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "zoomX",
                xAxis: xAxis
            }));
        }

        function updateChart(data) {
            data.forEach(item => {
                seriesDefinitions.forEach(def => {
                    item[def.field] = Number(item[def.field] || 0);
                });
            });

            xAxis.data.setAll(data);

            for (const def of seriesDefinitions) {
                const series = seriesMap[def.field];
                if (series) {
                    series.data.setAll(data);
                }
            }
        }

        // First render
        pvb_fetch_apigraph_data(buildChart);

        // Refresh hook
        function refreshAmchartData() {
            if (document.hidden) return;
            pvb_fetch_apigraph_data(updateChart);
        }

        // Start the timer for the first time.
        document.addEventListener("pvbUnifiedTick", function () {
            if (document.hidden) return;
            refreshAmchartData();
        });
    });
});