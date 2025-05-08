<?php
session_start();
require_once '../classes/Task.php';
require_once '../classes/Project.php';
require_once '../classes/User.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$task = new Task();
$project = new Project();
$user = new User();

// Get all projects for the filter
$projects = [];
if ($_SESSION['role'] === 'admin') {
    $projects = $project->getAllProjects();
} else {
    $projects = $project->getProjectsByUser($_SESSION['user_id']);
}

// Get all developers for the filter (only for admin)
$developers = [];
if ($_SESSION['role'] === 'admin') {
    $developers = $user->getDevelopers();
}

// Get filter values
$project_filter = isset($_GET['project']) && $_GET['project'] !== '' ? $_GET['project'] : '';
$status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';
$developer_filter = isset($_GET['developer']) && $_GET['developer'] !== '' ? $_GET['developer'] : '';

// Get tasks based on user role and filters
if ($_SESSION['role'] === 'admin') {
    $tasks = $task->getAllTasks($project_filter, $status_filter, $developer_filter);
} else {
    $tasks = $task->getTasksByUser($_SESSION['user_id'], $project_filter, $status_filter);
}

// Debug information
error_log("User Role: " . $_SESSION['role']);
error_log("User ID: " . $_SESSION['user_id']);
error_log("Tasks retrieved: " . print_r($tasks, true));

// Group tasks by project
$grouped_tasks = [];
foreach ($tasks as $task_item) {
    if (!isset($task_item['project_id'])) {
        error_log("Task missing project_id: " . print_r($task_item, true));
        continue;
    }
    
    $project_id = $task_item['project_id'];
    if (!isset($grouped_tasks[$project_id])) {
        $grouped_tasks[$project_id] = [
            'project_title' => $task_item['project_title'] ?? 'Unassigned Project',
            'tasks' => []
        ];
    }
    $grouped_tasks[$project_id]['tasks'][] = $task_item;
}

// Debug information
error_log("Grouped tasks: " . print_r($grouped_tasks, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - TechFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="../index.php" class="text-2xl font-bold text-yellow-500 hover:text-yellow-600">TechFlow</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="projects.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Projects
                        </a>
                        <a href="tasks.php" class="border-yellow-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Tasks
                        </a>
                        <a href="profile.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Profile
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="hidden sm:flex sm:items-center">
                        <span class="text-gray-700 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="../logout.php" class="text-gray-700 hover:text-yellow-500 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    </div>
                    <!-- Mobile menu button -->
                    <div class="flex items-center sm:hidden">
                        <button type="button" onclick="toggleMobileMenu()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-yellow-500">
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="sm:hidden hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="index.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Dashboard
                </a>
                <a href="projects.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Projects
                </a>
                <a href="tasks.php" class="bg-yellow-50 border-yellow-500 text-yellow-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
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
    </nav>

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
            <!-- Add Task Button for Admin -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="mb-6">
                <a href="add_task.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New Task
                </a>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-6">
                <div class="md:grid md:grid-cols-2 md:gap-6">
                    <div class="md:col-span-1">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Filters</h3>
                        <form action="" method="GET" class="mt-4 space-y-4">
                            <div class="flex flex-wrap gap-4 mb-4">
                                <select name="project" class="rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                    <option value="">All Projects</option>
                                    <?php foreach ($projects as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo $project_filter == $p['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                    <option value="">All Status</option>
                                    <option value="To Do" <?php echo $status_filter === 'To Do' ? 'selected' : ''; ?>>To Do</option>
                                    <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>

                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                <select name="developer" class="rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                    <option value="">All Developers</option>
                                    <?php foreach ($developers as $dev): ?>
                                        <option value="<?php echo $dev['id']; ?>" <?php echo $developer_filter == $dev['id'] ? 'selected' : ''; ?>>
                                            <?php 
                                            $display_name = !empty($dev['full_name']) ? $dev['full_name'] : $dev['username'];
                                            echo htmlspecialchars($display_name); 
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php endif; ?>

                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <?php if (empty($grouped_tasks)): ?>
                <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
                    No tasks found.
                </div>
                <?php else: ?>
                <?php foreach ($grouped_tasks as $project_id => $project_data): ?>
                <div class="border-b border-gray-200 last:border-b-0">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <?php echo htmlspecialchars($project_data['project_title']); ?>
                        </h3>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($project_data['tasks'] as $task_item): ?>
                        <li class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        <?php echo htmlspecialchars($task_item['task_desc']); ?>
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Assigned to: <?php echo htmlspecialchars($task_item['assigned_to_name'] ?? 'Unassigned'); ?>
                                    </p>
                                </div>
                                <div class="ml-4 flex-shrink-0 flex items-center space-x-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch($task_item['status']) {
                                            case 'To-Do':
                                                echo 'bg-gray-100 text-gray-800';
                                                break;
                                            case 'In Progress':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'Done':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                        }
                                        ?>">
                                        <?php echo htmlspecialchars($task_item['status']); ?>
                                    </span>
                                    <?php if ($task->canEditTask($_SESSION['user_id'], $_SESSION['role'])): ?>
                                    <a href="edit_task.php?id=<?php echo $task_item['id']; ?>" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($task->canDeleteTask($_SESSION['user_id'], $_SESSION['role'])): ?>
                                    <button onclick="deleteTask(<?php echo $task_item['id']; ?>)" class="text-red-600 hover:text-red-900">Delete</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include '../components/footer.php'; ?>
    <script>
    function deleteTask(id) {
        if (confirm('Are you sure you want to delete this task?')) {
            window.location.href = `delete_task.php?id=${id}`;
        }
    }
    </script>
</body>
</html> 