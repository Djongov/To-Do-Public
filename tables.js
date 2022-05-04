// When the page loads, focus on the task input field so you can start typing a new task name immediately
document.getElementsByClassName('task-input')[0].focus();

// function to detectdouble clicks and enable contenteditable on the element
const listenForDoubleClick = (element) => {
    element.contentEditable = true;
    setTimeout(() => {
        if (document.activeElement !== element) {
            element.contentEditable = false;
        }
    }, 300);
}

// Send a POST request to the edit-entry script, who will update the db with the new name or price of the task
const editEntry = (id, newEntry, table, type) => {
    fetch('/functions/edit-entry', {
        method: 'post',
        // Let's send this secret header
        headers: {
            'secretHeader': 'badass'
        },
        body: new URLSearchParams({
            'id': id,
            'entry': newEntry,
            'table': table,
            'type': type
        })
    })
    // get the text() from the Response object
    .then(response => response.text())
    .then(text => {
        // The backend is expected to send an OK string, we decide to do nothing then (null), but if it's not OK, send the response text for more info
        (text === 'OK') ? null : alert(text);
    });
}

// Query all elements that have the contenteditable class
const contentEditableTds = document.querySelectorAll('.contenteditable');

// Loop through them
if (contentEditableTds.length > 0) {
    contentEditableTds.forEach(td => {
        // On clcik fire, the listenForDoubleClick function with the current element (event.target in ES6, this before that)
        td.addEventListener('click', (event) => {
            listenForDoubleClick(event.target);
        });
        // On blure return the contenteditable to false, part of the double click detection logic
        td.addEventListener('blur', (event) => {
            event.target.contentEditable = false;
        });
        // When the user focuses out of the selected td, send the fire the ajax function that will update the DB with the new entries. event.target.innerHTML is the actual HTML of the entry, the rest are data sets that will help know which table and entry to update in the db
        td.addEventListener('focusout', (event) => {
            //event.target.style.background = '';
            editEntry(event.target.dataset.id, event.target.innerHTML, event.target.dataset.table, event.target.dataset.type);
        });
    })
}

// Deleting an entry
const deleteButtons = document.querySelectorAll('.delete-small');

if (deleteButtons.length > 0) {
    deleteButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            let table = event.target.dataset.table;
            let id = event.target.dataset.id;
            fetch('/functions/delete-entry', {
                method: 'post',
                // Let's send this secret header
                headers: {
                    'secretHeader': 'badass'
                },
                body: new URLSearchParams({
                    'id': id,
                    'table': table
                })
            })
            // get the text() from the Response object
            .then(response => response.text())
            .then(text => {
                if (text === 'OK') {
                    let td = event.target.parentNode; 
                    let tr = td.parentNode;
                    tr.parentNode.removeChild(tr);
                } else {
                    alert(text);
                }
            });
        })
    })
}

// Send a POST request to the edit-entry script, who will update the db with the new name or price of the task
const markComplete = (table, id, action) => {
    fetch('/functions/complete-entry', {
        method: 'post',
        // Let's send this secret header
        headers: {
            'secretHeader': 'badass'
        },
        body: new URLSearchParams({
            'id': id,
            'table': table,
            'action': action
        })
    })
    // get the text() from the Response object
    .then(response => response.text())
    .then(text => {
        (text === 'OK') ? null : alert(text);
    });
}

const completeButtons = document.querySelectorAll('.mark-complete');

if (completeButtons.length > 0) {
    completeButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            toggleMarkComplete(event.target);
        })
    })
}
const undoCompleteButtons = document.querySelectorAll('.undo-complete');

if (undoCompleteButtons.length > 0) {
    undoCompleteButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            toggleUndo(event.target);
        })
    })
}

const toggleUndo = (target) => {
    let td = target.parentNode; 
    let tr = td.parentNode;
    tr.classList.remove('completed');
    target.innerHTML = '&#10003;';
    target.title = 'Mark Complete';
    target.classList.replace('undo-complete', 'mark-complete');
    markComplete(target.dataset.table, target.dataset.id, 'undo');
    target.addEventListener('click', (event) => {
        toggleMarkComplete(event.target);
    });
}

const toggleMarkComplete = (target) => {
    let td = target.parentNode; 
    let tr = td.parentNode;
    tr.classList.add('completed');
    target.innerHTML = '&#9100;';
    target.title = 'Undo Complete';
    target.classList.replace('mark-complete', 'undo-complete');
    markComplete(target.dataset.table, target.dataset.id, 'mark-complete');
    target.addEventListener('click', (event) => {
        toggleUndo(event.target);
    });
}