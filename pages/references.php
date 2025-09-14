<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>References - LearnCode</title>
    <meta name="description" content="Quick reference guides for HTML, CSS, JavaScript, PHP, Python, SQL, and more.">
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <section class="bg-gradient-to-r from-gray-900 to-black text-white py-16">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-4xl font-bold mb-4">Programming References</h1>
            <p class="text-xl text-gray-300">
                Quick reference guides for HTML, CSS, JavaScript, PHP, Python, SQL, and more.
            </p>
        </div>
    </section>

    <div class="container mx-auto px-6 py-16">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            <!-- HTML Reference -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-html5 text-orange-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">HTML Reference</h3>
                <p class="text-gray-600 mb-4">
                    Tags, attributes, forms, tables, and more.
                </p>
                <a href="topics.php?language=HTML" class="btn btn-primary">View HTML Reference</a>
            </div>
            <!-- CSS Reference -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-css3-alt text-blue-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">CSS Reference</h3>
                <p class="text-gray-600 mb-4">
                    Properties, selectors, flexbox, grid, and more.
                </p>
                <a href="topics.php?language=CSS" class="btn btn-primary">View CSS Reference</a>
            </div>
            <!-- JavaScript Reference -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-js-square text-yellow-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">JavaScript Reference</h3>
                <p class="text-gray-600 mb-4">
                    Syntax, DOM, events, functions, and more.
                </p>
                <a href="topics.php?language=JavaScript" class="btn btn-primary">View JS Reference</a>
            </div>
            <!-- PHP Reference -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-php text-purple-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">PHP Reference</h3>
                <p class="text-gray-600 mb-4">
                    Syntax, functions, arrays, forms, and more.
                </p>
                <a href="topics.php?language=PHP" class="btn btn-primary">View PHP Reference</a>
            </div>
            <!-- Python Reference -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-python text-blue-800"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">Python Reference</h3>
                <p class="text-gray-600 mb-4">
                    Syntax, data types, functions, modules, and more.
                </p>
                <a href="topics.php?language=Python" class="btn btn-primary">View Python Reference</a>
            </div>
            <!-- SQL Reference -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fas fa-database text-green-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">SQL Reference</h3>
                <p class="text-gray-600 mb-4">
                    Queries, joins, functions, and more.
                </p>
                <a href="topics.php?language=SQL" class="btn btn-primary">View SQL Reference</a>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

</body>
</html>