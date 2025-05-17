<?php
// contact.php - Contact page
require_once 'includes/header.php';
?>

<div class="row">
    <div class="twelve columns">
        <div class="contact-container">
            <h3>Contact Us</h3>
            
            <div class="contact-content">
                <p>We'd love to hear from you! Fill out the form below to get in touch.</p>
                
                <form action="" method="POST" class="contact-form">
                    <div class="row">
                        <div class="six columns">
                            <label for="name">Name</label>
                            <input class="u-full-width" type="text" id="name" name="name" required>
                        </div>
                        <div class="six columns">
                            <label for="email">Email</label>
                            <input class="u-full-width" type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="twelve columns">
                            <label for="message">Message</label>
                            <textarea class="u-full-width" id="message" name="message" required></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="button-primary">Send Message</button>
                </form>
                
                <div class="contact-info">
                    <p><strong>Note:</strong> This is a placeholder form. To make it functional, edit the <code>contact.php</code> file and add your email processing code.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>