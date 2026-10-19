# Hospital IT Helpdesk System

A complete, functional web application for hospital staff to report IT problems, view open tickets, and generate reports on overdue tickets, average resolution times, and recurring IT problems.

## Technology Stack

| Layer             | Technology                          |
|-------------------|-------------------------------------|
| Backend            | PHP 8 (PDO)                        |
| Database           | MySQL / MariaDB (XAMPP)            |
| DB Administration  | phpMyAdmin                         |
| Frontend           | HTML5, CSS3, JavaScript            |
| UI Framework       | Bootstrap 5                        |
| Icons              | Bootstrap Icons                    |
| Development Env    | XAMPP (Apache + MySQL/MariaDB)     |

## Setup Instructions (XAMPP)

### 1. Copy project to XAMPP

Copy the entire project folder into your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\hospital-helpdesk\
```

### 2. Start XAMPP

Open the **XAMPP Control Panel** and start:
- **Apache**
- **MySQL**

### 3. Import the database

1. Open **phpMyAdmin** in your browser: `http://localhost/phpmyadmin`
2. Click the **Import** tab at the top
3. Click **Choose File** and select:
   ```
   sql/hospital_helpdesk.sql
   ```
4. Click **Go** to import

This creates the `hospital_helpdesk` database with all tables, sample data, and indexes.

### 4. Open the application

Navigate to:

```
http://localhost/hospital-helpdesk/index.php
```

## Database Configuration

Default XAMPP credentials are pre-configured in `includes/db.php`:

| Setting   | Value                |
|-----------|----------------------|
| Host      | localhost            |
| Database  | hospital_helpdesk    |
| Username  | root                 |
| Password  | *(empty)*            |

If your XAMPP uses a different password, edit `includes/db.php`.

## Database Schema

### Tables

1. **staff** — Hospital staff and IT support personnel
   - Primary key: `staff_id`
   - Indexes: `department`, `is_support`
   - `is_support` flag distinguishes IT support staff from general hospital staff

2. **categories** — IT problem categories (Hardware, Software, Network, etc.)
   - Primary key: `category_id`
   - Unique constraint on `name`

3. **tickets** — IT support tickets
   - Primary key: `ticket_id`
   - Foreign keys: `staff_id` → staff, `category_id` → categories, `assigned_to` → staff
   - Indexes: `status`, `staff_id`, `category_id`, `assigned_to`, `created_at`, `priority`
   - Enum fields for `priority` and `status`

4. **ticket_updates** — Activity log / updates on each ticket
   - Primary key: `update_id`
   - Foreign keys: `ticket_id` → tickets (ON DELETE CASCADE), `staff_id` → staff
   - Indexes: `ticket_id`, `staff_id`, `created_at`

### Sample Data

- 18 staff members (4 IT support + 14 hospital staff across 7 departments)
- 7 categories
- 40+ tickets spread across 60+ days (with deliberate overdue, resolved, and recurring patterns)
- 14 ticket updates

## Application Features

### Dashboard (`index.php`)
- Summary stat cards: total, open, in-progress, resolved, and overdue tickets
- List of 5 most recent tickets
- List of 3 most overdue tickets (>48 hours)

### Submit Ticket (`submit_ticket.php`)
- Form to submit a new IT support ticket
- Fields: name, category, subject, description, priority, optional assignment
- Full server-side validation:
  - All required fields checked
  - Minimum/maximum length enforcement
  - Enum values validated against allowed list
  - Numeric IDs validated
- Uses **parameterised SQL queries** (PDO prepared statements) — no SQL string concatenation
- Redirects to ticket detail page on success with confirmation message

### View Open Tickets (`view_tickets.php`)
- Table of all open and in-progress tickets
- Filter by status, category, and assigned support person
- Tickets older than 48 hours are highlighted in red
- Priority-based sorting (Critical first)

### Ticket Detail (`ticket_detail.php`)
- Full ticket information with requester contact details
- Activity timeline showing all updates
- Form to add updates and change ticket status
- Status changes are logged in the timeline with old/new status badges

### Reports (`reports.php`)
Three required SQL reports:

**Report A — Overdue Tickets**
- Finds all open/in-progress tickets older than 48 hours
- Organised by assigned support person
- Shows hours open and full ticket details

**Report B — Average Resolution Time per Category**
- Calculates average, fastest, and slowest resolution times
- Grouped by category
- Limited to tickets resolved in the last 30 days

**Report C — Recurring Problems**
- Identifies staff who raised 3+ tickets in the same category within a 7-day period
- Shows ticket count, date range, and days span
- Ordered by frequency

### SQL Reference
The reports page includes a collapsible section showing the exact SQL queries used.

## Security

- **Parameterised queries**: All database operations use PDO prepared statements — user input is never concatenated into SQL strings
- **Input validation**: Every form field is validated server-side before database operations
- **XSS prevention**: All output is escaped with `htmlspecialchars()` via the `e()` helper function
- **SQL injection prevention**: PDO with `ATTR_EMULATE_PREPARES => false` ensures real prepared statements

## Project Structure

```
hospital-helpdesk/
├── index.php              # Dashboard
├── submit_ticket.php      # Submit new ticket form
├── view_tickets.php       # List of open tickets with filters
├── ticket_detail.php      # Ticket detail + update form
├── reports.php            # Three SQL reports
├── css/
│   └── style.css          # Custom styling
├── includes/
│   ├── db.php             # PDO database connection
│   ├── functions.php      # Helper functions
│   ├── header.php         # Shared header (navbar, Bootstrap)
│   └── footer.php         # Shared footer
├── sql/
│   └── hospital_helpdesk.sql  # Database schema + sample data
└── README.md
```
