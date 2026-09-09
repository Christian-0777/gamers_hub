document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.togglePassword);
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.textContent = visible ? 'Show' : 'Hide';
        button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
    });
});

const password = document.querySelector('#password');
const passwordConfirmation = document.querySelector('#password_confirmation');
const terms = document.querySelector('#terms');
const createAccountButton = document.querySelector('#create-account-button');

const updateSignupButton = () => {
    if (!password || !passwordConfirmation || !terms || !createAccountButton) return;
    const passwordsMatch = password.value !== '' && password.value === passwordConfirmation.value;
    createAccountButton.disabled = !passwordsMatch || !terms.checked;
};

[password, passwordConfirmation, terms].forEach((field) => field?.addEventListener('input', updateSignupButton));
[terms].forEach((field) => field?.addEventListener('change', updateSignupButton));
updateSignupButton();

document.querySelectorAll('[data-coming-soon]').forEach((button) => {
    button.addEventListener('click', () => {
        window.alert(`${button.dataset.comingSoon} sign up is coming soon.`);
    });
});
