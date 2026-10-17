<?php
/**
 * Ticket Detail — Hospital IT Helpdesk System
 * Shows ticket information and allows support staff to add updates / change status.
 */
$page_title = 'Ticket Detail';
$current_page = 'tickets';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Validate ticket ID
$ticketId = $_GET['id'] ?? '';
if (!ctype_digit($ticketId)) {
    set_flash('danger', 'Invalid ticket ID.');
    header('Location: view_tickets.php');
    exit;
}
$ticketId = (int)$ticketId;

// Fetch ticket (parameterised)
$stmt = $db->prepare(
    "SELECT t.*,
            c.name AS category_name, c.category_id,
            CONCAT(s.first_name, ' ', s.last_name) AS staff_name,
            s.department AS staff_department, s.email AS staff_email, s.phone AS staff_phone,
            CONCAT(sp.first_name, ' ', sp.last_name) AS assigned_name,
            sp.role AS assigned_role
     FROM tickets t
       JOIN categories c  ON t.category_id = c.category_id
       JOIN staff s       ON t.staff_id = s.staff_id
       LEFT JOIN staff sp ON t.assigned_to = sp.staff_id
     WHERE t.ticket_id = ?");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    set_flash('danger', 'Ticket not found.');
    header('Location: view_tickets.php');
    exit;
}

// Fetch updates (parameterised)
$stmt = $db->prepare(
    "SELECT tu.*, CONCAT(s.first_name, ' ', s.last_name) AS author_name
     FROM ticket_updates tu
       JOIN staff s ON tu.staff_id = s.staff_id
     WHERE tu.ticket_id = ?
     ORDER BY tu.created_at ASC");
$stmt->execute([$ticketId]);
$updates = $stmt->fetchAll();

$supportStaff = get_support_staff();
$errors = [];

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action       = $_POST['action'] ?? '';
    $updateMessage = trim($_POST['update_message'] ?? '');
    $newStatus     = trim($_POST['new_status'] ?? '');
    $assignTo      = trim($_POST['assign_to'] ?? '');

    if ($action === 'add_update') {
        if ($updateMessage === '') {
            $errors['update_message'] = 'Please enter an update message.';
        } elseif (mb_strlen($updateMessage) > 5000) {
            $errors['update_message'] = 'Update message must not exceed 5000 characters.';
        }

        $validStatuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
        if (!in_array($newStatus, $validStatuses, true)) {
            $errors['new_status'] = 'Invalid status.';
        }
        if ($assignTo !== '' && !ctype_digit($assignTo)) {
            $errors['assign_to'] = 'Invalid assignment.';
        }

        if (empty($errors)) {
            try {
                $db->beginTransaction();

                // Insert update record (parameterised)
                $stmt = $db->prepare(
                    "INSERT INTO ticket_updates (ticket_id, staff_id, message, old_status, new_status)
                     VALUES (:ticket_id, :staff_id, :message, :old_status, :new_status)");
                $stmt->execute([
                    ':ticket_id'  => $ticketId,
                    ':staff_id'   => (int)$assignTo,
                    ':message'    => $updateMessage,
                    ':old_status' => $ticket['status'],
                    ':new_status' => $newStatus,
                ]);

                // Update ticket status / assignment (parameterised)
                $resolvedAt = $newStatus === 'Resolved' ? date('Y-m-d H:i:s') : null;
                if ($newStatus === 'Open' || $newStatus === 'In Progress') {
                    $resolvedAt = null;
                }

                $stmt = $db->prepare(
                    "UPDATE tickets
                     SET status = :status, resolved_at = :resolved_at
                     WHERE ticket_id = :ticket_id");
                $stmt->execute([
                    ':status'      => $newStatus,
                    ':resolved_at' => $resolvedAt,
                    ':ticket_id'   => $ticketId,
                ]);

                $db->commit();

                set_flash('success', "Ticket #{$ticketId} has been updated.");
                header("Location: ticket_detail.php?id={$ticketId}");
                exit;
            } catch (PDOException $ex) {
                $db->rollBack();
                $errors['general'] = 'A database error occurred while updating the ticket.';
            }
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1>
            <a href="view_tickets.php" class="text-decoration-none text-muted small me-2">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            Ticket #<?= $ticketId ?>
        </h1>
        <div class="subtitle"><?= e($ticket['subject']) ?></div>
    </div>
    <div class="d-flex gap-2">
        <span class="badge <?= status_badge_class($ticket['status']) ?> fs-6"><?= e($ticket['status']) ?></span>
        <span class="badge <?= priority_badge_class($ticket['priority']) ?> fs-6"><?= e($ticket['priority']) ?></span>
    </div>
</div>

<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($errors['general']) ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Ticket details -->
    <div class="col-lg-8">
        <div class="ticket-detail-header">
            <h2><?= e($ticket['subject']) ?></h2>
            <div class="ticket-meta">
                <div class="ticket-meta-item">
                    <span class="label">Category</span>
                    <span class="value"><?= e($ticket['category_name']) ?></span>
                </div>
                <div class="ticket-meta-item">
                    <span class="label">Raised By</span>
                    <span class="value"><?= e($ticket['staff_name']) ?> (<?= e($ticket['staff_department']) ?>)</span>
                </div>
                <div class="ticket-meta-item">
                    <span class="label">Assigned To</span>
                    <span class="value"><?= e($ticket['assigned_name'] ?: 'Unassigned') ?>
                        <?php if (!empty($ticket['assigned_role'])): ?>
                            <span class="text-muted">(<?= e($ticket['assigned_role']) ?>)</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="ticket-meta-item">
                    <span class="label">Created</span>
                    <span class="value"><?= format_date($ticket['created_at']) ?></span>
                </div>
                <div class="ticket-meta-item">
                    <span class="label">Resolved</span>
                    <span class="value"><?= format_date($ticket['resolved_at']) ?></span>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white">
                <strong><i class="bi bi-chat-left-text text-primary"></i> Description</strong>
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space:pre-wrap;"><?= e($ticket['description']) ?></p>
            </div>
        </div>

        <!-- Updates timeline -->
        <div class="card">
            <div class="card-header bg-white">
                <strong><i class="bi bi-clock-history text-primary"></i> Updates &amp; Activity</strong>
                <span class="badge bg-secondary ms-2"><?= count($updates) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($updates)): ?>
                    <p class="text-muted text-center py-3 mb-0">No updates yet. Be the first to add one below.</p>
                <?php else: ?>
                    <ul class="timeline">
                        <?php foreach ($updates as $u): ?>
                            <li class="timeline-item">
                                <div class="timeline-header">
                                    <span class="timeline-author"><?= e($u['author_name']) ?></span>
                                    <span class="timeline-date"><?= format_date($u['created_at']) ?></span>
                                </div>
                                <div class="timeline-body">
                                    <?= e($u['message']) ?>
                                    <?php if ($u['old_status'] || $u['new_status']): ?>
                                        <div class="timeline-status">
                                            <?php if ($u['old_status']): ?>
                                                <span class="badge <?= status_badge_class($u['old_status']) ?>"><?= e($u['old_status']) ?></span>
                                                <i class="bi bi-arrow-right text-muted mx-1"></i>
                                            <?php endif; ?>
                                            <?php if ($u['new_status']): ?>
                                                <span class="badge <?= status_badge_class($u['new_status']) ?>"><?= e($u['new_status']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add update form (only if ticket is not closed) -->
        <?php if ($ticket['status'] !== 'Closed'): ?>
        <div class="card mt-4">
            <div class="card-header bg-white">
                <strong><i class="bi bi-reply text-primary"></i> Add Update</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="ticket_detail.php?id=<?= $ticketId ?>" novalidate>
                    <input type="hidden" name="action" value="add_update">

                    <div class="mb-3">
                        <label for="update_message" class="form-label">Update Message <span class="req">*</span></label>
                        <textarea class="form-control <?= isset($errors['update_message']) ? 'is-invalid' : '' ?>"
                                  id="update_message" name="update_message" rows="3" maxlength="5000"
                                  placeholder="Describe what was done, what was found, or next steps..."></textarea>
                        <?php if (isset($errors['update_message'])): ?>
                            <div class="invalid-feedback"><?= e($errors['update_message']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="assign_to" class="form-label">Your Name (Support Staff)</label>
                            <select class="form-select <?= isset($errors['assign_to']) ? 'is-invalid' : '' ?>" id="assign_to" name="assign_to">
                                <option value="">— Select yourself —</option>
                                <?php foreach ($supportStaff as $sp): ?>
                                    <option value="<?= (int)$sp['staff_id'] ?>"><?= e($sp['first_name'] . ' ' . $sp['last_name']) ?> — <?= e($sp['role']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['assign_to'])): ?>
                                <div class="invalid-feedback"><?= e($errors['assign_to']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="new_status" class="form-label">Change Status To</label>
                            <select class="form-select <?= isset($errors['new_status']) ? 'is-invalid' : '' ?>" id="new_status" name="new_status">
                                <option value="Open">Open</option>
                                <option value="In Progress" <?= $ticket['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Resolved">Resolved</option>
                                <option value="Closed">Closed</option>
                            </select>
                            <?php if (isset($errors['new_status'])): ?>
                                <div class="invalid-feedback"><?= e($errors['new_status']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Add Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar: requester info -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <strong><i class="bi bi-person text-primary"></i> Requester</strong>
            </div>
            <div class="card-body">
                <p class="mb-1 fw-semibold"><?= e($ticket['staff_name']) ?></p>
                <p class="mb-1 text-muted small"><?= e($ticket['staff_department']) ?> Department</p>
                <hr class="my-2">
                <p class="mb-1 small"><i class="bi bi-envelope"></i> <?= e($ticket['staff_email']) ?></p>
                <?php if (!empty($ticket['staff_phone'])): ?>
                    <p class="mb-0 small"><i class="bi bi-telephone"></i> <?= e($ticket['staff_phone']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <strong><i class="bi bi-info-circle text-primary"></i> Ticket Info</strong>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted small d-block">Ticket ID</span>
                    <span class="fw-semibold">#<?= $ticketId ?></span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Priority</span>
                    <span class="badge <?= priority_badge_class($ticket['priority']) ?>"><?= e($ticket['priority']) ?></span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Status</span>
                    <span class="badge <?= status_badge_class($ticket['status']) ?>"><?= e($ticket['status']) ?></span>
                </div>
                <div class="mb-0">
                    <span class="text-muted small d-block">Created</span>
                    <span class="small"><?= format_date($ticket['created_at']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
