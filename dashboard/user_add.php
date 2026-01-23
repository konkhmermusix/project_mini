<?php
require '../inc/db.php';
session_start();


if ($_POST) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    $stmt = $conn->prepare(
        "INSERT INTO users(username,email,password,role) VALUES(?,?,?,?)"
    );
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    $stmt->execute();

    header("Location: users.php");
}

require 'inc/header.php';

?>

<?php if (!empty($message)) echo $message; ?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Add Users</h3>
            <a href="users.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="POST">

                <div class="form-outline mb-3">
                    <input class="form-control" type="text" name="username" placeholder=" " required>
                    <label>User Name</label>
                </div>

                <div class="form-outline mb-3">
                    <input class="form-control" type="email" name="email" placeholder=" " required>
                    <label>Email</label>
                </div>

                <div class="form-outline mb-3">
                    <input class="form-control" type="password" name="password" placeholder=" " required>
                    <label>Password</label>
                </div>

                <div class="form-outline mb-3">
                    <select class="form-select" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                    <label>Role</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="status" checked>
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