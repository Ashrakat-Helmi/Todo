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
        $task_id = $_POST['task_id'];
        $filename = $_FILES['file']['name'];
        $filetmp = $_FILES['file']['tmp_name'];
        $filepath = 'uploads/' . $filename;

        if (move_uploaded_file($filetmp, $filepath)) {
            $sql = "INSERT INTO attachments (task_id, file_name, file_path) VALUES (:task_id, :filename ,:filepath)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':task_id', $task_id);
            $stmt->bindParam(':filename', $filename);
            $stmt->bindParam(':filepath', $filepath);

            if ($stmt->execute()) {
                header('Location: attachments.php');
                exit;
            } else {
                echo "Error: " . $stmt->errorInfo()[2];
            }
        } else {
            echo "Failed to upload file.";
        }
    }
} elseif ($action === 'delete') {
    $id = $_GET['id'];
    $sql = "SELECT filename FROM attachments WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attachment) {
        $filepath = 'uploads/' . $attachment['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $sql = "DELETE FROM attachments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            header('Location: attachments.php');
            exit;
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }
}

// Fetch all attachments
$sql = "SELECT attachments.*, tasks.title AS task_title 
        FROM attachments 
        LEFT JOIN tasks ON attachments.task_id = tasks.id";
$stmt = $pdo->query($sql);
$attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all tasks for the form dropdown
$sql = "SELECT * FROM tasks WHERE user_id =" . $_SESSION['user_id'];
$stmt = $pdo->query($sql);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include './shared/header.php'; ?>

<div class="container mt-5">
    <h2>Attachments</h2>
    <a href="attachments.php?action=add" class="btn btn-primary mb-3">Add Attachment</a>

    <?php if ($action === 'add'): ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="task_id" class="form-label">Task</label>
                <select class="form-control" id="task_id" name="task_id" required>
                    <?php foreach ($tasks as $task): ?>
                        <option value="<?php echo $task['id']; ?>"><?php echo $task['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">File</label>
                <input type="file" class="form-control" id="file" name="file" required>
            </div>
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task</th>
                    <th>File</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attachments as $attachment): ?>
                    <tr>
                        <td><?php echo $attachment['id']; ?></td>
                        <td><?php echo $attachment['task_title']; ?></td>
                        <td><a href="uploads/<?php echo $attachment['file_name']; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a></td>
                        <td>
                            <a href="attachments.php?action=delete&id=<?php echo $attachment['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include './shared/footer.php'; ?>
