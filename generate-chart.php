<?php
// Load the data from a CSV file
$filePath = 'out2.csv'; // Replace with your actual CSV file path
$outputPath = 'report.html'; // The static HTML file to be generated

if (!file_exists($filePath)) {
    die("File not found.");
}

$data = [];
$headers = [];

// Read the CSV file and parse its contents
if (($handle = fopen($filePath, "r")) !== false) {
    // Get headers from the first row
    $headers = fgetcsv($handle, 1000, ",", '"', '\\');

    if ($headers === false) {
        die("Failed to read CSV headers.");
    }

    // Standardize headers
    $headers = array_map('trim', $headers);
    $headers = array_map('strtolower', $headers); // Convert to lowercase
    $headers = array_map(function ($header) {
        return preg_replace('/[^a-z0-9_]+/', '_', $header); // Replace non-alphanumeric with underscores
    }, $headers);

    // Read each row and combine it with headers
    while (($row = fgetcsv($handle, 1000, ",", '"', '\\')) !== false) {
        $data[] = array_combine($headers, $row);
    }
    fclose($handle);
}

// Ensure the columns we need exist
$requiredColumns = ['benchmark_name', 'subject_name', 'result_time_avg', 'subject_time_unit'];
foreach ($requiredColumns as $column) {
    if (!in_array($column, $headers)) {
        die("Required column '{$column}' is missing in the CSV file.");
    }
}

// Extract the time unit (assuming it's consistent across all rows)
$timeUnit = $data[0]['subject_time_unit'];

// Group and average data by benchmark_name and subject_name
$groupedData = [];
$subjectNames = [];
$benchmarkAverages = [];

foreach ($data as $row) {
    $benchmarkName = $row['benchmark_name'];
    $subjectName = $row['subject_name'];
    $resultTimeAvg = (float)$row['result_time_avg'];

    // Collect unique subject names for the x-axis
    if (!in_array($subjectName, $subjectNames)) {
        $subjectNames[] = $subjectName;
    }

    // Initialize the average calculation structure if not present
    if (!isset($benchmarkAverages[$benchmarkName])) {
        $benchmarkAverages[$benchmarkName] = [];
    }
    if (!isset($benchmarkAverages[$benchmarkName][$subjectName])) {
        $benchmarkAverages[$benchmarkName][$subjectName] = ['sum' => 0, 'count' => 0];
    }

    // Accumulate values for averaging
    $benchmarkAverages[$benchmarkName][$subjectName]['sum'] += $resultTimeAvg;
    $benchmarkAverages[$benchmarkName][$subjectName]['count']++;
}

// Calculate averages
foreach ($benchmarkAverages as $benchmarkName => $subjects) {
    foreach ($subjects as $subjectName => $values) {
        $groupedData[$benchmarkName][$subjectName] = $values['sum'] / $values['count'];
    }
}

// Prepare datasets for the chart
$datasets = [];
$colors = ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(153, 102, 255, 1)'];

$colors = ['rgb(255, 181, 95)','rgb(9, 187, 159)','rgb(176, 65, 34)','rgb(21, 96, 122)'];
$backgroundColors = ['rgb(255, 181, 95)','rgb(9, 187, 159)','rgb(176, 65, 34)','rgb(21, 96, 122)'];
$colorIndex = 0;

foreach ($groupedData as $benchmarkName => $results) {
    $dataPoints = [];
    foreach ($subjectNames as $subjectName) {
        $dataPoints[] = $results[$subjectName] ?? null; // Add null if no data for a subject
    }

    $datasets[] = [
        'label' => $benchmarkName,
        'data' => $dataPoints,
        'borderColor' => $colors[$colorIndex % count($colors)],
        'backgroundColor' => $backgroundColors[$colorIndex % count($backgroundColors)],
        'borderWidth' => 5,
        'pointStyle' => 'circle', // Use circles for data points
        'pointRadius' => 6,
    ];

    $colorIndex++;
}

// Prepare data for the chart as JSON
$subjectNamesJson = json_encode($subjectNames);
$datasetsJson = json_encode($datasets);

// Generate the HTML content
$htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Performance Report</h1>
    <p>Time Unit: $timeUnit</p>
    <canvas id="resultTimeChart" width="800" height="400"></canvas>
    <script>
        const ctx = document.getElementById('resultTimeChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line', // Line chart
            data: {
                labels: $subjectNamesJson,
                datasets: $datasetsJson
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Subject Name'
                        },
                        grid: {
                            display: false // Remove vertical gridlines
                        },
                        offset: true
                    },
                    y: {
                        type: 'logarithmic',
                        title: {
                            display: true,
                            text: 'Average Result Time ($timeUnit)'
                        },
                        beginAtZero: false,
                        grid: {
                            display: true // Keep horizontal gridlines
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
HTML;

// Write the HTML content to a file
if (file_put_contents($outputPath, $htmlContent)) {
    echo "Static HTML report generated successfully: $outputPath";
} else {
    echo "Failed to generate the report.";
}
?>
