<?php
require '../inc/db.php';
session_start();
$id = (int)$_GET['id'];

$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();

if ($_POST) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $role     = $_POST['role'];
    $status   = isset($_POST['status']) ? 1 : 0;

    if (!empty($_POST['password'])) {
        // Update with new password
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "UPDATE users SET username=?, email=?, password=?, role=?, status=? WHERE id=?"
        );
        $stmt->bind_param("ssssii", $username, $email, $password, $role, $status, $id);
    } else {
        // Update without password
        $stmt = $conn->prepare(
            "UPDATE users SET username=?, email=?, role=?, status=? WHERE id=?"
        );
        $stmt->bind_param("sssii", $username, $email, $role, $status, $id);
    }

    $stmt->execute();
    header("Location: users.php");
    exit;
}


require 'inc/header.php';

?>

<?php if (!empty($message)) echo $message; ?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Edit Users</h3>
            <a href="users.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="POST">

                <div class="form-outline mb-3">
                    <input class="form-control" type="text" name="username" placeholder=" " value="<?= $user['username'] ?>" required>
                    <label>User Name</label>
                </div>

                <div class="form-outline mb-3">
                    <input class="form-control" type="email" name="email" placeholder=" " value="<?= $user['email'] ?>" required>
                    <label>Email</label>
                </div>

                <div class="form-outline mb-3">
                    <input class="form-control" type="password" name="password" placeholder=" ">
                    <label>New Password (leave blank to keep current)</label>
                </div>

                <div class="form-outline mb-3">
                    <select class="form-select" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="user" <?= $user['role'] == 'user'  ? 'selected' : '' ?>>User</option>
                    </select>
                </div>

                <div class="mb-3">
                    <input class="form-check-input" type="checkbox" name="status" value="1"
                        <?= $user['status'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Active</label>
                </div>

                <button class="btn btn-success">Save</button>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        var preview = document.getElementById('preview');
        preview.src = URL.createObjectURL(event.target.files[0]);
        preview.style.display = 'block';
    }       
    setTimeout(() => {
        document.querySelector('.alert-right')?.remove();
    }, 3000);
</script>

<?php require 'inc/footer.php'; ?>  