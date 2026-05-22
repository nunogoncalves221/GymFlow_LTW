document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('profile_picture');
    if (!fileInput) return;

    const preview = document.querySelector('.profile-picture-preview');
    const hint    = fileInput.closest('form')?.querySelector('.upload-hint');

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;

        if (preview) {
            const reader = new FileReader();
            reader.onload = (e) => { preview.src = e.target.result; };
            reader.readAsDataURL(file);
        }

        if (hint) hint.textContent = file.name;
    });
});
