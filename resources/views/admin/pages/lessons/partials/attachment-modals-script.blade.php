@once
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.attachment-files-input').forEach(function(input) {
        var lessonId = input.getAttribute('data-lesson');
        var preview = document.getElementById('filesPreview' + lessonId);
        if (!preview) {
            return;
        }

        input.addEventListener('change', function() {
            if (!this.files || this.files.length === 0) {
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
});
</script>
@endonce
