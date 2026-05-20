<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('addQuestionToQuizModal');
    if (!modalEl) {
        return;
    }

    var quizzesUrlBase = @json(route('admin.subjects.quizzes.for-add', $subject->id));
    var addQuestionUrlBase = @json(url('/admin/quizzes'));
    var csrfToken = @json(csrf_token());
    var subjectName = @json($subject->name ?? '');

    var modal = typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    var previewEl = document.getElementById('addToQuizQuestionPreview');
    var loadingEl = document.getElementById('addToQuizLoading');
    var errorEl = document.getElementById('addToQuizError');
    var emptyEl = document.getElementById('addToQuizEmpty');
    var listEl = document.getElementById('addToQuizList');

    var currentQuestionId = null;
    var currentSubjectId = null;
    var currentPoints = 10;

    function showToast(type, message) {
        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-triangle') + ' me-2"></i>' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>';
        var container = document.querySelector('.main-content .container-fluid');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            setTimeout(function () {
                alertDiv.remove();
            }, 5000);
        }
    }

    function setListState(state) {
        loadingEl.classList.toggle('d-none', state !== 'loading');
        errorEl.classList.toggle('d-none', state !== 'error');
        emptyEl.classList.toggle('d-none', state !== 'empty');
        listEl.classList.toggle('d-none', state !== 'list');
    }

    function buildQuizzesUrl(questionId) {
        var url = quizzesUrlBase;
        if (questionId) {
            url += (url.indexOf('?') >= 0 ? '&' : '?') + 'question_id=' + encodeURIComponent(questionId);
        }
        return url;
    }

    function buildAddQuestionUrl(quizId) {
        return addQuestionUrlBase.replace(/\/$/, '') + '/' + quizId + '/add-question';
    }

    function renderQuizList(quizzes) {
        listEl.innerHTML = '';
        if (!quizzes || quizzes.length === 0) {
            setListState('empty');
            return;
        }

        quizzes.forEach(function (quiz) {
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center add-to-quiz-pick';
            item.dataset.quizId = quiz.id;

            var titleWrap = document.createElement('div');
            titleWrap.className = 'text-start';
            var title = document.createElement('span');
            title.className = 'fw-semibold d-block';
            title.textContent = quiz.title;
            titleWrap.appendChild(title);

            var meta = document.createElement('small');
            meta.className = 'text-muted';
            var statusParts = [];
            if (quiz.is_published) {
                statusParts.push('منشور');
            }
            statusParts.push(quiz.questions_count + ' سؤال');
            meta.textContent = statusParts.join(' · ');
            titleWrap.appendChild(meta);
            item.appendChild(titleWrap);

            if (quiz.already_added) {
                item.disabled = true;
                item.classList.add('disabled');
                var badge = document.createElement('span');
                badge.className = 'badge bg-success-transparent text-success';
                badge.textContent = 'مضاف';
                item.appendChild(badge);
            } else {
                var icon = document.createElement('i');
                icon.className = 'bi bi-plus-circle text-success';
                item.appendChild(icon);
            }

            listEl.appendChild(item);
        });

        setListState('list');
    }

    function loadQuizzes(subjectId, questionId) {
        setListState('loading');
        errorEl.textContent = '';

        fetch(buildQuizzesUrl(questionId), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error('load failed');
                }
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.success) {
                    throw new Error('invalid response');
                }
                renderQuizList(data.quizzes || []);
            })
            .catch(function () {
                errorEl.textContent = 'تعذر تحميل قائمة الاختبارات';
                setListState('error');
            });
    }

    function addQuestionToQuiz(quizId, btn) {
        if (!currentQuestionId) {
            return;
        }

        btn.disabled = true;
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(buildAddQuestionUrl(quizId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                question_id: currentQuestionId,
                points: currentPoints,
            }),
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, status: res.status, data: data };
                });
            })
            .then(function (result) {
                if (result.ok && result.data && result.data.success) {
                    showToast('success', result.data.message || 'تم إضافة السؤال للاختبار بنجاح');
                    btn.innerHTML = '';
                    var badge = document.createElement('span');
                    badge.className = 'badge bg-success-transparent text-success';
                    badge.textContent = 'مضاف';
                    btn.appendChild(badge);
                    btn.classList.add('disabled');
                    btn.disabled = true;
                    return;
                }

                var msg = (result.data && result.data.message) ? result.data.message : 'تعذر إضافة السؤال';
                showToast('error', msg);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            })
            .catch(function () {
                showToast('error', 'حدث خطأ أثناء الإضافة');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('.add-to-quiz-btn');
        if (trigger) {
            e.preventDefault();
            currentQuestionId = trigger.dataset.questionId;
            currentSubjectId = trigger.dataset.subjectId;
            currentPoints = parseFloat(trigger.dataset.questionPoints) || 10;

            if (previewEl) {
                previewEl.textContent = 'اختر اختباراً من مادة «' + (subjectName || '') + '» لإضافة هذا السؤال.';
            }

            loadQuizzes(currentSubjectId, currentQuestionId);

            if (modal) {
                modal.show();
            }
            return;
        }

        var pick = e.target.closest('.add-to-quiz-pick');
        if (pick && !pick.disabled && pick.dataset.quizId) {
            addQuestionToQuiz(pick.dataset.quizId, pick);
        }
    });
});
</script>
