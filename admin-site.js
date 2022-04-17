// Bind a click event to the delete list button to ask for confirmation
document.getElementById('delete-list').addEventListener('click', (event) => {
    let choice = confirm('Are you sure you want to delete this list?');
            if (choice) {
                document.getElementById('form-delete-list').submit();
            } else {
                event.preventDefault();
            }
    askForConfirmation('form-delete-list');
});

// Query the delete user buttons
const deleteButtons = document.querySelectorAll('button.delete');

// Assign the click event to ask for confirmation to delete the user
deleteButtons.forEach(el => {
    el.addEventListener('click', (event) => {
        let choice = confirm('Are you sure you want to delete this user?');
        if (choice) {

        } else {
            event.preventDefault();
        }
    })
})