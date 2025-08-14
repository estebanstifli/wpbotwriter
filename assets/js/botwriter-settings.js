document.addEventListener('DOMContentLoaded', function() {
    const apiKeyInput = document.getElementById('botwriter_openai_api_key');
    const toggleButton = document.getElementById('toggle_api_key');

    if (apiKeyInput && toggleButton) {
        toggleButton.addEventListener('click', function() {
            if (apiKeyInput.type === 'password') {
                apiKeyInput.type = 'text';
                toggleButton.textContent = 'Hide';
            } else {
                apiKeyInput.type = 'password';
                toggleButton.textContent = 'Show';
            }
        });
    }
}); 