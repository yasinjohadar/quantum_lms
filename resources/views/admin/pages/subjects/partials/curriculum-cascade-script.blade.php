<script>
(function () {
    var pickerRegistry = {};

    function routes() {
        return window.curriculumCascadeRoutes || {};
    }

    function fetchJson(url) {
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (csrf) {
            headers['X-CSRF-TOKEN'] = csrf.content;
        }
        return fetch(url, {
            headers: headers,
            credentials: 'same-origin',
        }).then(function (response) {
            if (!response.ok) {
                return response.text().then(function (body) {
                    throw new Error('HTTP ' + response.status + ': ' + body.slice(0, 120));
                });
            }
            return response.json();
        });
    }

    function resetSelect(select, placeholder, disabled) {
        if (!select) {
            return;
        }
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        select.disabled = !!disabled;
    }

    function resolveExcludeSubjectIds(config) {
        if (!config || !config.excludeSubjectIds) {
            return [];
        }
        if (typeof config.excludeSubjectIds === 'function') {
            return config.excludeSubjectIds() || [];
        }
        return Array.isArray(config.excludeSubjectIds) ? config.excludeSubjectIds : [];
    }

    function resolveExcludeUnitId(config) {
        if (!config || !config.excludeUnitId) {
            return '';
        }
        if (typeof config.excludeUnitId === 'function') {
            return config.excludeUnitId() || '';
        }
        return config.excludeUnitId || '';
    }

    function subjectsFromStructure(classId, excluded) {
        if (!Array.isArray(window.linkableStructure)) {
            return [];
        }
        return window.linkableStructure.filter(function (subject) {
            if (String(subject.class_id) !== String(classId)) {
                return false;
            }
            return !excluded.some(function (id) { return String(id) === String(subject.id); });
        });
    }

    function loadSubjects(config, classId) {
        var subjectSelect = config.subjectSelect;
        var sectionSelect = config.sectionSelect;
        var unitSelect = config.unitSelect;

        resetSelect(subjectSelect, '-- اختر المادة --', true);
        if (sectionSelect) {
            resetSelect(sectionSelect, '-- اختر القسم --', true);
        }
        if (unitSelect) {
            resetSelect(unitSelect, '-- اختر الوحدة --', true);
        }
        delete subjectSelect.dataset.sectionsCache;

        if (!classId) {
            return Promise.resolve();
        }

        resetSelect(subjectSelect, 'جاري التحميل...', true);
        var excluded = resolveExcludeSubjectIds(config);
        var url = (routes().subjects || '') + '?class_id=' + encodeURIComponent(classId);

        function populateSubjects(items) {
            resetSelect(subjectSelect, '-- اختر المادة --', false);
            items.forEach(function (subject) {
                var opt = document.createElement('option');
                opt.value = subject.id;
                var label = subject.name || '';
                if (subject.school_class && subject.school_class.name) {
                    label += ' (' + subject.school_class.name + ')';
                } else if (subject.class_name) {
                    label += ' (' + subject.class_name + ')';
                }
                opt.textContent = label;
                subjectSelect.appendChild(opt);
            });
            if (subjectSelect.options.length <= 1) {
                resetSelect(subjectSelect, 'لا توجد مواد لهذا الصف', true);
            }
        }

        if (!url || url.indexOf('class_id=') === 0) {
            populateSubjects(subjectsFromStructure(classId, excluded));
            return Promise.resolve();
        }

        return fetchJson(url)
            .then(function (payload) {
                var items = (payload && payload.data) ? payload.data : [];
                if (!items.length) {
                    items = subjectsFromStructure(classId, excluded);
                }
                populateSubjects(items);
            })
            .catch(function (error) {
                console.warn('[curriculum-cascade] subjects load failed:', error);
                var fallback = subjectsFromStructure(classId, excluded);
                if (fallback.length) {
                    populateSubjects(fallback);
                } else {
                    resetSelect(subjectSelect, 'تعذر تحميل المواد', true);
                }
            });
    }

    function loadSections(config, subjectId) {
        var subjectSelect = config.subjectSelect;
        var sectionSelect = config.sectionSelect;
        var unitSelect = config.unitSelect;

        if (!sectionSelect) {
            return Promise.resolve([]);
        }

        resetSelect(sectionSelect, 'جاري التحميل...', true);
        if (unitSelect) {
            resetSelect(unitSelect, '-- اختر الوحدة --', true);
        }

        if (!subjectId) {
            resetSelect(sectionSelect, '-- اختر القسم --', true);
            delete subjectSelect.dataset.sectionsCache;
            return Promise.resolve([]);
        }

        function applySections(list) {
            resetSelect(sectionSelect, '-- اختر القسم --', false);
            list.forEach(function (sec) {
                var opt = document.createElement('option');
                opt.value = sec.id;
                opt.textContent = sec.path_title || sec.title || ('#' + sec.id);
                sectionSelect.appendChild(opt);
            });
            subjectSelect.dataset.sectionsCache = JSON.stringify(list);
            if (sectionSelect.options.length <= 1) {
                resetSelect(sectionSelect, 'لا توجد أقسام', true);
            }
            return list;
        }

        var structureSubject = Array.isArray(window.linkableStructure)
            ? window.linkableStructure.find(function (x) { return String(x.id) === String(subjectId); })
            : null;
        if (structureSubject && Array.isArray(structureSubject.sections) && structureSubject.sections.length) {
            return Promise.resolve(applySections(structureSubject.sections));
        }

        var url = (routes().sections || '') + '?subject_id=' + encodeURIComponent(subjectId);
        return fetchJson(url)
            .then(function (sections) {
                return applySections(Array.isArray(sections) ? sections : []);
            })
            .catch(function (error) {
                console.warn('[curriculum-cascade] sections load failed:', error);
                if (structureSubject && structureSubject.sections) {
                    return applySections(structureSubject.sections);
                }
                resetSelect(sectionSelect, 'تعذر تحميل الأقسام', true);
                return [];
            });
    }

    function loadUnits(config, subjectId, sectionId) {
        var subjectSelect = config.subjectSelect;
        var unitSelect = config.unitSelect;

        if (!unitSelect) {
            return Promise.resolve();
        }

        resetSelect(unitSelect, 'جاري التحميل...', true);

        if (!subjectId) {
            resetSelect(unitSelect, '-- اختر الوحدة --', true);
            return Promise.resolve();
        }

        var excludedUnitId = resolveExcludeUnitId(config);

        function applyUnits(list) {
            resetSelect(unitSelect, '-- اختر الوحدة --', false);
            list.forEach(function (unit) {
                if (excludedUnitId && String(unit.id) === String(excludedUnitId)) {
                    return;
                }
                var opt = document.createElement('option');
                opt.value = unit.id;
                opt.textContent = unit.label || unit.title || ('#' + unit.id);
                opt.dataset.unitTitle = unit.title || '';
                opt.dataset.sectionTitle = unit.section_title || '';
                unitSelect.appendChild(opt);
            });
            if (unitSelect.options.length <= 1) {
                resetSelect(unitSelect, 'لا توجد وحدات', true);
            }
        }

        var structureSubject = Array.isArray(window.linkableStructure)
            ? window.linkableStructure.find(function (x) { return String(x.id) === String(subjectId); })
            : null;
        var structureSection = structureSubject && structureSubject.sections && sectionId
            ? structureSubject.sections.find(function (x) { return String(x.id) === String(sectionId); })
            : null;
        if (structureSection && Array.isArray(structureSection.units) && structureSection.units.length) {
            applyUnits(structureSection.units);
            return Promise.resolve();
        }

        var url = (routes().units || '') + '?subject_id=' + encodeURIComponent(subjectId);
        if (sectionId) {
            url += '&section_id=' + encodeURIComponent(sectionId);
        }

        return fetchJson(url)
            .then(function (units) {
                applyUnits(Array.isArray(units) ? units : (units.data || []));
            })
            .catch(function (error) {
                console.warn('[curriculum-cascade] units load failed:', error);
                if (structureSection && structureSection.units) {
                    applyUnits(structureSection.units);
                } else {
                    resetSelect(unitSelect, 'تعذر تحميل الوحدات', true);
                }
            });
    }

    function registerPicker(key, config) {
        if (!config || !config.classSelect || !config.subjectSelect) {
            return;
        }
        pickerRegistry[key] = config;
        config.classSelect.dataset.curriculumPickerKey = key;
        config.subjectSelect.dataset.curriculumPickerKey = key;
        if (config.sectionSelect) {
            config.sectionSelect.dataset.curriculumPickerKey = key;
        }
        if (config.unitSelect) {
            config.unitSelect.dataset.curriculumPickerKey = key;
        }
    }

    function getPickerFromElement(el) {
        if (!el || !el.dataset.curriculumPickerKey) {
            return null;
        }
        return pickerRegistry[el.dataset.curriculumPickerKey] || null;
    }

    document.addEventListener('change', function (event) {
        var target = event.target;
        var config = getPickerFromElement(target);
        if (!config) {
            return;
        }

        if (target === config.classSelect) {
            loadSubjects(config, target.value).then(function () {
                if (typeof config.onClassChange === 'function') {
                    config.onClassChange(target.value);
                }
            });
            return;
        }

        if (target === config.subjectSelect) {
            loadSections(config, target.value).then(function (sections) {
                if (!config.sectionSelect && config.unitSelect) {
                    loadUnits(config, target.value, '');
                }
                if (typeof config.onSubjectChange === 'function') {
                    config.onSubjectChange(target.value, sections);
                }
            });
            return;
        }

        if (config.sectionSelect && target === config.sectionSelect) {
            loadUnits(config, config.subjectSelect.value, target.value).then(function () {
                if (typeof config.onSectionChange === 'function') {
                    config.onSectionChange(target.value);
                }
            });
            return;
        }

        if (config.unitSelect && target === config.unitSelect) {
            if (typeof config.onUnitChange === 'function') {
                config.onUnitChange(target.value);
            }
        }
    });

    window.initCurriculumCascadePicker = function (config) {
        registerPicker('picker-' + Math.random().toString(36).slice(2), config);
    };

    window.getCurriculumCascadeSelection = function (classSelect, subjectSelect, sectionSelect, unitSelect) {
        return {
            class_label: classSelect && classSelect.selectedOptions.length ? classSelect.selectedOptions[0].textContent.trim() : '',
            subject_id: subjectSelect ? subjectSelect.value : '',
            subject_label: subjectSelect && subjectSelect.selectedOptions.length ? subjectSelect.selectedOptions[0].textContent.trim() : '',
            section_id: sectionSelect ? sectionSelect.value : '',
            section_label: sectionSelect && sectionSelect.selectedOptions.length ? sectionSelect.selectedOptions[0].textContent.trim() : '',
            unit_id: unitSelect ? unitSelect.value : '',
            unit_label: unitSelect && unitSelect.selectedOptions.length ? unitSelect.selectedOptions[0].textContent.trim() : '',
            unit_option: unitSelect && unitSelect.selectedOptions.length ? unitSelect.selectedOptions[0] : null,
        };
    };

    window.bootCurriculumLinkModals = function () {
        var lessonClass = document.getElementById('lessonLinkClassSelect');
        var lessonSubject = document.getElementById('lessonLinkSubjectSelect');
        var lessonSection = document.getElementById('lessonLinkSectionSelect');
        var lessonUnit = document.getElementById('lessonLinkUnitSelect');
        if (lessonClass && lessonSubject) {
            registerPicker('lesson-link', {
                classSelect: lessonClass,
                subjectSelect: lessonSubject,
                sectionSelect: lessonSection,
                unitSelect: lessonUnit,
                excludeUnitId: function () {
                    var form = document.getElementById('linkLessonUnitsForm');
                    return form ? (form.getAttribute('data-primary-unit-id') || '') : '';
                },
                onUnitChange: function (unitId) {
                    var addBtn = document.getElementById('addLessonLinkedUnitBtn');
                    if (addBtn) {
                        addBtn.disabled = !unitId;
                    }
                },
            });
        }

        var quizClass = document.getElementById('quizLinkClassSelect');
        var quizSubject = document.getElementById('quizLinkSubjectSelect');
        var quizSection = document.getElementById('quizLinkSectionSelect');
        var quizUnit = document.getElementById('quizLinkUnitSelect');
        if (quizClass && quizSubject) {
            registerPicker('quiz-link', {
                classSelect: quizClass,
                subjectSelect: quizSubject,
                sectionSelect: quizSection,
                unitSelect: quizUnit,
                excludeUnitId: function () {
                    var form = document.getElementById('linkQuizUnitsForm');
                    return form ? (form.getAttribute('data-primary-unit-id') || '') : '';
                },
                onUnitChange: function (unitId) {
                    var addBtn = document.getElementById('addQuizLinkedUnitBtn');
                    if (addBtn) {
                        addBtn.disabled = !unitId;
                    }
                },
            });
        }

        var sectionClass = document.getElementById('sectionLinkClassSelect');
        var sectionSubject = document.getElementById('sectionLinkSubjectSelect');
        if (sectionClass && sectionSubject) {
            registerPicker('section-link', {
                classSelect: sectionClass,
                subjectSelect: sectionSubject,
                excludeSubjectIds: function () {
                    var form = document.getElementById('linkSectionSubjectsForm');
                    var primaryId = form ? form.getAttribute('data-primary-subject-id') : '';
                    return primaryId ? [primaryId] : [];
                },
                onSubjectChange: function (subjectId) {
                    var placementWrap = document.getElementById('sectionLinkPlacementWrap');
                    var addBtn = document.getElementById('addSectionLinkedSubjectBtn');
                    var placementRoot = document.getElementById('sectionLinkPlacementRoot');
                    var placementChild = document.getElementById('sectionLinkPlacementChild');
                    if (!subjectId) {
                        if (placementWrap) {
                            placementWrap.style.display = 'none';
                        }
                        if (addBtn) {
                            addBtn.disabled = true;
                        }
                        return;
                    }
                    if (placementWrap) {
                        placementWrap.style.display = '';
                    }
                    if (addBtn) {
                        addBtn.disabled = false;
                    }
                    if (placementRoot) {
                        placementRoot.checked = true;
                    }
                    if (placementChild) {
                        placementChild.checked = false;
                    }
                    if (typeof window.syncSectionLinkPlacementUI === 'function') {
                        window.syncSectionLinkPlacementUI();
                    }
                    if (typeof window.populateSectionLinkParentSelect === 'function') {
                        window.populateSectionLinkParentSelect(subjectId);
                    }
                },
            });
        }

        document.querySelectorAll('.unit-mirror-class-select').forEach(function (classSelect, index) {
            var row = classSelect.closest('[data-current-subject-id]');
            var subjectSelect = row ? row.querySelector('.unit-mirror-subject-select') : null;
            var sectionSelect = row ? row.querySelector('.unit-mirror-section-select') : null;
            if (!subjectSelect || !sectionSelect) {
                return;
            }
            registerPicker('unit-mirror-' + index, {
                classSelect: classSelect,
                subjectSelect: subjectSelect,
                sectionSelect: sectionSelect,
                excludeSubjectIds: function () {
                    var currentSubjectId = row.getAttribute('data-current-subject-id') || '';
                    return currentSubjectId ? [currentSubjectId] : [];
                },
            });
        });

        document.querySelectorAll('.link-class-select').forEach(function (classSelect, index) {
            var modal = classSelect.closest('.modal');
            if (!modal) {
                return;
            }
            var subjectSelect = modal.querySelector('.link-subject-select');
            var sectionSelect = modal.querySelector('.link-section-select');
            var unitSelect = modal.querySelector('.link-unit-select');
            if (!subjectSelect) {
                return;
            }
            var row = classSelect.closest('[data-current-subject-id]');
            registerPicker('legacy-link-' + index, {
                classSelect: classSelect,
                subjectSelect: subjectSelect,
                sectionSelect: sectionSelect,
                unitSelect: unitSelect,
                excludeSubjectIds: function () {
                    if (!row) {
                        return [];
                    }
                    var currentSubjectId = row.getAttribute('data-current-subject-id') || '';
                    var currentClassId = row.getAttribute('data-current-class-id') || '';
                    if (currentSubjectId && String(currentClassId) === String(classSelect.value)) {
                        return [];
                    }
                    return currentSubjectId ? [currentSubjectId] : [];
                },
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.bootCurriculumLinkModals);
    } else {
        window.bootCurriculumLinkModals();
    }
})();
</script>
