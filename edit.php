<?php
session_start();
require 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) die('User ID required.');

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die('User not found.');

$errors = [];
$name = $user['name'];
$email = $user['email'];
$gender = $user['gender'];
$country = $user['country'];
$hobbies = explode(',', $user['hobbies']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $st2 = $pdo->prepare("UPDATE users SET name=?, email=?, gender=?, hobbies=?, country=? WHERE id=?");
        $st2->execute([$name, $email, $gender, $h_str, $country, $id]);
        $_SESSION['flash'] = "Record updated.";
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
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
            justify-content: center;
            align-items: flex-start;
        }
        .form-card {
            min-width: 340px;
            max-width: 400px;
            margin: 0 auto;
        }
        @media (max-width: 900px) {
            .container { flex-direction: column; align-items: center; gap: 32px; }
        }
    </style>
</head>
<body>
<div class="navbar">
    <div class="nav-title">User Management</div>
    <div class="nav-links">
        <a href="index.php#form">Add User</a>
        <a href="index.php#list">User List</a>
    </div>
</div>
<div class="main-bg" style="min-height:100vh;">
    <div class="container">
        <div class="form-card">
            <h1 class="form-title" style="font-size:1.5em;">Edit User</h1>
            <?php if ($errors): ?>
                <div class="error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
            <?php endif; ?>
            <form method="post" style="margin-bottom:0;">
                <label>Name<input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required></label>
                <label>Email<input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></label>
                <div class="inline-group">Gender:
                    <label><input type="radio" name="gender" value="Male" <?= $gender==='Male'?'checked':''; ?>> Male</label>
                    <label><input type="radio" name="gender" value="Female" <?= $gender==='Female'?'checked':''; ?>> Female</label>
                </div>
                <div class="inline-group">Hobbies:
                    <label><input type="checkbox" name="hobbies[]" value="Reading" <?= in_array('Reading',$hobbies)?'checked':''; ?>> Reading</label>
                    <label><input type="checkbox" name="hobbies[]" value="Music" <?= in_array('Music',$hobbies)?'checked':''; ?>> Music</label>
                    <label><input type="checkbox" name="hobbies[]" value="Sports" <?= in_array('Sports',$hobbies)?'checked':''; ?>> Sports</label>
                </div>
                <label>Country
                    <select name="country" required>
                        <option value="">Select</option>
                        <option value="India" <?= $country==='India'?'selected':''; ?>>India</option>
                        <option value="USA" <?= $country==='USA'?'selected':''; ?>>USA</option>
                        <option value="Other" <?= $country==='Other'?'selected':''; ?>>Other</option>
                    </select>
                </label>
                <button type="submit">Update</button>
                <a href="index.php" style="margin-left:12px;">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
