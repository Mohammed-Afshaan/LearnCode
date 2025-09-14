<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

$success = false;
$errors = [];
$form_data = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $form_data = [
        'name' => sanitize_input($_POST['name'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'subject' => sanitize_input($_POST['subject'] ?? ''),
        'message' => sanitize_input($_POST['message'] ?? '')
    ];
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }
    
    // Rate limiting check
    if (!checkRateLimit($_SERVER['REMOTE_ADDR'] . '_contact', 3, 3600)) {
        $errors[] = 'Too many contact form submissions. Please try again later.';
    }
    
    // Validate required fields
    if (empty($form_data['name'])) {
        $errors[] = 'Name is required.';
    } elseif (strlen($form_data['name']) < 2) {
        $errors[] = 'Name must be at least 2 characters long.';
    }
    
    if (empty($form_data['email'])) {
        $errors[] = 'Email address is required.';
    } elseif (!validateEmail($form_data['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($form_data['subject'])) {
        $errors[] = 'Subject is required.';
    } elseif (strlen($form_data['subject']) < 5) {
        $errors[] = 'Subject must be at least 5 characters long.';
    }
    
    if (empty($form_data['message'])) {
        $errors[] = 'Message is required.';
    } elseif (strlen($form_data['message']) < 10) {
        $errors[] = 'Message must be at least 10 characters long.';
    }
    
    // Save message if no errors
    if (empty($errors)) {
        $insert_query = "INSERT INTO contact_messages (name, email, subject, message, created_at) 
                         VALUES (?, ?, ?, ?, NOW())";
        
        $result = executeQuery($insert_query, 'ssss', [
            $form_data['name'],
            $form_data['email'],
            $form_data['subject'],
            $form_data['message']
        ]);
        
        if ($result) {
            $success = true;
            // Clear form data
            $form_data = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
            
            // Send notification email to admin (optional)
            $admin_email = getSiteSetting('admin_email', 'admin@w3clone.com');
            $email_subject = 'New Contact Form Submission - ' . $form_data['subject'];
            $email_message = "
                <h3>New Contact Form Submission</h3>
                <p><strong>Name:</strong> {$form_data['name']}</p>
                <p><strong>Email:</strong> {$form_data['email']}</p>
                <p><strong>Subject:</strong> {$form_data['subject']}</p>
                <p><strong>Message:</strong></p>
                <p>{$form_data['message']}</p>
            ";
            
            sendEmail($admin_email, $email_subject, $email_message);
        } else {
            $errors[] = 'Failed to send message. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - W3Clone</title>
    <meta name="description" content="Get in touch with the W3Clone team. We're here to help with your questions, feedback, and support needs.">
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-gray-900 to-black text-white py-16">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-4xl font-bold mb-4">Get in Touch</h1>
            <p class="text-xl text-gray-300">
                Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
            </p>
        </div>
    </section>

    <div class="container mx-auto px-6 py-16">
        <div class="grid lg:grid-cols-2 gap-16">
            
            <!-- Contact Form -->
            <div class="max-w-2xl">
                <h2 class="text-3xl font-bold mb-8">Send us a Message</h2>

                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <div>
                                <h4 class="font-semibold">Message Sent Successfully!</h4>
                                <p class="text-sm">Thank you for contacting us. We'll get back to you within 24 hours.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold mb-2">Please fix the following errors:</h4>
                                <ul class="text-sm space-y-1">
                                    <?php foreach ($errors as $error): ?>
                                        <li>• <?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="fas fa-user mr-2"></i>
                                Full Name
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="<?= htmlspecialchars($form_data['name']) ?>"
                                class="form-input w-full" 
                                placeholder="Your full name"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope mr-2"></i>
                                Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?= htmlspecialchars($form_data['email']) ?>"
                                class="form-input w-full" 
                                placeholder="your@email.com"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">
                            <i class="fas fa-tag mr-2"></i>
                            Subject
                        </label>
                        <select 
                            id="subject" 
                            name="subject" 
                            class="form-select w-full"
                            required
                        >
                            <option value="">Select a subject</option>
                            <option value="General Inquiry" <?= $form_data['subject'] === 'General Inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                            <option value="Technical Support" <?= $form_data['subject'] === 'Technical Support' ? 'selected' : '' ?>>Technical Support</option>
                            <option value="Tutorial Request" <?= $form_data['subject'] === 'Tutorial Request' ? 'selected' : '' ?>>Tutorial Request</option>
                            <option value="Bug Report" <?= $form_data['subject'] === 'Bug Report' ? 'selected' : '' ?>>Bug Report</option>
                            <option value="Partnership" <?= $form_data['subject'] === 'Partnership' ? 'selected' : '' ?>>Partnership</option>
                            <option value="Feedback" <?= $form_data['subject'] === 'Feedback' ? 'selected' : '' ?>>Feedback</option>
                            <option value="Other" <?= $form_data['subject'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">
                            <i class="fas fa-comment mr-2"></i>
                            Message
                        </label>
                        <textarea 
                            id="message" 
                            name="message" 
                            rows="6"
                            class="form-textarea w-full" 
                            placeholder="Tell us how we can help you..."
                            required
                        ><?= htmlspecialchars($form_data['message']) ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">
                            Minimum 10 characters required
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary px-8 py-3 text-lg">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Information -->
            <div>
                <h2 class="text-3xl font-bold mb-8">Contact Information</h2>
                
                <div class="space-y-8 mb-12">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-black text-white rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-1">Email</h3>
                            <p class="text-gray-600 mb-2">Send us an email anytime!</p>
                            <a href="mailto:support@w3clone.com" class="text-black font-medium hover:underline">
                                support@w3clone.com
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-black text-white rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-1">Response Time</h3>
                            <p class="text-gray-600 mb-2">We typically respond within:</p>
                            <p class="text-black font-medium">24 hours</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-black text-white rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-life-ring"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-1">Support Hours</h3>
                            <p class="text-gray-600 mb-2">We're here to help:</p>
                            <p class="text-black font-medium">Monday - Friday: 9AM - 6PM UTC</p>
                        </div>
                    </div>
                </div>

                <!-- FAQ Link -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h3 class="text-xl font-bold mb-4">
                        <i class="fas fa-question-circle text-black mr-2"></i>
                        Frequently Asked Questions
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Looking for quick answers? Check out our FAQ section for common questions and solutions.
                    </p>
                    <a href="faq.php" class="btn btn-outline px-4 py-2">
                        <i class="fas fa-arrow-right mr-2"></i>
                        View FAQ
                    </a>
                </div>

                <!-- Social Media -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Follow Us</h3>
                    <p class="text-gray-600 mb-4">
                        Stay connected with us on social media for updates, tips, and community discussions.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-12 h-12 bg-gray-100 hover:bg-black hover:text-white rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-100 hover:bg-black hover:text-white rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-100 hover:bg-black hover:text-white rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-100 hover:bg-black hover:text-white rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-100 hover:bg-black hover:text-white rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alternative Contact Methods -->
        <div class="mt-16 pt-16 border-t border-gray-200">
            <h2 class="text-2xl font-bold text-center mb-8">Other Ways to Reach Us</h2>
            <div class="grid md:grid-cols-3 gap-8">
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-black text-white rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Community Forum</h3>
                    <p class="text-gray-600 mb-4">
                        Join our community forum to get help from other developers and share your knowledge.
                    </p>
                    <a href="#" class="text-black font-medium hover:underline">
                        Visit Forum →
                    </a>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-black text-white rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fab fa-discord"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Discord Server</h3>
                    <p class="text-gray-600 mb-4">
                        Real-time chat with our community and get instant help with your coding questions.
                    </p>
                    <a href="#" class="text-black font-medium hover:underline">
                        Join Discord →
                    </a>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-black text-white rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Documentation</h3>
                    <p class="text-gray-600 mb-4">
                        Browse our comprehensive documentation for detailed guides and API references.
                    </p>
                    <a href="#" class="text-black font-medium hover:underline">
                        Read Docs →
                    </a>
                </div>

            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Character counter for message textarea
        document.addEventListener('DOMContentLoaded', function() {
            const messageTextarea = document.getElementById('message');
            const minLength = 10;
            
            function updateCharacterCount() {
                const currentLength = messageTextarea.value.length;
                const helpText = messageTextarea.parentElement.querySelector('.text-gray-500');
                
                if (currentLength < minLength) {
                    const remaining = minLength - currentLength;
                    helpText.textContent = `${remaining} more characters needed (minimum ${minLength})`;
                    helpText.className = 'text-sm text-red-500 mt-1';
                } else {
                    helpText.textContent = `${currentLength} characters`;
                    helpText.className = 'text-sm text-green-500 mt-1';
                }
            }
            
            messageTextarea.addEventListener('input', updateCharacterCount);
            updateCharacterCount(); // Initialize
        });
    </script>

</body>
</html>