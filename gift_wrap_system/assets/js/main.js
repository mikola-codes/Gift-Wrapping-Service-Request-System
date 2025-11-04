document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be reversed.')) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.kiosk-main a').forEach(function(link) {
        link.addEventListener('mouseover', function() {
            link.style.transform = 'scale(1.02)';
        });
        link.addEventListener('mouseout', function() {
            link.style.transform = 'scale(1)';
        });
    });
});