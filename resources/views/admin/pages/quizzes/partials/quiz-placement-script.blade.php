<script>
document.addEventListener('DOMContentLoaded', function () {
    const idPrefix = @json($idPrefix ?? 'quizPlacement');
    const stageSelect = document.getElementById(idPrefix + 'StageSelect');
    const classSelect = document.getElementById(idPrefix + 'ClassSelect');
    const subjectSelect = document.getElementById(idPrefix + 'SubjectSelect');
    const sectionSelect = document.getElementById(idPrefix + 'SectionSelect');
    const unitSelect = document.getElementById(idPrefix + 'UnitSelect');
    const lessonSelect = document.getElementById(idPrefix + 'LessonSelect');
    const lessonField = document.getElementById(idPrefix + 'LessonField');
    const scopeRadios = document.querySelectorAll('.quiz-placement-scope');

    if (!stageSelect || !classSelect || !subjectSelect || !sectionSelect || !unitSelect) {
        return;
    }

    const routes = {
        classes: @json(route('admin.quizzes.get-classes-by-stage')),
        subjects: @json(route('admin.quizzes.get-subjects-by-class')),
        sections: @json(route('admin.quizzes.get-sections')),
        units: @json(route('admin.quizzes.get-units')),
        lessons: @json(route('admin.quizzes.get-lessons-by-unit')),
    };

    const prefill = {
        stageId: @json(old('stage_id', $selectedStageId ?? '')),
        classId: @json(old('class_id', $selectedClassId ?? '')),
        subjectId: @json(old('subject_id', $selectedSubjectId ?? '')),
        sectionId: @json(old('section_id', $selectedSectionId ?? '')),
        unitId: @json(old('unit_id', $selectedUnitId ?? '')),
        lessonId: @json(old('lesson_id', $selectedLessonId ?? '')),
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
            opt.textContent = item[labelKey] || item.title || ('#' + item.id);
            if (selectedId && String(item.id) === String(selectedId)) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    }

    function selectedScope() {
        const checked = document.querySelector('.quiz-placement-scope:checked');
        return checked ? checked.value : 'unit';
    }

    function toggleLessonField() {
        const isLesson = selectedScope() === 'lesson';
        if (lessonField) {
            lessonField.classList.toggle('d-none', !isLesson);
        }
        if (lessonSelect) {
            lessonSelect.required = isLesson;
            if (!isLesson) {
                lessonSelect.value = '';
            }
        }
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
                resetSelect(subjectSelect, '-- اختر المادة --');
                (payload.data || []).forEach(function (subject) {
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

    function loadSections(subjectId, selectedSectionId) {
        resetSelect(sectionSelect, '-- اختر القسم --');
        if (!subjectId) {
            return Promise.resolve();
        }

        resetSelect(sectionSelect, 'جاري التحميل...');

        return fetchJson(routes.sections + '?subject_id=' + encodeURIComponent(subjectId))
            .then(function (sections) {
                resetSelect(sectionSelect, '-- اختر القسم --');
                populateOptions(sectionSelect, Array.isArray(sections) ? sections : [], 'title', selectedSectionId);
            })
            .catch(function () {
                resetSelect(sectionSelect, 'تعذر تحميل الأقسام');
            });
    }

    function loadUnits(subjectId, sectionId, selectedUnitId) {
        resetSelect(unitSelect, '-- اختر الوحدة --');
        if (!subjectId) {
            return Promise.resolve();
        }

        resetSelect(unitSelect, 'جاري التحميل...');
        let url = routes.units + '?subject_id=' + encodeURIComponent(subjectId);
        if (sectionId) {
            url += '&section_id=' + encodeURIComponent(sectionId);
        }

        return fetchJson(url)
            .then(function (units) {
                resetSelect(unitSelect, '-- اختر الوحدة --');
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
                resetSelect(unitSelect, 'تعذر تحميل الوحدات');
            });
    }

    function loadLessons(unitId, selectedLessonId) {
        if (!lessonSelect) {
            return Promise.resolve();
        }

        resetSelect(lessonSelect, '-- اختر الدرس --');
        if (!unitId || selectedScope() !== 'lesson') {
            return Promise.resolve();
        }

        resetSelect(lessonSelect, 'جاري التحميل...');

        return fetchJson(routes.lessons + '?unit_id=' + encodeURIComponent(unitId))
            .then(function (lessons) {
                resetSelect(lessonSelect, '-- اختر الدرس --');
                populateOptions(lessonSelect, Array.isArray(lessons) ? lessons : [], 'title', selectedLessonId);
            })
            .catch(function () {
                resetSelect(lessonSelect, 'تعذر تحميل الدروس');
            });
    }

    stageSelect.addEventListener('change', function () {
        if (isInitializing) return;
        loadClasses(stageSelect.value, '').then(function () {
            resetSelect(subjectSelect, '-- اختر المادة --');
            resetSelect(sectionSelect, '-- اختر القسم --');
            resetSelect(unitSelect, '-- اختر الوحدة --');
            if (lessonSelect) resetSelect(lessonSelect, '-- اختر الدرس --');
        });
    });

    classSelect.addEventListener('change', function () {
        if (isInitializing) return;
        loadSubjects(classSelect.value, stageSelect.value, '').then(function () {
            resetSelect(sectionSelect, '-- اختر القسم --');
            resetSelect(unitSelect, '-- اختر الوحدة --');
            if (lessonSelect) resetSelect(lessonSelect, '-- اختر الدرس --');
        });
    });

    subjectSelect.addEventListener('change', function () {
        if (isInitializing) return;
        loadSections(subjectSelect.value, '').then(function () {
            return loadUnits(subjectSelect.value, '', '');
        }).then(function () {
            if (lessonSelect) resetSelect(lessonSelect, '-- اختر الدرس --');
        });
    });

    sectionSelect.addEventListener('change', function () {
        if (isInitializing) return;
        loadUnits(subjectSelect.value, sectionSelect.value, '').then(function () {
            if (lessonSelect) resetSelect(lessonSelect, '-- اختر الدرس --');
        });
    });

    unitSelect.addEventListener('change', function () {
        if (isInitializing) return;
        loadLessons(unitSelect.value, '');
    });

    scopeRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            toggleLessonField();
            if (selectedScope() === 'lesson' && unitSelect.value) {
                loadLessons(unitSelect.value, '');
            }
        });
    });

    toggleLessonField();

    isInitializing = true;
    loadClasses(prefill.stageId, prefill.classId)
        .then(function () {
            return loadSubjects(prefill.classId, prefill.stageId, prefill.subjectId);
        })
        .then(function () {
            return loadSections(prefill.subjectId, prefill.sectionId);
        })
        .then(function () {
            return loadUnits(prefill.subjectId, prefill.sectionId, prefill.unitId);
        })
        .then(function () {
            return loadLessons(prefill.unitId, prefill.lessonId);
        })
        .finally(function () {
            isInitializing = false;
        });
});
</script>
