<script>
document.addEventListener('DOMContentLoaded', function () {
    const stageSelect = document.getElementById('stageSelect');
    const classSelect = document.getElementById('classSelect');
    const subjectSelect = document.getElementById('subjectSelect');
    const unitSelect = document.getElementById('unitSelect');

    if (!stageSelect || !classSelect || !subjectSelect || !unitSelect) {
        return;
    }

    const routes = {
        classes: @json(route('admin.quizzes.get-classes-by-stage')),
        subjects: @json(route('admin.quizzes.get-subjects-by-class')),
        units: @json(route('admin.quizzes.get-units')),
    };

    const prefill = {
        stageId: @json(old('stage_id', $selectedStageId ?? '')),
        classId: @json(old('class_id', $selectedClassId ?? '')),
        subjectId: @json(old('subject_id', $selectedSubjectId ?? '')),
        unitId: @json(old('unit_id', $selectedUnitId ?? '')),
    };

    let isInitializing = true;

    function fetchJson(url) {
        return fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Network error');
            }
            return response.json();
        });
    }

    function resetSelect(select, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
    }

    function populateOptions(select, items, labelKey, selectedId) {
        items.forEach(function (item) {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item[labelKey];
            if (selectedId && String(item.id) === String(selectedId)) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    }

    function loadClasses(stageId, selectedClassId) {
        resetSelect(classSelect, 'جاري التحميل...');
        const params = new URLSearchParams();
        if (stageId) {
            params.set('stage_id', stageId);
        }

        return fetchJson(routes.classes + (params.toString() ? '?' + params.toString() : ''))
            .then(function (payload) {
                resetSelect(classSelect, 'كل الصفوف');
                populateOptions(classSelect, payload.data || [], 'name', selectedClassId);
            })
            .catch(function () {
                resetSelect(classSelect, 'تعذر تحميل الصفوف');
            });
    }

    function loadSubjects(classId, stageId, selectedSubjectId) {
        resetSelect(subjectSelect, 'جاري التحميل...');
        const params = new URLSearchParams();
        if (classId) {
            params.set('class_id', classId);
        }
        if (stageId) {
            params.set('stage_id', stageId);
        }

        return fetchJson(routes.subjects + (params.toString() ? '?' + params.toString() : ''))
            .then(function (payload) {
                resetSelect(subjectSelect, 'كل المواد');
                const items = payload.data || [];
                items.forEach(function (subject) {
                    const opt = document.createElement('option');
                    opt.value = subject.id;
                    let label = subject.name;
                    if (subject.school_class && subject.school_class.name) {
                        label += ' (' + subject.school_class.name + ')';
                    }
                    opt.textContent = label;
                    if (selectedSubjectId && String(subject.id) === String(selectedSubjectId)) {
                        opt.selected = true;
                    }
                    subjectSelect.appendChild(opt);
                });
            })
            .catch(function () {
                resetSelect(subjectSelect, 'تعذر تحميل المواد');
            });
    }

    function loadUnits(subjectId, selectedUnitId) {
        resetSelect(unitSelect, 'كل الوحدات');
        if (!subjectId) {
            return Promise.resolve();
        }

        resetSelect(unitSelect, 'جاري التحميل...');

        return fetchJson(routes.units + '?subject_id=' + encodeURIComponent(subjectId))
            .then(function (units) {
                resetSelect(unitSelect, 'كل الوحدات');
                const list = Array.isArray(units) ? units : (units.data || []);
                populateOptions(unitSelect, list, 'title', selectedUnitId);
            })
            .catch(function () {
                resetSelect(unitSelect, 'كل الوحدات');
            });
    }

    stageSelect.addEventListener('change', function () {
        if (isInitializing) {
            return;
        }
        const stageId = stageSelect.value;
        resetSelect(classSelect, 'جاري التحميل...');
        resetSelect(subjectSelect, 'كل المواد');
        resetSelect(unitSelect, 'كل الوحدات');
        loadClasses(stageId, '').then(function () {
            return loadSubjects('', stageId, '');
        });
    });

    classSelect.addEventListener('change', function () {
        if (isInitializing) {
            return;
        }
        const classId = classSelect.value;
        resetSelect(subjectSelect, 'جاري التحميل...');
        resetSelect(unitSelect, 'كل الوحدات');
        loadSubjects(classId, stageSelect.value, '').then(function () {
            return loadUnits('', '');
        });
    });

    subjectSelect.addEventListener('change', function () {
        if (isInitializing) {
            return;
        }
        loadUnits(subjectSelect.value, '');
    });

    isInitializing = true;
    loadClasses(prefill.stageId, prefill.classId)
        .then(function () {
            return loadSubjects(prefill.classId, prefill.stageId, prefill.subjectId);
        })
        .then(function () {
            return loadUnits(prefill.subjectId, prefill.unitId);
        })
        .finally(function () {
            isInitializing = false;
        });
});
</script>
