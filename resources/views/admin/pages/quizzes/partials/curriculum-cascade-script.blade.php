@php
    $cascadeRequireStage = (bool) ($cascadeRequireStage ?? false);
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stageSelect = document.getElementById('stageSelect');
    const classSelect = document.getElementById('classSelect');
    const subjectSelect = document.getElementById('subjectSelect');
    const unitSelect = document.getElementById('unitSelect');

    if (!stageSelect || !classSelect || !subjectSelect || !unitSelect) {
        return;
    }

    const requireStage = @json($cascadeRequireStage);

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

    const labels = {
        classAll: requireStage ? 'اختر الصف' : 'كل الصفوف',
        classNeedStage: 'اختر المرحلة أولاً',
        subjectAll: requireStage ? 'اختر المادة' : 'كل المواد',
        subjectNeedClass: 'اختر الصف أولاً',
        unitAll: requireStage ? 'اختر الوحدة (اختياري)' : 'كل الوحدات',
        unitNeedSubject: 'اختر المادة أولاً',
        loading: 'جاري التحميل...',
        classError: 'تعذر تحميل الصفوف',
        subjectError: 'تعذر تحميل المواد',
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

    function resetSelect(select, placeholder, disabled) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        if (typeof disabled === 'boolean') {
            select.disabled = disabled;
        }
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
        if (requireStage && !stageId) {
            resetSelect(classSelect, labels.classNeedStage, true);
            return Promise.resolve();
        }

        resetSelect(classSelect, labels.loading, false);
        const params = new URLSearchParams();
        if (stageId) {
            params.set('stage_id', stageId);
        }

        return fetchJson(routes.classes + (params.toString() ? '?' + params.toString() : ''))
            .then(function (payload) {
                resetSelect(classSelect, labels.classAll, false);
                populateOptions(classSelect, payload.data || [], 'name', selectedClassId);
            })
            .catch(function () {
                resetSelect(classSelect, labels.classError, false);
            });
    }

    function loadSubjects(classId, stageId, selectedSubjectId) {
        if (requireStage && !classId && !stageId) {
            resetSelect(subjectSelect, labels.subjectNeedClass, true);
            return Promise.resolve();
        }

        if (requireStage && !classId && stageId) {
            // في الوضع الصارم: المواد تظهر بعد اختيار الصف فقط
            resetSelect(subjectSelect, labels.subjectNeedClass, true);
            return Promise.resolve();
        }

        resetSelect(subjectSelect, labels.loading, false);
        const params = new URLSearchParams();
        if (classId) {
            params.set('class_id', classId);
        }
        if (stageId) {
            params.set('stage_id', stageId);
        }

        return fetchJson(routes.subjects + (params.toString() ? '?' + params.toString() : ''))
            .then(function (payload) {
                resetSelect(subjectSelect, labels.subjectAll, false);
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
                resetSelect(subjectSelect, labels.subjectError, false);
            });
    }

    function loadUnits(subjectId, selectedUnitId) {
        if (!subjectId) {
            resetSelect(unitSelect, requireStage ? labels.unitNeedSubject : labels.unitAll, !!requireStage);
            return Promise.resolve();
        }

        resetSelect(unitSelect, labels.loading, false);

        return fetchJson(routes.units + '?subject_id=' + encodeURIComponent(subjectId))
            .then(function (units) {
                resetSelect(unitSelect, labels.unitAll, false);
                const list = Array.isArray(units) ? units : (units.data || []);
                list.forEach(function (unit) {
                    const opt = document.createElement('option');
                    opt.value = unit.id;
                    opt.textContent = unit.label || unit.title || ('#' + unit.id);
                    if (selectedUnitId && String(unit.id) === String(selectedUnitId)) {
                        opt.selected = true;
                    }
                    unitSelect.appendChild(opt);
                });
            })
            .catch(function () {
                resetSelect(unitSelect, labels.unitAll, false);
            });
    }

    stageSelect.addEventListener('change', function () {
        if (isInitializing) {
            return;
        }
        const stageId = stageSelect.value;
        resetSelect(subjectSelect, requireStage ? labels.subjectNeedClass : labels.subjectAll, !!requireStage);
        resetSelect(unitSelect, requireStage ? labels.unitNeedSubject : labels.unitAll, !!requireStage);
        loadClasses(stageId, '').then(function () {
            if (requireStage) {
                return Promise.resolve();
            }
            return loadSubjects('', stageId, '');
        });
    });

    classSelect.addEventListener('change', function () {
        if (isInitializing) {
            return;
        }
        const classId = classSelect.value;
        resetSelect(unitSelect, requireStage ? labels.unitNeedSubject : labels.unitAll, !!requireStage);
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
