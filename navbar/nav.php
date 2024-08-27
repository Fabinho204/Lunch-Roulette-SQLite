<nav class="navbar navbar-expand-lg navbar-light navbar-custom">
    <div class="container-fluid"> <!-- Changed from 'container' to 'container-fluid' for full width -->
        <a href="index.php">
            <img class="company-logo" src="https://upload.wikimedia.org/wikipedia/commons/5/5f/Siemens-logo.svg">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.php">Home</a>
                </li>
                <?php
                // Check if the user is logged in and is an admin
                if (isset($_SESSION['adminlogin']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                    echo '<li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">Benutzermanagement</a>
                        </li>';
                }
                ?>
            </ul>
            <ul class="navbar-nav ml-auto">
                <?php
                if (isset($_SESSION['adminlogin'])) {
                    echo '<li class="nav-item">
                            <a class="nav-link" href="../functional_files/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>';
                } else {
                    echo '<li class="nav-item">
                            <a class="nav-link logout-btn" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>
