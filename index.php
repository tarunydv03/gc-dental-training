<?php
// Minimal PHP form handler with MariaDB save
ini_set('display_errors', 1);
error_reporting(E_ALL);

$errors = [];
$success = "";
$name = $email = $gender = $country = "";
$hobbies = [];

// Handle form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $country = $_POST['country'] ?? '';
    $hobbies = $_POST['hobbies'] ?? [];
    // Validate fields
    if ($name === '') $errors[] = "Name is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";
    if ($gender === '') $errors[] = "Select gender.";
    if (empty($hobbies)) $errors[] = "Select at least one hobby.";
    if ($country === '') $errors[] = "Select your country.";
    // Save to DB if valid
    if (empty($errors)) {
        $conn = new mysqli('db', 'db', 'db', 'db');
        if ($conn->connect_errno) {
            $errors[] = "DB error: " . $conn->connect_error;
        } else {
            $hobbies_str = implode(',', $hobbies);
            $stmt = $conn->prepare("INSERT INTO users (name,email,gender,hobbies,country) VALUES (?,?,?,?,?)");
            $stmt->bind_param('sssss', $name, $email, $gender, $hobbies_str, $country);
            if ($stmt->execute()) {
                $success = "Saved! Name: " . htmlspecialchars($name) . ", Email: " . htmlspecialchars($email);
                $name = $email = $gender = $country = "";
                $hobbies = [];
            } else {
                $errors[] = "Insert error: " . $stmt->error;
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Registration Form</title>
    <link rel="stylesheet" href="style.css">
    <script src="form-validate.js"></script>
</head>
<body>
<div class="main-bg">
    <div class="float-shape float1"></div>
    <div class="float-shape float2"></div>
    <div class="float-shape float3"></div>
    <div class="form-card" id="draggableCard">
        <div class="form-title">
            <span class="logo-anim">
                <svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="20" fill="#2196f3" opacity=".25"/><circle cx="24" cy="24" r="14" fill="#fff" opacity=".7"/><path d="M24 15a9 9 0 0 1 9 9v1a9 9 0 0 1-18 0v-1a9 9 0 0 1 9-9z" fill="#2196f3"/><circle cx="24" cy="21" r="4" fill="#90caf9"/></svg>
            </span>
            <span>Registration Form</span>
        </div>
        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="error"><?= implode('<br>', $errors) ?></div>
        <?php endif; ?>
        <form id="mainForm" method="post" novalidate>
            <div class="input-group">
                <label for="name">Name</label>
                <span class="input-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#90caf9" stroke-width="2"/><path d="M4 20c0-4 4-7 8-7s8 3 8 7" stroke="#90caf9" stroke-width="2"/></svg>
                </span>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required autocomplete="off">
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <span class="input-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3" stroke="#90caf9" stroke-width="2"/><path d="M3 7l9 6 9-6" stroke="#90caf9" stroke-width="2"/></svg>
                </span>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required autocomplete="off">
            </div>
            <div class="inline-group">
                <span style="margin-right:8px; color:#2196f3; font-size:1.1em;">Gender:</span>
                <label><input type="radio" name="gender" value="Male" <?= $gender==='Male'?'checked':''; ?>> Male</label>
                <label><input type="radio" name="gender" value="Female" <?= $gender==='Female'?'checked':''; ?>> Female</label>
            </div>
            <div class="inline-group">
                <span style="margin-right:8px; color:#2196f3; font-size:1.1em;">Hobbies:</span>
                <label><input type="checkbox" name="hobbies[]" value="Reading" <?= in_array('Reading',$hobbies)?'checked':''; ?>> Reading</label>
                <label><input type="checkbox" name="hobbies[]" value="Music" <?= in_array('Music',$hobbies)?'checked':''; ?>> Music</label>
                <label><input type="checkbox" name="hobbies[]" value="Sports" <?= in_array('Sports',$hobbies)?'checked':''; ?>> Sports</label>
            </div>
            <div class="input-group">
                <label for="country">Country</label>
                <span class="input-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="10" rx="3" stroke="#90caf9" stroke-width="2"/><path d="M3 9l9 4 9-4" stroke="#90caf9" stroke-width="2"/></svg>
                </span>
                <select id="country" name="country" required>
                    <option value="">Select</option>
                    <option value="India" <?= $country==='India'?'selected':''; ?>>India</option>
                    <option value="USA" <?= $country==='USA'?'selected':''; ?>>USA</option>
                    <option value="Other" <?= $country==='Other'?'selected':''; ?>>Other</option>
                </select>
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
</div>
</body>
<script>
// Make the card draggable
const card = document.getElementById('draggableCard');
let isDragging = false, offsetX = 0, offsetY = 0;
card.addEventListener('mousedown', function(e) {
    isDragging = true;
    offsetX = e.clientX - card.getBoundingClientRect().left;
    offsetY = e.clientY - card.getBoundingClientRect().top;
    card.style.transition = 'none';
    card.style.zIndex = 10;
});
document.addEventListener('mousemove', function(e) {
    if (isDragging) {
        card.style.position = 'fixed';
        card.style.left = (e.clientX - offsetX) + 'px';
        card.style.top = (e.clientY - offsetY) + 'px';
    }
});
document.addEventListener('mouseup', function() {
    if (isDragging) {
        isDragging = false;
        card.style.transition = '';
        card.style.zIndex = 2;
    }
});
</script>
<script src="form_validate.js"></script>
</html>
