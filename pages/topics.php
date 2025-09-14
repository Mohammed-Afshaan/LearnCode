<?php
session_start();
require_once '../config.php';

// Connect (mysqli)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle filter/search
$language = $_GET['language'] ?? '';
$search   = trim($_GET['search'] ?? '');

// Build query
$query  = "SELECT * FROM topics WHERE 1=1";
$types  = "";
$params = [];

if ($language !== '') {
    $query  .= " AND language = ?";
    $types  .= "s";
    $params[] = $language;
}

if ($search !== '') {
    $query  .= " AND (title LIKE ? OR description LIKE ?)";
    $types  .= "ss";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($query);

if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch all languages for dropdown dynamically
$langQuery  = "SELECT DISTINCT language FROM topics ORDER BY language ASC";
$langResult = $conn->query($langQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learning Topics - W3Clone</title>
  <link href="../assets/css/globals.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .code-snippet {
      background: #1e293b;
      color: #e2e8f0;
      font-family: 'Courier New', monospace;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">

  <!-- Header -->
  <?php include '../includes/header.php'; ?>

  <div class="flex">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 min-h-screen">
      <div class="max-w-5xl mx-auto p-6">

        <!-- Page Header -->
        <div class="mb-8">
          <h1 class="text-4xl font-bold text-gray-800 mb-2">Learning Topics</h1>
          <p class="text-gray-600">Explore our comprehensive programming tutorials and examples</p>
        </div>

        <!-- Search and Filter -->
        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-4 mb-8 bg-white p-6 rounded-xl shadow-lg border">
          <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Topics</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
              placeholder="🔍 Search by title or description..."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
          </div>

          <div class="w-full md:w-48">
            <label for="language" class="block text-sm font-medium text-gray-700 mb-1">Filter by Language</label>
            <select id="language" name="language" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              <option value="">All Languages</option>
              <?php if ($langResult && $langResult->num_rows > 0): ?>
                <?php while ($langRow = $langResult->fetch_assoc()): ?>
                  <option value="<?= htmlspecialchars($langRow['language']) ?>" <?= $language === $langRow['language'] ? "selected" : "" ?>>
                    <?= htmlspecialchars($langRow['language']) ?>
                  </option>
                <?php endwhile; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="flex gap-2 md:pt-6">
            <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
              <i class="fas fa-search mr-2"></i>Search
            </button>
            <a href="topics.php" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
              <i class="fas fa-times mr-2"></i>Clear
            </a>
          </div>
        </form>

        <!-- Results Count -->
        <?php 
        $total_results = $result ? $result->num_rows : 0;
        ?>
        <div class="mb-6">
          <p class="text-gray-600">
            Found <span class="font-semibold"><?= $total_results ?></span> topic<?= $total_results !== 1 ? 's' : '' ?>
            <?php if ($language): ?>
              in <span class="font-semibold"><?= htmlspecialchars($language) ?></span>
            <?php endif; ?>
            <?php if ($search): ?>
              matching "<span class="font-semibold"><?= htmlspecialchars($search) ?></span>"
            <?php endif; ?>
          </p>
        </div>

        <!-- Lessons -->
        <div id="lesson-content" class="space-y-6">
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <div id="topic-<?= $row['id'] ?>" class="p-6 bg-white border border-gray-200 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                
                <!-- Topic Header -->
                <div class="flex justify-between items-start mb-4">
                  <div class="flex items-center gap-3">
                    <span class="px-3 py-1 text-sm font-semibold bg-purple-100 text-purple-700 rounded-full">
                      <?= htmlspecialchars($row['language']) ?>
                    </span>
                    <?php if (isset($row['difficulty'])): ?>
                      <span class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-full">
                        <?= htmlspecialchars($row['difficulty']) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <span class="text-sm text-gray-500">
                    <i class="far fa-calendar-alt mr-1"></i>
                    <?= date('M j, Y', strtotime($row['created_at'])) ?>
                  </span>
                </div>

                <!-- Topic Content -->
                <h2 class="text-2xl font-bold text-gray-800 mb-3 hover:text-purple-600 transition">
                  <?= htmlspecialchars($row['title']) ?>
                </h2>
                
                <p class="text-gray-700 mb-4 leading-relaxed">
                  <?= htmlspecialchars($row['description']) ?>
                </p>

                <!-- Code Snippet -->
                <?php if (!empty($row['code_snippet'])): ?>
                  <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                      <h4 class="text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-code mr-2"></i>Code Example
                      </h4>
                      <button onclick="copyCode('code-<?= $row['id'] ?>')" class="text-sm text-gray-500 hover:text-gray-700 transition">
                        <i class="far fa-copy mr-1"></i>Copy
                      </button>
                    </div>
                    <pre id="code-<?= $row['id'] ?>" class="code-snippet text-sm p-4 rounded-lg overflow-x-auto border"><code><?= htmlspecialchars($row['code_snippet']) ?></code></pre>
                  </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                  <div class="flex gap-2">
                    <button class="flex items-center gap-2 text-gray-600 hover:text-purple-600 transition">
                      <i class="far fa-heart"></i>
                      <span class="text-sm">Like</span>
                    </button>
                    <button class="flex items-center gap-2 text-gray-600 hover:text-purple-600 transition">
                      <i class="far fa-bookmark"></i>
                      <span class="text-sm">Save</span>
                    </button>
                    <button class="flex items-center gap-2 text-gray-600 hover:text-purple-600 transition">
                      <i class="far fa-share-alt"></i>
                      <span class="text-sm">Share</span>
                    </button>
                  </div>
                  <a href="#topic-<?= $row['id'] ?>" class="text-purple-600 hover:text-purple-800 font-semibold text-sm">
                    <i class="fas fa-link mr-1"></i>Permalink
                  </a>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="p-12 text-center text-gray-500 bg-white rounded-xl shadow-lg">
              <div class="text-6xl mb-4 text-gray-300">
                <i class="fas fa-search"></i>
              </div>
              <h3 class="text-xl font-semibold mb-2">No topics found</h3>
              <p class="mb-6">Try adjusting your search terms or filter options</p>
              <a href="topics.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
                View All Topics
              </a>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
          <!-- Load More Button (for future pagination) -->
          <div class="text-center mt-12">
            <button class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
              Load More Topics
            </button>
          </div>
        <?php endif; ?>

      </div>
    </main>
  </div>

  <!-- Footer -->
  <?php include '../includes/footer.php'; ?>

  <script src="../assets/js/main.js"></script>
  <script>
    // Copy code functionality
    function copyCode(elementId) {
      const codeElement = document.getElementById(elementId);
      const text = codeElement.textContent;
      
      navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const button = codeElement.parentElement.querySelector('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
        button.classList.add('text-green-600');
        
        setTimeout(() => {
          button.innerHTML = originalText;
          button.classList.remove('text-green-600');
        }, 2000);
      });
    }
  </script>

</body>
</html>

<?php
$conn->close();
?>