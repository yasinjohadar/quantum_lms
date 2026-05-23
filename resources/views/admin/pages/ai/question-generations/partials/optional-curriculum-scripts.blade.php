<script>
(function () {
    window.initOptionalCurriculumCascade = function (config) {
        var classSelect = document.getElementById(config.classSelectId);
        var subjectSelect = document.getElementById(config.subjectSelectId);
        var unitSelect = document.getElementById(config.unitSelectId);
        if (!unitSelect) {
            return;
        }

        var ajaxSubjectsBase = config.ajaxSubjectsBase || @json(url('/admin/ai/question-generations/ajax/classes'));
        var ajaxUnitsBase = config.ajaxUnitsBase || @json(url('/admin/ai/question-generations/ajax/subjects'));
        var prefillSubjectId = config.prefillSubjectId || '';
        var prefillUnitId = config.prefillUnitId || '';
        var lockedSubjectId = config.lockedSubjectId || null;

        function resetUnitsPlaceholder() {
            unitSelect.disabled = true;
            unitSelect.innerHTML = '<option value="">اختر المادة أولاً</option>';
        }

        function resetSubjectsPlaceholder() {
            if (!subjectSelect) {
                resetUnitsPlaceholder();
                return;
            }
            subjectSelect.disabled = true;
            subjectSelect.innerHTML = '<option value="">اختر الصف أولاً</option>';
            resetUnitsPlaceholder();
        }

        function populateSubjects(classId, selectedSubjectId) {
            if (!subjectSelect) {
                return Promise.resolve();
            }
            subjectSelect.disabled = false;
            subjectSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            resetUnitsPlaceholder();
            return fetch(ajaxSubjectsBase + '/' + classId + '/subjects', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Network error');
                    }
                    return response.json();
                })
                .then(function (data) {
                    subjectSelect.innerHTML = '<option value="">اختر المادة</option>';
                    data.forEach(function (subject) {
                        var opt = document.createElement('option');
                        opt.value = subject.id;
                        opt.textContent = subject.name;
                        if (selectedSubjectId && String(subject.id) === String(selectedSubjectId)) {
                            opt.selected = true;
                        }
                        subjectSelect.appendChild(opt);
                    });
                    if (selectedSubjectId) {
                        return populateUnits(selectedSubjectId, prefillUnitId);
                    }
                })
                .catch(function () {
                    subjectSelect.innerHTML = '<option value="">تعذر تحميل المواد</option>';
                    resetUnitsPlaceholder();
                });
        }

        function populateUnits(subjectId, selectedUnitId) {
            unitSelect.disabled = false;
            unitSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            return fetch(ajaxUnitsBase + '/' + subjectId + '/units', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Network error');
                    }
                    return response.json();
                })
                .then(function (data) {
                    unitSelect.innerHTML = '<option value="">اختر الوحدة</option>';
                    data.forEach(function (unit) {
                        var opt = document.createElement('option');
                        opt.value = unit.id;
                        opt.textContent = unit.title;
                        if (selectedUnitId && String(unit.id) === String(selectedUnitId)) {
                            opt.selected = true;
                        }
                        unitSelect.appendChild(opt);
                    });
                })
                .catch(function () {
                    unitSelect.innerHTML = '<option value="">تعذر تحميل الوحدات</option>';
                });
        }

        if (classSelect) {
            classSelect.addEventListener('change', function () {
                var classId = this.value;
                if (!classId) {
                    resetSubjectsPlaceholder();
                    return;
                }
                populateSubjects(classId, null);
            });
        }

        if (subjectSelect) {
            subjectSelect.addEventListener('change', function () {
                var subjectId = this.value;
                if (!subjectId) {
                    resetUnitsPlaceholder();
                    return;
                }
                populateUnits(subjectId, null);
            });
        }

        if (lockedSubjectId) {
            populateUnits(lockedSubjectId, prefillUnitId || null);
        } else if (classSelect && classSelect.value) {
            populateSubjects(classSelect.value, prefillSubjectId || null);
        } else if (subjectSelect && subjectSelect.value) {
            populateUnits(subjectSelect.value, prefillUnitId || null);
        }
    };
})();
</script>
