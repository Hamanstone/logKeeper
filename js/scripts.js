document.addEventListener('DOMContentLoaded', () => {
    const logPreviewModal = document.getElementById('log-preview-modal');
    const closeModal = document.querySelector('.close');
    const logContentEl = document.getElementById('log-content');
    const logFilenameEl = document.getElementById('log-filename');
    const logDetailsEl = document.getElementById('log-details');
    const themeToggleBtn = document.getElementById('toggle-theme');
    const viewMarkdownBtn = document.getElementById('view-markdown');
    const markdownView = document.getElementById('markdown-view');
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
    let isDarkMode = false;
    let currentLogSiblings = [];
    let currentLogIndex = -1;
    let isMarkdownFile = false;
    let markdownVisible = false;

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
                isMarkdownFile = /\.(md|markdown)$/i.test(data.file_info.name || '');

                // Reset state before showing new content
                lineNumbersVisible = false;
                toggleLineNumbersBtn.textContent = 'Show Line Numbers';
                searchInput.value = '';
                markdownVisible = false;
                if (markdownView) {
                    markdownView.style.display = 'none';
                    markdownView.innerHTML = '';
                }
                logContentEl.style.display = 'block';

                updateLogView(); // Use the new unified render function
                updateMarkdownControls();

                logPreviewModal.style.display = 'block';
                updatePrevNextButtons();
                applyThemePreference();
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

    function updateMarkdownControls() {
        if (!viewMarkdownBtn || !markdownView) return;
        if (isMarkdownFile) {
            viewMarkdownBtn.style.display = 'inline-block';
            viewMarkdownBtn.textContent = markdownVisible ? 'View Raw' : 'View Markdown';
        } else {
            viewMarkdownBtn.style.display = 'none';
            markdownVisible = false;
            markdownView.style.display = 'none';
            logContentEl.style.display = 'block';
        }
    }

    function renderMarkdownView() {
        if (!markdownView) return;
        if (typeof marked === 'undefined') {
            markdownView.innerHTML = '<p>Markdown renderer not available.</p>';
            return;
        }
        markdownView.innerHTML = marked.parse(originalLogContent || '');
    }

    // 3. Modal Buttons and Actions
    function applyThemePreference() {
        if (!themeToggleBtn) return;
        if (isDarkMode) {
            logPreviewModal.classList.add('dark-mode');
            themeToggleBtn.textContent = 'Light Mode';
        } else {
            logPreviewModal.classList.remove('dark-mode');
            themeToggleBtn.textContent = 'Dark Mode';
        }
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            isDarkMode = !isDarkMode;
            applyThemePreference();
        });

        applyThemePreference();
    }

    if (viewMarkdownBtn && markdownView) {
        viewMarkdownBtn.addEventListener('click', () => {
            if (!isMarkdownFile) return;
            markdownVisible = !markdownVisible;
            if (markdownVisible) {
                renderMarkdownView();
                markdownView.style.display = 'block';
                logContentEl.style.display = 'none';
            } else {
                markdownView.style.display = 'none';
                logContentEl.style.display = 'block';
                updateLogView();
            }
            updateMarkdownControls();
        });
    }

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

    // Helper function to show "Copied!" feedback
    function showCopyFeedback(button) {
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        setTimeout(() => {
            button.textContent = originalText;
        }, 2000);
    }

    copyBtn.addEventListener('click', () => {
        const textToCopy = originalLogContent;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                showCopyFeedback(copyBtn);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy text automatically. Please try again or copy manually.');
                fallbackCopyText(textToCopy);
            });
        } else {
            fallbackCopyText(textToCopy);
        }
    });

    function fallbackCopyText(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        textArea.style.top = '-9999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopyFeedback(copyBtn);
            } else {
                prompt('Failed to copy automatically. Please copy the text below manually:', text);
            }
        } catch (err) {
            console.error('Fallback copy error:', err);
            prompt('Failed to copy automatically. Please copy the text below manually:', text);
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

    // Add keyboard navigation for prev/next
    document.addEventListener('keydown', (e) => {
        if (logPreviewModal.style.display === 'block') { // Only active when modal is open
            if (e.key === 'ArrowLeft') {
                prevBtn.click();
            } else if (e.key === 'ArrowRight') {
                nextBtn.click();
            }
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
