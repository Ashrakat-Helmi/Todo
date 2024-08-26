<?php
session_start();
include './shared/db.php';
include './shared/header.php';

// Fetch some example data from the database to display on the dashboard
try {
    $stmt = $pdo->query('SELECT t.title, t.description, t.deadline, t.priority, c.name AS category
                         FROM tasks t
                         JOIN categories c ON t.category_id = c.id
                         WHERE t.user_id = ' . $_SESSION['user_id'] . '
                         ORDER BY t.created_at DESC
                         LIMIT 5');
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>
<div class="jumbotron">
    <h1 class="display-4">Welcome to Your To-Do App!</h1>
    <p class="lead">Manage your tasks, and categories efficiently.</p>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <a class="btn btn-primary btn-lg" href="auth/register.php" role="button">Get Started</a>
    <?php endif; ?>
</div>

<div class="container mt-5">
    <h2>Recent Tasks</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Deadline</th>
                <th>Priority</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($tasks): ?>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
                        <td><?php echo htmlspecialchars($task['deadline']); ?></td>
                        <td><?php echo htmlspecialchars($task['priority']); ?></td>
                        <td><?php echo htmlspecialchars($task['category']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No tasks available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
include './shared/footer.php';
?>