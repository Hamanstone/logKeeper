document.addEventListener('DOMContentLoaded', () => {
    const logPreviewModal = document.getElementById('log-preview-modal');
    const closeModal = document.querySelector('.close');
    const logContentEl = document.getElementById('log-content');
    const logFilenameEl = document.getElementById('log-filename');
    const logDetailsEl = document.getElementById('log-details');
    const copyBtn = document.getElementById('copy-log');
    const downloadBtn = document.getElementById('download-log');
    const prevBtn = document.getElementById('prev-log');
    const nextBtn = document.getElementById('next-log');
    const toggleLineNumbersBtn = document.getElementById('toggle-line-numbers');
    const searchInput = document.getElementById('log-search-input');
    const logsTableBody = document.querySelector('#logs-table tbody');

    let currentLogPath = '';
    let originalLogContent = '';
    let lineNumbersVisible = false;
    let currentLogSiblings = [];
    let currentLogIndex = -1;

    // 1. Show Log Preview Modal
    function showLogPreview(logPath) {
        if (!logPath) return;
        currentLogPath = logPath;

        fetch(`api/log_preview.php?path=${encodeURIComponent(logPath)}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                originalLogContent = data.content;
                logFilenameEl.textContent = data.file_info.name;
                logDetailsEl.textContent = `Size: ${data.file_info.size} | Modified: ${data.file_info.modified}`;

                // Reset state before showing new content
                lineNumbersVisible = false;
                toggleLineNumbersBtn.textContent = 'Show Line Numbers';
                searchInput.value = '';

                updateLogView(); // Use the new unified render function

                logPreviewModal.style.display = 'block';
                updatePrevNextButtons();
            })
            .catch(error => {
                console.error('Error fetching log preview:', error);
                alert(`Could not load log: ${error.message}`);
            });
    }

    function escapeHtml(unsafe) {
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // 2. Unified Log View Rendering
    function updateLogView() {
        const searchTerm = searchInput.value;
        let linesToRender = [];
        let hasMatches = false;

        // Step 1: Prepare lines for rendering (filtering and highlighting if necessary)
        if (searchTerm) {
            const searchRegex = new RegExp(searchTerm.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'gi');
            linesToRender = originalLogContent.split('\n').map((line, index) => {
                if (line.toLowerCase().includes(searchTerm.toLowerCase())) {
                    const highlightedLine = escapeHtml(line).replace(searchRegex, (match) => `<mark>${match}</mark>`);
                    return { content: highlightedLine, originalLine: index + 1 };
                }
                return null;
            }).filter(Boolean); // Remove nulls (lines that didn't match)

            hasMatches = linesToRender.length > 0;
        } else {
            // No search term, display all lines
            linesToRender = originalLogContent.split('\n').map((line, index) => {
                return { content: escapeHtml(line), originalLine: index + 1 };
            });
            hasMatches = true;
        }

        if (!hasMatches) {
            logContentEl.innerHTML = '<p>No matches found.</p>';
            return;
        }

        // Step 2: Render with or without line numbers
        if (lineNumbersVisible) {
            const numberedHtml = linesToRender.map(lineInfo => {
                const lineContent = lineInfo.content === '' ? '&nbsp;' : lineInfo.content;
                return `<div class="line"><span class="line-number">${lineInfo.originalLine}</span><span class="line-content">${lineContent}</span></div>`;
            }).join('');
            logContentEl.innerHTML = numberedHtml;
        } else {
            const plainHtml = linesToRender.map(lineInfo => lineInfo.content).join('\n');
            logContentEl.innerHTML = `<pre>${plainHtml}</pre>`;
        }
    }

    // 3. Modal Buttons and Actions
    closeModal.addEventListener('click', () => {
        logPreviewModal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target == logPreviewModal) {
            logPreviewModal.style.display = 'none';
        }
    });

    downloadBtn.addEventListener('click', () => {
        if (currentLogPath) {
            window.location.href = `api/download.php?path=${encodeURIComponent(currentLogPath)}`;
        }
    });

    copyBtn.addEventListener('click', () => {
        const textToCopy = originalLogContent;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                alert('Log content copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy text.');
                fallbackCopyText(textToCopy);
            });
        } else {
            fallbackCopyText(textToCopy);
        }
    });

    function fallbackCopyText(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed'; // Avoid scrolling to bottom
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                alert('Log content copied (fallback method)!');
            } else {
                alert('Fallback copy was unsuccessful.');
            }
        } catch (err) {
            console.error('Fallback copy error:', err);
            alert('Fallback copy was unsuccessful.');
        }
        document.body.removeChild(textArea);
    }

    // 4. Prev/Next Logic
    function updatePrevNextButtons() {
        prevBtn.disabled = currentLogIndex <= 0;
        nextBtn.disabled = currentLogIndex >= currentLogSiblings.length - 1;
    }

    prevBtn.addEventListener('click', () => {
        if (currentLogIndex > 0) {
            currentLogIndex--;
            const prevRow = currentLogSiblings[currentLogIndex];
            const logPath = prevRow.querySelector('.btn-preview').dataset.path;
            showLogPreview(logPath);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentLogIndex < currentLogSiblings.length - 1) {
            currentLogIndex++;
            const nextRow = currentLogSiblings[currentLogIndex];
            const logPath = nextRow.querySelector('.btn-preview').dataset.path;
            showLogPreview(logPath);
        }
    });

    // 5. Line Numbers and Search
    toggleLineNumbersBtn.addEventListener('click', () => {
        lineNumbersVisible = !lineNumbersVisible;
        toggleLineNumbersBtn.textContent = lineNumbersVisible ? 'Hide Line Numbers' : 'Show Line Numbers';
        updateLogView();
    });

    searchInput.addEventListener('input', () => {
        updateLogView();
    });

    // 6. Event Listener for logs table
    logsTableBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-preview')) {
            const logPath = e.target.dataset.path;
            const parentRow = e.target.closest('tr');
            currentLogSiblings = Array.from(logsTableBody.querySelectorAll('tr'));
            currentLogIndex = currentLogSiblings.findIndex(row => row === parentRow);
            showLogPreview(logPath);
        }
    });
});