<?php
session_start();
require_once '../classes/Task.php';
require_once '../classes/Project.php';
require_once '../classes/User.php';

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$task = new Task();
$project = new Project();
$user = new User();

// Get all projects and developers for the form
$projects = $project->read();
$developers = $user->getDevelopers();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? '';
    $task_desc = $_POST['task_desc'] ?? '';
    $assigned_to = $_POST['assigned_to'] ?? null;
    $status = $_POST['status'] ?? 'To-Do';

    if (empty($project_id) || empty($task_desc)) {
        $error = 'Project and task description are required';
    } else {
        $result = $task->create([
            'project_id' => $project_id,
            'task_desc' => $task_desc,
            'assigned_to' => $assigned_to,
            'status' => $status
        ]);

        if ($result) {
            $success = 'Task created successfully';
            // Clear form data after successful submission
            $project_id = '';
            $task_desc = '';
            $assigned_to = '';
            $status = 'To-Do';
        } else {
            $error = 'Failed to create task';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Task - TechFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../logout.php" class="text-gray-700 hover:text-yellow-500 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Add New Task</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Create a new task and assign it to a developer.
                        </p>
                    </div>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form action="add_task.php" method="POST">
                        <div class="shadow sm:rounded-md sm:overflow-hidden">
                            <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                                <?php if ($error): ?>
                                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($success): ?>
                                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div>
                                    <label for="project_id" class="block text-sm font-medium text-gray-700">Project</label>
                                    <select id="project_id" name="project_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                        <option value="">Select a project</option>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?php echo $project['id']; ?>">
                                                <?php echo htmlspecialchars($project['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label for="task_desc" class="block text-sm font-medium text-gray-700">Task Description</label>
                                    <textarea id="task_desc" name="task_desc" rows="3" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm"></textarea>
                                </div>

                                <div>
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign To</label>
                                    <select id="assigned_to" name="assigned_to" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                        <option value="">Unassigned</option>
                                        <?php foreach ($developers as $developer): ?>
                                            <option value="<?php echo $developer['id']; ?>">
                                                <?php echo htmlspecialchars($developer['username']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select id="status" name="status" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                        <option value="To-Do" <?php echo ($status ?? '') === 'To-Do' ? 'selected' : ''; ?>>To-Do</option>
                                        <option value="In Progress" <?php echo ($status ?? '') === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Done" <?php echo ($status ?? '') === 'Done' ? 'selected' : ''; ?>>Done</option>
                                    </select>
                                </div>
                            </div>
                            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                <a href="tasks.php" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 mr-3">
                                    Cancel
                                </a>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    Create Task
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include '../components/footer.php'; ?>
</body>
</html> 