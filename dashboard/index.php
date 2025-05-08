<?php
session_start();
require_once '../classes/Project.php';
require_once '../classes/Task.php';
require_once '../classes/Feedback.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$project = new Project();
$task = new Task();
$feedback = new Feedback();

$projects = $project->read();
$tasks = $task->read();
$unreadFeedback = $feedback->getUnreadCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lemon Hive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="../index.php" class="text-2xl font-bold text-yellow-500 hover:text-yellow-600">TechFlow</a>
                    </div>
                    <!-- Mobile menu button -->
                    <div class="flex items-center sm:hidden ml-4">
                        <button type="button" onclick="toggleMobileMenu()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-yellow-500">
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" class="border-yellow-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="projects.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Projects
                        </a>
                        <a href="tasks.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Tasks
                        </a>
                        <a href="profile.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Profile
                        </a>
                    </div>
                </div>
                <div class="hidden sm:flex items-center">
                    <span class="text-gray-700 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../logout.php" class="text-gray-700 hover:text-yellow-500 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile menu -->
    <div class="sm:hidden hidden" id="mobile-menu">
        <div class="pt-2 pb-3 space-y-1">
            <a href="index.php" class="bg-yellow-50 border-yellow-500 text-yellow-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Dashboard
            </a>
            <a href="projects.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Projects
            </a>
            <a href="tasks.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Tasks
            </a>
            <a href="profile.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Profile
            </a>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="../logout.php" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                    Logout
                </a>
            </div>
        </div>
    </div>

    <script>
    function toggleMobileMenu() {
        const mobileMenu = document.getElementById('mobile-menu');
        const isHidden = mobileMenu.classList.contains('hidden');
        
        if (isHidden) {
            mobileMenu.classList.remove('hidden');
        } else {
            mobileMenu.classList.add('hidden');
        }
    }
    </script>

    <!-- Main Content -->
    <div class="flex-1">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Projects Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Projects
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            <?php echo count($projects); ?>
                        </dd>
                    </div>
                </div>

                <!-- Tasks Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Tasks
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            <?php echo count($tasks); ?>
                        </dd>
                    </div>
                </div>

                <!-- Feedback Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Unread Feedback
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            <?php echo $unreadFeedback; ?>
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Admin Actions -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="mt-8">
                <h2 class="text-lg leading-6 font-medium text-gray-900">Admin Actions</h2>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="add_developer.php" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                    <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <h3 class="text-lg font-medium text-gray-900">Add Developer</h3>
                                    <p class="mt-1 text-sm text-gray-500">Create a new developer account</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Projects -->
            <div class="mt-8">
                <h2 class="text-lg leading-6 font-medium text-gray-900">Recent Projects</h2>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <?php 
                    $recentProjects = array_slice($projects, 0, 3);
                    foreach($recentProjects as $project): 
                    ?>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($project['title']); ?></h3>
                            <p class="mt-1 text-sm text-gray-500">Client: <?php echo htmlspecialchars($project['client_name']); ?></p>
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

            <!-- Recent Tasks -->
            <div class="mt-8">
                <h2 class="text-lg leading-6 font-medium text-gray-900">Recent Tasks</h2>
                <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        <?php 
                        $recentTasks = array_slice($tasks, 0, 5);
                        foreach($recentTasks as $task): 
                        ?>
                        <li>
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-yellow-500 truncate">
                                        <?php echo htmlspecialchars($task['task_desc']); ?>
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php echo $task['status'] === 'Done' ? 'bg-green-100 text-green-800' : 
                                                    ($task['status'] === 'In Progress' ? 'bg-blue-100 text-blue-800' : 
                                                    'bg-yellow-100 text-yellow-800'); ?>">
                                            <?php echo htmlspecialchars($task['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 sm:flex sm:justify-between">
                                    <div class="sm:flex">
                                        <p class="flex items-center text-sm text-gray-500">
                                            Assigned to: <?php echo htmlspecialchars($task['assigned_to_name'] ?? 'Unassigned'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php include '../components/footer.php'; ?>
</body>
</html> 