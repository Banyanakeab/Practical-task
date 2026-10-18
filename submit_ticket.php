<?php
/**
 * Submit Ticket — Hospital IT Helpdesk System
 * Handles form display, validation, and database insertion using parameterised queries.
 */
$page_title = 'Submit Ticket';
$current_page = 'submit';
require_once __DIR__ . '/includes/header.php';

$hospitalStaff = get_hospital_staff();
$categories    = get_categories();
$supportStaff  = get_support_staff();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId     = trim($_POST['staff_id'] ?? '');
    $categoryId  = trim($_POST['category_id'] ?? '');
    $assignedTo  = trim($_POST['assigned_to'] ?? '');
    $subject     = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority    = trim($_POST['priority'] ?? '');

    $old = compact('staffId', 'categoryId', 'assignedTo', 'subject', 'description', 'priority');

    // --- Validation ---
    if ($staffId === '' || !ctype_digit($staffId)) {
        $errors['staff_id'] = 'Please select your name from the list.';
    }
    if ($categoryId === '' || !ctype_digit($categoryId)) {
        $errors['category_id'] = 'Please select a category.';
    }
    if ($assignedTo !== '' && !ctype_digit($assignedTo)) {
        $errors['assigned_to'] = 'Invalid support person selection.';
    }
    if ($subject === '') {
        $errors['subject'] = 'Subject is required.';
    } elseif (mb_strlen($subject) < 5) {
        $errors['subject'] = 'Subject must be at least 5 characters.';
    } elseif (mb_strlen($subject) > 200) {
        $errors['subject'] = 'Subject must not exceed 200 characters.';
    }
    if ($description === '') {
        $errors['description'] = 'Please describe the problem.';
    } elseif (mb_strlen($description) < 10) {
        $errors['description'] = 'Description must be at least 10 characters.';
    } elseif (mb_strlen($description) > 5000) {
        $errors['description'] = 'Description must not exceed 5000 characters.';
    }
    $validPriorities = ['Low', 'Medium', 'High', 'Critical'];
    if (!in_array($priority, $validPriorities, true)) {
        $errors['priority'] = 'Please select a valid priority.';
    }

    // --- Insert with parameterised query (PDO prepared statement) ---
    if (empty($errors)) {
        try {
            $sql = 'INSERT INTO tickets (staff_id, category_id, assigned_to, subject, description, priority, status)
                    VALUES (:staff_id, :category_id, :assigned_to, :subject, :description, :priority, "Open")';
            $stmt = get_db()->prepare($sql);
            $stmt->execute([
                ':staff_id'     => (int)$staffId,
                ':category_id'  => (int)$categoryId,
                ':assigned_to'  => $assignedTo !== '' ? (int)$assignedTo : null,
                ':subject'      => $subject,
                ':description'  => $description,
                ':priority'     => $priority,
            ]);

            $ticketId = (int) get_db()->lastInsertId();
            set_flash('success', "Ticket #{$ticketId} has been submitted successfully. IT support will be in touch.");
            header('Location: ticket_detail.php?id=' . $ticketId);
            exit;
        } catch (PDOException $ex) {
            $errors['general'] = 'A database error occurred while submitting your ticket. Please try again.';
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-plus-circle"></i> Submit IT Support Ticket</h1>
        <div class="subtitle">Report an IT problem and it will be routed to the support team</div>
    </div>
    <a href="view_tickets.php" class="btn btn-outline-secondary">
        <i class="bi bi-list-task"></i> View Open Tickets
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= e($errors['general']) ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="submit_ticket.php" novalidate>

                    <div class="row g-3">
                        <!-- Staff member -->
                        <div class="col-md-6">
                            <label for="staff_id" class="form-label">
                                Your Name <span class="req">*</span>
                            </label>
                            <select class="form-select <?= isset($errors['staff_id']) ? 'is-invalid' : '' ?>" id="staff_id" name="staff_id">
                                <option value="">— Select your name —</option>
                                <?php foreach ($hospitalStaff as $s): ?>
                                    <option value="<?= (int)$s['staff_id'] ?>"
                                        <?= ($old['staffId'] ?? '') === (string)$s['staff_id'] ? 'selected' : '' ?>>
                                        <?= e($s['first_name'] . ' ' . $s['last_name']) ?> — <?= e($s['department']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['staff_id'])): ?>
                                <div class="invalid-feedback"><?= e($errors['staff_id']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">
                                Category <span class="req">*</span>
                            </label>
                            <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" id="category_id" name="category_id">
                                <option value="">— Select category —</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= (int)$c['category_id'] ?>"
                                        <?= ($old['categoryId'] ?? '') === (string)$c['category_id'] ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <div class="invalid-feedback"><?= e($errors['category_id']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Subject -->
                        <div class="col-12">
                            <label for="subject" class="form-label">
                                Subject <span class="req">*</span>
                            </label>
                            <input type="text" class="form-control <?= isset($errors['subject']) ? 'is-invalid' : '' ?>"
                                   id="subject" name="subject" maxlength="200"
                                   placeholder="Brief summary of the problem"
                                   value="<?= e($old['subject'] ?? '') ?>">
                            <?php if (isset($errors['subject'])): ?>
                                <div class="invalid-feedback"><?= e($errors['subject']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label">
                                Description <span class="req">*</span>
                            </label>
                            <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                                      id="description" name="description" rows="5" maxlength="5000"
                                      placeholder="Describe the problem in detail — what were you doing, what happened, any error messages?"><?= e($old['description'] ?? '') ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="invalid-feedback"><?= e($errors['description']) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Minimum 10 characters, maximum 5000 characters.</div>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-6">
                            <label for="priority" class="form-label">
                                Priority <span class="req">*</span>
                            </label>
                            <select class="form-select <?= isset($errors['priority']) ? 'is-invalid' : '' ?>" id="priority" name="priority">
                                <option value="">— Select priority —</option>
                                <option value="Low" <?= ($old['priority'] ?? '') === 'Low' ? 'selected' : '' ?>>Low — Minor issue, no urgency</option>
                                <option value="Medium" <?= ($old['priority'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium — Affects some work</option>
                                <option value="High" <?= ($old['priority'] ?? '') === 'High' ? 'selected' : '' ?>>High — Significant impact</option>
                                <option value="Critical" <?= ($old['priority'] ?? '') === 'Critical' ? 'selected' : '' ?>>Critical — Cannot work / patient safety</option>
                            </select>
                            <?php if (isset($errors['priority'])): ?>
                                <div class="invalid-feedback"><?= e($errors['priority']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Assign to (optional) -->
                        <div class="col-md-6">
                            <label for="assigned_to" class="form-label">
                                Assign To <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <select class="form-select <?= isset($errors['assigned_to']) ? 'is-invalid' : '' ?>" id="assigned_to" name="assigned_to">
                                <option value="">— Auto-assign —</option>
                                <?php foreach ($supportStaff as $sp): ?>
                                    <option value="<?= (int)$sp['staff_id'] ?>"
                                        <?= ($old['assignedTo'] ?? '') === (string)$sp['staff_id'] ? 'selected' : '' ?>>
                                        <?= e($sp['first_name'] . ' ' . $sp['last_name']) ?> — <?= e($sp['role']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['assigned_to'])): ?>
                                <div class="invalid-feedback"><?= e($errors['assigned_to']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Submit Ticket
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3 text-muted small">
            <i class="bi bi-shield-check"></i> All inputs are validated and stored using parameterised SQL queries to prevent SQL injection.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
