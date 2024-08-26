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
        $title = $_POST['title'];
        $description = $_POST['description'];
        $deadline = $_POST['deadline'];
        $priority = $_POST['priority'];
        $category_id = $_POST['category_id'];

        $sql = "INSERT INTO tasks (title,user_id ,description, deadline, priority, category_id) 
                VALUES (:title,:user_id , :description, :deadline, :priority, :category_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':user_id',$_SESSION['user_id']);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':deadline', $deadline);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':category_id', $category_id);

        if ($stmt->execute()) {
            header('Location: tasks.php');
            exit;
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }
} elseif ($action === 'edit') {
    $id = $_GET['id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $deadline = $_POST['deadline'];
        $priority = $_POST['priority'];
        $category_id = $_POST['category_id'];

        $sql = "UPDATE tasks SET title = :title,user_id = :user_id, description = :description, deadline = :deadline, 
                priority = :priority, category_id = :category_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':user_id',$_SESSION['user_id']);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':deadline', $deadline);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header('Location: tasks.php');
            exit;
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    } else {
        $sql = "SELECT * FROM tasks WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} elseif ($action === 'delete') {
    $id = $_GET['id'];
    $sql = "DELETE FROM tasks WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        header('Location: tasks.php');
        exit;
    } else {
        echo "Error: " . $stmt->errorInfo()[2];
    }
}

// Fetch all tasks
$sql = "SELECT tasks.*, categories.name AS category_name 
        FROM tasks 
        LEFT JOIN categories ON tasks.category_id = categories.id
        WHERE tasks.user_id = " . $_SESSION['user_id'] ."
        ORDER BY tasks.id ASC";
$stmt = $pdo->query($sql);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories for the form dropdown
$sql = "SELECT * FROM categories";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include './shared/header.php'; ?>

<div class="container mt-5">
    <h2>Tasks</h2>

    <?php if ($action === 'add' || $action === 'edit'):  ?>
        <!-- Form to add/edit task -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Task Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo $task['title'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" required><?php echo $task['description'] ?? ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="date" class="form-control" id="deadline" name="deadline" value="<?php echo $task['deadline'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="priority" class="form-label">Priority</label>
                <select class="form-control" id="priority" name="priority" required>
                    <option value="Low" <?php if (($task['priority'] ?? '') === 'Low') echo 'selected'; ?>>Low</option>
                    <option value="Medium" <?php if (($task['priority'] ?? '') === 'Medium') echo 'selected'; ?>>Medium</option>
                    <option value="High" <?php if (($task['priority'] ?? '') === 'High') echo 'selected'; ?>>High</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php if (($task['category_id'] ?? '') == $category['id']) echo 'selected'; ?>><?php echo $category['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $action === 'edit' ? 'Update' : 'Add'; ?></button>
        </form>
    <?php else: ?>
        <a href="tasks.php?action=add" class="btn mb-3 btn-block">ADD <i class="fa-solid fa-circle-plus fa-lg" style="color: #63E6BE;"></i></a>

        <div class="row">
            <?php foreach ($tasks as $task): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($task['description']); ?></p>
                            <p class="card-text"><strong>Deadline:</strong> <?php echo htmlspecialchars($task['deadline']); ?></p>
                            <p class="card-text"><strong>Priority:</strong> <?php echo htmlspecialchars($task['priority']); ?></p>
                            <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($task['category_name']); ?></p>
                            <div class="btn-group" role="group" aria-label="Task Actions">
                                <a href="task_details.php?task_id=<?php echo $task['id']; ?>" class="btn btn-sm"><i class="fa-solid fa-eye" style="color: #74C0FC;"></i></a>
                                <a href="tasks.php?action=edit&id=<?php echo $task['id']; ?>" class="btn btn-sm"><i class="fa-solid fa-pen-to-square" style="color: #74C0FC;"></i></a>
                                <a href="tasks.php?action=delete&id=<?php echo $task['id']; ?>" class="btn btn-sm" onclick="return confirm('Are you sure?')"><i class="fa-solid fa-eraser" style="color: #ff0000;"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>



<?php include './shared/footer.php'; ?>
