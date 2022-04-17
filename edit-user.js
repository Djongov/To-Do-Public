const changePasswordCheckBox = document.getElementById('turn-password-on');
changePasswordCheckBox.addEventListener('change', (event) => {
    const passwordInput = document.getElementsByName('password')[0];
    // Attempt to save the password value before resetting it later
    let passwordValue = document.getElementsByName('password')[0].value;
    // If checbox is checked, remove the disabled state of the password field
    if (event.target.checked) {
        passwordInput.disabled = false;
        // Also make it empty so you can write the new password
        passwordInput.value = '';
    } else {
        // Return the disabled state on unchecking
        passwordInput.disabled = true;
        // Return the value it used to be (just for visuals, because disabled input fields do not send data to POST anyway)
        passwordInput.value = passwordValue;
    }
});