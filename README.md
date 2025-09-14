# 📘 LearnCode

**LearnCode** is a comprehensive web-based platform for learning web development and programming.  
It features interactive tutorials, code examples, exercises, user progress tracking, and an admin panel for content management.


## 🚀 Features

- **Multi-language Tutorials:** HTML, CSS, JavaScript, PHP, Python, SQL, and more.  
- **Interactive Exercises:** Practice coding with hands-on challenges.  
- **References:** Quick-access programming references for all major languages.  
- **User Accounts:** Registration, login, profile management, and progress tracking.  
- **Favorites & Progress:** Mark topics as favorites and track lesson completion.  
- **Admin Panel:** Manage users, topics, and site settings.  
- **Responsive Design:** Mobile-friendly UI using Tailwind CSS.  
- **Newsletter & Community:** Newsletter signup and community links in the footer.  


## 📂 Project Structure

```

/LearnCode
├── admin/        # Admin dashboard and management pages
├── assets/       # CSS, JS, and images
├── database/     # SQL schema and seed data
├── includes/     # Shared PHP includes (header, footer, functions, etc.)
├── pages/        # Main user-facing pages (about, contact, dashboard, etc.)
├── uploads/      # Uploaded files (e.g., profile images)
├── config.php    # Configuration and utility functions
└── index.php     # Homepage

````


## 🗄️ Database

- MySQL schema → [`database/create_tables.sql`](database/create_tables.sql)  
- Sample data → [`database/seed.sql`](database/seed.sql)  


## ⚙️ Setup

1. **Clone the repository**
   ```sh
   git clone https://github.com/yourusername/learncode.git
   cd learncode
````

2. **Database**

   * Create a MySQL database (e.g., `myW3clone`).
   * Import schema and seed data:

     ```sh
     mysql -u root -p myW3clone < database/create_tables.sql
     mysql -u root -p myW3clone < database/seed.sql
     ```

3. **Configuration**

   * Edit `config.php` with your DB credentials and site settings.

4. **Web Server**

   * Place the project in your web server root (e.g., `htdocs` for XAMPP).
   * Access the app at:

     ```
     http://localhost/learncode
     ```

## 🔑 Admin Access

* Default admin credentials are set in the seed data (`database/seed.sql`).
* Admin panel login → `/admin/login.php`


## 🛠️ Technologies

* PHP (OOP + procedural)
* MySQL
* HTML5, CSS3 (Tailwind CSS), JavaScript
* Font Awesome (icons)


## 🎨 Customization

* Add new tutorials and exercises via the **admin panel**.
* Update site settings in the **admin panel** or directly in the `site_settings` table.


## 📜 License

This project is licensed under the **MIT License**.

> ⚠️ *Note: This project is for educational purposes. Contributions and suggestions are welcome!*

