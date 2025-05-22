function showForm(formId) {
    const formIds = ['add-part', 'add-conn', 'required-conn'];
    const formSections = document.querySelectorAll('.form-section');
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    
    // Validate the formId
    if (!formIds.includes(formId)) {
        console.error('Invalid form ID:', formId);
        return;
    }

    // Hide all forms and deactivate all buttons
    formSections.forEach(form => form.classList.remove('active'));
    toggleButtons.forEach(btn => btn.classList.remove('active'));

    // Show the selected form and activate its button
    const activeForm = document.getElementById(formId);
    if (activeForm) {
        activeForm.classList.add('active');
        
        // Reset and enable all controls in the active form
        if (activeForm.reset) activeForm.reset();
        activeForm.querySelectorAll('select, input[list]').forEach(control => {
            if (control.tagName === 'SELECT') {
                control.disabled = false;
                if (!control.value) control.value = "";
            } else {
                control.value = "";
            }
        });
    }

    // Activate the corresponding button
    const btnIndex = formIds.indexOf(formId);
    if (btnIndex !== -1 && toggleButtons[btnIndex]) {
        toggleButtons[btnIndex].classList.add('active');
    }

    // Reset all inactive forms
    formSections.forEach(form => {
        if (form !== activeForm) {
            if (form.reset) form.reset();
            form.querySelectorAll('select, input[list]').forEach(control => {
                if (control.tagName === 'SELECT') {
                    control.disabled = false;
                    control.value = "";
                } else {
                    control.value = "";
                }
            });
        }
    });
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    // Activate the first form by default if none is active
    const activeForm = document.querySelector('.form-section.active');
    if (!activeForm) {
        showForm('add-part'); // Default to first form
    } else {
        // Ensure the corresponding button is active
        const formId = activeForm.id;
        document.querySelectorAll('.toggle-btn').forEach((btn, index) => {
            if (btn.classList.contains('active') && btnIndex !== formIds.indexOf(formId)) {
                btn.classList.remove('active');
            }
        });
        const btnIndex = ['add-part', 'add-conn', 'required-conn'].indexOf(formId);
        if (btnIndex !== -1) {
            document.querySelectorAll('.toggle-btn')[btnIndex].classList.add('active');
        }
    }
});