<?php
/**
 * User Management Tool
 * เครื่องมือจัดการ Users: เพิ่ม, แก้ไข, ลบ
 */

require_once 'db_config.php';
header('Content-Type: text/html; charset=utf-8');

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            // Add new user
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $email = trim($_POST['email']);
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $phone = trim($_POST['phone']);
            $role = $_POST['role'];
            
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, email, first_name, last_name, phone, role)
                VALUES (:username, :password, :email, :first_name, :last_name, :phone, :role)
            ");
            
            $stmt->execute([
                'username' => $username,
                'password' => $hashedPassword,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'role' => $role
            ]);
            
            $message = "✅ User '{$username}' added successfully!";
            $messageType = 'success';
            
        } elseif ($action === 'update_password') {
            // Update password
            $username = trim($_POST['username']);
            $newPassword = trim($_POST['new_password']);
            
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
            
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = :username");
            $stmt->execute(['password' => $hashedPassword, 'username' => $username]);
            
            if ($stmt->rowCount() > 0) {
                $message = "✅ Password updated for '{$username}'";
                $messageType = 'success';
            } else {
                $message = "❌ User '{$username}' not found";
                $messageType = 'error';
            }
            
        } elseif ($action === 'delete') {
            // Delete user
            $username = trim($_POST['username']);
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            
            if ($stmt->rowCount() > 0) {
                $message = "✅ User '{$username}' deleted";
                $messageType = 'success';
            } else {
                $message = "❌ User '{$username}' not found";
                $messageType = 'error';
            }
        }
    } catch (PDOException $e) {
        $message = "❌ Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all users
$stmt = $pdo->query("SELECT user_id, username, email, first_name, last_name, phone, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #0056b3; border-bottom: 3px solid #0056b3; padding-bottom: 10px; }
        h2 { color: #333; margin-top: 30px; }
        .message { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-section { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #ddd; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        input:focus, select:focus { outline: none; border-color: #0056b3; }
        button { background: #0056b3; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; font-weight: bold; }
        button:hover { background: #003d82; }
        button.danger { background: #dc3545; }
        button.danger:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #0056b3; color: white; font-weight: bold; }
        tr:hover { background: #f5f5f5; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge.doctor { background: #007bff; color: white; }
        .badge.therapist { background: #28a745; color: white; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👥 User Management Tool</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Add New User -->
        <div class="form-section">
            <h2>➕ Add New User</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="grid">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="text" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone">
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role" required>
                            <option value="doctor">Doctor</option>
                            <option value="physical_therapist">Physical Therapist</option>
                        </select>
                    </div>
                </div>
                <button type="submit">Add User</button>
            </form>
        </div>
        
        <!-- Update Password -->
        <div class="form-section">
            <h2>🔑 Update Password</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_password">
                <div class="grid">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="text" name="new_password" required>
                    </div>
                </div>
                <button type="submit">Update Password</button>
            </form>
        </div>
        
        <!-- Delete User -->
        <div class="form-section">
            <h2>🗑️ Delete User</h2>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                <input type="hidden" name="action" value="delete">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required>
                </div>
                <button type="submit" class="danger">Delete User</button>
            </form>
        </div>
        
        <!-- List All Users -->
        <h2>📋 All Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td>
                        <span class="badge <?php echo $user['role'] === 'doctor' ? 'doctor' : 'therapist'; ?>">
                            <?php echo $user['role'] === 'doctor' ? 'Doctor' : 'Physical Therapist'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
