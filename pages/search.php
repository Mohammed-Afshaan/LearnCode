<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

$query = sanitize_input($_GET['q'] ?? '');
$language_filter = sanitize_input($_GET['language'] ?? '');
$difficulty_filter = sanitize_input($_GET['difficulty'] ?? '');
$results = [];
$total_results = 0;

if (!empty($query)) {
    // Build search query
    $search_sql = "SELECT t.*, u.username as author_name,
                          MATCH(t.title, t.description, t.content, t.tags) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                   FROM topics t 
                   LEFT JOIN users u ON t.author_id = u.id 
                   WHERE t.is_published = 1 
                   AND (t.title LIKE ? OR t.description LIKE ? OR t.content LIKE ? OR t.tags LIKE ? 
                        OR MATCH(t.title, t.description, t.content, t.tags) AGAINST(? IN NATURAL LANGUAGE MODE))";
    
    $params = [$query, "%$query%", "%$query%", "%$query%", "%$query%", $query];
    $types = 'ssssss';
    
    // Add language filter
    if ($language_filter) {
        $search_sql .= " AND t.language = ?";
        $params[] = $language_filter;
        $types .= 's';
    }
    
    // Add difficulty filter
    if ($difficulty_filter) {
        $search_sql .= " AND t.difficulty = ?";
        $params[] = $difficulty_filter;
        $types .= 's';
    }
    
    $search_sql .= " ORDER BY relevance DESC, t.view_count DESC, t.created_at DESC LIMIT 20";
    
    $result = executeQuery($search_sql, $types, $params);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $total_results = count($results);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= !empty($query) ? 'Search: ' . htmlspecialchars($query) : 'Search' ?> - W3Clone</title>
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto px-6 py-8">
        
        <!-- Search Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-4">
                <?php if (!empty($query)): ?>
                    Search Results for "<?= htmlspecialchars($query) ?>"
                <?php else: ?>
                    Search Tutorials
                <?php endif; ?>
            </h1>
            
            <?php if (!empty($query)): ?>
                <p class="text-gray-600">
                    Found <?= $total_results ?> result<?= $total_results !== 1 ? 's' : '' ?>
                    <?php if ($language_filter): ?>
                        in <?= htmlspecialchars($language_filter) ?>
                    <?php endif; ?>
                    <?php if ($difficulty_filter): ?>
                        for <?= htmlspecialchars($difficulty_filter) ?> level
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input 
                        type="text" 
                        name="q" 
                        value="<?= htmlspecialchars($query) ?>"
                        placeholder="Search for tutorials, topics, or keywords..."
                        class="form-input w-full text-lg py-3"
                        autofocus
                    >
                </div>
                
                <div class="md:w-48">
                    <select name="language" class="form-select w-full py-3">
                        <option value="">All Languages</option>
                        <option value="HTML" <?= $language_filter === 'HTML' ? 'selected' : '' ?>>HTML</option>
                        <option value="CSS" <?= $language_filter === 'CSS' ? 'selected' : '' ?>>CSS</option>
                        <option value="JavaScript" <?= $language_filter === 'JavaScript' ? 'selected' : '' ?>>JavaScript</option>
                        <option value="PHP" <?= $language_filter === 'PHP' ? 'selected' : '' ?>>PHP</option>
                        <option value="Python" <?= $language_filter === 'Python' ? 'selected' : '' ?>>Python</option>
                        <option value="SQL" <?= $language_filter === 'SQL' ? 'selected' : '' ?>>SQL</option>
                    </select>
                </div>
                
                <div class="md:w-48">
                    <select name="difficulty" class="form-select w-full py-3">
                        <option value="">All Levels</option>
                        <option value="Beginner" <?= $difficulty_filter === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="Intermediate" <?= $difficulty_filter === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="Advanced" <?= $difficulty_filter === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary px-8 py-3">
                    <i class="fas fa-search mr-2"></i>
                    Search
                </button>
            </form>
        </div>

        <?php if (!empty($query)): ?>
            
            <!-- Search Results -->
            <?php if (empty($results)): ?>
                <div class="text-center py-16">
                    <div class="text-6xl text-gray-300 mb-4">
                        <i class="fas fa-search"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-600 mb-4">No Results Found</h2>
                    <p class="text-gray-600 mb-8">
                        We couldn't find any tutorials matching "<?= htmlspecialchars($query) ?>". 
                        Try different keywords or browse our categories.
                    </p>
                    <div class="space-x-4">
                        <a href="topics.php" class="btn btn-primary">
                            Browse All Tutorials
                        </a>
                        <button onclick="document.querySelector('input[name=q]').value = ''; document.querySelector('form').submit();" class="btn btn-outline">
                            Clear Search
                        </button>
                    </div>
                </div>
            <?php else: ?>
                
                <div class="space-y-6">
                    <?php foreach ($results as $result): ?>
                        <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
                            
                            <!-- Topic Header -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 text-sm font-semibold bg-purple-100 text-purple-700 rounded-full">
                                        <?= htmlspecialchars($result['language']) ?>
                                    </span>
                                    <span class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-full">
                                        <?= htmlspecialchars($result['difficulty']) ?>
                                    </span>
                                </div>
                                <span class="text-sm text-gray-500">
                                    <i class="far fa-eye mr-1"></i>
                                    <?= number_format($result['view_count']) ?> views
                                </span>
                            </div>

                            <!-- Topic Title -->
                            <h2 class="text-xl font-bold text-gray-800 mb-3 hover:text-purple-600 transition">
                                <a href="topic-detail.php?slug=<?= htmlspecialchars($result['slug']) ?>">
                                    <?= htmlspecialchars($result['title']) ?>
                                </a>
                            </h2>
                            
                            <!-- Description -->
                            <p class="text-gray-700 mb-4 leading-relaxed">
                                <?= excerpt($result['description'], 200) ?>
                            </p>

                            <!-- Code Snippet Preview -->
                            <?php if (!empty($result['code_snippet'])): ?>
                                <div class="mb-4">
                                    <div class="bg-gray-900 text-green-400 text-sm p-4 rounded-lg overflow-x-auto">
                                        <code><?= htmlspecialchars(substr($result['code_snippet'], 0, 150)) ?><?= strlen($result['code_snippet']) > 150 ? '...' : '' ?></code>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Footer -->
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <div class="text-sm text-gray-600">
                                    By <?= htmlspecialchars($result['author_name']) ?> • 
                                    <?= formatDate($result['created_at']) ?>
                                </div>
                                <a href="topic-detail.php?slug=<?= htmlspecialchars($result['slug']) ?>" 
                                   class="text-purple-600 hover:text-purple-800 font-semibold text-sm">
                                    Read More →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        <?php else: ?>
            
            <!-- Popular Topics -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Popular Topics</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="topics.php?language=HTML" class="block p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4">
                            <i class="fab fa-html5 text-3xl text-orange-600"></i>
                            <div>
                                <h3 class="font-bold text-lg">HTML</h3>
                                <p class="text-gray-600 text-sm">Structure your web content</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="topics.php?language=CSS" class="block p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4">
                            <i class="fab fa-css3-alt text-3xl text-blue-600"></i>
                            <div>
                                <h3 class="font-bold text-lg">CSS</h3>
                                <p class="text-gray-600 text-sm">Style your web pages</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="topics.php?language=JavaScript" class="block p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4">
                            <i class="fab fa-js-square text-3xl text-yellow-600"></i>
                            <div>
                                <h3 class="font-bold text-lg">JavaScript</h3>
                                <p class="text-gray-600 text-sm">Add interactivity</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="topics.php?language=PHP" class="block p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4">
                            <i class="fab fa-php text-3xl text-purple-600"></i>
                            <div>
                                <h3 class="font-bold text-lg">PHP</h3>
                                <p class="text-gray-600 text-sm">Server-side programming</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="topics.php?language=Python" class="block p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4">
                            <i class="fab fa-python text-3xl text-blue-800"></i>
                            <div>
                                <h3 class="font-bold text-lg">Python</h3>
                                <p class="text-gray-600 text-sm">Versatile programming</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="topics.php?language=SQL" class="block p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4">
                            <i class="fas fa-database text-3xl text-green-600"></i>
                            <div>
                                <h3 class="font-bold text-lg">SQL</h3>
                                <p class="text-gray-600 text-sm">Database queries</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Search Tips -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-bold text-blue-800 mb-3">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Search Tips
                </h3>
                <ul class="text-blue-700 space-y-2">
                    <li>• Use specific keywords like "CSS flexbox" or "JavaScript arrays"</li>
                    <li>• Try different variations of your search terms</li>
                    <li>• Use the language and difficulty filters to narrow results</li>
                    <li>• Search for concepts like "responsive design" or "form validation"</li>
                </ul>
            </div>

        <?php endif; ?>

    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Highlight search terms in results
        document.addEventListener('DOMContentLoaded', function() {
            const query = '<?= addslashes($query) ?>';
            if (query.length > 0) {
                const results = document.querySelectorAll('.bg-white h2, .bg-white p');
                results.forEach(element => {
                    const regex = new RegExp(`(${query})`, 'gi');
                    element.innerHTML = element.innerHTML.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
                });
            }
        });
    </script>

</body>
</html>