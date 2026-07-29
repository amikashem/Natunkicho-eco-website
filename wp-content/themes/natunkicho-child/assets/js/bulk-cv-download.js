/**
 * NatunKicho - Bulk CV Download (Premium Employer)
 * Handles candidate selection checkboxes and bulk ZIP download.
 */
(function() {
    'use strict';

    const MAX_SELECTION = 10;
    let selectedCandidates = new Set();

    // Wait for DOM
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.querySelector('.nk-talent-db-wrapper');
        if (!wrapper) return;

        const floatingBar = document.getElementById('nk-bulk-cv-bar');
        if (!floatingBar) return;

        const countEl     = document.getElementById('nk-bulk-cv-count');
        const downloadBtn = document.getElementById('nk-bulk-cv-download-btn');
        const clearBtn    = document.getElementById('nk-bulk-cv-clear-btn');
        const statusEl    = document.getElementById('nk-bulk-cv-status');

        // Attach checkbox listeners
        wrapper.addEventListener('change', function(e) {
            if (!e.target.classList.contains('nk-cv-checkbox')) return;

            const candidateId = parseInt(e.target.dataset.candidateId, 10);

            if (e.target.checked) {
                if (selectedCandidates.size >= MAX_SELECTION) {
                    e.target.checked = false;
                    showStatus('Maximum ' + MAX_SELECTION + ' candidates can be selected at once.', 'error');
                    return;
                }
                selectedCandidates.add(candidateId);
            } else {
                selectedCandidates.delete(candidateId);
            }

            updateUI();
        });

        // Download button
        downloadBtn.addEventListener('click', function() {
            if (selectedCandidates.size === 0) {
                showStatus('Please select at least one candidate.', 'error');
                return;
            }

            downloadBtn.disabled = true;
            downloadBtn.textContent = '⏳ Generating ZIP...';
            showStatus('Preparing your download...', 'info');

            const formData = new FormData();
            formData.append('action', 'nk_bulk_cv_download');
            formData.append('security', nkBulkCV.nonce);

            selectedCandidates.forEach(function(id) {
                formData.append('candidate_ids[]', id);
            });

            fetch(nkBulkCV.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result.success) {
                    showStatus('✅ ' + result.data.count + ' CV(s) ready! Starting download...', 'success');

                    // Trigger download
                    const link = document.createElement('a');
                    link.href = result.data.download_url;
                    link.download = 'candidate-cvs.zip';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Reset after 3 seconds
                    setTimeout(function() {
                        clearSelection();
                    }, 3000);
                } else {
                    showStatus('❌ ' + (result.data.message || 'Download failed.'), 'error');
                }
            })
            .catch(function() {
                showStatus('❌ Network error. Please try again.', 'error');
            })
            .finally(function() {
                downloadBtn.disabled = false;
                downloadBtn.textContent = '📥 Download Selected CVs';
            });
        });

        // Clear button
        clearBtn.addEventListener('click', clearSelection);

        function updateUI() {
            const count = selectedCandidates.size;
            countEl.textContent = count;

            if (count > 0) {
                floatingBar.classList.add('nk-bulk-bar-visible');
            } else {
                floatingBar.classList.remove('nk-bulk-bar-visible');
                statusEl.textContent = '';
            }

            // Update checkbox visual states
            document.querySelectorAll('.nk-cv-checkbox').forEach(function(cb) {
                const id = parseInt(cb.dataset.candidateId, 10);
                const card = cb.closest('.nk-candidate-card');
                if (card) {
                    if (selectedCandidates.has(id)) {
                        card.classList.add('nk-card-selected');
                    } else {
                        card.classList.remove('nk-card-selected');
                    }
                }
            });
        }

        function clearSelection() {
            selectedCandidates.clear();
            document.querySelectorAll('.nk-cv-checkbox').forEach(function(cb) {
                cb.checked = false;
            });
            updateUI();
            statusEl.textContent = '';
        }

        function showStatus(message, type) {
            statusEl.textContent = message;
            statusEl.className = 'nk-bulk-status nk-bulk-status-' + type;
        }
    });
})();
