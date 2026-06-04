const textarea = document.getElementById('custom-envs');
const status = document.getElementById('status');

chrome.storage.local.get(['qlms_custom_environments'], (stored) => {
  const custom = stored.qlms_custom_environments || [];
  textarea.value = JSON.stringify(custom, null, 2);
});

document.getElementById('save').addEventListener('click', () => {
  try {
    const parsed = JSON.parse(textarea.value.trim() || '[]');
    if (!Array.isArray(parsed)) {
      throw new Error('يجب أن يكون الملف مصفوفة JSON');
    }
    chrome.storage.local.set({ qlms_custom_environments: parsed }, () => {
      status.textContent = 'تم الحفظ.';
    });
  } catch (error) {
    status.textContent = error.message;
  }
});
