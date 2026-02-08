@extends('admin.layout')

@section('title', 'Background Settings')
@section('page-icon')<i class="fa-solid fa-images"></i>@endsection
@section('page-title', 'Background Settings')

@section('header-actions')
@endsection

@section('styles')
<style>
    .upload-area {
        border: 2px dashed #ddd;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fafafa;
    }

    .upload-area:hover {
        border-color: #1a1a2e;
        background: #f0f0f0;
    }

    .upload-area.dragover {
        border-color: #28a745;
        background: #e8f5e9;
    }

    .upload-area i {
        font-size: 2rem;
        color: #999;
        margin-bottom: 0.5rem;
    }

    .upload-area p {
        margin: 0;
        color: #666;
    }

    .backgrounds-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .background-item {
        position: relative;
        border-radius: 0.5rem;
        overflow: hidden;
        aspect-ratio: 16/9;
        background: #f0f0f0;
    }

    .background-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .background-item.hidden {
        opacity: 0.5;
    }

    .background-item .overlay-actions {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 0.5rem;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        display: flex;
        justify-content: flex-end;
        gap: 0.25rem;
    }

    .background-item .order-badge {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
    }

    .background-item .hidden-badge {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(220,53,69,0.9);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
    }

    .settings-row {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .settings-row .form-group {
        flex: 1;
        min-width: 150px;
    }

    .range-value {
        display: inline-block;
        min-width: 3rem;
        text-align: right;
        font-weight: 500;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #999;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }
</style>
@endsection

@section('content')
<!-- Slideshow Settings -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-sliders"></i> Slideshow Settings
    </div>
    <form id="settingsForm" onsubmit="saveSettings(event)">
        <div class="settings-row">
            <div class="form-group">
                <label for="slide_interval">Slide Interval</label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="range" id="slide_interval" name="slide_interval" min="3" max="60" value="10" 
                           style="flex: 1;" oninput="updateRangeValue(this, 'interval_value', 's')">
                    <span class="range-value" id="interval_value">10s</span>
                </div>
            </div>
            <div class="form-group">
                <label for="transition_duration">Transition Duration</label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="range" id="transition_duration" name="transition_duration" min="1" max="5" value="2"
                           style="flex: 1;" oninput="updateRangeValue(this, 'transition_value', 's')">
                    <span class="range-value" id="transition_value">2s</span>
                </div>
            </div>
            <div class="form-group">
                <label for="overlay_opacity">Overlay Darkness</label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="range" id="overlay_opacity" name="overlay_opacity" min="0" max="80" value="50"
                           style="flex: 1;" oninput="updateRangeValue(this, 'opacity_value', '%')">
                    <span class="range-value" id="opacity_value">50%</span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="align-self: flex-end;">
                <i class="fa-solid fa-save"></i> Save
            </button>
        </div>
    </form>
</div>

<!-- Upload Area -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-upload"></i> Upload Background
    </div>
    <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
        <i class="fa-solid fa-cloud-upload-alt"></i>
        <p>Click or drag & drop images here</p>
        <p style="font-size: 0.8rem; opacity: 0.7;">JPG, PNG, WebP (max 10MB)</p>
    </div>
    <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" multiple style="display: none;" onchange="uploadFiles(this.files)">
</div>

<!-- Backgrounds Grid -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-th"></i> Background Images
        <span id="backgroundCount" style="font-weight: normal; opacity: 0.7; margin-left: 0.5rem;"></span>
    </div>
    <div id="backgroundsContainer">
        <div class="empty-state">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Loading backgrounds...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let backgrounds = [];
    let settings = {};

    document.addEventListener('DOMContentLoaded', () => {
        loadSettings();
        loadBackgrounds();
        setupDragDrop();
    });

    function updateRangeValue(input, spanId, suffix) {
        document.getElementById(spanId).textContent = input.value + suffix;
    }

    async function loadSettings() {
        try {
            const response = await fetch('/api/backgrounds/settings');
            const data = await response.json();

            if (data.success) {
                settings = data.settings;
                document.getElementById('slide_interval').value = settings.slide_interval;
                document.getElementById('transition_duration').value = settings.transition_duration;
                document.getElementById('overlay_opacity').value = settings.overlay_opacity;
                
                updateRangeValue(document.getElementById('slide_interval'), 'interval_value', 's');
                updateRangeValue(document.getElementById('transition_duration'), 'transition_value', 's');
                updateRangeValue(document.getElementById('overlay_opacity'), 'opacity_value', '%');
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    async function saveSettings(event) {
        event.preventDefault();

        const formData = {
            slide_interval: parseInt(document.getElementById('slide_interval').value),
            transition_duration: parseInt(document.getElementById('transition_duration').value),
            overlay_opacity: parseInt(document.getElementById('overlay_opacity').value),
        };

        try {
            const response = await fetch('/api/backgrounds/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                showToast('Settings saved', 'success');
            } else {
                showToast(data.message || 'Error saving settings', 'error');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            showToast('Error saving settings', 'error');
        }
    }

    async function loadBackgrounds() {
        try {
            const response = await fetch('/api/backgrounds/all');
            const data = await response.json();

            if (data.success) {
                backgrounds = data.backgrounds;
                renderBackgrounds();
            }
        } catch (error) {
            console.error('Error loading backgrounds:', error);
        }
    }

    function renderBackgrounds() {
        const container = document.getElementById('backgroundsContainer');
        const countSpan = document.getElementById('backgroundCount');
        
        const visibleCount = backgrounds.filter(bg => bg.is_visible).length;
        countSpan.textContent = `(${visibleCount} visible of ${backgrounds.length})`;

        if (backgrounds.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-images"></i>
                    <p>No backgrounds yet</p>
                    <p style="font-size: 0.9rem;">Upload some images or click "Import Existing" to get started</p>
                </div>
            `;
            return;
        }

        container.innerHTML = `
            <div class="backgrounds-grid">
                ${backgrounds.map((bg, index) => `
                    <div class="background-item ${bg.is_visible ? '' : 'hidden'}" data-id="${bg.id}">
                        <img src="/${bg.path}" alt="${bg.original_name || bg.filename}" loading="lazy">
                        <span class="order-badge">#${index + 1}</span>
                        ${!bg.is_visible ? '<span class="hidden-badge">Hidden</span>' : ''}
                        <div class="overlay-actions">
                            <button class="btn ${bg.is_visible ? 'btn-success' : 'btn-secondary'} btn-sm" 
                                    onclick="toggleVisibility(${bg.id})" title="${bg.is_visible ? 'Hide' : 'Show'}">
                                <i class="fa-solid ${bg.is_visible ? 'fa-eye' : 'fa-eye-slash'}"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteBackground(${bg.id})" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function setupDragDrop() {
        const uploadArea = document.getElementById('uploadArea');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            uploadFiles(e.dataTransfer.files);
        });
    }

    async function uploadFiles(files) {
        for (const file of files) {
            if (!file.type.startsWith('image/')) {
                showToast(`${file.name} is not an image`, 'error');
                continue;
            }

            const formData = new FormData();
            formData.append('image', file);

            try {
                showToast(`Uploading ${file.name}...`, 'success');
                
                const response = await fetch('/api/backgrounds/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast(`${file.name} uploaded`, 'success');
                    loadBackgrounds();
                } else {
                    showToast(data.message || `Error uploading ${file.name}`, 'error');
                }
            } catch (error) {
                console.error('Upload error:', error);
                showToast(`Error uploading ${file.name}`, 'error');
            }
        }

        // Clear the file input
        document.getElementById('fileInput').value = '';
    }

    async function toggleVisibility(id) {
        try {
            const response = await fetch(`/api/backgrounds/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message, 'success');
                loadBackgrounds();
            } else {
                showToast(data.message || 'Error toggling visibility', 'error');
            }
        } catch (error) {
            console.error('Error toggling visibility:', error);
            showToast('Error toggling visibility', 'error');
        }
    }

    async function deleteBackground(id) {
        if (!confirm('Are you sure you want to delete this background?')) {
            return;
        }

        try {
            const response = await fetch(`/api/backgrounds/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast('Background deleted', 'success');
                loadBackgrounds();
            } else {
                showToast(data.message || 'Error deleting background', 'error');
            }
        } catch (error) {
            console.error('Error deleting background:', error);
            showToast('Error deleting background', 'error');
        }
    }

</script>
@endsection
