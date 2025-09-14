<?php
// Get current directory for proper URL paths
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$base_url = ($current_dir === 'pages') ? '' : 'pages/';
?>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            
            <!-- About Section -->
            <div class="footer-section">
                <h4>
                    <i class="fas fa-code"></i>
                    LearnCode
                </h4>
                <p class="footer-description">
                    Learn web development with comprehensive tutorials, examples, and exercises. 
                    Master HTML, CSS, JavaScript, PHP, Python and more.
                </p>
                <div class="social-links">
                    <a href="https://facebook.com/w3clone" class="social-link" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/w3clone" class="social-link" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://github.com/w3clone" class="social-link" aria-label="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="https://linkedin.com/company/w3clone" class="social-link" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://youtube.com/w3clone" class="social-link" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Top Tutorials -->
            <div class="footer-section">
                <h4>
                    <i class="fas fa-star"></i>
                    Top Tutorials
                </h4>
                <ul class="footer-links">
                    <li><a href="<?= $base_url ?>topics.php?language=HTML">HTML Tutorial</a></li>
                    <li><a href="<?= $base_url ?>topics.php?language=CSS">CSS Tutorial</a></li>
                    <li><a href="<?= $base_url ?>topics.php?language=JavaScript">JavaScript Tutorial</a></li>
                    <li><a href="<?= $base_url ?>topics.php?language=PHP">PHP Tutorial</a></li>
                    <li><a href="<?= $base_url ?>topics.php?language=Python">Python Tutorial</a></li>
                    <li><a href="<?= $base_url ?>topics.php?language=SQL">SQL Tutorial</a></li>
                </ul>
            </div>

            <!-- References -->
            <div class="footer-section">
                <h4>
                    <i class="fas fa-bookmark"></i>
                    References
                </h4>
                <ul class="footer-links">
                    <li><a href="<?= $base_url ?>reference.php?topic=html">HTML Reference</a></li>
                    <li><a href="<?= $base_url ?>reference.php?topic=css">CSS Reference</a></li>
                    <li><a href="<?= $base_url ?>reference.php?topic=javascript">JS Reference</a></li>
                    <li><a href="<?= $base_url ?>reference.php?topic=php">PHP Reference</a></li>
                    <li><a href="<?= $base_url ?>reference.php?topic=python">Python Reference</a></li>
                    <li><a href="<?= $base_url ?>reference.php?topic=sql">SQL Reference</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div class="footer-section">
                <h4>
                    <i class="fas fa-building"></i>
                    Company
                </h4>
                <ul class="footer-links">
                    <li><a href="<?= $base_url ?>about.php">About Us</a></li>
                    <li><a href="<?= $base_url ?>contact.php">Contact</a></li>
                    <li><a href="<?= $base_url ?>careers.php">Careers</a></li>
                    <li><a href="<?= $base_url ?>blog.php">Blog</a></li>
                    <li><a href="<?= $base_url ?>privacy.php">Privacy Policy</a></li>
                    <li><a href="<?= $base_url ?>terms.php">Terms of Service</a></li>
                </ul>
            </div>

            <!-- Help & Support -->
            <div class="footer-section">
                <h4>
                    <i class="fas fa-life-ring"></i>
                    Help & Support
                </h4>
                <ul class="footer-links">
                    <li><a href="<?= $base_url ?>help.php">Help Center</a></li>
                    <li><a href="<?= $base_url ?>faq.php">FAQ</a></li>
                    <li><a href="<?= $base_url ?>feedback.php">Feedback</a></li>
                    <li><a href="<?= $base_url ?>community.php">Community</a></li>
                    <li><a href="<?= $base_url ?>report-bug.php">Report a Bug</a></li>
                    <li><a href="<?= $base_url ?>suggest-feature.php">Suggest Feature</a></li>
                </ul>
            </div>

        </div>

        <!-- Newsletter Signup -->
        <div class="newsletter-section">
            <div class="newsletter-content">
                <div class="newsletter-info">
                    <h3>
                        <i class="fas fa-envelope"></i>
                        Stay Updated
                    </h3>
                    <p>Get the latest tutorials and web development tips delivered to your inbox.</p>
                </div>
                <form class="newsletter-form" action="<?= $base_url ?>subscribe.php" method="POST">
                    <div class="newsletter-input-group">
                        <input type="email" name="email" placeholder="Enter your email address" required class="newsletter-input">
                        <button type="submit" class="newsletter-button">
                            <i class="fas fa-paper-plane"></i>
                            Subscribe
                        </button>
                    </div>
                    <p class="newsletter-note">No spam. Unsubscribe anytime.</p>
                </form>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="footer-stats">
            <div class="stat-item">
                <span class="stat-number">125+</span>
                <span class="stat-label">Tutorials</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">50K+</span>
                <span class="stat-label">Students</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">10K+</span>
                <span class="stat-label">Code Examples</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">24/7</span>
                <span class="stat-label">Support</span>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="copyright">
                    <p>&copy; <?= date('Y') ?> W3Clone. All rights reserved.</p>
                    <p class="powered-by">
                        Made with <i class="fas fa-heart" style="color: #e74c3c;"></i> for developers
                    </p>
                </div>
                <div class="footer-bottom-links">
                    <a href="<?= $base_url ?>sitemap.php">Sitemap</a>
                    <span class="separator">|</span>
                    <a href="<?= $base_url ?>accessibility.php">Accessibility</a>
                    <span class="separator">|</span>
                    <a href="<?= $base_url ?>cookies.php">Cookies</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="back-to-top" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Footer Styles -->
<style>
.footer {
    background: #f8f8f8;
    border-top: 1px solid #e5e5e5;
    margin-top: auto;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    padding: 3rem 0 2rem;
}

.footer-section h4 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #000000;
    font-size: 1.125rem;
    margin-bottom: 1rem;
}

.footer-section h4 i {
    font-size: 1rem;
}

.footer-description {
    color: #666666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.social-links {
    display: flex;
    gap: 1rem;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 50%;
    color: #666666;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: #000000;
    color: #ffffff;
    border-color: #000000;
    transform: translateY(-2px);
    text-decoration: none;
}

.footer-links {
    list-style: none;
    margin: 0;
    padding: 0;
}

.footer-links li {
    margin-bottom: 0.5rem;
}

.footer-links a {
    color: #666666;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #000000;
    text-decoration: none;
}

.newsletter-section {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    padding: 2rem;
    margin: 2rem 0;
}

.newsletter-content {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 2rem;
    align-items: center;
}

.newsletter-info h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #000000;
    margin-bottom: 0.5rem;
}

.newsletter-info p {
    color: #666666;
    margin: 0;
}

.newsletter-form {
    min-width: 350px;
}

.newsletter-input-group {
    display: flex;
    gap: 0;
    margin-bottom: 0.5rem;
}

.newsletter-input {
    flex: 1;
    padding: 0.875rem 1rem;
    border: 1px solid #e5e5e5;
    border-right: none;
    border-radius: 8px 0 0 8px;
    font-size: 0.9rem;
    background: #ffffff;
}

.newsletter-input:focus {
    outline: none;
    border-color: #000000;
}

.newsletter-button {
    padding: 0.875rem 1.5rem;
    background: #000000;
    color: #ffffff;
    border: 1px solid #000000;
    border-left: none;
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.newsletter-button:hover {
    background: #333333;
}

.newsletter-note {
    font-size: 0.75rem;
    color: #999999;
    margin: 0;
    text-align: center;
}

.footer-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 2rem;
    padding: 2rem 0;
    border-top: 1px solid #e5e5e5;
    border-bottom: 1px solid #e5e5e5;
    text-align: center;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #000000;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #666666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.footer-bottom {
    padding: 2rem 0;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.copyright {
    color: #666666;
    font-size: 0.9rem;
}

.powered-by {
    margin-top: 0.25rem;
    font-size: 0.8rem;
}

.footer-bottom-links {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.footer-bottom-links a {
    color: #666666;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #000000;
    text-decoration: none;
}

.separator {
    color: #cccccc;
}

.back-to-top {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 50px;
    height: 50px;
    background: #000000;
    color: #ffffff;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.back-to-top:hover {
    background: #333333;
    transform: translateY(-2px);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
        padding: 2rem 0;
    }
    
    .newsletter-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .newsletter-form {
        min-width: auto;
    }
    
    .newsletter-input-group {
        flex-direction: column;
    }
    
    .newsletter-input,
    .newsletter-button {
        border-radius: 8px;
        border: 1px solid #e5e5e5;
    }
    
    .newsletter-button {
        border-top: none;
        margin-top: -1px;
    }
    
    .footer-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }
    
    .social-links {
        justify-content: center;
    }
    
    .back-to-top {
        bottom: 1rem;
        right: 1rem;
        width: 45px;
        height: 45px;
    }
}

@media (max-width: 480px) {
    .footer-stats {
        grid-template-columns: 1fr;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .newsletter-section {
        margin: 1rem -1rem;
        border-radius: 0;
        border-left: none;
        border-right: none;
    }
}
</style>

<!-- Footer JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Back to top button functionality
    const backToTopButton = document.getElementById('backToTop');
    
    // Show/hide back to top button
    function toggleBackToTop() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('visible');
        } else {
            backToTopButton.classList.remove('visible');
        }
    }
    
    // Scroll event listener
    window.addEventListener('scroll', toggleBackToTop);
    
    // Click event for back to top
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Newsletter form submission
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value;
            const button = this.querySelector('.newsletter-button');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
            button.disabled = true;
            
            // Reset after 2 seconds (replace with actual AJAX call)
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    this.reset();
                }, 2000);
            }, 1000);
        });
    }
});
</script>