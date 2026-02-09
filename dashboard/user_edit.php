<?php
require '../inc/db.php';
session_start();

$message = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: users.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// បើក្មេីន User ក្នុង DB ទេ ឱ្យត្រឡប់ទៅវិញ
if (!$user) {
    header("Location: users.php");
    exit;
}

// --- ២. Logic ពេលចុច Update ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $role     = $_POST['role'];
    $status   = isset($_POST['status']) ? 1 : 0;

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=?, role=?, status=? WHERE id=?");
        $update_stmt->bind_param("ssssii", $username, $email, $password, $role, $status, $id);
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, status=? WHERE id=?");
        $update_stmt->bind_param("sssii", $username, $email, $role, $status, $id);
    }

    if ($update_stmt->execute()) {
        header("Location: users.php?msg=updated");
        exit;
    } else {
        $message = "<div class='alert alert-danger'>Error updating user.</div>";
    }
}

require 'inc/header.php';
?>

<?php if (!empty($message)) echo $message; ?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Edit User: <?php echo htmlspecialchars($user['username']); ?></h3>
            <a href="users.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="POST">
                <div class="form-outline mb-4">
                    <input class="form-control" type="text" name="username"
                        value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    <label>User Name</label>
                </div>

                <div class="form-outline mb-4">
                    <input class="form-control" type="email" name="email"
                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <label>Email</label>
                </div>

                <div class="form-outline mb-4">
                    <input class="form-control" type="password" name="password" placeholder="Leave blank to keep current password">
                    <label>Password (Optional)</label>
                </div>

                <div class="form-outline mb-4">
                    <select class="form-select" name="role" required>
                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                    </select>
                    <label>Role</label>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="status" id="status"
                        <?php echo ($user['status'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="status">Active Account</label>
                </div>

                <button class="btn btn-primary px-4 shadow-sm">
                    <i class="bi bi-check-circle me-2"></i>Update User
                </button>
            </form>
        </div>
    </div>
</div>

<?php require 'inc/footer.php'; ?>