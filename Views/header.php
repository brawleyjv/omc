<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/OMC/public/css/styles.css">
    <style>
        .button {
            background-color: white; /* Change button background to white */
            color: #007bff; /* Change button text color to blue */
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 10px;
            border: 2px solid #007bff; /* Add border to button */
            font-weight: bold; /* Make button text bold */
        }
        .button:hover {
            background-color: #007bff; /* Change hover background to blue */
            color: white; /* Change hover text color to white */
        }
        .button.disabled {
            pointer-events: none; /* Disable click events */
            opacity: 0.5; /* Reduce opacity */
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info span {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div>
                <h1>Ozark Made Crafts</h1>
                <p>Precision Craftsmanship with a Personal Touch</p>
            </div>
            <div>
                <?php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                if (isset($_SESSION['username'])): ?>
                    <a href="/OMC/views/main.php" class="btn styled-btn">Home</a>
                <?php else: ?>
                    <a href="#" class="btn styled-btn disabled">Home</a> <!-- Inactive Home button -->
                <?php endif; ?>
                <a href="/OMC/views/about.php" class="btn styled-btn">About</a>
                <?php if (isset($_SESSION['username'])): ?>
                    <div class="user-info">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="/OMC/public/logout.php" class="btn styled-btn">Logout</a> <!-- Logout button to the right of the user's name -->
                    </div>
                <?php else: ?>
                    <a href="/OMC/public/Users/register.php" class="btn styled-btn">Register</a> <!-- Registration button -->
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
