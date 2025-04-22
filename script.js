// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    // State selection functionality
    const stateSelect = document.getElementById('stateSelect');
    if (stateSelect) {
        stateSelect.addEventListener('change', function() {
            const selectedState = this.value;
            if (selectedState) {
                // Store the selected state in localStorage
                localStorage.setItem('selectedState', selectedState);
                
                // You can add additional functionality here, like:
                // - Filtering events based on state
                // - Showing state-specific content
                // - Updating the UI to reflect the selected state
                console.log(`Selected state: ${selectedState}`);
                
                // Example: Show a notification
                alert(`Events will be filtered for ${this.options[this.selectedIndex].text}`);
            }
        });

        // Load previously selected state if exists
        const savedState = localStorage.getItem('selectedState');
        if (savedState) {
            stateSelect.value = savedState;
        }
    }

    // Add smooth scrolling to all links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Search functionality
    const searchInput = document.querySelector('input[type="text"]');
    const searchButton = document.querySelector('button');
    
    searchButton.addEventListener('click', function() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            alert(`Searching for: ${searchTerm}`);
            // Here you would typically make an API call to search for events
        }
    });

    // Book Now button functionality
    document.querySelectorAll('.bg-blue-600').forEach(button => {
        button.addEventListener('click', function() {
            const eventTitle = this.closest('.bg-white').querySelector('h3').textContent;
            alert(`Booking ticket for: ${eventTitle}`);
            // Here you would typically redirect to a booking page or show a modal
        });
    });

    // Category navigation
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.getAttribute('href').substring(1);
            if (category) {
                alert(`Navigating to ${category} category`);
                // Here you would typically filter or load the selected category
            }
        });
    });
}); 