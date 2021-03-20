const input = document.querySelector('input[type="file"]');
const formName = input.classList[0].split('__')[0];
const button = document.querySelector(`.${formName}__input-file-button`);
const previewsContainer = document.querySelector('.dropzone-previews');

button.addEventListener('click', function () {
    input.click();
});

input.addEventListener('change', function () {
    const file = input.files[0];

    if (file.type.indexOf('image/') === -1) {
        previewsContainer.innerHTML = '';
        return;
    }

    const reader = new FileReader();

    reader.addEventListener('load', function () {
        const previewTemplate = `
            <div class="dz-preview dz-image-preview">
                <div class="${formName}__image-wrapper form__file-wrapper">
                    <img class="form__image" src="${reader.result}" alt="${file.name}" data-dz-thumbnail="">
                </div>
                <div style="max-width: 361px;" class="${formName}__file-data form__file-data">
                    <span class="${formName}__file-name form__file-name dz-filename" data-dz-name="">${file.name}</span>
                    <button class="${formName}__delete-button form__delete-button button" type="button" data-dz-remove="">
                        <span>Удалить</span>
                        <svg style="margin-left: 6px;" class="${formName}__delete-icon form__delete-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" width="12" height="12">
                            <path d="M18 1.3L16.7 0 9 7.7 1.3 0 0 1.3 7.7 9 0 16.7 1.3 18 9 10.3l7.7 7.7 1.3-1.3L10.3 9z"></path>
                        </svg>
                    </button>
                </div>
            </div>`;

        previewsContainer.innerHTML = previewTemplate;

        document.querySelector(`.${formName}__delete-button`).addEventListener('click', function () {
            previewsContainer.innerHTML = '';
            input.value = '';
        });
    });

    reader.readAsDataURL(file);
});
