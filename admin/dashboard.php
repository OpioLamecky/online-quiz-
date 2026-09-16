<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

// Admin Stats
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$totalQuizzes = $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
$totalAttempts = $pdo->query("SELECT COUNT(*) FROM quiz_attempts")->fetchColumn();

// Performance per class
$perfStmt = $pdo->query("
    SELECT u.class_level, ROUND(AVG((qa.score / qa.total_marks) * 100), 1) as avg_percent
    FROM quiz_attempts qa
    JOIN users u ON qa.student_id = u.id
    WHERE qa.status = 'graded' AND u.class_level IS NOT NULL AND qa.total_marks > 0
    GROUP BY u.class_level
    ORDER BY u.class_level
");
$performanceData = $perfStmt->fetchAll();

$classLabels = [];
$classAverages = [];
foreach ($performanceData as $row) {
    $classLabels[] = $row['class_level'];
    $classAverages[] = $row['avg_percent'];
}

require_once '../includes/header.php';
?>
<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row g-4 mb-5 align-items-center">
    <div class="col-lg-8">
        <div class="mb-2">
            <span class="text-primary fw-bold text-uppercase tracking-widest small">Admin Dashboard</span>
        </div>
        <h1 class="display-5 fw-bold mb-2" style="letter-spacing: -0.02em;">Welcome back!</h1>
        <p class="text-muted lead mb-0">Here's what's happening in your system today.</p>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0 d-print-none">
        <button onclick="window.print()" class="btn btn-light fw-bold px-4 py-2.5 rounded-4 shadow-sm border hover-lift transition-all">
            <i class="bi bi-printer me-2 text-primary"></i>Print Report
        </button>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 me-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-mortarboard-fill fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.08em;">Total Students</small>
                        <h2 class="fw-bold mb-0 display-6" style="letter-spacing: -0.02em;"><?= $totalStudents ?></h2>
                    </div>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-4 p-3 me-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-person-workspace fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.08em;">Total Teachers</small>
                        <h2 class="fw-bold mb-0 display-6" style="letter-spacing: -0.02em;"><?= $totalTeachers ?></h2>
                    </div>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-info" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3 me-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-file-earmark-text fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.08em;">Active Quizzes</small>
                        <h2 class="fw-bold mb-0 display-6" style="letter-spacing: -0.02em;"><?= $totalQuizzes ?></h2>
                    </div>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 me-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.08em;">Total Attempts</small>
                        <h2 class="fw-bold mb-0 display-6" style="letter-spacing: -0.02em;"><?= $totalAttempts ?></h2>
                    </div>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-9">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 px-4 py-4 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-1">Performance Analytics</h5>
                    <p class="text-muted small mb-0">Average student performance trends across all class levels</p>
                </div>
                <div class="dropdown d-print-none">
                    <button class="btn btn-light btn-sm rounded-pill px-4 py-2 border" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download me-2"></i>Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-pdf me-2 text-danger"></i>PDF Report</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel me-2 text-success"></i>Excel Sheet</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="chart-container position-relative" style="height: 400px; width: 100%;">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
            <div class="card-footer bg-light bg-opacity-50 border-0 px-4 py-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3" style="width: 40px; height: 40px;">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.08em;">Highest Avg</small>
                                <span class="fw-bold" style="font-size: 1.25rem;"><?= !empty($classAverages) ? max($classAverages) : 0 ?>%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 border-start border-end">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 me-3" style="width: 40px; height: 40px;">
                                <i class="bi bi-calculator"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.08em;">Global Mean</small>
                                <span class="fw-bold" style="font-size: 1.25rem;"><?= !empty($classAverages) ? round(array_sum($classAverages) / count($classAverages), 1) : 0 ?>%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 me-3" style="width: 40px; height: 40px;">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.08em;">Participation</small>
                                <span class="fw-bold" style="font-size: 1.25rem;"><?= $totalAttempts ?> Attempts</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 d-print-none">
            <div class="card-header bg-white border-0 px-4 py-4">
                <h5 class="fw-bold mb-0">Quick Actions</h5>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="d-grid gap-3">
                    <a href="users.php" class="btn btn-light text-start p-4 border shadow-none hover-lift d-flex align-items-center rounded-4 transition-all bg-white">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-2 me-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-bold d-block text-dark">Manage Students</span>
                            <small class="text-muted">View and edit student accounts</small>
                        </div>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="staff.php" class="btn btn-light text-start p-4 border shadow-none hover-lift d-flex align-items-center rounded-4 transition-all bg-white">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-2 me-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-shield-lock-fill fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-bold d-block text-dark">Staff Management</span>
                            <small class="text-muted">Teachers & supervisors</small>
                        </div>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="all_results.php" class="btn btn-light text-start p-4 border shadow-none hover-lift d-flex align-items-center rounded-4 transition-all bg-white">
                        <div class="bg-info bg-opacity-10 text-info rounded-4 p-2 me-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-bar-chart-fill fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-bold d-block text-dark">Global Results</span>
                            <small class="text-muted">Deep performance logs</small>
                        </div>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="settings.php" class="btn btn-light text-start p-4 border shadow-none hover-lift d-flex align-items-center rounded-4 transition-all bg-white">
                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-4 p-2 me-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-gear-fill fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-bold d-block text-dark">System Settings</span>
                            <small class="text-muted">Platform configuration</small>
                        </div>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    // Primary Gradient for the line area
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.15)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

    const data = {
        labels: <?= json_encode($classLabels) ?>,
        datasets: [{
            label: 'Average Score (%)',
            data: <?= json_encode($classAverages) ?>,
            borderColor: '#4f46e5',
            backgroundColor: gradient,
            borderWidth: 4,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#4f46e5',
            pointBorderWidth: 3,
            pointRadius: 8,
            pointHoverRadius: 10,
            pointHoverBackgroundColor: '#4f46e5',
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 4
        }]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0,0,0,0.03)',
                        lineWidth: 1
                    },
                    ticks: {
                        callback: function(value) { return value + '%'; },
                        font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
                        color: '#64748b',
                        padding: 15
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 13, weight: '700' },
                        color: '#1e293b',
                        padding: 15
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 15,
                    cornerRadius: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return ' Performance: ' + context.parsed.y + '%';
                        }
                    }
                }
            }
        }
    };

    new Chart(ctx, config);
});
</script>

<?php require_once '../includes/footer.php'; ?>