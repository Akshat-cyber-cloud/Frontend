// User Authentication State Management

// Function to initialize authentication on page load
function initializeAuth() {
    // Check if user is logged in
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    
    // Update UI based on login status
    updateUIForLoginState(userData);
    
    // Populate state dropdown
    populateStateDropdown();
}

// Update UI elements based on login state
function updateUIForLoginState(userData) {
    const userProfile = document.getElementById('userProfile');
    const stateSelect = document.getElementById('stateSelect');
    const welcomeMessage = document.getElementById('welcomeMessage');
    const userNameDisplay = document.getElementById('userNameDisplay');
    const navUserName = document.getElementById('navUserName');
    
    if (userData && userData.email) {
        // User is logged in
        if (welcomeMessage && userNameDisplay) {
            welcomeMessage.classList.remove('hidden');
            userNameDisplay.textContent = userData.name || userData.email;
        }
        
        if (userProfile) {
            userProfile.classList.remove('hidden');
            userProfile.classList.add('flex');
            
            if (navUserName) {
                navUserName.textContent = userData.name || userData.email;
            }
        }
    }
}

// Populate state dropdown
function populateStateDropdown() {
    const stateSelect = document.getElementById('stateSelect');
    if (!stateSelect) return;
    
    const states = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
        'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
        'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
        'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
        'Uttar Pradesh', 'Uttarakhand', 'West Bengal'
    ];
    
    // Clear existing options
    stateSelect.innerHTML = '<option value="" disabled selected>Select State</option>';
    
    // Add states to dropdown
    const fragment = document.createDocumentFragment();
    states.forEach(state => {
        const option = document.createElement('option');
        option.value = state.toLowerCase().replace(/\s+/g, '-');
        option.textContent = state;
        fragment.appendChild(option);
    });
    stateSelect.appendChild(fragment);
    
    // Set selected state if exists in localStorage
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    if (userData.state) {
        stateSelect.value = userData.state;
    }
    
    // Update state on change
    stateSelect.addEventListener('change', function() {
        const userData = JSON.parse(localStorage.getItem('userData') || '{}');
        userData.state = this.value;
        localStorage.setItem('userData', JSON.stringify(userData));
    });
}

// Logout function
function logout() {
    localStorage.removeItem('userData');
    window.location.href = 'index.html';
}

// No need to call initializeAuth here since we're calling it directly in HTML 