// javascript/account-id-assigner.js
// Optimized: Generates a random 6-digit ID for a specific form on submission

document.addEventListener('DOMContentLoaded', () => {
    // Target form by ID to be specific (assuming your registration form has id="register-form")
    const form = document.querySelector('#register-form');

    // Exit early if no form is found (prevents errors on non-form pages)
    if (!form) return;

    // Create hidden input once and append it
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'accountnr';
    hiddenInput.id = 'accountnr';
    form.appendChild(hiddenInput);

    // Submit handler for ID generation
    const handleSubmit = (event) => {
        // Generate random 6-digit ID (100000 to 999999)
        const randomNum = Math.floor(100000 + Math.random() * 900000);
        hiddenInput.value = randomNum.toString();
    };

    // Add submit listener
    form.addEventListener('submit', handleSubmit);

    // Optional: Clean up listener on page unload (uncommon, but thorough)
    window.addEventListener('unload', () => {
        form.removeEventListener('submit', handleSubmit);
    });
});