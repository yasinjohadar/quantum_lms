<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('questionBankFilters');
    var resultsEl = document.getElementById('questionBankResults');
    if (!form || !resultsEl) {
        return;
    }

    var fetchUrl = @json(route('admin.questions.index'));
    var ajaxSubjectsBase = @json(url('/admin/questions/ajax/classes'));
    var classSelect = document.getElementById('filter_class_id');
    var subjectSelect = document.getElementById('filter_subject_id');
    var searchInput = document.getElementById('filter_search');
    var prefillSubjectId = @json(request('subject_id'));
    var searchDebounceTimer = null;
    var isFetching = false;

    function buildFetchParams(page) {
        var params = new URLSearchParams(new FormData(form));
        params.set('page', String(page || 1));
        if (!classSelect || !classSelect.value) {
            params.delete('class_id');
        }
        if (!subjectSelect || !subjectSelect.value) {
            params.delete('subject_id');
        }
        return params.toString();
    }

    function syncUrl(page) {
        var qs = buildFetchParams(page);
        var newUrl = fetchUrl + (qs ? '?' + qs : '');
        window.history.replaceState({}, '', newUrl);
    }

    function resetSubjectsPlaceholder() {
        if (!subjectSelect) {
            return;
        }
        subjectSelect.disabled = true;
        subjectSelect.innerHTML = '<option value="">اختر الصف أولاً</option>';
    }

    function populateSubjects(classId, selectedSubjectId) {
        if (!subjectSelect) {
            return Promise.resolve();
        }
        subjectSelect.disabled = false;
        subjectSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        return fetch(ajaxSubjectsBase + '/' + classId + '/subjects', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Network error');
                }
                return response.json();
            })
            .then(function (data) {
                subjectSelect.innerHTML = '<option value="">الكل</option>';
                data.forEach(function (subject) {
                    var opt = document.createElement('option');
                    opt.value = subject.id;
                    opt.textContent = subject.name;
                    if (selectedSubjectId && String(subject.id) === String(selectedSubjectId)) {
                        opt.selected = true;
                    }
                    subjectSelect.appendChild(opt);
                });
            })
            .catch(function () {
                subjectSelect.innerHTML = '<option value="">تعذر تحميل المواد</option>';
            });
    }

    function refreshMath(root) {
        if (typeof window.renderQuestionMath === 'function') {
            window.renderQuestionMath(root || resultsEl);
        }
    }

    function fetchQuestions(page) {
        if (isFetching) {
            return;
        }
        isFetching = true;
        resultsEl.classList.add('opacity-50');

        var url = fetchUrl + '?' + buildFetchParams(page);
        fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.success || data.html === undefined) {
                    return;
                }
                resultsEl.innerHTML = data.html;
                syncUrl(page);
                refreshMath(resultsEl);
            })
            .catch(function (err) {
                console.error('questionBank fetch error:', err);
            })
            .finally(function () {
                isFetching = false;
                resultsEl.classList.remove('opacity-50');
            });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchQuestions(1);
    });

    if (classSelect) {
        classSelect.addEventListener('change', function () {
            var classId = this.value;
            if (!classId) {
                resetSubjectsPlaceholder();
                if (subjectSelect) {
                    subjectSelect.value = '';
                }
            } else {
                populateSubjects(classId, null);
                if (subjectSelect) {
                    subjectSelect.value = '';
                }
            }
            fetchQuestions(1);
        });
    }

    if (subjectSelect) {
        subjectSelect.addEventListener('change', function () {
            fetchQuestions(1);
        });
    }

    ['filter_type', 'filter_difficulty', 'filter_is_active', 'filter_sort', 'filter_unit_id'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                fetchQuestions(1);
            });
        }
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function () {
                fetchQuestions(1);
            }, 300);
        });
    }

    resultsEl.addEventListener('click', function (e) {
        var link = e.target.closest('#questionBankPagination a');
        if (!link || !link.href) {
            return;
        }
        e.preventDefault();
        try {
            var pageUrl = new URL(link.href, window.location.origin);
            var page = pageUrl.searchParams.get('page') || '1';
            fetchQuestions(parseInt(page, 10) || 1);
        } catch (err) {
            console.error(err);
        }
    });

    if (classSelect && classSelect.value) {
        populateSubjects(classSelect.value, prefillSubjectId || null);
    }
});
</script>
