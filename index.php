<?php
session_start();
require_once './config.php';

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get featured topics for homepage
$featuredQuery = "SELECT * FROM topics ORDER BY created_at DESC LIMIT 6";
$featuredResult = $conn->query($featuredQuery);

// Get topic count by language
$statsQuery = "SELECT language, COUNT(*) as count FROM topics GROUP BY language ORDER BY count DESC LIMIT 5";
$statsResult = $conn->query($statsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LearnCode - Learn Programming</title>
  <link href="./assets/css/globals.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .card-hover {
      transition: all 0.3s ease;
    }
    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">

  <!-- Header -->
  <?php include './includes/header.php'; ?>

  <!-- Hero Section -->
  <section class="gradient-bg text-white py-20">
    <div class="max-w-6xl mx-auto px-6 text-center">
      <h1 class="text-5xl md:text-6xl font-bold mb-6">
        Learn to <span class="text-yellow-300">Code</span>
      </h1>
      <p class="text-xl md:text-2xl mb-8 opacity-90">
        Master programming with our comprehensive tutorials and examples
      </p>
      <div class="flex justify-center gap-4 mb-12">
        <a href="pages/topics.php" class="bg-white text-purple-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition">
          Start Learning
        </a>
        <a href="pages/register.php" class="border-2 border-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-purple-600 transition">
          Sign Up Free
        </a>
      </div>
      
      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16">
        <div class="text-center">
          <div class="text-4xl font-bold text-yellow-300">50K+</div>
          <div class="text-lg opacity-90">Students</div>
        </div>
        <div class="text-center">
          <div class="text-4xl font-bold text-yellow-300">
            <?php 
            $totalQuery = "SELECT COUNT(*) as total FROM topics";
            $totalResult = $conn->query($totalQuery);
            $total = $totalResult->fetch_assoc()['total'];
            echo $total;
            ?>+
          </div>
          <div class="text-lg opacity-90">Lessons</div>
        </div>
        <div class="text-center">
          <div class="text-4xl font-bold text-yellow-300">24/7</div>
          <div class="text-lg opacity-90">Support</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Popular Languages -->
  <section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-6">
      <h2 class="text-4xl font-bold text-center mb-12">Popular Programming Languages</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <div class="card-hover bg-orange-50 border-2 border-orange-200 rounded-xl p-6 text-center">
          <div class="text-5xl mb-4">
            <i class="fab fa-html5 text-orange-600"></i>
          </div>
          <h3 class="text-2xl font-bold mb-2">HTML</h3>
          <p class="text-gray-600 mb-4">Structure your web content</p>
          <a href="pages/topics.php?language=HTML" class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition">
            Learn HTML
          </a>
        </div>

        <div class="card-hover bg-blue-50 border-2 border-blue-200 rounded-xl p-6 text-center">
          <div class="text-5xl mb-4">
            <i class="fab fa-css3-alt text-blue-600"></i>
          </div>
          <h3 class="text-2xl font-bold mb-2">CSS</h3>
          <p class="text-gray-600 mb-4">Style your web pages</p>
          <a href="pages/topics.php?language=CSS" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            Learn CSS
          </a>
        </div>

        <div class="card-hover bg-yellow-50 border-2 border-yellow-200 rounded-xl p-6 text-center">
          <div class="text-5xl mb-4">
            <i class="fab fa-js-square text-yellow-600"></i>
          </div>
          <h3 class="text-2xl font-bold mb-2">JavaScript</h3>
          <p class="text-gray-600 mb-4">Add interactivity to web</p>
          <a href="pages/topics.php?language=JavaScript" class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700 transition">
            Learn JavaScript
          </a>
        </div>

        <div class="card-hover bg-purple-50 border-2 border-purple-200 rounded-xl p-6 text-center">
          <div class="text-5xl mb-4">
            <i class="fab fa-php text-purple-600"></i>
          </div>
          <h3 class="text-2xl font-bold mb-2">PHP</h3>
          <p class="text-gray-600 mb-4">Server-side programming</p>
          <a href="pages/topics.php?language=PHP" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
            Learn PHP
          </a>
        </div>

        <div class="card-hover bg-green-50 border-2 border-green-200 rounded-xl p-6 text-center">
          <div class="text-5xl mb-4">
            <i class="fas fa-database text-green-600"></i>
          </div>
          <h3 class="text-2xl font-bold mb-2">SQL</h3>
          <p class="text-gray-600 mb-4">Manage your databases</p>
          <a href="pages/topics.php?language=SQL" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
            Learn SQL
          </a>
        </div>

        <div class="card-hover bg-blue-50 border-2 border-blue-200 rounded-xl p-6 text-center">
          <div class="text-5xl mb-4">
            <i class="fab fa-python text-blue-800"></i>
          </div>
          <h3 class="text-2xl font-bold mb-2">Python</h3>
          <p class="text-gray-600 mb-4">Versatile programming</p>
          <a href="pages/topics.php?language=Python" class="bg-blue-800 text-white px-6 py-2 rounded-lg hover:bg-blue-900 transition">
            Learn Python
          </a>
        </div>

      </div>
    </div>
  </section>

  <!-- Featured Topics -->
  <section class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-6">
      <h2 class="text-4xl font-bold text-center mb-12">Featured Lessons</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if ($featuredResult && $featuredResult->num_rows > 0): ?>
          <?php while ($topic = $featuredResult->fetch_assoc()): ?>
            <div class="card-hover bg-white rounded-xl shadow-lg p-6 border">
              <div class="flex justify-between items-start mb-3">
                <span class="px-3 py-1 text-sm bg-blue-100 text-blue-600 rounded-full">
                  <?= htmlspecialchars($topic['language']) ?>
                </span>
                <span class="text-xs text-gray-500">
                  <?= date('M j, Y', strtotime($topic['created_at'])) ?>
                </span>
              </div>
              <h3 class="text-xl font-bold mb-3"><?= htmlspecialchars($topic['title']) ?></h3>
              <p class="text-gray-600 line-clamp-2 mb-4">
                <?= htmlspecialchars($topic['description']) ?>
              </p>
              <a href="pages/topics.php#topic-<?= $topic['id'] ?>" class="text-blue-600 hover:text-blue-800 font-semibold">
                Read More →
              </a>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
      <div class="text-center mt-12">
        <a href="pages/topics.php" class="bg-purple-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-purple-700 transition">
          View All Lessons
        </a>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-6">
      <h2 class="text-4xl font-bold text-center mb-12">Why Choose W3Clone?</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div class="text-center">
          <div class="text-4xl mb-4 text-purple-600">
            <i class="fas fa-rocket"></i>
          </div>
          <h3 class="text-xl font-bold mb-2">Fast Learning</h3>
          <p class="text-gray-600">Quick tutorials to get you started</p>
        </div>
        <div class="text-center">
          <div class="text-4xl mb-4 text-purple-600">
            <i class="fas fa-code"></i>
          </div>
          <h3 class="text-xl font-bold mb-2">Code Examples</h3>
          <p class="text-gray-600">Real working code snippets</p>
        </div>
        <div class="text-center">
          <div class="text-4xl mb-4 text-purple-600">
            <i class="fas fa-users"></i>
          </div>
          <h3 class="text-xl font-bold mb-2">Community</h3>
          <p class="text-gray-600">Learn with thousands of students</p>
        </div>
        <div class="text-center">
          <div class="text-4xl mb-4 text-purple-600">
            <i class="fas fa-certificate"></i>
          </div>
          <h3 class="text-xl font-bold mb-2">Certificates</h3>
          <p class="text-gray-600">Get certified in your skills</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <?php include './includes/footer.php'; ?>

  <script src="./assets/js/main.js"></script>
</body>
</html>

<?php
$conn->close();
?>