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
    <title>About Us - LearnCode</title>
    <meta name="description" content="Learn about LearnCode - your premier destination for web development tutorials, courses, and resources.">
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero bg-gradient-to-r from-gray-900 to-black text-white py-20">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl font-bold mb-6">About LearnCode</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Empowering developers worldwide with comprehensive, accessible, and practical web development education.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-16">
        
        <!-- Mission Section -->
        <section class="mb-16">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold mb-6">Our Mission</h2>
                    <p class="text-gray-700 text-lg leading-relaxed mb-6">
                        At LearnCode, we believe that quality web development education should be accessible to everyone, 
                        regardless of their background or experience level. Our mission is to provide comprehensive, 
                        up-to-date, and practical tutorials that help developers build real-world skills.
                    </p>
                    <p class="text-gray-700 text-lg leading-relaxed">
                        We strive to create a learning environment where beginners can start their journey with confidence 
                        and experienced developers can enhance their skills with advanced topics and best practices.
                    </p>
                </div>
                <div class="text-center">
                    <div class="inline-block p-8 bg-gray-100 rounded-full">
                        <i class="fas fa-graduation-cap text-6xl text-black"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="bg-gray-50 rounded-2xl p-12 mb-16">
            <h2 class="text-3xl font-bold text-center mb-12">LearnCode by the Numbers</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div class="stat-item">
                    <div class="text-4xl font-bold text-black mb-2">125+</div>
                    <div class="text-gray-600">Tutorials</div>
                </div>
                <div class="stat-item">
                    <div class="text-4xl font-bold text-black mb-2">50K+</div>
                    <div class="text-gray-600">Students</div>
                </div>
                <div class="stat-item">
                    <div class="text-4xl font-bold text-black mb-2">15+</div>
                    <div class="text-gray-600">Languages</div>
                </div>
                <div class="stat-item">
                    <div class="text-4xl font-bold text-black mb-2">24/7</div>
                    <div class="text-gray-600">Support</div>
                </div>
            </div>
        </section>

        <!-- What We Offer -->
        <section class="mb-16">
            <h2 class="text-4xl font-bold text-center mb-12">What We Offer</h2>
            <div class="grid md:grid-cols-3 gap-8">
                
                <div class="card text-center p-8">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-book-open text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Comprehensive Tutorials</h3>
                    <p class="text-gray-600">
                        From HTML basics to advanced JavaScript frameworks, our tutorials cover 
                        everything you need to become a proficient web developer.
                    </p>
                </div>

                <div class="card text-center p-8">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-code text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Interactive Examples</h3>
                    <p class="text-gray-600">
                        Learn by doing with our interactive code examples. See the results 
                        immediately and experiment with different approaches.
                    </p>
                </div>

                <div class="card text-center p-8">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-users text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Community Support</h3>
                    <p class="text-gray-600">
                        Join thousands of developers in our community. Get help, share knowledge, 
                        and collaborate on projects together.
                    </p>
                </div>

                <div class="card text-center p-8">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-chart-line text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Progress Tracking</h3>
                    <p class="text-gray-600">
                        Monitor your learning journey with our progress tracking system. 
                        See what you've completed and what to learn next.
                    </p>
                </div>

                <div class="card text-center p-8">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-mobile-alt text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Mobile Friendly</h3>
                    <p class="text-gray-600">
                        Learn anywhere, anytime. Our platform is fully responsive and 
                        optimized for mobile devices and tablets.
                    </p>
                </div>

                <div class="card text-center p-8">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-certificate text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Certificates</h3>
                    <p class="text-gray-600">
                        Earn certificates upon completing courses to showcase your 
                        skills to employers and add to your professional portfolio.
                    </p>
                </div>

            </div>
        </section>

        <!-- Our Story -->
        <section class="mb-16">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl font-bold mb-8">Our Story</h2>
                <p class="text-gray-700 text-lg leading-relaxed mb-6">
                    LearnCode was founded in 2024 with a simple vision: to make high-quality web development 
                    education accessible to everyone. Starting as a small project, we quickly grew into 
                    a comprehensive learning platform trusted by thousands of developers worldwide.
                </p>
                <p class="text-gray-700 text-lg leading-relaxed mb-6">
                    Our team consists of experienced developers, educators, and content creators who are 
                    passionate about sharing knowledge and helping others succeed in their programming journey. 
                    We continuously update our content to reflect the latest industry trends and best practices.
                </p>
                <p class="text-gray-700 text-lg leading-relaxed">
                    Today, LearnCode serves as a bridge between beginners and the ever-evolving world of 
                    web development, providing the tools, resources, and community support needed to 
                    succeed in this dynamic field.
                </p>
            </div>
        </section>

        <!-- Team Section -->
        <section class="mb-16">
            <h2 class="text-4xl font-bold text-center mb-12">Meet Our Team</h2>
            <div class="grid md:grid-cols-3 gap-8">
                
                <div class="card text-center p-8">
                    <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-user text-4xl text-gray-500"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">John Developer</h3>
                    <p class="text-gray-600 mb-4">Founder & Lead Developer</p>
                    <p class="text-gray-700 text-sm">
                        10+ years of experience in web development. Passionate about education 
                        and making coding accessible to everyone.
                    </p>
                    <div class="mt-4 space-x-2">
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-github"></i></a>
                    </div>
                </div>

                <div class="card text-center p-8">
                    <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-user text-4xl text-gray-500"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Jane Designer</h3>
                    <p class="text-gray-600 mb-4">UI/UX Designer</p>
                    <p class="text-gray-700 text-sm">
                        Expert in user experience design with a focus on creating intuitive 
                        and accessible learning interfaces.
                    </p>
                    <div class="mt-4 space-x-2">
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-dribbble"></i></a>
                    </div>
                </div>

                <div class="card text-center p-8">
                    <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-user text-4xl text-gray-500"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Mike Teacher</h3>
                    <p class="text-gray-600 mb-4">Content Creator</p>
                    <p class="text-gray-700 text-sm">
                        Former computer science professor with expertise in creating 
                        engaging educational content and curriculum development.
                    </p>
                    <div class="mt-4 space-x-2">
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-600 hover:text-black"><i class="fas fa-globe"></i></a>
                    </div>
                </div>

            </div>
        </section>

        <!-- Contact CTA -->
        <section class="text-center bg-black text-white rounded-2xl p-12">
            <h2 class="text-3xl font-bold mb-4">Ready to Start Learning?</h2>
            <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                Join thousands of developers who are already advancing their careers with LearnCode. 
                Start your journey today!
            </p>
            <div class="space-x-4">
                <a href="register.php" class="btn btn-primary bg-white text-black px-8 py-3 text-lg hover:bg-gray-200">
                    Get Started Free
                </a>
                <a href="contact.php" class="btn btn-outline border-white text-white px-8 py-3 text-lg hover:bg-white hover:text-black">
                    Contact Us
                </a>
            </div>
        </section>

    </div>

    <?php include '../includes/footer.php'; ?>

</body>
</html>