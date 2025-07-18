<div class="wrap">
    <h1><strong>Settings</strong></h1>
    <div class="sas-admin-container">
        <h4>Copy this shortcode</h4>
        <div class="shortcode-container">
            <input
                class="sas-copy-shortcode"
                type="text"
                readonly
                value="[display_find_member_form]"
                id="shortcodeToCopy"
            >
            <button
                class="copy-button"
                onclick="copyShortcode()"
                id="copyButton"
            >
                Copy
            </button>
        </div>
        <p class="documentation-link-container">
            For more detailed instructions and advanced usage, refer to our <a href="https://github.com/your-repo-link" target="_blank" class="documentation-link">GitHub Documentation</a>.
        </p>
    </div>
</div>

<script>
    function copyShortcode() {
        const shortcodeInput = document.getElementById('shortcodeToCopy');
        const copyButton = document.getElementById('copyButton');

        shortcodeInput.select();
        shortcodeInput.setSelectionRange(0, 99999);

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                const originalButtonText = copyButton.textContent;
                const originalClasses = copyButton.className;

                copyButton.textContent = 'Copied!';
                copyButton.classList.add('bg-green-500');
                copyButton.classList.remove('copy-button');

                setTimeout(() => {
                    copyButton.textContent = originalButtonText;
                    copyButton.className = originalClasses;
                }, 2000);
            } else {
                console.error('Failed to copy text using execCommand.');
                alert('Copy failed. Please manually copy the shortcode: ' + shortcodeInput.value);
            }
        } catch (err) {
            console.error('Error copying text:', err);
            alert('Error copying. Please manually copy the shortcode: ' + shortcodeInput.value);
        }
    }
</script>