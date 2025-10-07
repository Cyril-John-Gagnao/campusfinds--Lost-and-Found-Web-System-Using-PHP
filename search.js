
// Escape HTML to prevent XSS
function escapeHTML(str) {
    return str.replace(/[&<>"']/g, function (m) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[m];
    });
}

// Debounce timer variable
let debounceTimer;

// Generic debounced search function
function debouncedSearch(actionUrl, fullListId = 'fullUserListContainer', resultsId = 'results') {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        let input = document.getElementById('searchInput').value.trim();
        performSearch(input, actionUrl, fullListId, resultsId);
    }, 300); // 300ms delay
}

//  AJAX search and render results
function performSearch(input, actionUrl, fullListId, resultsId) {
    const fullList = document.getElementById(fullListId);
    const results = document.getElementById(resultsId);

    if (input.length === 0) {
        if (fullList) fullList.style.display = 'block';
        if (results) results.innerHTML = '';
        return;
    }


    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (fullList) fullList.style.display = 'none';
            if (results) {
              results.innerHTML = `
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Search Results for: 
                            <span class="italic text-blue-600">${escapeHTML(input)}</span>
                        </h2>
                        ${this.responseText}
                    `;
            }
        } else if (this.readyState === 4) {
            alert("Could not connect to server");
        }
    };

    xhr.open("GET", actionUrl + "?keyword=" + encodeURIComponent(input), true);
    xhr.send();
}
