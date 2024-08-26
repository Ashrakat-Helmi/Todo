<?php
session_start();
include './shared/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$task_id = $_GET['task_id'];
// Handle CRUD actions
$action = $_GET['action'] ?? '';

if ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $task_id = $_POST['task_id'];
        $comment = $_POST['comment'];

        $sql = "INSERT INTO comments (task_id, user_id, comment) VALUES (:task_id, :user_id, :comment)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':task_id', $task_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':comment', $comment);

        if ($stmt->execute()) {
            header('Location: comments.php');
            exit;
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }
} elseif ($action === 'edit') {
    $id = $_GET['id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $comment = $_POST['comment'];

        $sql = "UPDATE comments SET comment = :comment WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header('Location: comments.php');
            exit;
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    } else {
        $sql = "SELECT * FROM comments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} elseif ($action === 'delete') {
    $id = $_GET['id'];
    $sql = "DELETE FROM comments WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        header('Location: comments.php');
        exit;
    } else {
        echo "Error: " . $stmt->errorInfo()[2];
    }
}

// Fetch all comments
$sql = "SELECT comments.*, tasks.title AS task_title, users.username AS username 
        FROM comments 
        LEFT JOIN tasks ON comments.task_id = tasks.id 
        LEFT JOIN users ON comments.user_id = users.id";
$stmt = $pdo->query($sql);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all tasks for the form dropdown
$sql = "SELECT * FROM tasks WHERE user_id =" . $_SESSION['user_id'];
$stmt = $pdo->query($sql);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include './shared/header.php'; ?>

<div class="container mt-5">
    <h2>Comments</h2>
    <a href="comments.php?action=add" class="btn btn-primary mb-3">Add Comment</a>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="task_id" class="form-label">Task</label>
                <select class="form-control" id="task_id" name="task_id" required>
                    <?php foreach ($tasks as $task): ?>
                        <option value="<?php echo $task['id']; ?>" <?php if (($comment['task_id'] ?? '') == $task['id']) echo 'selected'; ?>><?php echo $task['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="comment" class="form-label">Comment</label>
                <textarea class="form-control" id="comment" name="comment" required><?php echo $comment['comment'] ?? ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $action === 'edit' ? 'Update' : 'Add'; ?></button>
        </form>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task</th>
                    <th>Comment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><?php echo $comment['id']; ?></td>
                        <td><?php echo $comment['task_title']; ?></td>
                        <td><?php echo $comment['comment']; ?></td>
                        <td>
                            <a href="comments.php?action=edit&id=<?php echo $comment['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="comments.php?action=delete&id=<?php echo $comment['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include './shared/footer.php'; ?>
