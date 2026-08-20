{{--
    يربط حقل السعر بمفتاح «تفعيل السعر» في جدول العملات.

    readonly لا disabled بقصد: الحقل المعطّل لا يُرسل مع الفورم، و SubjectController يقرأ
    ‎$priceData['price'] ?? 0‎ — فإيقاف المفتاح كان سيصفّر السعر المحفوظ عند أول حفظ.
    مع readonly تبقى القيمة مُرسَلة ومحفوظة، ويُستأنف البيع بتشغيل المفتاح فقط.
--}}
<style>
    .subject-form-currency-table .price-input[readonly] {
        background-color: #f1f3f5;
        color: #868e96;
        cursor: not-allowed;
    }

    [data-theme-mode=dark] .subject-form-currency-table .price-input[readonly],
    [data-bs-theme=dark] .subject-form-currency-table .price-input[readonly] {
        background-color: rgba(255, 255, 255, 0.06);
        color: #adb5bd;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var table = document.querySelector('.subject-form-currency-table');
    if (!table) {
        return;
    }

    function syncRow(toggle) {
        var row = toggle.closest('tr');
        if (!row) {
            return;
        }

        var input = row.querySelector('.price-input');
        if (!input) {
            return;
        }

        input.readOnly = !toggle.checked;
        input.title = toggle.checked ? '' : 'شغّل «تفعيل السعر» لتعديل السعر بهذه العملة.';
    }

    table.querySelectorAll('.price-active').forEach(function (toggle) {
        syncRow(toggle);
        toggle.addEventListener('change', function () {
            syncRow(toggle);
            // فتح الحقل ثم نقل التركيز إليه يجعل الخطوة التالية واضحة بلا نقرة إضافية.
            if (toggle.checked) {
                var input = toggle.closest('tr')?.querySelector('.price-input');
                input?.focus();
                input?.select();
            }
        });
    });
});
</script>
