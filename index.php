<?php
session_start();
$host = 'localhost';
$dbname = 'task_manager';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task'], $_POST['deadline'])) {
    $task = htmlspecialchars($_POST['task']);
    $deadline = htmlspecialchars($_POST['deadline']);
    
    if (!empty($task) && !empty($deadline)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (task, deadline, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$task, $deadline]);
        $_SESSION['notification'] = "Tugas baru berhasil ditambahkan!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['notification'] = "Tugas berhasil dihapus!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if (isset($_GET['complete'])) {
    $id = $_GET['complete'];
    $stmt = $pdo->prepare("UPDATE tasks SET status = 'completed' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['notification'] = "Tugas telah diselesaikan!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$tasks = $pdo->query("SELECT * FROM tasks ORDER BY deadline ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #F5A623;
            --success-color: #2ECC71;
            --danger-color: #E74C3C;
            --background-gradient: linear-gradient(135deg, #6983aa, #79a6d2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-gradient);
            min-height: 100vh;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .task-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .task-form input {
            padding: 0.8rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .task-form input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
            outline: none;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #357ABD;
            transform: translateY(-2px);
        }

        .task-list {
            list-style: none;
            margin-top: 2rem;
        }

        .task-item {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 1rem;
            align-items: center;
            animation: slideIn 0.5s ease-out;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .task-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .task-content {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .task-name {
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .task-deadline {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .completed {
            text-decoration: line-through;
            opacity: 0.7;
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 2rem;
            background: var(--primary-color);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideInRight 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification animate__animated animate__fadeInRight">
            <?php 
                echo $_SESSION['notification'];
                unset($_SESSION['notification']);
            ?>
        </div>
    <?php endif; ?>

    <div class="container animate__animated animate__fadeIn">
        <h1>Task Manager</h1>
        
        <form method="POST" action="" class="task-form">
            <input type="text" name="task" placeholder="Masukkan Tugas" required>
            <input type="date" name="deadline" required>
            <button type="submit" class="btn btn-primary">Tambah Tugas</button>
        </form>

        <div class="task-list">
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item <?php echo $task['status'] === 'completed' ? 'completed' : ''; ?>">
                        <div class="task-content">
                            <div class="task-name"><?php echo htmlspecialchars($task['task']); ?></div>
                            <div class="task-deadline">Deadline: <?php echo htmlspecialchars($task['deadline']); ?></div>
                        </div>
                        <a href="?complete=<?php echo $task['id']; ?>" class="btn btn-success">✓</a>
                        <a href="?delete=<?php echo $task['id']; ?>" class="btn btn-danger">×</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    Belum ada tugas yang ditambahkan.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-hide notifications after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.querySelector('.notification');
            if (notification) {
                setTimeout(() => {
                    notification.classList.add('animate__fadeOutRight');
                    setTimeout(() => {
                        notification.remove();
                    }, 500);
                }, 3000);
            }
        });
    </script>
</body>
</html>