"use strict";
const menuButton = document.querySelector(".menu-toggle");
const navigation = document.querySelector(".navigation");
menuButton?.addEventListener("click", () => {
    const expanded = menuButton.getAttribute("aria-expanded") === "true";
    menuButton.setAttribute("aria-expanded", String(!expanded));
    navigation.classList.toggle("is-open", !expanded);
});
document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && menuButton?.getAttribute("aria-expanded") === "true") {
        navigation.classList.remove("is-open");
        menuButton.setAttribute("aria-expanded", "false");
        menuButton.focus();
    }
});
document.querySelectorAll(".show-password").forEach((button) => {
    button.addEventListener("click", () => {
        const input = document.getElementById(button.getAttribute("aria-controls"));
        const show = input.type === "password";
        input.type = show ? "text" : "password";
        button.textContent = show ? "Slēpt" : "Rādīt";
        button.setAttribute("aria-pressed", String(show));
    });
});
document.querySelectorAll("form[data-submit]").forEach((form) => {
    form.addEventListener("submit", () => {
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = "Lūdzu, uzgaidi…";
    });
});
window.addEventListener("pageshow", () => {
    document.querySelectorAll("button[data-original-text]").forEach((button) => {
        button.disabled = false;
        button.textContent = button.dataset.originalText;
    });
});

document.querySelectorAll("input[data-file-label]").forEach((input) => {
    let previewUrl;
    const label = document.getElementById(input.dataset.fileLabel);
    const original = label?.textContent || "";
    input.addEventListener("change", () => {
        const file = input.files?.[0];
        if (label)
            label.textContent = file
                ? Array.from(input.files)
                      .map((item) => item.name)
                      .join(", ")
                : original;
        const preview = document.getElementById(input.dataset.preview || "");
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        if (preview) {
            preview.hidden = true;
            preview.removeAttribute("src");
            if (file && /^image\/(jpeg|png|webp)$/.test(file.type)) {
                previewUrl = URL.createObjectURL(file);
                preview.src = previewUrl;
                preview.onload = () => {
                    preview.hidden = false;
                };
                preview.onerror = () => {
                    preview.hidden = true;
                };
            }
        }
    });
});
