document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("astro_space_image");
    const dropzone = document.getElementById("astro-dropzone");
    const fileName = document.getElementById("astro-file-name");
    const preview = document.getElementById("astro-image-preview");

    if (!input || !dropzone || !fileName || !preview) {
        return;
    }

    let previewUrl = null;

    function displayFile(file) {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        if (!file || !file.type.startsWith("image/")) {
            fileName.textContent = "";
            preview.hidden = true;
            preview.removeAttribute("src");
            dropzone.classList.remove("has-preview");
            return;
        }

        fileName.textContent = "Selected image: " + file.name;
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.hidden = false;
        dropzone.classList.add("has-preview");
    }

    input.addEventListener("change", function () {
        displayFile(input.files[0]);
    });

    ["dragenter", "dragover"].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            dropzone.classList.add("dragging");
        });
    });

    ["dragleave", "drop"].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            dropzone.classList.remove("dragging");
        });
    });

    dropzone.addEventListener("drop", function (event) {
        const files = event.dataTransfer.files;

        if (files.length > 0) {
            input.files = files;
            displayFile(files[0]);
        }
    });
});
