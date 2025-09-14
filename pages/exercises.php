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
    <title>Exercises - LearnCode</title>
    <meta name="description" content="Practice programming with interactive exercises for HTML, CSS, JavaScript, PHP, Python, SQL, and more.">
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <section class="bg-gradient-to-r from-gray-900 to-black text-white py-16">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-4xl font-bold mb-4">Practice Exercises</h1>
            <p class="text-xl text-gray-300">
                Sharpen your skills with hands-on coding challenges and quizzes.
            </p>
        </div>
    </section>

    <div class="container mx-auto px-6 py-16">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            <!-- HTML Exercises -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-html5 text-orange-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">HTML Exercises</h3>
                <p class="text-gray-600 mb-4">
                    Practice HTML tags, forms, tables, and more.
                </p>
                <a href="topics.php?language=HTML&exercises=1" class="btn btn-primary">Start HTML Exercises</a>
            </div>
            <!-- CSS Exercises -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-css3-alt text-blue-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">CSS Exercises</h3>
                <p class="text-gray-600 mb-4">
                    Test your knowledge of selectors, properties, and layouts.
                </p>
                <a href="topics.php?language=CSS&exercises=1" class="btn btn-primary">Start CSS Exercises</a>
            </div>
            <!-- JavaScript Exercises -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-js-square text-yellow-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">JavaScript Exercises</h3>
                <p class="text-gray-600 mb-4">
                    Practice JS syntax, DOM, functions, and more.
                </p>
                <a href="topics.php?language=JavaScript&exercises=1" class="btn btn-primary">Start JS Exercises</a>
            </div>
            <!-- PHP Exercises -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-php text-purple-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">PHP Exercises</h3>
                <p class="text-gray-600 mb-4">
                    Practice PHP syntax, arrays, forms, and more.
                </p>
                <a href="topics.php?language=PHP&exercises=1" class="btn btn-primary">Start PHP Exercises</a>
            </div>
            <!-- Python Exercises -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fab fa-python text-blue-800"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">Python Exercises</h3>
                <p class="text-gray-600 mb-4">
                    Practice Python basics, data types, and more.
                </p>
                <a href="topics.php?language=Python&exercises=1" class="btn btn-primary">Start Python Exercises</a>
            </div>
            <!-- SQL Exercises -->
            <div class="card text-center p-8">
                <div class="text-5xl mb-4">
                    <i class="fas fa-database text-green-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">SQL Exercises</h3>
                <p class="text-gray-600 mb-4">
                    Practice SQL queries, joins, and more.
                </p>
                <a href="topics.php?language=SQL&exercises=1" class="btn btn-primary">Start SQL Exercises</a>
            </div>
        </div>
    </div>

  ?>