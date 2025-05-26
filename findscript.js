// script.js - Stores Management System

// ===== MAIN DOCUMENT READY FUNCTION =====
document.addEventListener('DOMContentLoaded', function() {
    
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
                fetch(`find_details.php?ajax=search_part_no&term=${encodeURIComponent(term)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            data.forEach(partNo => {
                                const suggestion = document.createElement('div');
                                suggestion.className = 'suggestion-item';
                                suggestion.textContent = partNo;
                                suggestion.addEventListener('click', function() {
                                    const partNoo = partNo.split(' - ')[0].trim();
                                    findPartBox.value = partNoo;
                                    suggestionsDropdown.style.display = 'none';
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
                fetch(`find_details.php?ajax=get_makes&part_no=${encodeURIComponent(partNo)}`)
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
            fetch(`find_details.php?ajax=get_part_details&part_no=${encodeURIComponent(partNo)}&make=${encodeURIComponent(make)}`)
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
});
