    document.addEventListener('DOMContentLoaded', function() {
        
        // Star rating functionality
        const stars = document.querySelectorAll('.rating-stars i');
        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = this.getAttribute('data-rating');
                resetStars();
                highlightStars(rating);
            });
            
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                resetStars();
                highlightStars(rating);
                // Here you would normally send the rating to the server
                console.log(`User rated: ${rating} stars`);
            });
        });
        
        // Reset stars on mouseout from rating container
        const ratingContainer = document.querySelector('.rating-stars');
        ratingContainer.addEventListener('mouseout', function() {
            resetStars();
        });
        
        function resetStars() {
            stars.forEach(star => {
                star.classList.remove('bi-star-fill');
                star.classList.add('bi-star');
            });
        }
        
        function highlightStars(count) {
            for (let i = 0; i < count; i++) {
                stars[i].classList.remove('bi-star');
                stars[i].classList.add('bi-star-fill');
            }
        }
    });

    // select avatar
    function selectAvatar(element) {
        // Remove selection from all avatars
        document.querySelectorAll('[data-avatar-id]').forEach(img => {
            img.classList.remove('border-primary', 'border-3');
        });
    
        // Add highlight to selected avatar
        element.classList.add('border-primary', 'border-3');
    
        // Set the hidden input
        document.getElementById('selected_avatar_id').value = element.getAttribute('data-avatar-id');
    }
    
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.querySelector('.modal-backdrop')?.remove();
    });
