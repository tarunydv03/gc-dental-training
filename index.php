
<?php
session_start();
require 'db.php'; // This line requires db.php to be present

// Session and cookie test
$_SESSION['test'] = 'Session is working!';
setcookie('testcookie', 'Cookie works', time()+3600, '/');

$session_test = $_SESSION['test'] ?? 'Session not set';
$cookie_test = $_COOKIE['testcookie'] ?? 'Cookie not set yet (refresh page)';

$errors = [];
$success = "";
$name = $email = $gender = $country = "";
$hobbies = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $country = $_POST['country'] ?? '';
    $hobbies = $_POST['hobbies'] ?? [];
    if ($name === '') $errors[] = "Name required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";
    if ($gender === '') $errors[] = "Select gender.";
    if (empty($hobbies)) $errors[] = "Select at least one hobby.";
    if ($country === '') $errors[] = "Select country.";
    if (empty($errors)) {
        $h_str = implode(',', $hobbies);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, gender, hobbies, country) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $gender, $h_str, $country]);
        $_SESSION['flash'] = "Record created successfully.";
        header("Location: index.php");
        exit;
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="style.css">
    <script src="form-validate.js"></script>
    <style>
        .navbar {
            width: 100%;
            background: #fff;
            box-shadow: 0 2px 12px #b3e5fc33;
            padding: 0 0 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 60px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10;
        }
        .navbar .nav-title {
            font-size: 1.4em;
            color: #2196f3;
            font-weight: bold;
            letter-spacing: 1px;
            margin-left: 32px;
        }
        .navbar .nav-links {
            display: flex;
            gap: 18px;
            margin-right: 32px;
        }
        .navbar .nav-links a {
            text-decoration: none;
            color: #2196f3;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 8px;
            transition: background 0.2s, color 0.2s;
        }
        .navbar .nav-links a.active, .navbar .nav-links a:hover {
            background: #e3f2fd;
            color: #1565c0;
        }
        .container {
            margin-top: 90px;
            display: flex;
            gap: 40px;
            justify-content: center;
            align-items: flex-start;
        }
        .form-card {
            min-width: 340px;
            max-width: 400px;
            margin: 0;
        }
        .crud-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            background: rgba(255,255,255,0.85);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 2px 12px #b3e5fc22;
        }
        .crud-table th, .crud-table td {
            padding: 12px 18px;
            text-align: left;
        }
        .crud-table th {
            background: #e3f2fd;
            color: #2196f3;
            font-weight: 600;
        }
        .crud-table tr:not(:last-child) {
            border-bottom: 1px solid #b3e5fc55;
        }
        .crud-table tr:hover {
            background: #f5fafd;
        }
        .action-btn {
            display: inline-block;
            padding: 6px 14px;
            margin: 0 2px;
            border-radius: 7px;
            background: #2196f3;
            color: #fff;
            text-decoration: none;
            font-size: 0.98em;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 4px #b3e5fc33;
        }
        .action-btn.edit { background: #43a047; }
        .action-btn.delete { background: #e53935; }
        .action-btn:hover { filter: brightness(1.08); box-shadow: 0 2px 8px #b3e5fc55; }
        @media (max-width: 900px) {
            .container { flex-direction: column; align-items: center; gap: 32px; }
        }
    </style>
</head>
<body>
<div style="position:fixed;top:65px;right:24px;z-index:9999;background:#fff8;padding:10px 18px;border-radius:10px;box-shadow:0 2px 8px #b3e5fc33;font-size:0.98em;">
    <b>Session:</b> <?= htmlspecialchars($session_test) ?><br>
    <b>Cookie:</b> <?= htmlspecialchars($cookie_test) ?>
</div>
<div class="navbar">
    <div class="nav-title">User Management</div>
    <div class="nav-links">
        <a href="#form" class="active">Add User</a>
        <a href="#list">User List</a>
    </div>
</div>
<div class="main-bg" style="min-height:100vh;">
    <div class="container">
        <div class="form-card" id="form">
            <h1 class="form-title" style="font-size:1.5em;">Add User</h1>
            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="success"><?= htmlspecialchars($_SESSION['flash']) ?></div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif ?>
            <?php if ($errors): ?>
                <div class="error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
            <?php endif; ?>
            <form id="mainForm" method="post" novalidate style="margin-bottom:0;">
                <input type="hidden" name="action" value="create">
                <label>Name<input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required></label>
                <label>Email<input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></label>
                <div class="inline-group">Gender:
                    <label><input type="radio" name="gender" value="Male" <?= $gender === 'Male' ? 'checked' : ''; ?>> Male</label>
                    <label><input type="radio" name="gender" value="Female" <?= $gender === 'Female' ? 'checked' : ''; ?>> Female</label>
                </div>
                <div class="inline-group">Hobbies:
                    <label><input type="checkbox" name="hobbies[]" value="Reading" <?= in_array('Reading', $hobbies) ? 'checked' : ''; ?>> Reading</label>
                    <label><input type="checkbox" name="hobbies[]" value="Music" <?= in_array('Music', $hobbies) ? 'checked' : ''; ?>> Music</label>
                    <label><input type="checkbox" name="hobbies[]" value="Sports" <?= in_array('Sports', $hobbies) ? 'checked' : ''; ?>> Sports</label>
                </div>
                <label>Country
                    <select name="country" required>
                        <option value="">Select</option>
                        <option value="India" <?= $country === 'India' ? 'selected' : ''; ?>>India</option>
                        <option value="USA" <?= $country === 'USA' ? 'selected' : ''; ?>>USA</option>
                        <option value="Other" <?= $country === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </label>
                <button type="submit">Add User</button>
            </form>
        </div>
        <div style="flex:1;max-width:700px;" id="list">
            <h2 class="form-title" style="font-size:1.3em;margin-bottom:18px;">User List</h2>
            <table class="crud-table">
                <tr>
                    <th>Name</th><th>Email</th><th>Gender</th><th>Hobbies</th><th>Country</th><th>Action</th>
                </tr>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['gender']) ?></td>
                    <td><?= htmlspecialchars($u['hobbies']) ?></td>
                    <td><?= htmlspecialchars($u['country']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $u['id'] ?>" class="action-btn edit">Edit</a>
                        <a href="delete.php?id=<?= $u['id'] ?>" class="action-btn delete" onclick="return confirm('Delete this user?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
