<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Log Explorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Log Explorer</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" id="theme-toggle" title="Toggle Dark Mode">
                            <i class="bi bi-moon-fill"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="api/auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="card-modern">
            <h4 class="mb-4">Dashboard</h4>
            <div id="main" style="width: 100%; height:400px;"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Dark mode initialization
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = themeToggle.querySelector('i');
            const savedTheme = localStorage.getItem('theme') || 'light';
            
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
                themeIcon.classList.remove('bi-moon-fill');
                themeIcon.classList.add('bi-sun-fill');
            }

            themeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                const isDark = document.body.classList.contains('dark-mode');
                
                if (isDark) {
                    themeIcon.classList.remove('bi-moon-fill');
                    themeIcon.classList.add('bi-sun-fill');
                    localStorage.setItem('theme', 'dark');
                } else {
                    themeIcon.classList.remove('bi-sun-fill');
                    themeIcon.classList.add('bi-moon-fill');
                    localStorage.setItem('theme', 'light');
                }
                
                // Update chart theme
                updateChartTheme();
            });

            // Initialize chart
            var myChart = echarts.init(document.getElementById('main'));

            function getChartOption() {
                const isDark = document.body.classList.contains('dark-mode');
                return {
                    title: {
                        text: 'Log Count per 5 Minutes',
                        textStyle: {
                            color: isDark ? '#f8fafc' : '#0f172a'
                        }
                    },
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: isDark ? '#1e293b' : '#ffffff',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        textStyle: {
                            color: isDark ? '#f8fafc' : '#0f172a'
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: [],
                        axisLine: {
                            lineStyle: {
                                color: isDark ? '#475569' : '#cbd5e1'
                            }
                        },
                        axisLabel: {
                            color: isDark ? '#94a3b8' : '#64748b'
                        }
                    },
                    yAxis: {
                        type: 'value',
                        axisLine: {
                            lineStyle: {
                                color: isDark ? '#475569' : '#cbd5e1'
                            }
                        },
                        axisLabel: {
                            color: isDark ? '#94a3b8' : '#64748b'
                        },
                        splitLine: {
                            lineStyle: {
                                color: isDark ? '#334155' : '#e2e8f0'
                            }
                        }
                    },
                    series: [{
                        data: [],
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            color: {
                                type: 'linear',
                                x: 0,
                                y: 0,
                                x2: 0,
                                y2: 1,
                                colorStops: [{
                                    offset: 0,
                                    color: isDark ? 'rgba(79, 70, 229, 0.4)' : 'rgba(79, 70, 229, 0.2)'
                                }, {
                                    offset: 1,
                                    color: isDark ? 'rgba(79, 70, 229, 0.05)' : 'rgba(79, 70, 229, 0.05)'
                                }]
                            }
                        },
                        lineStyle: {
                            color: '#4f46e5',
                            width: 2
                        },
                        itemStyle: {
                            color: '#4f46e5'
                        }
                    }]
                };
            }

            myChart.setOption(getChartOption());

            function updateChartTheme() {
                myChart.setOption(getChartOption());
            }

            function fetchData() {
                $.ajax({
                    url: 'api/stats.php',
                    dataType: 'json',
                    success: function(data) {
                        myChart.setOption({
                            xAxis: {
                                data: data.labels
                            },
                            series: [{
                                data: data.values
                            }]
                        });
                    }
                });
            }

            // Initial fetch
            fetchData();

            // Auto update every 5 seconds
            setInterval(fetchData, 5000);

            // Handle window resize
            window.addEventListener('resize', function() {
                myChart.resize();
            });
        });
    </script>
</body>
</html>
