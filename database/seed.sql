-- -- W3Clone Sample Data
-- -- Run this after create_tables.sql

-- -- ========================================
-- -- INSERT SAMPLE USERS
-- -- ========================================

-- -- Admin user (password: admin123)
-- INSERT INTO users (username, email, password, full_name, is_admin, is_active, email_verified) VALUES
-- ('admin', 'admin@w3clone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 1, 1, 1);

-- -- Regular users (password: user123 for all)
-- INSERT INTO users (username, email, password, full_name, is_active, email_verified) VALUES
-- ('johndoe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 1, 1),
-- ('janedoe', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Doe', 1, 1),
-- ('developer', 'dev@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Developer User', 1, 1);

-- -- ========================================
-- -- INSERT CATEGORIES
-- -- ========================================
-- INSERT INTO categories (name, slug, description, icon, color, sort_order) VALUES
-- ('HTML', 'html', 'HyperText Markup Language - Structure of web pages', 'fab fa-html5', '#e34f26', 1),
-- ('CSS', 'css', 'Cascading Style Sheets - Styling and layout', 'fab fa-css3-alt', '#1572b6', 2),
-- ('JavaScript', 'javascript', 'Programming language for web interactivity', 'fab fa-js-square', '#f7df1e', 3),
-- ('PHP', 'php', 'Server-side scripting language', 'fab fa-php', '#777bb4', 4),
-- ('Python', 'python', 'High-level programming language', 'fab fa-python', '#3776ab', 5),
-- ('SQL', 'sql', 'Database query language', 'fas fa-database', '#00758f', 6),
-- ('React', 'react', 'JavaScript library for building user interfaces', 'fab fa-react', '#61dafb', 7),
-- ('Node.js', 'nodejs', 'JavaScript runtime for server-side development', 'fab fa-node-js', '#339933', 8);

-- -- ========================================
-- -- INSERT SAMPLE TOPICS
-- -- ========================================

-- -- HTML Topics
-- INSERT INTO topics (title, slug, description, content, code_snippet, language, category_id, difficulty, tags, is_featured, author_id) VALUES
-- ('HTML Basics - Getting Started', 'html-basics-getting-started', 'Learn the fundamentals of HTML and create your first web page', 
-- 'HTML (HyperText Markup Language) is the standard markup language for creating web pages. It describes the structure of a web page using markup tags.', 
-- '<!DOCTYPE html>
-- <html lang="en">
-- <head>
--     <meta charset="UTF-8">
--     <meta name="viewport" content="width=device-width, initial-scale=1.0">
--     <title>My First Web Page</title>
-- </head>
-- <body>
--     <h1>Hello World!</h1>
--     <p>This is my first paragraph.</p>
-- </body>
-- </html>', 'HTML', 1, 'Beginner', 'html,basics,web,structure', 1, 1),

-- ('HTML Forms and Input Elements', 'html-forms-input-elements', 'Master HTML forms, input types, and form validation', 
-- 'HTML forms are used to collect user input. Forms contain form elements like input fields, checkboxes, radio buttons, and more.',
-- '<form action="/submit" method="post">
--     <label for="name">Name:</label>
--     <input type="text" id="name" name="name" required>
    
--     <label for="email">Email:</label>
--     <input type="email" id="email" name="email" required>
    
--     <label for="message">Message:</label>
--     <textarea id="message" name="message" rows="4"></textarea>
    
--     <button type="submit">Submit</button>
-- </form>', 'HTML', 1, 'Intermediate', 'html,forms,input,validation', 1, 1),

-- -- CSS Topics
-- ('CSS Basics - Styling Your First Page', 'css-basics-styling', 'Learn CSS fundamentals including selectors, properties, and values', 
-- 'CSS (Cascading Style Sheets) is used to style and layout web pages. It controls colors, fonts, spacing, and positioning.',
-- '/* Basic CSS Styling */
-- body {
--     font-family: Arial, sans-serif;
--     margin: 0;
--     padding: 20px;
--     background-color: #f4f4f4;
-- }

-- h1 {
--     color: #333;
--     text-align: center;
--     margin-bottom: 30px;
-- }

-- .container {
--     max-width: 800px;
--     margin: 0 auto;
--     background: white;
--     padding: 20px;
--     border-radius: 8px;
--     box-shadow: 0 2px 10px rgba(0,0,0,0.1);
-- }', 'CSS', 2, 'Beginner', 'css,styling,basics,selectors', 1, 1),

-- ('CSS Flexbox Layout Guide', 'css-flexbox-layout-guide', 'Master CSS Flexbox for modern, responsive layouts', 
-- 'Flexbox is a powerful CSS layout method that makes it easy to design flexible and responsive layout structures.',
-- '.container {
--     display: flex;
--     justify-content: space-between;
--     align-items: center;
--     gap: 20px;
--     flex-wrap: wrap;
-- }

-- .item {
--     flex: 1;
--     min-width: 200px;
--     padding: 20px;
--     background: #f0f0f0;
--     border-radius: 8px;
-- }

-- .item:first-child {
--     flex: 2;
-- }', 'CSS', 2, 'Intermediate', 'css,flexbox,layout,responsive', 1, 1),

-- -- JavaScript Topics
-- ('JavaScript Basics - Variables and Functions', 'javascript-basics-variables-functions', 'Learn JavaScript fundamentals including variables, data types, and functions', 
-- 'JavaScript is a programming language that enables interactive web pages. It is an essential part of web applications.',
-- '// Variables and Data Types
-- let name = "John Doe";
-- const age = 25;
-- var isStudent = true;

-- // Function Declaration
-- function greetUser(userName) {
--     return `Hello, ${userName}! Welcome to our site.`;
-- }

-- // Function Expression
-- const calculateArea = function(width, height) {
--     return width * height;
-- };

-- // Arrow Function
-- const multiply = (a, b) => a * b;

-- // Using the functions
-- console.log(greetUser(name));
-- console.log(calculateArea(10, 5));
-- console.log(multiply(4, 7));', 'JavaScript', 3, 'Beginner', 'javascript,variables,functions,basics', 1, 1),

-- ('JavaScript DOM Manipulation', 'javascript-dom-manipulation', 'Learn how to interact with HTML elements using JavaScript', 
-- 'The Document Object Model (DOM) is a programming interface for HTML documents. JavaScript can change HTML content, attributes, and CSS styles.',
-- '// Select elements
-- const heading = document.getElementById("main-heading");
-- const buttons = document.querySelectorAll(".btn");
-- const container = document.querySelector(".container");

-- // Change content
-- heading.textContent = "Updated Heading";
-- heading.innerHTML = "<span>HTML Content</span>";

-- // Change styles
-- heading.style.color = "blue";
-- heading.style.fontSize = "2rem";

-- // Add event listeners
-- buttons.forEach(button => {
--     button.addEventListener("click", function() {
--         this.classList.toggle("active");
--     });
-- });

-- // Create new elements
-- const newParagraph = document.createElement("p");
-- newParagraph.textContent = "This is a new paragraph";
-- container.appendChild(newParagraph);', 'JavaScript', 3, 'Intermediate', 'javascript,dom,manipulation,events', 1, 1),

-- -- PHP Topics
-- ('PHP Basics - Your First PHP Script', 'php-basics-first-script', 'Learn PHP fundamentals including variables, arrays, and basic syntax', 
-- 'PHP is a server-side scripting language designed for web development. It can be embedded into HTML.',
-- '<?php
-- // PHP Basic Syntax
-- echo "Hello, World!";

-- // Variables
-- $name = "John Doe";
-- $age = 25;
-- $isActive = true;

-- // Arrays
-- $fruits = ["apple", "banana", "orange"];
-- $person = [
--     "name" => "Jane",
--     "age" => 30,
--     "city" => "New York"
-- ];

-- // Control Structures
-- if ($age >= 18) {
--     echo "You are an adult";
-- } else {
--     echo "You are a minor";
-- }

-- // Loops
-- for ($i = 1; $i <= 5; $i++) {
--     echo "Number: $i<br>";
-- }

-- foreach ($fruits as $fruit) {
--     echo "Fruit: $fruit<br>";
-- }
-- ?>', 'PHP', 4, 'Beginner', 'php,basics,variables,arrays', 1, 1),

-- ('PHP MySQL Database Connection', 'php-mysql-database-connection', 'Learn how to connect PHP with MySQL database and perform CRUD operations', 
-- 'PHP can interact with databases to store and retrieve data. MySQL is one of the most popular databases used with PHP.',
-- '<?php
-- // Database configuration
-- $host = "localhost";
-- $username = "root";
-- $password = "";
-- $database = "my_database";

-- // Create connection using MySQLi
-- $conn = new mysqli($host, $username, $password, $database);

-- // Check connection
-- if ($conn->connect_error) {
--     die("Connection failed: " . $conn->connect_error);
-- }

-- // INSERT data
-- $sql = "INSERT INTO users (name, email) VALUES (?, ?)";
-- $stmt = $conn->prepare($sql);
-- $stmt->bind_param("ss", $name, $email);
-- $name = "John Doe";
-- $email = "john@example.com";
-- $stmt->execute();

-- // SELECT data
-- $sql = "SELECT id, name, email FROM users";
-- $result = $conn->query($sql);

-- if ($result->num_rows > 0) {
--     while($row = $result->fetch_assoc()) {
--         echo "ID: " . $row["id"]. " - Name: " . $row["name"]. " - Email: " . $row["email"]. "<br>";
--     }
-- }

-- $conn->close();
-- ?>', 'PHP', 4, 'Intermediate', 'php,mysql,database,crud', 1, 1),

-- -- Python Topics
-- ('Python Basics - Getting Started', 'python-basics-getting-started', 'Learn Python fundamentals including variables, data types, and basic operations', 
-- 'Python is a high-level, interpreted programming language known for its simplicity and readability.',
-- '# Variables and Data Types
-- name = "Alice"
-- age = 30
-- height = 5.6
-- is_student = False

-- # Lists and Dictionaries
-- fruits = ["apple", "banana", "cherry"]
-- person = {
--     "name": "Bob",
--     "age": 25,
--     "city": "Seattle"
-- }

-- # Functions
-- def greet(name):
--     return f"Hello, {name}!"

-- def calculate_area(length, width):
--     return length * width

-- # Control Flow
-- if age >= 18:
--     print("You are an adult")
-- else:
--     print("You are a minor")

-- # Loops
-- for fruit in fruits:
--     print(f"I like {fruit}")

-- for i in range(1, 6):
--     print(f"Count: {i}")

-- # Using functions
-- print(greet(name))
-- area = calculate_area(10, 5)
-- print(f"Area: {area}")', 'Python', 5, 'Beginner', 'python,basics,variables,functions', 1, 1),

-- -- SQL Topics
-- ('SQL Basics - Database Queries', 'sql-basics-database-queries', 'Learn fundamental SQL commands for database operations', 
-- 'SQL (Structured Query Language) is used to communicate with databases. It can retrieve, insert, update, and delete data.',
-- '-- Create a table
-- CREATE TABLE students (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     name VARCHAR(100) NOT NULL,
--     email VARCHAR(100) UNIQUE,
--     age INT,
--     grade CHAR(1)
-- );

-- -- Insert data
-- INSERT INTO students (name, email, age, grade) 
-- VALUES 
--     ("John Doe", "john@example.com", 20, "A"),
--     ("Jane Smith", "jane@example.com", 19, "B"),
--     ("Mike Johnson", "mike@example.com", 21, "A");

-- -- Select data
-- SELECT * FROM students;
-- SELECT name, grade FROM students WHERE age > 19;
-- SELECT COUNT(*) as total_students FROM students;

-- -- Update data
-- UPDATE students 
-- SET grade = "A+" 
-- WHERE name = "Jane Smith";

-- -- Delete data
-- DELETE FROM students WHERE age < 18;

-- -- Join example (assuming another table exists)
-- SELECT s.name, s.grade, c.course_name
-- FROM students s
-- JOIN courses c ON s.id = c.student_id;', 'SQL', 6, 'Beginner', 'sql,database,queries,crud', 1, 1);

-- -- ========================================
-- -- INSERT SITE SETTINGS
-- -- ========================================
-- INSERT INTO site_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
-- ('site_name', 'W3Clone', 'string', 'Site name displayed in header', 1),
-- ('site_description', 'Learn web development with comprehensive tutorials', 'string', 'Site meta description', 1),
-- ('contact_email', 'contact@w3clone.com', 'string', 'Contact email address', 1),
-- ('social_facebook', 'https://facebook.com/w3clone', 'string', 'Facebook page URL', 1),
-- ('social_twitter', 'https://twitter.com/w3clone', 'string', 'Twitter profile URL', 1),
-- ('social_github', 'https://github.com/w3clone', 'string', 'GitHub repository URL', 1),
-- ('enable_registration', '1', 'boolean', 'Allow new user registration', 0),
-- ('enable_comments', '1', 'boolean', 'Enable comments on topics', 0),
-- ('items_per_page', '12', 'number', 'Number of topics per page', 0);

-- -- ========================================
-- -- INSERT SAMPLE USER FAVORITES
-- -- ========================================
-- INSERT INTO user_favorites (user_id, topic_id) VALUES
-- (2, 1), (2, 3), (2, 5),  -- John likes HTML, CSS, and JavaScript topics
-- (3, 2), (3, 4), (3, 6),  -- Jane likes different topics
-- (4, 1), (4, 2), (4, 7);  -- Developer user favorites

-- -- ========================================
-- -- INSERT SAMPLE PROGRESS
-- -- ========================================
-- INSERT INTO user_progress (user_id, topic_id, status, progress_percentage, time_spent) VALUES
-- (2, 1, 'completed', 100, 1800),    -- John completed HTML basics (30 mins)
-- (2, 3, 'in_progress', 75, 1200),   -- John 75% through CSS basics (20 mins)
-- (3, 2, 'completed', 100, 2400),    -- Jane completed HTML forms (40 mins)
-- (3, 4, 'in_progress', 50, 900),    -- Jane 50% through CSS flexbox (15 mins)
-- (4, 1, 'completed', 100, 1500),    -- Developer completed HTML basics (25 mins)
-- (4, 7, 'in_progress', 30, 600);    -- Developer 30% through PHP basics (10 mins)

-- -- ========================================
-- -- INSERT SAMPLE COMMENTS
-- -- ========================================
-- INSERT INTO comments (topic_id, user_id, comment, is_approved) VALUES
-- (1, 2, 'Great tutorial! This helped me understand HTML basics really well.', 1),
-- (1, 3, 'Thanks for the clear explanation. The code examples are very helpful.', 1),
-- (3, 2, 'CSS can be tricky at first, but this tutorial makes it easy to understand.', 1),
-- (5, 3, 'JavaScript is so powerful! Looking forward to more advanced topics.', 1),
-- (7, 4, 'PHP is my favorite backend language. This is a solid introduction.', 1);

-- -- ========================================
-- -- INSERT SAMPLE TOPIC VIEWS (for analytics)
-- -- ========================================
-- INSERT INTO topic_views (topic_id, user_id, ip_address, viewed_at) VALUES
-- (1, 2, '192.168.1.100', DATE_SUB(NOW(), INTERVAL 1 DAY)),
-- (1, 3, '192.168.1.101', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
-- (1, NULL, '192.168.1.102', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
-- (3, 2, '192.168.1.100', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
-- (3, 4, '192.168.1.103', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
-- (5, 3, '192.168.1.101', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
-- (7, 4, '192.168.1.103', DATE_SUB(NOW(), INTERVAL 15 MINUTE));

-- -- Update view counts (this should be handled by trigger, but just in case)
-- UPDATE topics t SET view_count = (
--     SELECT COUNT(*) FROM topic_views tv WHERE tv.topic_id = t.id
-- );
ALTER TABLE users
  ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL,
  ADD COLUMN remember_expires DATETIME DEFAULT NULL;