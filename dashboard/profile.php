<?php
session_start();
require_once '../classes/User.php';
require_once '../classes/Task.php';
require_once '../classes/Project.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user = new User();
$task = new Task();
$project = new Project();

$error = '';
$success = '';

// Get user data
$userData = $user->read($_SESSION['user_id']);

// Debug information
error_log("User Data: " . print_r($userData, true));
error_log("Session User ID: " . $_SESSION['user_id']);

// Get user statistics only for developers
$totalTasks = 0;
$completedTasks = 0;
$inProgressTasks = 0;
$todoTasks = 0;
$totalProjects = 0;

if ($_SESSION['role'] === 'developer') {
    $totalTasks = $task->getTotalTasksByUser($_SESSION['user_id']);
    $completedTasks = $task->getCompletedTasksByUser($_SESSION['user_id']);
    $inProgressTasks = $task->getInProgressTasksByUser($_SESSION['user_id']);
    $todoTasks = $task->getTodoTasksByUser($_SESSION['user_id']);
    $totalProjects = $project->getTotalProjectsByUser($_SESSION['user_id']);
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $data = [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'full_name' => $_POST['full_name']
        ];
        
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_picture']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'profile_' . $_SESSION['user_id'] . '.' . $ext;
                $upload_dir = __DIR__ . '/../uploads/profile_pictures/';
                $upload_path = $upload_dir . $new_filename;
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Remove old profile picture if it exists
                if (!empty($userData['profile_picture'])) {
                    $old_file = $upload_dir . $userData['profile_picture'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                    $data['profile_picture'] = $new_filename;
                    $success = 'Profile picture uploaded successfully';
                } else {
                    $error = 'Failed to upload profile picture. Please try again.';
                }
            } else {
                $error = 'Invalid file type. Please upload a JPG, JPEG, PNG, or GIF image.';
            }
        }
        
        if (empty($error) && $user->updateProfile($_SESSION['user_id'], $data)) {
            $success = 'Profile updated successfully';
            $userData = $user->read($_SESSION['user_id']); // Refresh user data
        } else if (empty($error)) {
            $error = 'Failed to update profile';
        }
    }
    
    // Handle password update
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } else {
            if ($user->updatePassword($_SESSION['user_id'], $current_password, $new_password)) {
                $success = 'Password updated successfully';
            } else {
                $error = 'Current password is incorrect';
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
    <title>Profile - Lemon Hive</title>
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
                        <a href="tasks.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Tasks
                        </a>
                        <a href="profile.php" class="border-yellow-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Profile
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
            <?php if ($error): ?>
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <div class="md:grid md:grid-cols-3 md:gap-6">
                <!-- Profile Information -->
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="text-center">
                                <?php 
                                // Debug information
                                echo "<!-- Debug Info:\n";
                                echo "User Data: " . print_r($userData, true) . "\n";
                                echo "Profile Picture: " . ($userData['profile_picture'] ?? 'Not set') . "\n";
                                echo "Upload Directory: " . __DIR__ . '/../uploads/profile_pictures/' . "\n";
                                echo "-->";
                                
                                $profile_pic = !empty($userData['profile_picture']) ? $userData['profile_picture'] : null;
                                if ($profile_pic) {
                                    $full_path = __DIR__ . '/../uploads/profile_pictures/' . $profile_pic;
                                    echo "<!-- File Info:\n";
                                    echo "Profile Picture: " . $profile_pic . "\n";
                                    echo "Full Path: " . $full_path . "\n";
                                    echo "File Exists: " . (file_exists($full_path) ? 'Yes' : 'No') . "\n";
                                    echo "File Size: " . (file_exists($full_path) ? filesize($full_path) : 'N/A') . "\n";
                                    echo "-->";
                                }
                                ?>
                                <?php if ($profile_pic): ?>
                                    <img src="<?php echo '/Intern/uploads/profile_pictures/' . htmlspecialchars($profile_pic); ?>" 
                                         alt="Profile Picture" 
                                         class="mx-auto h-32 w-32 rounded-full object-cover"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'mx-auto h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center\'><span class=\'text-4xl text-gray-500\'><?php echo strtoupper(substr($userData['username'], 0, 1)); ?></span></div>'">
                                <?php else: ?>
                                    <div class="mx-auto h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-4xl text-gray-500"><?php echo strtoupper(substr($userData['username'], 0, 1)); ?></span>
                                    </div>
                                <?php endif; ?>
                                <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo htmlspecialchars($userData['full_name'] ?? $userData['username']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($userData['role']); ?></p>
                            </div>
                            
                            <!-- Statistics -->
                            <?php if ($_SESSION['role'] === 'developer'): ?>
                            <div class="mt-6 border-t border-gray-200 pt-6">
                                <dl class="grid grid-cols-1 gap-4">
                                    <div class="bg-gray-50 px-4 py-3 rounded-lg">
                                        <dt class="text-sm font-medium text-gray-500">Total Tasks</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900"><?php echo $totalTasks; ?></dd>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 rounded-lg">
                                        <dt class="text-sm font-medium text-gray-500">Completed Tasks</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-green-600"><?php echo $completedTasks; ?></dd>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 rounded-lg">
                                        <dt class="text-sm font-medium text-gray-500">In Progress Tasks</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-yellow-600"><?php echo $inProgressTasks; ?></dd>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 rounded-lg">
                                        <dt class="text-sm font-medium text-gray-500">To-Do Tasks</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-600"><?php echo $todoTasks; ?></dd>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 rounded-lg">
                                        <dt class="text-sm font-medium text-gray-500">Total Projects</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-blue-600"><?php echo $totalProjects; ?></dd>
                                    </div>
                                </dl>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Profile Update Forms -->
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Update Profile Form -->
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="space-y-6">
                                    <div>
                                        <h3 class="text-lg leading-6 font-medium text-gray-900">Profile Information</h3>
                                        <p class="mt-1 text-sm text-gray-500">Update your profile information and photo.</p>
                                    </div>

                                    <div>
                                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="profile_picture" class="block text-sm font-medium text-gray-700">Profile Picture</label>
                                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="mt-1 block w-full">
                                    </div>

                                    <div>
                                        <button type="submit" name="update_profile" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            Update Profile
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Update Password Form -->
                            <div class="mt-8 pt-8 border-t border-gray-200">
                                <form action="" method="POST">
                                    <div class="space-y-6">
                                        <div>
                                            <h3 class="text-lg leading-6 font-medium text-gray-900">Update Password</h3>
                                            <p class="mt-1 text-sm text-gray-500">Change your password.</p>
                                        </div>

                                        <div>
                                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                            <input type="password" name="current_password" id="current_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                        </div>

                                        <div>
                                            <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                                            <input type="password" name="new_password" id="new_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                        </div>

                                        <div>
                                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                            <input type="password" name="confirm_password" id="confirm_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                        </div>

                                        <div>
                                            <button type="submit" name="update_password" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                Update Password
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../components/footer.php'; ?>
</body>
</html> 