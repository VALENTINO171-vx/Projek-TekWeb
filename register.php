<?php
include 'user.php';
include 'connection.php';
// ...existing code...
session_start();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '') $errors[] = 'Username is required.';
    if ($password === '') $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $password_confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        if (!isset($conn)) {
            $conn = new mysqli('localhost', 'root', '', 'petra_airlines');
        }

        $dbError = false;
        if ($conn instanceof PDO) {
            try {
                $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
            } catch (Exception $e) {
                $dbError = true;
                $errors[] = 'Database connection failed (PDO).';
            }
        } elseif ($conn instanceof mysqli) {
            if ($conn->connect_error) {
                $dbError = true;
                $errors[] = 'Database connection failed (mysqli).';
            }
        } else {
            $dbError = true;
            $errors[] = 'Unknown database connection type.';
        }

        if (!$dbError) {
            $exists = false;
            if ($conn instanceof PDO) {
                $checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
                if ($checkStmt === false) {
                    $errors[] = 'Database error.';
                } else {
                    $ok = $checkStmt->execute([$username]);
                    if ($ok) {
                        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
                        if ($row) $exists = true;
                    } else {
                        $errors[] = 'Database error.';
                    }
                }
            } else {
                $checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
                if (!$checkStmt) {
                    $errors[] = 'Database error.';
                } else {
                    $checkStmt->bind_param('s', $username);
                    $checkStmt->execute();
                    $checkStmt->store_result();
                    if ($checkStmt->num_rows > 0) $exists = true;
                    $checkStmt->close();
                }
            }

            if ($exists) {
                $errors[] = 'Username already taken.';
            }
        }

        if (empty($errors)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = 'member';
            $insertOk = false;

            if ($conn instanceof PDO) {
                $insertStmt = $conn->prepare('INSERT INTO users (username, pass, role) VALUES (?, ?, ?)');
                if ($insertStmt !== false) {
                    $insertOk = $insertStmt->execute([$username, $hashed, $role]);
                }
                if (!$insertOk) $errors[] = 'Failed to register (PDO).';
            } else {
                $insertStmt = $conn->prepare('INSERT INTO users (username, pass, role) VALUES (?, ?, ?)');
                if (!$insertStmt) {
                    $errors[] = 'Database error on insert.';
                } else {
                    $insertStmt->bind_param('sss', $username, $hashed, $role);
                    if ($insertStmt->execute()) {
                        $insertOk = true;
                    } else {
                        $errors[] = 'Failed to register.';
                    }
                    $insertStmt->close();
                }
            }

            if ($insertOk) {
                $success = 'Registration successful. You can now <a href="login.php">login</a>.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <?php if (!empty($errors)): ?>
        <div style="color:#b00">
            <ul>
                <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
               <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green"><?php echo $success; ?></div>
    <?php endif; ?>
    <div class="container container-fluid">
        <?php
            if(isset($_SESSION['error'])){
                echo '<div class="alert alert-danger mt-3" role="alert">'.htmlspecialchars($_SESSION['error']).'</div>';
                unset($_SESSION['error']);
            }
        ?>
        <form method="POST" action="register.php">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    register Form
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label for="username" class="col-sm-2 col-form-label">Username</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="password" class="col-sm-2 col-form-label">Password</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="password_confirm" class="col-sm-2 col-form-label">Confirm password</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a class="navbar-brand" href="login.php">login</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                    </button>
                    <button type="submit" class="btn btn-primary">register</button>
                </div>
            </div>
        </form>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>