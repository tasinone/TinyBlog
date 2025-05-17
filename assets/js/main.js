// assets/js/main.js - Main JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const hamburgerButton = document.querySelector('.hamburger-menu button');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (hamburgerButton && mobileMenu) {
        hamburgerButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            mobileMenu.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.hamburger-menu') && mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
            }
        });
        
        // Close menu when clicking on a menu item
        const menuItems = mobileMenu.querySelectorAll('a');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
            });
        });
    }
    
    // See more/less functionality for posts
    setupPostExpansion();
    
    // Like functionality
    setupLikeButtons();
    
    // Share functionality
    setupShareButtons();
});

// Setup post expansion functionality
function setupPostExpansion() {
    const posts = document.querySelectorAll('.post');
    
    posts.forEach(post => {
        const content = post.querySelector('.content-preview');
        const seeMoreBtn = post.querySelector('.see-more');
        const seeLessBtn = post.querySelector('.see-less');
        
        // Only show See more button if content is overflowing
        if (content && seeMoreBtn) {
            if (content.scrollHeight > content.clientHeight) {
                seeMoreBtn.style.display = 'inline-block';
            } else {
                seeMoreBtn.style.display = 'none';
            }
            
            // See more button click
            seeMoreBtn.addEventListener('click', function() {
                post.classList.add('expanded');
                content.style.maxHeight = 'none';
                seeMoreBtn.style.display = 'none';
                seeLessBtn.style.display = 'inline-block';
            });
            
            // See less button click
            seeLessBtn.addEventListener('click', function() {
                post.classList.remove('expanded');
                content.style.maxHeight = '300px';
                seeMoreBtn.style.display = 'inline-block';
                seeLessBtn.style.display = 'none';
                
                // Scroll back to post top
                post.scrollIntoView({ behavior: 'smooth' });
            });
        }
    });
}

// Setup like button functionality
function setupLikeButtons() {
    const likeButtons = document.querySelectorAll('.like-action');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const likeCount = this.querySelector('.like-count');
            
            // Send AJAX request to like/unlike post
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    if (xhr.responseText === 'liked') {
                        button.classList.add('liked');
                        likeCount.textContent = parseInt(likeCount.textContent) + 1;
                    } else if (xhr.responseText === 'unliked') {
                        button.classList.remove('liked');
                        likeCount.textContent = parseInt(likeCount.textContent) - 1;
                    }
                }
            };
            
            xhr.send('like_post=1&post_id=' + postId);
        });
    });
}

// Setup share button functionality
function setupShareButtons() {
    const shareButtons = document.querySelectorAll('.share-action');
    
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postUrl = window.location.protocol + '//' + this.getAttribute('data-post-url');
            
            // Create a temporary input to copy the URL
            const tempInput = document.createElement('input');
            tempInput.value = postUrl;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            // Show a tooltip or alert
            alert('Post URL copied to clipboard!');
        });
    });
}

// Check if we need to scroll to a specific post (when coming from archive or shared link)
window.addEventListener('load', function() {
    // Check for hash in URL
    if (window.location.hash) {
        const targetId = window.location.hash.substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            // Scroll to the element
            setTimeout(function() {
                targetElement.scrollIntoView({ behavior: 'smooth' });
                
                // Highlight the post briefly
                targetElement.classList.add('highlight');
                setTimeout(function() {
                    targetElement.classList.remove('highlight');
                }, 2000);
            }, 500);
        }
    }
});