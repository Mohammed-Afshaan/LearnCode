<!-- filepath: c:\xampp\htdocs\W3Clone(Final)\includes\admin_header.php -->
<header class="bg-gradient-to-r from-purple-700 via-purple-600 to-purple-800 text-white px-6 py-4 flex justify-between items-center shadow-md">
    <div class="flex items-center gap-3">
        <i class="fas fa-shield-alt text-yellow-300 text-2xl"></i>
        <span class="text-2xl font-bold tracking-wide drop-shadow">Admin Panel - <?= defined('SITE_NAME') ? SITE_NAME : 'LearnCode' ?></span>
    </div>
    <div class="flex items-center gap-6">
        <span class="flex items-center gap-2 text-lg">
            <i class="fas fa-user-circle text-yellow-200"></i>
            <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
        </span>
        <a href="logout.php" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-yellow-400 hover:bg-yellow-500 transition text-purple-900 font-semibold text-base shadow">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>