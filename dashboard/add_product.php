<?php
require '../inc/header.php';
require '../inc/db.php';

// Admin guard
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Handle form submission
if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
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
        $stmt = $conn->prepare("INSERT INTO products(name, description, price, image) VALUES(?,?,?,?)");
        $stmt->bind_param("ssds", $name, $description, $price, $image);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success alert-right'>Product added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger alert-right'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Floating label style */
        .form-outline {
            position: relative;
        }

        .form-outline input,
        .form-outline textarea {
            height: 45px;
            border-radius: 5px;
            padding: 16px 12px;
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
            transition: 0.2s ease;
            pointer-events: none;
        }

        .form-outline input:focus+label,
        .form-outline input:not(:placeholder-shown)+label,
        .form-outline textarea:focus+label,
        .form-outline textarea:not(:placeholder-shown)+label {
            top: 0;
            font-size: 12px;
            color: #0d6efd;
        }

        /* Alert right-side */
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
</head>

<body>
    <?php if (!empty($message)) echo $message; ?>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Add New Product</h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">

                    <div class="form-outline mb-3">
                        <input id="name" class="form-control" name="name" placeholder=" " required>
                        <label for="name">Product Name</label>
                    </div>

                    <div class="form-outline mb-3">
                        <textarea id="description" class="form-control" name="description" placeholder=" " rows="5"></textarea>
                        <label for="description">Description</label>
                    </div>

                    <div class="form-outline mb-3">
                        <input id="price" class="form-control" type="number" step="0.01" name="price" placeholder=" " required>
                        <label for="price">Price ($)</label>
                    </div>

                    <div class="mb-3">
                        <input id="image" class="form-control" type="file" name="image" accept="image/*" onchange="previewImage(event)">
                        <!-- <label for="image">Product Image</label> -->
                        <img id="preview" src="#" alt="Image Preview" class="img-fluid">
                    </div>

                    <button type="submit" class="btn btn-success" name="submit">Add Product</button>
                    <a href="products.php" class="btn btn-secondary ms-2">Back to Products</a>
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

        // Auto-hide alert after 3 seconds
        setTimeout(() => {
            document.querySelector('.alert-right')?.remove();
        }, 3000);
    </script>

    <?php include '../inc/footer.php'; ?>
</body>

</html>