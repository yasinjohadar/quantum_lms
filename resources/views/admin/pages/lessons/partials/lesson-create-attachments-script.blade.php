@once
<script>
(function() {
    function setLessonCreateAttachmentMode(fieldId, mode) {
        var filesInput = document.getElementById('lessonCreateFilesInput' + fieldId);
        var urlInput = document.getElementById('lessonCreateUrl' + fieldId);
        var titleFiles = document.getElementById('lessonCreateTitleFiles' + fieldId);
        var titleLink = document.getElementById('lessonCreateTitleLink' + fieldId);

        if (!filesInput || !urlInput) return;

        if (mode === 'link') {
            filesInput.disabled = true;
            filesInput.value = '';
            if (titleFiles) {
                titleFiles.disabled = true;
                titleFiles.value = '';
            }
            urlInput.disabled = false;
            if (titleLink) titleLink.disabled = false;
        } else {
            filesInput.disabled = false;
            urlInput.disabled = true;
            urlInput.value = '';
            if (titleFiles) titleFiles.disabled = false;
            if (titleLink) {
                titleLink.disabled = true;
                titleLink.value = '';
            }
        }

        var preview = document.getElementById('lessonCreateFilesPreview' + fieldId);
        if (preview && mode === 'link') {
            preview.style.display = 'none';
            preview.innerHTML = '';
        }
    }

    function bindLessonCreateAttachmentTabs(scope) {
        var root = scope || document;
        root.querySelectorAll('.lesson-create-attachment-tab').forEach(function(tab) {
            if (tab.dataset.lessonCreateTabBound === '1') return;
            tab.dataset.lessonCreateTabBound = '1';

            var fieldId = tab.getAttribute('data-field-id');
            var mode = tab.getAttribute('data-attachment-mode') || 'files';

            tab.addEventListener('shown.bs.tab', function() {
                setLessonCreateAttachmentMode(fieldId, mode);
            });

            if (tab.classList.contains('active')) {
                setLessonCreateAttachmentMode(fieldId, mode);
            }
        });
    }

    function bindLessonCreateAttachmentsPreview(scope) {
        var root = scope || document;
        root.querySelectorAll('.lesson-create-attachments-input').forEach(function(input) {
            if (input.dataset.previewBound === '1') return;
            input.dataset.previewBound = '1';

            var fieldId = input.getAttribute('data-field-id');
            var preview = document.getElementById('lessonCreateFilesPreview' + fieldId);
            if (!preview) return;

            input.addEventListener('change', function() {
                if (input.disabled || !this.files || this.files.length === 0) {
                    preview.style.display = 'none';
                    preview.innerHTML = '';
                    return;
                }

                var names = Array.from(this.files).map(function(file) {
                    return file.name;
                });

                preview.style.display = 'block';
                preview.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i> '
                    + names.length + ' ملف/ملفات: '
                    + names.slice(0, 5).join('، ')
                    + (names.length > 5 ? '...' : '');
            });
        });
    }

    function initLessonCreateAttachments(scope) {
        bindLessonCreateAttachmentTabs(scope);
        bindLessonCreateAttachmentsPreview(scope);
    }

    document.addEventListener('DOMContentLoaded', function() {
        initLessonCreateAttachments(document);
    });

    window.initLessonCreateAttachments = initLessonCreateAttachments;
})();
</script>
@endonce
