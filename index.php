<?php
session_start();
require_once 'classes/Project.php';
require_once 'classes/Feedback.php';

$project = new Project();
$feedback = new Feedback();

$projects = $project->read();
// Get recent testimonials for the homepage
$testimonials = $feedback->getRecentFeedback(3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFlow - Project Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-2xl font-bold text-yellow-500">TechFlow</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="dashboard/index.php" class="text-gray-700 hover:text-yellow-500 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <a href="dashboard/projects.php" class="text-gray-700 hover:text-yellow-500 px-3 py-2 rounded-md text-sm font-medium">Projects</a>
                        <a href="dashboard/profile.php" class="text-gray-700 hover:text-yellow-500 px-3 py-2 rounded-md text-sm font-medium">Profile</a>
                        <a href="logout.php" class="text-gray-700 hover:text-yellow-500 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-yellow-500 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-yellow-500">
        <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold text-white sm:text-5xl sm:tracking-tight lg:text-6xl">
                    Digital Solutions for Modern Businesses
                </h1>
                <p class="mt-6 max-w-2xl mx-auto text-xl text-yellow-100">
                    We create innovative digital experiences that help businesses grow and succeed in the digital world.
                </p>
            </div>
        </div>
    </div>

    <!-- Projects Section -->
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-8">Our Projects</h2>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach($projects as $project): ?>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($project['title']); ?></h3>
                    <p class="mt-1 text-sm text-gray-500">Client: <?php echo htmlspecialchars($project['client_name']); ?></p>
                    <p class="mt-2 text-sm text-gray-600"><?php echo htmlspecialchars($project['description']); ?></p>
                    <div class="mt-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            <?php echo $project['status'] === 'Completed' ? 'bg-green-100 text-green-800' : 
                                    ($project['status'] === 'In Progress' ? 'bg-blue-100 text-blue-800' : 
                                    'bg-yellow-100 text-yellow-800'); ?>">
                            <?php echo htmlspecialchars($project['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Feedback Form -->
    <div class="bg-white">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-lg mx-auto">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-8">Send Us Feedback</h2>
                <form id="feedbackForm" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div>
                        <label for="project" class="block text-sm font-medium text-gray-700">Project</label>
                        <select name="project_id" id="project" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="">Select a project</option>
                            <?php foreach($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea name="message" id="message" rows="4" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                    </div>
                    <div>
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            Send Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#feedbackForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'api/submitFeedback.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        alert('Thank you for your feedback!');
                        $('#feedbackForm')[0].reset();
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
    </script>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">TechFlow</h3>
                    <p class="text-gray-400">Empowering businesses with innovative digital solutions and project management expertise.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-yellow-500">Home</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="dashboard.php" class="text-gray-400 hover:text-yellow-500">Dashboard</a></li>
                            <li><a href="projects.php" class="text-gray-400 hover:text-yellow-500">Projects</a></li>
                            <li><a href="profile.php" class="text-gray-400 hover:text-yellow-500">Profile</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="text-gray-400 hover:text-yellow-500">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li>Email: info@techflow.com</li>
                        <li>Phone: +1 (555) 123-4567</li>
                        <li>Address: 123 Tech Street, Digital City</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> TechFlow. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html> 