
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>
        <?= $_SESSION['success'] ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?= $_SESSION['error'] ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php unset($_SESSION['error']); endif; ?>
