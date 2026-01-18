<?php
require '../inc/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get old data
$stmt = $conn->prepare("SELECT * FROM slideshow WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$slide = $result->fetch_assoc();
$stmt->close();

if (!$slide) {
    die("Slide not found!");
}

// Submit update
if (isset($_POST['submit'])) {

    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $link        = trim($_POST['link']);
    $position    = intval($_POST['position']);
    $status      = isset($_POST['status']) ? 1 : 0;

    $image = $slide['image']; // keep old image

    // // If upload new image
    // if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    //     $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    //     $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    //     if (!in_array($ext, $allowed)) {
    //         $message = "<div class='alert alert-danger alert-right'>Invalid image type!</div>";
    //     } else {
    //         $newImage = 'uploads/slideshow_' . time() . '_' . basename($_FILES['image']['name']);
    //         move_uploaded_file($_FILES['image']['tmp_name'], '../' . $newImage);

    //         // delete old image (optional)
    //         if (file_exists('../' . $slide['image'])) {
    //             unlink('../' . $slide['image']);
    //         }

    //         $image = $newImage;
    //     }
    // }

    $image = $slide['image']; // Keep old image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $message = "<div class='alert alert-danger alert-right'>Invalid image type!</div>";
        } else {
            $image = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare(
            "UPDATE slideshow 
            SET title=?, description=?, image=?, link=?, position=?, status=?  WHERE id=?"
        );
        $stmt->bind_param("ssssiii", $title, $description, $image, $link, $position, $status, $id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success alert-right'>Product updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger alert-right'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();

        header("Location: slideshows.php");
        exit;
    }
}

require('inc/header.php');
?>

<style>
    .form-outline {
        position: relative;
    }

    .form-outline input,
    .form-outline textarea,
    .form-outline select {
        height: 45px;
        padding: 16px 12px;
        border-radius: 5px;
    }

    .form-outline label {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        background: #fff;
        padding: 0 6px;
        color: #6c757d;
        font-size: 14px;
        pointer-events: none;
        transition: .2s;
    }

    .form-outline input:focus+label,
    .form-outline input:not(:placeholder-shown)+label,
    .form-outline textarea:focus+label,
    .form-outline textarea:not(:placeholder-shown)+label,
    .form-outline select:focus+label,
    .form-outline select:not(:placeholder-shown)+label {
        top: 0;
        font-size: 12px;
        color: #0d6efd;
    }

    .alert-right {
        position: fixed;
        top: 20px;
        right: 20px;
        min-width: 300px;
        z-index: 1055;
        border-radius: 8px;
    }

    #preview {
        display: none;
        max-height: 200px;
        margin-top: 10px;
    }
</style>


<?php if (!empty($message)) echo $message; ?>

<div class="container mt-4">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Edit Slide</h3>
            <a href="slideshows.php" class="btn btn-secondary ms-auto">&larr; Back</a>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">

                <div class="form-outline mb-3">
                    <input type="text" name="title" class="form-control" placeholder=" " value="<?= htmlspecialchars($slide['title']) ?>" required>
                    <label>Title</label>
                </div>

                <div class="form-outline mb-3">
                    <textarea name="description" class="form-control"><?= htmlspecialchars($slide['description']) ?></textarea>
                    <label>Description</label>
                </div>

                <div class="form-outline mb-3">
                    <input type="text" name="link" class="form-control" value="<?= htmlspecialchars($slide['link']) ?>">
                    <label>Link</label>
                </div>

                <div class="form-outline mb-3">
                    <input type="number" name="position" class="form-control" value="<?= htmlspecialchars($slide['position']) ?>">
                    <label>Position</label>
                </div>

                <div class="mb-3">
                    <input class="form-control" type="file" name="image" accept="image/*" class="form-control" onchange="previewImage(event)">
                    <?php if ($slide['image']): ?>
                        <img id="preview" src="../<?= htmlspecialchars($slide['image']) ?>" class="img-fluid" alt="Preview">
                    <?php else: ?>
                        <img id="preview" src="#" class="img-fluid" alt="Preview">
                    <?php endif; ?>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="status" <?= $slide['status'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Active</label>
                </div>

                <button class="btn btn-success" name="submit">Save</button>
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

<?php include 'inc/footer.php'; ?>