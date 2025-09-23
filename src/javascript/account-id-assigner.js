// javascript/account-id-assigner.js
// Generates a random 6-digit ID on form submission and sets it as hidden field

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'accountnr';
    hiddenInput.id = 'accountnr';
    form.appendChild(hiddenInput);

    form.addEventListener('submit', function (event) {
        // Generate random 6-digit ID (100000 to 999999)
        const randomNum = Math.floor(100000 + Math.random() * 900000);
        hiddenInput.value = randomNum.toString();
    });
});