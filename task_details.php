<?php
session_start();
require './vendor/autoload.php'; // For Google Cloud Storage
include './shared/header.php';
include './shared/db.php';
putenv('GOOGLE_APPLICATION_CREDENTIALS=./ejada-internship-project-ab9d527aff10.json');
use Google\Cloud\Storage\StorageClient;
$storage = new StorageClient([
    'keyFilePath' => './ejada-internship-project-ab9d527aff10.json'
]);
$bucketName = 'todo-attachments-bucket';
$bucket = $storage->bucket($bucketName);


// Get the task ID from the URL parameter
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if ($task_id == 0) {
    echo "<p>Invalid task ID.</p>";
    include 'includes/footer.php';
    exit;
}

// Fetch task details
try {
    $stmt = $pdo->prepare('SELECT t.title, t.description, t.deadline, t.priority, c.name AS category
                           FROM tasks t
                           JOIN categories c ON t.category_id = c.id
                           WHERE t.id = :task_id');
    $stmt->execute(['task_id' => $task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo "<p>Task not found.</p>";
        include 'includes/footer.php';
        exit;
    }

    // Fetch comments
    $stmt = $pdo->prepare('SELECT c.id, c.comment, u.username, c.created_at
                           FROM comments c
                           JOIN users u ON c.user_id = u.id
                           WHERE c.task_id = :task_id
                           ORDER BY c.created_at ASC');
    $stmt->execute(['task_id' => $task_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch attachments
    $stmt = $pdo->prepare('SELECT id, file_name, file_path, uploaded_at
                           FROM attachments
                           WHERE task_id = :task_id
                           ORDER BY uploaded_at ASC');
    $stmt->execute(['task_id' => $task_id]);
    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Handle Add/Edit/Delete Comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_comment'])) {
        $new_comment = $_POST['comment'];
        $user_id = $_SESSION['user_id'];

        try {
            $stmt = $pdo->prepare('INSERT INTO comments (task_id, user_id, comment) VALUES (:task_id, :user_id, :comment)');
            $stmt->execute(['task_id' => $task_id, 'user_id' => $user_id, 'comment' => $new_comment]);
            header("Location: task_details.php?task_id=$task_id");
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    if (isset($_POST['edit_comment'])) {
        $comment_id = intval($_POST['comment_id']);
        $updated_comment = $_POST['comment'];

        try {
            $stmt = $pdo->prepare('UPDATE comments SET comment = :comment WHERE id = :comment_id');
            $stmt->execute(['comment' => $updated_comment, 'comment_id' => $comment_id]);
            header("Location: task_details.php?task_id=$task_id");
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    if (isset($_POST['delete_comment'])) {
        $comment_id = intval($_POST['comment_id']);

        try {
            $stmt = $pdo->prepare('DELETE FROM comments WHERE id = :comment_id');
            $stmt->execute(['comment_id' => $comment_id]);
            header("Location: task_details.php?task_id=$task_id");
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Handle Add/Edit/Delete Attachment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_attachment'])) {
        $file_name = $_FILES['attachment']['name'];
        $file_tmp_name = $_FILES['attachment']['tmp_name'];
        $file_path = 'uploads/' . $file_name;

        // Move the file to the uploads directory
        move_uploaded_file($file_tmp_name, $file_path);

        // Upload file to Google Cloud Storage
        $object = $bucket->upload(
            fopen($file_tmp_name, 'r'),
            [
                'name' => $file_name
            ]
        );

        // Get the public URL of the uploaded file
        $publicUrl = sprintf(
            'https://storage.googleapis.com/%s/%s',
            $bucketName,
            $file_name
        );


        
        try {
            $stmt = $pdo->prepare('INSERT INTO attachments (task_id, file_name, file_path) VALUES (:task_id, :file_name, :file_path)');
            $stmt->execute(['task_id' => $task_id, 'file_name' => $file_name, 'file_path' => $publicUrl]);
            header("Location: task_details.php?task_id=$task_id");
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    if (isset($_POST['edit_attachment'])) {
        $attachment_id = intval($_POST['attachment_id']);
        $file_name = $_FILES['attachment']['name'];
        $file_tmp_name = $_FILES['attachment']['tmp_name'];
        $file_path =  'uploads/' . $file_name;

        // Move the file to the uploads directory
        move_uploaded_file($file_tmp_name, $file_path);

        try {
            $stmt = $pdo->prepare('UPDATE attachments SET file_name = :file_name, file_path = :file_path WHERE id = :attachment_id');
            $stmt->execute(['file_name' => $file_name, 'file_path' => $file_path, 'attachment_id' => $attachment_id]);
            header("Location: task_details.php?task_id=$task_id");
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    if (isset($_POST['delete_attachment'])) {
        $attachment_id = intval($_POST['attachment_id']);
        $sql = "SELECT file_name FROM attachments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $attachment_id);
        $stmt->execute();
        $attachment = $stmt->fetch(PDO::FETCH_ASSOC);
        try {
            // Delete the file from Google Cloud Storage
            $object = $bucket->object($attachment['file_name']);
            $object->delete();

            $stmt = $pdo->prepare('DELETE FROM attachments WHERE id = :attachment_id');
            $stmt->execute(['attachment_id' => $attachment_id]);
            header("Location: task_details.php?task_id=$task_id");
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

?>

<div class="container mt-5">
    <!-- Task Title and Description -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h1 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h1>
            <p class="card-text"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
            <div class="d-flex justify-content-between">
                <p><strong>Category:</strong> <?php echo htmlspecialchars($task['category']); ?></p>
                <p><strong>Deadline:</strong> <span
                        class="badge bg-danger" style="color:white;"><?php echo htmlspecialchars($task['deadline']); ?></span></p>
                <p><strong>Priority:</strong> <span
                        class="badge bg-warning"><?php echo htmlspecialchars($task['priority']); ?></span></p>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h2>Comments</h2>
        </div>
        <div class="card-body">
            <?php if ($comments): ?>
                <ul class="list-group">
                    <?php foreach ($comments as $comment): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                <small class="text-muted">Posted on:
                                    <?php echo htmlspecialchars($comment['created_at']); ?></small>
                            </div>
                            <div class="d-flex">
                                <!-- Edit Comment Form -->
                                <form method="POST" action="" class="d-inline-block me-2 w-100">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <div class="input-group">
                                        <input type="text" name="comment" class="form-control form-control-sm"
                                            value="<?php echo htmlspecialchars($comment['comment']); ?>" required>
                                        <button class="btn btn-primary btn-sm" type="submit" name="edit_comment">
                                            <i class="fa-solid fa-pen-to-square" style="color: #74C0FC;"></i>
                                        </button>
                                    </div>
                                </form>
                                <!-- Delete Comment Form -->
                                <form method="POST" action="" class="d-inline-block">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <button class="btn btn-danger btn-sm" type="submit" name="delete_comment">
                                        <i class="fa-solid fa-eraser"></i>
                                    </button>
                                </form>
                            </div>

                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No comments yet.</p>
            <?php endif; ?>

            <!-- Add Comment Form -->
            <form method="POST" action="" class="mt-4">
                <div class="input-group">
                    <input type="text" name="comment" class="form-control" placeholder="Add a new comment..." required>
                    <div class="input-group-append">
                        <button class="btn btn-success" type="submit" name="add_comment"><i
                                class="fa-solid fa-plus"></i> Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Attachments Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h2>Attachments</h2>
        </div>
        <div class="card-body">
            <?php if ($attachments): ?>
                <ul class="list-group">
                    <?php foreach ($attachments as $attachment): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>"
                                target="_blank"><?php echo htmlspecialchars($attachment['file_name']); ?></a>
                            <small class="text-muted">Uploaded on:
                                <?php echo htmlspecialchars($attachment['uploaded_at']); ?></small>
                            <form method="POST" action="" enctype="multipart/form-data" class="d-inline-block">
                                <input type="hidden" name="attachment_id" value="<?php echo $attachment['id']; ?>">
                                <button class="btn btn-danger btn-sm" type="submit" name="delete_attachment"><i
                                        class="fa-solid fa-eraser"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No attachments yet.</p>
            <?php endif; ?>

            <!-- Add Attachment Form -->
            <form method="POST" action="" enctype="multipart/form-data" class="mt-4">
                <div class="input-group">
                    <input type="file" name="attachment" class="form-control" required>
                    <div class="input-group-append">
                        <button class="btn btn-success" type="submit" name="add_attachment"><i
                                class="fa-solid fa-plus"></i> Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
include './shared/footer.php';
?>