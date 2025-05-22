// script.js - Stores Management System

// ===== MAIN DOCUMENT READY FUNCTION =====
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== ALERT HANDLING =====
    const okButton = document.querySelector('.alert-ok-btn');
    if (okButton) {
        okButton.addEventListener('click', function() {
            document.querySelector('.alert-box').style.display = 'none';
            document.querySelector('.overlay').style.display = 'none';
        });
    }

    // ===== FIND PART FUNCTIONALITY =====
    const findPartBox = document.getElementById('find-part-box');
    const findSuggestions = document.getElementById('find-suggestions');
    const findMakeGroup = document.getElementById('find-make-group');
    const findMakeSelect = document.getElementById('find-make-select');
    const findDetailsBtn = document.getElementById('find-details-btn');
    const findDetailsResult = document.getElementById('find-details-result');

    if (findPartBox) {
        // Create a custom dropdown container
        const suggestionsDropdown = document.createElement('div');
        suggestionsDropdown.className = 'custom-suggestions';
        suggestionsDropdown.style.display = 'none';
        findPartBox.parentNode.appendChild(suggestionsDropdown);

        // Show suggestions as user types
        findPartBox.addEventListener('input', function() {
            const term = findPartBox.value.trim();
            suggestionsDropdown.innerHTML = '';
            suggestionsDropdown.style.display = 'none';
            
            if (term.length > 0) {
                fetch(`index.php?ajax=search_part_no&term=${encodeURIComponent(term)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            data.forEach(partNo => {
                                const suggestion = document.createElement('div');
                                suggestion.className = 'suggestion-item';
                                suggestion.textContent = partNo;
                                suggestion.addEventListener('click', function() {
                                    findPartBox.value = partNo;
                                    suggestionsDropdown.style.display = 'none';
                                    // Trigger change event to load makes
                                    const event = new Event('change');
                                    findPartBox.dispatchEvent(event);
                                });
                                suggestionsDropdown.appendChild(suggestion);
                            });
                            suggestionsDropdown.style.display = 'block';
                        }
                    });
            }
        });

        // Hide dropdown when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (e.target !== findPartBox) {
                suggestionsDropdown.style.display = 'none';
            }
        });

        // Rest of your find part functionality...
        findPartBox.addEventListener('change', function() {
            const partNo = findPartBox.value.trim();
            findMakeSelect.innerHTML = '';
            findMakeGroup.style.display = 'none';
            findDetailsBtn.style.display = 'none';
            findDetailsResult.textContent = '';
            
            if (partNo) {
                fetch(`index.php?ajax=get_makes&part_no=${encodeURIComponent(partNo)}`)
                    .then(response => response.json())
                    .then(makes => {
                        if (makes.length === 1) {
                            findMakeSelect.innerHTML = `<option value="${makes[0]}">${makes[0]}</option>`;
                            getPartDetails(partNo, makes[0]);
                        } else if (makes.length > 1) {
                            findMakeSelect.innerHTML = '<option value="">Select Make</option>';
                            makes.forEach(make => {
                                const option = document.createElement('option');
                                option.value = make;
                                option.textContent = make;
                                findMakeSelect.appendChild(option);
                            });
                            findMakeGroup.style.display = 'block';
                        }
                    });
            }
        });

        findMakeSelect.addEventListener('change', function() {
            if (this.value) {
                getPartDetails(findPartBox.value.trim(), this.value);
            }
        });

        function getPartDetails(partNo, make) {
            findDetailsResult.innerHTML = 'Loading...';
            fetch(`index.php?ajax=get_part_details&part_no=${encodeURIComponent(partNo)}&make=${encodeURIComponent(make)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.found) {
                        findDetailsResult.innerHTML = `
                            <div><strong>Part:</strong> ${partNo}</div>
                            <div><strong>Make:</strong> ${make}</div>
                            <div><strong>Total:</strong> ${data.quantity}</div>
                            <div><strong>Available:</strong> ${data.availableqty}</div>
                            <div><strong>Used:</strong> ${data.usedqty}</div>
                        `;
                    } else {
                        findDetailsResult.innerHTML = '<span class="error">Details not found</span>';
                    }
                });
        }
    }

    // ===== ADD EXISTING CONNECTORS FORM =====
    const connInput = document.getElementById('conn-name');
    const connDatalist = document.getElementById('conn-suggestions');
    const addMakeSelect = document.getElementById('add-make');

    if (connInput) {
        connInput.addEventListener('input', function() {
            const term = connInput.value;
            if (term.length === 0) {
                connDatalist.innerHTML = '';
                addMakeSelect.disabled = false;
                resetMakeSelect(addMakeSelect, 'add-make');
                return;
            }
            fetch(`index.php?ajax=search_part_no&term=${encodeURIComponent(term)}`)
                .then(response => response.json())
                .then(data => {
                    connDatalist.innerHTML = '';
                    data.forEach(partNo => {
                        const option = document.createElement('option');
                        option.value = partNo;
                        connDatalist.appendChild(option);
                    });
                });
        });

        connInput.addEventListener('change', function() {
            const partNo = connInput.value;
            if (partNo.length === 0) {
                addMakeSelect.disabled = false;
                resetMakeSelect(addMakeSelect, 'add-make');
                return;
            }
            fetch(`index.php?ajax=get_makes&part_no=${encodeURIComponent(partNo)}`)
                .then(response => response.json())
                .then(makes => {
                    handleMakeSelection(makes, addMakeSelect, 'add-make', 'add-conn');
                });
        });
    }

    // ===== REQUIRED CONNECTORS FORM =====
    const reqPartInput = document.getElementById('req-part');
    const reqMakeSelect = document.getElementById('req-make');
    const reqDatalist = document.getElementById('req-suggestions');

    if (reqPartInput) {
        reqPartInput.addEventListener('input', function() {
            const term = reqPartInput.value;
            if (term.length === 0) {
                reqDatalist.innerHTML = '';
                reqMakeSelect.disabled = false;
                resetMakeSelect(reqMakeSelect, 'req-make');
                return;
            }
            fetch(`index.php?ajax=search_part_no&term=${encodeURIComponent(term)}`)
                .then(response => response.json())
                .then(data => {
                    reqDatalist.innerHTML = '';
                    data.forEach(partNo => {
                        const option = document.createElement('option');
                        option.value = partNo;
                        reqDatalist.appendChild(option);
                    });
                });
        });

        reqPartInput.addEventListener('change', function() {
            const partNo = reqPartInput.value;
            if (partNo.length === 0) {
                reqMakeSelect.disabled = false;
                resetMakeSelect(reqMakeSelect, 'req-make');
                return;
            }
            fetch(`index.php?ajax=get_makes&part_no=${encodeURIComponent(partNo)}`)
                .then(response => response.json())
                .then(makes => {
                    handleMakeSelection(makes, reqMakeSelect, 'req-make', 'required-conn');
                });
        });
    }

    // ===== FORM TOGGLE FUNCTIONALITY =====
    // Initialize form based on URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const formParam = urlParams.get('form');
    if (formParam) {
        showForm(formParam);
    }
});

// ===== UTILITY FUNCTIONS =====
function showForm(formId) {
    // Hide all forms
    document.querySelectorAll('.form-section').forEach(form => {
        form.classList.remove('active');
    });
    
    // Show selected form
    document.getElementById(formId).classList.add('active');
    
    // Update active button
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`.toggle-btn[onclick="showForm('${formId}')"]`).classList.add('active');
    
    // Update URL without reloading
    history.pushState(null, null, `index.php?form=${formId}`);
}

function resetMakeSelect(selectElement, inputName) {
    selectElement.innerHTML = `
        <option value="">Select</option>
        <option value="Amphenol">Amphenol</option>
        <option value="Glenair">Glenair</option>
        <option value="SOURIAU">Souriau</option>
        <option value="ITT Cannon">ITT Cannon</option>
    `;
    selectElement.value = "";
    
    // Remove hidden input if exists
    const hiddenInput = document.querySelector(`input[name="${inputName}"][type="hidden"]`);
    if (hiddenInput) {
        hiddenInput.remove();
    }
}

function handleMakeSelection(makes, selectElement, inputName, formId) {
    let hiddenInput = document.querySelector(`input[name="${inputName}"][type="hidden"]`);
    
    if (makes.length === 1) {
        // Single make - auto-select and disable dropdown
        selectElement.innerHTML = `<option value="${makes[0]}">${makes[0]}</option>`;
        selectElement.value = makes[0];
        selectElement.disabled = true;
        
        // Create hidden input if doesn't exist
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = inputName;
            document.getElementById(formId).appendChild(hiddenInput);
        }
        hiddenInput.value = makes[0];
    } else if (makes.length > 1) {
        // Multiple makes - enable dropdown
        selectElement.disabled = false;
        selectElement.innerHTML = '<option value="">Select</option>';
        makes.forEach(mk => {
            const option = document.createElement('option');
            option.value = mk;
            option.textContent = mk;
            selectElement.appendChild(option);
        });
        
        // Remove hidden input if exists
        if (hiddenInput) {
            hiddenInput.remove();
        }
    } else {
        // No makes found - reset to default
        selectElement.disabled = false;
        resetMakeSelect(selectElement, inputName);
    }
}