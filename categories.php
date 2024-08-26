<?php
session_start();
include './shared/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle CRUD actions
$action = $_GET['action'] ?? '';

if ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $sql = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        header('Location: categories.php');
        exit;
    }
} elseif ($action === 'edit') {
    $id = $_GET['id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $sql = "UPDATE categories SET name = :name WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        header('Location: categories.php');
        exit;
    } else {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} elseif ($action === 'delete') {
    $id = $_GET['id'];
    $sql = "DELETE FROM categories WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    header('Location: categories.php');
    exit;
}

// Fetch all categories
$sql = "SELECT * FROM categories ORDER BY id";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include './shared/header.php'; ?>

<div class="container mt-5">
    <h2>Categories</h2>
    <a href="categories.php?action=add" class="btn mb-3">ADD <i class="fa-solid fa-circle-plus fa-lg" style="color: #63E6BE;"></i></a>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $category['name'] ?? ''; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $action === 'edit' ? 'Update' : 'Add'; ?></button>
        </form>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['id']; ?></td>
                        <td><?php echo $category['name']; ?></td>
                        <td>
                            <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm"><i class="fa-solid fa-pen-to-square" style="color: #74C0FC;"></i></a>
                            <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm" onclick="return confirm('Are you sure?')"><i class="fa-solid fa-eraser" style="color: #ff0000;"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include './shared/footer.php'; ?>
