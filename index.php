<?php
/**
 * Dashboard — Hospital IT Helpdesk System
 */
$page_title = 'Dashboard';
$current_page = 'index';
require_once __DIR__ . '/includes/header.php';

$stats = get_dashboard_stats();
$db = get_db();

// Recent tickets (latest 5)
$stmt = $db->query(
    "SELECT t.ticket_id, t.subject, t.priority, t.status, t.created_at,
            c.name AS category,
            CONCAT(s.first_name, ' ', s.last_name) AS staff_name
     FROM tickets t
       JOIN categories c ON t.category_id = c.category_id
       JOIN staff s     ON t.staff_id = s.staff_id
     ORDER BY t.created_at DESC
     LIMIT 5"
);
$recent_tickets = $stmt->fetchAll();

// Overdue tickets (top 3)
$stmt = $db->query(
    "SELECT t.ticket_id, t.subject, t.priority, t.created_at,
            CONCAT(sp.first_name, ' ', sp.last_name) AS assigned_name,
            TIMESTAMPDIFF(HOUR, t.created_at, NOW()) AS hours_open
     FROM tickets t
       JOIN staff sp ON t.assigned_to = sp.staff_id
     WHERE t.status IN ('Open', 'In Progress')
       AND t.created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)
     ORDER BY hours_open DESC
     LIMIT 3"
);
$overdue_tickets = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
        <div class="subtitle">Overview of IT support tickets across the hospital</div>
    </div>
    <a href="submit_ticket.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Submit New Ticket
    </a>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg">
        <div class="card stat-card stat-total h-100">
            <div class="card-body">
                <i class="bi bi-clipboard-data stat-icon text-primary"></i>
                <div class="stat-value text-primary"><?= $stats['total'] ?></div>
                <div class="stat-label text-muted">Total Tickets</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card stat-card stat-open h-100">
            <div class="card-body">
                <i class="bi bi-exclamation-circle stat-icon text-danger"></i>
                <div class="stat-value text-danger"><?= $stats['open'] ?></div>
                <div class="stat-label text-muted">Open</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card stat-card stat-progress h-100">
            <div class="card-body">
                <i class="bi bi-arrow-repeat stat-icon text-warning"></i>
                <div class="stat-value text-warning"><?= $stats['in_progress'] ?></div>
                <div class="stat-label text-muted">In Progress</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card stat-card stat-resolved h-100">
            <div class="card-body">
                <i class="bi bi-check-circle stat-icon text-success"></i>
                <div class="stat-value text-success"><?= $stats['resolved'] ?></div>
                <div class="stat-label text-muted">Resolved</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg">
        <div class="card stat-card stat-overdue h-100">
            <div class="card-body">
                <i class="bi bi-clock-history stat-icon" style="color:#9b59b6"></i>
                <div class="stat-value" style="color:#9b59b6"><?= $stats['overdue'] ?></div>
                <div class="stat-label text-muted">Overdue (&gt;48h)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent tickets -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history text-primary"></i> Recent Tickets</h5>
                <a href="view_tickets.php" class="btn btn-sm btn-outline-primary">View All Open</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Category</th>
                                <th>Raised By</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_tickets as $t): ?>
                            <tr>
                                <td><a href="ticket_detail.php?id=<?= (int)$t['ticket_id'] ?>" class="text-decoration-none fw-bold">#<?= (int)$t['ticket_id'] ?></a></td>
                                <td>
                                    <a href="ticket_detail.php?id=<?= (int)$t['ticket_id'] ?>" class="text-decoration-none">
                                        <?= e($t['subject']) ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($t['category']) ?></span></td>
                                <td class="small"><?= e($t['staff_name']) ?></td>
                                <td><span class="badge <?= priority_badge_class($t['priority']) ?>"><?= e($t['priority']) ?></span></td>
                                <td><span class="badge <?= status_badge_class($t['status']) ?>"><?= e($t['status']) ?></span></td>
                                <td class="small text-muted"><?= time_ago($t['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue tickets -->
    <div class="col-lg-4">
        <div class="card h-100 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Overdue Tickets</h5>
            </div>
            <div class="card-body">
                <?php if (empty($overdue_tickets)): ?>
                    <p class="text-muted text-center py-3 mb-0">
                        <i class="bi bi-check-circle text-success" style="font-size:2rem;"></i><br>
                        No overdue tickets.
                    </p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($overdue_tickets as $t): ?>
                        <li class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <a href="ticket_detail.php?id=<?= (int)$t['ticket_id'] ?>" class="text-decoration-none fw-semibold">
                                        <?= e($t['subject']) ?>
                                    </a>
                                    <div class="small text-muted mt-1">
                                        Assigned to <?= e($t['assigned_name']) ?>
                                    </div>
                                </div>
                                <span class="badge bg-danger ms-2"><?= (int)$t['hours_open'] ?>h</span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="text-end mt-2">
                        <a href="reports.php" class="btn btn-sm btn-outline-danger">View Reports</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
