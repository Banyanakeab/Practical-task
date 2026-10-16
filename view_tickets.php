<?php
/**
 * View Open Tickets — Hospital IT Helpdesk System
 */
$page_title = 'Open Tickets';
$current_page = 'tickets';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Filter parameters (validated)
$statusFilter  = $_GET['status'] ?? 'all';
$catFilter     = $_GET['category'] ?? 'all';
$assignFilter  = $_GET['assigned'] ?? 'all';

$validStatuses = ['all', 'Open', 'In Progress'];
if (!in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = 'all';
}
if ($catFilter !== 'all' && !ctype_digit($catFilter)) {
    $catFilter = 'all';
}
if ($assignFilter !== 'all' && !ctype_digit($assignFilter)) {
    $assignFilter = 'all';
}


$sql = "SELECT t.ticket_id, t.subject, t.priority, t.status, t.created_at,
               c.name AS category,
               CONCAT(s.first_name, ' ', s.last_name) AS staff_name,
               CONCAT(sp.first_name, ' ', sp.last_name) AS assigned_name,
               TIMESTAMPDIFF(HOUR, t.created_at, NOW()) AS hours_open
        FROM tickets t
          JOIN categories c  ON t.category_id = c.category_id
          JOIN staff s       ON t.staff_id = s.staff_id
          LEFT JOIN staff sp ON t.assigned_to = sp.staff_id
        WHERE t.status IN ('Open', 'In Progress')";

$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND t.status = :status";
    $params[':status'] = $statusFilter;
}
if ($catFilter !== 'all') {
    $sql .= " AND t.category_id = :cat";
    $params[':cat'] = (int)$catFilter;
}
if ($assignFilter !== 'all') {
    $sql .= " AND t.assigned_to = :assign";
    $params[':assign'] = (int)$assignFilter;
}

$sql .= " ORDER BY
            CASE t.priority WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Medium' THEN 3 WHEN 'Low' THEN 4 END,
            t.created_at ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

$categories   = get_categories();
$supportStaff = get_support_staff();
?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-list-task"></i> Open Tickets</h1>
        <div class="subtitle">All tickets currently awaiting resolution (<?= count($tickets) ?> shown)</div>
    </div>
    <a href="submit_ticket.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Submit New Ticket
    </a>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="view_tickets.php" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Open</option>
                    <option value="Open" <?= $statusFilter === 'Open' ? 'selected' : '' ?>>Open</option>
                    <option value="In Progress" <?= $statusFilter === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Category</label>
                <select class="form-select form-select-sm" name="category">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['category_id'] ?>" <?= $catFilter === (string)$c['category_id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Assigned To</label>
                <select class="form-select form-select-sm" name="assigned">
                    <option value="all">Everyone</option>
                    <?php foreach ($supportStaff as $sp): ?>
                        <option value="<?= (int)$sp['staff_id'] ?>" <?= $assignFilter === (string)$sp['staff_id'] ? 'selected' : '' ?>>
                            <?= e($sp['first_name'] . ' ' . $sp['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                <a href="view_tickets.php" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Raised By</th>
                        <th>Assigned To</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Age</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size:2rem;"></i><br>
                                No open tickets matching the current filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <tr class="<?= $t['hours_open'] >= 48 ? 'row-overdue' : '' ?>">
                                <td><a href="ticket_detail.php?id=<?= (int)$t['ticket_id'] ?>" class="fw-bold text-decoration-none">#<?= (int)$t['ticket_id'] ?></a></td>
                                <td>
                                    <a href="ticket_detail.php?id=<?= (int)$t['ticket_id'] ?>" class="text-decoration-none">
                                        <?= e($t['subject']) ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($t['category']) ?></span></td>
                                <td class="small"><?= e($t['staff_name']) ?></td>
                                <td class="small"><?= e($t['assigned_name'] ?: 'Unassigned') ?></td>
                                <td><span class="badge <?= priority_badge_class($t['priority']) ?>"><?= e($t['priority']) ?></span></td>
                                <td><span class="badge <?= status_badge_class($t['status']) ?>"><?= e($t['status']) ?></span></td>
                                <td class="small <?= $t['hours_open'] >= 48 ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                    <?= (int)$t['hours_open'] ?>h
                                    <?php if ($t['hours_open'] >= 48): ?>
                                        <i class="bi bi-exclamation-triangle"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="ticket_detail.php?id=<?= (int)$t['ticket_id'] ?>" class="btn btn-sm btn-outline-primary py-0">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
