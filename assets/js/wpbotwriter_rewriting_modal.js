// Wait for the DOM to be fully loaded before executing the script
document.addEventListener("DOMContentLoaded", function() {
    // Get references to the necessary DOM elements
    var generateRewritePromptBtn = document.getElementById('generateRewritePromptBtn');
    var promptTextarea = document.getElementById('promptTextarea');
    var contentprompt = document.getElementById('content_prompt');
    var rewritePromptModal = new bootstrap.Modal(document.getElementById('rewritePromptModal'));

    // Add a click event listener to the generate rewrite prompt button
    generateRewritePromptBtn.addEventListener('click', function () {
        // Get the selected values from the dropdowns
        var selectedLanguage = document.getElementById('languageSelect').options[document.getElementById('languageSelect').selectedIndex].text;
        var selectedSubtitle = document.getElementById('subtitleSelect').options[document.getElementById('subtitleSelect').selectedIndex].text;
        var selectedNarration = document.getElementById('narrationSelect').options[document.getElementById('narrationSelect').selectedIndex].text;

        // Generate the rewrite prompt string using the selected values
        var generatedPrompt = `[autowp-rewriting-promptcode]${selectedLanguage},${selectedSubtitle},${selectedNarration}[/autowp-rewriting-promptcode]`;

        // Set the generated prompt in the prompt textarea and content prompt input
        promptTextarea.value = generatedPrompt;
        // Select the text in the prompt textarea
        promptTextarea.select();
        // Copy the selected text to the clipboard
        document.execCommand('copy');
        // Set the generated prompt in the content prompt input
        contentprompt.value = generatedPrompt;

        // Hide the rewrite prompt modal
        rewritePromptModal.hide();
    });
});