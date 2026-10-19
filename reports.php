<?php
/**
 * Reports — Hospital IT Helpdesk System
 * Runs the three required SQL queries and displays results.
 */
$page_title = 'Reports';
$current_page = 'reports';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// =====================================================
// Report A: Open tickets older than 48 hours, by support person
// =====================================================
$sqlA = "SELECT
            sp.first_name  AS support_first,
            sp.last_name   AS support_last,
            t.ticket_id,
            t.subject,
            t.priority,
            t.status,
            c.name         AS category,
            t.created_at,
            TIMESTAMPDIFF(HOUR, t.created_at, NOW()) AS hours_open
         FROM tickets t
           JOIN categories c ON t.category_id = c.category_id
           JOIN staff     sp ON t.assigned_to = sp.staff_id
         WHERE t.status IN ('Open', 'In Progress')
           AND t.created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)
         ORDER BY sp.last_name, sp.first_name, t.created_at ASC";
$ticketsA = $db->query($sqlA)->fetchAll();

// =====================================================
// Report B: Average resolution time per category (last 30 days)
// =====================================================
$sqlB = "SELECT
            c.name AS category,
            COUNT(t.ticket_id) AS tickets_resolved,
            ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)) / 60, 2) AS avg_resolution_hours,
            ROUND(MIN(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)) / 60, 2) AS fastest_hours,
            ROUND(MAX(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)) / 60, 2) AS slowest_hours
         FROM tickets t
           JOIN categories c ON t.category_id = c.category_id
         WHERE t.status IN ('Resolved', 'Closed')
           AND t.resolved_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY c.category_id, c.name
         ORDER BY avg_resolution_hours DESC";
$ticketsB = $db->query($sqlB)->fetchAll();

// =====================================================
// Report C: Staff with 3+ tickets in same category within 7 days
// =====================================================
$sqlC = "SELECT
            s.first_name,
            s.last_name,
            s.department,
            c.name AS category,
            COUNT(t.ticket_id) AS ticket_count,
            MIN(t.created_at) AS first_ticket_date,
            MAX(t.created_at) AS last_ticket_date,
            DATEDIFF(MAX(t.created_at), MIN(t.created_at)) AS days_span
         FROM tickets t
           JOIN staff      s ON t.staff_id = s.staff_id
           JOIN categories c ON t.category_id = c.category_id
         GROUP BY s.staff_id, s.first_name, s.last_name, s.department, c.category_id, c.name
         HAVING COUNT(t.ticket_id) >= 3
            AND DATEDIFF(MAX(t.created_at), MIN(t.created_at)) <= 7
         ORDER BY ticket_count DESC, s.last_name";
$ticketsC = $db->query($sqlC)->fetchAll();
?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-bar-chart-line"></i> Reports</h1>
        <div class="subtitle">Overdue tickets, resolution times, and recurring problems</div>
    </div>
</div>

<!-- ===================================================== -->
<!-- Report A -->
<!-- ===================================================== -->
<div class="card report-card shadow-sm">
    <div class="card-header">
        <i class="bi bi-clock-history report-icon"></i> Report A: Overdue Tickets (Older Than 48 Hours)
        <span class="report-desc">All open or in-progress tickets older than 48 hours, organised by assigned support person</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ticketsA)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-check-circle text-success" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">No overdue tickets. All open tickets are within the 48-hour threshold.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Support Person</th>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Hours Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $currentPerson = '';
                        foreach ($ticketsA as $t):
                            $fullName = $t['support_first'] . ' ' . $t['support_last'];
                            $isNewGroup = $fullName !== $currentPerson;
                            $currentPerson = $fullName;
                        ?>
                            <tr class="<?= $t['hours_open'] >= 72 ? 'row-overdue' : '' ?>">
                                <td>
                                    <?php if ($isNewGroup): ?>
                                        <strong><?= e($fullName) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td><a href="ticket_detail.php?id=<?= (int)$t['ticket_id'] ?>" class="fw-bold text-decoration-none">#<?= (int)$t['ticket_id'] ?></a></td>
                                <td>
                                    <a href="ticket_detail.php?id=<?= (int)$t['ticket_id'] ?>" class="text-decoration-none">
                                        <?= e($t['subject']) ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($t['category']) ?></span></td>
                                <td><span class="badge <?= priority_badge_class($t['priority']) ?>"><?= e($t['priority']) ?></span></td>
                                <td><span class="badge <?= status_badge_class($t['status']) ?>"><?= e($t['status']) ?></span></td>
                                <td class="small"><?= format_date($t['created_at']) ?></td>
                                <td class="fw-bold <?= $t['hours_open'] >= 72 ? 'text-danger' : 'text-warning' ?>"><?= (int)$t['hours_open'] ?>h</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===================================================== -->
<!-- Report B -->
<!-- ===================================================== -->
<div class="card report-card shadow-sm">
    <div class="card-header">
        <i class="bi bi-graph-up report-icon"></i> Report B: Average Resolution Time per Category (Last 30 Days)
        <span class="report-desc">Average time taken to resolve tickets, grouped by category, over the past 30 days</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ticketsB)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">No tickets resolved in the last 30 days.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-center">Tickets Resolved</th>
                            <th class="text-center">Avg Resolution (hours)</th>
                            <th class="text-center">Fastest (hours)</th>
                            <th class="text-center">Slowest (hours)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ticketsB as $r): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark border"><?= e($r['category']) ?></span></td>
                                <td class="text-center fw-semibold"><?= (int)$r['tickets_resolved'] ?></td>
                                <td class="text-center">
                                    <span class="badge <?= ($r['avg_resolution_hours'] ?? 0) > 24 ? 'bg-danger' : 'bg-success' ?>">
                                        <?= e($r['avg_resolution_hours'] ?? '—') ?>h
                                    </span>
                                </td>
                                <td class="text-center text-success small"><?= e($r['fastest_hours'] ?? '—') ?>h</td>
                                <td class="text-center text-danger small"><?= e($r['slowest_hours'] ?? '—') ?>h</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===================================================== -->
<!-- Report C -->
<!-- ===================================================== -->
<div class="card report-card shadow-sm">
    <div class="card-header">
        <i class="bi bi-exclamation-triangle report-icon"></i> Report C: Recurring Problems (3+ Tickets in Same Category Within 7 Days)
        <span class="report-desc">Staff who raised three or more tickets in the same category within a seven-day period</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ticketsC)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-check-circle text-success" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">No recurring problems detected. No staff member raised 3+ tickets in the same category within 7 days.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th>Department</th>
                            <th>Category</th>
                            <th class="text-center">Ticket Count</th>
                            <th>First Ticket</th>
                            <th>Last Ticket</th>
                            <th class="text-center">Days Span</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ticketsC as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                <td><span class="badge bg-primary"><?= e($r['department']) ?></span></td>
                                <td><span class="badge bg-light text-dark border"><?= e($r['category']) ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= (int)$r['ticket_count'] ?></span>
                                </td>
                                <td class="small"><?= format_date($r['first_ticket_date']) ?></td>
                                <td class="small"><?= format_date($r['last_ticket_date']) ?></td>
                                <td class="text-center small"><?= (int)$r['days_span'] ?> days</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SQL reference -->
<div class="card mt-4">
    <div class="card-header bg-light">
        <a class="text-decoration-none d-block" data-bs-toggle="collapse" href="#sqlReference">
            <strong><i class="bi bi-code-slash"></i> Show SQL Queries Used in These Reports</strong>
        </a>
    </div>
    <div class="collapse" id="sqlReference">
        <div class="card-body">
            <h6 class="text-primary">Report A — Overdue tickets (older than 48 hours, by support person)</h6>
            <pre class="bg-dark text-light p-3 rounded small"><code>SELECT
    sp.first_name, sp.last_name,
    t.ticket_id, t.subject, t.priority, t.status,
    c.name AS category, t.created_at,
    TIMESTAMPDIFF(HOUR, t.created_at, NOW()) AS hours_open
FROM tickets t
  JOIN categories c ON t.category_id = c.category_id
  JOIN staff sp     ON t.assigned_to = sp.staff_id
WHERE t.status IN ('Open', 'In Progress')
  AND t.created_at &lt; DATE_SUB(NOW(), INTERVAL 48 HOUR)
ORDER BY sp.last_name, sp.first_name, t.created_at ASC;</code></pre>

            <h6 class="text-primary mt-3">Report B — Average resolution time per category (last 30 days)</h6>
            <pre class="bg-dark text-light p-3 rounded small"><code>SELECT
    c.name AS category,
    COUNT(t.ticket_id) AS tickets_resolved,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)) / 60, 2)
        AS avg_resolution_hours,
    ROUND(MIN(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)) / 60, 2)
        AS fastest_hours,
    ROUND(MAX(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)) / 60, 2)
        AS slowest_hours
FROM tickets t
  JOIN categories c ON t.category_id = c.category_id
WHERE t.status IN ('Resolved', 'Closed')
  AND t.resolved_at &gt;= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY c.category_id, c.name
ORDER BY avg_resolution_hours DESC;</code></pre>

            <h6 class="text-primary mt-3">Report C — Staff with 3+ tickets in same category within 7 days</h6>
            <pre class="bg-dark text-light p-3 rounded small"><code>SELECT
    s.first_name, s.last_name, s.department,
    c.name AS category,
    COUNT(t.ticket_id) AS ticket_count,
    MIN(t.created_at) AS first_ticket_date,
    MAX(t.created_at) AS last_ticket_date,
    DATEDIFF(MAX(t.created_at), MIN(t.created_at)) AS days_span
FROM tickets t
  JOIN staff s      ON t.staff_id = s.staff_id
  JOIN categories c ON t.category_id = c.category_id
GROUP BY s.staff_id, s.first_name, s.last_name, s.department,
         c.category_id, c.name
HAVING COUNT(t.ticket_id) &gt;= 3
   AND DATEDIFF(MAX(t.created_at), MIN(t.created_at)) &lt;= 7
ORDER BY ticket_count DESC, s.last_name;</code></pre>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
