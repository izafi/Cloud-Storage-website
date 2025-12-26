<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudBox - Secure Cloud Storage</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-cloud"></i>
                <span>CloudBox</span>
            </div>
            <div class="nav-links">
                <a href="login.php" class="btn btn-outline">Login</a>
                <a href="register.php" class="btn btn-primary">Sign Up</a>
            </div>
        </nav>

        <main class="hero">
            <div class="hero-content">
                <h1 class="animate-text">Store, Access, Share</h1>
                <h2 class="animate-text-delay">Your Files in the Cloud</h2>
                <p class="hero-description">
                    Secure, reliable, and simple cloud storage for all your files.
                    Access your documents, photos, and videos from anywhere.
                </p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i> Get Started Free
                    </a>
                    <a href="#features" class="btn btn-outline btn-large">
                        <i class="fas fa-info-circle"></i> Learn More
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <div class="cloud-animation">
                    <div class="cloud cloud-1"><i class="fas fa-file-pdf"></i></div>
                    <div class="cloud cloud-2"><i class="fas fa-file-image"></i></div>
                    <div class="cloud cloud-3"><i class="fas fa-file-video"></i></div>
                    <div class="cloud cloud-4"><i class="fas fa-file-archive"></i></div>
                </div>
            </div>
        </main>

        <section id="features" class="features">
            <h2>Why Choose CloudBox?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure Storage</h3>
                    <p>Military-grade encryption for all your files</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3>Anywhere Access</h3>
                    <p>Access files from any device, anywhere</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-infinity"></i>
                    </div>
                    <h3>Unlimited Space</h3>
                    <p>Store as much as you need, no limits</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Fast Uploads</h3>
                    <p>High-speed upload and download</p>
                </div>
            </div>
        </section>

        <footer class="footer">
            <p>&copy; 2024 CloudBox. All rights reserved.</p>
            <p class="footer-links">
                <a href="#">Privacy Policy</a> | 
                <a href="#">Terms of Service</a> | 
                <a href="#">Contact Us</a>
            </p>
        </footer>
    </div>

    <script src="script.js"></script>
</body>
</html>
