<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            overflow-x: hidden;
        }
        #sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            transition: all 0.3s;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background-color: #212529;
        }
        #sidebar ul.components {
            padding: 20px 0;
        }
        #sidebar ul li a {
            padding: 10px 20px;
            font-size: 1.1em;
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }
        #sidebar ul li a:hover {
            color: #fff;
            background-color: #495057;
        }
        #sidebar ul li.active > a {
            color: #fff;
            background-color: #007bff;
        }
        #content {
            width: 100%;
            min-height: 100vh;
            padding: 20px;
            transition: all 0.3s;
        }
        .iframe-container {
            position: relative;
            width: 100%;
            height: calc(100vh - 100px);
        }
        .iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark" id="sidebar">
            <div class="sidebar-header text-center">
                <h4>Dashboard</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link active" data-module="dashboard.html">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-module="video.html">
                        <i class="fas fa-video me-2"></i>Video
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-module="gallery.html">
                        <i class="fas fa-images me-2"></i>Gallery
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-module="settings.php">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                </li>
            </ul>
        </div>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                <div class="container-fluid">
                    <button class="btn btn-dark d-lg-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a class="navbar-brand" href="#">Cloud NVR - WebbyPage</a>
                    <div class="ms-auto">
                        <span class="navbar-text me-3">
                            Welcome, <strong>Admin</strong>
                        </span>
                        <button class="btn btn-outline-danger btn-sm" style="display: none;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </div>
                </div>
            </nav>

            <div class="iframe-container">
                <iframe id="contentFrame" src="dashboard.html" frameborder="0"></iframe>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Sidebar toggle
            $('#sidebarToggle').click(function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            // Handle navigation clicks
            $('.nav-link').click(function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                $('.nav-link').removeClass('active');
                
                // Add active class to clicked link
                $(this).addClass('active');
                
                // Get the module to load
                const module = $(this).data('module');
                
                // Load the module in the iframe
                $('#contentFrame').attr('src', module);
            });

            // Handle iframe load event
            $('#contentFrame').on('load', function() {
                // You can add additional logic here when iframe content loads
                console.log('Content loaded: ' + $(this).attr('src'));
            });
        });
    </script>
</body>
</html>